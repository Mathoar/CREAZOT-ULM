<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\OdooJsonRpcService;
use App\Repository\SiteSettingsRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:test:odoo-flow', description: 'Test complet du flux Odoo')]
class TestOdooFlowCommand extends Command
{
    public function __construct(
        private readonly OdooJsonRpcService $odoo,
        private readonly SiteSettingsRepository $settingsRepo,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $settings = $this->settingsRepo->findInstance();
        $passed = 0;
        $failed = 0;

        $output->writeln('');
        $output->writeln('<info>== TEST DU FLUX ODOO == ' . date('Y-m-d H:i:s') . '</info>');
        $output->writeln('');

        // TEST 1 : Connexion
        $output->writeln('<comment>-- TEST 1 : Connexion --</comment>');
        try {
            $r = $this->odoo->testConnection(
                $settings->getOdooUrl(),
                $settings->getOdooBdd(),
                $settings->getOdooUser(),
                $settings->getOdooApiKey()
            );
            if ($r['success']) {
                $output->writeln('  <info>OK</info> ' . $r['message']);
                $passed++;
            } else {
                $output->writeln('  <error>FAIL</error> ' . $r['message']);
                return Command::FAILURE;
            }
        } catch (\Throwable $e) {
            $output->writeln('  <error>FAIL</error> ' . $e->getMessage());
            return Command::FAILURE;
        }

        // TEST 2 : Authentification service
        $output->writeln('');
        $output->writeln('<comment>-- TEST 2 : Authentification service --</comment>');
        try {
            $uid = $this->odoo->authenticate();
            $output->writeln("  <info>OK</info> UID: $uid");
            $passed++;
        } catch (\Throwable $e) {
            $output->writeln('  <error>FAIL</error> ' . $e->getMessage());
            $failed++;
            return Command::FAILURE;
        }

        // TEST 3 : Création partner
        $output->writeln('');
        $output->writeln('<comment>-- TEST 3 : Creation partner test --</comment>');
        $testName = 'TEST C6L ' . date('His');
        $partnerId = null;
        try {
            $partnerId = $this->odoo->createPartner([
                'name' => $testName,
                'email' => 'test-' . time() . '@test.local',
                'phone' => '0262000000',
                'customer_rank' => 1,
                'is_company' => true,
                'comment' => 'Test automatique C6L — a supprimer',
            ]);
            $output->writeln("  <info>OK</info> Partner cree, Odoo ID: $partnerId");
            $passed++;
        } catch (\Throwable $e) {
            $output->writeln('  <error>FAIL</error> ' . $e->getMessage());
            $failed++;
        }

        // TEST 4 : Update partner
        $output->writeln('');
        $output->writeln('<comment>-- TEST 4 : Update partner --</comment>');
        if ($partnerId) {
            try {
                $this->odoo->updatePartner($partnerId, [
                    'phone' => '0262999999',
                    'street' => '123 Rue du Test',
                ]);
                $output->writeln("  <info>OK</info> Partner $partnerId mis a jour");
                $passed++;
            } catch (\Throwable $e) {
                $output->writeln('  <error>FAIL</error> ' . $e->getMessage());
                $failed++;
            }
        } else {
            $output->writeln('  <comment>SKIP</comment>');
        }

        // TEST 5 : Création facture
        $output->writeln('');
        $output->writeln('<comment>-- TEST 5 : Creation facture brouillon --</comment>');
        $invoiceId = null;
        if ($partnerId) {
            try {
                $invoiceId = $this->odoo->createInvoice($partnerId, [
                    ['name' => 'TEST Forfait Mensuel', 'quantity' => 1, 'price_unit' => 49.90],
                    ['name' => 'TEST Module Avance', 'quantity' => 1, 'price_unit' => 19.90],
                ], 2, 'TEST C6L Flow');
                $output->writeln("  <info>OK</info> Facture creee, Odoo ID: $invoiceId");
                $passed++;
            } catch (\Throwable $e) {
                $output->writeln('  <error>FAIL</error> ' . $e->getMessage());
                $failed++;
            }
        } else {
            $output->writeln('  <comment>SKIP</comment>');
        }

        // TEST 6 : Lecture facture
        $output->writeln('');
        $output->writeln('<comment>-- TEST 6 : Lecture facture --</comment>');
        if ($invoiceId) {
            try {
                $inv = $this->odoo->getInvoice($invoiceId);
                if ($inv) {
                    $output->writeln('  <info>OK</info> Facture lue :');
                    $output->writeln('    Ref     : ' . ($inv['name'] ?? 'N/A'));
                    $output->writeln('    Total   : ' . ($inv['amount_total'] ?? 'N/A') . ' EUR');
                    $output->writeln('    Etat    : ' . ($inv['state'] ?? 'N/A'));
                    $output->writeln('    Paiement: ' . ($inv['payment_state'] ?? 'N/A'));
                    $passed++;
                } else {
                    $output->writeln('  <error>FAIL</error> Facture non trouvee');
                    $failed++;
                }
            } catch (\Throwable $e) {
                $output->writeln('  <error>FAIL</error> ' . $e->getMessage());
                $failed++;
            }
        } else {
            $output->writeln('  <comment>SKIP</comment>');
        }

        // TEST 7 : Validation facture
        $output->writeln('');
        $output->writeln('<comment>-- TEST 7 : Validation facture --</comment>');
        if ($invoiceId) {
            try {
                $this->odoo->validateInvoice($invoiceId);
                $output->writeln("  <info>OK</info> Facture $invoiceId validee");
                $passed++;
            } catch (\Throwable $e) {
                $output->writeln('  <error>FAIL</error> ' . $e->getMessage());
                $failed++;
            }
        } else {
            $output->writeln('  <comment>SKIP</comment>');
        }

        // TEST 8 : Relecture post-validation
        $output->writeln('');
        $output->writeln('<comment>-- TEST 8 : Relecture post-validation --</comment>');
        if ($invoiceId) {
            try {
                $inv = $this->odoo->getInvoice($invoiceId);
                if ($inv) {
                    $output->writeln('  <info>OK</info> Post-validation :');
                    $output->writeln('    Etat    : ' . ($inv['state'] ?? '?'));
                    $output->writeln('    Paiement: ' . ($inv['payment_state'] ?? '?'));
                    $output->writeln('    Total   : ' . ($inv['amount_total'] ?? '?') . ' EUR');
                    $output->writeln('    Restant : ' . ($inv['amount_residual'] ?? '?') . ' EUR');
                    $passed++;
                }
            } catch (\Throwable $e) {
                $output->writeln('  <error>FAIL</error> ' . $e->getMessage());
                $failed++;
            }
        } else {
            $output->writeln('  <comment>SKIP</comment>');
        }

        // TEST 9 : Factures du partner
        $output->writeln('');
        $output->writeln('<comment>-- TEST 9 : Factures du partner test --</comment>');
        if ($partnerId) {
            try {
                $invs = $this->odoo->getPartnerInvoices($partnerId);
                $output->writeln('  <info>OK</info> ' . count($invs) . ' facture(s)');
                foreach ($invs as $i) {
                    $output->writeln('    - ' . ($i['name'] ?? '?') . ' | ' . ($i['amount_total'] ?? '?') . ' EUR | ' . ($i['state'] ?? '?'));
                }
                $passed++;
            } catch (\Throwable $e) {
                $output->writeln('  <error>FAIL</error> ' . $e->getMessage());
                $failed++;
            }
        } else {
            $output->writeln('  <comment>SKIP</comment>');
        }

        // TEST 10 : Factures impayées
        $output->writeln('');
        $output->writeln('<comment>-- TEST 10 : Factures impayees (+30j) --</comment>');
        try {
            $overdue = $this->odoo->getOverdueInvoices(30);
            $output->writeln('  <info>OK</info> ' . count($overdue) . ' facture(s) impayee(s)');
            foreach (array_slice($overdue, 0, 3) as $i) {
                $pn = is_array($i['partner_id'] ?? null) ? $i['partner_id'][1] : ($i['partner_id'] ?? '?');
                $output->writeln('    - ' . ($i['name'] ?? '?') . ' | ' . $pn . ' | ' . ($i['amount_residual'] ?? '?') . ' EUR');
            }
            if (count($overdue) > 3) {
                $output->writeln('    ... et ' . (count($overdue) - 3) . ' autre(s)');
            }
            $passed++;
        } catch (\Throwable $e) {
            $output->writeln('  <error>FAIL</error> ' . $e->getMessage());
            $failed++;
        }

        // RÉSUMÉ
        $output->writeln('');
        $output->writeln(sprintf('<info>== RESULTATS : %d OK / %d FAIL ==</info>', $passed, $failed));
        if ($partnerId) {
            $output->writeln('');
            $output->writeln("<comment>  Nettoyage : partner '$testName' (ID: $partnerId)" . ($invoiceId ? " + facture (ID: $invoiceId)" : '') . '</comment>');
            $output->writeln('<comment>  A supprimer dans Odoo si necessaire.</comment>');
        }
        $output->writeln('');

        return $failed > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}
