<?php

require dirname(__DIR__) . '/vendor/autoload.php';

use App\Kernel;

$kernel = new Kernel('prod', false);
$kernel->boot();
$conn = $kernel->getContainer()->get('doctrine')->getManager()->getConnection();

$conn->executeStatement('ALTER TABLE aeronef ADD COLUMN IF NOT EXISTS modele VARCHAR(255) DEFAULT NULL');
$conn->executeStatement('ALTER TABLE aeronef ADD COLUMN IF NOT EXISTS immatriculation_complete VARCHAR(20) DEFAULT NULL');

echo "Done: added modele and immatriculation_complete columns to aeronef.\n";
