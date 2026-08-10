<footer>
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4">
                <h5 class="text-white fw-bold d-flex align-items-center gap-2">
                    <i class="bi bi-fire text-warning"></i> Kravyo
                </h5>
                <p class="small text-muted">
                    Empowering homemakers, home chefs, and small food businesses to sell authentic, hygienic home-cooked meals without physical restaurant investment.
                </p>
            </div>
            <div class="col-6 col-lg-2">
                <h6 class="text-white fw-bold mb-3">Quick Links</h6>
                <ul class="list-unstyled small">
                    <li class="mb-2"><a href="<?= url('/kitchens') ?>">Explore Kitchens</a></li>
                    <li class="mb-2"><a href="<?= url('/menu') ?>">Dish Catalog</a></li>
                    <li class="mb-2"><a href="<?= url('/zero-waste') ?>">Zero Waste Section</a></li>
                </ul>
            </div>
            <div class="col-6 col-lg-2">
                <h6 class="text-white fw-bold mb-3">For Home Chefs</h6>
                <ul class="list-unstyled small">
                    <li class="mb-2"><a href="<?= url('/register') ?>">Partner with Us</a></li>
                    <li class="mb-2"><a href="<?= url('/login') ?>">Chef Portal Login</a></li>
                    <li class="mb-2"><a href="<?= url('/about') ?>">Hygiene Standards</a></li>
                </ul>
            </div>
            <div class="col-lg-4">
                <h6 class="text-white fw-bold mb-3">Academic Project Information</h6>
                <p class="small text-muted mb-1">Degree: Bachelor of Computer Applications (BCA)</p>
                <p class="small text-muted mb-1">Institute: B. V. Patel Institute of Computer Science, UTU</p>
                <p class="small text-muted">Guided By: Shivani Talaviya</p>
            </div>
        </div>
        <hr class="my-4 border-secondary">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center small">
            <p class="mb-0">&copy; <?= date('Y') ?> Kravyo Platform. Built for Homemakers & Small Food Businesses.</p>
            <p class="mb-0 text-muted">System Architecture: PHP 8 MVC + MySQL + Bootstrap 5</p>
        </div>
    </div>
</footer>
