<?php
require 'config/app.php';
require 'config/database.php';
require 'core/Database.php';

$db = Database::getInstance();

// 1. Alter table to increase length of fssai_license
$db->query("ALTER TABLE kitchens MODIFY fssai_license VARCHAR(20)");

// 2. Update the row with full FSSAI and dummy banner
$db->query("UPDATE kitchens SET 
    fssai_license = 'FSSAI-2024-MH-12345',
    banner_image = 'default_banner.jpg' 
    WHERE kitchen_name LIKE '%Mehul%'");

echo "Fixed column length and updated kitchen data.\n";
