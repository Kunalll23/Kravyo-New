<?php
/**
 * Kravyo - Auth Controller
 */

require_once APP_PATH . '/models/User.php';
require_once APP_PATH . '/models/Kitchen.php';

class AuthController extends Controller {

    private User $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    /**
     * Show Login Form
     */
    public function showLoginForm(): void {
        // Redirect if already logged in
        if (Session::has('user_id')) {
            $this->redirectBasedOnRole(Session::get('user_role'));
        }

        $this->render('auth/login', [
            'title' => 'Login to Kravyo'
        ]);
    }

    /**
     * Process Login Submission
     */
    public function login(): void {
        Middleware::verifyCsrf();

        $email = sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            Session::setFlash('danger', 'Please enter both email and password.');
            $this->redirect('/login');
        }

        $user = $this->userModel->findByEmail($email);

        if ($user && $this->userModel->verifyPassword($password, $user['password_hash'])) {
            if ($user['status'] !== 'active') {
                Session::setFlash('danger', 'Your account is currently ' . $user['status'] . '. Please contact support.');
                $this->redirect('/login');
            }

            // Securely set session variables
            Session::set('user_id', $user['id']);
            Session::set('user_name', $user['full_name']);
            Session::set('user_role', $user['role']);

            Session::setFlash('success', 'Welcome back, ' . $user['full_name'] . '!');
            
            $this->redirectBasedOnRole($user['role']);
        } else {
            Session::setFlash('danger', 'Invalid email or password.');
            $this->redirect('/login');
        }
    }

    /**
     * Show Registration Form
     */
    public function showRegisterForm(): void {
        // Redirect if already logged in
        if (Session::has('user_id')) {
            $this->redirectBasedOnRole(Session::get('user_role'));
        }

        $this->render('auth/register', [
            'title' => 'Create a Kravyo Account'
        ]);
    }

    /**
     * Process Registration Submission
     */
    public function register(): void {
        Middleware::verifyCsrf();

        $fullName = sanitize($_POST['full_name'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $phone = sanitize($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = sanitize($_POST['role'] ?? ROLE_CUSTOMER);

        // Basic Validation
        if (empty($fullName) || empty($email) || empty($phone) || empty($password)) {
            Session::setFlash('danger', 'All fields are required.');
            $this->redirect('/register');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::setFlash('danger', 'Invalid email format.');
            $this->redirect('/register');
        }
        
        if (strlen($password) < 6) {
            Session::setFlash('danger', 'Password must be at least 6 characters long.');
            $this->redirect('/register');
        }

        // Check if email or phone already exists
        if ($this->userModel->findByEmail($email)) {
            Session::setFlash('danger', 'Email address is already registered.');
            $this->redirect('/register');
        }

        if ($this->userModel->findByPhone($phone)) {
            Session::setFlash('danger', 'Phone number is already registered.');
            $this->redirect('/register');
        }

        // Create User
        $userData = [
            'full_name' => $fullName,
            'email' => $email,
            'phone' => $phone,
            'password' => $password,
            'role' => ($role === ROLE_CHEF) ? ROLE_CHEF : ROLE_CUSTOMER,
            'status' => 'active'
        ];

        try {
            $userId = $this->userModel->register($userData);
            
            // If registering as a Chef, auto-create a pending kitchen profile
            if ($userData['role'] === ROLE_CHEF) {
                $kitchenModel = new Kitchen();
                $kitchenData = [
                    'user_id'         => $userId,
                    'kitchen_name'    => $fullName . "'s Kitchen",
                    'address'         => '',
                    'city'            => '',
                    'pincode'         => '',
                    'approval_status' => KITCHEN_STATUS_PENDING,
                    'is_open'         => 0,
                ];
                $kitchenModel->create($kitchenData);
            }

            // Auto login after registration
            Session::set('user_id', $userId);
            Session::set('user_name', $fullName);
            Session::set('user_role', $userData['role']);

            Session::setFlash('success', 'Account created successfully! Welcome to Kravyo.');
            $this->redirectBasedOnRole($userData['role']);
            
        } catch (Exception $e) {
            Session::setFlash('danger', 'An error occurred during registration. Please try again.');
            $this->redirect('/register');
        }
    }

    /**
     * Process Logout
     */
    public function logout(): void {
        Middleware::verifyCsrf();
        Session::destroy();
        $this->redirect('/');
    }

    /**
     * Helper to redirect based on user role
     */
    private function redirectBasedOnRole(string $role): void {
        switch ($role) {
            case ROLE_ADMIN:
                $this->redirect('/admin/dashboard');
                break;
            case ROLE_CHEF:
                $this->redirect('/chef/dashboard');
                break;
            case ROLE_CUSTOMER:
            default:
                $this->redirect('/');
                break;
        }
    }
}
