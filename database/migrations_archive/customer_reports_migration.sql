CREATE TABLE IF NOT EXISTS customer_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    kitchen_id INT NOT NULL,
    target_type ENUM('kitchen', 'dish', 'delivery') NOT NULL,
    target_id INT DEFAULT NULL,
    reason VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    status ENUM('pending', 'reviewed', 'resolved') DEFAULT 'pending',
    admin_notes TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (kitchen_id) REFERENCES kitchens(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
