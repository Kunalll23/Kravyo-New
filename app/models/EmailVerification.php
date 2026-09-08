<?php
/**
 * Kravyo — EmailVerification Model (V2 - Pending Table)
 *
 * Handles OTP generation, storage, validation, and email delivery.
 * Uses a temporary `pending_registrations` table so unverified users
 * are NEVER inserted into the main `users` table.
 *
 * PHPMailer is loaded from vendor/phpmailer/ (downloaded manually).
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

class EmailVerification extends Model {
    protected string $table = 'pending_registrations';

    // ─── OTP Generation ───────────────────────────────────────────────────────

    /**
     * Generate a cryptographically secure 6-digit OTP.
     */
    public function generateOtp(): string {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    // ─── Pending Registration Storage ─────────────────────────────────────────

    /**
     * Store the pending registration data and OTP hash.
     * Uses REPLACE INTO to handle cases where a user tries to register
     * the same email again before verifying (overwrites the pending record).
     */
    public function storePendingRegistration(array $userData, string $plainOtp): void {
        $hashedOtp = password_hash($plainOtp, PASSWORD_DEFAULT);
        $expiresAt = date('Y-m-d H:i:s', time() + (OTP_EXPIRY_MINUTES * 60));
        $resentAt  = date('Y-m-d H:i:s');

        $sql = "REPLACE INTO {$this->table}
                (email, full_name, phone, password_hash, role, email_otp_hash, email_otp_expires_at, email_otp_resent_at, email_otp_attempts)
                VALUES
                (:email, :full_name, :phone, :password_hash, :role, :otp_hash, :expires_at, :resent_at, 0)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'email'         => $userData['email'],
            'full_name'     => $userData['full_name'],
            'phone'         => $userData['phone'],
            'password_hash' => $userData['password_hash'],
            'role'          => $userData['role'],
            'otp_hash'      => $hashedOtp,
            'expires_at'    => $expiresAt,
            'resent_at'     => $resentAt,
        ]);
    }

    /**
     * Get pending registration data by email.
     */
    public function getPendingRegistration(string $email): ?array {
        $sql = "SELECT * FROM {$this->table} WHERE email = :email LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['email' => $email]);
        $record = $stmt->fetch();
        return $record ?: null;
    }

    /**
     * Delete pending registration (called after successful verification).
     */
    public function deletePendingRegistration(string $email): void {
        $sql = "DELETE FROM {$this->table} WHERE email = :email";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['email' => $email]);
    }

    // ─── OTP Verification ─────────────────────────────────────────────────────

    /**
     * Verify the submitted OTP for an email address.
     * Returns an array: ['status' => string, 'data' => array|null]
     * Statuses: 'valid' | 'no_otp' | 'expired' | 'max_attempts' | 'invalid'
     */
    public function verifyOtp(string $email, string $plainOtp): array {
        $user = $this->getPendingRegistration($email);

        if (!$user) {
            return ['status' => 'no_otp', 'data' => null];
        }

        // Check expiry
        if (strtotime($user['email_otp_expires_at']) < time()) {
            return ['status' => 'expired', 'data' => null];
        }

        // Check max attempts reached
        if ((int) $user['email_otp_attempts'] >= OTP_MAX_ATTEMPTS) {
            return ['status' => 'max_attempts', 'data' => null];
        }

        // Verify OTP hash
        if (!password_verify($plainOtp, $user['email_otp_hash'])) {
            $this->incrementAttempts($email);
            return ['status' => 'invalid', 'data' => null];
        }

        // Valid OTP — return the data so AuthController can create the real user
        return ['status' => 'valid', 'data' => $user];
    }

    /**
     * Increment the failed OTP attempt counter.
     */
    private function incrementAttempts(string $email): void {
        $sql = "UPDATE {$this->table}
                SET email_otp_attempts = email_otp_attempts + 1
                WHERE email = :email";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['email' => $email]);
    }

    /**
     * Update the OTP fields for a resend request.
     */
    public function updateOtp(string $email, string $plainOtp): void {
        $hashedOtp = password_hash($plainOtp, PASSWORD_DEFAULT);
        $expiresAt = date('Y-m-d H:i:s', time() + (OTP_EXPIRY_MINUTES * 60));
        $resentAt  = date('Y-m-d H:i:s');

        $sql = "UPDATE {$this->table}
                SET email_otp_hash       = :otp_hash,
                    email_otp_expires_at = :expires_at,
                    email_otp_resent_at  = :resent_at,
                    email_otp_attempts   = 0
                WHERE email = :email";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'otp_hash'   => $hashedOtp,
            'expires_at' => $expiresAt,
            'resent_at'  => $resentAt,
            'email'      => $email,
        ]);
    }

    // ─── Resend Cooldown ──────────────────────────────────────────────────────

    public function canResend(string $email): bool {
        $row = $this->getPendingRegistration($email);

        if (!$row || empty($row['email_otp_resent_at'])) {
            return true;
        }

        $lastResent = strtotime($row['email_otp_resent_at']);
        return (time() - $lastResent) >= OTP_RESEND_COOLDOWN;
    }

    public function resendCooldownRemaining(string $email): int {
        $row = $this->getPendingRegistration($email);

        if (!$row || empty($row['email_otp_resent_at'])) {
            return 0;
        }

        $elapsed = time() - strtotime($row['email_otp_resent_at']);
        return max(0, OTP_RESEND_COOLDOWN - $elapsed);
    }

    public function getOtpExpiryInfo(string $email): array {
        $row = $this->getPendingRegistration($email);

        if (!$row || empty($row['email_otp_expires_at'])) {
            return ['seconds_remaining' => 0, 'expired' => true];
        }

        $secondsRemaining = max(0, strtotime($row['email_otp_expires_at']) - time());
        return [
            'seconds_remaining' => $secondsRemaining,
            'expired'           => $secondsRemaining === 0,
        ];
    }

    // ─── Email Sending ────────────────────────────────────────────────────────

    public function sendVerificationEmail(
        string $toEmail,
        string $toName,
        string $plainOtp
    ): bool {
        // Load PHPMailer classes
        require_once ROOT_PATH . '/vendor/phpmailer/phpmailer/src/Exception.php';
        require_once ROOT_PATH . '/vendor/phpmailer/phpmailer/src/PHPMailer.php';
        require_once ROOT_PATH . '/vendor/phpmailer/phpmailer/src/SMTP.php';

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = SMTP_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = SMTP_USERNAME;
            // STRIP SPACES from the password so Gmail accepts it!
            $mail->Password   = str_replace(' ', '', SMTP_PASSWORD);
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = SMTP_PORT;
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
            $mail->addAddress($toEmail, $toName);
            $mail->addReplyTo(SMTP_FROM_EMAIL, SMTP_FROM_NAME);

            $mail->isHTML(true);
            $mail->Subject = 'Your Kravyo Email Verification Code';
            $mail->Body    = $this->buildEmailHtml($toName, $plainOtp);
            $mail->AltBody = $this->buildEmailText($toName, $plainOtp);

            $mail->send();
            return true;

        } catch (PHPMailerException $e) {
            error_log('[Kravyo OTP] PHPMailer error for ' . $toEmail . ': ' . $e->getMessage());
            return false;
        }
    }

    // ─── Email Templates ──────────────────────────────────────────────────────

    private function buildEmailHtml(string $name, string $otp): string {
        $expiry   = OTP_EXPIRY_MINUTES;
        $safeName = htmlspecialchars($name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Verify Your Kravyo Email</title>
</head>
<body style="margin:0;padding:0;background:#f4f4f4;font-family:'Segoe UI',Arial,sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f4f4;padding:40px 0;">
    <tr>
      <td align="center">
        <table width="600" cellpadding="0" cellspacing="0"
               style="background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,0.08);max-width:600px;width:100%;">
          <tr>
            <td style="background:linear-gradient(135deg,#1a1a2e 0%,#c0392b 100%);padding:36px 40px;text-align:center;">
              <h1 style="margin:0;color:#ffffff;font-size:28px;font-weight:800;letter-spacing:1px;">🔥 Kravyo</h1>
              <p style="margin:8px 0 0;color:rgba(255,255,255,0.75);font-size:13px;">Home-Cooked Food, Verified & Delivered</p>
            </td>
          </tr>
          <tr>
            <td style="padding:40px 40px 32px;">
              <h2 style="margin:0 0 8px;color:#1a1a2e;font-size:22px;font-weight:700;">Verify Your Email Address</h2>
              <p style="margin:0 0 24px;color:#555555;font-size:15px;line-height:1.6;">
                Hi <strong>{$safeName}</strong>,<br><br>
                Welcome to Kravyo! To complete your registration and start ordering
                authentic home-cooked meals, please verify your email address using
                the code below.
              </p>
              <div style="background:linear-gradient(135deg,#f8f0ff,#fff3e0);border:2px dashed #c0392b;
                          border-radius:12px;padding:28px;text-align:center;margin:0 0 28px;">
                <p style="margin:0 0 10px;color:#888;font-size:13px;text-transform:uppercase;
                           letter-spacing:2px;font-weight:600;">Your Verification Code</p>
                <div style="font-size:42px;font-weight:900;letter-spacing:12px;color:#c0392b;
                            font-family:'Courier New',Courier,monospace;">
                  {$otp}
                </div>
                <p style="margin:12px 0 0;color:#888;font-size:12px;">
                  ⏱ This code expires in <strong>{$expiry} minutes</strong>
                </p>
              </div>
              <p style="margin:0 0 16px;color:#555555;font-size:14px;line-height:1.6;">
                Enter this 6-digit code on the Kravyo verification page to activate your account.
              </p>
              <div style="background:#fff8e1;border-left:4px solid #ffc107;border-radius:4px;padding:14px 18px;margin:0 0 8px;">
                <p style="margin:0;color:#666;font-size:13px;line-height:1.5;">
                  🔒 <strong>Security notice:</strong> Kravyo will never ask for this code by phone or chat. Do not share it.
                </p>
              </div>
            </td>
          </tr>
          <tr>
            <td style="background:#f8f9fa;border-top:1px solid #eee;padding:24px 40px;text-align:center;">
              <p style="margin:0 0 4px;color:#999;font-size:12px;">If you did not create a Kravyo account, safely ignore this email.</p>
              <p style="margin:0;color:#bbb;font-size:11px;">© 2026 Kravyo — All rights reserved.</p>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
HTML;
    }

    private function buildEmailText(string $name, string $otp): string {
        $expiry = OTP_EXPIRY_MINUTES;
        return <<<TEXT
Kravyo — Verify Your Email Address
====================================

Hi {$name},

Welcome to Kravyo! Your email verification code is:

  {$otp}

This code expires in {$expiry} minutes.

Enter this code on the Kravyo verification page to activate your account.

IMPORTANT: Do not share this code with anyone.
If you did not register on Kravyo, please ignore this email.

© 2026 Kravyo
TEXT;
    }
}
