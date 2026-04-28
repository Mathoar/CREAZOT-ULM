<?php

require dirname(__DIR__) . '/vendor/autoload.php';

use App\Kernel;

$kernel = new Kernel('prod', false);
$kernel->boot();
$conn = $kernel->getContainer()->get('doctrine')->getConnection();

$newCustomHtml = '<h3>Méthodologie d\'analyse des risques</h3>
<p>L\'exploitant met en œuvre une démarche d\'identification et de gestion des risques basée sur la méthode <strong>TEM (Threat and Error Management)</strong>. Cette analyse est réalisée pour chaque <strong>activité particulière (AP)</strong> déclarée.</p>

<h4>Principe de la méthode TEM</h4>
<p>La méthode TEM distingue trois niveaux :</p>
<ol>
    <li><strong>Menaces (Threats)</strong> : facteurs externes susceptibles d\'affecter la sécurité du vol (météo, relief, trafic, état de la piste, etc.).</li>
    <li><strong>Erreurs (Errors)</strong> : actions ou inactions de l\'équipage pouvant réduire les marges de sécurité.</li>
    <li><strong>États indésirables (Undesired States)</strong> : situations résultant de menaces non gérées ou d\'erreurs non détectées.</li>
</ol>

<p><em>Échelle de probabilité : Rare — Occasionnel — Probable — Fréquent</em><br/>
<em>Échelle de gravité : Mineur — Modéré — Majeur — Critique — Catastrophique</em></p>

<p>Les grilles d\'analyse détaillées ci-dessous sont générées automatiquement pour chaque activité particulière (AP) identifiée parmi les circuits de l\'exploitant. L\'exploitant est invité à compléter et adapter ces grilles en fonction de ses conditions locales d\'exploitation.</p>';

$updated = $conn->executeStatement(
    "UPDATE manex_section SET has_auto_content = true, custom_html = ? WHERE section_key = 'analyse_risques'",
    [$newCustomHtml]
);

echo "Sections analyse_risques mises à jour : {$updated}\n";
echo "Done.\n";
