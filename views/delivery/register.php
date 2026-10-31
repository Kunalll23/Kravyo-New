<?php
// Operational cities on Kravyo
$operatingCities = [
    'Surat', 'Bardoli', 'Ahmedabad', 'Mumbai', 'Pune', 'Delhi', 'Bangalore', 'Hyderabad', 'Jaipur'
];
try {
    require_once APP_PATH . '/models/Kitchen.php';
    $dbKitchenCities = (new Kitchen())->getDistinctCities();
    if (!empty($dbKitchenCities)) {
        $operatingCities = array_values(array_unique(array_merge($operatingCities, $dbKitchenCities)));
    }
} catch (\Throwable $e) {}

$cityLabels = [
    'Bangalore' => 'Bangalore (Bengaluru)',
    'Delhi'     => 'Delhi / NCR',
];

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
                        <i class="bi bi-bicycle display-4 text-warning mb-2"></i>
                        <h3 class="fw-bold">Become a Delivery Partner</h3>
                        <p class="text-muted">Earn on your own schedule by delivering delicious home-cooked meals.</p>
                    </div>

                    <div class="d-flex justify-content-center mb-4">
                        <div class="btn-group p-1 bg-light rounded-pill border" role="group">
                            <a href="<?= url('/register?role=customer') ?>" class="btn btn-sm rounded-pill px-3 text-muted">
                                <i class="bi bi-person-heart me-1"></i> Customer
                            </a>
                            <a href="<?= url('/register?role=chef') ?>" class="btn btn-sm rounded-pill px-3 text-muted">
                                <i class="bi bi-shop me-1"></i> Home Chef
                            </a>
                            <a href="<?= url('/delivery/register') ?>" class="btn btn-sm rounded-pill px-3 btn-warning text-dark shadow-sm fw-bold">
                                <i class="bi bi-bicycle me-1"></i> Delivery Partner
                            </a>
                        </div>
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
                            <label for="city" class="form-label fw-semibold">Operating City</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-geo-alt text-muted"></i></span>
                                <select class="form-select form-control-lg border-start-0" id="city" name="city" required>
                                    <option value="" selected disabled>Select your operating city</option>
                                    <optgroup label="📍 Operating Cities (Active Kitchen Hubs)">
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
                                    <input type="text" class="form-control form-control-lg border-start-0" id="other_city" name="other_city" placeholder="Type your operating city" maxlength="50">
                                </div>
                                <div class="form-text text-muted">Select or enter the city where you will fulfill deliveries.</div>
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
                                <input type="text" class="form-control form-control-lg border-start-0 text-uppercase" id="vehicle_number" name="vehicle_number" placeholder="e.g. GJ 05 AB 1234" required>
                            </div>
                            <div id="vehicleHelpText" class="form-text">Format: State + RTO + Number (e.g. GJ 05 AB 1234). For bicycles: "N/A".</div>
                            <div id="vehicleFeedback" class="text-danger small mt-1 fw-semibold"></div>
                        </div>

                        <div class="mb-4">
                            <label for="driving_license_number" class="form-label fw-semibold">Driving License Number</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-person-badge text-muted"></i></span>
                                <input type="text" class="form-control form-control-lg border-start-0 text-uppercase" id="driving_license_number" name="driving_license_number" placeholder="e.g. GJ0520200012345" required>
                            </div>
                            <div id="dlHelpText" class="form-text">Standard Indian DL (e.g. GJ0520200012345). For bicycles: enter "N/A" or 12-digit Aadhaar.</div>
                            <div id="dlFeedback" class="text-danger small mt-1 fw-semibold"></div>
                        </div>
                        
                        <!-- Sending OTP feedback notice -->
                        <div id="registerNotice" class="alert alert-success border-0 shadow-sm d-none align-items-center mb-3 py-2 px-3 rounded-3" style="background: #e8f5e9;">
                            <span class="spinner-border spinner-border-sm text-success me-2"></span>
                            <span class="small fw-semibold text-success"><i class="bi bi-envelope-check me-1"></i> Sending OTP to email... Please wait.</span>
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" id="deliverySubmitBtn" class="btn btn-kravyo-primary btn-lg">
                                <i class="bi bi-shield-check me-2"></i>Verify Email &amp; Submit Application
                            </button>
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
    const notice     = document.getElementById('registerNotice');
    const pwd        = document.getElementById('password');
    const confirmPwd = document.getElementById('confirm_password');
    const matchMsg   = document.getElementById('passwordMatchMsg');
    const citySelect = document.getElementById('city');
    const otherCityContainer = document.getElementById('otherCityContainer');
    const otherCityInput = document.getElementById('other_city');
    const vehicleTypeSelect = document.getElementById('vehicle_type');
    const vehicleNumberInput = document.getElementById('vehicle_number');
    const dlInput = document.getElementById('driving_license_number');
    const vehicleFeedback = document.getElementById('vehicleFeedback');
    const dlFeedback = document.getElementById('dlFeedback');

    // Handle city dropdown
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

    // Dynamic vehicle type handling
    if (vehicleTypeSelect && vehicleNumberInput && dlInput) {
        vehicleTypeSelect.addEventListener('change', function () {
            if (this.value === 'bicycle') {
                vehicleNumberInput.value = 'N/A';
                vehicleNumberInput.classList.remove('is-invalid');
                vehicleFeedback.textContent = '';
                dlInput.placeholder = 'Enter N/A or 12-digit Aadhaar';
            } else {
                if (vehicleNumberInput.value.trim().toUpperCase() === 'N/A') {
                    vehicleNumberInput.value = '';
                }
                dlInput.placeholder = 'e.g. GJ0520200012345';
            }
        });
    }

    // Password matching
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

    // Form submit validation
    if (form && btn) {
        form.addEventListener('submit', function (e) {
            if (!form.checkValidity()) {
                return;
            }

            // Custom City validation
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

            // Password match check
            if (pwd && confirmPwd && pwd.value !== confirmPwd.value) {
                e.preventDefault();
                matchMsg.innerHTML = '<span class="text-danger small fw-semibold"><i class="bi bi-exclamation-triangle-fill me-1"></i>Passwords do not match. Please re-enter.</span>';
                confirmPwd.classList.add('is-invalid');
                confirmPwd.focus();
                return;
            }

            // Vehicle Number & Driving License strict validation
            const vType = vehicleTypeSelect ? vehicleTypeSelect.value : 'scooter';
            const rawV = vehicleNumberInput ? vehicleNumberInput.value.trim() : '';
            const rawDL = dlInput ? dlInput.value.trim() : '';

            const normV = rawV.replace(/[^A-Za-z0-9]/g, '').toUpperCase();
            const normDL = rawDL.replace(/[^A-Za-z0-9]/g, '').toUpperCase();

            // Clear previous errors
            if (vehicleNumberInput) vehicleNumberInput.classList.remove('is-invalid');
            if (dlInput) dlInput.classList.remove('is-invalid');
            if (vehicleFeedback) vehicleFeedback.textContent = '';
            if (dlFeedback) dlFeedback.textContent = '';

            if (vType === 'bicycle') {
                const isAadhaar = (/^[0-9]{12}$/.test(normDL));
                const isNA = (normDL === 'NA' || normDL === 'NOTAPPLICABLE' || normDL === 'NONE');
                if (!isAadhaar && !isNA) {
                    e.preventDefault();
                    dlInput.classList.add('is-invalid');
                    dlFeedback.textContent = 'For bicycle, enter "N/A" or a valid 12-digit Aadhaar number.';
                    dlInput.focus();
                    return;
                }
            } else {
                // Vehicle pattern: 2 letters + 1-2 digits + 1-3 letters + 1-4 digits OR BH series
                const vehiclePattern = /^([A-Z]{2}[0-9]{1,2}[A-Z]{1,3}[0-9]{1,4}|[0-9]{2}BH[0-9]{4}[A-Z]{1,2})$/;
                if (!vehiclePattern.test(normV)) {
                    e.preventDefault();
                    vehicleNumberInput.classList.add('is-invalid');
                    vehicleFeedback.textContent = 'Invalid vehicle number format. Example: GJ 05 AB 1234 or MH01AB1234.';
                    vehicleNumberInput.focus();
                    return;
                }

                // DL pattern: State(2) + RTO(2) + Year(4) + Number(4-7), or 13-16 alphanumeric chars
                const dlPattern = /^([A-Z]{2}[0-9]{2}(?:19|20)[0-9]{2}[0-9]{4,7}|[A-Z]{2}[0-9]{11,14})$/;
                if (!dlPattern.test(normDL)) {
                    e.preventDefault();
                    dlInput.classList.add('is-invalid');
                    dlFeedback.textContent = 'Invalid Driving License format. Example: GJ0520200012345 or DL-04-2018-0001234.';
                    dlInput.focus();
                    return;
                }
            }

            if (notice) {
                notice.classList.remove('d-none');
                notice.classList.add('d-flex');
            }
            setTimeout(function() {
                btn.disabled = true;
            }, 50);
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sending OTP...';
        });
    }
});
</script>
