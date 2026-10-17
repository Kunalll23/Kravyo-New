-- ================================================================
-- Kravyo — Password Reset OTP Migration
-- File: database/password_reset_migration.sql
--
-- SAFE: Only ADDS new nullable columns to the users table.
-- Does NOT drop tables, delete rows, or change existing columns.
-- ================================================================

ALTER TABLE `users`
  ADD COLUMN `reset_otp_hash`       VARCHAR(255)  DEFAULT NULL  AFTER `status`,
  ADD COLUMN `reset_otp_expires_at` DATETIME      DEFAULT NULL  AFTER `reset_otp_hash`,
  ADD COLUMN `reset_otp_resent_at`  DATETIME      DEFAULT NULL  AFTER `reset_otp_expires_at`,
  ADD COLUMN `reset_otp_attempts`   TINYINT(1)    NOT NULL DEFAULT 0 AFTER `reset_otp_resent_at`;
