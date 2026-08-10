-- ====================================================================
-- Kravyo - Phase 7 Migration: Tiffin Subscription System
-- Run this AFTER the base schema.sql has been applied.
-- ====================================================================

USE `kravyo_db`;

-- Customer Subscriptions Table (tracks who subscribed to which plan)
CREATE TABLE IF NOT EXISTS `customer_subscriptions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `customer_id` INT NOT NULL,
    `subscription_plan_id` INT NOT NULL,
    `kitchen_id` INT NOT NULL,
    `address_id` INT NOT NULL,
    `start_date` DATE NOT NULL,
    `end_date` DATE NOT NULL,
    `status` ENUM('active', 'paused', 'cancelled', 'completed') DEFAULT 'active',
    `payment_method` ENUM('cod', 'upi', 'card', 'netbanking') DEFAULT 'cod',
    `payment_status` ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    `total_paid` DECIMAL(10, 2) NOT NULL,
    `special_instructions` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`customer_id`) REFERENCES `users`(`id`),
    FOREIGN KEY (`subscription_plan_id`) REFERENCES `tiffin_subscriptions`(`id`),
    FOREIGN KEY (`kitchen_id`) REFERENCES `kitchens`(`id`),
    FOREIGN KEY (`address_id`) REFERENCES `addresses`(`id`)
) ENGINE=InnoDB;
