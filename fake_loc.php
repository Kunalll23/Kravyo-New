<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/core/Database.php';

$db = Database::getInstance();
$stmt = $db->query("SELECT delivery_partner_id, kitchen_id FROM orders WHERE id=110");
$order = $stmt->fetch();
if($order && $order['delivery_partner_id']){
    $dpId = $order['delivery_partner_id'];
    // get kitchen location
    $stmt = $db->query("SELECT latitude, longitude FROM kitchens WHERE id=" . $order['kitchen_id']);
    $kitchen = $stmt->fetch();
    $lat = $kitchen['latitude'] ?? 26.9124;
    $lng = $kitchen['longitude'] ?? 75.7873;
    
    // offset slightly for visual separation
    $lat -= 0.005;
    $lng -= 0.005;

    $db->query("UPDATE delivery_partners SET current_latitude=$lat, current_longitude=$lng, location_updated_at=NOW() WHERE id=$dpId");
    echo "Updated fake location for DP $dpId";
}
