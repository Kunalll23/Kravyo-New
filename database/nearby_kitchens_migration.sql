-- ============================================================
-- Kravyo: Nearby Cloud Kitchens Map Feature Migration
-- Adds latitude and longitude coordinates to kitchens table
-- ============================================================

-- Add latitude and longitude columns if they don't already exist
ALTER TABLE `kitchens` 
    ADD COLUMN IF NOT EXISTS `latitude` DECIMAL(10, 8) NULL DEFAULT NULL AFTER `pincode`,
    ADD COLUMN IF NOT EXISTS `longitude` DECIMAL(11, 8) NULL DEFAULT NULL AFTER `latitude`;

-- Add composite index on approval_status, latitude, longitude for efficient geospatial queries
CREATE INDEX IF NOT EXISTS `idx_kitchens_geo` ON `kitchens` (`approval_status`, `latitude`, `longitude`);
