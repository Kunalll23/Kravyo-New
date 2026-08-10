-- ====================================================================
-- Kravyo - Cloud Kitchen Platform Database Schema
-- Target Engine: MySQL / MariaDB (XAMPP Compatible)
-- ====================================================================

CREATE DATABASE IF NOT EXISTS `kravyo_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `kravyo_db`;

-- 1. USERS TABLE (Customer, Home Chef / Seller, Admin)
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `full_name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(120) NOT NULL UNIQUE,
    `phone` VARCHAR(20) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `role` ENUM('customer', 'chef', 'admin') NOT NULL DEFAULT 'customer',
    `status` ENUM('active', 'inactive', 'suspended') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. KITCHENS TABLE (Home Chef Kitchen Profiles)
CREATE TABLE IF NOT EXISTS `kitchens` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `kitchen_name` VARCHAR(150) NOT NULL,
    `personal_story` TEXT NULL,
    `address` TEXT NOT NULL,
    `city` VARCHAR(100) NOT NULL,
    `pincode` VARCHAR(10) NOT NULL,
    `fssai_license` VARCHAR(50) NULL,
    `hygiene_badge` ENUM('none', 'verified') DEFAULT 'none',
    `hygiene_certificate_image` VARCHAR(255) NULL,
    `approval_status` ENUM('pending', 'approved', 'rejected', 'suspended') DEFAULT 'pending',
    `admin_notes` TEXT NULL,
    `is_open` TINYINT(1) DEFAULT 1,
    `banner_image` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 3. FOOD CATEGORIES TABLE
CREATE TABLE IF NOT EXISTS `categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `category_name` VARCHAR(100) NOT NULL UNIQUE,
    `description` VARCHAR(255) NULL,
    `image` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 4. MENU ITEMS TABLE
CREATE TABLE IF NOT EXISTS `menu_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `kitchen_id` INT NOT NULL,
    `category_id` INT NOT NULL,
    `item_name` VARCHAR(150) NOT NULL,
    `description` TEXT NULL,
    `price` DECIMAL(10, 2) NOT NULL,
    `image` VARCHAR(255) NULL,
    `is_veg` TINYINT(1) DEFAULT 1,
    `is_jain_available` TINYINT(1) DEFAULT 0,
    `is_diabetic_friendly` TINYINT(1) DEFAULT 0,
    `is_available` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`kitchen_id`) REFERENCES `kitchens`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 5. TIFFIN SUBSCRIPTION PLANS TABLE
CREATE TABLE IF NOT EXISTS `tiffin_subscriptions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `kitchen_id` INT NOT NULL,
    `plan_name` VARCHAR(100) NOT NULL,
    `plan_type` ENUM('weekly', 'monthly') NOT NULL,
    `price` DECIMAL(10, 2) NOT NULL,
    `description` TEXT NULL,
    `meals_per_day` INT DEFAULT 1,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`kitchen_id`) REFERENCES `kitchens`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 6. ZERO FOOD WASTE DISCOUNTED LISTINGS
CREATE TABLE IF NOT EXISTS `zero_waste_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `menu_item_id` INT NOT NULL,
    `original_price` DECIMAL(10, 2) NOT NULL,
    `discounted_price` DECIMAL(10, 2) NOT NULL,
    `quantity_available` INT NOT NULL,
    `expiry_time` DATETIME NOT NULL,
    `status` ENUM('active', 'sold_out', 'expired') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 7. CUSTOMER DELIVERY ADDRESSES
CREATE TABLE IF NOT EXISTS `addresses` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `address_type` ENUM('Home', 'Work', 'Other') DEFAULT 'Home',
    `street_address` TEXT NOT NULL,
    `landmark` VARCHAR(150) NULL,
    `city` VARCHAR(100) NOT NULL,
    `pincode` VARCHAR(10) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 8. ORDERS TABLE
CREATE TABLE IF NOT EXISTS `orders` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `order_number` VARCHAR(50) NOT NULL UNIQUE,
    `customer_id` INT NOT NULL,
    `kitchen_id` INT NOT NULL,
    `address_id` INT NOT NULL,
    `total_amount` DECIMAL(10, 2) NOT NULL,
    `payment_method` ENUM('cod', 'upi', 'card', 'netbanking') DEFAULT 'cod',
    `payment_status` ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    `order_status` ENUM('pending', 'accepted', 'preparing', 'out_for_delivery', 'delivered', 'cancelled') DEFAULT 'pending',
    `special_instructions` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`customer_id`) REFERENCES `users`(`id`),
    FOREIGN KEY (`kitchen_id`) REFERENCES `kitchens`(`id`),
    FOREIGN KEY (`address_id`) REFERENCES `addresses`(`id`)
) ENGINE=InnoDB;

-- 9. ORDER ITEMS TABLE (Line Items & Customization Options)
CREATE TABLE IF NOT EXISTS `order_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT NOT NULL,
    `menu_item_id` INT NOT NULL,
    `quantity` INT NOT NULL DEFAULT 1,
    `unit_price` DECIMAL(10, 2) NOT NULL,
    `subtotal` DECIMAL(10, 2) NOT NULL,
    `spice_level` ENUM('Low', 'Medium', 'High') DEFAULT 'Medium',
    `oil_level` ENUM('Normal', 'Less Oil') DEFAULT 'Normal',
    `is_jain` TINYINT(1) DEFAULT 0,
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items`(`id`)
) ENGINE=InnoDB;

-- 10. REVIEWS & RATINGS TABLE
CREATE TABLE IF NOT EXISTS `reviews` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT NOT NULL UNIQUE,
    `customer_id` INT NOT NULL,
    `kitchen_id` INT NOT NULL,
    `rating` INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    `review_text` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`),
    FOREIGN KEY (`customer_id`) REFERENCES `users`(`id`),
    FOREIGN KEY (`kitchen_id`) REFERENCES `kitchens`(`id`)
) ENGINE=InnoDB;
