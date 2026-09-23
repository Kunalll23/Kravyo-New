<div class="container">
    <div class="row justify-content-center">
        <div class="col-12 col-sm-10 col-md-7 col-lg-5 col-xl-4">

            <!-- Logo / Branding -->
            <div class="text-center mb-4">
                <div class="mb-3">
                    <span style="font-size:2.8rem;">🛡️</span>
                </div>
                <h1 class="fw-bold text-white mb-1" style="font-size:1.6rem; letter-spacing:-0.5px;">Kravyo Admin</h1>
                <p class="text-white-50 mb-0" style="font-size:0.9rem;">Platform Administration Portal</p>
            </div>

            <!-- Flash Messages -->
            <?php
            $flashes = Session::getFlashes();
            foreach ($flashes as $type => $message):
                $safeType = preg_match('/^(success|danger|warning|info)$/', $type) ? $type : 'info';
            ?>
                <div class="alert alert-<?= $safeType ?> alert-dismissible fade show mb-4" role="alert">
                    <?= $message ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endforeach; ?>

            <!-- Login Card -->
            <div class="card border-0 shadow-lg" style="border-radius:20px; backdrop-filter:blur(20px); background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.1);">
                <div class="card-body p-4 p-md-5">
                    <h2 class="fw-bold text-white mb-1" style="font-size:1.4rem;">Sign In</h2>
                    <p class="mb-4" style="color:rgba(255,255,255,0.5); font-size:0.88rem;">Access restricted to authorised administrators only.</p>

                    <form action="<?= url('/admin/login') ?>" method="POST" id="adminLoginForm" novalidate>
                        <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">

                        <!-- Email -->
                        <div class="mb-3">
                            <label for="adminEmail" class="form-label fw-semibold" style="color:rgba(255,255,255,0.8); font-size:0.9rem;">
                                <i class="bi bi-envelope me-1"></i> Admin Email
                            </label>
                            <input
                                type="email"
                                class="form-control form-control-lg"
                                id="adminEmail"
                                name="email"
                                placeholder="admin@kravyo.com"
                                autocomplete="email"
                                required
                                style="background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.15); color:#fff; border-radius:12px;"
                            >
                        </div>

                        <!-- Password -->
                        <div class="mb-4">
                            <label for="adminPassword" class="form-label fw-semibold" style="color:rgba(255,255,255,0.8); font-size:0.9rem;">
                                <i class="bi bi-lock me-1"></i> Password
                            </label>
                            <div class="input-group">
                                <input
                                    type="password"
                                    class="form-control form-control-lg"
                                    id="adminPassword"
                                    name="password"
                                    placeholder="••••••"
                                    autocomplete="current-password"
                                    minlength="6"
                                    maxlength="6"
                                    pattern="[0-9]{6}"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6);"
                                    required
                                    style="background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.15); border-right:none; color:#fff; border-radius:12px 0 0 12px;"
                                >
                                <button
                                    type="button"
                                    class="btn"
                                    id="togglePassword"
                                    style="background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.15); border-left:none; color:rgba(255,255,255,0.6); border-radius:0 12px 12px 0;"
                                    onclick="document.getElementById('adminPassword').type = document.getElementById('adminPassword').type === 'password' ? 'text' : 'password'; this.innerHTML = this.innerHTML.includes('eye-slash') ? '<i class=\'bi bi-eye\'></i>' : '<i class=\'bi bi-eye-slash\'></i>'"
                                >
                                    <i class="bi bi-eye-slash"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Submit -->
                        <button type="submit" class="btn btn-warning btn-lg w-100 fw-bold" style="border-radius:12px; font-size:1rem; letter-spacing:0.3px;">
                            <i class="bi bi-shield-lock me-2"></i> Sign In to Admin Panel
                        </button>
                    </form>
                </div>
            </div>

            <!-- Back to site link -->
            <div class="text-center mt-4">
                <a href="<?= url('/') ?>" class="text-white-50 text-decoration-none" style="font-size:0.85rem;">
                    <i class="bi bi-arrow-left me-1"></i> Back to Kravyo
                </a>
            </div>

        </div>
    </div>
</div>

<style>
    /* Placeholder colour fix for dark inputs */
    #adminEmail::placeholder,
    #adminPassword::placeholder {
        color: rgba(255, 255, 255, 0.3);
    }
    #adminEmail:focus,
    #adminPassword:focus {
        background: rgba(255, 255, 255, 0.12) !important;
        border-color: rgba(255, 193, 7, 0.6) !important;
        box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.15);
        color: #fff !important;
        outline: none;
    }
</style>
