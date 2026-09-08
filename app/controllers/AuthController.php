<?php
/**
 * Kravyo — Auth Controller (V2 - No Pending Users)
 *
 * Registration flow:
 *   POST /register → store in pending_registrations table
 *                 → generate OTP → send email → redirect to /verify-email
 *
 * Verification flow:
 *   GET  /verify-email  → show OTP form
 *   POST /verify-email  → check OTP → insert to `users` table → activate → redirect
 *   POST /resend-email-otp → cooldown check → generate new OTP → send email
 */

require_once APP_PATH . '/models/User.php';
require_once APP_PATH . '/models/Kitchen.php';
require_once APP_PATH . '/models/EmailVerification.php';
require_once APP_PATH . '/models/PasswordReset.php';

class AuthController extends Controller {

    private User                $userModel;
    private EmailVerification   $emailVerModel;
    private PasswordReset       $passwordResetModel;

    public function __construct() {
        $this->userModel           = new User();
        $this->emailVerModel       = new EmailVerification();
        $this->passwordResetModel  = new PasswordReset();
    }

    // ─── Login ────────────────────────────────────────────────────────────────

    public function showLoginForm(): void {
        if (Session::has('user_id')) {
            $this->redirectBasedOnRole(Session::get('user_role'));
        }
        $this->render('auth/login', ['title' => 'Login to Kravyo']);
    }

    public function login(): void {
        Middleware::verifyCsrf();

        $email    = sanitize(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            Session::setFlash('danger', 'Please enter both email and password.');
            Session::set('_old_email', $email);
            $this->redirect('/login');
            return;
        }

        $user = $this->userModel->findByEmail($email);

        if (!$user || !$this->userModel->verifyPassword($password, $user['password_hash'])) {
            Session::setFlash('danger', 'Invalid email or password.');
            Session::set('_old_email', $email);
            $this->redirect('/login');
            return;
        }

        // ── Check for non-active statuses (inactive/suspended) ───────────────
        if ($user['status'] !== 'active') {
            Session::setFlash('danger',
                'Your account is currently <strong>' . sanitize($user['status']) . '</strong>. ' .
                'Please contact support.'
            );
            Session::set('_old_email', $email);
            $this->redirect('/login');
            return;
        }

        // ── Successful login ──────────────────────────────────────────────────
        Session::set('user_id',   (int) $user['id']);
        Session::set('user_name', $user['full_name']);
        Session::set('user_role', $user['role']);

        Session::setFlash('success', 'Welcome back, ' . sanitize($user['full_name']) . '!');
        $this->redirectBasedOnRole($user['role']);
    }

    // ─── Registration ─────────────────────────────────────────────────────────

    public function showRegisterForm(): void {
        if (Session::has('user_id')) {
            $this->redirectBasedOnRole(Session::get('user_role'));
        }
        $this->render('auth/register', ['title' => 'Create a Kravyo Account']);
    }

    public function register(): void {
        Middleware::verifyCsrf();

        $fullName = sanitize(trim($_POST['full_name'] ?? ''));
        $email    = strtolower(trim($_POST['email']    ?? ''));
        $phone    = sanitize(trim($_POST['phone']      ?? ''));
        $password = $_POST['password']  ?? '';
        $role     = sanitize($_POST['role'] ?? ROLE_CUSTOMER);

        // ── Validation ────────────────────────────────────────────────────────
        if (empty($fullName) || empty($email) || empty($phone) || empty($password)) {
            Session::setFlash('danger', 'All fields are required.');
            $this->redirect('/register');
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::setFlash('danger', 'Please enter a valid email address.');
            $this->redirect('/register');
            return;
        }

        if (strlen($password) < 6) {
            Session::setFlash('danger', 'Password must be at least 6 characters long.');
            $this->redirect('/register');
            return;
        }

        if (!preg_match('/^[6-9][0-9]{9}$/', $phone)) {
            Session::setFlash('danger', 'Please enter a valid 10-digit Indian mobile number (must start with 6, 7, 8, or 9).');
            $this->redirect('/register');
            return;
        }

        // Check if already fully registered
        if ($this->userModel->findByEmail($email)) {
            Session::setFlash('danger', 'This email address is already registered. Please log in.');
            $this->redirect('/login');
            return;
        }
        if ($this->userModel->findByPhone($phone)) {
            Session::setFlash('danger', 'This phone number is already registered.');
            $this->redirect('/register');
            return;
        }

        // ── Create pending registration ───────────────────────────────────────
        $safeRole = ($role === ROLE_CHEF) ? ROLE_CHEF : ROLE_CUSTOMER;
        
        // Hash password before putting in pending table
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $userData = [
            'full_name'      => $fullName,
            'email'          => $email,
            'phone'          => $phone,
            'password_hash'  => $passwordHash,
            'role'           => $safeRole,
        ];

        try {
            // Generate OTP
            $plainOtp = $this->emailVerModel->generateOtp();
            
            // Store temporarily
            $this->emailVerModel->storePendingRegistration($userData, $plainOtp);

            // Send email
            $emailSent = $this->emailVerModel->sendVerificationEmail(
                $email,
                $fullName,
                $plainOtp
            );

            // Store pending email in session for the verify-email page
            Session::set('_pending_verification_email', $email);

            if ($emailSent) {
                Session::setFlash('success',
                    '✅ A 6-digit verification code has been sent to ' .
                    '<strong>' . sanitize($email) . '</strong>. Please check your inbox.'
                );
            } else {
                Session::setFlash('warning',
                    'Could not send the verification email right now. ' .
                    'Please use the <strong>Resend OTP</strong> button on this page, ' .
                    'or check your SMTP settings.'
                );
            }

            $this->redirect('/verify-email');

        } catch (\Exception $e) {
            error_log('[Kravyo Auth] Registration error: ' . $e->getMessage());
            Session::setFlash('danger', 'An error occurred during registration. Please try again.');
            $this->redirect('/register');
        }
    }

    // ─── Email Verification ───────────────────────────────────────────────────

    public function showVerifyEmail(): void {
        if (Session::has('user_id')) {
            $this->redirectBasedOnRole(Session::get('user_role'));
            return;
        }

        $pendingEmail = Session::get('_pending_verification_email', '');

        if (empty($pendingEmail)) {
            Session::setFlash('warning', 'No pending verification found. Please register or log in.');
            $this->redirect('/register');
            return;
        }

        $pendingRecord = $this->emailVerModel->getPendingRegistration($pendingEmail);

        if (!$pendingRecord) {
            Session::remove('_pending_verification_email');
            Session::setFlash('danger', 'Registration session expired. Please register again.');
            $this->redirect('/register');
            return;
        }

        $maskedEmail = $this->maskEmail($pendingEmail);
        $expiryInfo  = $this->emailVerModel->getOtpExpiryInfo($pendingEmail);
        $cooldown    = $this->emailVerModel->resendCooldownRemaining($pendingEmail);

        $this->render('auth/verify_email', [
            'title'        => 'Verify Your Email — Kravyo',
            'maskedEmail'  => $maskedEmail,
            'expiryInfo'   => $expiryInfo,
            'cooldown'     => $cooldown,
        ]);
    }

    public function verifyEmail(): void {
        Middleware::verifyCsrf();

        $pendingEmail = Session::get('_pending_verification_email', '');

        if (empty($pendingEmail)) {
            Session::setFlash('danger', 'Session expired. Please register again.');
            $this->redirect('/register');
            return;
        }

        $submittedOtp = trim($_POST['otp'] ?? '');

        if (!preg_match('/^\d{6}$/', $submittedOtp)) {
            Session::setFlash('danger', 'Please enter a valid 6-digit code.');
            $this->redirect('/verify-email');
            return;
        }

        $result = $this->emailVerModel->verifyOtp($pendingEmail, $submittedOtp);

        switch ($result['status']) {
            case 'valid':
                // OTP IS VALID! Now we actually insert them into the users table.
                $userData = $result['data'];
                
                try {
                    // Create the final user record
                    // Note: userModel->register expects plain password, but we already hashed it.
                    // Let's manually insert or bypass the hash step.
                    // Wait, User::register() hashes the password. Let's create a custom insert array.
                    $finalUserData = [
                        'full_name'      => $userData['full_name'],
                        'email'          => $userData['email'],
                        'phone'          => $userData['phone'],
                        'password'       => 'WILL_BE_REPLACED', // Dummy to pass structure
                        'role'           => $userData['role'],
                        'status'         => 'active',
                    ];
                    
                    // Actually, let's just insert it directly to avoid double hashing
                    $db = Database::getInstance();
                    $sql = "INSERT INTO users (full_name, email, phone, password_hash, role, status) 
                            VALUES (:full_name, :email, :phone, :password_hash, :role, 'active')";
                    $stmt = $db->prepare($sql);
                    $stmt->execute([
                        'full_name'     => $userData['full_name'],
                        'email'         => $userData['email'],
                        'phone'         => $userData['phone'],
                        'password_hash' => $userData['password_hash'], // Already hashed
                        'role'          => $userData['role']
                    ]);
                    
                    $userId = (int) $db->lastInsertId();

                    // If Chef, auto-create kitchen
                    if ($userData['role'] === ROLE_CHEF) {
                        $kitchenModel = new Kitchen();
                        $kitchenModel->create([
                            'user_id'         => $userId,
                            'kitchen_name'    => $userData['full_name'] . "'s Kitchen",
                            'address'         => '',
                            'city'            => '',
                            'pincode'         => '',
                            'approval_status' => KITCHEN_STATUS_PENDING,
                            'is_open'         => 0,
                        ]);
                    }

                    // Clean up pending registration
                    $this->emailVerModel->deletePendingRegistration($pendingEmail);
                    Session::remove('_pending_verification_email');

                    // Auto login!
                    Session::set('user_id',   $userId);
                    Session::set('user_name', $userData['full_name']);
                    Session::set('user_role', $userData['role']);

                    Session::setFlash('success', '🎉 Email verified! Your account has been created.');
                    $this->redirectBasedOnRole($userData['role']);

                } catch (\Exception $e) {
                    error_log('[Kravyo OTP] Final account creation error: ' . $e->getMessage());
                    Session::setFlash('danger', 'Error creating account. Please contact support.');
                    $this->redirect('/register');
                }
                break;

            case 'expired':
                Session::setFlash('danger', 'This verification code has expired. Please request a new code.');
                $this->redirect('/verify-email');
                break;

            case 'max_attempts':
                Session::setFlash('danger', 'Too many incorrect attempts. Please request a new code.');
                $this->redirect('/verify-email');
                break;

            case 'no_otp':
                Session::setFlash('warning', 'No verification session found. Please request a new code or register again.');
                $this->redirect('/verify-email');
                break;

            case 'invalid':
            default:
                Session::setFlash('danger', 'Invalid verification code. Please try again.');
                $this->redirect('/verify-email');
                break;
        }
    }

    public function resendOtp(): void {
        Middleware::verifyCsrf();

        $pendingEmail = Session::get('_pending_verification_email', '');

        if (empty($pendingEmail)) {
            Session::setFlash('danger', 'Session expired. Please register again.');
            $this->redirect('/register');
            return;
        }

        $pendingRecord = $this->emailVerModel->getPendingRegistration($pendingEmail);

        if (!$pendingRecord) {
            Session::remove('_pending_verification_email');
            Session::setFlash('danger', 'Registration session expired. Please register again.');
            $this->redirect('/register');
            return;
        }

        if (!$this->emailVerModel->canResend($pendingEmail)) {
            $remaining = $this->emailVerModel->resendCooldownRemaining($pendingEmail);
            Session::setFlash('warning', "Please wait {$remaining} more second(s) before requesting a new code.");
            $this->redirect('/verify-email');
            return;
        }

        $plainOtp = $this->emailVerModel->generateOtp();
        $this->emailVerModel->updateOtp($pendingEmail, $plainOtp);

        $emailSent = $this->emailVerModel->sendVerificationEmail(
            $pendingRecord['email'],
            $pendingRecord['full_name'],
            $plainOtp
        );

        if ($emailSent) {
            Session::setFlash('success',
                'A new verification code has been sent to ' .
                '<strong>' . sanitize($this->maskEmail($pendingEmail)) . '</strong>.'
            );
        } else {
            Session::setFlash('danger', 'Could not send verification email. Please check SMTP configuration.');
        }

        $this->redirect('/verify-email');
    }

    // ─── Logout ───────────────────────────────────────────────────────────────

    public function logout(): void {
        Middleware::verifyCsrf();
        Session::destroy();
        $this->redirect('/');
    }

    // ─── Forgot Password ──────────────────────────────────────────────────────

    /**
     * Show Forgot Password Form
     * GET /forgot-password
     */
    public function showForgotPasswordForm(): void {
        if (Session::has('user_id')) {
            $this->redirectBasedOnRole(Session::get('user_role'));
            return;
        }
        $this->render('auth/forgot_password', ['title' => 'Forgot Password — Kravyo']);
    }

    /**
     * Process Forgot Password — Send OTP
     * POST /forgot-password
     */
    public function forgotPassword(): void {
        Middleware::verifyCsrf();

        if (Session::has('user_id')) {
            $this->redirectBasedOnRole(Session::get('user_role'));
            return;
        }

        $email = strtolower(trim($_POST['email'] ?? ''));

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::setFlash('danger', 'Please enter a valid email address.');
            $this->redirect('/forgot-password');
            return;
        }

        $user = $this->userModel->findByEmail($email);

        // Always show the same success message regardless of whether
        // the email exists — prevents email enumeration attacks.
        if (!$user) {
            Session::setFlash('success',
                'If that email address is registered with Kravyo, ' .
                'you will receive a password reset code shortly.'
            );
            $this->redirect('/forgot-password');
            return;
        }

        // Check if user account is active
        if ($user['status'] !== 'active') {
            Session::setFlash('danger',
                'This account is currently <strong>' . sanitize($user['status']) . '</strong>. ' .
                'Please contact support.'
            );
            $this->redirect('/forgot-password');
            return;
        }

        // Generate and send reset OTP
        $plainOtp = $this->passwordResetModel->generateOtp();
        $this->passwordResetModel->storeResetOtp((int) $user['id'], $plainOtp);

        $emailSent = $this->passwordResetModel->sendResetEmail(
            $user['email'],
            $user['full_name'],
            $plainOtp
        );

        // Store user ID in session for the reset page
        Session::set('_reset_user_id', (int) $user['id']);

        if ($emailSent) {
            Session::setFlash('success',
                '✅ A 6-digit reset code has been sent to ' .
                '<strong>' . sanitize($this->maskEmail($email)) . '</strong>. Please check your inbox.'
            );
        } else {
            Session::setFlash('warning',
                'Could not send the reset email right now. ' .
                'Please use the <strong>Resend Code</strong> button, or try again later.'
            );
        }

        $this->redirect('/reset-password');
    }

    /**
     * Show Reset Password Form (OTP + new password)
     * GET /reset-password
     */
    public function showResetPasswordForm(): void {
        if (Session::has('user_id')) {
            $this->redirectBasedOnRole(Session::get('user_role'));
            return;
        }

        $resetUserId = (int) Session::get('_reset_user_id', 0);

        if ($resetUserId === 0) {
            Session::setFlash('warning', 'No password reset session found. Please start again.');
            $this->redirect('/forgot-password');
            return;
        }

        $user = $this->userModel->findById($resetUserId);

        if (!$user) {
            Session::remove('_reset_user_id');
            Session::setFlash('danger', 'Account not found. Please start again.');
            $this->redirect('/forgot-password');
            return;
        }

        $maskedEmail = $this->maskEmail($user['email']);
        $expiryInfo  = $this->passwordResetModel->getOtpExpiryInfo($resetUserId);
        $cooldown    = $this->passwordResetModel->resendCooldownRemaining($resetUserId);

        $this->render('auth/reset_password', [
            'title'       => 'Reset Password — Kravyo',
            'maskedEmail' => $maskedEmail,
            'expiryInfo'  => $expiryInfo,
            'cooldown'    => $cooldown,
        ]);
    }

    /**
     * Process Password Reset (verify OTP + set new password)
     * POST /reset-password
     */
    public function resetPassword(): void {
        Middleware::verifyCsrf();

        $resetUserId = (int) Session::get('_reset_user_id', 0);

        if ($resetUserId === 0) {
            Session::setFlash('danger', 'Session expired. Please start again.');
            $this->redirect('/forgot-password');
            return;
        }

        $submittedOtp    = trim($_POST['otp']              ?? '');
        $newPassword     = $_POST['new_password']          ?? '';
        $confirmPassword = $_POST['confirm_password']      ?? '';

        // Validate OTP format
        if (!preg_match('/^\d{6}$/', $submittedOtp)) {
            Session::setFlash('danger', 'Please enter a valid 6-digit code.');
            $this->redirect('/reset-password');
            return;
        }

        // Validate password
        if (strlen($newPassword) < 6) {
            Session::setFlash('danger', 'New password must be at least 6 characters long.');
            $this->redirect('/reset-password');
            return;
        }

        if ($newPassword !== $confirmPassword) {
            Session::setFlash('danger', 'Passwords do not match. Please try again.');
            $this->redirect('/reset-password');
            return;
        }

        $result = $this->passwordResetModel->verifyResetOtp($resetUserId, $submittedOtp);

        switch ($result) {
            case 'valid':
                $this->passwordResetModel->resetPassword($resetUserId, $newPassword);
                Session::remove('_reset_user_id');
                Session::setFlash('success',
                    '🎉 Password reset successfully! You can now log in with your new password.'
                );
                $this->redirect('/login');
                break;

            case 'expired':
                Session::setFlash('danger', 'This reset code has expired. Please request a new one.');
                $this->redirect('/reset-password');
                break;

            case 'max_attempts':
                Session::setFlash('danger', 'Too many incorrect attempts. Please request a new code.');
                $this->redirect('/reset-password');
                break;

            case 'no_otp':
                Session::setFlash('warning', 'No reset code found. Please request a new one.');
                $this->redirect('/reset-password');
                break;

            case 'invalid':
            default:
                Session::setFlash('danger', 'Invalid reset code. Please try again.');
                $this->redirect('/reset-password');
                break;
        }
    }

    /**
     * Resend Password Reset OTP
     * POST /resend-reset-otp
     */
    public function resendResetOtp(): void {
        Middleware::verifyCsrf();

        $resetUserId = (int) Session::get('_reset_user_id', 0);

        if ($resetUserId === 0) {
            Session::setFlash('danger', 'Session expired. Please start again.');
            $this->redirect('/forgot-password');
            return;
        }

        $user = $this->userModel->findById($resetUserId);

        if (!$user) {
            Session::remove('_reset_user_id');
            Session::setFlash('danger', 'Account not found. Please start again.');
            $this->redirect('/forgot-password');
            return;
        }

        if (!$this->passwordResetModel->canResend($resetUserId)) {
            $remaining = $this->passwordResetModel->resendCooldownRemaining($resetUserId);
            Session::setFlash('warning', "Please wait {$remaining} more second(s) before requesting a new code.");
            $this->redirect('/reset-password');
            return;
        }

        $plainOtp = $this->passwordResetModel->generateOtp();
        $this->passwordResetModel->storeResetOtp($resetUserId, $plainOtp);

        $emailSent = $this->passwordResetModel->sendResetEmail(
            $user['email'],
            $user['full_name'],
            $plainOtp
        );

        if ($emailSent) {
            Session::setFlash('success',
                'A new reset code has been sent to ' .
                '<strong>' . sanitize($this->maskEmail($user['email'])) . '</strong>.'
            );
        } else {
            Session::setFlash('danger', 'Could not send the reset email. Please check SMTP configuration.');
        }

        $this->redirect('/reset-password');
    }

    // ─── Private Helpers ──────────────────────────────────────────────────────

    private function redirectBasedOnRole(string $role): void {
        match ($role) {
            ROLE_ADMIN  => $this->redirect('/admin/dashboard'),
            ROLE_CHEF   => $this->redirect('/chef/dashboard'),
            default     => $this->redirect('/'),
        };
    }

    private function maskEmail(string $email): string {
        [$local, $domain] = explode('@', $email, 2);
        $visible = min(2, strlen($local));
        $masked  = substr($local, 0, $visible) . str_repeat('*', max(0, strlen($local) - $visible));
        return $masked . '@' . $domain;
    }
}
