<?php

require dirname(__DIR__) . '/vendor/autoload.php';

use App\Kernel;

$kernel = new Kernel('prod', false);
$kernel->boot();
$em = $kernel->getContainer()->get('doctrine')->getManager();
$conn = $em->getConnection();

// Insert new packs
$newPacks = [
    [
        'name' => 'Manex',
        'slug' => 'manex',
        'description' => "Manuel d'exploitation réglementaire",
        'modules' => json_encode(['hasManex']),
        'is_default' => false,
        'sort_order' => 8,
        'tier_group' => 'essentiel',
        'tier_order' => 4,
        'features_list' => 'Génération automatique du MANEX,Sections personnalisables,Export PDF,Historique des versions',
    ],
    [
        'name' => 'Notifications',
        'slug' => 'notifications',
        'description' => 'SMS et planification des envois',
        'modules' => json_encode(['hasSMS', 'hasPlanification']),
        'is_default' => false,
        'sort_order' => 9,
        'tier_group' => 'premium',
        'tier_order' => 1,
        'features_list' => 'Envoi SMS groupé,Planification J-1,Briefing passager,Page publique réservation,Suivi statut livraison',
    ],
    [
        'name' => 'Intelligence Artificielle',
        'slug' => 'ia',
        'description' => 'Briefing IA et analyse opérationnelle',
        'modules' => json_encode(['hasAI', 'hasCams']),
        'is_default' => false,
        'sort_order' => 10,
        'tier_group' => 'premium',
        'tier_order' => 2,
        'features_list' => 'Briefing météo IA (KIMI),Score OPS automatique,Interprétation NOTAM,Caméras terrain intégrées',
    ],
    [
        'name' => 'Formation',
        'slug' => 'formation',
        'description' => 'Module formation pilote',
        'modules' => json_encode(['hasTraining']),
        'is_default' => false,
        'sort_order' => 11,
        'tier_group' => 'premium',
        'tier_order' => 3,
        'features_list' => 'Leçons et programmes,Suivi progression élèves,Validation instructeur,Carnets de formation',
    ],
    [
        'name' => 'IA Avancée',
        'slug' => 'ia-avancee',
        'description' => 'Assistants IA autonomes',
        'modules' => json_encode(['hasAiReservationAssistant', 'hasVoiceAssistant']),
        'is_default' => false,
        'sort_order' => 12,
        'tier_group' => 'excellence',
        'tier_order' => 1,
        'features_list' => 'Assistant email autonome,Prise de réservation par IA,Assistant vocal Vapi,Répondeur intelligent 24/7',
    ],
];

foreach ($newPacks as $pack) {
    $exists = $conn->fetchOne("SELECT id FROM module_pack WHERE slug = ?", [$pack['slug']]);
    if ($exists) {
        echo "Pack '{$pack['name']}' already exists (id={$exists}), skipping insert.\n";
        continue;
    }
    $conn->insert('module_pack', $pack, [
        'is_default' => 'boolean',
    ]);
    echo "Inserted pack: {$pack['name']}\n";
}

// Assign tier_group to existing packs
$tierMapping = [
    'base'         => ['tier_group' => 'essentiel', 'tier_order' => 1, 'features_list' => 'Dashboard opérationnel,Gestion flotte aéronefs,Suivi vols et heures,Carnet de vol numérique,Météo METAR/TAF'],
    'reservations' => ['tier_group' => 'confort',   'tier_order' => 1, 'features_list' => 'Réservations en ligne,Options tarifaires (photos vidéos...),Confirmations email automatiques,Calendrier interactif'],
    'commerce'     => ['tier_group' => 'confort',   'tier_order' => 2, 'features_list' => 'Bons cadeaux,Boutique Wix intégrée,Gestion partenaires/revendeurs,Suivi commissions'],
    'passagers'    => ['tier_group' => 'confort',   'tier_order' => 3, 'features_list' => 'Inscription passagers RGPD,Fiche poids et contact urgence,Tracking origine client,Statistiques fréquentation'],
    'finances'     => ['tier_group' => 'confort',   'tier_order' => 4, 'features_list' => 'Suivi encaissements,Gestion impayés,Suivi dépenses (carburant maintenance),Tableau de bord financier'],
    'tracking'     => ['tier_group' => 'confort',   'tier_order' => 5, 'features_list' => 'Position GPS temps réel,Historique des trajectoires,Alertes géofencing,Compatible Microtrak'],
    'avance'       => ['tier_group' => 'essentiel', 'tier_order' => 3, 'features_list' => 'Gestion atterrissages,Carnets individuels pilote,Mise à jour groupée réservations,Consultation NOTAM'],
];

foreach ($tierMapping as $slug => $data) {
    $conn->executeStatement(
        "UPDATE module_pack SET tier_group = ?, tier_order = ?, features_list = ? WHERE slug = ?",
        [$data['tier_group'], $data['tier_order'], $data['features_list'], $slug]
    );
    echo "Updated tier_group for pack: {$slug}\n";
}

// Add module_pack_price for new packs (both Tarif Public and FFPLUM)
$publicCatId = $conn->fetchOne("SELECT id FROM pricing_category WHERE slug = 'public'");
$ffplumCatId = $conn->fetchOne("SELECT id FROM pricing_category WHERE slug = 'ffplum'");

$packPrices = [
    'manex'         => ['public' => 15, 'ffplum' => 13],
    'notifications' => ['public' => 20, 'ffplum' => 17],
    'ia'            => ['public' => 25, 'ffplum' => 21],
    'formation'     => ['public' => 15, 'ffplum' => 13],
    'ia-avancee'    => ['public' => 30, 'ffplum' => 26],
];

foreach ($packPrices as $slug => $prices) {
    $packId = $conn->fetchOne("SELECT id FROM module_pack WHERE slug = ?", [$slug]);
    if (!$packId) continue;

    foreach ([[$publicCatId, $prices['public']], [$ffplumCatId, $prices['ffplum']]] as [$catId, $price]) {
        if (!$catId) continue;
        $exists = $conn->fetchOne(
            "SELECT id FROM module_pack_price WHERE module_pack_id = ? AND pricing_category_id = ?",
            [$packId, $catId]
        );
        if ($exists) {
            echo "Price already exists for {$slug} / cat {$catId}, skipping.\n";
            continue;
        }
        $conn->insert('module_pack_price', [
            'module_pack_id' => $packId,
            'pricing_category_id' => $catId,
            'monthly_price' => $price,
        ]);
        echo "Added price {$price}€ for {$slug} (cat {$catId})\n";
    }
}

echo "\nDone! New pricing structure ready.\n";
