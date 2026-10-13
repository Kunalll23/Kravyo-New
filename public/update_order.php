<?php
require_once __DIR__ . '/../config/app.php';
require_once CORE_PATH . '/Database.php';

$db = Database::getInstance();
$db->query("UPDATE orders SET delivery_latitude = 21.19044770, delivery_longitude = 72.86680490 WHERE id = 159");
echo "Order 159 updated!";
