<?php
define('CONFIG_PATH', 'c:/xampp/htdocs/Kravyo/config');
define('APP_ENV', 'development');
require 'c:/xampp/htdocs/Kravyo/core/Database.php';

$db = Database::getInstance();

$updates = [
    'Paneer Butter Masala' => 'pexels-chanwalrus-958545.jpg',
    'Kadai Paneer'         => 'pexels-dhanno-23547666.jpg',
    'Palak Paneer'         => 'pexels-dhiraj-jain-207743066-12737920.jpg',
    'Shahi Paneer'         => 'pexels-lalit-bali-3608084-39044714.jpg'
];

$stmt = $db->prepare("UPDATE menu_items SET image = ? WHERE item_name = ?");

foreach ($updates as $name => $img) {
    $stmt->execute([$img, $name]);
    echo "Updated $name with $img\n";
}

echo "All images assigned!\n";
