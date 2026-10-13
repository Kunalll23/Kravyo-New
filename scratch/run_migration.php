<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/constants.php';
require_once CORE_PATH . '/Database.php';

try {
    $db = Database::getInstance();
    echo "Connecting to MariaDB...\n";

    // 1. Add columns if not exist
    $columns = $db->query("SHOW COLUMNS FROM kitchens LIKE 'latitude'")->fetchAll();
    if (empty($columns)) {
        $db->exec("ALTER TABLE `kitchens` ADD COLUMN `latitude` DECIMAL(10, 8) NULL DEFAULT NULL AFTER `pincode`");
        echo "✅ Added `latitude` column to kitchens.\n";
    } else {
        echo "ℹ️ `latitude` column already exists.\n";
    }

    $columns = $db->query("SHOW COLUMNS FROM kitchens LIKE 'longitude'")->fetchAll();
    if (empty($columns)) {
        $db->exec("ALTER TABLE `kitchens` ADD COLUMN `longitude` DECIMAL(11, 8) NULL DEFAULT NULL AFTER `latitude`");
        echo "✅ Added `longitude` column to kitchens.\n";
    } else {
        echo "ℹ️ `longitude` column already exists.\n";
    }

    // 2. Add index if not exist
    $indexes = $db->query("SHOW INDEX FROM kitchens WHERE Key_name = 'idx_kitchens_geo'")->fetchAll();
    if (empty($indexes)) {
        $db->exec("CREATE INDEX `idx_kitchens_geo` ON `kitchens` (`approval_status`, `latitude`, `longitude`)");
        echo "✅ Created `idx_kitchens_geo` index.\n";
    } else {
        echo "ℹ️ `idx_kitchens_geo` index already exists.\n";
    }

    echo "Migration completed successfully!\n";
} catch (Exception $e) {
    echo "❌ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
