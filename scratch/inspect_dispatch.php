<?php
$pdo = new PDO('mysql:host=localhost;dbname=kravyo_db', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
foreach (['delivery_partners','delivery_users','orders','notifications','kitchens'] as $t) {
    echo "=== $t ===\n";
    try {
        foreach ($pdo->query("SHOW COLUMNS FROM $t") as $c) echo "  {$c['Field']} {$c['Type']} {$c['Null']} {$c['Default']}\n";
    } catch (Exception $e) { echo "  ERR " . $e->getMessage() . "\n"; }
}
echo "=== partners ===\n";
foreach ($pdo->query("SELECT dp.id, dp.user_id, u.full_name, u.email, dp.is_online, dp.is_busy, dp.verification_status, dp.current_latitude, dp.current_longitude FROM delivery_partners dp LEFT JOIN delivery_users u ON u.id=dp.user_id") as $r) echo '  ' . json_encode($r) . "\n";
echo "=== kitchens city ===\n";
foreach ($pdo->query("SELECT id, kitchen_name, city, latitude, longitude FROM kitchens LIMIT 10") as $r) echo '  ' . json_encode($r) . "\n";
echo "=== tables ===\n";
foreach ($pdo->query("SHOW TABLES") as $r) echo '  ' . $r[0] . "\n";
