-- Kravyo Notification System — Database Migration
-- Creates the `notifications` table for Modules 1.13, 2.8, 3.11

CREATE TABLE IF NOT EXISTS `notifications` (
  `id`          INT(11) NOT NULL AUTO_INCREMENT,
  `user_id`     INT(11) DEFAULT NULL COMMENT 'NULL = broadcast to all users',
  `title`       VARCHAR(100) NOT NULL,
  `message`     VARCHAR(500) NOT NULL,
  `type`        ENUM('order_update','promotion','system_alert','kitchen_update') NOT NULL DEFAULT 'system_alert',
  `is_read`     TINYINT(1) NOT NULL DEFAULT 0,
  `link`        VARCHAR(255) DEFAULT NULL COMMENT 'Optional URL to navigate to on click',
  `created_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_read` (`user_id`, `is_read`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
