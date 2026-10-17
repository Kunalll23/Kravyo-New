<?php
/**
 * ── Active-user resolution ───────────────────────────────────────────────────
 * The primary session is determined by the URL (detectContext()):
 *   /admin/* → KRAVYO_ADMIN   /chef/* → KRAVYO_CHEF   else → KRAVYO_CUSTOMER
 *
 * Session::getCrossSessionUser() returns Chef identity when:
 *   - Primary context is KRAVYO_CUSTOMER (non-/chef page), AND
 *   - No Customer is logged in, AND
 *   - A KRAVYO_CHEF cookie exists in the browser.
 * This lets a Chef browsing "/" or "/login" appear correctly in the header
 * without opening two sessions simultaneously on every request.
 */
$_sessionCtx   = Session::currentContext();
$_isPrimary    = Session::has('user_id');
$_hasUser      = $_isPrimary;

// Display data
$_userName     = Session::get('user_name', 'Account');
$_userRole     = Session::get('user_role', '');
$_activeUserId = (int) Session::get('user_id', 0);

// Logout action & CSRF: strictly mapped to current role / session context
if ($_sessionCtx === Session::CHEF_SESSION || $_userRole === ROLE_CHEF) {
    $_logoutAction = url('/chef/logout');
    $_logoutCsrf   = csrf_token();
} elseif ($_sessionCtx === Session::DELIVERY_SESSION || $_userRole === ROLE_DELIVERY) {
    $_logoutAction = url('/delivery/logout');
    $_logoutCsrf   = csrf_token();
} elseif ($_sessionCtx === Session::ADMIN_SESSION) {
    $_logoutAction = url('/admin/logout');
    $_logoutCsrf   = csrf_token();
} else {
    $_logoutAction = url('/logout');
    $_logoutCsrf   = csrf_token();
}

// ── Context-aware navigation URLs ────────────────────────────────────────────
// When a Chef or Admin clicks "Browse Dishes" / "Explore Kitchens", the URL
// must stay within the /chef/* or /admin/* prefix so the session cookie
// (KRAVYO_CHEF / KRAVYO_ADMIN) remains active.  Without this, navigating to
// /kitchens or /menu would switch to KRAVYO_CUSTOMER and lose the Chef/Admin
// session, showing the Customer login instead.
$_sessionCtx = Session::currentContext();

switch ($_sessionCtx) {
    case Session::CHEF_SESSION:
        $_navHome          = url('/chef/dashboard');
        $_navKitchens      = url('/chef/kitchens');
        $_navNearby        = url('/chef/nearby-kitchens');
        $_navMenu          = url('/chef/menu-browse');
        $_navSubscriptions = url('/chef/subscriptions-browse');
        $_navZeroWaste     = url('/chef/zero-waste-deals');
        $_navDishPrefix    = '/chef/dish/';
        $_navHomeLabel     = 'Dashboard';
        $_navHomeIcon      = 'bi-speedometer2';
        break;
    case Session::ADMIN_SESSION:
        $_navHome          = url('/admin/dashboard');
        $_navKitchens      = url('/admin/kitchens-browse');
        $_navNearby        = url('/admin/nearby-kitchens');
        $_navMenu          = url('/admin/menu-browse');
        $_navSubscriptions = url('/admin/subscriptions-browse');
        $_navZeroWaste     = url('/admin/zero-waste-browse');
        $_navDishPrefix    = '/admin/dish/';
        $_navHomeLabel     = 'Dashboard';
        $_navHomeIcon      = 'bi-speedometer2';
        break;
    case Session::DELIVERY_SESSION:
        $_navHome          = url('/delivery/dashboard');
        $_navKitchens      = url('/delivery/kitchens');
        $_navNearby        = url('/delivery/nearby-kitchens');
        $_navMenu          = url('/delivery/menu-browse');
        $_navSubscriptions = url('/delivery/subscriptions-browse');
        $_navZeroWaste     = url('/delivery/zero-waste-browse');
        $_navDishPrefix    = '/delivery/dish/';
        $_navHomeLabel     = 'Delivery Dashboard';
        $_navHomeIcon      = 'bi-bicycle';
        break;
    case Session::PUBLIC_SESSION:
        $_navHome          = url('/');
        $_navKitchens      = url('/kitchens');
        $_navNearby        = url('/nearby-kitchens');
        $_navMenu          = url('/menu');
        $_navSubscriptions = url('/subscriptions');
        $_navZeroWaste     = url('/zero-waste');
        $_navDishPrefix    = '/dish/';
        $_navHomeLabel     = 'Home';
        $_navHomeIcon      = 'bi-house-door';
        break;
    default: // CUSTOMER_SESSION
        $_navHome          = url('/home');
        $_navKitchens      = url('/kitchens');
        $_navNearby        = url('/nearby-kitchens');
        $_navMenu          = url('/menu');
        $_navSubscriptions = url('/subscriptions');
        $_navZeroWaste     = url('/zero-waste');
        $_navDishPrefix    = '/dish/';
        $_navHomeLabel     = 'Home';
        $_navHomeIcon      = 'bi-house-door';
        break;
}
?>
<header>
    <nav class="navbar navbar-expand-lg navbar-kravyo py-2">
        <div class="container-fluid px-3 px-xl-4">
            <a class="navbar-brand d-flex align-items-center me-2 me-xl-3 py-0" href="<?= $_navHome ?>">
                <img src="<?= url('/assets/images/kravyo-logo.png') ?>" alt="Kravyo" height="32" style="object-fit: contain; max-height: 32px; transform: scale(1.35); transform-origin: left center; margin-right: 20px;">
            </a>
            
            <button class="navbar-toggler text-white border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarKravyoContent">
                <i class="bi bi-list fs-2 text-white"></i>
            </button>

            <div class="collapse navbar-collapse" id="navbarKravyoContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 align-items-lg-center">
                    <li class="nav-item">
                        <a class="nav-link active" href="<?= $_navHome ?>"><i class="bi <?= $_navHomeIcon ?> me-1"></i> <?= $_navHomeLabel ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $_navKitchens ?>"><i class="bi bi-shop me-1"></i> Explore Kitchens</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $_navNearby ?>"><i class="bi bi-geo-alt-fill text-danger me-1"></i> Nearby Map</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $_navMenu ?>"><i class="bi bi-journal-text me-1"></i> Browse Dishes</a>
                    </li>
                    <?php if ($_sessionCtx !== Session::DELIVERY_SESSION && $_userRole !== ROLE_DELIVERY): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $_navSubscriptions ?>"><i class="bi bi-calendar2-week me-1"></i> Tiffin Plans</a>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link text-warning fw-semibold" href="<?= $_navZeroWaste ?>">
                            <i class="bi bi-tag-fill me-1"></i> Zero Waste Deals
                        </a>
                    </li>
                    <?php if ($_userRole === ROLE_CUSTOMER): ?>
                    <li class="nav-item">
                        <a class="nav-link fw-semibold" href="<?= url('/recommendations') ?>"
                           style="background: linear-gradient(135deg,#6a0dad,#9b30ff); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
                            <i class="bi bi-stars me-1" style="-webkit-text-fill-color: #9b30ff;"></i> For You
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>

                <div class="d-flex align-items-center gap-2 gap-xl-3 flex-shrink-0 ms-lg-2">
                    <?php if (empty($_userRole) || $_userRole === ROLE_CUSTOMER || $_sessionCtx === Session::PUBLIC_SESSION): ?>
                        <div class="dropdown me-2">
                            <button class="btn btn-light rounded-pill border-0 d-flex align-items-center gap-2 px-3 py-2 shadow-sm" type="button" data-bs-toggle="dropdown" style="background-color: #f8f9fa; color: #333; font-weight: 500;">
                                <i class="bi bi-geo-alt-fill text-dark"></i>
                                <span><?= sanitize(Session::get('selected_city') ?: 'Surat') ?></span>
                                <i class="bi bi-chevron-down ms-1" style="font-size: 0.8em; color: #888;"></i>
                            </button>
                            <ul class="dropdown-menu shadow border-0" style="border-radius: 12px; max-height: 300px; overflow-y: auto;">
                                <?php
                                require_once APP_PATH . '/models/Kitchen.php';
                                $cityList = (new Kitchen())->getDistinctCities();
                                foreach ($cityList as $cityOption):
                                ?>
                                <li><a class="dropdown-item py-2 <?= (Session::get('selected_city') === $cityOption) ? 'bg-light text-danger fw-bold' : '' ?>" href="javascript:void(0)" onclick="setGlobalCity('<?= htmlspecialchars($cityOption, ENT_QUOTES) ?>')"><i class="bi bi-geo-alt text-danger me-2"></i> <?= sanitize($cityOption) ?></a></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        
                        
                        <script>
                        function setGlobalCity(city) {
                            fetch('<?= url('/api/set-city') ?>', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                                body: 'city=' + encodeURIComponent(city) + '&_csrf=<?= csrf_token() ?>'
                            })
                            .then(res => res.json())
                            .then(data => {
                                if (data && data.success) {
                                    const url = new URL(window.location.href);
                                    if (url.searchParams.has('city')) {
                                        url.searchParams.set('city', city);
                                        window.location.href = url.toString();
                                    } else {
                                        window.location.reload();
                                    }
                                } else {
                                    window.location.reload();
                                }
                            })
                            .catch(() => window.location.reload());
                        }
                        </script>
                    <?php endif; ?>
                    <?php if (Session::has('admin_id')): ?>
                        <!-- Logged-in Admin Dropdown -->
                        <div class="dropdown">
                            <button class="btn btn-warning dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-shield-lock"></i>
                                <span><?= sanitize(Session::get('admin_name', 'Admin')) ?></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="<?= url('/admin/dashboard') ?>"><i class="bi bi-speedometer2 me-2"></i> Admin Dashboard</a></li>
                                <li><a class="dropdown-item" href="<?= url('/admin/dispatch-monitor') ?>"><i class="bi bi-broadcast text-danger me-2"></i> Live Dispatch Board</a></li>
                                <li><a class="dropdown-item" href="<?= url('/admin/orders') ?>"><i class="bi bi-bag-check text-success me-2"></i> Platform Orders</a></li>
                                <li><a class="dropdown-item" href="<?= url('/admin/chefs') ?>"><i class="bi bi-shop text-warning me-2"></i> Manage Chefs</a></li>
                                <li><a class="dropdown-item" href="<?= url('/admin/users') ?>"><i class="bi bi-people text-info me-2"></i> Manage Users</a></li>
                                <li><a class="dropdown-item" href="<?= url('/admin/delivery-partners') ?>"><i class="bi bi-bicycle text-primary me-2"></i> Manage Delivery Partners</a></li>
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
                    <?php elseif ($_hasUser): ?>
                        <!-- Logged-in User Dropdown (Customer or Chef) -->
                        <div class="dropdown">
                            <button class="btn btn-outline-light dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle"></i>
                                <span><?= sanitize($_userName) ?></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <?php if ($_userRole === ROLE_CUSTOMER): ?>
                                    <li><a class="dropdown-item" href="<?= url('/profile') ?>"><i class="bi bi-person me-2"></i> My Profile</a></li>
                                    <li><a class="dropdown-item" href="<?= url('/orders/history') ?>"><i class="bi bi-bag-check me-2"></i> My Orders</a></li>
                                <?php elseif ($_userRole === ROLE_CHEF || $_sessionCtx === Session::CHEF_SESSION): ?>
                                    <li><a class="dropdown-item" href="<?= url('/chef/dashboard') ?>"><i class="bi bi-speedometer2 me-2"></i> Chef Dashboard</a></li>
                                    <li><a class="dropdown-item" href="<?= url('/chef/menu') ?>"><i class="bi bi-egg-fried me-2"></i> Manage Menu</a></li>
                                    <li><a class="dropdown-item" href="<?= url('/chef/orders') ?>"><i class="bi bi-bag-check me-2"></i> View Orders</a></li>
                                    <li><a class="dropdown-item" href="<?= url('/chef/reviews') ?>"><i class="bi bi-star-fill text-warning me-2"></i> Customer Reviews</a></li>
                                    <li><a class="dropdown-item" href="<?= url('/chef/zero-waste') ?>"><i class="bi bi-recycle me-2"></i> Zero Waste</a></li>
                                    <li><a class="dropdown-item" href="<?= url('/chef/subscriptions') ?>"><i class="bi bi-calendar-week me-2"></i> Tiffins</a></li>
                                <?php elseif ($_userRole === ROLE_DELIVERY): ?>
                                    <li><a class="dropdown-item" href="<?= url('/delivery/dashboard') ?>"><i class="bi bi-bicycle me-2"></i> Delivery Dashboard</a></li>
                                    <li><a class="dropdown-item" href="<?= url('/delivery/history') ?>"><i class="bi bi-clock-history me-2"></i> Delivery History</a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="<?= $_logoutAction ?>" method="POST" class="d-inline">
                                        <input type="hidden" name="_csrf" value="<?= $_logoutCsrf ?>">
                                        <button type="submit" class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i> Logout</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <!-- Guest Navigation Links -->
                        <?php if ($_sessionCtx === Session::CHEF_SESSION): ?>
                            <a href="<?= url('/chef/login') ?>" class="btn btn-warning text-dark btn-sm px-3 fw-bold">Chef Login</a>
                            <a href="<?= url('/register') ?>" class="btn btn-outline-light btn-sm px-3">Join as Chef</a>
                        <?php elseif ($_sessionCtx === Session::DELIVERY_SESSION): ?>
                            <a href="<?= url('/delivery/login') ?>" class="btn btn-warning text-dark btn-sm px-3 fw-bold">Delivery Login</a>
                            <a href="<?= url('/delivery/register') ?>" class="btn btn-outline-light btn-sm px-3">Join as Rider</a>
                        <?php elseif ($_sessionCtx === Session::ADMIN_SESSION): ?>
                            <a href="<?= url('/admin/login') ?>" class="btn btn-warning text-dark btn-sm px-3 fw-bold">Admin Login</a>
                        <?php else: ?>
                            <a href="<?= url('/login') ?>" class="btn btn-light border text-dark btn-sm px-3">Login</a>
                            <a href="<?= url('/register') ?>" class="btn btn-kravyo-primary btn-sm px-3">Register / Join Chef</a>
                        <?php endif; ?>
                    <?php endif; ?>

                    <!-- Notification Bell (all portals: customer, chef, delivery, admin) -->
                    <?php
                        require_once APP_PATH . '/models/Notification.php';
                        $_notifUserId   = $_activeUserId;
                        $_notifUserType = $_userRole;
                        if ($_sessionCtx === Session::ADMIN_SESSION && Session::has('admin_id')) {
                            $_notifUserId   = (int) Session::get('admin_id');
                            $_notifUserType = 'admin';
                        }
                    ?>
                    <?php if ($_notifUserId > 0 && in_array($_notifUserType, Notification::USER_TYPES, true)): ?>
                    <?php
                        require_once APP_PATH . '/models/Notification.php';
                        $notifModel_   = new Notification();
                        $_unreadCount  = $notifModel_->countUnread($_notifUserId, $_notifUserType);
                        $_latestNotifs = $notifModel_->getLatest($_notifUserId, $_notifUserType, 5);
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
                                <a href="<?= ctx_url('/notifications') ?>" class="small text-warning text-decoration-none">View All</a>
                            </div>
                            <div id="notifDropdownList">
                            <?php if (empty($_latestNotifs)): ?>
                                <div class="text-center text-muted py-4 px-3">
                                    <i class="bi bi-bell-slash fs-2 d-block mb-2"></i>
                                    No notifications yet.
                                </div>
                            <?php else: ?>
                                <?php foreach ($_latestNotifs as $notif): 
                                    $link = $notif['link'] ? url($notif['link']) : url('/notifications');
                                    $targetOrderNo = '';
                                    $targetCity = '';

                                    // Extract order & city if dispatch-monitor alert
                                    if (!empty($notif['link']) && strpos($notif['link'], '/admin/dispatch-monitor') === 0) {
                                        if (preg_match('/[?&]order=([^&]+)/i', $notif['link'], $mOParam)) {
                                            $targetOrderNo = urldecode($mOParam[1]);
                                        } elseif (preg_match('/Order #(KRV-[A-Z0-9-]+)/i', $notif['message'] ?? '', $mO)) {
                                            $targetOrderNo = $mO[1];
                                        }
                                        if (preg_match('/[?&]city=([^&]+)/i', $notif['link'], $mCParam)) {
                                            $targetCity = urldecode($mCParam[1]);
                                        } elseif (preg_match('/in ([A-Za-z\s]+) \(/i', $notif['message'] ?? '', $mC)) {
                                            $targetCity = trim($mC[1]);
                                        }
                                        if (strpos($link, 'order=') === false && $targetOrderNo) {
                                            $link .= (strpos($link, '?') === false ? '?' : '&') . 'order=' . urlencode($targetOrderNo) . ($targetCity ? '&city=' . urlencode($targetCity) : '');
                                        }
                                    }
                                ?>
                                <a href="<?= $link ?>"
                                   class="d-flex align-items-start gap-2 px-3 py-2 text-decoration-none border-bottom notif-item-clickable <?= $notif['is_read'] ? 'bg-white' : 'bg-light' ?>"
                                   data-notif-href="<?= htmlspecialchars($link, ENT_QUOTES) ?>"
                                   data-dispatch-order="<?= htmlspecialchars($targetOrderNo, ENT_QUOTES) ?>"
                                   data-dispatch-city="<?= htmlspecialchars($targetCity, ENT_QUOTES) ?>"
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
                                            <?php 
                                                $_msg = $notif['message'] ?? '';
                                                $_cut = function_exists('mb_substr') ? mb_substr($_msg, 0, 80) : substr($_msg, 0, 80);
                                                $_len = function_exists('mb_strlen') ? mb_strlen($_msg) : strlen($_msg);
                                            ?>
                                            <?= sanitize($_cut) ?><?= $_len > 80 ? '…' : '' ?>
                                        </div>
                                        <div class="text-muted" style="font-size:0.7rem;"><?= date('d M, h:i A', strtotime($notif['created_at'])) ?></div>
                                    </div>
                                    <?php if (!$notif['is_read']): ?>
                                    <span class="flex-shrink-0 mt-1" style="width:8px;height:8px;background:#e85d04;border-radius:50%;display:inline-block;"></span>
                                    <?php endif; ?>
                                </a>
                                <?php endforeach; ?>
                                <?php endif; ?></div>
                            <script>
                            (function() {
                                var notifList = document.getElementById('notifDropdownList');
                                if (!notifList) return;

                                notifList.addEventListener('click', function(e) {
                                    var item = e.target.closest('a');
                                    if (!item) return;

                                    var orderNo = item.dataset.dispatchOrder;
                                    var city    = item.dataset.dispatchCity;

                                    // Special handler for admin dispatch board
                                    if (window.handleNotificationJump && orderNo) {
                                        e.preventDefault();
                                        e.stopPropagation();
                                        window.handleNotificationJump(orderNo, city);
                                        return;
                                    }

                                    var targetHref = item.getAttribute('href') || item.dataset.notifHref;
                                    if (targetHref && targetHref !== '#' && targetHref.indexOf('javascript:') !== 0) {
                                        e.preventDefault();
                                        e.stopPropagation();

                                        try {
                                            var currentUrl = new URL(window.location.href);
                                            var destUrl    = new URL(targetHref, window.location.origin);
                                            // If already on the exact page, reload to refresh order/tracking state
                                            if (currentUrl.pathname === destUrl.pathname && currentUrl.search === destUrl.search) {
                                                window.location.reload();
                                                return;
                                            }
                                        } catch (err) {}

                                        window.location.href = targetHref;
                                    }
                                });
                            })();
                            </script>
                            <div class="text-center py-2">
                                    <form action="<?= ctx_url('/notifications/mark-all') ?>" method="POST">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-link btn-sm text-secondary text-decoration-none small">
                                            <i class="bi bi-check2-all me-1"></i>Mark all as read
                                        </button>
                                    </form>
                                </div>

                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ($_sessionCtx === Session::CUSTOMER_SESSION): ?>
                    <!-- Shopping Cart Icon (Customer Only) -->
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
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
    <script>
        window.KRAVYO_APP_URL = <?= json_encode(APP_URL) ?>;
        window.KRAVYO_CTX_PREFIX = <?= json_encode(
            $_sessionCtx === Session::CHEF_SESSION ? '/chef'
            : ($_sessionCtx === Session::ADMIN_SESSION ? '/admin'
            : ($_sessionCtx === Session::DELIVERY_SESSION ? '/delivery' : ''))
        ) ?>;
    </script>
</header>
