<?php
/**
 * Kravyo — Forgot Password Page
 * Route: GET /forgot-password
 * User enters their registered email address to receive an OTP.
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
                        <span style="font-size: 2.8rem;">🔐</span>
                    </div>
                    <h4 class="fw-800 text-white mb-1">Forgot Password?</h4>
                    <p class="text-white-50 small mb-0">
                        Enter your registered email address.<br>
                        We'll send you a 6-digit reset code.
                    </p>
                </div>

                <div class="card-body p-4 p-md-5">
                    <form action="<?= url('/forgot-password') ?>" method="POST" id="forgotForm">
                        <?= csrf_field() ?>

                        <div class="mb-4">
                            <label for="email" class="form-label fw-semibold">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-envelope text-muted"></i>
                                </span>
                                <input type="email"
                                       class="form-control form-control-lg border-start-0"
                                       id="email"
                                       name="email"
                                       placeholder="Enter your registered email"
                                       value="<?= sanitize($_GET['email'] ?? '') ?>"
                                       required autofocus>
                            </div>
                            <div class="form-text">
                                <i class="bi bi-info-circle me-1"></i>
                                Enter the email you used to register on Kravyo.
                            </div>
                        </div>

                        <div class="d-grid mb-3">
                            <button type="submit" id="submitBtn" class="btn btn-kravyo-primary btn-lg fw-bold"
                                    style="border-radius: 10px;">
                                <i class="bi bi-send me-2"></i> Send Reset Code
                            </button>
                        </div>
                    </form>

                    <div class="text-center mt-3 pt-3 border-top">
                        <a href="<?= url('/login') ?>" class="text-decoration-none text-muted small">
                            <i class="bi bi-arrow-left me-1"></i> Back to Login
                        </a>
                    </div>
                </div>
            </div>

            <div class="text-center mt-4">
                <p class="text-muted small">
                    <i class="bi bi-shield-lock me-1"></i>
                    For security, the code expires in <strong><?= OTP_EXPIRY_MINUTES ?> minutes</strong>.
                </p>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const form = document.getElementById('forgotForm');
    const btn  = document.getElementById('submitBtn');
    if (form && btn) {
        form.addEventListener('submit', function () {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sending…';
        });
    }
})();
</script>
