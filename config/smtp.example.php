<?php
/**
 * Kravyo — SMTP Configuration Example / Template
 *
 * COPY THIS FILE to config/smtp.php and fill in your real credentials.
 * The smtp.php file is gitignored for security.
 *
 * Gmail Setup:
 *   1. Enable 2-Step Verification on your Google Account
 *   2. Generate an App Password at https://myaccount.google.com/apppasswords
 *   3. Use that 16-character App Password as SMTP_PASSWORD
 */

define('SMTP_HOST',       'smtp.gmail.com');
define('SMTP_PORT',       587);
define('SMTP_ENCRYPTION', 'tls');

define('SMTP_USERNAME',   'your_email@gmail.com');
define('SMTP_PASSWORD',   'your_app_password_here');  // 16-char Gmail App Password

define('SMTP_FROM_EMAIL', 'your_email@gmail.com');
define('SMTP_FROM_NAME',  'Kravyo');

define('OTP_EXPIRY_MINUTES',  5);
define('OTP_RESEND_COOLDOWN', 60);
define('OTP_MAX_ATTEMPTS',    5);
