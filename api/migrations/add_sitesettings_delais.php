<?php

require dirname(__DIR__).'/vendor/autoload.php';

$kernel = new \App\Kernel('prod', false);
$kernel->boot();
$conn = $kernel->getContainer()->get('doctrine')->getConnection();

$queries = [
    "ALTER TABLE site_settings ADD COLUMN IF NOT EXISTS delai_notification_dgac_heures INT NOT NULL DEFAULT 72",
    "ALTER TABLE site_settings ADD COLUMN IF NOT EXISTS delai_compte_rendu_suivi_jours INT NOT NULL DEFAULT 30",
];

foreach ($queries as $sql) {
    try {
        $conn->executeStatement($sql);
        echo "OK: " . $sql . "\n";
    } catch (\Throwable $e) {
        echo "SKIP/ERR: " . $e->getMessage() . "\n";
    }
}

echo "\nMigration terminée.\n";
