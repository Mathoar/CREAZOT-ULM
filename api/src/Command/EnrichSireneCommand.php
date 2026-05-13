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
 * Enrichit le dataset FFPLUM via l'API recherche-entreprises (data.gouv.fr).
 * Gratuit, sans clé, limite ~7 req/sec.
 *
 * @link https://api.gouv.fr/les-api/api-recherche-entreprises
 */
#[AsCommand(
    name: 'app:prospects:enrich-sirene',
    description: 'Enrichit le JSON FFPLUM avec SIRET / NAF / effectif / nature juridique / dirigeants via recherche-entreprises.api.gouv.fr',
)]
class EnrichSireneCommand extends Command
{
    private const API_URL = 'https://recherche-entreprises.api.gouv.fr/search';

    public function __construct(
        private HttpClientInterface $httpClient,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('input', null, InputOption::VALUE_REQUIRED, 'Chemin du JSON FFPLUM en entrée', 'var/prospects/ffplum-full.json')
            ->addOption('output', null, InputOption::VALUE_REQUIRED, 'Chemin du JSON enrichi en sortie', 'var/prospects/enrichment-sirene.json')
            ->addOption('delay', null, InputOption::VALUE_REQUIRED, 'Délai entre appels API en ms', '180')
            ->addOption('limit', null, InputOption::VALUE_REQUIRED, 'Limiter à N structures (test)')
            ->addOption('resume', null, InputOption::VALUE_NONE, 'Reprendre le fichier de sortie existant (skip ce qui est déjà enrichi)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $inputPath = (string) $input->getOption('input');
        $outputPath = (string) $input->getOption('output');
        $delayMs = max(0, (int) $input->getOption('delay'));
        $limit = $input->getOption('limit') !== null ? (int) $input->getOption('limit') : null;
        $resume = (bool) $input->getOption('resume');

        if (!is_file($inputPath)) {
            $io->error(sprintf('Fichier introuvable : %s', $inputPath));
            return Command::INVALID;
        }

        $structures = json_decode((string) file_get_contents($inputPath), true);
        if (!is_array($structures)) {
            $io->error('JSON invalide.');
            return Command::INVALID;
        }

        $existing = [];
        if ($resume && is_file($outputPath)) {
            $loaded = json_decode((string) file_get_contents($outputPath), true);
            if (is_array($loaded)) {
                foreach ($loaded as $row) {
                    if (isset($row['code'])) {
                        $existing[$row['code']] = $row;
                    }
                }
                $io->note(sprintf('Reprise : %d entrées déjà enrichies.', count($existing)));
            }
        }

        if ($limit !== null) {
            $structures = array_slice($structures, 0, $limit);
        }

        $total = count($structures);
        $io->title(sprintf('Enrichissement SIRENE — %d structures', $total));

        $results = [];
        $stats = ['matched' => 0, 'no_match' => 0, 'error' => 0, 'skipped' => 0];

        $io->progressStart($total);
        foreach ($structures as $s) {
            $code = $s['code'] ?? null;
            if ($code !== null && isset($existing[$code])) {
                $results[] = $existing[$code];
                $stats['skipped']++;
                $io->progressAdvance();
                continue;
            }

            $match = $this->lookup($s);
            if ($match === null) {
                $results[] = ['code' => $code, 'matched' => false];
                $stats['no_match']++;
            } elseif (isset($match['error'])) {
                $results[] = ['code' => $code, 'matched' => false, 'error' => $match['error']];
                $stats['error']++;
            } else {
                $results[] = array_merge(['code' => $code, 'matched' => true], $match);
                $stats['matched']++;
            }

            $io->progressAdvance();
            if ($delayMs > 0) {
                usleep($delayMs * 1000);
            }
        }
        $io->progressFinish();

        $fs = new Filesystem();
        $fs->mkdir(dirname($outputPath));
        $json = json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $fs->dumpFile($outputPath, $json !== false ? $json : '[]');

        $io->section('Résumé');
        $io->table(
            ['Total', 'Matchées', 'Aucun match', 'Erreurs', 'Skippées (resume)'],
            [[
                $total,
                $stats['matched'],
                $stats['no_match'],
                $stats['error'],
                $stats['skipped'],
            ]],
        );

        $io->success(sprintf('JSON écrit : %s', $outputPath));

        return Command::SUCCESS;
    }

    /**
     * @param array<string, mixed> $structure
     * @return array<string, mixed>|null
     */
    private function lookup(array $structure): ?array
    {
        $name = (string) ($structure['name'] ?? '');
        $zip = (string) ($structure['zip'] ?? '');
        if ($name === '') {
            return null;
        }

        $params = [
            'q' => $this->normalizeQuery($name),
            'page' => 1,
            'per_page' => 5,
        ];
        if ($zip !== '' && preg_match('/^\d{5}$/', $zip)) {
            $params['code_postal'] = $zip;
        }

        try {
            $response = $this->httpClient->request('GET', self::API_URL, [
                'query' => $params,
                'timeout' => 10,
                'headers' => ['Accept' => 'application/json'],
            ]);
            if ($response->getStatusCode() !== 200) {
                return ['error' => 'http_' . $response->getStatusCode()];
            }
            $data = $response->toArray(false);
        } catch (\Throwable $e) {
            return ['error' => substr($e->getMessage(), 0, 80)];
        }

        $candidates = $data['results'] ?? [];
        if ($candidates === []) {
            // Retry sans code postal
            try {
                $response = $this->httpClient->request('GET', self::API_URL, [
                    'query' => ['q' => $this->normalizeQuery($name), 'page' => 1, 'per_page' => 5],
                    'timeout' => 10,
                ]);
                if ($response->getStatusCode() === 200) {
                    $data = $response->toArray(false);
                    $candidates = $data['results'] ?? [];
                }
            } catch (\Throwable) {
                // ignore retry errors
            }
        }

        if ($candidates === []) {
            return null;
        }

        $best = $this->pickBestMatch($name, $zip, $candidates);
        if ($best === null) {
            return null;
        }

        return $this->extractFields($best);
    }

    private function normalizeQuery(string $name): string
    {
        $name = transliterator_transliterate('Any-Latin; Latin-ASCII', $name);
        $name = (string) preg_replace('/[^a-zA-Z0-9\s]/u', ' ', $name);
        $name = (string) preg_replace('/\s+/', ' ', $name);
        return trim($name);
    }

    /**
     * @param array<int, array<string, mixed>> $candidates
     * @return array<string, mixed>|null
     */
    private function pickBestMatch(string $sourceName, string $sourceZip, array $candidates): ?array
    {
        $normalize = function (string $s): string {
            $s = transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', $s);
            return trim((string) preg_replace('/[^a-z0-9 ]/', ' ', $s));
        };

        $needle = $normalize($sourceName);
        $bestScore = 0.0;
        $best = null;

        foreach ($candidates as $c) {
            $name = (string) ($c['nom_complet'] ?? $c['nom_raison_sociale'] ?? '');
            $hay = $normalize($name);
            if ($hay === '') {
                continue;
            }
            similar_text($needle, $hay, $score);

            $cpMatch = false;
            if ($sourceZip !== '') {
                $candidateZip = (string) ($c['siege']['code_postal'] ?? '');
                $cpMatch = $candidateZip === $sourceZip;
            }
            if ($cpMatch) {
                $score += 15;
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $c;
            }
        }

        return $bestScore >= 55.0 ? $best : null;
    }

    /**
     * @param array<string, mixed> $entry
     * @return array<string, mixed>
     */
    private function extractFields(array $entry): array
    {
        $siege = $entry['siege'] ?? [];

        $effectifs = [
            'NN' => 'Pas d\'effectif renseigné',
            '00' => '0 salarié',
            '01' => '1-2 salariés',
            '02' => '3-5 salariés',
            '03' => '6-9 salariés',
            '11' => '10-19 salariés',
            '12' => '20-49 salariés',
            '21' => '50-99 salariés',
            '22' => '100-199 salariés',
            '31' => '200-249 salariés',
            '32' => '250-499 salariés',
            '41' => '500-999 salariés',
            '42' => '1000-1999 salariés',
            '51' => '2000-4999 salariés',
            '52' => '5000-9999 salariés',
            '53' => '10000+ salariés',
        ];

        $effCode = (string) ($entry['tranche_effectif_salarie'] ?? '');
        $effLabel = $effectifs[$effCode] ?? null;

        // Codes nature juridique INSEE (préfixes utiles)
        $naturePrefix = function (string $code): string {
            if ($code === '') return 'inconnu';
            $p1 = $code[0];
            return match (true) {
                str_starts_with($code, '92') => 'Association',
                $p1 === '1' => 'Personne physique',
                $p1 === '5' => 'Société commerciale',
                str_starts_with($code, '54') => 'SARL',
                str_starts_with($code, '55') => 'SA',
                str_starts_with($code, '56') => 'SAS',
                str_starts_with($code, '6') => 'Personne morale spéciale',
                str_starts_with($code, '7') => 'Administration publique',
                default => 'Autre',
            };
        };

        $natureCode = (string) ($entry['nature_juridique'] ?? '');

        $dirigeants = [];
        foreach (($entry['dirigeants'] ?? []) as $d) {
            $dirigeants[] = [
                'nom' => $d['nom'] ?? null,
                'prenoms' => $d['prenoms'] ?? null,
                'qualite' => $d['qualite'] ?? null,
            ];
        }

        return [
            'siren' => $entry['siren'] ?? null,
            'siret' => $siege['siret'] ?? null,
            'nom' => $entry['nom_complet'] ?? $entry['nom_raison_sociale'] ?? null,
            'naf' => $entry['activite_principale'] ?? null,
            'nature_juridique_code' => $natureCode,
            'nature_juridique' => $naturePrefix($natureCode),
            'date_creation' => $entry['date_creation'] ?? null,
            'effectif_code' => $effCode,
            'effectif' => $effLabel,
            'categorie_entreprise' => $entry['categorie_entreprise'] ?? null,
            'etat_administratif' => $entry['etat_administratif'] ?? null,
            'caractere_employeur' => $entry['caractere_employeur'] ?? ($siege['caractere_employeur'] ?? null),
            'latitude' => isset($siege['latitude']) ? (float) $siege['latitude'] : null,
            'longitude' => isset($siege['longitude']) ? (float) $siege['longitude'] : null,
            'dirigeants' => $dirigeants,
        ];
    }
}
