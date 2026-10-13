<!-- Kravyo - Role Selection Landing Page -->
<section class="py-5" style="min-height: 100vh; display: flex; align-items: center; background-color: #f8f9fa;">
    <div class="container">
        <div class="text-center mb-5">
            <h1 class="fw-bold hero-title mb-3" style="color: #f47b20; font-size: 3.5rem;">Welcome to Kravyo</h1>
        </div>

        <div class="row g-4 justify-content-center">
            <!-- Customer Role -->
            <div class="col-sm-6 col-lg-3">
                <div class="card kravyo-card h-100 p-4 text-center d-flex flex-column shadow-sm border-0 rounded-4">
                    <div class="fs-1 text-danger mb-3"><i class="bi bi-person-heart"></i></div>
                    <h4 class="fw-bold text-dark">Customer</h4>
                    <p class="text-muted small flex-grow-1">
                        Order food, subscribe to tiffins, and manage your diet.
                    </p>
                    <div class="d-flex flex-column gap-2 mt-auto">
                        <a href="<?= url('/register?role=customer') ?>" class="btn btn-danger fw-bold w-100 py-2 rounded-pill shadow-sm">Join as Customer</a>
                    </div>
                </div>
            </div>

            <!-- Home Chef Role -->
            <div class="col-sm-6 col-lg-3">
                <div class="card kravyo-card h-100 p-4 text-center d-flex flex-column shadow-sm border-0 rounded-4">
                    <div class="fs-1 text-warning mb-3"><i class="bi bi-shop"></i></div>
                    <h4 class="fw-bold text-dark">Home Chef</h4>
                    <p class="text-muted small flex-grow-1">
                        Manage your kitchen, update menus, and process orders.
                    </p>
                    <div class="d-flex flex-column gap-2 mt-auto">
                        <a href="<?= url('/register?role=chef') ?>" class="btn btn-warning text-dark fw-bold w-100 py-2 rounded-pill shadow-sm">Join as Chef</a>
                    </div>
                </div>
            </div>

            <!-- Delivery Partner Role -->
            <div class="col-sm-6 col-lg-3">
                <div class="card kravyo-card h-100 p-4 text-center d-flex flex-column shadow-sm border-0 rounded-4">
                    <div class="fs-1 text-success mb-3"><i class="bi bi-bicycle"></i></div>
                    <h4 class="fw-bold text-dark">Delivery Partner</h4>
                    <p class="text-muted small flex-grow-1">
                        Accept delivery requests, track routes, and earn.
                    </p>
                    <div class="d-flex flex-column gap-2 mt-auto">
                        <a href="<?= url('/delivery/register') ?>" class="btn btn-success fw-bold w-100 py-2 rounded-pill shadow-sm">Join as Delivery Partner</a>
                    </div>
                </div>
            </div>

            <!-- Administrator Role -->
            <div class="col-sm-6 col-lg-3">
                <div class="card kravyo-card h-100 p-4 text-center d-flex flex-column shadow-sm border-0 rounded-4">
                    <div class="fs-1 text-primary mb-3"><i class="bi bi-shield-lock"></i></div>
                    <h4 class="fw-bold text-dark">Administrator</h4>
                    <p class="text-muted small flex-grow-1">
                        Monitor platform activity, verify users, and manage system.
                    </p>
                    <div class="d-flex flex-column gap-2 mt-auto">
                        <a href="<?= url('/admin/login') ?>" class="btn btn-primary fw-bold w-100 py-2 rounded-pill shadow-sm">Join as Admin</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
