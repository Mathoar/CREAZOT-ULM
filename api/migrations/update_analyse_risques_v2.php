<?php

require dirname(__DIR__) . '/vendor/autoload.php';

use App\Kernel;

$kernel = new Kernel('prod', false);
$kernel->boot();
$conn = $kernel->getContainer()->get('doctrine')->getConnection();

$defaultMethodology = '<h3>Méthodologie d\'analyse des risques</h3>
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

<p>Les grilles d\'analyse détaillées ci-dessous sont à compléter pour chaque activité particulière (AP) identifiée parmi les circuits de l\'exploitant.</p>';

$tableTemplate = '<table>
    <thead>
        <tr>
            <th>Menace identifiée</th>
            <th>Probabilité</th>
            <th>Gravité</th>
            <th>Niveau de risque</th>
            <th>Mesures d\'atténuation</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><em>Conditions météo défavorables</em></td>
            <td><em>Probable</em></td>
            <td><em>Majeur</em></td>
            <td><em>Élevé</em></td>
            <td><em>Consultation METAR/TAF, minima définis, décision GO/NO GO</em></td>
        </tr>
        <tr>
            <td><em>Panne moteur en vol</em></td>
            <td><em>Rare</em></td>
            <td><em>Critique</em></td>
            <td><em>Élevé</em></td>
            <td><em>Maintien en vol plané, zones d\'atterrissage identifiées, suivi maintenance</em></td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
    </tbody>
</table>';

$sections = $conn->fetchAllAssociative(
    "SELECT ms.id, ms.client_id FROM manex_section ms WHERE ms.section_key = 'analyse_risques'"
);

foreach ($sections as $sec) {
    $clientId = $sec['client_id'];
    echo "Client ID: {$clientId}\n";

    $apCircuits = $conn->fetchAllAssociative(
        "SELECT c.nom, c.code, n.label AS nature
         FROM circuit c
         INNER JOIN nature n ON c.nature_id = n.id
         WHERE c.client_id = ? AND c.is_available = true AND n.is_particular_activity = true
         ORDER BY c.nom ASC",
        [$clientId]
    );

    $html = $defaultMethodology;

    if (empty($apCircuits)) {
        $html .= '<p><em>Aucun circuit associé à une activité particulière (AP) n\'a été identifié.</em></p>';
    } else {
        $html .= '<h3>Grilles d\'analyse des risques par activité particulière</h3>';

        foreach ($apCircuits as $circuit) {
            $title = ($circuit['nature'] ?? 'AP') . ' — ' . $circuit['nom'];
            if (!empty($circuit['code'])) {
                $title .= ' (' . $circuit['code'] . ')';
            }
            $html .= '<h4>' . htmlspecialchars($title) . '</h4>';
            $html .= $tableTemplate;
        }

        $html .= '<p><em>Les lignes en italique sont des exemples indicatifs. L\'exploitant doit compléter et adapter chaque grille '
            . 'en fonction des risques spécifiques à son activité et à ses conditions locales d\'exploitation.</em></p>';
    }

    $conn->executeStatement(
        "UPDATE manex_section SET has_auto_content = false, custom_html = ? WHERE id = ?",
        [$html, $sec['id']]
    );

    echo "  -> " . count($apCircuits) . " circuit(s) AP, contenu mis à jour.\n";
}

echo "Done.\n";
