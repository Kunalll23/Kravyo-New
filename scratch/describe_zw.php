<?php
require 'C:\xampp\htdocs\Kravyo\config\app.php';
require 'C:\xampp\htdocs\Kravyo\config\constants.php';
require 'C:\xampp\htdocs\Kravyo\core\Database.php';
$db = Database::getInstance();
$r = $db->query('DESCRIBE zero_waste_items');
print_r($r->fetchAll(PDO::FETCH_COLUMN));
