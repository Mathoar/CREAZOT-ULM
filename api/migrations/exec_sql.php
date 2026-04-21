<?php
require '/app/vendor/autoload.php';
use Symfony\Component\Dotenv\Dotenv;
(new Dotenv())->bootEnv('/app/.env');
use App\Kernel;
$kernel = new Kernel($_ENV['APP_ENV'], (bool) $_ENV['APP_DEBUG']);
$kernel->boot();
$conn = $kernel->getContainer()->get('doctrine.dbal.default_connection');

$sql = file_get_contents($argv[1] ?? '/tmp/patterns_sms.sql');
$sql = preg_replace('/^\s*--.*$/m', '', $sql);
$statements = array_filter(array_map('trim', preg_split('/;\s*(\n|$)/', $sql)));

$count = 0;
foreach ($statements as $stmt) {
    if ($stmt === '') {
        continue;
    }
    echo "\n>>> " . substr(preg_replace('/\s+/', ' ', $stmt), 0, 120) . "...\n";
    $rows = $conn->executeStatement($stmt);
    echo "    rows affected: $rows\n";
    $count++;
}
echo "\nTotal: $count statements executed\n";
