<header>
    <nav class="navbar navbar-expand-lg navbar-kravyo">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center me-4" href="<?= url('/') ?>">
                <img src="<?= url('/assets/images/kravyo-logo.png') ?>" alt="Kravyo" height="30" style="object-fit: contain; max-height: 30px; transform: scale(1.8); transform-origin: left center; margin-right: 60px;">
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
                    <?php if (Session::get('user_role') === ROLE_CUSTOMER): ?>
                    <li class="nav-item">
                        <a class="nav-link fw-semibold" href="<?= url('/recommendations') ?>"
                           style="background: linear-gradient(135deg,#6a0dad,#9b30ff); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
                            <i class="bi bi-stars me-1" style="-webkit-text-fill-color: #9b30ff;"></i> For You
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>

                <div class="d-flex align-items-center gap-3">
                    <?php if (Session::has('admin_id')): ?>
                        <!-- Logged-in Admin Dropdown -->
                        <div class="dropdown">
                            <button class="btn btn-warning dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-shield-lock"></i>
                                <span><?= sanitize(Session::get('admin_name', 'Admin')) ?></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="<?= url('/admin/dashboard') ?>"><i class="bi bi-speedometer2 me-2"></i> Admin Dashboard</a></li>
                                <li><a class="dropdown-item" href="<?= url('/admin/orders') ?>"><i class="bi bi-bag-check text-success me-2"></i> Platform Orders</a></li>
                                <li><a class="dropdown-item" href="<?= url('/admin/chefs') ?>"><i class="bi bi-shop text-warning me-2"></i> Manage Chefs</a></li>
                                <li><a class="dropdown-item" href="<?= url('/admin/users') ?>"><i class="bi bi-people text-info me-2"></i> Manage Users</a></li>
                                <li><a class="dropdown-item" href="<?= url('/admin/categories') ?>"><i class="bi bi-tags text-danger me-2"></i> Food Categories</a></li>
                                <li><a class="dropdown-item" href="<?= url('/admin/reviews') ?>"><i class="bi bi-star-fill text-warning me-2"></i> Manage Reviews</a></li>
                                <li><a class="dropdown-item" href="<?= url('/admin/zero-waste') ?>"><i class="bi bi-recycle text-success me-2"></i> Manage Zero Waste</a></li>
                                <li><a class="dropdown-item" href="<?= url('/admin/ai-recommendations') ?>"><i class="bi bi-cpu-fill text-primary me-2"></i> AI Recommendations</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="<?= url('/admin/logout') ?>" method="POST" class="d-inline">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i> Logout</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    <?php elseif (Session::has('user_id')): ?>
                        <!-- Logged-in User Dropdown -->
                        <div class="dropdown">
                            <button class="btn btn-outline-light dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle"></i>
                                <span><?= sanitize(Session::get('user_name', 'Account')) ?></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <?php if (Session::get('user_role') === ROLE_CUSTOMER): ?>
                                    <li><a class="dropdown-item" href="<?= url('/profile') ?>"><i class="bi bi-person me-2"></i> My Profile</a></li>
                                    <li><a class="dropdown-item" href="<?= url('/orders/history') ?>"><i class="bi bi-bag-check me-2"></i> My Orders</a></li>
                                <?php elseif (Session::get('user_role') === ROLE_CHEF): ?>
                                    <li><a class="dropdown-item" href="<?= url('/chef/dashboard') ?>"><i class="bi bi-speedometer2 me-2"></i> Chef Dashboard</a></li>
                                    <li><a class="dropdown-item" href="<?= url('/chef/menu') ?>"><i class="bi bi-egg-fried me-2"></i> Manage Menu</a></li>
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

                    <!-- Notification Bell (logged-in customers & chefs) -->
                    <?php if (Session::has('user_id')): ?>
                    <?php
                        require_once APP_PATH . '/models/Notification.php';
                        $_notifModel   = new Notification();
                        $_unreadCount  = $_notifModel->countUnread((int) Session::get('user_id'));
                        $_latestNotifs = $_notifModel->getLatest((int) Session::get('user_id'), 5);
                    ?>
                    <div class="dropdown" id="notificationDropdown">
                        <button class="btn btn-link text-white position-relative p-0 border-0"
                                type="button"
                                id="notifBellBtn"
                                data-bs-toggle="dropdown"
                                data-bs-auto-close="outside"
                                aria-expanded="false"
                                title="Notifications">
                            <i class="bi bi-bell-fill fs-5"></i>
                            <?php if ($_unreadCount > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notifBadge" style="font-size:0.6rem;">
                                <?= $_unreadCount > 99 ? '99+' : $_unreadCount ?>
                            </span>
                            <?php else: ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none" id="notifBadge"></span>
                            <?php endif; ?>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end p-0 shadow-lg" style="width:340px;max-height:450px;overflow-y:auto;" aria-labelledby="notifBellBtn">
                            <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom bg-dark text-white">
                                <span class="fw-bold"><i class="bi bi-bell me-1"></i> Notifications</span>
                                <a href="<?= url('/notifications') ?>" class="small text-warning text-decoration-none">View All</a>
                            </div>
                            <?php if (empty($_latestNotifs)): ?>
                                <div class="text-center text-muted py-4 px-3">
                                    <i class="bi bi-bell-slash fs-2 d-block mb-2"></i>
                                    No notifications yet.
                                </div>
                            <?php else: ?>
                                <?php foreach ($_latestNotifs as $notif): ?>
                                <a href="<?= $notif['link'] ? url($notif['link']) : url('/notifications') ?>"
                                   class="d-flex align-items-start gap-2 px-3 py-2 text-decoration-none border-bottom notif-item <?= $notif['is_read'] ? 'bg-white' : 'bg-light' ?>"
                                   style="color:inherit;">
                                    <?php
                                    $iconMap = [
                                        'order_update'   => 'bi-bag-check-fill text-success',
                                        'promotion'      => 'bi-megaphone-fill text-warning',
                                        'system_alert'   => 'bi-exclamation-triangle-fill text-danger',
                                        'kitchen_update' => 'bi-shop-window text-primary',
                                    ];
                                    $icon = $iconMap[$notif['type']] ?? 'bi-bell-fill text-secondary';
                                    ?>
                                    <i class="bi <?= $icon ?> mt-1 flex-shrink-0"></i>
                                    <div class="flex-grow-1 overflow-hidden">
                                        <div class="fw-semibold small text-truncate <?= $notif['is_read'] ? 'text-muted' : 'text-dark' ?>">
                                            <?= sanitize($notif['title']) ?>
                                        </div>
                                        <div class="small text-muted" style="font-size:0.78rem;line-height:1.3;">
                                            <?= sanitize(mb_substr($notif['message'], 0, 80)) ?><?= mb_strlen($notif['message']) > 80 ? '…' : '' ?>
                                        </div>
                                        <div class="text-muted" style="font-size:0.7rem;"><?= date('d M, h:i A', strtotime($notif['created_at'])) ?></div>
                                    </div>
                                    <?php if (!$notif['is_read']): ?>
                                    <span class="flex-shrink-0 mt-1" style="width:8px;height:8px;background:#e85d04;border-radius:50%;display:inline-block;"></span>
                                    <?php endif; ?>
                                </a>
                                <?php endforeach; ?>
                                <div class="text-center py-2">
                                    <form action="<?= url('/notifications/mark-all') ?>" method="POST">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-link btn-sm text-secondary text-decoration-none small">
                                            <i class="bi bi-check2-all me-1"></i>Mark all as read
                                        </button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
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
