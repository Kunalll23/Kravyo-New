<?php
define('ROOT_PATH', 'c:/xampp/htdocs/Kravyo');
define('APP_PATH', ROOT_PATH . '/app');
define('CORE_PATH', ROOT_PATH . '/core');
define('CONFIG_PATH', ROOT_PATH . '/config');

require_once ROOT_PATH . '/config/constants.php';
require_once CORE_PATH . '/Database.php';

$db = Database::getInstance();

$itemsIncluded = [
    1 => 'Bagara Rice, 3 Phulkas, Khatti Dal, Veg Korma, Curd, Papad',
    2 => '4 Tawa Rotis, Dal Makhani / Rajma, Seasonal Subzi, Jeera Rice, Salad',
    3 => '4 Desi Ghee Rotis, Gatte Ki Sabzi, Panchmel Dal, Basmati Rice, Chutney',
    4 => '3 Chapatis, Varan Bhaat, Aloo/Bhindi Bhaji, Usal, Koshimbir, Papad',
    5 => 'Steamed Rice, Madras Sambar, Rasam, Poriyal, Kootu, Appalam, Curd',
    6 => '4 Wheat Rotis, Chana Dal, Aloo Bhujia, Baingan Bharta, Jeera Rice',
    7 => '4 Phulkas, Panchkuta/Gatta Curry, Dal Fry, Steamed Rice, Boondi Raita, Gur',
    8 => '2 Bajra Rotla / 4 Phulkas, Sev Tameta / Ringna Olo, Dal-Bhat, Chaas',
    9 => 'Steamed Rice, Andhra Pappu, Gongura Pachadi, Dry Veg Fry, Curd, Papad',
    10 => '3 Parathas, Paneer Lababdar, Dal Sultani, Dum Pulao, Mint Raita',
    11 => '4 Butter Phulkas, Rasawala Sabzi, Gujarati Dal, Rice, Masala Chaas',
    12 => '4 Multigrain Rotis, Paneer Handi, Dal Tadka, Jeera Rice, Curd, Salad',
    13 => 'Breakfast: Poha/Paratha | Lunch: 4 Rotis, Paneer, Dal, Rice, Sweet | Dinner: Khichdi-Kadhi/Rotis',
    14 => '3 Wheat Rotis, Seasonal Sukhi Sabzi, Dal Fry, Plain Rice',
    15 => '4 Ghee Phulkas, Shahi Paneer, Dal Tadka, Jeera Rice, Fresh Salad',
    16 => '4 Phulkas, Seasonal Dry Subzi, Dal Tadka, Steamed Rice, Pickle',
    17 => '4 Tawa Rotis, Pindi Chole / Paneer, Dal Makhani, Jeera Rice, Lachha Pyaz'
];

$stmt = $db->prepare("UPDATE tiffin_subscriptions SET description = :description WHERE id = :id");

foreach ($itemsIncluded as $id => $desc) {
    $stmt->execute(['id' => $id, 'description' => $desc]);
    echo "Plan #$id updated: $desc\n";
}

echo "\nAll 17 tiffin plan descriptions updated to concise item inclusions successfully!\n";
