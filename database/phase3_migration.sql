-- ====================================================================
-- Kravyo - Phase 3 Migration Script
-- Run this on your existing kravyo_db to add Phase 3 columns
-- ====================================================================

USE `kravyo_db`;

-- Add admin_notes column to kitchens table (stores approval/rejection reason)
ALTER TABLE `kitchens`
    ADD COLUMN `admin_notes` TEXT NULL AFTER `approval_status`;

-- Add hygiene_certificate_image column to kitchens table (uploaded document path)
ALTER TABLE `kitchens`
    ADD COLUMN `hygiene_certificate_image` VARCHAR(255) NULL AFTER `hygiene_badge`;
