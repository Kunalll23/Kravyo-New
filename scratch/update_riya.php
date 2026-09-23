<?php
require 'config/app.php';
require 'config/database.php';
require 'core/Database.php';

$db = Database::getInstance();

$db->query("UPDATE users SET full_name = 'Riya' WHERE email = 'test@gmail.com'");
$db->query("UPDATE kitchens SET kitchen_name = 'Riya\'s Kitchen' WHERE kitchen_name LIKE 'test%'");

echo "Kitchen and User updated to Riya.\n";
