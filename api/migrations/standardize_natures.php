<?php

require dirname(__DIR__).'/vendor/autoload.php';

$kernel = new \App\Kernel('prod', false);
$kernel->boot();
$conn = $kernel->getContainer()->get('doctrine')->getConnection();

echo "=== Standardisation des natures de vol ===\n\n";

// 1. Add is_particular_activity column
echo "1. Ajout colonne is_particular_activity...\n";
try {
    $conn->executeStatement("ALTER TABLE nature ADD COLUMN is_particular_activity BOOLEAN NOT NULL DEFAULT false");
    echo "   OK\n";
} catch (\Throwable $e) {
    echo "   Colonne déjà existante ou erreur: {$e->getMessage()}\n";
}

// 2. Transform N/A (id 6) into VLP
echo "\n2. Transformation N/A (id 6) → VLP...\n";
$conn->executeStatement("UPDATE nature SET code = 'VLP', label = 'Vol Local à titre Privé' WHERE id = 6");
echo "   OK\n";

// 3. Create new natures
echo "\n3. Création des nouvelles natures...\n";
$newNatures = [
    ['BAH', 'Vol Basse Hauteur', true],
    ['PVA', 'Prise de Vues Aériennes', true],
    ['VOG', 'Voltige', true],
    ['LAR', 'Largage', true],
    ['TRA', 'Tractage / Remorquage', true],
    ['FI',  'Formation Initiale', false],
    ['FC',  'Formation Continue', false],
];

foreach ($newNatures as [$code, $label, $isAP]) {
    $exists = $conn->fetchOne("SELECT id FROM nature WHERE code = ?", [$code]);
    if ($exists) {
        echo "   {$code} existe déjà (id {$exists}), skip\n";
    } else {
        $conn->executeStatement(
            "INSERT INTO nature (code, label, is_particular_activity) VALUES (?, ?, ?)",
            [$code, $label, $isAP ? 'true' : 'false']
        );
        $newId = $conn->fetchOne("SELECT id FROM nature WHERE code = ?", [$code]);
        echo "   {$code} créé (id {$newId})\n";
    }
}

// Get IDs for new natures
$idVLP = (int) $conn->fetchOne("SELECT id FROM nature WHERE code = 'VLP'");
$idFI  = (int) $conn->fetchOne("SELECT id FROM nature WHERE code = 'FI'");
$idFC  = (int) $conn->fetchOne("SELECT id FROM nature WHERE code = 'FC'");
$idPVA = (int) $conn->fetchOne("SELECT id FROM nature WHERE code = 'PVA'");
$idLAR = (int) $conn->fetchOne("SELECT id FROM nature WHERE code = 'LAR'");

echo "\n   IDs: VLP={$idVLP}, FI={$idFI}, FC={$idFC}, PVA={$idPVA}, LAR={$idLAR}\n";

// 4. Reassign AUTRE (id 5) carnets → VLP
echo "\n4. Réaffectation carnets AUTRE (id 5) → VLP...\n";
$nbAutre = $conn->executeStatement("UPDATE carnet_vol SET type_de_vol_id = ? WHERE type_de_vol_id = 5", [$idVLP]);
echo "   {$nbAutre} carnets réaffectés\n";

// 5. Reassign VEF (id 3) carnets → FI or FC based on qualifications
echo "\n5. Réaffectation carnets VEF (id 3) → FI/FC...\n";

// Pilots WITHOUT any qualification → FI
$nbFI = $conn->executeStatement(
    "UPDATE carnet_vol SET type_de_vol_id = ? WHERE type_de_vol_id = 3 AND profil_id NOT IN (SELECT DISTINCT profil_id FROM pilot_qualification)",
    [$idFI]
);
echo "   {$nbFI} carnets → FI (pilotes sans qualification)\n";

// Pilots WITH at least one qualification → FC
$nbFC = $conn->executeStatement(
    "UPDATE carnet_vol SET type_de_vol_id = ? WHERE type_de_vol_id = 3 AND profil_id IN (SELECT DISTINCT profil_id FROM pilot_qualification)",
    [$idFC]
);
echo "   {$nbFC} carnets → FC (pilotes qualifiés)\n";

// 6. Reassign VAPO (id 4) carnets → PVA
echo "\n6. Réaffectation carnets VAPO (id 4) → PVA...\n";
$nbVAPO = $conn->executeStatement("UPDATE carnet_vol SET type_de_vol_id = ? WHERE type_de_vol_id = 4", [$idPVA]);
echo "   {$nbVAPO} carnets réaffectés\n";

// 7. Reassign circuits
echo "\n7. Réaffectation des circuits...\n";

// AUTRE (id 5) circuits → VLP
$n = $conn->executeStatement("UPDATE circuit SET nature_id = ? WHERE nature_id = 5", [$idVLP]);
echo "   {$n} circuits AUTRE → VLP\n";

// VEF (id 3) circuits: Instruction & Initiation → FI, Entraînement → FC
$n = $conn->executeStatement("UPDATE circuit SET nature_id = ? WHERE nature_id = 3 AND nom IN ('Instruction', 'Initiation')", [$idFI]);
echo "   {$n} circuits (Instruction/Initiation) → FI\n";

$n = $conn->executeStatement("UPDATE circuit SET nature_id = ? WHERE nature_id = 3 AND nom = 'Entraînement'", [$idFC]);
echo "   {$n} circuit (Entraînement) → FC\n";

// Catch remaining VEF circuits
$remaining = $conn->executeStatement("UPDATE circuit SET nature_id = ? WHERE nature_id = 3", [$idFI]);
if ($remaining > 0) {
    echo "   {$remaining} circuits VEF restants → FI\n";
}

// VAPO (id 4) circuits
$n = $conn->executeStatement("UPDATE circuit SET nature_id = ? WHERE nature_id = 4 AND nom = 'Photos'", [$idPVA]);
echo "   {$n} circuit (Photos) → PVA\n";

$n = $conn->executeStatement("UPDATE circuit SET nature_id = ? WHERE nature_id = 4 AND nom = 'Largage Parachutiste'", [$idLAR]);
echo "   {$n} circuit (Largage Parachutiste) → LAR\n";

// Catch remaining VAPO circuits
$remaining = $conn->executeStatement("UPDATE circuit SET nature_id = ? WHERE nature_id = 4", [$idPVA]);
if ($remaining > 0) {
    echo "   {$remaining} circuits VAPO restants → PVA\n";
}

// 8. Delete obsolete natures
echo "\n8. Suppression des natures obsolètes...\n";
$toDelete = [
    5 => 'AUTRE',
    2 => 'VSO',
    3 => 'VEF',
    4 => 'VAPO',
];

foreach ($toDelete as $id => $code) {
    $remainingCarnets = (int) $conn->fetchOne("SELECT COUNT(*) FROM carnet_vol WHERE type_de_vol_id = ?", [$id]);
    $remainingCircuits = (int) $conn->fetchOne("SELECT COUNT(*) FROM circuit WHERE nature_id = ?", [$id]);

    if ($remainingCarnets > 0 || $remainingCircuits > 0) {
        echo "   SKIP {$code} (id {$id}): encore {$remainingCarnets} carnets, {$remainingCircuits} circuits\n";
    } else {
        $conn->executeStatement("DELETE FROM nature WHERE id = ?", [$id]);
        echo "   {$code} (id {$id}) supprimé\n";
    }
}

// 9. Set isParticularActivity flags on existing natures
echo "\n9. Marquage des activités particulières...\n";
$conn->executeStatement("UPDATE nature SET is_particular_activity = true WHERE code IN ('BAH', 'PVA', 'VOG', 'LAR', 'TRA')");
$conn->executeStatement("UPDATE nature SET is_particular_activity = false WHERE code IN ('VLO', 'VLD', 'VLP', 'FI', 'FC')");
echo "   OK\n";

// 10. Final state
echo "\n=== État final ===\n";
$rows = $conn->fetchAllAssociative("SELECT id, code, label, is_particular_activity as ap FROM nature ORDER BY id");
foreach ($rows as $row) {
    $ap = $row['ap'] ? 'AP' : '  ';
    echo "   [{$ap}] {$row['id']} | {$row['code']} | {$row['label']}\n";
}

echo "\n=== Migration terminée ===\n";
