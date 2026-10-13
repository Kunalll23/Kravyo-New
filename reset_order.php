<?php
define('ROOT_PATH', __DIR__);
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');
require 'config/database.php';
require 'core/Database.php';
$db = Database::getInstance();
$db->query("UPDATE orders SET 
    order_status = 'preparing', 
    dispatch_status = 'pending', 
    dispatch_round = 1, 
    dispatch_started_at = NULL, 
    delivery_partner_id = NULL, 
    delivery_otp = NULL, 
    delivery_status = 'unassigned' 
    WHERE id = 138");
$db->query("DELETE FROM delivery_offers WHERE order_id = 138");
$db->query("UPDATE delivery_partners SET is_busy = 0 WHERE id = 53");
echo "Order 138 fully reset to preparing with clean offers and partner 53 unbusy.\n";
