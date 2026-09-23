-- Kravyo Database - Hash Column Optimization

SET FOREIGN_KEY_CHECKS = 0;

ALTER TABLE `admins` MODIFY `password_hash` VARCHAR(60) NOT NULL;
ALTER TABLE `pending_registrations` MODIFY `password_hash` VARCHAR(60) NOT NULL;
ALTER TABLE `pending_registrations` MODIFY `email_otp_hash` VARCHAR(60) DEFAULT NULL;
ALTER TABLE `users` MODIFY `password_hash` VARCHAR(60) NOT NULL;
ALTER TABLE `users` MODIFY `reset_otp_hash` VARCHAR(60) DEFAULT NULL;

SET FOREIGN_KEY_CHECKS = 1;
