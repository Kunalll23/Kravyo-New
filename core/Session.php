<?php
/**
 * Kravyo - Core Session & CSRF Security Manager
 *
 * ─── Four-Way Concurrent Login Architecture ──────────────────────────────
 * Allows Customer, Chef, Delivery, and Admin to each be logged in simultaneously
 * in different tabs of the SAME browser, with zero session interference.
 *
 * THREE independent named session cookies:
 *
 *   KRAVYO_CUSTOMER  — Customer portal   (all non-/chef, non-/admin, non-/delivery routes)
 *   KRAVYO_CHEF      — Chef portal       (/chef/* routes)
 *   KRAVYO_DELIVERY  — Delivery portal   (/delivery/* routes)
 *   KRAVYO_ADMIN     — Admin portal      (/admin/* routes)
 *
 * Context is detected from the request URI BEFORE session_start().
 * Login actions use switchToRole() to ensure data lands in the right cookie.
 *
 * Result:
 *   Tab 1 -> Customer logged in   (KRAVYO_CUSTOMER cookie)
 *   Tab 2 -> Chef logged in       (KRAVYO_CHEF cookie)
 *   Tab 3 -> Delivery logged in   (KRAVYO_DELIVERY cookie)
 *   Tab 4 -> Admin logged in      (KRAVYO_ADMIN cookie)
 *   All four fully independent -- log out one, the others remain.
 */

class Session {

    /** Session cookie for the Customer portal */
    const CUSTOMER_SESSION = 'KRAVYO_CUSTOMER';

    /** Session cookie for the Chef portal */
    const CHEF_SESSION     = 'KRAVYO_CHEF';

    /** Session cookie for the Delivery portal */
    const DELIVERY_SESSION = 'KRAVYO_DELIVERY';

    /** Session cookie for the Admin portal */
    const ADMIN_SESSION    = 'KRAVYO_ADMIN';

    /** Session cookie for the Public root URL */
    const PUBLIC_SESSION   = 'KRAVYO_PUBLIC';

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
            'chef'     => self::CHEF_SESSION,
            'delivery' => self::DELIVERY_SESSION,
            'admin'    => self::ADMIN_SESSION,
            default    => self::CUSTOMER_SESSION,
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
        if (str_starts_with($path, '/public')) {
            $path = substr($path, strlen('/public'));
        }
        if ($path === '') {
            $path = '/';
        }
        // Classify by URL prefix
        if ($path === '/') {
            return self::PUBLIC_SESSION;
        }
        if (str_starts_with($path, '/admin')) {
            return self::ADMIN_SESSION;
        }
        if (str_starts_with($path, '/delivery')) {
            return self::DELIVERY_SESSION;
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
            $ctx = self::detectContext();
            session_name($ctx);

            // Auto-heal cookie collisions: if the incoming cookie for this context
            // matches any other role's cookie, regenerate ID so they never share files.
            $currentCookie = $_COOKIE[$ctx] ?? null;
            if ($currentCookie !== null) {
                $otherCookies = [
                    self::CUSTOMER_SESSION => $_COOKIE[self::CUSTOMER_SESSION] ?? null,
                    self::CHEF_SESSION     => $_COOKIE[self::CHEF_SESSION] ?? null,
                    self::DELIVERY_SESSION => $_COOKIE[self::DELIVERY_SESSION] ?? null,
                    self::ADMIN_SESSION    => $_COOKIE[self::ADMIN_SESSION] ?? null,
                ];
                unset($otherCookies[$ctx]);
                if (in_array($currentCookie, array_filter($otherCookies), true)) {
                    session_id(bin2hex(random_bytes(16)));
                }
            }

            session_start();

            // Auto-heal cross-contamination: if session data has a conflicting role for this context, clear the invalid role keys
            if ($ctx === self::CHEF_SESSION && isset($_SESSION['user_role']) && $_SESSION['user_role'] !== ROLE_CHEF) {
                unset($_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['user_role']);
            } elseif ($ctx === self::DELIVERY_SESSION && isset($_SESSION['user_role']) && $_SESSION['user_role'] !== ROLE_DELIVERY) {
                unset($_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['user_role'], $_SESSION['delivery_partner_id']);
            } elseif ($ctx === self::CUSTOMER_SESSION && isset($_SESSION['user_role']) && $_SESSION['user_role'] !== ROLE_CUSTOMER) {
                unset($_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['user_role']);
            }

        }
    }

    /**
     * Cross-session peeking is disabled to enforce strict context isolation.
     * Each browser tab relies purely on its own session context.
     *
     * @return array  Always returns []
     */
    public static function getCrossSessionUser(): array {
        return [];
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
     * @param string $role  'customer' | 'chef' | 'admin' | 'delivery'
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

        // Capture the source session's ID BEFORE closing it.
        $sourceSessionId = session_id();

        // Close the current session WITHOUT destroying its cookie
        // (we are switching away from it -- leaving it untouched)
        session_write_close();

        // Switch to the target session cookie
        session_name($targetName);

        // CRITICAL: Ensure the target role gets its own independent session file.
        //
        // Problem 1 — No cookie: After session_write_close(), PHP's internal
        //   session_id() still holds the source session's ID.  session_start()
        //   would reuse it, making both roles share one session file.
        //
        // Problem 2 — Corrupted cookie: A previous buggy switchToRole may have
        //   already set the target cookie to the SAME session ID as the source.
        //   The browser remembers that cookie, so even with the missing-cookie
        //   fix, the collision persists.
        //
        // Fix: generate a fresh ID if the target cookie is absent OR if its
        //   value matches the source session ID (i.e. collision).
        $targetCookieId = $_COOKIE[$targetName] ?? null;
        if ($targetCookieId === null || $targetCookieId === $sourceSessionId) {
            session_id(bin2hex(random_bytes(16)));
        } else {
            session_id($targetCookieId);
        }

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
     * Check if a user is currently logged in in this session context
     */
    public static function isLoggedIn(): bool {
        return self::has('user_id');
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
     * @param string $role  'customer' | 'chef' | 'admin' | 'delivery'
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
            session_id($_COOKIE[$targetName]);
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

    /**
     * Check if any flash messages are set
     */
    public static function hasFlash(): bool {
        self::init();
        return !empty($_SESSION['_flash']);
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
