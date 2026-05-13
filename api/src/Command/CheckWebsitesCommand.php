<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Vérifie l'état et le contenu basique des sites web des structures FFPLUM.
 *
 * Pour chaque site :
 *  - Vérifie qu'il répond (status, HTTPS, latence)
 *  - Récupère le titre <title>
 *  - Détecte des mots-clés métier (réservation, école, formation, tarifs, stage, baptême)
 *  - Détecte la présence d'outils concurrents (OpenFlyers, Aerogest, MyFlight, Skyfox, etc.)
 */
#[AsCommand(
    name: 'app:prospects:check-websites',
    description: 'Vérifie les sites web FFPLUM (vivant/mort, mots-clés, détection outils concurrents)',
)]
class CheckWebsitesCommand extends Command
{
    private const KEYWORDS = [
        'reservation' => '/r[eé]servation|réserver maintenant|book(ing)?/iu',
        'tarifs' => '/\btarifs?\b|prix.*vol/iu',
        'ecole' => '/\béco?le\b|formation/iu',
        'stage' => '/\bstage[s]?\b/iu',
        'bapteme' => '/bapt[eê]me/iu',
        'instructeurs' => '/instructeur[s]?/iu',
        'planning' => '/\bplanning\b|agenda en ligne/iu',
    ];

    private const COMPETITORS = [
        'openflyers' => '/openflyers/iu',
        'aerogest' => '/aerogest/iu',
        'myflight' => '/myflight/iu',
        'skyfox' => '/skyfox/iu',
        'aerops' => '/aerops/iu',
        'aerocrm' => '/aerocrm/iu',
        'flightlogger' => '/flightlogger/iu',
        'simpleflight' => '/simpleflight/iu',
    ];

    public function __construct(
        private HttpClientInterface $httpClient,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('input', null, InputOption::VALUE_REQUIRED, 'JSON FFPLUM en entrée', 'var/prospects/ffplum-full.json')
            ->addOption('output', null, InputOption::VALUE_REQUIRED, 'JSON résultats', 'var/prospects/websites-status.json')
            ->addOption('limit', null, InputOption::VALUE_REQUIRED, 'Limiter à N (test)')
            ->addOption('concurrency', null, InputOption::VALUE_REQUIRED, 'Nb requêtes parallèles', '8')
            ->addOption('timeout', null, InputOption::VALUE_REQUIRED, 'Timeout par requête (s)', '8');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $inputPath = (string) $input->getOption('input');
        $outputPath = (string) $input->getOption('output');
        $limit = $input->getOption('limit') !== null ? (int) $input->getOption('limit') : null;
        $concurrency = max(1, (int) $input->getOption('concurrency'));
        $timeout = max(1, (int) $input->getOption('timeout'));

        if (!is_file($inputPath)) {
            $io->error(sprintf('Fichier introuvable : %s', $inputPath));
            return Command::INVALID;
        }

        $structures = json_decode((string) file_get_contents($inputPath), true);
        if (!is_array($structures)) {
            $io->error('JSON invalide.');
            return Command::INVALID;
        }

        $targets = [];
        foreach ($structures as $s) {
            if (!empty($s['website'])) {
                $targets[] = $s;
            }
        }
        if ($limit !== null) {
            $targets = array_slice($targets, 0, $limit);
        }

        $total = count($targets);
        $io->title(sprintf('Vérification de %d sites web (concurrence %d)', $total, $concurrency));

        $results = [];
        $stats = ['alive' => 0, 'dead' => 0, 'https' => 0, 'with_keywords' => 0, 'with_competitor' => 0];
        $batches = array_chunk($targets, $concurrency);

        $io->progressStart($total);

        foreach ($batches as $batch) {
            $responses = [];
            foreach ($batch as $s) {
                try {
                    $responses[$s['code']] = [
                        's' => $s,
                        'r' => $this->httpClient->request('GET', $s['website'], [
                            'timeout' => $timeout,
                            'max_duration' => $timeout + 2,
                            'max_redirects' => 5,
                            'headers' => [
                                'User-Agent' => 'Mozilla/5.0 (compatible; CREAZOT-Crawler/1.0; +https://creazot-ulm.fr)',
                                'Accept' => 'text/html',
                                'Accept-Language' => 'fr-FR,fr;q=0.9',
                            ],
                        ]),
                    ];
                } catch (\Throwable $e) {
                    $results[] = $this->dead($s, 'request_init: ' . $e->getMessage());
                    $stats['dead']++;
                    $io->progressAdvance();
                }
            }

            foreach ($responses as $code => $bundle) {
                $s = $bundle['s'];
                $r = $bundle['r'];
                try {
                    $status = $r->getStatusCode();
                    $info = $r->getInfo();
                    $url = (string) ($info['url'] ?? $s['website']);
                    $isHttps = str_starts_with($url, 'https://');

                    if ($status >= 400) {
                        $results[] = $this->dead($s, 'http_' . $status);
                        $stats['dead']++;
                    } else {
                        $body = $r->getContent(false);
                        $analysis = $this->analyze($body);
                        $alive = [
                            'code' => $s['code'],
                            'website' => $url,
                            'alive' => true,
                            'status' => $status,
                            'https' => $isHttps,
                            'latency_ms' => isset($info['total_time']) ? (int) round($info['total_time'] * 1000) : null,
                            'final_url' => $url,
                            'title' => $analysis['title'],
                            'keywords' => $analysis['keywords'],
                            'competitors' => $analysis['competitors'],
                            'has_keywords' => $analysis['keywords'] !== [],
                            'has_competitor' => $analysis['competitors'] !== [],
                        ];
                        $results[] = $alive;
                        $stats['alive']++;
                        if ($isHttps) $stats['https']++;
                        if ($alive['has_keywords']) $stats['with_keywords']++;
                        if ($alive['has_competitor']) $stats['with_competitor']++;
                    }
                } catch (\Throwable $e) {
                    $results[] = $this->dead($s, 'response: ' . substr($e->getMessage(), 0, 80));
                    $stats['dead']++;
                }
                $io->progressAdvance();
            }
        }

        $io->progressFinish();

        $fs = new Filesystem();
        $fs->mkdir(dirname($outputPath));
        $json = json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $fs->dumpFile($outputPath, $json !== false ? $json : '[]');

        $io->section('Résumé');
        $io->table(
            ['Total', 'Vivants', 'Morts', 'HTTPS', 'Mots-clés métier', 'Outil concurrent détecté'],
            [[$total, $stats['alive'], $stats['dead'], $stats['https'], $stats['with_keywords'], $stats['with_competitor']]],
        );
        $io->success(sprintf('JSON écrit : %s', $outputPath));

        return Command::SUCCESS;
    }

    /**
     * @param array<string, mixed> $s
     * @return array<string, mixed>
     */
    private function dead(array $s, string $reason): array
    {
        return [
            'code' => $s['code'],
            'website' => $s['website'] ?? null,
            'alive' => false,
            'reason' => $reason,
        ];
    }

    /**
     * @return array{title: ?string, keywords: array<int, string>, competitors: array<int, string>}
     */
    private function analyze(string $html): array
    {
        $title = null;
        if (preg_match('/<title[^>]*>(.*?)<\/title>/iu', $html, $m)) {
            $title = trim(html_entity_decode(strip_tags($m[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            if (mb_strlen($title) > 200) {
                $title = mb_substr($title, 0, 200);
            }
        }

        $keywords = [];
        foreach (self::KEYWORDS as $name => $rx) {
            if (preg_match($rx, $html)) {
                $keywords[] = $name;
            }
        }

        $competitors = [];
        foreach (self::COMPETITORS as $name => $rx) {
            if (preg_match($rx, $html)) {
                $competitors[] = $name;
            }
        }

        return ['title' => $title, 'keywords' => $keywords, 'competitors' => $competitors];
    }
}
