<?php

require dirname(__DIR__) . '/vendor/autoload.php';

use App\Kernel;

$kernel = new Kernel('prod', false);
$kernel->boot();
$conn = $kernel->getContainer()->get('doctrine')->getManager()->getConnection();

$clients = $conn->fetchAllAssociative(
    'SELECT id, consent_text FROM client WHERE has_passenger_registration = true'
);

foreach ($clients as $client) {
    $html = '<h3>Enregistrement des passagers</h3>
<p>L\'exploitant utilise un système d\'enregistrement en ligne des passagers via la plateforme Logic\'Ciel. Avant chaque vol, les passagers sont invités à s\'enregistrer en fournissant leurs informations personnelles (nom, prénom, poids, coordonnées).</p>
<h4>Consentement et acceptation des conditions</h4>
<p>Lors de l\'enregistrement, chaque passager doit obligatoirement prendre connaissance et accepter les conditions suivantes avant de pouvoir finaliser son inscription :</p>';

    if (!empty($client['consent_text'])) {
        $html .= '<blockquote style="border-left: 3px solid #4a5568; padding: 8px 12px; margin: 10px 0; background: #f7fafc; font-style: italic;">'
            . htmlspecialchars($client['consent_text'], ENT_QUOTES, 'UTF-8')
            . '</blockquote>';
    }

    $html .= '<p>Ce dispositif permet de garantir la traçabilité des passagers, le respect de la réglementation en matière de consentement éclairé, et la bonne gestion des masses et du centrage de l\'aéronef.</p>';

    $count = $conn->executeStatement(
        'UPDATE manex_section SET custom_html = :html WHERE section_key = :key AND client_id = :clientId AND custom_html IS NULL',
        ['html' => $html, 'key' => 'procedures_pax', 'clientId' => $client['id']]
    );

    echo "Client {$client['id']}: procedures_pax — $count row(s) updated\n";
}

echo "Done.\n";
