<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../core/Database.php';

try {
    $db = Database::getInstance();
    
    // 1. Add meal_type to subscription_deliveries and update unique index
    // e.g. breakfast, lunch, dinner
    $db->exec("ALTER TABLE subscription_deliveries ADD COLUMN meal_type ENUM('breakfast', 'lunch', 'dinner') NOT NULL DEFAULT 'lunch' AFTER delivery_date;");
    
    // Update unique index to include meal_type so customers with multiple meals per day can have daily deliveries
    try {
        $db->exec("ALTER TABLE subscription_deliveries ADD UNIQUE KEY unique_delivery_meal (customer_subscription_id, delivery_date, meal_type);");
        $db->exec("ALTER TABLE subscription_deliveries DROP INDEX unique_daily_delivery;");
    } catch (Exception $idxEx) {
        // Index might already be updated
    }
    
    // 2. Add order_type and link to orders table
    $db->exec("ALTER TABLE orders ADD COLUMN order_type ENUM('standard', 'tiffin') NOT NULL DEFAULT 'standard' AFTER order_number;");
    $db->exec("ALTER TABLE orders ADD COLUMN subscription_delivery_id INT NULL AFTER order_type;");
    
    echo "Successfully migrated database for Phase 13: Tiffin Dispatch!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
