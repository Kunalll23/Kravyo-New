<!-- Hero Banner -->
<section class="hero-section text-center text-lg-start">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-7">
                <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold mb-3">
                    <i class="bi bi-heart-fill text-danger me-1"></i> Homemaker & Home Chef Revolution
                </span>
                <h1 class="hero-title mb-4">
                    Authentic Homemade Meals, Delivered from <span class="hero-highlight">Local Kitchens</span>
                </h1>
                <p class="lead text-light-50 mb-4 opacity-75">
                    Kravyo empowers passionate home chefs to monetize their culinary skills while bringing healthy, hygienic, home-cooked food to your doorstep.
                </p>
                <div class="d-flex flex-wrap gap-3 justify-content-center justify-content-lg-start">
                    <a href="<?= url('/kitchens') ?>" class="btn btn-kravyo-primary btn-lg">
                        <i class="bi bi-search me-2"></i> Explore Local Kitchens
                    </a>
                    <a href="<?= url('/register') ?>" class="btn btn-outline-light btn-lg">
                        <i class="bi bi-shop me-2"></i> Join as Home Chef
                    </a>
                </div>
            </div>
            <div class="col-lg-5 text-center">
                <div class="card kravyo-card p-4 text-dark text-start">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="bg-warning text-dark p-3 rounded-circle fs-3 fw-bold">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0">Verified Home Kitchens</h5>
                            <small class="text-muted">Hygiene & Authenticity Assured</small>
                        </div>
                    </div>
                    <ul class="list-group list-group-flush mb-3">
                        <li class="list-group-item bg-transparent ps-0"><i class="bi bi-check-circle-fill text-success me-2"></i> Custom Meal Preferences (Less oil, Jain, Diabetic)</li>
                        <li class="list-group-item bg-transparent ps-0"><i class="bi bi-check-circle-fill text-success me-2"></i> Tiffin Subscriptions (Weekly & Monthly)</li>
                        <li class="list-group-item bg-transparent ps-0"><i class="bi bi-check-circle-fill text-success me-2"></i> Zero Food Waste Discounted End-of-Day Deals</li>
                    </ul>
                    <a href="<?= url('/zero-waste') ?>" class="btn btn-kravyo-outline btn-sm w-100">
                        View Discounted End-of-Day Meals
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- System Features & Role Portals Overview -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Platform Capabilities & User Portals</h2>
            <p class="text-muted">Designed specifically for three core user roles</p>
        </div>

        <div class="row g-4">
            <!-- Customer Role -->
            <div class="col-md-4">
                <div class="card kravyo-card h-100 p-4">
                    <div class="fs-1 text-danger mb-3"><i class="bi bi-person-heart"></i></div>
                    <h4 class="fw-bold">Customer Portal</h4>
                    <p class="text-muted small">
                        Browse home kitchens by location & category, customize meal instructions (spice, oil, Jain), subscribe to tiffins, and place orders.
                    </p>
                    <a href="<?= url('/menu') ?>" class="mt-auto text-decoration-none fw-bold text-danger">Explore Customer View <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>

            <!-- Home Chef Role -->
            <div class="col-md-4">
                <div class="card kravyo-card h-100 p-4">
                    <div class="fs-1 text-warning mb-3"><i class="bi bi-shop"></i></div>
                    <h4 class="fw-bold">Home Chef / Seller Portal</h4>
                    <p class="text-muted small">
                        Manage kitchen profile & story, update dishes & pricing, manage tiffin subscriptions, view earnings, and list unsold meals for Zero Waste.
                    </p>
                    <a href="<?= url('/chef/dashboard') ?>" class="mt-auto text-decoration-none fw-bold text-warning">Explore Chef View <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>

            <!-- Administrator Role -->
            <div class="col-md-4">
                <div class="card kravyo-card h-100 p-4">
                    <div class="fs-1 text-primary mb-3"><i class="bi bi-shield-lock"></i></div>
                    <h4 class="fw-bold">Administrator Portal</h4>
                    <p class="text-muted small">
                        Verify home chefs, approve hygiene badges, manage food categories, monitor sales reports, and manage platform notifications.
                    </p>
                    <a href="<?= url('/admin/dashboard') ?>" class="mt-auto text-decoration-none fw-bold text-primary">Explore Admin View <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </div>
</section>
