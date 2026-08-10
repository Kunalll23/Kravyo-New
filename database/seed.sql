-- ====================================================================
-- Kravyo - Development Seed Data
-- Default password for all seed accounts: 'Password123'
-- Password hash generated using password_hash('Password123', PASSWORD_DEFAULT)
-- ====================================================================

USE `kravyo_db`;

-- 1. Insert Initial System Users
INSERT INTO `users` (`id`, `full_name`, `email`, `phone`, `password_hash`, `role`, `status`) VALUES
(1, 'System Administrator', 'admin@kravyo.com', '9876543210', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe1T9D4Z9S8H1fS5B7p5W0Y6.j5N5a7yW', 'admin', 'active'),
(2, 'Sunita Sharma (Home Chef)', 'sunita@kravyo.com', '9876543211', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe1T9D4Z9S8H1fS5B7p5W0Y6.j5N5a7yW', 'chef', 'active'),
(3, 'Ankit Kumar (Customer)', 'customer@kravyo.com', '9876543212', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe1T9D4Z9S8H1fS5B7p5W0Y6.j5N5a7yW', 'customer', 'active');

-- 2. Insert Home Kitchen Profile
INSERT INTO `kitchens` (`id`, `user_id`, `kitchen_name`, `personal_story`, `address`, `city`, `pincode`, `fssai_license`, `hygiene_badge`, `approval_status`, `is_open`) VALUES
(1, 2, 'Sunita\'s Kitchen', 'Preparing traditional North & West Indian home-cooked meals with low oil and pure spices since 2018.', '102 Green Park Society, Ring Road', 'Surat', '395007', '21522001000123', 'verified', 'approved', 1);

-- 3. Insert Default Food Categories
INSERT INTO `categories` (`id`, `category_name`, `description`) VALUES
(1, 'Thali & Combo Meals', 'Complete wholesome home thalis'),
(2, 'Rotis & Parathas', 'Freshly rolled whole wheat breads'),
(3, 'Sabzi & Curries', 'Authentic home-cooked gravies'),
(4, 'Tiffin Subscriptions', 'Daily monthly meal plans');

-- 4. Insert Menu Items
INSERT INTO `menu_items` (`id`, `kitchen_id`, `category_id`, `item_name`, `description`, `price`, `is_veg`, `is_jain_available`, `is_diabetic_friendly`, `is_available`) VALUES
(1, 1, 1, 'Homestyle Special Guj/Punjabi Thali', '4 Phulkas, 2 Sabzi (Paneer + Dal Fry), Jeera Rice, Salad, Sweet & Curd', 140.00, 1, 1, 0, 1),
(2, 1, 2, 'Aloo Stuffed Paratha with White Butter', '2 Whole wheat Aloo Parathas with homemade butter & pickle', 90.00, 1, 0, 0, 1),
(3, 1, 3, 'Low-Oil Dal Tadka', 'Authentic yellow arhar dal tempered with ghee and cumin', 80.00, 1, 1, 1, 1);
