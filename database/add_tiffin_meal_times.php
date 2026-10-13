<?php
/**
 * Migration: Add meal delivery timings to tiffin_subscriptions and subscription_deliveries
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../core/Database.php';

$db = Database::getInstance();

echo "=== Running Tiffin Meal Times Migration ===\n";

// 1. Add columns to tiffin_subscriptions
try {
    $cols = $db->query("SHOW COLUMNS FROM tiffin_subscriptions LIKE 'lunch_time'")->fetchAll();
    if (empty($cols)) {
        $db->exec("ALTER TABLE tiffin_subscriptions 
            ADD COLUMN breakfast_time VARCHAR(50) NULL DEFAULT '08:00 AM - 09:00 AM' AFTER meals_per_day,
            ADD COLUMN lunch_time VARCHAR(50) NULL DEFAULT '12:30 PM - 01:30 PM' AFTER breakfast_time,
            ADD COLUMN dinner_time VARCHAR(50) NULL DEFAULT '07:30 PM - 08:30 PM' AFTER lunch_time;");
        echo "Added breakfast_time, lunch_time, dinner_time to tiffin_subscriptions.\n";
    } else {
        echo "Columns already exist in tiffin_subscriptions.\n";
    }
} catch (Exception $e) {
    echo "tiffin_subscriptions error: " . $e->getMessage() . "\n";
}

// 2. Add column to subscription_deliveries
try {
    $cols = $db->query("SHOW COLUMNS FROM subscription_deliveries LIKE 'delivery_time'")->fetchAll();
    if (empty($cols)) {
        $db->exec("ALTER TABLE subscription_deliveries 
            ADD COLUMN delivery_time VARCHAR(50) NULL DEFAULT NULL AFTER meal_type;");
        echo "Added delivery_time to subscription_deliveries.\n";
    } else {
        echo "delivery_time already exists in subscription_deliveries.\n";
    }
} catch (Exception $e) {
    echo "subscription_deliveries error: " . $e->getMessage() . "\n";
}

// 3. Update existing records with default timings
try {
    $db->exec("UPDATE tiffin_subscriptions SET lunch_time = '12:30 PM - 01:30 PM' WHERE lunch_time IS NULL OR lunch_time = ''");
    $db->exec("UPDATE tiffin_subscriptions SET dinner_time = '07:30 PM - 08:30 PM' WHERE (meals_per_day >= 2) AND (dinner_time IS NULL OR dinner_time = '')");
    $db->exec("UPDATE tiffin_subscriptions SET breakfast_time = '08:00 AM - 09:00 AM' WHERE (meals_per_day >= 3) AND (breakfast_time IS NULL OR breakfast_time = '')");

    // Also update existing deliveries with appropriate time
    $db->exec("UPDATE subscription_deliveries SET delivery_time = '08:00 AM - 09:00 AM' WHERE meal_type = 'breakfast' AND (delivery_time IS NULL OR delivery_time = '')");
    $db->exec("UPDATE subscription_deliveries SET delivery_time = '12:30 PM - 01:30 PM' WHERE meal_type = 'lunch' AND (delivery_time IS NULL OR delivery_time = '')");
    $db->exec("UPDATE subscription_deliveries SET delivery_time = '07:30 PM - 08:30 PM' WHERE meal_type = 'dinner' AND (delivery_time IS NULL OR delivery_time = '')");

    echo "Default timings updated for existing plans and deliveries.\n";
} catch (Exception $e) {
    echo "Update defaults error: " . $e->getMessage() . "\n";
}

echo "=== Migration Finished ===\n";
