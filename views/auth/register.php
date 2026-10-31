<?php
$role = $role ?? $_GET['role'] ?? 'customer';
if ($role !== 'chef') $role = 'customer';
$roleName = ($role === 'chef') ? 'Home Chef' : 'Customer';
$iconClass = ($role === 'chef') ? 'bi-shop text-warning' : 'bi-person-heart text-danger';

// Core operational cities on Kravyo
$operatingCities = [
    'Surat',
    'Bardoli',
    'Ahmedabad',
    'Mumbai',
    'Pune',
    'Delhi',
    'Bangalore',
    'Hyderabad',
    'Jaipur',
];

// Dynamically merge any approved kitchen cities from database
try {
    require_once APP_PATH . '/models/Kitchen.php';
    $dbKitchenCities = (new Kitchen())->getDistinctCities();
    if (!empty($dbKitchenCities)) {
        $operatingCities = array_values(array_unique(array_merge($operatingCities, $dbKitchenCities)));
    }
} catch (\Throwable $e) {}

// City display label overrides
$cityLabels = [
    'Bangalore' => 'Bangalore (Bengaluru)',
    'Delhi'     => 'Delhi / NCR',
];

// Comprehensive catalog of all major Indian cities across states and UTs
$allIndianCities = [
    'Agra', 'Ahmedabad', 'Ajmer', 'Aligarh', 'Allahabad (Prayagraj)', 'Amravati', 'Amritsar', 
    'Anand', 'Asansol', 'Aurangabad (Chhatrapati Sambhaji Nagar)', 'Bangalore', 'Bardoli', 
    'Bareilly', 'Belgaum (Belagavi)', 'Bhavnagar', 'Bhilai', 'Bhopal', 'Bhubaneswar', 
    'Bikaner', 'Bokaro Steel City', 'Chandigarh', 'Chennai', 'Coimbatore', 'Cuttack', 
    'Dehradun', 'Delhi', 'Dhanbad', 'Durgapur', 'Erode', 'Faridabad', 'Firozabad', 
    'Gandhinagar', 'Ghaziabad', 'Gorakhpur', 'Gulbarga (Kalaburagi)', 'Guntur', 
    'Gurgaon (Gurugram)', 'Guwahati', 'Gwalior', 'Hubli-Dharwad', 'Hyderabad', 'Indore', 
    'Jabalpur', 'Jaipur', 'Jalandhar', 'Jalgaon', 'Jammu', 'Jamnagar', 'Jamshedpur', 
    'Jhansi', 'Jodhpur', 'Kalyan-Dombivli', 'Kanpur', 'Kochi (Cochin)', 'Kolhapur', 
    'Kolkata', 'Kollam', 'Kota', 'Kozhikode (Calicut)', 'Lucknow', 'Ludhiana', 'Madurai', 
    'Mangalore (Mangaluru)', 'Meerut', 'Moradabad', 'Mumbai', 'Muzaffarnagar', 'Mysore (Mysuru)', 
    'Nagpur', 'Nanded', 'Nashik', 'Navsari', 'Navi Mumbai', 'Nellore', 'Noida', 'Patna', 
    'Pondicherry (Puducherry)', 'Pune', 'Raipur', 'Rajahmundry', 'Rajkot', 'Ranchi', 
    'Rourkela', 'Saharanpur', 'Salem', 'Sangli', 'Shimla', 'Siliguri', 'Solapur', 
    'Srinagar', 'Surat', 'Thane', 'Thiruvananthapuram (Trivandrum)', 'Thrissur', 
    'Tiruchirappalli (Trichy)', 'Tirunelveli', 'Tirupati', 'Tiruppur', 'Udaipur', 
    'Ujjain', 'Vadodara (Baroda)', 'Valsad', 'Vapi', 'Varanasi', 'Vasai-Virar', 
    'Vijayawada', 'Visakhapatnam', 'Warangal'
];

$otherCities = array_values(array_diff($allIndianCities, $operatingCities));
sort($otherCities);
?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card kravyo-card border-0">
                <div class="card-body p-4 p-md-5">
                    <div class="text-center mb-4">
                        <i class="bi <?= $iconClass ?> display-4 mb-2"></i>
                        <h3 class="fw-bold">Join as <?= $roleName ?></h3>
                        <p class="text-muted">Create an account to <?= $role === 'chef' ? 'start your kitchen' : 'order food' ?></p>
                    </div>

                    <div class="d-flex justify-content-center mb-4">
                        <div class="btn-group p-1 bg-light rounded-pill border" role="group">
                            <a href="<?= url('/register?role=customer') ?>" class="btn btn-sm rounded-pill px-3 <?= $role === 'customer' ? 'btn-danger text-white shadow-sm fw-bold' : 'text-muted' ?>">
                                <i class="bi bi-person-heart me-1"></i> Customer
                            </a>
                            <a href="<?= url('/register?role=chef') ?>" class="btn btn-sm rounded-pill px-3 <?= $role === 'chef' ? 'btn-warning text-dark shadow-sm fw-bold' : 'text-muted' ?>">
                                <i class="bi bi-shop me-1"></i> Home Chef
                            </a>
                            <a href="<?= url('/delivery/register') ?>" class="btn btn-sm rounded-pill px-3 text-muted">
                                <i class="bi bi-bicycle me-1"></i> Delivery Partner
                            </a>
                        </div>
                    </div>

                    <?php View::partial('alerts'); ?>

                    <form action="<?= url('/register') ?>" method="POST" id="registerForm">
                        <?= csrf_field() ?>
                        <input type="hidden" name="role" value="<?= $role ?>">

                        <div class="mb-3">
                            <label for="full_name" class="form-label fw-semibold">Full Name</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-person text-muted"></i></span>
                                <input type="text" class="form-control form-control-lg border-start-0" id="full_name" name="full_name" placeholder="Enter your full name" pattern="^[A-Za-z][A-Za-z' \-]{1,49}$" title="Please enter a valid name using letters, spaces, hyphens, or apostrophes only." required>
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
                                <input type="tel" class="form-control form-control-lg border-start-0" id="phone" name="phone" placeholder="Enter your 10-digit mobile number" pattern="[6-9][0-9]{9}" title="Please enter a valid Indian mobile number starting with 6, 7, 8, or 9" required maxlength="10" minlength="10" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="city" class="form-label fw-semibold">City</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-geo-alt text-muted"></i></span>
                                <select class="form-select form-control-lg border-start-0" id="city" name="city" required>
                                    <option value="" selected disabled>Select your city</option>
                                    <optgroup label="📍 Operating Cities (Active Kitchens & Delivery)">
                                        <?php foreach ($operatingCities as $c): ?>
                                            <option value="<?= htmlspecialchars($c, ENT_QUOTES) ?>">
                                                <?= htmlspecialchars($cityLabels[$c] ?? $c, ENT_QUOTES) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                    <optgroup label="🏙️ All Other Cities across India">
                                        <?php foreach ($otherCities as $c): ?>
                                            <option value="<?= htmlspecialchars($c, ENT_QUOTES) ?>">
                                                <?= htmlspecialchars($cityLabels[$c] ?? $c, ENT_QUOTES) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                    <optgroup label="✨ Can't find your city?">
                                        <option value="__other__">➕ Other City (Type manually...)</option>
                                    </optgroup>
                                </select>
                            </div>
                            <div id="otherCityContainer" class="mt-2 d-none">
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-pencil-square text-muted"></i></span>
                                    <input type="text" class="form-control form-control-lg border-start-0" id="other_city" name="other_city" placeholder="Type your city or town name" maxlength="50">
                                </div>
                                <div class="form-text text-muted">We will set your account to your registered city.</div>
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
                        
                        <!-- Sending OTP feedback notice -->
                        <div id="registerNotice" class="alert alert-success border-0 shadow-sm d-none align-items-center mb-3 py-2 px-3 rounded-3" style="background: #e8f5e9;">
                            <span class="spinner-border spinner-border-sm text-success me-2"></span>
                            <span class="small fw-semibold text-success"><i class="bi bi-envelope-check me-1"></i> Sending OTP to email... Please wait.</span>
                        </div>
                        
                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" id="registerSubmitBtn" class="btn btn-kravyo-primary btn-lg">
                                <i class="bi bi-shield-check me-2"></i>Verify Email
                            </button>
                        </div>
                    </form>

                    <div class="text-center mt-4 pt-3 border-top">
                        <p class="mb-0 text-muted">Already have an account? <a href="<?= url($role === 'chef' ? '/chef/login' : '/login') ?>" class="text-decoration-none fw-bold text-primary">Log in here</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form               = document.getElementById('registerForm');
    const btn                = document.getElementById('registerSubmitBtn');
    const notice             = document.getElementById('registerNotice');
    const pwd                = document.getElementById('password');
    const confirmPwd         = document.getElementById('confirm_password');
    const matchMsg           = document.getElementById('passwordMatchMsg');
    const citySelect         = document.getElementById('city');
    const otherCityContainer = document.getElementById('otherCityContainer');
    const otherCityInput     = document.getElementById('other_city');

    if (citySelect && otherCityContainer && otherCityInput) {
        citySelect.addEventListener('change', function () {
            if (this.value === '__other__') {
                otherCityContainer.classList.remove('d-none');
                otherCityInput.setAttribute('required', 'required');
                otherCityInput.focus();
            } else {
                otherCityContainer.classList.add('d-none');
                otherCityInput.removeAttribute('required');
                otherCityInput.classList.remove('is-invalid');
                otherCityInput.value = '';
            }
        });
    }

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

            // Verify custom city if selected
            if (citySelect && citySelect.value === '__other__') {
                const customCity = (otherCityInput ? otherCityInput.value.trim() : '');
                if (!customCity) {
                    e.preventDefault();
                    if (otherCityInput) {
                        otherCityInput.classList.add('is-invalid');
                        otherCityInput.focus();
                    }
                    return;
                }
                let customOption = citySelect.querySelector('option[data-custom="true"]');
                if (!customOption) {
                    customOption = document.createElement('option');
                    customOption.setAttribute('data-custom', 'true');
                    citySelect.appendChild(customOption);
                }
                customOption.value = customCity;
                customOption.textContent = customCity;
                citySelect.value = customCity;
            }

            // Verify password match
            if (pwd && confirmPwd && pwd.value !== confirmPwd.value) {
                e.preventDefault();
                matchMsg.innerHTML = '<span class="text-danger small fw-semibold"><i class="bi bi-exclamation-triangle-fill me-1"></i>Passwords do not match. Please re-enter.</span>';
                confirmPwd.classList.add('is-invalid');
                confirmPwd.focus();
                return;
            }

            if (notice) {
                notice.classList.remove('d-none');
                notice.classList.add('d-flex');
            }
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sending OTP to email...';
        });
    }
});
</script>
