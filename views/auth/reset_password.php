<?php
/**
 * Kravyo — Reset Password Page
 * Route: GET /reset-password
 * Variables: $maskedEmail, $expiryInfo (seconds_remaining, expired), $cooldown
 *
 * User enters:
 *  - The 6-digit OTP from their email
 *  - Their new password (+ confirm)
 */
?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">

            <div class="card border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">

                <!-- Gradient Header -->
                <div class="card-header border-0 text-center py-4"
                     style="background: linear-gradient(135deg, #1a1a2e 0%, #c0392b 100%);">
                    <div class="mb-2">
                        <span style="font-size: 2.8rem;">🔑</span>
                    </div>
                    <h4 class="fw-800 text-white mb-1">Reset Your Password</h4>
                    <p class="text-white-50 small mb-0">
                        Code sent to <strong class="text-warning"><?= sanitize($maskedEmail) ?></strong>
                    </p>
                </div>

                <div class="card-body p-4 p-md-5">

                    <!-- OTP Expiry Countdown -->
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
                        <div class="alert alert-warning border-0 rounded-3 small mb-4">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <strong>Code expired.</strong> Please request a new one below.
                        </div>
                    <?php endif; ?>

                    <!-- Reset Password Form -->
                    <form action="<?= url('/reset-password') ?>" method="POST" id="resetForm" autocomplete="off">
                        <?= csrf_field() ?>

                        <!-- OTP Field -->
                        <div class="mb-4">
                            <label for="otp" class="form-label fw-semibold text-center d-block mb-3">
                                Enter Reset Code
                            </label>
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
                                          border: 2px solid #dee2e6; padding: 14px; background: #f8f9fa;">
                        </div>

                        <hr class="my-3">

                        <!-- New Password -->
                        <div class="mb-3">
                            <label for="new_password" class="form-label fw-semibold">
                                <i class="bi bi-lock me-1"></i> New Password
                            </label>
                            <input type="password"
                                   class="form-control form-control-lg"
                                   id="new_password"
                                   name="new_password"
                                   placeholder="Create a new password (min. 6 characters)"
                                   minlength="6"
                                   required
                                   style="border-radius: 10px;">
                        </div>

                        <!-- Confirm Password -->
                        <div class="mb-4">
                            <label for="confirm_password" class="form-label fw-semibold">
                                <i class="bi bi-lock-fill me-1"></i> Confirm New Password
                            </label>
                            <input type="password"
                                   class="form-control form-control-lg"
                                   id="confirm_password"
                                   name="confirm_password"
                                   placeholder="Re-enter your new password"
                                   minlength="6"
                                   required
                                   style="border-radius: 10px;">
                            <div id="passwordMatchMsg" class="form-text mt-1"></div>
                        </div>

                        <div class="d-grid mb-3">
                            <button type="submit" id="resetBtn"
                                    class="btn btn-kravyo-primary btn-lg fw-bold"
                                    style="border-radius: 10px;">
                                <i class="bi bi-check-circle me-2"></i> Reset Password
                            </button>
                        </div>
                    </form>

                    <hr class="my-3">

                    <!-- Resend Section -->
                    <div class="text-center">
                        <p class="text-muted small mb-2">Didn't receive the code?</p>

                        <?php if ($cooldown > 0): ?>
                            <button class="btn btn-outline-secondary btn-sm" disabled>
                                <i class="bi bi-arrow-clockwise me-1"></i>
                                Resend Code
                                <span class="badge bg-secondary ms-1" id="resendCountdown"><?= $cooldown ?>s</span>
                            </button>
                        <?php else: ?>
                            <form action="<?= url('/resend-reset-otp') ?>" method="POST" class="d-inline" id="resendForm">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-outline-warning fw-semibold btn-sm"
                                        id="resendBtn" style="border-radius: 8px;">
                                    <i class="bi bi-arrow-clockwise me-1"></i> Resend Code
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>

                    <div class="text-center mt-4 pt-3 border-top">
                        <a href="<?= url('/login') ?>" class="text-decoration-none text-muted small">
                            <i class="bi bi-arrow-left me-1"></i> Back to Login
                        </a>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

<script>
(function () {
    'use strict';

    // ── OTP digits only ───────────────────────────────────────────────────────
    const otpInput = document.getElementById('otp');
    if (otpInput) {
        otpInput.addEventListener('input', function () {
            this.value = this.value.replace(/\D/g, '');
        });
        otpInput.addEventListener('keydown', function (e) {
            const allowed = ['Backspace','Delete','Tab','ArrowLeft','ArrowRight','Home','End'];
            if (allowed.includes(e.key) || e.ctrlKey || e.metaKey) return;
            if (!/^\d$/.test(e.key)) e.preventDefault();
        });
    }

    // ── Password match indicator ──────────────────────────────────────────────
    const newPwd     = document.getElementById('new_password');
    const confirmPwd = document.getElementById('confirm_password');
    const matchMsg   = document.getElementById('passwordMatchMsg');

    function checkMatch() {
        if (!confirmPwd.value) { matchMsg.innerHTML = ''; return; }
        if (newPwd.value === confirmPwd.value) {
            matchMsg.innerHTML = '<span class="text-success"><i class="bi bi-check-circle me-1"></i>Passwords match</span>';
        } else {
            matchMsg.innerHTML = '<span class="text-danger"><i class="bi bi-x-circle me-1"></i>Passwords do not match</span>';
        }
    }
    if (newPwd && confirmPwd) {
        newPwd.addEventListener('input', checkMatch);
        confirmPwd.addEventListener('input', checkMatch);
    }

    // ── Client-side password match before submit ──────────────────────────────
    const resetForm = document.getElementById('resetForm');
    const resetBtn  = document.getElementById('resetBtn');
    if (resetForm && resetBtn) {
        resetForm.addEventListener('submit', function (e) {
            if (newPwd.value !== confirmPwd.value) {
                e.preventDefault();
                matchMsg.innerHTML = '<span class="text-danger"><i class="bi bi-x-circle me-1"></i>Passwords do not match. Please try again.</span>';
                confirmPwd.focus();
                return;
            }
            resetBtn.disabled = true;
            resetBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Resetting…';
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
    if (resendCountdownEl) {
        let cooldown = <?= (int) ($cooldown ?? 0) ?>;
        const resendTick = setInterval(function () {
            cooldown--;
            if (cooldown <= 0) {
                clearInterval(resendTick);
                const container = resendCountdownEl.closest('.text-center');
                if (container) {
                    container.innerHTML =
                        '<p class="text-muted small mb-2">Didn\'t receive the code?</p>' +
                        '<form action="<?= url('/resend-reset-otp') ?>" method="POST" class="d-inline">' +
                        '<input type="hidden" name="_csrf" value="<?= csrf_token() ?>">' +
                        '<button type="submit" class="btn btn-outline-warning fw-semibold btn-sm" style="border-radius:8px;">' +
                        '<i class="bi bi-arrow-clockwise me-1"></i> Resend Code' +
                        '</button></form>';
                }
                return;
            }
            resendCountdownEl.textContent = cooldown + 's';
        }, 1000);
    }

    // ── Resend button spinner ─────────────────────────────────────────────────
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
