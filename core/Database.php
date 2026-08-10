<?php
/**
 * Kravyo - Core Database Singleton Manager (PDO Wrapper)
 */

class Database {
    private static ?PDO $instance = null;

    private function __construct() {}
    private function __clone() {}

    /**
     * Get singleton PDO connection instance
     */
    public static function getInstance(): PDO {
        if (self::$instance === null) {
            $config = require CONFIG_PATH . '/database.php';
            
            $dsn = sprintf(
                "%s:host=%s;port=%s;dbname=%s;charset=%s",
                $config['driver'],
                $config['host'],
                $config['port'],
                $config['dbname'],
                $config['charset']
            );

            try {
                self::$instance = new PDO(
                    $dsn,
                    $config['username'],
                    $config['password'],
                    $config['options']
                );
            } catch (PDOException $e) {
                if (APP_ENV === 'development') {
                    die("Database Connection Error: " . $e->getMessage());
                } else {
                    die("Database Connection Error. Please check system configuration.");
                }
            }
        }

        return self::$instance;
    }

    /**
     * Helper method to execute a query with bindings
     */
    public static function query(string $sql, array $params = []): PDOStatement {
        $db = self::getInstance();
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
}
