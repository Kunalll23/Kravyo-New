
-- ====================================================================
-- Kravyo - Phase 8 Migration: Zero Food Waste Module
-- Run this against kravyo_db to activate Phase 8 features
-- ====================================================================

USE `kravyo_db`;

-- Zero Food Waste Discounted Listings Table
-- Already defined in schema.sql; this migration ensures it exists in live DB
CREATE TABLE IF NOT EXISTS `zero_waste_items` (
    `id`                 INT AUTO_INCREMENT PRIMARY KEY,
    `menu_item_id`       INT NOT NULL,
    `kitchen_id`         INT NOT NULL,
    `original_price`     DECIMAL(10, 2) NOT NULL,
    `discounted_price`   DECIMAL(10, 2) NOT NULL,
    `quantity_available` INT NOT NULL DEFAULT 1,
    `expiry_time`        DATETIME NOT NULL,
    `status`             ENUM('active', 'sold_out', 'expired') DEFAULT 'active',
    `created_at`         TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`kitchen_id`)   REFERENCES `kitchens`(`id`)   ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
