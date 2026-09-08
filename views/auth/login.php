<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card kravyo-card border-0">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="bi bi-person-circle display-4 text-warning mb-2"></i>
                        <h3 class="fw-bold">Welcome Back</h3>
                        <p class="text-muted">Login to your Kravyo account</p>
                    </div>

                    <form action="<?= url('/login') ?>" method="POST">
                        <?= csrf_field() ?>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">Email address</label>
                            <?php $oldEmail = Session::get('_old_email', ''); Session::remove('_old_email'); ?>
                            <input type="email" class="form-control form-control-lg" id="email" name="email" placeholder="Enter your email address" value="<?= sanitize($oldEmail) ?>" required autofocus>
                        </div>
                        
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <label for="password" class="form-label fw-semibold mb-0">Password</label>
                                <!-- Forgot password link placeholder for future implementation -->
                                <a href="<?= url('/forgot-password') ?>" class="text-decoration-none small text-muted">Forgot password?</a>
                            </div>
                            <input type="password" class="form-control form-control-lg mt-2" id="password" name="password" placeholder="Enter your password" required>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-kravyo-primary btn-lg">Log In</button>
                        </div>
                    </form>

                    <div class="text-center mt-4 pt-3 border-top">
                        <p class="mb-0 text-muted">Don't have an account? <a href="<?= url('/register') ?>" class="text-decoration-none fw-bold text-primary">Create one</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
