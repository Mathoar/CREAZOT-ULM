<?php

require dirname(__DIR__) . '/vendor/autoload.php';

use App\Kernel;

$kernel = new Kernel('prod', false);
$kernel->boot();
$em = $kernel->getContainer()->get('doctrine')->getManager();
$conn = $em->getConnection();

$defaults = [
    'procedures_prevol' => '<h3>Check-list pré-vol</h3>
<p>Avant chaque vol, le pilote commandant de bord effectue les vérifications suivantes :</p>
<ul>
    <li><strong>Conditions météorologiques</strong> : consultation du METAR/TAF et vérification des conditions locales (vent, visibilité, plafond) au regard des minima définis dans la section « Limites météorologiques ».</li>
    <li><strong>Aéronef</strong> : visite pré-vol complète selon la check-list constructeur, vérification du carburant, de l\'état général et du carnet de route.</li>
    <li><strong>Documents réglementaires</strong> : validité du certificat de navigabilité, assurance, licence et qualification du pilote, certificat médical.</li>
    <li><strong>Masse et centrage</strong> : vérification de la masse au décollage et du centrage en fonction du nombre de passagers et du carburant embarqué.</li>
    <li><strong>Zone de vol</strong> : consultation des NOTAM, vérification des espaces aériens et restrictions éventuelles.</li>
    <li><strong>Communication passagers</strong> : envoi du SMS pré-vol aux passagers conformément au modèle configuré dans l\'application.</li>
</ul>
<h3>Critères NO GO</h3>
<p>Le vol est annulé si l\'une des conditions suivantes est constatée :</p>
<ul>
    <li>Conditions météorologiques inférieures aux minima définis.</li>
    <li>Anomalie technique détectée lors de la visite pré-vol.</li>
    <li>Documents pilote ou aéronef non à jour.</li>
    <li>Qualification pilote non valide pour le type de vol prévu.</li>
</ul>',

    'incidents' => '<h3>Procédure de gestion des incidents</h3>
<p>En cas d\'événement de sécurité (incident, accident, quasi-accident), la procédure suivante s\'applique :</p>
<h4>1. Actions immédiates</h4>
<ul>
    <li>Assurer la sécurité des personnes (passagers, équipage, tiers).</li>
    <li>Sécuriser l\'aéronef et la zone concernée.</li>
    <li>Prévenir les secours si nécessaire (SAMU, pompiers).</li>
</ul>
<h4>2. Notification</h4>
<ul>
    <li>Informer immédiatement le dirigeant responsable de l\'exploitation.</li>
    <li>En cas d\'accident ou d\'incident grave : notification au BEA (Bureau d\'Enquêtes et d\'Analyses) dans les plus brefs délais.</li>
    <li>Déclaration à la DSAC (Direction de la Sécurité de l\'Aviation Civile) si applicable.</li>
</ul>
<h4>3. Rapport</h4>
<ul>
    <li>Rédaction d\'un compte rendu d\'événement (CRE) dans les 72 heures.</li>
    <li>Documentation photographique si applicable.</li>
    <li>Conservation de tous les éléments de preuve (carnet de route, données de vol, témoignages).</li>
</ul>
<h4>4. Analyse et retour d\'expérience</h4>
<ul>
    <li>Analyse des causes de l\'événement.</li>
    <li>Mise en place d\'actions correctives si nécessaire.</li>
    <li>Communication du retour d\'expérience à l\'ensemble des pilotes.</li>
</ul>',

    'responsabilites' => '<h3>Responsabilités du dirigeant responsable</h3>
<ul>
    <li>Garantir le respect de la réglementation applicable à l\'exploitation.</li>
    <li>S\'assurer que les pilotes disposent des qualifications et certifications médicales à jour.</li>
    <li>Maintenir la flotte en état de navigabilité.</li>
    <li>Définir et mettre à jour les procédures d\'exploitation.</li>
    <li>Organiser les formations et contrôles périodiques des pilotes.</li>
    <li>Tenir à jour le présent MANEX.</li>
</ul>
<h3>Responsabilités du pilote commandant de bord</h3>
<ul>
    <li>Effectuer la visite pré-vol et vérifier l\'aptitude de l\'aéronef au vol.</li>
    <li>Prendre la décision GO / NO GO en fonction des conditions météorologiques et de l\'état de l\'aéronef.</li>
    <li>Assurer la sécurité des passagers et le respect du briefing de sécurité.</li>
    <li>Respecter les limitations définies dans le présent MANEX (météo, qualifications, procédures).</li>
    <li>Signaler tout événement de sécurité au dirigeant responsable.</li>
    <li>Tenir à jour le carnet de route de l\'aéronef.</li>
</ul>
<h3>Responsabilités du personnel au sol</h3>
<ul>
    <li>Accueillir et orienter les passagers conformément aux procédures.</li>
    <li>S\'assurer du bon déroulement du briefing passagers.</li>
    <li>Vérifier l\'identité et le nombre de passagers avant embarquement.</li>
    <li>Signaler toute anomalie ou situation inhabituelle au pilote ou au dirigeant.</li>
</ul>',
];

$updated = 0;
foreach ($defaults as $key => $html) {
    $count = $conn->executeStatement(
        'UPDATE manex_section SET custom_html = :html WHERE section_key = :key AND custom_html IS NULL',
        ['html' => $html, 'key' => $key]
    );
    $updated += $count;
    echo "Section '$key': $count rows updated\n";
}

echo "\nTotal updated: $updated\n";
