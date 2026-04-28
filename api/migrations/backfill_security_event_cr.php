<?php

require dirname(__DIR__).'/vendor/autoload.php';

$kernel = new \App\Kernel('prod', false);
$kernel->boot();
$conn = $kernel->getContainer()->get('doctrine')->getConnection();

$html = '<h2>Compte-rendu d\'événement de sécurité</h2>

<h3>1. Identification</h3>
<table>
<tr><td><strong>Exploitant</strong></td><td>Planetair974</td></tr>
<tr><td><strong>Type d\'événement</strong></td><td>Incident</td></tr>
<tr><td><strong>Date et heure</strong></td><td>27/04/2026 à 15h33</td></tr>
<tr><td><strong>Pilote</strong></td><td>Seb Maillot</td></tr>
<tr><td><strong>Aéronef</strong></td><td>F-JDQJ</td></tr>
</table>

<h3>2. Description des faits</h3>
<p>Atterrissage en piste 15 alors qu\'un aéronef était sur la piste en roulage pour une sortie par la bretelle D.</p>

<h3>3. Circonstances</h3>
<p><em>Conditions météo, phase de vol, environnement…</em></p>

<h3>4. Conséquences</h3>
<p><em>Dommages matériels, corporels, impact sur l\'exploitation…</em></p>

<h3>5. Analyse des causes</h3>
<p><em>Facteurs contributifs identifiés…</em></p>

<h3>6. Mesures correctives</h3>
<ul>
<li><em>Action corrective 1…</em></li>
<li><em>Action corrective 2…</em></li>
</ul>

<h3>7. Retour d\'expérience</h3>
<p><em>Enseignements tirés et communication aux pilotes…</em></p>';

$conn->executeStatement(
    'UPDATE security_event SET compte_rendu_suivi = ? WHERE id = ? AND compte_rendu_suivi IS NULL',
    [$html, 1]
);

echo "OK — CR pré-rempli pour security_event #1\n";
