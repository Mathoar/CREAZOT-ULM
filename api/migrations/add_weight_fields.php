<?php

require dirname(__DIR__).'/vendor/autoload.php';

$kernel = new \App\Kernel('prod', false);
$kernel->boot();
$conn = $kernel->getContainer()->get('doctrine')->getConnection();

$queries = [
    "ALTER TABLE passager ADD COLUMN IF NOT EXISTS poids DOUBLE PRECISION DEFAULT NULL",
    "ALTER TABLE flight_rule ADD COLUMN IF NOT EXISTS poids_max_passager INT DEFAULT NULL",
    "ALTER TABLE client ADD COLUMN IF NOT EXISTS has_weight_collection BOOLEAN DEFAULT NULL",
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
