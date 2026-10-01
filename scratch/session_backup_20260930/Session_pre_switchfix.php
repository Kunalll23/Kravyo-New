<?php
/**
 * Kravyo - Core Session & CSRF Security Manager
 *
 * ─── Three-Way Concurrent Login Architecture ──────────────────────────────
 * Allows Customer, Chef, and Admin to each be logged in simultaneously
 * in different tabs of the SAME browser, with zero session interference.
 *
 * THREE independent named session cookies:
 *
 *   KRAVYO_CUSTOMER  — Customer portal  (all non-/chef, non-/admin routes)
 *   KRAVYO_CHEF      — Chef portal      (/chef/* routes)
 *   KRAVYO_ADMIN     — Admin portal     (/admin/* routes)
 *
 * Context is detected from the request URI BEFORE session_start().
 * Login actions use switchToRole() to ensure data lands in the right cookie.
 *
 * Result:
 *   Tab 1 -> Customer logged in  (KRAVYO_CUSTOMER cookie)
 *   Tab 2 -> Chef logged in      (KRAVYO_CHEF cookie)
 *   Tab 3 -> Admin logged in     (KRAVYO_ADMIN cookie)
 *   All three fully independent -- log out one, the other two remain.
 */

class Session {

    /** Session cookie for the Customer portal */
    const CUSTOMER_SESSION = 'KRAVYO_CUSTOMER';

    /** Session cookie for the Chef portal */
    const CHEF_SESSION     = 'KRAVYO_CHEF';

    /** Session cookie for the Admin portal */
    const ADMIN_SESSION    = 'KRAVYO_ADMIN';

    /**
     * Cross-session user data: populated when a Chef is logged in (KRAVYO_CHEF
     * cookie exists) but the current request opens KRAVYO_CUSTOMER (e.g. GET /).
     * This is a read-only snapshot — all session writes still go to the primary
     * session determined by the URL.
     */
    private static array $crossSessionUser = [];

    /**
     * Map a user role string to its session cookie name.
     */
    private static function roleToSessionName(string $role): string {
        return match ($role) {
            'chef'  => self::CHEF_SESSION,
            'admin' => self::ADMIN_SESSION,
            default => self::CUSTOMER_SESSION,
        };
    }

    /**
     * Detect the correct session cookie name from the current request URI.
     *
     * /admin/*  -> KRAVYO_ADMIN
     * /chef/*   -> KRAVYO_CHEF
     * everything else -> KRAVYO_CUSTOMER
     */
    public static function detectContext(): string {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        // Strip query string
        $path = parse_url($uri, PHP_URL_PATH) ?? '/';
        // Strip subfolder prefix if installed under /Kravyo/ on XAMPP
        $scriptDir  = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
        $baseFolder = str_replace('/public', '', $scriptDir);
        if ($baseFolder !== '' && $baseFolder !== '/' && str_starts_with($path, $baseFolder)) {
            $path = substr($path, strlen($baseFolder));
        }
        if ($path === '') {
            $path = '/';
        }
        // Classify by URL prefix
        if (str_starts_with($path, '/admin')) {
            return self::ADMIN_SESSION;
        }
        if (str_starts_with($path, '/chef')) {
            return self::CHEF_SESSION;
        }
        return self::CUSTOMER_SESSION;
    }

    /**
     * Start the correct session for this request.
     * Called once from public/index.php before routing.
     *
     * After starting the primary session, if we are on a Customer-context page
     * (KRAVYO_CUSTOMER) with no logged-in Customer but a KRAVYO_CHEF cookie is
     * present, we perform a targeted read-only peek at the Chef session so that
     * the header and controllers can identify the Chef across URL contexts.
     * All session writes for the request still go to the primary session.
     */
    public static function init(): void {
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.cookie_httponly', '1');
            ini_set('session.use_only_cookies', '1');
            // Name MUST be set before session_start()
            session_name(self::detectContext());
            session_start();

            // On Customer-context pages: if no Customer is logged in but a
            // Chef cookie exists, peek at KRAVYO_CHEF to load Chef identity
            // for header display and redirect logic on shared URLs (/login, /).
            if (
                session_name() === self::CUSTOMER_SESSION
                && !isset($_SESSION['user_id'])
                && isset($_COOKIE[self::CHEF_SESSION])
            ) {
                self::$crossSessionUser = self::peekSession(self::CHEF_SESSION);
            }
        }
    }

    /**
     * Temporarily open a secondary session to read user identity (and ensure a
     * CSRF token exists within it), then restore the primary session.
     *
     * Why CSRF? When a Chef is shown in the header on a Customer-URL page, the
     * logout form must POST to /chef/logout (KRAVYO_CHEF context) using the
     * Chef's own CSRF token — not the Customer session's token.
     *
     * The primary session is fully restored before returning; $crossSessionUser
     * is a plain PHP array and does not keep the secondary session open.
     *
     * @param  string $targetName  Session cookie name to peek at
     * @return array               ['user_id', 'user_name', 'user_role', '_csrf_token']
     */
    private static function peekSession(string $targetName): array {
        $primaryName = session_name();
        $primaryId   = session_id();

        // Save primary and switch to target
        session_write_close();
        session_name($targetName);
        session_start();

        // Ensure the peeked session has a CSRF token (needed for logout form)
        if (!isset($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }

        $data = [
            'user_id'     => $_SESSION['user_id']    ?? null,
            'user_name'   => $_SESSION['user_name']  ?? null,
            'user_role'   => $_SESSION['user_role']  ?? null,
            '_csrf_token' => $_SESSION['_csrf_token'],
        ];

        // Save peeked session (CSRF may have just been generated), restore primary
        session_write_close();
        session_name($primaryName);
        session_id($primaryId);   // Must be set BEFORE session_start()
        session_start();

        return $data;
    }

    /**
     * Return cross-session user data loaded during init().
     *
     * This is populated only when:
     *   - The current request is on a Customer-context URL (KRAVYO_CUSTOMER), AND
     *   - No Customer is logged in (no user_id in KRAVYO_CUSTOMER), AND
     *   - A KRAVYO_CHEF cookie is present in the browser.
     *
     * Returns an empty array if no cross-session user is active.
     * Always check ['user_id'] is non-null before trusting this data.
     *
     * @return array  Keys: user_id, user_name, user_role, _csrf_token  (or empty [])
     */
    public static function getCrossSessionUser(): array {
        return self::$crossSessionUser;
    }

    /**
     * Return which session context is currently active.
     */
    public static function currentContext(): string {
        return session_name();
    }

    /**
     * Switch the active session to the correct named cookie for a given user role.
     *
     * Called by AuthController::login() and AuthController::verifyEmail() AFTER
     * credentials are validated, BEFORE any session keys are written.
     *
     * Why: The login form lives at /login (-> KRAVYO_CUSTOMER by URL), but a Chef
     * logging in must have their data stored in KRAVYO_CHEF instead.
     *
     * This method:
     *   1. Saves any temp data already in the current session (flash, temp keys)
     *   2. Closes the current session (session_write_close) WITHOUT destroying cookie
     *   3. Sets the new session name
     *   4. Starts the new session
     *   5. Restores the saved temp data into the new session
     *
     * @param string $role  'customer' | 'chef' | 'admin'
     */
    public static function switchToRole(string $role): void {
        self::init(); // Ensure current session is active

        $targetName  = self::roleToSessionName($role);
        $currentName = session_name();

        // Already in the right context -- nothing to do
        if ($currentName === $targetName) {
            return;
        }

        // Preserve temp data we need to carry across (flash messages survive the switch)
        $savedFlash        = $_SESSION['_flash']                        ?? [];
        $savedOldEmail     = $_SESSION['_old_email']                    ?? null;
        $savedPendingEmail = $_SESSION['_pending_verification_email']   ?? null;
        $savedResetId      = $_SESSION['_reset_user_id']                ?? null;

        // Close the current session WITHOUT destroying its cookie
        // (we are switching away from it -- leaving it untouched)
        session_write_close();

        // Switch to the target session cookie
        session_name($targetName);
        session_start();

        // Restore carried-over temp data into the new session
        if (!empty($savedFlash)) {
            $_SESSION['_flash'] = array_merge($_SESSION['_flash'] ?? [], $savedFlash);
        }
        if ($savedOldEmail !== null) {
            $_SESSION['_old_email'] = $savedOldEmail;
        }
        if ($savedPendingEmail !== null) {
            $_SESSION['_pending_verification_email'] = $savedPendingEmail;
        }
        if ($savedResetId !== null) {
            $_SESSION['_reset_user_id'] = $savedResetId;
        }
    }

    // --- Standard Session Accessors -------------------------------------------

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
     * Destroy the current context's session only.
     * Does NOT touch any other portal's session cookie.
     */
    public static function destroy(): void {
        self::init();
        $cookieName = session_name();
        session_unset();
        session_destroy();
        // Expire the specific cookie in the browser
        if (isset($_COOKIE[$cookieName])) {
            setcookie($cookieName, '', time() - 3600, '/');
        }
    }

    /**
     * Destroy the session belonging to a specific role, regardless of which
     * session is currently active.  This is the correct way to log out: it
     * always targets the right cookie even when the logout POST URL (e.g.
     * /chef/logout) opens a different session context than expected.
     *
     * After destruction the primary session for this request is gone; callers
     * should redirect immediately without writing to the session.
     *
     * @param string $role  'customer' | 'chef' | 'admin'
     */
    public static function destroyRole(string $role): void {
        $targetName  = self::roleToSessionName($role);
        $currentName = (session_status() === PHP_SESSION_ACTIVE) ? session_name() : '';

        if ($currentName !== $targetName) {
            // The target session is not the one currently open; switch to it.
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_write_close();
            }
            // Only proceed if the target cookie exists in the browser
            if (!isset($_COOKIE[$targetName])) {
                return;
            }
            session_name($targetName);
            session_start();
        }

        $cookieName = session_name(); // equals $targetName
        session_unset();
        session_destroy();
        if (isset($_COOKIE[$cookieName])) {
            setcookie($cookieName, '', time() - 3600, '/');
        }
    }

    // --- Flash Messages -------------------------------------------------------

    /**
     * Set flash message (persists only for next request)
     */
    public static function setFlash(string $type, string $message): void {
        self::init();
        $_SESSION['_flash'][$type] = $message;
    }

    /**
     * Get and clear all flash messages
     */
    public static function getFlashes(): array {
        self::init();
        $flashes = $_SESSION['_flash'] ?? [];
        unset($_SESSION['_flash']);
        return $flashes;
    }

    // --- CSRF -----------------------------------------------------------------

    /**
     * Generate or return the CSRF token for the current session context.
     * Each portal (Customer, Chef, Admin) has its own independent token.
     */
    public static function generateCsrfToken(): string {
        self::init();
        if (!isset($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf_token'];
    }

    /**
     * Verify CSRF Token against the current session context.
     */
    public static function verifyCsrfToken(?string $token): bool {
        self::init();
        return isset($_SESSION['_csrf_token']) && hash_equals($_SESSION['_csrf_token'], (string)$token);
    }
}
