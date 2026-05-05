<?php

require dirname(__DIR__) . '/vendor/autoload.php';

use App\Kernel;

$kernel = new Kernel('prod', false);
$kernel->boot();
$em = $kernel->getContainer()->get('doctrine')->getManager();
$conn = $em->getConnection();

// Mark existing pricing_tier rows as "legacy" (old model)
$conn->executeStatement("UPDATE pricing_tier SET tier_group = 'legacy' WHERE tier_group IS NULL");

// Get category IDs
$publicCatId = $conn->fetchOne("SELECT id FROM pricing_category WHERE slug = 'public'");
$ffplumCatId = $conn->fetchOne("SELECT id FROM pricing_category WHERE slug = 'ffplum'");

if (!$publicCatId || !$ffplumCatId) {
    die("Missing pricing categories\n");
}

$ffplumDiscount = 0.15; // 15% FFPLUM discount

// New per-aeronef pricing by tier (all-inclusive)
$tierPricing = [
    'essentiel' => [
        ['min' => 1, 'max' => 2, 'price' => 25],
        ['min' => 3, 'max' => 5, 'price' => 22],
        ['min' => 6, 'max' => 10, 'price' => 20],
        ['min' => 11, 'max' => null, 'price' => 18],
    ],
    'confort' => [
        ['min' => 1, 'max' => 2, 'price' => 49],
        ['min' => 3, 'max' => 5, 'price' => 45],
        ['min' => 6, 'max' => 10, 'price' => 40],
        ['min' => 11, 'max' => null, 'price' => 36],
    ],
    'premium' => [
        ['min' => 1, 'max' => 2, 'price' => 75],
        ['min' => 3, 'max' => 5, 'price' => 68],
        ['min' => 6, 'max' => 10, 'price' => 62],
        ['min' => 11, 'max' => null, 'price' => 56],
    ],
    'excellence' => [
        ['min' => 1, 'max' => 2, 'price' => 95],
        ['min' => 3, 'max' => 5, 'price' => 86],
        ['min' => 6, 'max' => 10, 'price' => 78],
        ['min' => 11, 'max' => null, 'price' => 70],
    ],
];

foreach ($tierPricing as $tierGroup => $brackets) {
    foreach ($brackets as $bracket) {
        foreach ([[$publicCatId, 1.0], [$ffplumCatId, 1 - $ffplumDiscount]] as [$catId, $factor]) {
            $price = round($bracket['price'] * $factor);
            $exists = $conn->fetchOne(
                "SELECT id FROM pricing_tier WHERE tier_group = ? AND min_aeronefs = ? AND pricing_category_id = ?",
                [$tierGroup, $bracket['min'], $catId]
            );
            if ($exists) {
                echo "Tier {$tierGroup} [{$bracket['min']}-" . ($bracket['max'] ?? '+') . "] cat {$catId} already exists, skipping.\n";
                continue;
            }
            $conn->insert('pricing_tier', [
                'tier_group' => $tierGroup,
                'min_aeronefs' => $bracket['min'],
                'max_aeronefs' => $bracket['max'],
                'price_per_aeronef' => $price,
                'pricing_category_id' => $catId,
            ]);
            echo "Inserted: {$tierGroup} [{$bracket['min']}-" . ($bracket['max'] ?? '+') . "] = {$price}€ (cat {$catId})\n";
        }
    }
}

// Update module_pack add-on availability
// Mark which packs are add-ons and from which tier they're available
// We'll use a new column "addon_from" to indicate the minimum tier needed to purchase as add-on
$conn->executeStatement("ALTER TABLE module_pack ADD COLUMN IF NOT EXISTS addon_from VARCHAR(20) DEFAULT NULL");
$conn->executeStatement("ALTER TABLE module_pack ADD COLUMN IF NOT EXISTS is_addon BOOLEAN DEFAULT false");

$addons = [
    'manex'         => 'essentiel',
    'notifications' => 'confort',
    'ia'            => 'confort',
    'formation'     => 'confort',
    'ia-avancee'    => 'confort',
];

foreach ($addons as $slug => $from) {
    $conn->executeStatement(
        "UPDATE module_pack SET addon_from = ?, is_addon = true WHERE slug = ?",
        [$from, $slug]
    );
    echo "Marked {$slug} as add-on from {$from}\n";
}

// Ensure non-addon packs are marked correctly
$conn->executeStatement("UPDATE module_pack SET is_addon = false WHERE is_addon IS NULL OR slug NOT IN ('manex','notifications','ia','formation','ia-avancee')");

echo "\nDone! New all-inclusive pricing model ready.\n";
