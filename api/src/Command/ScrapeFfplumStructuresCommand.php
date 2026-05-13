<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\FfplumScraperService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

#[AsCommand(
    name: 'app:prospects:scrape-ffplum',
    description: 'Scrape l\'annuaire des structures FFPLUM (clubs ULM) et exporte en CSV/JSON',
)]
class ScrapeFfplumStructuresCommand extends Command
{
    public function __construct(
        private FfplumScraperService $scraper,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'dept',
                null,
                InputOption::VALUE_REQUIRED,
                'Codes département à scraper (séparés par des virgules, ex: "13,75,2A,988")',
            )
            ->addOption(
                'all',
                null,
                InputOption::VALUE_NONE,
                'Scraper tous les départements connus (métropole + DOM-TOM)',
            )
            ->addOption(
                'csv',
                null,
                InputOption::VALUE_REQUIRED,
                'Chemin de sortie CSV (ex: var/prospects/ffplum.csv)',
            )
            ->addOption(
                'json',
                null,
                InputOption::VALUE_REQUIRED,
                'Chemin de sortie JSON (ex: var/prospects/ffplum.json)',
            )
            ->addOption(
                'limit',
                null,
                InputOption::VALUE_REQUIRED,
                'Limiter le nombre de structures par département (utile pour tester)',
            )
            ->addOption(
                'delay',
                null,
                InputOption::VALUE_REQUIRED,
                'Délai en millisecondes entre chaque requête (politesse, défaut: 600)',
                '600',
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $depts = $this->resolveDepartments($input, $io);
        if ($depts === null) {
            return Command::INVALID;
        }

        $limit = $input->getOption('limit') !== null ? max(0, (int) $input->getOption('limit')) : null;
        $delayMs = max(0, (int) $input->getOption('delay'));
        $csvPath = $input->getOption('csv');
        $jsonPath = $input->getOption('json');

        $io->title('FFPLUM — scrape annuaire des structures (clubs ULM)');
        $io->text(sprintf(
            'Départements à traiter : %d • Délai entre requêtes : %d ms%s',
            count($depts),
            $delayMs,
            $limit !== null ? " • Limite par dépt : {$limit}" : '',
        ));

        $allStructures = [];
        $perDeptStats = [];

        $io->progressStart(count($depts));

        foreach ($depts as $dept) {
            $structures = $this->scraper->scrapeDepartment($dept);

            if ($limit !== null && $limit > 0) {
                $structures = array_slice($structures, 0, $limit);
            }

            $count = count($structures);
            $withEmail = 0;
            $withPhone = 0;
            $withWebsite = 0;
            foreach ($structures as $s) {
                if (!empty($s['email'])) {
                    $withEmail++;
                }
                if (!empty($s['phone'])) {
                    $withPhone++;
                }
                if (!empty($s['website'])) {
                    $withWebsite++;
                }
            }

            $perDeptStats[] = [
                'dept' => $dept,
                'count' => $count,
                'with_email' => $withEmail,
                'with_phone' => $withPhone,
                'with_website' => $withWebsite,
            ];

            foreach ($structures as $s) {
                $allStructures[] = $s;
            }

            $io->progressAdvance();

            if ($delayMs > 0) {
                usleep($delayMs * 1000);
            }
        }

        $io->progressFinish();

        $totalCount = count($allStructures);
        $totalWithEmail = array_sum(array_column($perDeptStats, 'with_email'));
        $totalWithPhone = array_sum(array_column($perDeptStats, 'with_phone'));
        $totalWithWebsite = array_sum(array_column($perDeptStats, 'with_website'));

        $io->section('Résumé global');
        $io->table(
            ['Total structures', 'Avec email', 'Avec téléphone', 'Avec site web'],
            [[
                $totalCount,
                $this->ratio($totalWithEmail, $totalCount),
                $this->ratio($totalWithPhone, $totalCount),
                $this->ratio($totalWithWebsite, $totalCount),
            ]],
        );

        if (count($depts) > 1) {
            $io->section('Détail par département');
            $rows = [];
            foreach ($perDeptStats as $s) {
                $rows[] = [
                    $s['dept'],
                    $s['count'],
                    $s['with_email'],
                    $s['with_phone'],
                    $s['with_website'],
                ];
            }
            $io->table(['Dépt', 'Structures', 'Email', 'Tél', 'Site'], $rows);
        }

        if ($totalCount === 0) {
            $io->warning('Aucune structure trouvée. Aucun fichier de sortie n\'est écrit.');

            return Command::SUCCESS;
        }

        $fs = new Filesystem();

        if ($jsonPath !== null) {
            $this->writeJson($fs, $jsonPath, $allStructures);
            $io->success(sprintf('JSON écrit : %s (%d structures)', $jsonPath, $totalCount));
        }

        if ($csvPath !== null) {
            $this->writeCsv($fs, $csvPath, $allStructures);
            $io->success(sprintf('CSV écrit : %s (%d structures)', $csvPath, $totalCount));
        }

        if ($jsonPath === null && $csvPath === null) {
            $io->note('Aucun fichier de sortie spécifié (--csv / --json). Voici les 5 premiers résultats :');
            $sample = array_slice($allStructures, 0, 5);
            $io->writeln(json_encode($sample, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '');
        }

        return Command::SUCCESS;
    }

    /**
     * @return string[]|null
     */
    private function resolveDepartments(InputInterface $input, SymfonyStyle $io): ?array
    {
        $all = (bool) $input->getOption('all');
        $deptsOption = $input->getOption('dept');

        if ($all && $deptsOption !== null) {
            $io->error('--all et --dept sont mutuellement exclusifs.');

            return null;
        }

        if ($all) {
            return FfplumScraperService::DEPARTMENTS;
        }

        if ($deptsOption === null) {
            $io->error('Vous devez spécifier --dept=XX,YY ou --all.');

            return null;
        }

        $depts = array_values(array_filter(array_map(
            fn (string $d) => strtoupper(trim($d)),
            explode(',', (string) $deptsOption),
        ), fn (string $d) => $d !== ''));

        if ($depts === []) {
            $io->error('Aucun département valide dans --dept.');

            return null;
        }

        return $depts;
    }

    /**
     * @param array<int, array<string, mixed>> $structures
     */
    private function writeCsv(Filesystem $fs, string $path, array $structures): void
    {
        $fs->mkdir(dirname($path));

        $fp = fopen($path, 'w');
        if ($fp === false) {
            throw new \RuntimeException(sprintf('Impossible d\'ouvrir %s en écriture', $path));
        }

        // BOM UTF-8 pour ouverture propre dans Excel
        fwrite($fp, "\xEF\xBB\xBF");

        $headers = [
            'dept', 'code', 'name', 'address', 'zip', 'city',
            'president', 'phone', 'email', 'website',
            'source_url', 'scraped_at',
        ];
        fputcsv($fp, $headers, ',', '"', '\\');

        foreach ($structures as $s) {
            $row = [];
            foreach ($headers as $h) {
                $row[] = (string) ($s[$h] ?? '');
            }
            fputcsv($fp, $row, ',', '"', '\\');
        }

        fclose($fp);
    }

    /**
     * @param array<int, array<string, mixed>> $structures
     */
    private function writeJson(Filesystem $fs, string $path, array $structures): void
    {
        $fs->mkdir(dirname($path));

        $json = json_encode(
            $structures,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
        );
        if ($json === false) {
            throw new \RuntimeException('Impossible de sérialiser les structures en JSON.');
        }

        $fs->dumpFile($path, $json);
    }

    private function ratio(int $part, int $total): string
    {
        if ($total === 0) {
            return '0';
        }

        return sprintf('%d (%d%%)', $part, (int) round($part / $total * 100));
    }
}
