<?php
require __DIR__ . '/../config/database.php';
$db = (new Database())->getConnection();
$tables = ['users', 'addresses', 'orders', 'kitchens'];
foreach ($tables as $t) {
    echo "--- $t ---\n";
    try {
        $stmt = $db->query("DESCRIBE $t");
        if ($stmt) {
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
                echo $r['Field'] . ' ' . $r['Type'] . "\n";
            }
        }
    } catch (Exception $e) {}
}
