<?php
require 'c:/xampp/htdocs/Kravyo/config/app.php';
require 'c:/xampp/htdocs/Kravyo/core/Database.php';
$db = Database::getInstance();
$stmt = $db->query('SELECT is_online, location_updated_at FROM delivery_partners WHERE user_id = 53');
print_r($stmt->fetch());
