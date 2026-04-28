<?php

require dirname(__DIR__).'/vendor/autoload.php';

$kernel = new \App\Kernel('prod', false);
$kernel->boot();
$conn = $kernel->getContainer()->get('doctrine')->getConnection();

$queries = [
    // Aeronef: type balise
    "ALTER TABLE aeronef ADD COLUMN IF NOT EXISTS type_balise VARCHAR(255) DEFAULT NULL",

    // Aeronef: parachute
    "ALTER TABLE aeronef ADD COLUMN IF NOT EXISTS has_parachute BOOLEAN DEFAULT NULL",
    "ALTER TABLE aeronef ADD COLUMN IF NOT EXISTS date_reconditionnement_parachute DATE DEFAULT NULL",
    "ALTER TABLE aeronef ADD COLUMN IF NOT EXISTS periodicite_parachute_mois INT DEFAULT NULL",
    "ALTER TABLE aeronef ADD COLUMN IF NOT EXISTS alerte_parachute_envoyee BOOLEAN NOT NULL DEFAULT false",

    // Client: seuil alerte parachute
    "ALTER TABLE client ADD COLUMN IF NOT EXISTS seuil_alerte_parachute_jours INT DEFAULT 180",

    // Client: déclaration DGAC
    "ALTER TABLE client ADD COLUMN IF NOT EXISTS date_declaration_dgac DATE DEFAULT NULL",
    "ALTER TABLE client ADD COLUMN IF NOT EXISTS periodicite_declaration_mois INT DEFAULT 24",

    // SecurityEvent table
    "CREATE TABLE IF NOT EXISTS security_event (
        id SERIAL PRIMARY KEY,
        client_id INT DEFAULT NULL REFERENCES client(id),
        type VARCHAR(30) NOT NULL,
        description TEXT NOT NULL,
        date_evenement TIMESTAMP NOT NULL,
        pilote_id UUID NOT NULL REFERENCES \"user\"(id),
        aeronef_id INT DEFAULT NULL REFERENCES aeronef(id),
        prestation_id INT DEFAULT NULL REFERENCES prestation(id),
        date_notification_exploitant TIMESTAMP DEFAULT NULL,
        date_notification_dgac TIMESTAMP DEFAULT NULL,
        date_notification_bea TIMESTAMP DEFAULT NULL,
        status VARCHAR(20) NOT NULL DEFAULT 'ouvert',
        compte_rendu_suivi TEXT DEFAULT NULL,
        date_cloture TIMESTAMP DEFAULT NULL,
        created_at TIMESTAMP DEFAULT NULL,
        updated_at TIMESTAMP DEFAULT NULL
    )",
];

foreach ($queries as $sql) {
    try {
        $conn->executeStatement($sql);
        echo "OK: " . substr($sql, 0, 80) . "...\n";
    } catch (\Throwable $e) {
        echo "SKIP/ERR: " . $e->getMessage() . "\n";
    }
}

echo "\nMigration terminée.\n";
