-- Kravyo Database Performance Optimization
-- Phase 11: Adds essential indexes to improve query performance on dashboards and large tables.

-- We ignore errors if indexes already exist in some environments
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO';
SET FOREIGN_KEY_CHECKS=0;

-- 1. Orders table
ALTER TABLE `orders` ADD INDEX `idx_kitchen_id` (`kitchen_id`);
ALTER TABLE `orders` ADD INDEX `idx_customer_id` (`customer_id`);
ALTER TABLE `orders` ADD INDEX `idx_order_status` (`order_status`);
ALTER TABLE `orders` ADD INDEX `idx_created_at` (`created_at`);

-- 2. Customer Subscriptions
ALTER TABLE `customer_subscriptions` ADD INDEX `idx_sub_kitchen_id` (`kitchen_id`);
ALTER TABLE `customer_subscriptions` ADD INDEX `idx_sub_customer_id` (`customer_id`);
ALTER TABLE `customer_subscriptions` ADD INDEX `idx_sub_status` (`status`);

-- 3. Users
ALTER TABLE `users` ADD INDEX `idx_user_role` (`role`);

-- 4. Kitchens
ALTER TABLE `kitchens` ADD INDEX `idx_kitchen_status` (`approval_status`);
ALTER TABLE `kitchens` ADD INDEX `idx_kitchen_user_id` (`user_id`);

-- 5. Order Items
ALTER TABLE `order_items` ADD INDEX `idx_oi_order_id` (`order_id`);
ALTER TABLE `order_items` ADD INDEX `idx_oi_menu_item_id` (`menu_item_id`);

SET FOREIGN_KEY_CHECKS=1;
SET SQL_MODE=@OLD_SQL_MODE;
