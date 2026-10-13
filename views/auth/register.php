<?php
$role = $_GET['role'] ?? 'customer';
if ($role !== 'chef') $role = 'customer';
$roleName = ($role === 'chef') ? 'Home Chef' : 'Customer';
$iconClass = ($role === 'chef') ? 'bi-shop text-warning' : 'bi-person-heart text-danger';
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
                                    <option value="Ahmedabad">Ahmedabad</option>
                                    <option value="Bengaluru">Bengaluru</option>
                                    <option value="Delhi">Delhi</option>
                                    <option value="Mumbai">Mumbai</option>
                                    <option value="Pune">Pune</option>
                                    <option value="Surat">Surat</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="password" class="form-label fw-semibold">Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock text-muted"></i></span>
                                <input type="password" class="form-control form-control-lg border-start-0" id="password" name="password" placeholder="Create a 6-digit password" minlength="6" maxlength="6" pattern="[0-9]{6}" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6);" required>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-kravyo-primary btn-lg">Create Account</button>
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
