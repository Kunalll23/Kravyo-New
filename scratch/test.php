<?php
require_once "C:/xampp/htdocs/Kravyo/app/core/Database.php";
require_once "C:/xampp/htdocs/Kravyo/app/config.php"; // to define DB constants
$db = Database::getInstance();
$ordersSql = "SELECT * FROM orders WHERE dispatch_status IN ('broadcasting', 'no_rider', 'assigned')";
$orders = $db->query($ordersSql)->fetchAll();

foreach ($orders as $order) {
    if ($order['dispatch_status'] === 'assigned' && strtotime($order['updated_at']) < time() - 3600) {
        continue;
    }
    echo "ID: " . $order['id'] . " | Status: " . $order['dispatch_status'] . "\n";
}
