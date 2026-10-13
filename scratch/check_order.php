<?php
require 'c:/xampp/htdocs/Kravyo/config/app.php';
require 'c:/xampp/htdocs/Kravyo/core/Database.php';
$db = Database::getInstance();
$stmt = $db->query("SELECT id, order_number, delivery_partner_id, delivery_status FROM orders WHERE order_number = 'KRV-20261004-07A62'");
$order = $stmt->fetch(PDO::FETCH_ASSOC);
print_r($order);

if ($order) {
    $stmt2 = $db->prepare("SELECT id, user_id, vehicle_number FROM delivery_partners WHERE id = ?");
    $stmt2->execute([$order['delivery_partner_id']]);
    $partner = $stmt2->fetch(PDO::FETCH_ASSOC);
    echo "\nPartner assigned:\n";
    print_r($partner);
    
    $stmt3 = $db->prepare("SELECT full_name FROM delivery_users WHERE id = ?");
    $stmt3->execute([$partner['user_id']]);
    echo "\nUser name:\n";
    print_r($stmt3->fetch(PDO::FETCH_ASSOC));
}
