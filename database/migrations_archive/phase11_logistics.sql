-- Add is_busy to delivery_partners
ALTER TABLE `delivery_partners`
ADD COLUMN IF NOT EXISTS `is_busy` TINYINT(1) NOT NULL DEFAULT 0 AFTER `is_online`;

-- Add delivery coordinates to orders
ALTER TABLE `orders`
ADD COLUMN IF NOT EXISTS `delivery_latitude` DECIMAL(10, 8) NULL DEFAULT NULL AFTER `address_id`,
ADD COLUMN IF NOT EXISTS `delivery_longitude` DECIMAL(11, 8) NULL DEFAULT NULL AFTER `delivery_latitude`;
