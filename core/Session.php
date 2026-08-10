<?php
/**
 * Kravyo - Core Session & CSRF Security Manager
 */

class Session {

    /**
     * Start secure session
     */
    public static function init(): void {
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.cookie_httponly', '1');
            ini_set('session.use_only_cookies', '1');
            session_start();
        }
    }

    /**
     * Set session key-value
     */
    public static function set(string $key, mixed $value): void {
        self::init();
        $_SESSION[$key] = $value;
    }

    /**
     * Get session value
     */
    public static function get(string $key, mixed $default = null): mixed {
        self::init();
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Check if session key exists
     */
    public static function has(string $key): bool {
        self::init();
        return isset($_SESSION[$key]);
    }

    /**
     * Remove session key
     */
    public static function remove(string $key): void {
        self::init();
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    /**
     * Destroy whole session
     */
    public static function destroy(): void {
        self::init();
        session_unset();
        session_destroy();
    }

    /**
     * Set flash message (persists only for next request)
     */
    public static function setFlash(string $type, string $message): void {
        self::init();
        $_SESSION['_flash'][$type] = $message;
    }

    /**
     * Get and clear flash messages
     */
    public static function getFlashes(): array {
        self::init();
        $flashes = $_SESSION['_flash'] ?? [];
        unset($_SESSION['_flash']);
        return $flashes;
    }

    /**
     * Generate CSRF Token
     */
    public static function generateCsrfToken(): string {
        self::init();
        if (!isset($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf_token'];
    }

    /**
     * Verify CSRF Token
     */
    public static function verifyCsrfToken(?string $token): bool {
        self::init();
        return isset($_SESSION['_csrf_token']) && hash_equals($_SESSION['_csrf_token'], (string)$token);
    }
}
