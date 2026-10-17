-- ================================================================
-- Kravyo — Email Verification V2 Migration (No Pending Users)
-- File: database/email_verification_v2_migration.sql
-- ================================================================

-- Step 1: Clean up any unverified users inserted during recent testing
DELETE FROM `users` WHERE `status` = 'pending_verification';

-- Step 2: Drop the OTP columns from the users table (we won't use them here anymore)
ALTER TABLE `users`
  DROP COLUMN `email_verified`,
  DROP COLUMN `email_otp_hash`,
  DROP COLUMN `email_otp_expires_at`,
  DROP COLUMN `email_otp_resent_at`,
  DROP COLUMN `email_otp_attempts`;

-- Step 3: Revert the status ENUM back to its original state
-- (MariaDB allows modifying ENUMs as long as existing data doesn't violate it.
-- Since we deleted 'pending_verification' rows, this is safe).
ALTER TABLE `users`
  MODIFY COLUMN `status`
    ENUM('active','inactive','suspended')
    NOT NULL DEFAULT 'active';

-- Step 4: Create the new pending_registrations table
CREATE TABLE `pending_registrations` (
  `email` varchar(120) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('customer','chef','admin') NOT NULL DEFAULT 'customer',
  
  `email_otp_hash` varchar(255) DEFAULT NULL,
  `email_otp_expires_at` datetime DEFAULT NULL,
  `email_otp_resent_at` datetime DEFAULT NULL,
  `email_otp_attempts` tinyint(1) NOT NULL DEFAULT 0,
  
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
