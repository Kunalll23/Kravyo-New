<?php
/**
 * Kravyo - Core Middleware Class (Authentication Guards & Access Control)
 */

abstract class Middleware {

    /**
     * Ensure user is logged in
     */
    public static function auth(): void {
        if (!Session::has('user_id')) {
            Session::setFlash('warning', 'Please log in to access this page.');
            redirect('/login');
        }
    }

    /**
     * Ensure user has specific role (customer, chef, admin)
     */
    public static function role(string ...$roles): void {
        self::auth();
        $userRole = Session::get('user_role');
        if (!in_array($userRole, $roles, true)) {
            Session::setFlash('danger', 'Unauthorized access level.');
            redirect('/');
        }
    }

    /**
     * Verify CSRF Token on POST requests
     */
    public static function verifyCsrf(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
            if (!Session::verifyCsrfToken($token)) {
                http_response_code(403);
                die('403 Forbidden - Invalid CSRF Token verification failed.');
            }
        }
    }
}
