<?php

require dirname(__DIR__).'/vendor/autoload.php';

$kernel = new \App\Kernel('prod', false);
$kernel->boot();
$conn = $kernel->getContainer()->get('doctrine')->getConnection();

echo "=== Migration sections MANEX v2 ===\n\n";

// 1. Rename 'revision' → 'gestion_doc' and update title/position
echo "1. Renommage revision → gestion_doc...\n";
$n = $conn->executeStatement(
    "UPDATE manex_section SET section_key = 'gestion_doc', title = 'Gestion documentaire et révisions', position = 15 WHERE section_key = 'revision'"
);
echo "   {$n} sections renommées\n";

// 2. Update positions for existing sections that need shifting
echo "\n2. Mise à jour des positions...\n";
$positionUpdates = [
    ['documentation',    13],
    ['responsabilites',  14],
    ['annexes',          16],
];

foreach ($positionUpdates as [$key, $pos]) {
    $n = $conn->executeStatement(
        "UPDATE manex_section SET position = ? WHERE section_key = ?",
        [$pos, $key]
    );
    echo "   {$key} → position {$pos} ({$n} rows)\n";
}

// 3. Update incidents content for existing clients
echo "\n3. Mise à jour du contenu 'incidents'...\n";
$newIncidents = '<h3>Procédure de gestion des événements de sécurité</h3>
<p>En cas d\'événement de sécurité (incident, accident, quasi-accident, observation), la procédure suivante s\'applique :</p>

<h4>1. Actions immédiates</h4>
<ul>
    <li>Assurer la sécurité des personnes (passagers, équipage, tiers).</li>
    <li>Sécuriser l\'aéronef et la zone concernée.</li>
    <li>Prévenir les secours si nécessaire (SAMU, pompiers).</li>
</ul>

<h4>2. Enregistrement et notification automatisée</h4>
<p>L\'exploitant utilise le module « Événements sécurité » de Logic\'Ciel pour centraliser et automatiser la gestion des événements :</p>
<ul>
    <li><strong>Création de l\'événement</strong> : dès la saisie, un <strong>email de notification est automatiquement envoyé</strong> au dirigeant responsable, incluant le type d\'événement, la description, le pilote et l\'aéronef concernés.</li>
    <li><strong>Compte rendu pré-formaté</strong> : un modèle structuré (description des faits, circonstances, conséquences, analyse des causes, mesures correctives, retour d\'expérience) est <strong>automatiquement pré-rempli</strong> dans l\'éditeur, facilitant la rédaction.</li>
    <li><strong>Types d\'événements</strong> : incident, accident, quasi-accident, observation, ou note interne (pour les événements moins critiques ne nécessitant pas de notification réglementaire).</li>
</ul>

<h4>3. Délais réglementaires et envoi simplifié</h4>
<ul>
    <li>Notification à la DGAC / DSAC dans un délai maximal de <strong>72 heures</strong> suivant l\'événement.</li>
    <li>En cas d\'accident ou d\'incident grave : notification au <strong>BEA</strong> dans les plus brefs délais.</li>
    <li>Rédaction d\'un compte rendu de suivi dans les <strong>30 jours</strong> suivant la notification.</li>
    <li>Logic\'Ciel intègre un <strong>bouton d\'envoi du compte rendu en PDF</strong> directement à la DSAC, au BEA ou à un autre destinataire, avec <strong>mise à jour automatique des dates de notification</strong>.</li>
    <li>Les <strong>délais réglementaires sont affichés en temps réel</strong> sur chaque événement ouvert, avec un décompte visuel.</li>
</ul>

<h4>4. Suivi et clôture</h4>
<ul>
    <li>Le statut de chaque événement est suivi : ouvert, en cours d\'analyse, ou clôturé.</li>
    <li>La <strong>date de clôture est automatiquement renseignée</strong> lorsque le statut passe à « clôturé » (et réinitialisée si le statut est rouvert).</li>
    <li>Conservation de tous les éléments de preuve (carnet de route, données de vol, témoignages).</li>
</ul>

<h4>5. Analyse et retour d\'expérience</h4>
<ul>
    <li>Analyse des causes de l\'événement dans le compte rendu structuré.</li>
    <li>Mise en place et documentation des actions correctives.</li>
    <li>Communication du retour d\'expérience à l\'ensemble des pilotes.</li>
</ul>';

$n = $conn->executeStatement(
    "UPDATE manex_section SET custom_html = ? WHERE section_key = 'incidents'",
    [$newIncidents]
);
echo "   {$n} sections incidents mises à jour\n";

// 4. Insert default content for gestion_doc (for existing sections that were renamed)
echo "\n4. Ajout contenu par défaut gestion_doc...\n";
$gestionDocContent = '<h3>Politique d\'archivage</h3>
<p>L\'ensemble des documents relatifs à l\'exploitation sont conservés et archivés selon les règles suivantes :</p>
<ul>
    <li><strong>Documents pilotes</strong> (qualifications, certificats médicaux, attestations de formation) : conservés pendant toute la durée d\'activité du pilote au sein de la structure, puis 5 ans après son départ.</li>
    <li><strong>Carnets de route et carnets de vol</strong> : conservés de manière permanente dans Logic\'Ciel.</li>
    <li><strong>Événements de sécurité et comptes rendus</strong> : conservés de manière permanente, incluant les notifications, les analyses et les actions correctives.</li>
    <li><strong>Documents aéronef</strong> (suivi de maintenance, certificats de navigabilité) : conservés pendant toute la durée d\'exploitation de l\'aéronef, puis 2 ans après sa cession.</li>
    <li><strong>Versions du MANEX</strong> : chaque version générée est archivée avec son numéro de version, sa date et son auteur.</li>
</ul>

<h4>Révision du document</h4>
<p>Le présent manuel est révisé à chaque modification significative de l\'exploitation :</p>
<ul>
    <li>Changement de flotte (ajout ou retrait d\'aéronef).</li>
    <li>Modification des circuits ou des types d\'activités.</li>
    <li>Évolution des procédures de sécurité.</li>
    <li>Changement réglementaire impactant l\'exploitation.</li>
    <li>Retour d\'expérience nécessitant une mise à jour des procédures.</li>
</ul>
<p>Chaque révision donne lieu à une nouvelle version du MANEX, générée et archivée via Logic\'Ciel.</p>';

$n = $conn->executeStatement(
    "UPDATE manex_section SET custom_html = ? WHERE section_key = 'gestion_doc' AND (custom_html IS NULL OR custom_html = '')",
    [$gestionDocContent]
);
echo "   {$n} sections gestion_doc enrichies\n";

echo "\n=== Migration terminée ===\n";
echo "Note: Les sections 'formation', 'analyse_risques' et 'facteurs_humains' seront\n";
echo "créées automatiquement par ensureSections() au prochain accès MANEX de chaque client.\n";
