<?php
require 'config/app.php';
require 'config/database.php';
require 'core/Database.php';

$db = Database::getInstance();
$k = $db->query("SELECT * FROM kitchens WHERE kitchen_name LIKE '%Mehul%'")->fetch(PDO::FETCH_ASSOC);
print_r($k);

// Check column length of fssai_license
$columns = $db->query("SHOW COLUMNS FROM kitchens WHERE Field = 'fssai_license'")->fetch(PDO::FETCH_ASSOC);
print_r($columns);

// Fix the banner image
$db->query("UPDATE kitchens SET banner_image = 'default_banner.jpg', fssai_license = 'FSSAI-2024-MH-12345' WHERE kitchen_name LIKE '%Mehul%'");
echo "Updated kitchen.\n";
