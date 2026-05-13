<?php
/**
 * Import des données manquantes de Planetair (dump) vers Logic'Ciel.
 * Offset +10000 sur tous les IDs entiers, client_id=5, taux_tva=8.5
 */

$OFFSET = 10000;
$CLIENT_ID = 5;
$TVA = 8.5;
$DRY_RUN = in_array('--dry-run', $argv ?? []);

// Logic'Ciel (production) — credentials via env vars
$lcPassword = getenv('LC_DB_PASSWORD');
if (!$lcPassword) {
    fwrite(STDERR, "ERREUR : variable d'environnement LC_DB_PASSWORD requise.\n");
    fwrite(STDERR, "Usage : LC_DB_PASSWORD=xxx PT_DB_PASSWORD=yyy php import_planetair_delta.php\n");
    exit(1);
}
$lc = new PDO(
    "pgsql:host=db-logic-ciel-do-user-18144705-0.h.db.ondigitalocean.com;port=25060;dbname=defaultdb;sslmode=require",
    "doadmin",
    $lcPassword
);
$lc->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Planetair dump (local container DB)
$ptPassword = getenv('PT_DB_PASSWORD') ?: '!ChangeMe!';
$pt = new PDO("pgsql:host=database;port=5432;dbname=planetair_temp", "app", $ptPassword);
$pt->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$counts = [];

// --- 0. Cleanup: supprimer passagers de test ---
if (!$DRY_RUN) {
    $lc->exec("DELETE FROM passager WHERE id IN (8, 9, 12, 13)");
}
echo "[CLEANUP] Suppression passagers test (8,9,12,13)\n";

// --- 1. PASSAGER (id > 1684) ---
$rows = $pt->query("SELECT * FROM passager WHERE id > 1684 ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
$counts['passager'] = count($rows);
foreach ($rows as $r) {
    $sql = "INSERT INTO passager (id, nom, prenom, email, telephone, date, consent_accepted, consent_text, consent_datetime, client_id, poids)
            VALUES (:id, :nom, :prenom, :email, :telephone, :date, :consent_accepted, :consent_text, :consent_datetime, :client_id, NULL)
            ON CONFLICT (id) DO NOTHING";
    $params = [
        'id' => $r['id'] + $OFFSET,
        'nom' => $r['nom'],
        'prenom' => $r['prenom'],
        'email' => $r['email'],
        'telephone' => $r['telephone'],
        'date' => $r['date'],
        'consent_accepted' => $r['consent_accepted'],
        'consent_text' => $r['consent_text'],
        'consent_datetime' => $r['consent_datetime'],
        'client_id' => $CLIENT_ID,
    ];
    if (!$DRY_RUN) {
        $stmt = $lc->prepare($sql);
        $stmt->execute($params);
    }
}
echo "[1/7] passager: {$counts['passager']} enregistrements\n";

// --- 2. PAYMENT (id > 771) ---
$rows = $pt->query("SELECT * FROM payment WHERE id > 771 ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
$counts['payment'] = count($rows);
foreach ($rows as $r) {
    $sql = "INSERT INTO payment (id, reference, name, date, label, reservation_code, remarques, client_id)
            VALUES (:id, :reference, :name, :date, :label, :reservation_code, :remarques, :client_id)
            ON CONFLICT (id) DO NOTHING";
    $params = [
        'id' => $r['id'] + $OFFSET,
        'reference' => $r['reference'],
        'name' => $r['name'],
        'date' => $r['date'],
        'label' => $r['label'],
        'reservation_code' => $r['reservation_code'],
        'remarques' => $r['remarques'],
        'client_id' => $CLIENT_ID,
    ];
    if (!$DRY_RUN) {
        $stmt = $lc->prepare($sql);
        $stmt->execute($params);
    }
}
echo "[2/7] payment: {$counts['payment']} enregistrements\n";

// --- 3. PRESTATION (id > 965) ---
$rows = $pt->query("SELECT * FROM prestation WHERE id > 965 ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
$counts['prestation'] = count($rows);
foreach ($rows as $r) {
    $sql = "INSERT INTO prestation (id, date, duree, horametre_depart, horametre_fin, turnover, aeronef_id, pilote_id, remarques, encadrant_id, created_at, updated_at, created_by_id, updated_by_id, client_id)
            VALUES (:id, :date, :duree, :horametre_depart, :horametre_fin, :turnover, :aeronef_id, :pilote_id, :remarques, :encadrant_id, :created_at, :updated_at, :created_by_id, :updated_by_id, :client_id)
            ON CONFLICT (id) DO NOTHING";
    $params = [
        'id' => $r['id'] + $OFFSET,
        'date' => $r['date'],
        'duree' => $r['duree'],
        'horametre_depart' => $r['horametre_depart'],
        'horametre_fin' => $r['horametre_fin'],
        'turnover' => $r['turnover'],
        'aeronef_id' => $r['aeronef_id'] + $OFFSET,
        'pilote_id' => $r['pilote_id'],
        'remarques' => $r['remarques'],
        'encadrant_id' => $r['encadrant_id'],
        'created_at' => $r['created_at'],
        'updated_at' => $r['updated_at'],
        'created_by_id' => $r['created_by_id'],
        'updated_by_id' => $r['updated_by_id'],
        'client_id' => $CLIENT_ID,
    ];
    if (!$DRY_RUN) {
        $stmt = $lc->prepare($sql);
        $stmt->execute($params);
    }
}
echo "[3/7] prestation: {$counts['prestation']} enregistrements\n";

// --- 4. CADEAU (id > 105) ---
$rows = $pt->query("SELECT * FROM cadeau WHERE id > 105 ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
$counts['cadeau'] = count($rows);
foreach ($rows as $r) {
    $paymentId = $r['payment_id'];
    if ($paymentId !== null && $paymentId < 10000) {
        $paymentId += $OFFSET;
    }
    $sql = "INSERT INTO cadeau (id, code, beneficiaire, offreur, fin, payment_id, used, cout, circuit_id, option_id, email, message, send_email, quantite, date, prix, options_id, gift, telephone, client_id, taux_tva)
            VALUES (:id, :code, :beneficiaire, :offreur, :fin, :payment_id, :used, :cout, :circuit_id, :option_id, :email, :message, :send_email, :quantite, :date, :prix, :options_id, :gift, :telephone, :client_id, :taux_tva)
            ON CONFLICT (id) DO NOTHING";
    $params = [
        'id' => $r['id'] + $OFFSET,
        'code' => $r['code'],
        'beneficiaire' => $r['beneficiaire'],
        'offreur' => $r['offreur'],
        'fin' => $r['fin'],
        'payment_id' => $paymentId,
        'used' => $r['used'],
        'cout' => $r['cout'],
        'circuit_id' => $r['circuit_id'] !== null ? $r['circuit_id'] + $OFFSET : null,
        'option_id' => $r['option_id'] !== null ? $r['option_id'] + $OFFSET : null,
        'email' => $r['email'],
        'message' => $r['message'],
        'send_email' => $r['send_email'],
        'quantite' => $r['quantite'],
        'date' => $r['date'],
        'prix' => $r['prix'],
        'options_id' => $r['options_id'] !== null ? $r['options_id'] + $OFFSET : null,
        'gift' => $r['gift'],
        'telephone' => $r['telephone'],
        'client_id' => $CLIENT_ID,
        'taux_tva' => $TVA,
    ];
    if (!$DRY_RUN) {
        $stmt = $lc->prepare($sql);
        $stmt->execute($params);
    }
}
echo "[4/7] cadeau: {$counts['cadeau']} enregistrements\n";

// --- 5. RESERVATION (id > 2548) ---
$rows = $pt->query("SELECT * FROM reservation WHERE id > 2548 ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
$counts['reservation'] = count($rows);
foreach ($rows as $r) {
    $sql = "INSERT INTO reservation (id, nom, telephone, quantite, prix, debut, fin, color, statut, remarques, circuit_id, option_id, pilote_id, avion_id, report, email, position, paid, cadeau_id, code, payment_reference, client_id)
            VALUES (:id, :nom, :telephone, :quantite, :prix, :debut, :fin, :color, :statut, :remarques, :circuit_id, :option_id, :pilote_id, :avion_id, :report, :email, :position, :paid, :cadeau_id, :code, :payment_reference, :client_id)
            ON CONFLICT (id) DO NOTHING";
    $params = [
        'id' => $r['id'] + $OFFSET,
        'nom' => $r['nom'],
        'telephone' => $r['telephone'],
        'quantite' => $r['quantite'],
        'prix' => $r['prix'],
        'debut' => $r['debut'],
        'fin' => $r['fin'],
        'color' => $r['color'],
        'statut' => $r['statut'],
        'remarques' => $r['remarques'],
        'circuit_id' => $r['circuit_id'] !== null ? $r['circuit_id'] + $OFFSET : null,
        'option_id' => $r['option_id'] !== null ? $r['option_id'] + $OFFSET : null,
        'pilote_id' => $r['pilote_id'],
        'avion_id' => $r['avion_id'] !== null ? $r['avion_id'] + $OFFSET : null,
        'report' => $r['report'],
        'email' => $r['email'],
        'position' => $r['position'],
        'paid' => $r['paid'],
        'cadeau_id' => $r['cadeau_id'] !== null ? $r['cadeau_id'] + $OFFSET : null,
        'code' => $r['code'],
        'payment_reference' => $r['payment_reference'],
        'client_id' => $CLIENT_ID,
    ];
    if (!$DRY_RUN) {
        $stmt = $lc->prepare($sql);
        $stmt->execute($params);
    }
}
echo "[5/7] reservation: {$counts['reservation']} enregistrements\n";

// --- 6. VOL (id > 1598) ---
$rows = $pt->query("SELECT * FROM vol WHERE id > 1598 ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
$counts['vol'] = count($rows);
foreach ($rows as $r) {
    $sql = "INSERT INTO vol (id, quantite, duree, prix, circuit_id, prestation_id, option_id, cout, created_at, updated_at, created_by_id, updated_by_id, client_id, taux_tva)
            VALUES (:id, :quantite, :duree, :prix, :circuit_id, :prestation_id, :option_id, :cout, :created_at, :updated_at, :created_by_id, :updated_by_id, :client_id, :taux_tva)
            ON CONFLICT (id) DO NOTHING";
    $params = [
        'id' => $r['id'] + $OFFSET,
        'quantite' => $r['quantite'],
        'duree' => $r['duree'],
        'prix' => $r['prix'],
        'circuit_id' => $r['circuit_id'] + $OFFSET,
        'prestation_id' => $r['prestation_id'] + $OFFSET,
        'option_id' => $r['option_id'] !== null ? $r['option_id'] + $OFFSET : null,
        'cout' => $r['cout'],
        'created_at' => $r['created_at'],
        'updated_at' => $r['updated_at'],
        'created_by_id' => $r['created_by_id'],
        'updated_by_id' => $r['updated_by_id'],
        'client_id' => $CLIENT_ID,
        'taux_tva' => $TVA,
    ];
    if (!$DRY_RUN) {
        $stmt = $lc->prepare($sql);
        $stmt->execute($params);
    }
}
echo "[6/7] vol: {$counts['vol']} enregistrements\n";

// --- 7. LANDING (id > 1258) ---
$rows = $pt->query("SELECT * FROM landing WHERE id > 1258 ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
$counts['landing'] = count($rows);
foreach ($rows as $r) {
    $sql = "INSERT INTO landing (id, airport_code, airport_name, touches, complets, vol_id, client_id)
            VALUES (:id, :airport_code, :airport_name, :touches, :complets, :vol_id, :client_id)
            ON CONFLICT (id) DO NOTHING";
    $params = [
        'id' => $r['id'] + $OFFSET,
        'airport_code' => $r['airport_code'],
        'airport_name' => $r['airport_name'],
        'touches' => $r['touches'],
        'complets' => $r['complets'],
        'vol_id' => $r['vol_id'] + $OFFSET,
        'client_id' => $CLIENT_ID,
    ];
    if (!$DRY_RUN) {
        $stmt = $lc->prepare($sql);
        $stmt->execute($params);
    }
}
echo "[7/7] landing: {$counts['landing']} enregistrements\n";

// --- 8. PAYMENT_DETAIL (payment_id > 771) ---
$rows = $pt->query("SELECT * FROM payment_detail WHERE payment_id > 771 ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
$counts['payment_detail'] = count($rows);
foreach ($rows as $r) {
    $sql = "INSERT INTO payment_detail (id, mode, amount, payment_id, prepayment_id, expense_id, client_id, taux_tva)
            VALUES (:id, :mode, :amount, :payment_id, :prepayment_id, :expense_id, :client_id, :taux_tva)
            ON CONFLICT (id) DO NOTHING";
    $params = [
        'id' => $r['id'] + $OFFSET,
        'mode' => $r['mode'],
        'amount' => $r['amount'],
        'payment_id' => $r['payment_id'] + $OFFSET,
        'prepayment_id' => $r['prepayment_id'],
        'expense_id' => $r['expense_id'],
        'client_id' => $CLIENT_ID,
        'taux_tva' => $TVA,
    ];
    if (!$DRY_RUN) {
        $stmt = $lc->prepare($sql);
        $stmt->execute($params);
    }
}
echo "[+] payment_detail: {$counts['payment_detail']} enregistrements\n";

// --- Update sequences ---
if (!$DRY_RUN) {
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
        $lc->exec("SELECT setval('$seq', (SELECT MAX(id) FROM \"$table\"), true)");
    }
    echo "\n[SEQ] Séquences mises à jour\n";
}

$total = array_sum($counts);
echo "\n=== TOTAL: $total enregistrements " . ($DRY_RUN ? "(DRY RUN)" : "importés") . " ===\n";
