<footer>
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-6">
                <a href="<?= url('/') ?>" class="d-inline-block mb-3 text-decoration-none">
                    <img src="<?= url('/assets/images/kravyo-logo.png') ?>" alt="Kravyo" height="35" style="object-fit: contain; transform: scale(1.8); transform-origin: left center;">
                </a>
                <p class="small text-white-50">
                    Empowering homemakers, home chefs, and small food businesses to sell authentic, hygienic home-cooked meals without physical restaurant investment.
                </p>
            </div>
            <div class="col-6 col-lg-3">
                <h6 class="text-white fw-bold mb-3">Quick Links</h6>
                <ul class="list-unstyled small">
                    <li class="mb-2"><a href="<?= url('/kitchens') ?>">Explore Kitchens</a></li>
                    <li class="mb-2"><a href="<?= url('/menu') ?>">Dish Catalog</a></li>
                    <li class="mb-2"><a href="<?= url('/zero-waste') ?>">Zero Waste Section</a></li>
                    <li class="mb-2"><a href="<?= url('/admin/login') ?>" class="text-white-50"><i class="bi bi-shield-lock"></i> Admin Portal</a></li>
                </ul>
            </div>
            <div class="col-6 col-lg-3">
                <h6 class="text-white fw-bold mb-3">For Home Chefs</h6>
                <ul class="list-unstyled small">
                    <li class="mb-2"><a href="<?= url('/register') ?>">Partner with Us</a></li>
                    <li class="mb-2"><a href="<?= url('/login') ?>">Chef Portal Login</a></li>
                    <li class="mb-2"><a href="<?= url('/about') ?>">Hygiene Standards</a></li>
                </ul>
            </div>
        </div>
        <hr class="my-4 border-secondary">
        <div class="text-center small">
            <p class="mb-0 text-white-50">&copy; <?= date('Y') ?> Kravyo Platform. Built for Homemakers & Small Food Businesses.</p>
        </div>
    </div>
</footer>
