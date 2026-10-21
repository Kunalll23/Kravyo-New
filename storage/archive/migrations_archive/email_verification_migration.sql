-- ================================================================
-- Kravyo — Email OTP Verification Migration
-- File: database/email_verification_migration.sql
--
-- SAFE: Only adds new columns & a new ENUM value.
-- Does NOT drop tables, delete rows, or break existing data.
-- All 40+ existing users are marked as already-verified.
-- ================================================================

-- Step 1: Add 'pending_verification' to the status ENUM
-- The existing values (active, inactive, suspended) are preserved.
ALTER TABLE `users`
  MODIFY COLUMN `status`
    ENUM('active','inactive','suspended','pending_verification')
    NOT NULL DEFAULT 'active';

-- Step 2: Add email verification columns
ALTER TABLE `users`
  ADD COLUMN `email_verified`       TINYINT(1)    NOT NULL DEFAULT 0
      AFTER `status`,
  ADD COLUMN `email_otp_hash`       VARCHAR(255)  DEFAULT NULL
      AFTER `email_verified`,
  ADD COLUMN `email_otp_expires_at` DATETIME      DEFAULT NULL
      AFTER `email_otp_hash`,
  ADD COLUMN `email_otp_resent_at`  DATETIME      DEFAULT NULL
      AFTER `email_otp_expires_at`,
  ADD COLUMN `email_otp_attempts`   TINYINT(1)    NOT NULL DEFAULT 0
      AFTER `email_otp_resent_at`;

-- Step 3: Mark ALL existing users as already email-verified.
-- This ensures zero disruption to the 40+ existing accounts
-- (admin, chefs, customers) — they can all log in normally.
UPDATE `users`
  SET `email_verified` = 1
  WHERE `email_verified` = 0;
