<?php

require dirname(__DIR__).'/vendor/autoload.php';

$kernel = new \App\Kernel('prod', false);
$kernel->boot();
$conn = $kernel->getContainer()->get('doctrine')->getConnection();

$newIncidentsHtml = '<h3>Procédure de gestion des incidents</h3>
<p>En cas d\'événement de sécurité (incident, accident, quasi-accident, observation), la procédure suivante s\'applique :</p>

<h4>1. Actions immédiates</h4>
<ul>
    <li>Assurer la sécurité des personnes (passagers, équipage, tiers).</li>
    <li>Sécuriser l\'aéronef et la zone concernée.</li>
    <li>Prévenir les secours si nécessaire (SAMU, pompiers).</li>
</ul>

<h4>2. Notification et délais réglementaires</h4>
<ul>
    <li>Informer immédiatement le dirigeant responsable de l\'exploitation.</li>
    <li>En cas d\'accident ou d\'incident grave : notification au BEA (Bureau d\'Enquêtes et d\'Analyses) dans les plus brefs délais.</li>
    <li>Notification à la DGAC / DSAC dans un délai maximal de <strong>72 heures</strong> suivant l\'événement.</li>
    <li>Rédaction d\'un compte rendu de suivi dans les <strong>30 jours</strong> suivant la notification.</li>
</ul>

<h4>3. Enregistrement dans Logic\'Ciel</h4>
<p>Chaque événement de sécurité est enregistré dans le module « Événements sécurité » de la plateforme Logic\'Ciel, avec les informations suivantes :</p>
<ul>
    <li><strong>Type</strong> : incident, accident, quasi-accident ou observation.</li>
    <li><strong>Description</strong> : circonstances détaillées de l\'événement.</li>
    <li><strong>Pilote et aéronef</strong> concernés.</li>
    <li><strong>Dates de notification</strong> : exploitant, DGAC, BEA — permettant le suivi des délais réglementaires.</li>
    <li><strong>Statut</strong> : ouvert, en cours d\'analyse, ou clôturé.</li>
    <li><strong>Compte rendu de suivi</strong> : actions correctives et retour d\'expérience.</li>
</ul>

<h4>4. Rapport</h4>
<ul>
    <li>Rédaction d\'un compte rendu d\'événement (CRE) dans les 72 heures.</li>
    <li>Documentation photographique si applicable.</li>
    <li>Conservation de tous les éléments de preuve (carnet de route, données de vol, témoignages).</li>
</ul>

<h4>5. Analyse et retour d\'expérience</h4>
<ul>
    <li>Analyse des causes de l\'événement.</li>
    <li>Mise en place d\'actions correctives si nécessaire.</li>
    <li>Clôture de l\'événement dans Logic\'Ciel une fois les actions correctives validées.</li>
    <li>Communication du retour d\'expérience à l\'ensemble des pilotes.</li>
</ul>';

// 1. Update incidents sections that still have the original default content
$affected = $conn->executeStatement(
    "UPDATE manex_section SET custom_html = ? WHERE section_key = 'incidents' AND custom_html LIKE '<h3>Procédure de gestion des incidents</h3>%'",
    [$newIncidentsHtml]
);
echo "OK: Updated $affected incidents section(s)\n";

// 2. Update procedures_pax for clients with hasWeightCollection = true
$clients = $conn->fetchAllAssociative(
    "SELECT c.id, c.consent_text FROM client c WHERE c.has_weight_collection = true"
);

foreach ($clients as $clientRow) {
    $clientId = $clientRow['id'];
    $consentText = $clientRow['consent_text'];

    $html = '<h3>Enregistrement des passagers</h3>
<p>L\'exploitant utilise un système d\'enregistrement en ligne des passagers via la plateforme Logic\'Ciel. Avant chaque vol, les passagers sont invités à s\'enregistrer en fournissant leurs informations personnelles (nom, prénom, poids, coordonnées).</p>
<h4>Consentement et acceptation des conditions</h4>
<p>Lors de l\'enregistrement, chaque passager doit obligatoirement prendre connaissance et accepter les conditions suivantes avant de pouvoir finaliser son inscription :</p>';

    if ($consentText) {
        $html .= '<blockquote style="border-left: 3px solid #4a5568; padding: 8px 12px; margin: 10px 0; background: #f7fafc; font-style: italic;">'
            . htmlspecialchars($consentText, ENT_QUOTES, 'UTF-8')
            . '</blockquote>';
    }

    $html .= '<h4>Collecte du poids passager</h4>
<p>Le poids de chaque passager est collecté lors de l\'enregistrement en ligne. Cette information permet au pilote commandant de bord de vérifier que la masse totale au décollage (pilote + passager + carburant) reste dans les limites définies par le constructeur et les règles de vol de l\'exploitant.</p>
<p>En cas de dépassement du poids maximal passager défini dans les règles de vol, le pilote en est informé et prend la décision appropriée (adaptation de la charge carburant, refus d\'embarquement, ou changement d\'aéronef si disponible).</p>';

    $html .= '<p>Ce dispositif permet de garantir la traçabilité des passagers, le respect de la réglementation en matière de consentement éclairé, et la bonne gestion des masses et du centrage de l\'aéronef.</p>';

    $affected = $conn->executeStatement(
        "UPDATE manex_section SET custom_html = ? WHERE client_id = ? AND section_key = 'procedures_pax' AND custom_html LIKE '<h3>Enregistrement des passagers</h3>%'",
        [$html, $clientId]
    );
    echo "OK: Updated procedures_pax for client $clientId ($affected row(s))\n";
}

echo "\nMigration terminée.\n";
