<?php
define('ROOT_PATH', 'c:/xampp/htdocs/Kravyo');
define('APP_PATH', ROOT_PATH . '/app');
define('CORE_PATH', ROOT_PATH . '/core');
define('CONFIG_PATH', ROOT_PATH . '/config');

require_once ROOT_PATH . '/config/constants.php';
require_once CORE_PATH . '/Database.php';

$db = Database::getInstance();

$accuratePrices = [
    1  => 699.00,   // Weekly 1-meal (7 meals @ ~₹100)
    2  => 1249.00,  // Weekly 2-meals (14 meals @ ~₹89)
    3  => 799.00,   // Weekly 1-meal Desi Ghee (7 meals @ ~₹114)
    4  => 2699.00,  // Monthly 1-meal (30 meals @ ~₹90)
    5  => 2549.00,  // Monthly 1-meal (30 meals @ ~₹85)
    6  => 4799.00,  // Monthly 2-meals (60 meals @ ~₹80)
    7  => 2849.00,  // Monthly 1-meal (30 meals @ ~₹95)
    8  => 1299.00,  // Weekly 2-meals (14 meals @ ~₹93)
    9  => 2399.00,  // Monthly 1-meal (30 meals @ ~₹80)
    10 => 5399.00,  // Monthly 2-meals Awadhi (60 meals @ ~₹90)
    11 => 1199.00,  // Weekly 2-meals Surati (14 meals @ ~₹85)
    12 => 4899.00,  // Monthly 2-meals (60 meals @ ~₹81)
    13 => 6499.00,  // Monthly 3-meals (90 meals @ ~₹72)
    14 => 525.00,   // Weekly 1-meal budget (7 meals @ ₹75) - fixes unrealistic ₹150
    15 => 849.00,   // Weekly 1-meal Deluxe Paneer (7 meals @ ~₹121)
    16 => 1249.00,  // Weekly 2-meals (14 meals @ ~₹89)
    17 => 1449.00   // Weekly 2-meals Punjabi Feast (14 meals @ ~₹103)
];

$stmt = $db->prepare("UPDATE tiffin_subscriptions SET price = :price WHERE id = :id");

foreach ($accuratePrices as $id => $price) {
    $stmt->execute(['id' => $id, 'price' => $price]);
    echo "Plan #$id price updated to: ₹" . number_format($price, 2) . "\n";
}

echo "\nAll 17 tiffin plans updated with accurate, market-logical prices!\n";
