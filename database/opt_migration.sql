-- Kravyo Schema Optimization Migration
-- Approved changes only (skipped ID changes due to foreign key constraints).

SET FOREIGN_KEY_CHECKS=0;

-- 1. PHONE NUMBERS
ALTER TABLE `users` MODIFY `phone` CHAR(10) NOT NULL;
ALTER TABLE `pending_registrations` MODIFY `phone` CHAR(10) NOT NULL;

-- 2. PINCODE
ALTER TABLE `addresses` MODIFY `pincode` CHAR(6) NOT NULL;
ALTER TABLE `kitchens` MODIFY `pincode` CHAR(6) NOT NULL;

-- 3. FSSAI LICENSE
ALTER TABLE `kitchens` MODIFY `fssai_license` CHAR(14) DEFAULT NULL;

-- 4. EMAIL
ALTER TABLE `users` MODIFY `email` VARCHAR(50) NOT NULL;
ALTER TABLE `admins` MODIFY `email` VARCHAR(50) NOT NULL;
ALTER TABLE `pending_registrations` MODIFY `email` VARCHAR(50) NOT NULL;

-- 5. REVIEWS RATING
ALTER TABLE `reviews` MODIFY `rating` TINYINT UNSIGNED NOT NULL;

-- 6. MEALS PER DAY
ALTER TABLE `tiffin_subscriptions` MODIFY `meals_per_day` TINYINT UNSIGNED NOT NULL;

-- 7. QUANTITY FIELDS
ALTER TABLE `order_items` MODIFY `quantity` SMALLINT UNSIGNED NOT NULL;
ALTER TABLE `zero_waste_items` MODIFY `quantity_available` SMALLINT UNSIGNED NOT NULL;

-- 8. ORDER SPECIAL INSTRUCTIONS
ALTER TABLE `orders` MODIFY `special_instructions` VARCHAR(500) DEFAULT NULL;

-- 9. REVIEW TEXT
ALTER TABLE `reviews` MODIFY `review_text` VARCHAR(500) NOT NULL;

SET FOREIGN_KEY_CHECKS=1;
