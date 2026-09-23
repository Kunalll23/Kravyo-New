<?php
require 'config/app.php';
require 'config/database.php';
require 'core/Database.php';

$db = Database::getInstance();
$statuses = $db->query('SELECT DISTINCT payment_status FROM orders')->fetchAll(PDO::FETCH_ASSOC);
print_r($statuses);
