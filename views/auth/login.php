<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card kravyo-card border-0">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <?php if (!empty($isChef)): ?>
                            <i class="bi bi-egg-fried display-4 text-warning mb-2"></i>
                        <?php else: ?>
                            <i class="bi bi-person-circle display-4 text-warning mb-2"></i>
                        <?php endif; ?>
                        <h3 class="fw-bold"><?= sanitize($loginTitle ?? 'Welcome Back') ?></h3>
                        <p class="text-muted"><?= sanitize($loginSubtitle ?? 'Login to your Kravyo account') ?></p>
                    </div>

                    <?php View::partial('flash_messages'); ?>

                    <form action="<?= $loginAction ?? url('/login') ?>" method="POST">
                        <?= csrf_field() ?>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">Email address</label>
                            <?php $oldEmail = Session::get('_old_email', ''); Session::remove('_old_email'); ?>
                            <input type="email" class="form-control form-control-lg" id="email" name="email" placeholder="Enter your email address" value="<?= sanitize($oldEmail) ?>" required autofocus>
                        </div>
                        
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <label for="password" class="form-label fw-semibold mb-0">Password</label>
                                <a href="<?= url('/forgot-password') ?>" class="text-decoration-none small text-muted">Forgot password?</a>
                            </div>
                            <input type="password" class="form-control form-control-lg mt-2" id="password" name="password" placeholder="Enter your 6-digit password" minlength="6" maxlength="6" pattern="[0-9]{6}" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6);" required>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-kravyo-primary btn-lg"><?= !empty($isChef) ? 'Login to Chef Dashboard' : 'Log In' ?></button>
                        </div>
                    </form>

                    <div class="text-center mt-4 pt-3 border-top">
                        <?php if (!empty($isChef)): ?>
                            <p class="mb-0 text-muted">Want to register as a Home Chef? <a href="<?= url('/register') ?>" class="text-decoration-none fw-bold text-primary">Join Kravyo</a></p>
                        <?php else: ?>
                            <p class="mb-0 text-muted">Don't have an account? <a href="<?= url('/register') ?>" class="text-decoration-none fw-bold text-primary">Create one</a></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
