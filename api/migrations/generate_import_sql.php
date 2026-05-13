<?php
/**
 * Génère un fichier SQL d'import des données Planetair vers Logic'Ciel.
 * Lit depuis planetair_temp (local) et produit /tmp/import_planetair.sql
 * avec offset +10000, client_id=5, taux_tva=8.5
 *
 * Usage: php generate_import_sql.php
 * Conservé pour réutilisation lors de la bascule définitive.
 */

$OFFSET = 10000;
$CLIENT_ID = 5;
$TVA = 8.5;
$OUTPUT = '/tmp/import_planetair.sql';

$UUID_MAP = [
    '1effe8dc-f73d-600a-bd2c-3324ddd39a2e' => '1f105d7c-e69f-68fe-bfda-c752fddd30bf',
];

function mapUuid($v) {
    global $UUID_MAP;
    if ($v === null) return null;
    return $UUID_MAP[$v] ?? $v;
}

$pt = new PDO("pgsql:host=database;port=5432;dbname=planetair_temp", "app", "!ChangeMe!");
$pt->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$fp = fopen($OUTPUT, 'w');
fwrite($fp, "BEGIN;\n\n");

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

function offsetOrNull($v, $offset) {
    if ($v === null) return 'NULL';
    return (int)$v + $offset;
}

$counts = [];

// --- 0. Cleanup: supprimer passagers de test ---
fwrite($fp, "-- Cleanup: suppression passagers de test\n");
fwrite($fp, "DELETE FROM passager WHERE id IN (8, 9, 12, 13);\n\n");

// --- 1. PASSAGER (id > 1684) ---
$rows = $pt->query("SELECT * FROM passager WHERE id > 1684 ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
$counts['passager'] = count($rows);
fwrite($fp, "-- PASSAGER: {$counts['passager']} enregistrements\n");
foreach ($rows as $r) {
    $id = $r['id'] + $OFFSET;
    $sql = sprintf(
        "INSERT INTO passager (id, nom, prenom, email, telephone, date, consent_accepted, consent_text, consent_datetime, client_id, poids) VALUES (%d, %s, %s, %s, %s, %s, %s, %s, %s, %d, NULL) ON CONFLICT (id) DO NOTHING;\n",
        $id, esc($r['nom']), esc($r['prenom']), esc($r['email']), esc($r['telephone']),
        esc($r['date']), escBool($r['consent_accepted']), esc($r['consent_text']),
        esc($r['consent_datetime']), $CLIENT_ID
    );
    fwrite($fp, $sql);
}
fwrite($fp, "\n");

// --- 2. PAYMENT (id > 771) ---
$rows = $pt->query("SELECT * FROM payment WHERE id > 771 ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
$counts['payment'] = count($rows);
fwrite($fp, "-- PAYMENT: {$counts['payment']} enregistrements\n");
foreach ($rows as $r) {
    $id = $r['id'] + $OFFSET;
    $sql = sprintf(
        "INSERT INTO payment (id, reference, name, date, label, reservation_code, remarques, client_id) VALUES (%d, %s, %s, %s, %s, %s, %s, %d) ON CONFLICT (id) DO NOTHING;\n",
        $id, esc($r['reference']), esc($r['name']), esc($r['date']),
        esc($r['label']), esc($r['reservation_code']), esc($r['remarques']), $CLIENT_ID
    );
    fwrite($fp, $sql);
}
fwrite($fp, "\n");

// --- 3. PRESTATION (id > 965) ---
$rows = $pt->query("SELECT * FROM prestation WHERE id > 965 ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
$counts['prestation'] = count($rows);
fwrite($fp, "-- PRESTATION: {$counts['prestation']} enregistrements\n");
foreach ($rows as $r) {
    $id = $r['id'] + $OFFSET;
    $sql = sprintf(
        "INSERT INTO prestation (id, date, duree, horametre_depart, horametre_fin, turnover, aeronef_id, pilote_id, remarques, encadrant_id, created_at, updated_at, created_by_id, updated_by_id, client_id) VALUES (%d, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %d) ON CONFLICT (id) DO NOTHING;\n",
        $id, esc($r['date']), esc($r['duree']), esc($r['horametre_depart']), esc($r['horametre_fin']),
        esc($r['turnover']), offsetOrNull($r['aeronef_id'], $OFFSET), esc(mapUuid($r['pilote_id'])),
        esc($r['remarques']), esc(mapUuid($r['encadrant_id'])),
        esc($r['created_at']), esc($r['updated_at']), esc(mapUuid($r['created_by_id'])), esc(mapUuid($r['updated_by_id'])),
        $CLIENT_ID
    );
    fwrite($fp, $sql);
}
fwrite($fp, "\n");

// --- 4. CADEAU (id > 101, pour inclure le 102 manquant + nouveaux) ---
$rows = $pt->query("SELECT * FROM cadeau WHERE id > 101 ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
$counts['cadeau'] = count($rows);
fwrite($fp, "-- CADEAU: {$counts['cadeau']} enregistrements\n");
foreach ($rows as $r) {
    $id = $r['id'] + $OFFSET;
    $paymentId = $r['payment_id'];
    if ($paymentId !== null && (int)$paymentId < 10000) {
        $paymentId = (int)$paymentId + $OFFSET;
    }
    $paymentIdSql = $paymentId === null ? 'NULL' : (int)$paymentId;

    $sql = sprintf(
        "INSERT INTO cadeau (id, code, beneficiaire, offreur, fin, payment_id, used, cout, circuit_id, option_id, email, message, send_email, quantite, date, prix, options_id, gift, telephone, client_id, taux_tva) VALUES (%d, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %d, %s) ON CONFLICT (id) DO NOTHING;\n",
        $id, esc($r['code']), esc($r['beneficiaire']), esc($r['offreur']), esc($r['fin']),
        $paymentIdSql, escBool($r['used']), esc($r['cout']),
        offsetOrNull($r['circuit_id'], $OFFSET), offsetOrNull($r['option_id'], $OFFSET),
        esc($r['email']), esc($r['message']), escBool($r['send_email']),
        esc($r['quantite']), esc($r['date']), esc($r['prix']),
        offsetOrNull($r['options_id'], $OFFSET), escBool($r['gift']),
        esc($r['telephone']), $CLIENT_ID, $TVA
    );
    fwrite($fp, $sql);
}
fwrite($fp, "\n");

// --- 5. RESERVATION (id > 2548) ---
$rows = $pt->query("SELECT * FROM reservation WHERE id > 2548 ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
$counts['reservation'] = count($rows);
fwrite($fp, "-- RESERVATION: {$counts['reservation']} enregistrements\n");
foreach ($rows as $r) {
    $id = $r['id'] + $OFFSET;
    $sql = sprintf(
        "INSERT INTO reservation (id, nom, telephone, quantite, prix, debut, fin, color, statut, remarques, circuit_id, option_id, pilote_id, avion_id, report, email, position, paid, cadeau_id, code, payment_reference, client_id) VALUES (%d, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %d) ON CONFLICT (id) DO NOTHING;\n",
        $id, esc($r['nom']), esc($r['telephone']), esc($r['quantite']), esc($r['prix']),
        esc($r['debut']), esc($r['fin']), esc($r['color']), esc($r['statut']),
        esc($r['remarques']), offsetOrNull($r['circuit_id'], $OFFSET),
        offsetOrNull($r['option_id'], $OFFSET), esc(mapUuid($r['pilote_id'])),
        offsetOrNull($r['avion_id'], $OFFSET), escBool($r['report']),
        esc($r['email']), esc($r['position']), escBool($r['paid']),
        offsetOrNull($r['cadeau_id'], $OFFSET), esc($r['code']),
        esc($r['payment_reference']), $CLIENT_ID
    );
    fwrite($fp, $sql);
}
fwrite($fp, "\n");

// --- 6. VOL (id > 1598) ---
$rows = $pt->query("SELECT * FROM vol WHERE id > 1598 ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
$counts['vol'] = count($rows);
fwrite($fp, "-- VOL: {$counts['vol']} enregistrements\n");
foreach ($rows as $r) {
    $id = $r['id'] + $OFFSET;
    $sql = sprintf(
        "INSERT INTO vol (id, quantite, duree, prix, circuit_id, prestation_id, option_id, cout, created_at, updated_at, created_by_id, updated_by_id, client_id, taux_tva) VALUES (%d, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %d, %s) ON CONFLICT (id) DO NOTHING;\n",
        $id, esc($r['quantite']), esc($r['duree']), esc($r['prix']),
        (int)$r['circuit_id'] + $OFFSET, (int)$r['prestation_id'] + $OFFSET,
        offsetOrNull($r['option_id'], $OFFSET), esc($r['cout']),
        esc($r['created_at']), esc($r['updated_at']), esc(mapUuid($r['created_by_id'])), esc(mapUuid($r['updated_by_id'])),
        $CLIENT_ID, $TVA
    );
    fwrite($fp, $sql);
}
fwrite($fp, "\n");

// --- 7. LANDING (id > 1258) ---
$rows = $pt->query("SELECT * FROM landing WHERE id > 1258 ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
$counts['landing'] = count($rows);
fwrite($fp, "-- LANDING: {$counts['landing']} enregistrements\n");
foreach ($rows as $r) {
    $id = $r['id'] + $OFFSET;
    $sql = sprintf(
        "INSERT INTO landing (id, airport_code, airport_name, touches, complets, vol_id, client_id) VALUES (%d, %s, %s, %s, %s, %d, %d) ON CONFLICT (id) DO NOTHING;\n",
        $id, esc($r['airport_code']), esc($r['airport_name']),
        esc($r['touches']), esc($r['complets']),
        (int)$r['vol_id'] + $OFFSET, $CLIENT_ID
    );
    fwrite($fp, $sql);
}
fwrite($fp, "\n");

// --- 8. PAYMENT_DETAIL (payment_id > 771) ---
$rows = $pt->query("SELECT * FROM payment_detail WHERE payment_id > 771 ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
$counts['payment_detail'] = count($rows);
fwrite($fp, "-- PAYMENT_DETAIL: {$counts['payment_detail']} enregistrements\n");
foreach ($rows as $r) {
    $id = $r['id'] + $OFFSET;
    $sql = sprintf(
        "INSERT INTO payment_detail (id, mode, amount, payment_id, prepayment_id, expense_id, client_id, taux_tva) VALUES (%d, %s, %s, %d, %s, %s, %d, %s) ON CONFLICT (id) DO NOTHING;\n",
        $id, esc($r['mode']), esc($r['amount']),
        (int)$r['payment_id'] + $OFFSET, offsetOrNull($r['prepayment_id'], $OFFSET), esc($r['expense_id']),
        $CLIENT_ID, $TVA
    );
    fwrite($fp, $sql);
}
fwrite($fp, "\n");

// --- Update sequences ---
fwrite($fp, "-- Mise à jour des séquences\n");
$seqs = [
    'passager' => 'passager_id_seq',
    'payment' => 'payment_id_seq',
    'prestation' => 'prestation_id_seq',
    'cadeau' => 'cadeau_id_seq',
    'reservation' => 'reservation_id_seq',
    'vol' => 'vol_id_seq',
    'landing' => 'landing_id_seq',
    'payment_detail' => 'payment_detail_id_seq',
];
foreach ($seqs as $table => $seq) {
    fwrite($fp, "SELECT setval('$seq', (SELECT COALESCE(MAX(id), 1) FROM \"$table\"), true);\n");
}

fwrite($fp, "\nCOMMIT;\n");
fclose($fp);

$total = array_sum($counts);
echo "Fichier SQL généré: $OUTPUT\n";
echo "Détail:\n";
foreach ($counts as $table => $count) {
    echo "  $table: $count\n";
}
echo "TOTAL: $total enregistrements\n";
