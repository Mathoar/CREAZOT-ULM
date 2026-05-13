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

/**
 * Calcule le score "Acheteur idéal" pour chaque structure FFPLUM en agrégeant :
 *  - Données FFPLUM brutes (ffplum-full.json)
 *  - Annuaire instructeurs FFPLUM (instr-full.json) — nb instructeurs + spécialités
 *  - Enrichissement SIRENE (sirene-full.json) — SIRET, NAF, effectif, statut
 *  - Status sites web (websites-status.json) — vivant/mort, mots-clés, concurrents
 *
 * Score 0-100, composé de :
 *  - Taille effective (instructeurs)         max 25
 *  - Présence digitale (site/email/tel)      max 20
 *  - Maturité juridique (SIRENE)             max 20
 *  - Signaux d'achat (mots-clés site)        max 20
 *  - Bonus stratégiques                      max 15
 */
#[AsCommand(
    name: 'app:prospects:build-score',
    description: 'Agrège FFPLUM + instructeurs + SIRENE + sites web en un dataset unifié avec score Acheteur',
)]
class BuildProspectScoreCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addOption('structures', null, InputOption::VALUE_REQUIRED, 'JSON FFPLUM structures', 'var/prospects/ffplum-full.json')
            ->addOption('instructors', null, InputOption::VALUE_REQUIRED, 'JSON instructeurs', 'var/prospects/instr-full.json')
            ->addOption('sirene', null, InputOption::VALUE_REQUIRED, 'JSON SIRENE', 'var/prospects/sirene-full.json')
            ->addOption('websites', null, InputOption::VALUE_REQUIRED, 'JSON sites web', 'var/prospects/websites-status.json')
            ->addOption('output', null, InputOption::VALUE_REQUIRED, 'JSON enrichi en sortie', 'var/prospects/prospects-scored.json');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $structures = $this->load((string) $input->getOption('structures'));
        if ($structures === null) {
            $io->error('Structures introuvables.');
            return Command::INVALID;
        }

        $instructors = $this->load((string) $input->getOption('instructors')) ?? [];
        $sirene = $this->load((string) $input->getOption('sirene')) ?? [];
        $websites = $this->load((string) $input->getOption('websites')) ?? [];

        // Index instructeurs par dépt + matching de terrain
        $instructorsByDept = [];
        foreach ($instructors as $i) {
            $instructorsByDept[$i['dept']][] = $i;
        }

        // Index sirene/websites par code
        $sireneByCode = [];
        foreach ($sirene as $row) {
            if (!empty($row['code'])) {
                $sireneByCode[$row['code']] = $row;
            }
        }
        $websitesByCode = [];
        foreach ($websites as $row) {
            if (!empty($row['code'])) {
                $websitesByCode[$row['code']] = $row;
            }
        }

        $io->title(sprintf('Calcul du score sur %d structures', count($structures)));

        $enriched = [];
        $scoreSum = 0;
        $buckets = ['Excellent (80+)' => 0, 'Bon (60-79)' => 0, 'Moyen (40-59)' => 0, 'Faible (<40)' => 0];
        $competitorHits = [];

        foreach ($structures as $s) {
            $code = $s['code'];

            $matchedInstrs = $this->matchInstructors($s, $instructorsByDept);
            $instructorCount = count($matchedInstrs);
            $practices = $this->aggregatePractices($matchedInstrs);

            $sireneRow = $sireneByCode[$code] ?? null;
            $webRow = $websitesByCode[$code] ?? null;

            $score = $this->computeScore($s, $instructorCount, $practices, $sireneRow, $webRow);
            $scoreSum += $score['total'];

            if ($score['total'] >= 80) {
                $buckets['Excellent (80+)']++;
            } elseif ($score['total'] >= 60) {
                $buckets['Bon (60-79)']++;
            } elseif ($score['total'] >= 40) {
                $buckets['Moyen (40-59)']++;
            } else {
                $buckets['Faible (<40)']++;
            }

            if (!empty($webRow['competitors'])) {
                foreach ($webRow['competitors'] as $c) {
                    $competitorHits[$c] = ($competitorHits[$c] ?? 0) + 1;
                }
            }

            $enriched[] = [
                ...$s,
                'instructor_count' => $instructorCount,
                'practices' => $practices,
                'sirene' => $sireneRow !== null && ($sireneRow['matched'] ?? false) ? [
                    'siret' => $sireneRow['siret'] ?? null,
                    'naf' => $sireneRow['naf'] ?? null,
                    'nature' => $sireneRow['nature_juridique'] ?? null,
                    'effectif' => $sireneRow['effectif'] ?? null,
                    'employer' => $sireneRow['caractere_employeur'] ?? null,
                    'date_creation' => $sireneRow['date_creation'] ?? null,
                    'lat' => $sireneRow['latitude'] ?? null,
                    'lon' => $sireneRow['longitude'] ?? null,
                ] : null,
                'web' => $webRow !== null ? [
                    'alive' => $webRow['alive'] ?? false,
                    'https' => $webRow['https'] ?? null,
                    'title' => $webRow['title'] ?? null,
                    'keywords' => $webRow['keywords'] ?? [],
                    'competitors' => $webRow['competitors'] ?? [],
                ] : null,
                'score' => $score['total'],
                'score_breakdown' => $score['breakdown'],
                'tags' => $this->buildTags($s, $instructorCount, $practices, $sireneRow, $webRow, $score['total']),
            ];
        }

        usort($enriched, fn ($a, $b) => ($b['score'] ?? 0) <=> ($a['score'] ?? 0));

        $fs = new Filesystem();
        $outputPath = (string) $input->getOption('output');
        $fs->mkdir(dirname($outputPath));
        $json = json_encode($enriched, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $fs->dumpFile($outputPath, $json !== false ? $json : '[]');

        $io->section('Distribution du score');
        $rows = [];
        foreach ($buckets as $k => $v) {
            $rows[] = [$k, $v];
        }
        $io->table(['Bucket', 'Structures'], $rows);

        $io->text(sprintf('Score moyen : <comment>%.1f / 100</comment>', $scoreSum / max(1, count($enriched))));

        if ($competitorHits !== []) {
            $io->section('Outils concurrents détectés');
            $rows = [];
            arsort($competitorHits);
            foreach ($competitorHits as $name => $count) {
                $rows[] = [$name, $count];
            }
            $io->table(['Outil', 'Sites'], $rows);
        }

        $io->section('Top 15 prospects (score le plus élevé)');
        $rows = [];
        foreach (array_slice($enriched, 0, 15) as $p) {
            $rows[] = [
                $p['score'],
                $p['code'],
                substr($p['name'], 0, 35),
                substr($p['city'] ?? '', 0, 20),
                $p['instructor_count'],
                $p['sirene']['nature'] ?? '—',
                $p['sirene']['effectif'] ?? '—',
                $p['web']['alive'] ?? false ? '✓' : '—',
            ];
        }
        $io->table(
            ['Score', 'Code', 'Nom', 'Ville', 'Instr.', 'Statut', 'Effectif', 'Site OK'],
            $rows,
        );

        $io->success(sprintf('JSON écrit : %s (%d structures)', $outputPath, count($enriched)));

        return Command::SUCCESS;
    }

    /**
     * @return array<int, array<string, mixed>>|null
     */
    private function load(string $path): ?array
    {
        if (!is_file($path)) {
            return null;
        }
        $data = json_decode((string) file_get_contents($path), true);
        return is_array($data) ? $data : null;
    }

    /**
     * Match les instructeurs d'un département à une structure via similarité de nom de terrain.
     *
     * @param array<string, mixed> $structure
     * @param array<string, array<int, array<string, mixed>>> $instructorsByDept
     * @return array<int, array<string, mixed>>
     */
    private function matchInstructors(array $structure, array $instructorsByDept): array
    {
        $dept = $structure['dept'] ?? null;
        if ($dept === null || !isset($instructorsByDept[$dept])) {
            return [];
        }

        $clubName = $this->normalizeForMatch((string) ($structure['name'] ?? ''));
        if ($clubName === '') {
            return [];
        }
        // Tokens significatifs (>2 chars, hors mots vides)
        $stop = ['de', 'du', 'la', 'le', 'les', 'des', 'et', 'ulm', 'aero', 'aeroclub', 'club', 'association', 'sarl', 'sas'];
        $clubTokens = array_filter(
            preg_split('/\s+/', $clubName) ?: [],
            fn (string $t) => mb_strlen($t) > 2 && !in_array($t, $stop, true),
        );
        if ($clubTokens === []) {
            $clubTokens = array_filter(
                preg_split('/\s+/', $clubName) ?: [],
                fn (string $t) => mb_strlen($t) > 1,
            );
        }

        $matches = [];
        foreach ($instructorsByDept[$dept] as $i) {
            foreach ($i['terrains'] ?? [] as $t) {
                $terrainName = $this->normalizeForMatch((string) ($t['name'] ?? ''));
                if ($terrainName === '') {
                    continue;
                }
                similar_text($clubName, $terrainName, $sim);

                // Match si forte similarité OU si tous les tokens du club sont dans le terrain
                $tokenMatch = true;
                foreach ($clubTokens as $tok) {
                    if (!str_contains($terrainName, $tok)) {
                        $tokenMatch = false;
                        break;
                    }
                }

                if ($sim >= 65 || ($tokenMatch && count($clubTokens) >= 1)) {
                    $matches[] = $i;
                    break;
                }
            }
        }

        return $matches;
    }

    private function normalizeForMatch(string $s): string
    {
        $s = transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', $s);
        $s = (string) preg_replace('/[^a-z0-9 ]+/u', ' ', $s);
        return trim((string) preg_replace('/\s+/u', ' ', $s));
    }

    /**
     * @param array<int, array<string, mixed>> $instructors
     * @return array<int, string>
     */
    private function aggregatePractices(array $instructors): array
    {
        $set = [];
        foreach ($instructors as $i) {
            foreach ($i['practices'] ?? [] as $p) {
                $set[$p] = true;
            }
        }
        return array_keys($set);
    }

    /**
     * @param array<string, mixed> $structure
     * @param array<int, string> $practices
     * @param array<string, mixed>|null $sirene
     * @param array<string, mixed>|null $web
     * @return array{total: int, breakdown: array<string, int>}
     */
    private function computeScore(array $structure, int $instructorCount, array $practices, ?array $sirene, ?array $web): array
    {
        $b = [];

        // === Taille effective (max 25) ===
        // 5 par instructeur jusqu'à 5 instructeurs
        $b['size'] = min(25, $instructorCount * 5);

        // === Présence digitale (max 20) ===
        $digital = 0;
        if (!empty($structure['email'])) $digital += 6;
        if (!empty($structure['phone'])) $digital += 4;
        if (!empty($structure['website'])) $digital += 4;
        if ($web !== null && ($web['alive'] ?? false)) $digital += 6;
        $b['digital'] = min(20, $digital);

        // === Maturité juridique / fiscale (max 20) ===
        $juridical = 0;
        if ($sirene !== null && ($sirene['matched'] ?? false)) {
            $juridical += 5; // existe au SIRENE
            $effCode = $sirene['effectif_code'] ?? '';
            if ($effCode !== '' && $effCode !== 'NN' && $effCode !== '00') {
                $juridical += 8; // a au moins 1 salarié
            }
            $employer = $sirene['caractere_employeur'] ?? null;
            if ($employer === 'O') {
                $juridical += 3;
            }
            $naf = (string) ($sirene['naf'] ?? '');
            if (str_starts_with($naf, '85.51') || str_starts_with($naf, '85.32')) {
                $juridical += 4; // enseignement = idéal
            }
        }
        $b['juridical'] = min(20, $juridical);

        // === Signaux d'achat (max 20) ===
        $buy = 0;
        if ($web !== null && !empty($web['keywords'])) {
            $kw = $web['keywords'];
            // Mot-clé "ecole" ou "stage" = école de formation = besoin de gestion
            if (in_array('ecole', $kw, true)) $buy += 6;
            if (in_array('stage', $kw, true)) $buy += 3;
            if (in_array('reservation', $kw, true)) $buy += 4;
            if (in_array('tarifs', $kw, true)) $buy += 3;
            if (in_array('planning', $kw, true)) $buy += 3;
            if (in_array('instructeurs', $kw, true)) $buy += 2;
            if (in_array('bapteme', $kw, true)) $buy += 2;
        }
        $b['buy_signals'] = min(20, $buy);

        // === Bonus stratégiques (max 15) ===
        $bonus = 0;
        if ($web !== null && !empty($web['competitors'])) {
            $bonus += 8; // déjà un outil concurrent → cible de switch
        }
        // Email custom (pas mainstream)
        if (!empty($structure['email'])) {
            $domain = strtolower(substr((string) strstr((string) $structure['email'], '@'), 1));
            $mainstream = ['gmail.com','yahoo.fr','yahoo.com','hotmail.fr','hotmail.com','outlook.fr','outlook.com','live.fr','aol.com','laposte.net','sfr.fr','orange.fr','wanadoo.fr','free.fr'];
            if ($domain !== '' && !in_array($domain, $mainstream, true)) {
                $bonus += 4;
            }
        }
        // Plusieurs spécialités (école polyvalente)
        if (count($practices) >= 2) {
            $bonus += 3;
        }
        $b['bonus'] = min(15, $bonus);

        $total = array_sum($b);
        return ['total' => min(100, $total), 'breakdown' => $b];
    }

    /**
     * @param array<string, mixed> $s
     * @param array<int, string> $practices
     * @param array<string, mixed>|null $sirene
     * @param array<string, mixed>|null $web
     * @return array<int, string>
     */
    private function buildTags(array $s, int $instructorCount, array $practices, ?array $sirene, ?array $web, int $score): array
    {
        $tags = [];
        if ($score >= 80) $tags[] = 'top-prospect';
        elseif ($score >= 60) $tags[] = 'qualified';

        if ($instructorCount >= 5) $tags[] = 'big-team';
        elseif ($instructorCount >= 3) $tags[] = 'med-team';
        elseif ($instructorCount === 0) $tags[] = 'no-instructor';

        foreach ($practices as $p) {
            $tags[] = 'practice:' . strtolower(str_replace(' ', '-', $p));
        }

        if ($sirene !== null && ($sirene['matched'] ?? false)) {
            $tags[] = 'sirene-ok';
            if (!empty($sirene['siret'])) $tags[] = 'siret-known';
            $effCode = $sirene['effectif_code'] ?? '';
            if ($effCode !== '' && $effCode !== 'NN' && $effCode !== '00') {
                $tags[] = 'has-employees';
            }
            $naf = (string) ($sirene['naf'] ?? '');
            if (str_starts_with($naf, '85.51') || str_starts_with($naf, '85.32')) {
                $tags[] = 'ecole-officielle';
            }
            if (($sirene['nature_juridique'] ?? '') === 'SARL' || ($sirene['nature_juridique'] ?? '') === 'SAS') {
                $tags[] = 'commerciale';
            }
        }

        if ($web !== null) {
            if (($web['alive'] ?? false) === true) $tags[] = 'web-alive';
            else $tags[] = 'web-dead';
            if (!empty($web['competitors'])) {
                $tags[] = 'has-competitor';
                foreach ($web['competitors'] as $c) {
                    $tags[] = 'competitor:' . $c;
                }
            }
            if (!empty($web['keywords']) && in_array('ecole', $web['keywords'], true)) {
                $tags[] = 'mention-ecole';
            }
        }

        return $tags;
    }
}
