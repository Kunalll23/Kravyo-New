<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card kravyo-card border-0">
                <div class="card-body p-4 p-md-5">
                    <div class="text-center mb-4">
                        <i class="bi bi-bicycle display-4 text-warning mb-2"></i>
                        <h3 class="fw-bold">Delivery Partner Login</h3>
                        <p class="text-muted">Welcome back! Let's get delivering.</p>
                    </div>

                    <?php View::partial('flash_messages'); ?>

                    <form action="<?= url('/delivery/login') ?>" method="POST">
                        <?= csrf_field() ?>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                                <input type="email" class="form-control form-control-lg border-start-0" id="email" name="email" placeholder="Enter your email address" required autofocus>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="password" class="form-label fw-semibold">Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock text-muted"></i></span>
                                <input type="password" class="form-control form-control-lg border-start-0" id="password" name="password" placeholder="Enter your password" required>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-kravyo-primary btn-lg">Login to Dashboard</button>
                        </div>
                    </form>

                    <div class="text-center mt-4 pt-3 border-top">
                        <p class="mb-0 text-muted">Want to join as a delivery partner? <a href="<?= url('/delivery/register') ?>" class="text-decoration-none fw-bold text-primary">Register here</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
