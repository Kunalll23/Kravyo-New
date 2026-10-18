<?php
/**
 * Kravyo - Core Database Singleton Manager (PDO Wrapper)
 * Updated: Added transaction helpers and advisory lock support for concurrency safety.
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

    // ─── Transaction Helpers ──────────────────────────────────────────────────

    /**
     * Execute a callable inside a DB transaction with automatic deadlock retry.
     *
     * MySQL deadlock SQLSTATE: 40001 (InnoDB deadlock / lock wait timeout).
     * Retries up to $maxRetries times with exponential backoff (100ms, 200ms, 300ms).
     *
     * Usage:
     *   $result = Database::withTransaction(function() use (...) {
     *       // all DB writes here — auto rolled back on exception
     *       return $someValue;
     *   });
     */
    public static function withTransaction(callable $callback, int $maxRetries = 3): mixed {
        $attempt = 0;
        $db = self::getInstance();

        while ($attempt < $maxRetries) {
            try {
                $db->beginTransaction();
                $result = $callback();
                $db->commit();
                return $result;
            } catch (PDOException $e) {
                if ($db->inTransaction()) {
                    $db->rollBack();
                }
                // SQLSTATE 40001 = deadlock / serialization failure
                // MySQL/MariaDB driver codes: 1213 (ER_LOCK_DEADLOCK), 1205 (ER_LOCK_WAIT_TIMEOUT)
                $isDeadlock = ($e->getCode() == '40001')
                    || (isset($e->errorInfo[0]) && $e->errorInfo[0] === '40001')
                    || (isset($e->errorInfo[1]) && in_array((int)$e->errorInfo[1], [1213, 1205], true));

                if ($isDeadlock && $attempt < $maxRetries - 1) {
                    $attempt++;
                    usleep(100000 * $attempt); // 100ms, 200ms, 300ms...
                    continue;
                }
                throw $e;
            } catch (Throwable $e) {
                if ($db->inTransaction()) {
                    $db->rollBack();
                }
                throw $e;
            }
        }

        return null;
    }

    /**
     * Acquire a named MySQL advisory lock (application-level mutex).
     *
     * Returns true  — lock acquired; safe to proceed with exclusive logic.
     * Returns false — lock already held by another connection; skip.
     *
     * Usage:
     *   if (Database::acquireLock('kravyo_sweep', 0)) {
     *       try { ... } finally { Database::releaseLock('kravyo_sweep'); }
     *   }
     *
     * @param string $name            Unique lock name (max 64 chars)
     * @param int    $timeoutSeconds  0 = return immediately if locked (non-blocking)
     */
    public static function acquireLock(string $name, int $timeoutSeconds = 0): bool {
        $stmt = self::getInstance()->prepare("SELECT GET_LOCK(:name, :timeout) AS locked");
        $stmt->execute(['name' => $name, 'timeout' => $timeoutSeconds]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['locked'] ?? 0) === 1;
    }

    /**
     * Release a named MySQL advisory lock.
     */
    public static function releaseLock(string $name): void {
        $stmt = self::getInstance()->prepare("SELECT RELEASE_LOCK(:name)");
        $stmt->execute(['name' => $name]);
    }
}
