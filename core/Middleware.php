<?php
/**
 * Kravyo - Core Middleware Class (Authentication Guards & Access Control)
 *
 * Enforces strict role context isolation:
 * - Customer context  -> /login, /
 * - Chef context      -> /chef/login, /chef/dashboard
 * - Admin context     -> /admin/login, /admin/dashboard
 * - Delivery context  -> /delivery/login, /delivery/dashboard
 *
 * A role is NEVER redirected to another role's login or dashboard!
 */

abstract class Middleware {

    /**
     * Get the dedicated login URL for the current active context
     */
    public static function getContextLoginUrl(): string {
        $ctx = Session::currentContext();
        return match ($ctx) {
            Session::CHEF_SESSION     => '/chef/login',
            Session::DELIVERY_SESSION => '/delivery/login',
            Session::ADMIN_SESSION    => '/admin/login',
            default                   => '/login',
        };
    }

    /**
     * Get the home/dashboard URL for the current active context
     */
    public static function getContextHomeUrl(): string {
        $ctx = Session::currentContext();
        return match ($ctx) {
            Session::CHEF_SESSION     => '/chef/dashboard',
            Session::DELIVERY_SESSION => '/delivery/dashboard',
            Session::ADMIN_SESSION    => '/admin/dashboard',
            Session::PUBLIC_SESSION   => '/',
            default                   => '/home',
        };
    }

    /**
     * Ensure user is logged in within the current context
     */
    public static function auth(): void {
        $ctx = Session::currentContext();
        if ($ctx === Session::ADMIN_SESSION) {
            self::adminAuth();
            return;
        }

        if (!Session::has('user_id')) {
            Session::setFlash('warning', 'Please log in to access this page.');
            redirect(self::getContextLoginUrl());
        }
    }

    /**
     * Ensure user has specific role (customer, chef, admin, delivery)
     */
    public static function role(string ...$roles): void {
        if (!Session::has('user_id')) {
            Session::setFlash('warning', 'Please log in to access this page.');
            redirect(self::getContextLoginUrl());
        }

        $userRole = Session::get('user_role');
        if (!in_array($userRole, $roles, true)) {
            Session::setFlash('danger', 'Unauthorized access level.');
            redirect(self::getContextHomeUrl());
        }
    }

    /**
     * Ensure the request comes from an authenticated admin
     * (checks the dedicated admin_id session key, not the users table role)
     */
    public static function adminAuth(): void {
        if (!Session::has('admin_id')) {
            Session::setFlash('warning', 'Please log in to access the admin panel.');
            redirect('/admin/login');
        }
    }

    /**
     * Ensure the request comes from an authenticated delivery partner
     */
    public static function deliveryAuth(): void {
        if (!Session::has('user_id') || Session::get('user_role') !== ROLE_DELIVERY) {
            Session::setFlash('warning', 'Please log in to access the delivery portal.');
            redirect('/delivery/login');
        }
    }

    /**
     * Ensure the request comes from an authenticated chef
     */
    public static function chefAuth(): void {
        if (!Session::has('user_id') || Session::get('user_role') !== ROLE_CHEF) {
            Session::setFlash('warning', 'Please log in to access the chef portal.');
            redirect('/chef/login');
        }
    }

    /**
     * Verify CSRF Token on POST requests
     */
    public static function verifyCsrf(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['_csrf'] ?? $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
            if (!Session::verifyCsrfToken($token)) {
                $expected = Session::get('_csrf_token');
                $ctx = Session::currentContext();
                $uri = $_SERVER['REQUEST_URI'];
                error_log("[CSRF_FAIL] Context: $ctx | URI: $uri | Expected: $expected | Received: $token | _POST: " . print_r($_POST, true));

                if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                    http_response_code(403);
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Security token expired. Please refresh the page.']);
                    exit;
                }
                http_response_code(403);
                die('403 Forbidden - Invalid CSRF Token verification failed.');
            }
        }
    }
}
