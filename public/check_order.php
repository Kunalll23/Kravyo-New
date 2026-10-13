<?php
require_once __DIR__ . '/../config/app.php';
require_once CORE_PATH . '/Database.php';

$db = Database::getInstance();
$order = $db->query("SELECT id, kitchen_id, address_id, delivery_latitude, delivery_longitude FROM orders WHERE id = 159")->fetch(PDO::FETCH_ASSOC);

if ($order) {
    $kitchen = $db->query("SELECT latitude, longitude FROM kitchens WHERE id = " . $order['kitchen_id'])->fetch(PDO::FETCH_ASSOC);
    $address = $db->query("SELECT latitude, longitude FROM addresses WHERE id = " . $order['address_id'])->fetch(PDO::FETCH_ASSOC);
    print_r([
        'order' => $order,
        'kitchen' => $kitchen,
        'address' => $address
    ]);
} else {
    echo "Order not found";
}
