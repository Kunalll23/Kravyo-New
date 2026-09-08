<?php
/**
 * Kravyo — Email Verification Page
 * Route: GET /verify-email
 * Variables: $maskedEmail, $expiryInfo (seconds_remaining, expired), $cooldown
 */
?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">

            <!-- Card -->
            <div class="card border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">

                <!-- Gradient Header -->
                <div class="card-header border-0 text-center py-4"
                     style="background: linear-gradient(135deg, #1a1a2e 0%, #c0392b 100%);">
                    <div class="mb-2">
                        <span style="font-size: 2.8rem;">📧</span>
                    </div>
                    <h4 class="fw-800 text-white mb-1">Verify Your Email</h4>
                    <p class="text-white-50 small mb-0">
                        We sent a 6-digit code to<br>
                        <strong class="text-warning"><?= sanitize($maskedEmail) ?></strong>
                    </p>
                </div>

                <div class="card-body p-4 p-md-5">

                    <!-- Expiry Countdown Timer -->
                    <?php if (!$expiryInfo['expired'] && $expiryInfo['seconds_remaining'] > 0): ?>
                        <div class="text-center mb-4">
                            <div class="d-inline-flex align-items-center gap-2 px-4 py-2 rounded-pill"
                                 style="background: #fff8e1; border: 1px solid #ffc107;">
                                <i class="bi bi-clock text-warning"></i>
                                <span class="fw-semibold text-warning small">
                                    Code expires in
                                    <span id="otpCountdown" class="fw-800">
                                        <?php
                                            $m = floor($expiryInfo['seconds_remaining'] / 60);
                                            $s = $expiryInfo['seconds_remaining'] % 60;
                                            echo sprintf('%02d:%02d', $m, $s);
                                        ?>
                                    </span>
                                </span>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning border-0 rounded-3 small mb-4" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <strong>Code expired.</strong> Please request a new one below.
                        </div>
                    <?php endif; ?>

                    <!-- OTP Submission Form -->
                    <form action="<?= url('/verify-email') ?>" method="POST" id="otpForm" autocomplete="off">
                        <?= csrf_field() ?>

                        <div class="mb-4">
                            <label for="otp" class="form-label fw-semibold text-center d-block mb-3">
                                Enter Verification Code
                            </label>

                            <!-- Stylish 6-digit OTP input -->
                            <input type="text"
                                   name="otp"
                                   id="otp"
                                   class="form-control form-control-lg text-center fw-800"
                                   maxlength="6"
                                   pattern="\d{6}"
                                   inputmode="numeric"
                                   placeholder="● ● ● ● ● ●"
                                   autocomplete="one-time-code"
                                   required
                                   style="font-size: 2rem; letter-spacing: 0.8rem; border-radius: 12px;
                                          border: 2px solid #dee2e6; padding: 14px;
                                          background: #f8f9fa;">
                            <div class="form-text text-center mt-2">
                                <i class="bi bi-shield-lock me-1 text-success"></i>
                                Check your email inbox — including the Spam folder.
                            </div>
                        </div>

                        <div class="d-grid mb-3">
                            <button type="submit" id="verifyBtn" class="btn btn-kravyo-primary btn-lg fw-bold"
                                    style="border-radius: 10px;">
                                <i class="bi bi-check-circle me-2"></i> Verify Email
                            </button>
                        </div>
                    </form>

                    <hr class="my-3">

                    <!-- Resend OTP Section -->
                    <div class="text-center">
                        <p class="text-muted small mb-2">Didn't receive the code?</p>

                        <?php if ($cooldown > 0): ?>
                            <!-- Still in cooldown -->
                            <button class="btn btn-outline-secondary btn-sm" disabled>
                                <i class="bi bi-arrow-clockwise me-1"></i>
                                Resend OTP
                                <span class="badge bg-secondary ms-1" id="resendCountdown">
                                    <?= $cooldown ?>s
                                </span>
                            </button>
                            <p class="text-muted mt-2" style="font-size: 0.75rem;">
                                Wait <span id="resendSeconds"><?= $cooldown ?></span> second(s) before resending.
                            </p>
                        <?php else: ?>
                            <!-- Can resend -->
                            <form action="<?= url('/resend-email-otp') ?>" method="POST" class="d-inline" id="resendForm">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-outline-warning fw-semibold btn-sm"
                                        id="resendBtn" style="border-radius: 8px;">
                                    <i class="bi bi-arrow-clockwise me-1"></i> Resend OTP
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>

                    <!-- Back to Login -->
                    <div class="text-center mt-4 pt-3 border-top">
                        <a href="<?= url('/login') ?>" class="text-decoration-none text-muted small">
                            <i class="bi bi-arrow-left me-1"></i> Back to Login
                        </a>
                    </div>

                </div><!-- .card-body -->
            </div><!-- .card -->

            <!-- Help Text -->
            <div class="text-center mt-4">
                <p class="text-muted small">
                    <i class="bi bi-info-circle me-1"></i>
                    The code is valid for <strong><?= OTP_EXPIRY_MINUTES ?> minutes</strong>.
                    Check your spam/junk folder if you don't see it.
                </p>
            </div>

        </div>
    </div>
</div>

<!-- ── JavaScript: OTP Auto-format + Countdown Timers ───────────────────── -->
<script>
(function () {
    'use strict';

    // ── OTP input: allow only digits, auto-submit on 6 digits ────────────────
    const otpInput = document.getElementById('otp');
    if (otpInput) {
        otpInput.addEventListener('input', function () {
            // Strip non-digits
            this.value = this.value.replace(/\D/g, '');
        });

        otpInput.addEventListener('keydown', function (e) {
            // Allow: Backspace, Delete, Tab, arrows, Ctrl+A/C/V/X
            const allowedKeys = ['Backspace', 'Delete', 'Tab', 'ArrowLeft', 'ArrowRight', 'Home', 'End'];
            if (allowedKeys.includes(e.key)) return;
            if (e.ctrlKey || e.metaKey) return;
            // Block non-digit keys
            if (!/^\d$/.test(e.key)) e.preventDefault();
        });
    }

    // ── OTP expiry countdown ──────────────────────────────────────────────────
    const countdownEl = document.getElementById('otpCountdown');
    if (countdownEl) {
        let remaining = <?= (int) ($expiryInfo['seconds_remaining'] ?? 0) ?>;

        const tick = setInterval(function () {
            remaining--;
            if (remaining <= 0) {
                clearInterval(tick);
                countdownEl.closest('.d-inline-flex').innerHTML =
                    '<span class="text-danger fw-semibold small"><i class="bi bi-x-circle me-1"></i>Code expired — please resend</span>';
                return;
            }
            const m = Math.floor(remaining / 60);
            const s = remaining % 60;
            countdownEl.textContent = String(m).padStart(2,'0') + ':' + String(s).padStart(2,'0');
        }, 1000);
    }

    // ── Resend cooldown countdown ─────────────────────────────────────────────
    const resendCountdownEl = document.getElementById('resendCountdown');
    const resendSecondsEl   = document.getElementById('resendSeconds');
    if (resendCountdownEl && resendSecondsEl) {
        let cooldown = <?= (int) ($cooldown ?? 0) ?>;

        const resendTick = setInterval(function () {
            cooldown--;
            if (cooldown <= 0) {
                clearInterval(resendTick);
                // Replace the disabled button area with a live resend form
                const container = resendCountdownEl.closest('.text-center');
                if (container) {
                    container.innerHTML =
                        '<p class="text-muted small mb-2">Didn\'t receive the code?</p>' +
                        '<form action="<?= url('/resend-email-otp') ?>" method="POST" class="d-inline">' +
                        '<input type="hidden" name="_csrf" value="<?= csrf_token() ?>">' +
                        '<button type="submit" class="btn btn-outline-warning fw-semibold btn-sm" ' +
                        'style="border-radius:8px;">' +
                        '<i class="bi bi-arrow-clockwise me-1"></i> Resend OTP' +
                        '</button></form>';
                }
                return;
            }
            resendCountdownEl.textContent = cooldown + 's';
            resendSecondsEl.textContent   = cooldown;
        }, 1000);
    }

    // ── Prevent double-submit on verify form ─────────────────────────────────
    const otpForm   = document.getElementById('otpForm');
    const verifyBtn = document.getElementById('verifyBtn');
    if (otpForm && verifyBtn) {
        otpForm.addEventListener('submit', function () {
            verifyBtn.disabled = true;
            verifyBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Verifying…';
        });
    }

    // ── Prevent double-submit on resend form ─────────────────────────────────
    const resendForm = document.getElementById('resendForm');
    const resendBtn  = document.getElementById('resendBtn');
    if (resendForm && resendBtn) {
        resendForm.addEventListener('submit', function () {
            resendBtn.disabled = true;
            resendBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sending…';
        });
    }

})();
</script>
