<?php
/**
 * Test du flux Odoo complet — à exécuter via bin/console
 * Usage: php tests/test_odoo_flow.php
 */

require dirname(__DIR__) . '/vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;

$dotenv = new Dotenv();
$dotenv->loadEnv(dirname(__DIR__) . '/.env');

$kernel = new \App\Kernel($_SERVER['APP_ENV'] ?? 'prod', (bool) ($_SERVER['APP_DEBUG'] ?? false));
$kernel->boot();
$container = $kernel->getContainer();

$odoo = $container->get('App\Service\OdooJsonRpcService');
$settingsRepo = $container->get('App\Repository\SiteSettingsRepository');

$settings = $settingsRepo->findInstance();

echo "\n";
echo "══════════════════════════════════════════════\n";
echo "   TEST DU FLUX ODOO — " . date('Y-m-d H:i:s') . "\n";
echo "══════════════════════════════════════════════\n\n";

// ── TEST 1 : Connexion ──
echo "── TEST 1 : Test de connexion ──\n";
try {
    $result = $odoo->testConnection(
        $settings->getOdooUrl(),
        $settings->getOdooBdd(),
        $settings->getOdooUser(),
        $settings->getOdooApiKey()
    );
    if ($result['success']) {
        echo "  ✓ " . $result['message'] . "\n";
    } else {
        echo "  ✗ " . $result['message'] . "\n";
        echo "\n⛔ Connexion échouée — arrêt des tests.\n";
        exit(1);
    }
} catch (\Throwable $e) {
    echo "  ✗ Exception : " . $e->getMessage() . "\n";
    exit(1);
}

// ── TEST 2 : Authentification via le service ──
echo "\n── TEST 2 : Authentification service (authenticate) ──\n";
try {
    $uid = $odoo->authenticate();
    echo "  ✓ Authentifié avec UID: $uid\n";
} catch (\Throwable $e) {
    echo "  ✗ " . $e->getMessage() . "\n";
    exit(1);
}

// ── TEST 3 : Création d'un partner test ──
echo "\n── TEST 3 : Création d'un partner test ──\n";
$testPartnerData = [
    'name' => 'TEST C6L — ' . date('His'),
    'email' => 'test-c6l-' . time() . '@test.local',
    'phone' => '0262000000',
    'customer_rank' => 1,
    'is_company' => true,
    'comment' => 'Créé automatiquement par le test C6L — à supprimer',
];

$partnerId = null;
try {
    $partnerId = $odoo->createPartner($testPartnerData);
    echo "  ✓ Partner créé — Odoo ID: $partnerId\n";
} catch (\Throwable $e) {
    echo "  ✗ " . $e->getMessage() . "\n";
    echo "  ⚠ Suite des tests annulée (dépendance partner).\n";
    exit(1);
}

// ── TEST 4 : Mise à jour du partner ──
echo "\n── TEST 4 : Mise à jour du partner ──\n";
try {
    $ok = $odoo->updatePartner($partnerId, ['phone' => '0262999999', 'street' => '123 Rue du Test']);
    echo "  ✓ Partner $partnerId mis à jour (phone + street)\n";
} catch (\Throwable $e) {
    echo "  ✗ " . $e->getMessage() . "\n";
}

// ── TEST 5 : Création d'une facture brouillon ──
echo "\n── TEST 5 : Création d'une facture brouillon ──\n";
$lines = [
    ['name' => 'TEST — Forfait Logic\'Ciel Mensuel', 'quantity' => 1, 'price_unit' => 49.90],
    ['name' => 'TEST — Module Avancé', 'quantity' => 1, 'price_unit' => 19.90],
];

$invoiceId = null;
try {
    $invoiceId = $odoo->createInvoice($partnerId, $lines, 2, 'TEST C6L Flow');
    echo "  ✓ Facture créée — Odoo ID: $invoiceId\n";
} catch (\Throwable $e) {
    echo "  ✗ " . $e->getMessage() . "\n";
}

// ── TEST 6 : Lecture de la facture ──
echo "\n── TEST 6 : Lecture de la facture ──\n";
if ($invoiceId) {
    try {
        $invoice = $odoo->getInvoice($invoiceId);
        if ($invoice) {
            echo "  ✓ Facture lue :\n";
            echo "    - Référence    : " . ($invoice['name'] ?? 'N/A') . "\n";
            echo "    - Montant total: " . ($invoice['amount_total'] ?? 'N/A') . " €\n";
            echo "    - État         : " . ($invoice['state'] ?? 'N/A') . "\n";
            echo "    - Paiement     : " . ($invoice['payment_state'] ?? 'N/A') . "\n";
        } else {
            echo "  ✗ Facture non trouvée\n";
        }
    } catch (\Throwable $e) {
        echo "  ✗ " . $e->getMessage() . "\n";
    }
} else {
    echo "  ⏭ Skipped (pas de facture créée)\n";
}

// ── TEST 7 : Validation de la facture ──
echo "\n── TEST 7 : Validation de la facture ──\n";
if ($invoiceId) {
    try {
        $odoo->validateInvoice($invoiceId);
        echo "  ✓ Facture $invoiceId validée (postée)\n";
    } catch (\Throwable $e) {
        echo "  ✗ " . $e->getMessage() . "\n";
    }
} else {
    echo "  ⏭ Skipped\n";
}

// ── TEST 8 : Relecture après validation ──
echo "\n── TEST 8 : Relecture facture après validation ──\n";
if ($invoiceId) {
    try {
        $invoice = $odoo->getInvoice($invoiceId);
        if ($invoice) {
            echo "  ✓ État après validation :\n";
            echo "    - État     : " . ($invoice['state'] ?? 'N/A') . "\n";
            echo "    - Paiement : " . ($invoice['payment_state'] ?? 'N/A') . "\n";
            echo "    - Montant  : " . ($invoice['amount_total'] ?? 'N/A') . " €\n";
            echo "    - Restant  : " . ($invoice['amount_residual'] ?? 'N/A') . " €\n";
        }
    } catch (\Throwable $e) {
        echo "  ✗ " . $e->getMessage() . "\n";
    }
}

// ── TEST 9 : Récupération factures du partner ──
echo "\n── TEST 9 : Factures du partner test ──\n";
try {
    $invoices = $odoo->getPartnerInvoices($partnerId);
    echo "  ✓ " . count($invoices) . " facture(s) trouvée(s) pour le partner $partnerId\n";
    foreach ($invoices as $inv) {
        echo "    - " . ($inv['name'] ?? '?') . " | " . ($inv['amount_total'] ?? '?') . "€ | " . ($inv['state'] ?? '?') . "\n";
    }
} catch (\Throwable $e) {
    echo "  ✗ " . $e->getMessage() . "\n";
}

// ── TEST 10 : Récupération factures impayées ──
echo "\n── TEST 10 : Factures impayées (+30j) ──\n";
try {
    $overdue = $odoo->getOverdueInvoices(30);
    echo "  ✓ " . count($overdue) . " facture(s) impayée(s) depuis +30 jours\n";
    foreach (array_slice($overdue, 0, 3) as $inv) {
        $pName = is_array($inv['partner_id'] ?? null) ? $inv['partner_id'][1] : ($inv['partner_id'] ?? '?');
        echo "    - " . ($inv['name'] ?? '?') . " | $pName | " . ($inv['amount_residual'] ?? '?') . "€ dû\n";
    }
    if (count($overdue) > 3) {
        echo "    ... et " . (count($overdue) - 3) . " autre(s)\n";
    }
} catch (\Throwable $e) {
    echo "  ✗ " . $e->getMessage() . "\n";
}

// ── NETTOYAGE ──
echo "\n── NETTOYAGE ──\n";
echo "  ⚠ Le partner test (ID: $partnerId) et la facture test";
if ($invoiceId) echo " (ID: $invoiceId)";
echo " ont été créés dans Odoo.\n";
echo "  → Supprimez-les manuellement dans Odoo si nécessaire.\n";
echo "    Partner: '{$testPartnerData['name']}'\n";

echo "\n══════════════════════════════════════════════\n";
echo "   FIN DES TESTS\n";
echo "══════════════════════════════════════════════\n\n";
