-- ============================================================
-- Kravyo — Delivery Partner Module Migration
-- Phase: Delivery Partner System
-- Date: 2026-10-01
-- ============================================================

-- 1. Add 'delivery' role to users table
ALTER TABLE `users`
  MODIFY COLUMN `role` ENUM('customer','chef','admin','delivery') NOT NULL DEFAULT 'customer';

-- 2. Add 'ready_for_pickup' and 'delivery_failed' to orders.order_status
ALTER TABLE `orders`
  MODIFY COLUMN `order_status` ENUM('pending','accepted','preparing','ready_for_pickup','out_for_delivery','delivered','cancelled','delivery_failed') DEFAULT 'pending';

-- 3. Add delivery-related columns to orders table
ALTER TABLE `orders`
  ADD COLUMN `delivery_partner_id` INT(11) NULL DEFAULT NULL AFTER `order_status`,
  ADD COLUMN `delivery_otp` CHAR(4) NULL DEFAULT NULL AFTER `delivery_partner_id`,
  ADD COLUMN `delivery_status` ENUM('unassigned','assigned','accepted','picked_up','out_for_delivery','delivered','failed') DEFAULT 'unassigned' AFTER `delivery_otp`,
  ADD COLUMN `delivery_accepted_at` DATETIME NULL DEFAULT NULL AFTER `delivery_status`,
  ADD COLUMN `delivery_picked_up_at` DATETIME NULL DEFAULT NULL AFTER `delivery_accepted_at`,
  ADD COLUMN `delivery_completed_at` DATETIME NULL DEFAULT NULL AFTER `delivery_picked_up_at`,
  ADD COLUMN `delivery_failed_reason` VARCHAR(255) NULL DEFAULT NULL AFTER `delivery_completed_at`;

-- 4. Create delivery_partners profile table
CREATE TABLE IF NOT EXISTS `delivery_partners` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `vehicle_type` ENUM('bicycle','scooter','motorcycle','car') NOT NULL DEFAULT 'scooter',
  `vehicle_number` VARCHAR(20) NOT NULL,
  `driving_license_number` VARCHAR(30) NOT NULL,
  `verification_status` ENUM('pending','approved','rejected','suspended') NOT NULL DEFAULT 'pending',
  `is_online` TINYINT(1) NOT NULL DEFAULT 0,
  `current_latitude` DECIMAL(10,8) NULL DEFAULT NULL,
  `current_longitude` DECIMAL(11,8) NULL DEFAULT NULL,
  `total_deliveries` INT(11) NOT NULL DEFAULT 0,
  `total_earnings` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `admin_notes` TEXT NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_delivery_user` (`user_id`),
  KEY `idx_delivery_status` (`verification_status`),
  KEY `idx_delivery_online` (`is_online`, `verification_status`),
  CONSTRAINT `fk_delivery_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Add foreign key for delivery_partner_id in orders
ALTER TABLE `orders`
  ADD KEY `idx_orders_delivery` (`delivery_partner_id`);

-- 6. Index for delivery status filtering
ALTER TABLE `orders`
  ADD KEY `idx_orders_delivery_status` (`delivery_status`);
