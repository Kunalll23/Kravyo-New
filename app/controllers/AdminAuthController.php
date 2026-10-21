<?php
/**
 * Kravyo - Admin Authentication Controller
 * Handles login / logout for the dedicated `admins` table.
 * Completely separate from the customer/chef AuthController.
 */

require_once APP_PATH . '/models/Admin.php';

class AdminAuthController extends Controller {

    private Admin $adminModel;

    public function __construct() {
        $this->adminModel = new Admin();
    }

    // ─── Show Login Form ──────────────────────────────────────────────────────

    public function showLoginForm(): void {
        // If already logged in as admin, go straight to dashboard
        if (Session::has('admin_id')) {
            $this->redirect('/admin/dashboard');
        }
        $this->render('admin/login', ['title' => 'Admin Login'], 'admin_auth');
    }

    // ─── Process Login ────────────────────────────────────────────────────────

    public function login(): void {
        Middleware::verifyCsrf();

        $email    = strtolower(trim($_POST['email']    ?? ''));
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            Session::setFlash('danger', 'Please enter your email and password.');
            $this->redirect('/admin/login');
            return;
        }

        $admin = $this->adminModel->findByEmail($email);

        if (!$admin || !$this->adminModel->verifyPassword($password, $admin['password_hash'])) {
            Session::setFlash('danger', 'Invalid admin credentials.');
            $this->redirect('/admin/login');
            return;
        }

        // ── Successful admin login ────────────────────────────────────────────
        Session::set('admin_id',   (int) $admin['id']);
        Session::set('admin_name', $admin['full_name']);
        Session::set('admin_role', $admin['role'] ?? 'admin');

        // Record the login timestamp
        $this->adminModel->touchLastLogin((int) $admin['id']);

        Session::setFlash('success', 'Welcome back, ' . sanitize($admin['full_name']) . '!');
        $this->redirect('/admin/dashboard');
    }

    // ─── Logout ───────────────────────────────────────────────────────────────

    public function logout(): void {
        Middleware::verifyCsrf();

        // POST /admin/logout → detectContext() = KRAVYO_ADMIN → correct session already active.
        // destroyRole() explicitly targets KRAVYO_ADMIN, unsets all its keys,
        // destroys the session file, and expires the cookie — leaving all other
        // portal sessions (KRAVYO_CUSTOMER, KRAVYO_CHEF, KRAVYO_DELIVERY) untouched.
        Session::destroyRole('admin');
        redirect('/admin/login');
    }
}
