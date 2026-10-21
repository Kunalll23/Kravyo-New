<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card kravyo-card border-0">
                <div class="card-body p-4 p-md-5">
                    <div class="text-center mb-4">
                        <i class="bi bi-bicycle display-4 text-warning mb-2"></i>
                        <h3 class="fw-bold">Become a Delivery Partner</h3>
                        <p class="text-muted">Earn on your own schedule by delivering delicious home-cooked meals.</p>
                    </div>

                    <?php View::partial('flash_messages'); ?>

                    <form action="<?= url('/delivery/register') ?>" method="POST" id="registerForm">
                        <?= csrf_field() ?>

                        <h5 class="fw-bold mb-3 border-bottom pb-2">Personal Details</h5>
                        <div class="mb-3">
                            <label for="full_name" class="form-label fw-semibold">Full Name</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-person text-muted"></i></span>
                                <input type="text" class="form-control form-control-lg border-start-0" id="full_name" name="full_name" placeholder="Enter your full name" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                                <input type="email" class="form-control form-control-lg border-start-0" id="email" name="email" placeholder="Enter your email address" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label fw-semibold">Phone Number</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-telephone text-muted"></i></span>
                                <input type="tel" class="form-control form-control-lg border-start-0" id="phone" name="phone" placeholder="10-digit mobile number" pattern="[6-9][0-9]{9}" required maxlength="10" minlength="10" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold">Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock text-muted"></i></span>
                                <input type="password" class="form-control form-control-lg border-start-0" id="password" name="password" placeholder="Create a 6-digit password" minlength="6" maxlength="6" pattern="[0-9]{6}" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6);" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="confirm_password" class="form-label fw-semibold">Confirm Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-shield-lock text-muted"></i></span>
                                <input type="password" class="form-control form-control-lg border-start-0" id="confirm_password" name="confirm_password" placeholder="Re-enter your 6-digit password" minlength="6" maxlength="6" pattern="[0-9]{6}" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6);" required>
                            </div>
                            <div id="passwordMatchMsg" class="form-text mt-1"></div>
                        </div>

                        <h5 class="fw-bold mb-3 mt-4 border-bottom pb-2">Vehicle Details</h5>
                        
                        <div class="mb-3">
                            <label for="vehicle_type" class="form-label fw-semibold">Vehicle Type</label>
                            <select class="form-select form-select-lg" id="vehicle_type" name="vehicle_type" required>
                                <option value="scooter" selected>Scooter</option>
                                <option value="motorcycle">Motorcycle</option>
                                <option value="bicycle">Bicycle</option>
                                <option value="car">Car</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="vehicle_number" class="form-label fw-semibold">Vehicle Number</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-card-text text-muted"></i></span>
                                <input type="text" class="form-control form-control-lg border-start-0 text-uppercase" id="vehicle_number" name="vehicle_number" placeholder="e.g. MH 01 AB 1234" required>
                            </div>
                            <div class="form-text">For bicycles, enter "N/A".</div>
                        </div>

                        <div class="mb-4">
                            <label for="driving_license_number" class="form-label fw-semibold">Driving License Number</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-person-badge text-muted"></i></span>
                                <input type="text" class="form-control form-control-lg border-start-0 text-uppercase" id="driving_license_number" name="driving_license_number" placeholder="Enter DL number" required>
                            </div>
                            <div class="form-text">For bicycles, enter "N/A" or Aadhaar number.</div>
                        </div>
                        
                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" id="deliverySubmitBtn" class="btn btn-kravyo-primary btn-lg">Submit Application</button>
                        </div>
                    </form>

                    <div class="text-center mt-4 pt-3 border-top">
                        <p class="mb-0 text-muted">Already a partner? <a href="<?= url('/delivery/login') ?>" class="text-decoration-none fw-bold text-primary">Log in here</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form       = document.getElementById('registerForm');
    const btn        = document.getElementById('deliverySubmitBtn');
    const pwd        = document.getElementById('password');
    const confirmPwd = document.getElementById('confirm_password');
    const matchMsg   = document.getElementById('passwordMatchMsg');

    function checkPasswordMatch() {
        if (!confirmPwd || !pwd || !matchMsg) return;
        const p1 = pwd.value;
        const p2 = confirmPwd.value;

        if (p2.length === 0) {
            matchMsg.innerHTML = '';
            confirmPwd.classList.remove('is-valid', 'is-invalid');
            return;
        }

        if (p1 === p2 && p1.length === 6) {
            matchMsg.innerHTML = '<span class="text-success small fw-semibold"><i class="bi bi-check-circle-fill me-1"></i>Passwords match!</span>';
            confirmPwd.classList.add('is-valid');
            confirmPwd.classList.remove('is-invalid');
        } else if (p2.length === 6 && p1 !== p2) {
            matchMsg.innerHTML = '<span class="text-danger small fw-semibold"><i class="bi bi-x-circle-fill me-1"></i>Passwords do not match.</span>';
            confirmPwd.classList.add('is-invalid');
            confirmPwd.classList.remove('is-valid');
        } else {
            matchMsg.innerHTML = '';
            confirmPwd.classList.remove('is-valid', 'is-invalid');
        }
    }

    if (pwd && confirmPwd) {
        pwd.addEventListener('input', checkPasswordMatch);
        confirmPwd.addEventListener('input', checkPasswordMatch);
    }

    if (form && btn) {
        form.addEventListener('submit', function (e) {
            if (!form.checkValidity()) {
                return;
            }

            if (pwd && confirmPwd && pwd.value !== confirmPwd.value) {
                e.preventDefault();
                matchMsg.innerHTML = '<span class="text-danger small fw-semibold"><i class="bi bi-exclamation-triangle-fill me-1"></i>Passwords do not match. Please re-enter.</span>';
                confirmPwd.classList.add('is-invalid');
                confirmPwd.focus();
                return;
            }

            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Submitting Application...';
        });
    }
});
</script>
