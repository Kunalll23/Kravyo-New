-- FINAL KRAVYO DATABASE COLUMN OPTIMIZATION
-- Approved changes only. No ID changes.

SET FOREIGN_KEY_CHECKS=0;

ALTER TABLE `users` MODIFY `reset_otp_attempts` TINYINT UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE `pending_registrations` MODIFY `email_otp_attempts` TINYINT UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE `customer_subscriptions` MODIFY `total_paid` DECIMAL(8,2) NOT NULL;
ALTER TABLE `menu_items` MODIFY `price` DECIMAL(6,2) NOT NULL;
ALTER TABLE `order_items` MODIFY `unit_price` DECIMAL(6,2) NOT NULL;
ALTER TABLE `order_items` MODIFY `subtotal` DECIMAL(8,2) NOT NULL;
ALTER TABLE `orders` MODIFY `order_number` VARCHAR(30) NOT NULL;
ALTER TABLE `orders` MODIFY `total_amount` DECIMAL(8,2) NOT NULL;
ALTER TABLE `tiffin_subscriptions` MODIFY `price` DECIMAL(8,2) NOT NULL;
ALTER TABLE `zero_waste_items` MODIFY `original_price` DECIMAL(6,2) NOT NULL;
ALTER TABLE `zero_waste_items` MODIFY `discounted_price` DECIMAL(6,2) NOT NULL;

SET FOREIGN_KEY_CHECKS=1;
