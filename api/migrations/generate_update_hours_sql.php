<?php
/**
 * Génère le SQL pour :
 * 1. Importer les nouveaux carnet_vol
 * 2. Mettre à jour les horamètres aéronefs
 * 3. Mettre à jour les heures de vol des pilotes
 */

$OFFSET = 10000;
$OUTPUT = '/tmp/update_hours.sql';

$UUID_MAP = [
    '1effe8dc-f73d-600a-bd2c-3324ddd39a2e' => '1f105d7c-e69f-68fe-bfda-c752fddd30bf',
];

$TYPE_VOL_MAP = [
    1 => 1,
    3 => 6,
    5 => 6,
    6 => 6,
];

$pt = new PDO("pgsql:host=database;port=5432;dbname=planetair_temp", "app", "!ChangeMe!");
$pt->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

function esc($v) {
    if ($v === null) return 'NULL';
    if (is_bool($v)) return $v ? 'TRUE' : 'FALSE';
    if (is_int($v) || is_float($v)) return (string)$v;
    return "'" . str_replace("'", "''", (string)$v) . "'";
}

function escBool($v) {
    if ($v === null) return 'NULL';
    if ($v === 't' || $v === true || $v === '1' || $v === 1) return 'TRUE';
    return 'FALSE';
}

function mapUuid($v) {
    global $UUID_MAP;
    if ($v === null) return null;
    return $UUID_MAP[$v] ?? $v;
}

$fp = fopen($OUTPUT, 'w');
fwrite($fp, "BEGIN;\n\n");

// --- 1. CARNET_VOL (id > 1572) ---
$rows = $pt->query("SELECT * FROM carnet_vol WHERE id > 1572 ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
$count = count($rows);
fwrite($fp, "-- CARNET_VOL: $count enregistrements\n");
foreach ($rows as $r) {
    $id = $r['id'] + $OFFSET;
    $profilId = ($r['profil_id'] == 1) ? 1 : (int)$r['profil_id'] + $OFFSET;
    $typeVolId = $TYPE_VOL_MAP[(int)$r['type_de_vol_id']] ?? 'NULL';
    $sql = sprintf(
        "INSERT INTO carnet_vol (id, date, aeronef, duree, lieu_depart, is_validated, created_at, updated_at, profil_id, created_by_id, updated_by_id, lieux_arrivee, type_de_vol_id) VALUES (%d, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s) ON CONFLICT (id) DO NOTHING;\n",
        $id, esc($r['date']), esc($r['aeronef']), esc($r['duree']),
        esc($r['lieu_depart']), escBool($r['is_validated']),
        esc($r['created_at']), esc($r['updated_at']),
        $profilId, esc(mapUuid($r['created_by_id'])), esc(mapUuid($r['updated_by_id'])),
        esc($r['lieux_arrivee']), $typeVolId
    );
    fwrite($fp, $sql);
}
fwrite($fp, "\n");

// Sequence
fwrite($fp, "SELECT setval('carnet_vol_id_seq', (SELECT COALESCE(MAX(id), 1) FROM carnet_vol), true);\n\n");

// --- 2. UPDATE AERONEF HORAMETRES ---
fwrite($fp, "-- AERONEF: mise à jour horamètres\n");
$rows = $pt->query("SELECT id, immatriculation, horametre FROM aeronef ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    $lcId = $r['id'] + $OFFSET;
    fwrite($fp, sprintf(
        "UPDATE aeronef SET horametre = %s WHERE id = %d;\n",
        esc($r['horametre']), $lcId
    ));
}
fwrite($fp, "\n");

// --- 3. UPDATE PROFIL_PILOTE TOTAL_FLIGHT_HOURS ---
fwrite($fp, "-- PROFIL_PILOTE: mise à jour heures de vol\n");

$delta = $pt->query("SELECT profil_id, SUM(duree) as delta FROM carnet_vol WHERE id > 1572 AND profil_id = 1 GROUP BY profil_id")->fetch(PDO::FETCH_ASSOC);
if ($delta) {
    fwrite($fp, sprintf(
        "UPDATE profil_pilote SET total_flight_hours = total_flight_hours + %s WHERE id = 1;\n",
        $delta['delta']
    ));
}

$rows = $pt->query("SELECT id, total_flight_hours FROM profil_pilote WHERE id > 1 ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    $lcId = $r['id'] + $OFFSET;
    fwrite($fp, sprintf(
        "UPDATE profil_pilote SET total_flight_hours = %s WHERE id = %d;\n",
        esc($r['total_flight_hours']), $lcId
    ));
}

fwrite($fp, "\nCOMMIT;\n");
fclose($fp);

echo "Fichier généré: $OUTPUT\n";
echo "carnet_vol: $count entrées\n";
echo "aeronef: " . count($pt->query("SELECT id FROM aeronef")->fetchAll()) . " mises à jour\n";
echo "profil_pilote: " . (count($rows) + ($delta ? 1 : 0)) . " mises à jour\n";
