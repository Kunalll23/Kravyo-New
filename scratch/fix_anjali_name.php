<?php
define('CONFIG_PATH', 'c:/xampp/htdocs/Kravyo/config');
define('APP_ENV', 'development');
require 'c:/xampp/htdocs/Kravyo/core/Database.php';

$db = Database::getInstance();

$stmt = $db->prepare("UPDATE kitchens SET kitchen_name = 'Anjali''s Gujarati Rasoi' WHERE kitchen_name LIKE '%Anjali%'");
$stmt->execute();

echo "Successfully updated kitchen name to Anjali's Gujarati Rasoi\n";
