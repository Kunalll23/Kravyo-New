<header>
    <nav class="navbar navbar-expand-lg navbar-kravyo">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="<?= url('/') ?>">
                <i class="bi bi-fire text-warning"></i>
                <span>Kravyo</span>
            </a>
            
            <button class="navbar-toggler text-white border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarKravyoContent">
                <i class="bi bi-list fs-2 text-white"></i>
            </button>

            <div class="collapse navbar-collapse" id="navbarKravyoContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4">
                    <li class="nav-item">
                        <a class="nav-link active" href="<?= url('/') ?>"><i class="bi bi-house-door me-1"></i> Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= url('/kitchens') ?>"><i class="bi bi-shop me-1"></i> Explore Kitchens</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= url('/menu') ?>"><i class="bi bi-journal-text me-1"></i> Browse Dishes</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= url('/subscriptions') ?>"><i class="bi bi-calendar2-week me-1"></i> Tiffin Plans</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-warning fw-semibold" href="<?= url('/zero-waste') ?>">
                            <i class="bi bi-tag-fill me-1"></i> Zero Waste Deals
                        </a>
                    </li>
                </ul>

                <div class="d-flex align-items-center gap-3">
                    <?php if (Session::has('user_id')): ?>
                        <!-- Logged-in User Dropdown -->
                        <div class="dropdown">
                            <button class="btn btn-outline-light dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle"></i>
                                <span><?= sanitize(Session::get('user_name', 'Account')) ?></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <?php if (Session::get('user_role') === ROLE_CUSTOMER): ?>
                                    <li><a class="dropdown-item" href="<?= url('/orders/history') ?>"><i class="bi bi-bag-check me-2"></i> My Orders</a></li>
                                <?php elseif (Session::get('user_role') === ROLE_CHEF): ?>
                                    <li><a class="dropdown-item" href="<?= url('/chef/dashboard') ?>"><i class="bi bi-speedometer2 me-2"></i> Chef Dashboard</a></li>
                                    <li><a class="dropdown-item" href="<?= url('/chef/menu') ?>"><i class="bi bi-egg-fried me-2"></i> Manage Menu</a></li>
                                <?php elseif (Session::get('user_role') === ROLE_ADMIN): ?>
                                    <li><a class="dropdown-item" href="<?= url('/admin/dashboard') ?>"><i class="bi bi-shield-lock me-2"></i> Admin Panel</a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="<?= url('/logout') ?>" method="POST" class="d-inline">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i> Logout</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <!-- Guest Navigation Links -->
                        <a href="<?= url('/login') ?>" class="btn btn-light border text-dark btn-sm px-3">Login</a>
                        <a href="<?= url('/register') ?>" class="btn btn-kravyo-primary btn-sm px-3">Register / Join Chef</a>
                    <?php endif; ?>

                    <!-- Shopping Cart Icon -->
                    <?php
                    $cartItems = Session::get('cart', []);
                    $cartCount = 0;
                    if (!empty($cartItems['items'])) {
                        foreach ($cartItems['items'] as $ci) {
                            $cartCount += (int) ($ci['quantity'] ?? 0);
                        }
                    }
                    ?>
                    <a href="<?= url('/cart') ?>" class="btn btn-warning position-relative text-dark fw-bold">
                        <i class="bi bi-cart3"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger cart-badge-count <?= $cartCount === 0 ? 'd-none' : '' ?>">
                            <?= $cartCount ?>
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </nav>
</header>
