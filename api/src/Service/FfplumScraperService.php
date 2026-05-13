<?php

declare(strict_types=1);

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Scrape l'annuaire public des structures FFPLUM (clubs et écoles ULM).
 *
 * Source : https://ffplum.fr/annuaires/structures/structures.php?dept={DEPT}
 * Le HTML est server-side, sans pagination ni JS dynamique.
 *
 * Données extraites par structure :
 *  - code FFPLUM (identifiant unique, ex: "01303")
 *  - nom
 *  - adresse / cp / ville
 *  - président (nom)
 *  - téléphone, email, site web
 *  - département (input)
 */
class FfplumScraperService
{
    public const BASE_URL = 'https://ffplum.fr/annuaires/structures/structures.php';

    private const USER_AGENT = 'CREAZOT-ULM Prospect Importer (B2B research, contact via creazot-ulm.fr)';

    /**
     * Liste exhaustive des codes département supportés par FFPLUM.
     * On laisse le scraper tolérant : un dépt vide ou inconnu retourne [] sans erreur.
     */
    public const DEPARTMENTS = [
        '01', '02', '03', '04', '05', '06', '07', '08', '09', '10',
        '11', '12', '13', '14', '15', '16', '17', '18', '19',
        '20',
        '21', '22', '23', '24', '25', '26', '27', '28', '29', '30',
        '31', '32', '33', '34', '35', '36', '37', '38', '39', '40',
        '41', '42', '43', '44', '45', '46', '47', '48', '49', '50',
        '51', '52', '53', '54', '55', '56', '57', '58', '59', '60',
        '61', '62', '63', '64', '65', '66', '67', '68', '69', '70',
        '71', '72', '73', '74', '75', '76', '77', '78', '79', '80',
        '81', '82', '83', '84', '85', '86', '87', '88', '89', '90',
        '91', '92', '93', '94', '95',
        '971', '972', '973', '974', '975', '976', '977', '978',
        '986', '987', '988',
    ];

    public function __construct(
        private HttpClientInterface $httpClient,
        private LoggerInterface $logger,
    ) {}

    public const INSTRUCTORS_BASE_URL = 'https://ffplum.fr/annuaires/instructeurs/instructeurs.php';

    /**
     * Récupère et parse les instructeurs pour un département donné.
     *
     * @return array<int, array<string, mixed>>
     */
    public function scrapeInstructorsDepartment(string $dept): array
    {
        $url = sprintf('%s?dept=%s', self::INSTRUCTORS_BASE_URL, urlencode($dept));

        try {
            $response = $this->httpClient->request('GET', $url, [
                'headers' => [
                    'User-Agent' => self::USER_AGENT,
                    'Accept' => 'text/html,application/xhtml+xml',
                    'Accept-Language' => 'fr-FR,fr;q=0.9',
                ],
                'timeout' => 30,
            ]);
            if ($response->getStatusCode() !== 200) {
                return [];
            }
            $html = $response->getContent();
        } catch (\Throwable $e) {
            $this->logger->error('FFPLUM scrape instructors: HTTP error', ['dept' => $dept, 'error' => $e->getMessage()]);
            return [];
        }

        return $this->parseInstructors($html, $dept);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function parseInstructors(string $html, string $dept): array
    {
        $crawler = new Crawler($html);
        $instructors = [];

        $titleRows = $crawler->filter('td.TitreEncartPage[align="left"]');
        foreach ($titleRows as $titleNode) {
            $name = $this->cleanText($titleNode->textContent ?? '');
            if ($name === '') {
                continue;
            }

            $titleTr = $this->findAncestor($titleNode, 'tr');
            if ($titleTr === null) {
                continue;
            }

            // Pratiques : <td.TitreEncartPage align="right"><i>Pratiques : XXX</i></td>
            $practices = null;
            $iElements = $titleTr->getElementsByTagName('i');
            foreach ($iElements as $i) {
                $txt = $this->cleanText($i->textContent ?? '');
                if (str_starts_with($txt, 'Pratiques')) {
                    $practices = trim(preg_replace('/^Pratiques\s*:\s*/u', '', $txt) ?? '');
                    break;
                }
            }
            if ($practices === null || $practices === '') {
                continue;
            }

            $detailTable = $this->findNextDetailTable($titleNode);
            $details = $detailTable !== null
                ? $this->extractInstructorDetails($detailTable)
                : ['terrains' => [], 'phone' => null, 'mobile' => null, 'email' => null];

            $instructors[] = [
                'dept' => $dept,
                'name' => $name,
                'practices' => array_map('trim', explode(',', $practices)),
                'terrains' => $details['terrains'],
                'phone' => $details['phone'],
                'mobile' => $details['mobile'],
                'email' => $details['email'],
                'source_url' => sprintf('%s?dept=%s', self::INSTRUCTORS_BASE_URL, urlencode($dept)),
                'scraped_at' => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
            ];
        }

        return $instructors;
    }

    /**
     * @return array{terrains: array<int, array{oaci: ?string, name: ?string}>, phone: ?string, mobile: ?string, email: ?string}
     */
    private function extractInstructorDetails(\DOMElement $detailTable): array
    {
        $crawler = new Crawler($detailTable);

        $leftHtml = '';
        $left = $crawler->filter('td.Contenu[align="left"]');
        if ($left->count() > 0) {
            $leftHtml = $left->first()->html();
        }

        $rightHtml = '';
        $right = $crawler->filter('td.Contenu[align="right"]');
        if ($right->count() > 0) {
            $rightHtml = $right->first()->html();
        }

        return [
            'terrains' => $this->parseTerrains($leftHtml),
            'phone' => $this->parseLabeledPhone($rightHtml, 'Tel'),
            'mobile' => $this->parseLabeledPhone($rightHtml, 'Mobile'),
            'email' => $this->parseEmail($rightHtml),
        ];
    }

    /**
     * @return array<int, array{oaci: ?string, name: ?string}>
     */
    private function parseTerrains(string $html): array
    {
        $text = $this->htmlToPlainText($html);

        $terrains = [];
        $blocks = preg_split('/Code\s+du\s+terrain\s*:\s*/iu', $text) ?: [];
        array_shift($blocks);

        foreach ($blocks as $block) {
            $clean = $this->cleanText($block);
            if (preg_match('/^([A-Z0-9]+)?\s*Terrain\s*:\s*(.+)$/iu', $clean, $m)) {
                $oaci = isset($m[1]) ? trim($m[1]) : '';
                $name = isset($m[2]) ? $this->cleanText($m[2]) : '';
                if ($oaci === '' && $name === '') {
                    continue;
                }
                $terrains[] = [
                    'oaci' => $oaci !== '' ? $oaci : null,
                    'name' => $name !== '' ? $name : null,
                ];
            }
        }

        return $terrains;
    }

    private function parseLabeledPhone(string $html, string $label): ?string
    {
        $text = $this->htmlToPlainText($html);
        if (preg_match('/' . preg_quote($label, '/') . '\.?\s*:\s*([+\d\s().-]+)/iu', $text, $m)) {
            $cleaned = preg_replace('/[^\d+]/', '', trim($m[1]));
            return $cleaned !== '' ? $cleaned : null;
        }
        return null;
    }

    /**
     * Récupère et parse les structures pour un département donné.
     *
     * @return array<int, array<string, mixed>>
     */
    public function scrapeDepartment(string $dept): array
    {
        $url = sprintf('%s?dept=%s', self::BASE_URL, urlencode($dept));

        try {
            $response = $this->httpClient->request('GET', $url, [
                'headers' => [
                    'User-Agent' => self::USER_AGENT,
                    'Accept' => 'text/html,application/xhtml+xml',
                    'Accept-Language' => 'fr-FR,fr;q=0.9',
                ],
                'timeout' => 30,
            ]);

            $statusCode = $response->getStatusCode();
            if ($statusCode !== 200) {
                $this->logger->warning('FFPLUM scrape: non-200 response', [
                    'dept' => $dept,
                    'status' => $statusCode,
                ]);

                return [];
            }

            $html = $response->getContent();
        } catch (\Throwable $e) {
            $this->logger->error('FFPLUM scrape: HTTP error', [
                'dept' => $dept,
                'error' => $e->getMessage(),
            ]);

            return [];
        }

        $structures = $this->parse($html, $dept);

        $this->logger->info('FFPLUM scrape: parsed', [
            'dept' => $dept,
            'count' => count($structures),
        ]);

        return $structures;
    }

    /**
     * Parse le HTML d'une page d'annuaire et retourne les structures.
     *
     * @return array<int, array<string, mixed>>
     */
    public function parse(string $html, string $dept): array
    {
        $crawler = new Crawler($html);

        $structures = [];

        // Chaque structure = une <tr> qui contient un td.TitreEncartPage[width="48%"] non vide
        // (le bloc avec le nom du club). On itère sur ces lignes-titre, puis pour chacune
        // on remonte au <table> parent et on cherche le <table bgcolor="#E4EBF8"> suivant
        // qui contient le détail (adresse, président, contacts).
        $titleRows = $crawler->filter('td.TitreEncartPage[width="48%"]');

        foreach ($titleRows as $titleNode) {
            $name = $this->cleanText($titleNode->textContent ?? '');
            if ($name === '') {
                continue;
            }

            $titleTr = $this->findAncestor($titleNode, 'tr');
            if ($titleTr === null) {
                continue;
            }

            $code = $this->extractCode($titleTr);
            $detailTable = $this->findNextDetailTable($titleNode);

            $details = $detailTable !== null
                ? $this->extractDetails($detailTable)
                : ['raw_left' => '', 'raw_right' => '', 'address' => null, 'zip' => null, 'city' => null, 'president' => null, 'phone' => null, 'email' => null, 'website' => null];

            // On exclut les lignes "header" du tableau (CD 013, 00000) qui n'ont pas de code numérique
            // de structure et dont le nom commence par "CD ".
            if (preg_match('/^CD\s+\d+$/u', $name) === 1) {
                continue;
            }

            $structures[] = [
                'dept' => $dept,
                'code' => $code,
                'name' => $name,
                'address' => $details['address'],
                'zip' => $details['zip'],
                'city' => $details['city'],
                'president' => $details['president'],
                'phone' => $details['phone'],
                'email' => $details['email'],
                'website' => $details['website'],
                'source_url' => sprintf('%s?dept=%s', self::BASE_URL, urlencode($dept)),
                'scraped_at' => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
            ];
        }

        return $structures;
    }

    private function findAncestor(\DOMNode $node, string $tagName): ?\DOMElement
    {
        $current = $node->parentNode;
        while ($current !== null) {
            if ($current instanceof \DOMElement && strtolower($current->nodeName) === strtolower($tagName)) {
                return $current;
            }
            $current = $current->parentNode;
        }

        return null;
    }

    private function extractCode(\DOMElement $titleTr): ?string
    {
        $iElements = $titleTr->getElementsByTagName('i');
        foreach ($iElements as $i) {
            $text = $this->cleanText($i->textContent ?? '');
            if ($text !== '') {
                return $text;
            }
        }

        return null;
    }

    /**
     * Trouve la <table bgcolor="#E4EBF8"> qui suit le <table> contenant le titre.
     */
    private function findNextDetailTable(\DOMNode $titleNode): ?\DOMElement
    {
        $titleTable = $this->findAncestor($titleNode, 'table');
        if ($titleTable === null) {
            return null;
        }

        $sibling = $titleTable->nextSibling;
        while ($sibling !== null) {
            if (
                $sibling instanceof \DOMElement
                && strtolower($sibling->nodeName) === 'table'
                && strtolower($sibling->getAttribute('bgcolor')) === '#e4ebf8'
            ) {
                return $sibling;
            }
            $sibling = $sibling->nextSibling;
        }

        return null;
    }

    /**
     * @return array{
     *   raw_left: string,
     *   raw_right: string,
     *   address: ?string,
     *   zip: ?string,
     *   city: ?string,
     *   president: ?string,
     *   phone: ?string,
     *   email: ?string,
     *   website: ?string
     * }
     */
    private function extractDetails(\DOMElement $detailTable): array
    {
        $crawler = new Crawler($detailTable);

        // Bloc gauche : adresse, CP, ville, président
        $leftHtml = '';
        $leftTd = $crawler->filter('td.Contenu[align="left"][width="364px"]');
        if ($leftTd->count() > 0) {
            $leftHtml = $leftTd->first()->html();
        }

        // Bloc droit : tel, email, site web
        $rightHtml = '';
        $rightTd = $crawler->filter('td.Contenu[align="right"][width="220px"]');
        if ($rightTd->count() > 0) {
            $rightHtml = $rightTd->first()->html();
        }

        return [
            'raw_left' => $leftHtml,
            'raw_right' => $rightHtml,
            'address' => $this->parseAddress($leftHtml),
            'zip' => $this->parseZip($leftHtml),
            'city' => $this->parseCity($leftHtml),
            'president' => $this->parsePresident($leftHtml),
            'phone' => $this->parsePhone($rightHtml),
            'email' => $this->parseEmail($rightHtml),
            'website' => $this->parseWebsite($rightHtml),
        ];
    }

    private function parseAddress(string $leftHtml): ?string
    {
        // L'adresse est tout ce qui précède le <br> qui précède la ligne CP/ville.
        // Format observé : "<adresse libre><br>{cp}\t/ {ville}<br><br><u>Président</u> : ..."
        $text = $this->htmlToPlainText($leftHtml);
        $lines = $this->splitNonEmptyLines($text);

        if ($lines === []) {
            return null;
        }

        $addressLines = [];
        foreach ($lines as $line) {
            // On s'arrête dès qu'on tombe sur la ligne CP / ville
            if (preg_match('/^\d{4,5}\s*\/\s*.+/u', $line)) {
                break;
            }
            // Ou sur la ligne Président
            if (mb_stripos($line, 'Président') !== false) {
                break;
            }
            $addressLines[] = $line;
        }

        $address = trim(implode(' ', $addressLines));

        return $address !== '' ? $address : null;
    }

    private function parseZip(string $leftHtml): ?string
    {
        $text = $this->htmlToPlainText($leftHtml);
        if (preg_match('/(\d{4,5})\s*\/\s*[A-ZÀ-Ÿ]/u', $text, $m)) {
            return $m[1];
        }

        return null;
    }

    private function parseCity(string $leftHtml): ?string
    {
        $text = $this->htmlToPlainText($leftHtml);
        if (preg_match('/\d{4,5}\s*\/\s*([^\n\r]+?)(?:\s*Président|\s*$)/u', $text, $m)) {
            return $this->cleanText($m[1]);
        }

        return null;
    }

    private function parsePresident(string $leftHtml): ?string
    {
        $text = $this->htmlToPlainText($leftHtml);
        if (preg_match('/Président\s*:\s*([^\n\r]+)/u', $text, $m)) {
            return $this->cleanText($m[1]);
        }

        return null;
    }

    private function parsePhone(string $rightHtml): ?string
    {
        $text = $this->htmlToPlainText($rightHtml);
        if (preg_match('/Tel\.\s*:\s*([+\d\s().-]+)/iu', $text, $m)) {
            $raw = trim($m[1]);
            // Nettoyage minimal : on garde +, chiffres et espaces utiles
            $cleaned = preg_replace('/[^\d+]/', '', $raw);

            return $cleaned !== '' ? $cleaned : null;
        }

        return null;
    }

    private function parseEmail(string $rightHtml): ?string
    {
        // On extrait depuis le href mailto: pour éviter les surprises de mise en forme.
        if (preg_match('/mailto:([^"\'\s>]+)/i', $rightHtml, $m)) {
            return strtolower(trim($m[1]));
        }

        return null;
    }

    private function parseWebsite(string $rightHtml): ?string
    {
        // FFPLUM produit des href cassés ("http://https://..."), donc on lit le TEXTE
        // du lien "Site Internet" plutôt que son href.
        if (preg_match('/Site\s+Internet\s*:\s*<a[^>]*>([^<]+)<\/a>/iu', $rightHtml, $m)) {
            return $this->normalizeWebsite($m[1]);
        }

        return null;
    }

    private function normalizeWebsite(string $raw): ?string
    {
        $url = trim($raw);
        if ($url === '') {
            return null;
        }

        // Si la chaîne commence déjà par http(s)://, on garde tel quel.
        // Sinon on préfixe par https://.
        if (!preg_match('#^https?://#i', $url)) {
            $url = 'https://' . ltrim($url, '/');
        }

        return $url;
    }

    private function htmlToPlainText(string $html): string
    {
        // Remplace <br> par retour ligne pour préserver la structure visuelle
        $withBreaks = preg_replace('/<br\s*\/?>/i', "\n", $html);
        $stripped = strip_tags($withBreaks ?? $html);
        $decoded = html_entity_decode($stripped, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return $decoded;
    }

    /**
     * @return string[]
     */
    private function splitNonEmptyLines(string $text): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $text) ?: [];
        $clean = [];
        foreach ($lines as $line) {
            $trimmed = $this->cleanText($line);
            if ($trimmed !== '') {
                $clean[] = $trimmed;
            }
        }

        return $clean;
    }

    private function cleanText(string $text): string
    {
        $text = str_replace(["\xc2\xa0", "\t"], ' ', $text);
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

        return trim($text);
    }
}
