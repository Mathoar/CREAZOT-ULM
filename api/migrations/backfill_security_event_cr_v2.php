<?php

require dirname(__DIR__).'/vendor/autoload.php';

$kernel = new \App\Kernel('prod', false);
$kernel->boot();
$conn = $kernel->getContainer()->get('doctrine')->getConnection();

$html = '<h3>1. Description des faits</h3>
<p>Atterrissage en piste 15 alors qu\'un aéronef était sur la piste en roulage pour une sortie par la bretelle D.</p>

<h3>2. Circonstances</h3>
<p><em>Conditions météo, phase de vol, environnement…</em></p>

<h3>3. Conséquences</h3>
<p><em>Dommages matériels, corporels, impact sur l\'exploitation…</em></p>

<h3>4. Analyse des causes</h3>
<p><em>Facteurs contributifs identifiés…</em></p>

<h3>5. Mesures correctives</h3>
<ul>
<li><em>Action corrective 1…</em></li>
<li><em>Action corrective 2…</em></li>
</ul>

<h3>6. Retour d\'expérience</h3>
<p><em>Enseignements tirés et communication aux pilotes…</em></p>';

$conn->executeStatement(
    'UPDATE security_event SET compte_rendu_suivi = ? WHERE id = ?',
    [$html, 1]
);

echo "OK — CR mis à jour pour security_event #1 (sans section Identification)\n";
