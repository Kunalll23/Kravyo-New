<?php
require 'config/app.php';
require 'config/database.php';
require 'core/Database.php';

$db = Database::getInstance();
$cols = $db->query('SHOW COLUMNS FROM orders')->fetchAll(PDO::FETCH_ASSOC);
print_r(array_column($cols, 'Field'));

require 'app/models/Order.php';
$order = new Order();
print_r($order->findAllWithDetails(null, 1));
