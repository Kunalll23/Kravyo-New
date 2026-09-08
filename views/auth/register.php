<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card kravyo-card border-0">
                <div class="card-body p-4 p-md-5">
                    <div class="text-center mb-4">
                        <i class="bi bi-person-plus display-4 text-warning mb-2"></i>
                        <h3 class="fw-bold">Join Kravyo</h3>
                        <p class="text-muted">Create an account to order food or start your kitchen</p>
                    </div>

                    <form action="<?= url('/register') ?>" method="POST" id="registerForm">
                        <?= csrf_field() ?>
                        
                        <div class="mb-4 text-center">
                            <label class="form-label fw-bold d-block mb-3">I want to join as a:</label>
                            <div class="btn-group" role="group" aria-label="Role selection">
                                <input type="radio" class="btn-check" name="role" id="roleCustomer" value="customer" autocomplete="off" checked>
                                <label class="btn btn-outline-warning text-dark fw-semibold px-4 py-2" for="roleCustomer">
                                    <i class="bi bi-person-heart me-2 text-danger"></i>Customer
                                </label>

                                <input type="radio" class="btn-check" name="role" id="roleChef" value="chef" autocomplete="off">
                                <label class="btn btn-outline-warning text-dark fw-semibold px-4 py-2" for="roleChef">
                                    <i class="bi bi-shop me-2"></i>Home Chef
                                </label>
                            </div>
                        </div>

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
                                <input type="tel" class="form-control form-control-lg border-start-0" id="phone" name="phone" placeholder="Enter your 10-digit mobile number" pattern="[6-9][0-9]{9}" title="Please enter a valid Indian mobile number starting with 6, 7, 8, or 9" required>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="password" class="form-label fw-semibold">Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock text-muted"></i></span>
                                <input type="password" class="form-control form-control-lg border-start-0" id="password" name="password" placeholder="Create a password" minlength="6" required>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-kravyo-primary btn-lg">Create Account</button>
                        </div>
                    </form>

                    <div class="text-center mt-4 pt-3 border-top">
                        <p class="mb-0 text-muted">Already have an account? <a href="<?= url('/login') ?>" class="text-decoration-none fw-bold text-primary">Log in here</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
