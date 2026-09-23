<?php
require_once 'C:\xampp\htdocs\Kravyo\config\app.php';
require_once 'C:\xampp\htdocs\Kravyo\config\constants.php';
require_once 'C:\xampp\htdocs\Kravyo\core\Database.php';
require_once 'C:\xampp\htdocs\Kravyo\core\Model.php';
require_once 'C:\xampp\htdocs\Kravyo\app\models\Recommendation.php';

$rec = new Recommendation();

echo "--- DB Stats ---\n";
$stats = $rec->getAdminStats();
foreach ($stats as $k => $v) echo "$k = $v\n";

echo "\n--- AI Health ---\n";
$h = $rec->getAiHealth();
echo "online = " . ($h['online'] ? 'YES' : 'NO') . "\n";
echo "latency_ms = " . ($h['latency_ms'] ?? 'n/a') . "\n";

echo "\n--- AI Config (from Python or null) ---\n";
$cfg = $rec->getAiConfig();
if ($cfg) {
    foreach ($cfg as $k => $v) echo "$k = $v\n";
} else {
    echo "Python offline — config unavailable\n";
}

echo "\n--- All tests complete ---\n";
