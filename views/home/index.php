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

<!-- Phase 8: Zero Food Waste Teaser Section -->
<?php
// Load zero waste model to fetch homepage teaser deals
require_once APP_PATH . '/models/ZeroWasteItem.php';
$_zwModel       = new ZeroWasteItem();
$_teaserDeals   = $_zwModel->findHomepageTeaser(3);
$_activeCount   = $_zwModel->countActive();
?>
<?php if (!empty($_teaserDeals)): ?>
<section class="py-5" style="background: linear-gradient(135deg, #0a3d1f 0%, #145a32 100%);">
    <div class="container">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
            <div>
                <span class="badge bg-success bg-opacity-75 text-white px-3 py-1 rounded-pill fw-bold mb-2 d-inline-block">
                    <i class="bi bi-recycle me-1"></i>Zero Food Waste Initiative
                </span>
                <h2 class="fw-800 text-white mb-1">
                    🏷️ End-of-Day Discounted Deals
                </h2>
                <p class="text-white-50 mb-0">
                    Freshly cooked meals at massive discounts — expiring today!
                    <strong class="text-warning"><?= $_activeCount ?> deal<?= $_activeCount !== 1 ? 's' : '' ?> live right now</strong>
                </p>
            </div>
            <a href="<?= url('/zero-waste') ?>" class="btn btn-warning fw-bold mt-3 mt-md-0">
                <i class="bi bi-tag-fill me-1"></i>View All Deals
            </a>
        </div>

        <div class="row g-4">
            <?php foreach ($_teaserDeals as $_deal): ?>
                <?php
                    $_min = (int) $_deal['minutes_remaining'];
                    $_urgencyClass = $_min <= 30 ? 'danger' : ($_min <= 90 ? 'warning' : 'success');
                    $_timeText = $_min < 60 ? $_min . 'm left' : floor($_min/60) . 'h ' . ($_min%60) . 'm left';
                ?>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100 overflow-hidden position-relative" style="border-radius:16px;">
                        <!-- Discount Badge -->
                        <div class="position-absolute top-0 start-0 m-2 z-1">
                            <span class="badge bg-success fw-800 fs-6 px-3 py-2 rounded-pill shadow">
                                <?= (int) $_deal['discount_pct'] ?>% OFF
                            </span>
                        </div>
                        <!-- Urgency -->
                        <div class="position-absolute top-0 end-0 m-2 z-1">
                            <span class="badge bg-<?= $_urgencyClass ?> px-2 py-1 rounded-pill small">
                                <i class="bi bi-alarm me-1"></i><?= $_timeText ?>
                            </span>
                        </div>

                        <?php if (!empty($_deal['dish_image'])): ?>
                            <img src="<?= url('/uploads/dishes/' . $_deal['dish_image']) ?>"
                                 alt="<?= sanitize($_deal['item_name']) ?>"
                                 class="card-img-top object-fit-cover" style="height: 160px;">
                        <?php else: ?>
                            <div class="d-flex align-items-center justify-content-center bg-light" style="height:160px;font-size:3rem;">🍱</div>
                        <?php endif; ?>

                        <div class="card-body p-3">
                            <h6 class="fw-bold mb-1"><?= sanitize($_deal['item_name']) ?></h6>
                            <p class="text-muted small mb-2">
                                <i class="bi bi-shop me-1"></i><?= sanitize($_deal['kitchen_name']) ?>
                                · <i class="bi bi-geo-alt me-1"></i><?= sanitize($_deal['city']) ?>
                            </p>
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="text-muted text-decoration-line-through small">₹<?= number_format((float)$_deal['original_price'], 2) ?></span>
                                    <div class="fw-800 text-success fs-5">₹<?= number_format((float)$_deal['discounted_price'], 2) ?></div>
                                </div>
                                <a href="<?= url('/zero-waste') ?>" class="btn btn-sm btn-success fw-semibold">
                                    <i class="bi bi-cart-plus me-1"></i>Grab Deal
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ═══════════════════════════════════════════════════════════
     Phase 10: AI Recommendation Teaser (Homepage)
     Personalised for logged-in customers; popular picks for guests
     ════════════════════════════════════════════════════════════ -->
<?php
require_once APP_PATH . '/models/Recommendation.php';
$_recModel  = new Recommendation();
$_custId    = (int) Session::get('user_id', 0);
$_isCustomer = $_custId > 0 && Session::get('user_role') === ROLE_CUSTOMER;

if ($_isCustomer) {
    $_recDishes  = $_recModel->getForCustomer($_custId, 4);
    $_recTitle   = '🎯 Recommended For You';
    $_recSubtitle = 'Personalised dishes matched to your taste';
    $_recBadge   = '<i class="bi bi-stars me-1 text-warning"></i> AI-Personalised';
    $_recHref    = url('/recommendations');
} else {
    $_recDishes  = $_recModel->getPopularDishes(4);
    $_recTitle   = '🔥 Popular Picks';
    $_recSubtitle = 'Most-loved dishes by our community';
    $_recBadge   = '<i class="bi bi-fire me-1 text-warning"></i> Platform Favourites';
    $_recHref    = url('/menu');
}
?>
<?php if (!empty($_recDishes)): ?>
<section class="py-5" style="background: linear-gradient(135deg, #f3e8ff 0%, #fdf4ff 100%);">
    <div class="container">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
            <div>
                <span class="badge px-3 py-1 rounded-pill fw-bold mb-2 d-inline-block"
                      style="background:rgba(106,13,173,0.12); color:#6a0dad; border:1px solid rgba(106,13,173,0.2);">
                    <?= $_recBadge ?>
                </span>
                <h2 class="fw-800 mb-1" style="color:#2d0050;"><?= $_recTitle ?></h2>
                <p class="text-muted mb-0"><?= sanitize($_recSubtitle) ?></p>
            </div>
            <a href="<?= $_recHref ?>" class="btn fw-bold mt-3 mt-md-0"
               style="background:linear-gradient(135deg,#6a0dad,#9b30ff); color:#fff; border:none;">
                <i class="bi bi-arrow-right me-1"></i>
                <?= $_isCustomer ? 'View All Picks' : 'Browse All Dishes' ?>
            </a>
        </div>

        <div class="row g-4">
            <?php foreach ($_recDishes as $_rd): ?>
                <div class="col-lg-3 col-md-6">
                    <div class="card dish-card h-100 position-relative"
                         style="border:1px solid rgba(106,13,173,0.12); box-shadow: 0 4px 20px rgba(106,13,173,0.08);">

                        <?php if ($_isCustomer && isset($_rd['score']) && $_rd['score'] > 0): ?>
                            <div class="position-absolute top-0 end-0 m-2 z-1">
                                <span class="badge rounded-pill px-2 py-1 small fw-semibold"
                                      style="background: linear-gradient(135deg,#6a0dad,#9b30ff); color:#fff; font-size:0.65rem;">
                                    <i class="bi bi-stars me-1"></i>Match
                                </span>
                            </div>
                        <?php endif; ?>

                        <a href="<?= url('/dish/' . $_rd['id']) ?>" class="text-decoration-none">
                            <div class="dish-card-image">
                                <?php if (!empty($_rd['image'])): ?>
                                    <img src="<?= UPLOAD_URL . '/dishes/' . $_rd['image'] ?>"
                                         alt="<?= sanitize($_rd['item_name']) ?>">
                                <?php else: ?>
                                    <div class="dish-image-placeholder"><i class="bi bi-egg-fried"></i></div>
                                <?php endif; ?>
                                <span class="dish-price-badge"><?= format_currency($_rd['price']) ?></span>
                            </div>
                        </a>

                        <div class="card-body p-3">
                            <a href="<?= url('/dish/' . $_rd['id']) ?>" class="text-decoration-none">
                                <h6 class="fw-bold mb-1 text-dark"><?= sanitize($_rd['item_name']) ?></h6>
                            </a>
                            <p class="text-muted small mb-2">
                                <i class="bi bi-shop me-1"></i><?= sanitize($_rd['kitchen_name']) ?>
                                <span class="mx-1">·</span>
                                <i class="bi bi-geo-alt me-1"></i><?= sanitize($_rd['city']) ?>
                            </p>
                            <div class="d-flex flex-wrap gap-1 mb-3">
                                <?php if ($_rd['is_veg']): ?>
                                    <span class="dietary-badge dietary-veg"><i class="bi bi-circle-fill me-1"></i>Veg</span>
                                <?php else: ?>
                                    <span class="dietary-badge dietary-nonveg"><i class="bi bi-circle-fill me-1"></i>Non-Veg</span>
                                <?php endif; ?>
                                <?php if ($_rd['is_jain_available']): ?>
                                    <span class="dietary-badge dietary-jain"><i class="bi bi-flower1 me-1"></i>Jain</span>
                                <?php endif; ?>
                            </div>
                            <a href="<?= url('/dish/' . $_rd['id']) ?>" class="btn btn-sm w-100 fw-semibold"
                               style="background:linear-gradient(135deg,#6a0dad,#9b30ff); color:#fff; border:none;">
                                <i class="bi bi-cart-plus me-1"></i> View &amp; Order
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>
