-- ============================================================
-- Kravyo Phase 10: AI Recommendation Engine
-- Migration: Creates user_preferences table for storing
-- each customer's learned dietary & category affinities
-- ============================================================

-- User preference snapshot derived from order history
-- Rebuilt on-demand; manual overrides persist.
CREATE TABLE IF NOT EXISTS `user_preferences` (
    `id`                    INT(11)      NOT NULL AUTO_INCREMENT,
    `user_id`               INT(11)      NOT NULL,
    `preferred_category_id` INT(11)      DEFAULT NULL,      -- top category ordered most
    `prefers_veg`           TINYINT(1)   DEFAULT 0,
    `prefers_jain`          TINYINT(1)   DEFAULT 0,
    `prefers_diabetic`      TINYINT(1)   DEFAULT 0,
    `order_count_snapshot`  INT(11)      DEFAULT 0,         -- how many orders were used to build this
    `updated_at`            TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `user_id` (`user_id`),
    CONSTRAINT `user_preferences_ibfk_1`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `user_preferences_ibfk_2`
        FOREIGN KEY (`preferred_category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
