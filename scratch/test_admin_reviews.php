<?php
require_once 'C:\xampp\htdocs\Kravyo\config\app.php';
require_once 'C:\xampp\htdocs\Kravyo\config\constants.php';
require_once 'C:\xampp\htdocs\Kravyo\core\Database.php';
require_once 'C:\xampp\htdocs\Kravyo\core\Model.php';
require_once 'C:\xampp\htdocs\Kravyo\app\models\Review.php';

$reviewModel = new Review();

echo "Testing findAllWithDetails...\n";
try {
    $reviews = $reviewModel->findAllWithDetails();
    echo "SUCCESS: Found " . count($reviews) . " reviews.\n";
    if (count($reviews) > 0) {
        echo "First review details:\n";
        echo "- ID: " . $reviews[0]['id'] . "\n";
        echo "- Customer Name: " . $reviews[0]['customer_name'] . "\n";
        echo "- Kitchen Name: " . $reviews[0]['kitchen_name'] . "\n";
        echo "- Rating: " . $reviews[0]['rating'] . "\n";
    }
} catch (Exception $e) {
    echo "FAIL: Query error: " . $e->getMessage() . "\n";
}

echo "\nCompleted.\n";
