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
    name: 'app:prospects:scrape-ffplum-instructors',
    description: 'Scrape l\'annuaire des instructeurs FFPLUM (par département) et exporte en JSON',
)]
class ScrapeFfplumInstructorsCommand extends Command
{
    public function __construct(
        private FfplumScraperService $scraper,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('dept', null, InputOption::VALUE_REQUIRED, 'Codes département (ex: "13,75,20")')
            ->addOption('all', null, InputOption::VALUE_NONE, 'Scraper tous les départements')
            ->addOption('json', null, InputOption::VALUE_REQUIRED, 'Chemin de sortie JSON')
            ->addOption('delay', null, InputOption::VALUE_REQUIRED, 'Délai entre requêtes (ms)', '600');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $all = (bool) $input->getOption('all');
        $deptsOpt = $input->getOption('dept');

        $depts = $all
            ? FfplumScraperService::DEPARTMENTS
            : array_values(array_filter(array_map(
                fn (string $d) => strtoupper(trim($d)),
                explode(',', (string) ($deptsOpt ?? '')),
            ), fn (string $d) => $d !== ''));

        if ($depts === []) {
            $io->error('Spécifiez --dept=XX,YY ou --all.');
            return Command::INVALID;
        }

        $delayMs = max(0, (int) $input->getOption('delay'));
        $jsonPath = $input->getOption('json');

        $io->title('FFPLUM — scrape annuaire des instructeurs');
        $io->text(sprintf('Départements : %d • Délai : %d ms', count($depts), $delayMs));

        $all = [];
        $perDept = [];

        $io->progressStart(count($depts));
        foreach ($depts as $dept) {
            $rows = $this->scraper->scrapeInstructorsDepartment($dept);
            $perDept[] = ['dept' => $dept, 'count' => count($rows)];
            foreach ($rows as $r) {
                $all[] = $r;
            }
            $io->progressAdvance();
            if ($delayMs > 0) {
                usleep($delayMs * 1000);
            }
        }
        $io->progressFinish();

        $totalPractices = [];
        foreach ($all as $i) {
            foreach ($i['practices'] as $p) {
                $totalPractices[$p] = ($totalPractices[$p] ?? 0) + 1;
            }
        }
        arsort($totalPractices);

        $io->section('Résumé');
        $io->table(
            ['Total instructeurs', 'Avec email', 'Avec terrain'],
            [[
                count($all),
                count(array_filter($all, fn ($i) => !empty($i['email']))),
                count(array_filter($all, fn ($i) => !empty($i['terrains']))),
            ]],
        );

        $io->section('Pratiques');
        $rows = [];
        foreach ($totalPractices as $p => $c) {
            $rows[] = [$p, $c];
        }
        $io->table(['Pratique', 'Nb instructeurs'], $rows);

        if ($jsonPath !== null && count($all) > 0) {
            $fs = new Filesystem();
            $fs->mkdir(dirname($jsonPath));
            $json = json_encode($all, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $fs->dumpFile($jsonPath, $json !== false ? $json : '[]');
            $io->success(sprintf('JSON écrit : %s (%d instructeurs)', $jsonPath, count($all)));
        }

        return Command::SUCCESS;
    }
}
