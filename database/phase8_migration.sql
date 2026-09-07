-- ====================================================================
-- Kravyo - Phase 8 Migration: Zero Food Waste Module
-- Run this against kravyo_db to activate Phase 8 features
-- Safe to run multiple times (uses IF NOT EXISTS / IF statements)
-- ====================================================================

USE `kravyo_db`;

-- Step 1: Add kitchen_id column if it doesn't already exist
-- (The original schema.sql omitted kitchen_id from zero_waste_items)
SET @col_exists = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = 'kravyo_db'
      AND TABLE_NAME   = 'zero_waste_items'
      AND COLUMN_NAME  = 'kitchen_id'
);

-- Only run if kitchen_id is missing
SET @alter_sql = IF(@col_exists = 0,
    'ALTER TABLE zero_waste_items
     ADD COLUMN kitchen_id INT NOT NULL AFTER menu_item_id,
     ADD CONSTRAINT zwi_kitchen_fk FOREIGN KEY (kitchen_id) REFERENCES kitchens(id) ON DELETE CASCADE',
    'SELECT "kitchen_id column already exists — skipping"'
);
PREPARE stmt FROM @alter_sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Step 2: Ensure table exists for fresh installs
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

