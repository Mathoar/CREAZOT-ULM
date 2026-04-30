<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

use App\Kernel;

$kernel = new Kernel('prod', false);
$kernel->boot();
$conn = $kernel->getContainer()->get('doctrine')->getConnection();

echo "=== Migration: Ajout needsEncadrant sur Nature ===\n";

// 1. Ajouter la colonne si elle n'existe pas
$cols = $conn->executeQuery("SELECT column_name FROM information_schema.columns WHERE table_name = 'nature' AND column_name = 'needs_encadrant'")->fetchAllAssociative();

if (empty($cols)) {
    $conn->executeStatement("ALTER TABLE nature ADD COLUMN needs_encadrant BOOLEAN NOT NULL DEFAULT false");
    echo "Colonne needs_encadrant ajoutée sur nature.\n";
} else {
    echo "Colonne needs_encadrant existe déjà.\n";
}

// 2. Migrer : si des circuits avaient needsEncadrant = true, marquer leur nature
$result = $conn->executeQuery("
    SELECT DISTINCT c.nature_id
    FROM circuit c
    WHERE c.needs_encadrant = true
    AND c.nature_id IS NOT NULL
")->fetchAllAssociative();

$natureIds = array_column($result, 'nature_id');

if (!empty($natureIds)) {
    $placeholders = implode(',', $natureIds);
    $conn->executeStatement("UPDATE nature SET needs_encadrant = true WHERE id IN ($placeholders)");
    echo "Natures mises à jour avec needsEncadrant = true : " . implode(', ', $natureIds) . "\n";
} else {
    echo "Aucune nature à migrer (pas de circuit avec needsEncadrant = true).\n";
}

// 3. Vérification
$natures = $conn->executeQuery("SELECT id, code, label, needs_encadrant FROM nature ORDER BY id")->fetchAllAssociative();
echo "\nÉtat final des natures :\n";
foreach ($natures as $n) {
    $enc = $n['needs_encadrant'] ? 'OUI' : 'non';
    echo "  [{$n['id']}] {$n['code']} - {$n['label']} => encadrant: $enc\n";
}

echo "\n=== Migration terminée ===\n";
