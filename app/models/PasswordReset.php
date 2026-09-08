<?php
/**
 * Kravyo — PasswordReset Model
 *
 * Handles OTP generation, storage, validation, and email delivery
 * for the Forgot Password flow. Stores OTP hash directly in the
 * `users` table (reset_otp_* columns) since the user already exists.
 *
 * Security design:
 *  - OTP generated with random_int() (CSPRNG)
 *  - OTP stored as password_hash() — never plain-text in DB
 *  - 5-minute expiry enforced server-side
 *  - Max 5 wrong attempts before lockout (must resend)
 *  - 60-second resend cooldown to prevent spam
 *  - OTP cleared after successful password reset
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

class PasswordReset extends Model {
    protected string $table = 'users';

    // ─── OTP Generation ───────────────────────────────────────────────────────

    public function generateOtp(): string {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    // ─── OTP Storage ──────────────────────────────────────────────────────────

    /**
     * Hash the OTP and store it in the users table reset_otp_* columns.
     */
    public function storeResetOtp(int $userId, string $plainOtp): void {
        $hashedOtp = password_hash($plainOtp, PASSWORD_DEFAULT);
        $expiresAt = date('Y-m-d H:i:s', time() + (OTP_EXPIRY_MINUTES * 60));
        $resentAt  = date('Y-m-d H:i:s');

        $sql = "UPDATE {$this->table}
                SET reset_otp_hash       = :otp_hash,
                    reset_otp_expires_at = :expires_at,
                    reset_otp_resent_at  = :resent_at,
                    reset_otp_attempts   = 0
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'otp_hash'   => $hashedOtp,
            'expires_at' => $expiresAt,
            'resent_at'  => $resentAt,
            'id'         => $userId,
        ]);
    }

    // ─── OTP Verification ─────────────────────────────────────────────────────

    /**
     * Verify the submitted OTP for a user ID.
     * Returns: 'valid' | 'no_otp' | 'expired' | 'max_attempts' | 'invalid'
     */
    public function verifyResetOtp(int $userId, string $plainOtp): string {
        $sql = "SELECT reset_otp_hash, reset_otp_expires_at, reset_otp_attempts
                FROM {$this->table} WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $userId]);
        $user = $stmt->fetch();

        if (!$user || empty($user['reset_otp_hash'])) {
            return 'no_otp';
        }

        if (strtotime($user['reset_otp_expires_at']) < time()) {
            return 'expired';
        }

        if ((int) $user['reset_otp_attempts'] >= OTP_MAX_ATTEMPTS) {
            return 'max_attempts';
        }

        if (!password_verify($plainOtp, $user['reset_otp_hash'])) {
            $this->incrementAttempts($userId);
            return 'invalid';
        }

        return 'valid';
    }

    /**
     * Update the user's password and clear the reset OTP fields.
     */
    public function resetPassword(int $userId, string $newPassword): void {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        $sql = "UPDATE {$this->table}
                SET password_hash        = :password_hash,
                    reset_otp_hash       = NULL,
                    reset_otp_expires_at = NULL,
                    reset_otp_resent_at  = NULL,
                    reset_otp_attempts   = 0
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'password_hash' => $hashedPassword,
            'id'            => $userId,
        ]);
    }

    /**
     * Clear reset OTP fields without resetting password (e.g. on cancel).
     */
    public function clearResetOtp(int $userId): void {
        $sql = "UPDATE {$this->table}
                SET reset_otp_hash       = NULL,
                    reset_otp_expires_at = NULL,
                    reset_otp_resent_at  = NULL,
                    reset_otp_attempts   = 0
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $userId]);
    }

    private function incrementAttempts(int $userId): void {
        $sql = "UPDATE {$this->table}
                SET reset_otp_attempts = reset_otp_attempts + 1
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $userId]);
    }

    // ─── Resend Cooldown ──────────────────────────────────────────────────────

    public function canResend(int $userId): bool {
        $sql = "SELECT reset_otp_resent_at FROM {$this->table} WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $userId]);
        $row = $stmt->fetch();

        if (!$row || empty($row['reset_otp_resent_at'])) {
            return true;
        }

        return (time() - strtotime($row['reset_otp_resent_at'])) >= OTP_RESEND_COOLDOWN;
    }

    public function resendCooldownRemaining(int $userId): int {
        $sql = "SELECT reset_otp_resent_at FROM {$this->table} WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $userId]);
        $row = $stmt->fetch();

        if (!$row || empty($row['reset_otp_resent_at'])) {
            return 0;
        }

        return max(0, OTP_RESEND_COOLDOWN - (time() - strtotime($row['reset_otp_resent_at'])));
    }

    public function getOtpExpiryInfo(int $userId): array {
        $sql = "SELECT reset_otp_expires_at FROM {$this->table} WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $userId]);
        $row = $stmt->fetch();

        if (!$row || empty($row['reset_otp_expires_at'])) {
            return ['seconds_remaining' => 0, 'expired' => true];
        }

        $secondsRemaining = max(0, strtotime($row['reset_otp_expires_at']) - time());
        return [
            'seconds_remaining' => $secondsRemaining,
            'expired'           => $secondsRemaining === 0,
        ];
    }

    // ─── Email Sending ────────────────────────────────────────────────────────

    public function sendResetEmail(string $toEmail, string $toName, string $plainOtp): bool {
        require_once ROOT_PATH . '/vendor/phpmailer/phpmailer/src/Exception.php';
        require_once ROOT_PATH . '/vendor/phpmailer/phpmailer/src/PHPMailer.php';
        require_once ROOT_PATH . '/vendor/phpmailer/phpmailer/src/SMTP.php';

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = SMTP_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = SMTP_USERNAME;
            $mail->Password   = str_replace(' ', '', SMTP_PASSWORD);
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = SMTP_PORT;
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
            $mail->addAddress($toEmail, $toName);
            $mail->addReplyTo(SMTP_FROM_EMAIL, SMTP_FROM_NAME);

            $mail->isHTML(true);
            $mail->Subject = 'Kravyo — Password Reset Code';
            $mail->Body    = $this->buildResetEmailHtml($toName, $plainOtp);
            $mail->AltBody = $this->buildResetEmailText($toName, $plainOtp);

            $mail->send();
            return true;

        } catch (PHPMailerException $e) {
            error_log('[Kravyo PasswordReset] PHPMailer error for ' . $toEmail . ': ' . $e->getMessage());
            return false;
        }
    }

    // ─── Email Templates ──────────────────────────────────────────────────────

    private function buildResetEmailHtml(string $name, string $otp): string {
        $expiry   = OTP_EXPIRY_MINUTES;
        $safeName = htmlspecialchars($name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kravyo Password Reset</title>
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
              <p style="margin:8px 0 0;color:rgba(255,255,255,0.75);font-size:13px;">Password Reset Request</p>
            </td>
          </tr>
          <tr>
            <td style="padding:40px 40px 32px;">
              <h2 style="margin:0 0 8px;color:#1a1a2e;font-size:22px;font-weight:700;">Reset Your Password</h2>
              <p style="margin:0 0 24px;color:#555555;font-size:15px;line-height:1.6;">
                Hi <strong>{$safeName}</strong>,<br><br>
                We received a request to reset the password for your Kravyo account.
                Use the code below to reset your password. If you did not make this request,
                you can safely ignore this email.
              </p>
              <div style="background:linear-gradient(135deg,#fff3e0,#fce4ec);border:2px dashed #c0392b;
                          border-radius:12px;padding:28px;text-align:center;margin:0 0 28px;">
                <p style="margin:0 0 10px;color:#888;font-size:13px;text-transform:uppercase;
                           letter-spacing:2px;font-weight:600;">Password Reset Code</p>
                <div style="font-size:42px;font-weight:900;letter-spacing:12px;color:#c0392b;
                            font-family:'Courier New',Courier,monospace;">
                  {$otp}
                </div>
                <p style="margin:12px 0 0;color:#888;font-size:12px;">
                  ⏱ This code expires in <strong>{$expiry} minutes</strong>
                </p>
              </div>
              <div style="background:#fff8e1;border-left:4px solid #ffc107;border-radius:4px;padding:14px 18px;">
                <p style="margin:0;color:#666;font-size:13px;line-height:1.5;">
                  🔒 <strong>Security notice:</strong> Kravyo will never ask for this code
                  by phone or chat. Do not share it with anyone.
                </p>
              </div>
            </td>
          </tr>
          <tr>
            <td style="background:#f8f9fa;border-top:1px solid #eee;padding:24px 40px;text-align:center;">
              <p style="margin:0 0 4px;color:#999;font-size:12px;">
                If you did not request a password reset, no action is needed.
              </p>
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

    private function buildResetEmailText(string $name, string $otp): string {
        $expiry = OTP_EXPIRY_MINUTES;
        return <<<TEXT
Kravyo — Password Reset
========================

Hi {$name},

We received a request to reset your Kravyo password.
Your password reset code is:

  {$otp}

This code expires in {$expiry} minutes.

If you did not request a password reset, please ignore this email.
Do NOT share this code with anyone.

© 2026 Kravyo
TEXT;
    }
}
