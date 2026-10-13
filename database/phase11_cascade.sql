ALTER TABLE `orders`
ADD COLUMN `offered_driver_id` INT(11) NULL DEFAULT NULL AFTER `delivery_partner_id`,
ADD COLUMN `offer_expires_at` DATETIME NULL DEFAULT NULL AFTER `offered_driver_id`,
ADD COLUMN `rejected_by_drivers` JSON NULL DEFAULT NULL AFTER `offer_expires_at`;
