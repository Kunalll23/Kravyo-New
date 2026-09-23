-- =========================================================
-- Kravyo - Admin Table Migration
-- Creates a dedicated `admins` table, separate from `users`
-- Run this once in phpMyAdmin SQL tab
-- =========================================================

CREATE TABLE IF NOT EXISTS `admins` (
    `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `full_name`     VARCHAR(120) NOT NULL,
    `email`         VARCHAR(180) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `role`          ENUM('super_admin', 'moderator', 'support') NOT NULL DEFAULT 'super_admin',
    `last_login_at` DATETIME NULL DEFAULT NULL,
    `created_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- Insert default admin account
-- Credentials: admin@kravyo.com / password: admin@1234
-- IMPORTANT: Change this password after first login!
-- =========================================================
INSERT INTO `admins` (`full_name`, `email`, `password_hash`, `role`)
VALUES (
    'Super Admin',
    'admin@kravyo.com',
    '$2y$12$eImiTXuWVxfM37uY4JANjOe5XIfTJzFDlnMm.5Q4FYi0cWLzMp3ji',
    'super_admin'
);

-- =========================================================
-- The above password hash is for: admin@1234
-- Generated with: password_hash('admin@1234', PASSWORD_DEFAULT)
-- =========================================================
