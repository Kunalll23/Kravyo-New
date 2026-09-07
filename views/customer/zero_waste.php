<?php /* Phase 8: Customer Zero Food Waste Deals Showcase */ ?>

<!-- Hero Banner -->
<section class="kitchen-browse-hero">
    <div class="container text-center">
        <span class="badge bg-success bg-opacity-75 text-white px-3 py-2 rounded-pill fw-bold mb-3 d-inline-flex align-items-center gap-2">
            <i class="bi bi-recycle"></i> Zero Food Waste Initiative
        </span>
        <h1 class="fw-800 text-white mb-2">
            <i class="bi bi-tag-fill me-2 text-warning"></i>End-of-Day Discounted Deals
        </h1>
        <p class="text-white-50 mb-0 fs-5">
            Freshly cooked meals at massive discounts — save money, reduce food waste
        </p>
        <?php if ($totalActive > 0): ?>
            <div class="mt-3">
                <span class="badge bg-warning text-dark fs-6 px-4 py-2 rounded-pill fw-bold">
                    <i class="bi bi-broadcast-pin me-1"></i><?= $totalActive ?> Deal<?= $totalActive !== 1 ? 's' : '' ?> Live Right Now
                </span>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Filter Bar -->
<section class="py-4 border-bottom bg-white shadow-sm sticky-top" style="top: 70px; z-index: 100;">
    <div class="container">
        <form method="GET" action="<?= url('/zero-waste') ?>" class="row g-2 align-items-center">
            <!-- Veg Filter -->
            <div class="col-auto">
                <div class="form-check form-switch mb-0 d-flex align-items-center gap-2">
                    <input class="form-check-input" type="checkbox" id="veg_filter" name="veg"
                           <?= !empty($filters['is_veg']) ? 'checked' : '' ?>>
                    <label class="form-check-label fw-semibold small" for="veg_filter">
                        🌿 Veg Only
                    </label>
                </div>
            </div>

            <!-- City Filter -->
            <div class="col-md-3">
                <select class="form-select form-select-sm" name="city" id="city_filter">
                    <option value="">All Cities</option>
                    <?php foreach ($cities as $city): ?>
                        <option value="<?= sanitize($city) ?>"
                                <?= ($filters['city'] === $city) ? 'selected' : '' ?>>
                            <?= sanitize($city) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Sort -->
            <div class="col-md-3">
                <select class="form-select form-select-sm" name="sort" id="sort_filter">
                    <option value="expiry"   <?= ($filters['sort'] === 'expiry')    ? 'selected' : '' ?>>⏱ Expiring Soonest</option>
                    <option value="discount" <?= ($filters['sort'] === 'discount')  ? 'selected' : '' ?>>🔥 Biggest Discount</option>
                    <option value="price_low"<?= ($filters['sort'] === 'price_low') ? 'selected' : '' ?>>💰 Lowest Price</option>
                </select>
            </div>

            <div class="col-auto">
                <button type="submit" class="btn btn-kravyo-primary btn-sm">
                    <i class="bi bi-funnel me-1"></i>Apply
                </button>
            </div>
            <?php if (!empty($filters['city']) || !empty($filters['is_veg'])): ?>
                <div class="col-auto">
                    <a href="<?= url('/zero-waste') ?>" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-x-circle me-1"></i>Clear
                    </a>
                </div>
            <?php endif; ?>
        </form>
    </div>
</section>

<!-- Deals Grid -->
<section class="py-5">
    <div class="container">

        <?php if (empty($deals)): ?>
            <!-- Empty State -->
            <div class="text-center py-5">
                <div class="mb-3" style="font-size:4rem;">♻️</div>
                <h3 class="fw-bold">No active deals right now</h3>
                <p class="text-muted mb-4">
                    Home chefs post end-of-day deals in the evenings. Check back later or browse the full menu!
                </p>
                <a href="<?= url('/menu') ?>" class="btn btn-kravyo-primary me-2">
                    <i class="bi bi-egg-fried me-2"></i>Browse Full Menu
                </a>
                <a href="<?= url('/kitchens') ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-shop me-2"></i>Explore Kitchens
                </a>
            </div>

        <?php else: ?>
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold mb-0">
                    Showing <span class="text-success"><?= count($deals) ?></span> deal<?= count($deals) !== 1 ? 's' : '' ?>
                    <?= !empty($filters['city']) ? 'in ' . sanitize($filters['city']) : '' ?>
                </h5>
                <small class="text-muted"><i class="bi bi-arrow-clockwise me-1"></i>Deals auto-expire — act fast!</small>
            </div>

            <div class="row g-4">
                <?php foreach ($deals as $deal): ?>
                    <?php
                        $minRemaining = (int) $deal['minutes_remaining'];
                        $urgencyClass = $minRemaining <= 30 ? 'danger' : ($minRemaining <= 90 ? 'warning' : 'success');
                        $urgencyText  = $minRemaining <= 0 ? 'Expiring soon'
                                      : ($minRemaining < 60 ? $minRemaining . 'm left'
                                      : floor($minRemaining/60) . 'h ' . ($minRemaining%60) . 'm left');
                    ?>
                    <div class="col-sm-6 col-lg-4">
                        <div class="card kravyo-card h-100 border-0 position-relative overflow-hidden zero-waste-card">

                            <!-- Discount Badge -->
                            <div class="position-absolute top-0 start-0 m-2 z-1">
                                <span class="badge bg-success fs-6 fw-800 px-3 py-2 rounded-pill shadow">
                                    <?= (int) $deal['discount_pct'] ?>% OFF
                                </span>
                            </div>

                            <!-- Veg/Non-veg badge -->
                            <div class="position-absolute top-0 end-0 m-2 z-1">
                                <span class="badge <?= $deal['is_veg'] ? 'bg-success' : 'bg-danger' ?> rounded-circle p-2"
                                      title="<?= $deal['is_veg'] ? 'Vegetarian' : 'Non-Vegetarian' ?>">
                                    <?= $deal['is_veg'] ? '🌿' : '🍗' ?>
                                </span>
                            </div>

                            <!-- Dish Image -->
                            <?php if (!empty($deal['dish_image'])): ?>
                                <img src="<?= url('/uploads/dishes/' . $deal['dish_image']) ?>"
                                     alt="<?= sanitize($deal['item_name']) ?>"
                                     class="card-img-top object-fit-cover" style="height: 180px;">
                            <?php else: ?>
                                <div class="card-img-top d-flex align-items-center justify-content-center bg-light"
                                     style="height: 180px; font-size: 4rem;">
                                    🍱
                                </div>
                            <?php endif; ?>

                            <div class="card-body d-flex flex-column p-3">

                                <!-- Urgency Timer -->
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="badge bg-<?= $urgencyClass ?> bg-opacity-15 text-<?= $urgencyClass ?> fw-semibold small px-2 py-1">
                                        <i class="bi bi-alarm me-1"></i><?= $urgencyText ?>
                                    </span>
                                    <?php if ($deal['quantity_available'] <= 3): ?>
                                        <span class="badge bg-danger bg-opacity-15 text-danger fw-semibold small px-2 py-1">
                                            Only <?= (int) $deal['quantity_available'] ?> left!
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <h6 class="fw-bold mb-1"><?= sanitize($deal['item_name']) ?></h6>

                                <div class="d-flex align-items-center gap-1 mb-2 text-muted small">
                                    <i class="bi bi-shop me-1"></i>
                                    <span><?= sanitize($deal['kitchen_name']) ?></span>
                                    <span class="mx-1">·</span>
                                    <i class="bi bi-geo-alt me-1"></i>
                                    <span><?= sanitize($deal['city']) ?></span>
                                    <?php if ($deal['hygiene_badge'] === 'verified'): ?>
                                        <span class="mx-1">·</span>
                                        <span class="text-success"><i class="bi bi-patch-check-fill"></i></span>
                                    <?php endif; ?>
                                </div>

                                <?php if (!empty($deal['dish_description'])): ?>
                                    <p class="text-muted small mb-2 flex-grow-1" style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
                                        <?= sanitize($deal['dish_description']) ?>
                                    </p>
                                <?php endif; ?>

                                <!-- Pricing Row -->
                                <div class="d-flex align-items-center justify-content-between mt-auto pt-2 border-top">
                                    <div>
                                        <span class="text-muted text-decoration-line-through small">
                                            ₹<?= number_format((float)$deal['original_price'], 2) ?>
                                        </span>
                                        <div class="fw-800 text-success fs-5">
                                            ₹<?= number_format((float)$deal['discounted_price'], 2) ?>
                                        </div>
                                    </div>

                                    <!-- Add to Cart -->
                                    <?php if (Session::get('user_id') && Session::get('user_role') === 'customer'): ?>
                                        <form action="<?= url('/cart/add') ?>" method="POST">
                                            <?= csrfField() ?>
                                            <input type="hidden" name="menu_item_id" value="<?= (int) $deal['menu_item_id'] ?>">
                                            <input type="hidden" name="quantity" value="1">
                                            <input type="hidden" name="override_price" value="<?= number_format((float)$deal['discounted_price'], 2, '.', '') ?>">
                                            <input type="hidden" name="zero_waste_id" value="<?= (int) $deal['id'] ?>">
                                            <button type="submit" class="btn btn-success btn-sm fw-semibold px-3">
                                                <i class="bi bi-cart-plus me-1"></i>Add to Cart
                                            </button>
                                        </form>
                                    <?php elseif (!Session::get('user_id')): ?>
                                        <a href="<?= url('/login') ?>" class="btn btn-outline-success btn-sm fw-semibold px-3">
                                            <i class="bi bi-box-arrow-in-right me-1"></i>Login to Order
                                        </a>
                                    <?php else: ?>
                                        <a href="<?= url('/kitchen/' . $deal['kitchen_id']) ?>" class="btn btn-outline-primary btn-sm">
                                            <i class="bi bi-eye me-1"></i>View Kitchen
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        <?php endif; ?>
    </div>
</section>

<!-- How It Works -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-4">
            <h3 class="fw-bold">How Zero Food Waste Works</h3>
            <p class="text-muted">Our mission to reduce food waste while saving you money</p>
        </div>
        <div class="row g-4 justify-content-center">
            <div class="col-md-4 text-center">
                <div class="mb-3" style="font-size:3rem;">👩‍🍳</div>
                <h6 class="fw-bold">Chefs List Unsold Meals</h6>
                <p class="text-muted small">Home chefs list freshly cooked meals at discounted prices before closing time to avoid waste</p>
            </div>
            <div class="col-md-4 text-center">
                <div class="mb-3" style="font-size:3rem;">⏱️</div>
                <h6 class="fw-bold">Time-Limited Deals</h6>
                <p class="text-muted small">Every deal has an expiry time — the food is still fresh, just discounted to sell quickly</p>
            </div>
            <div class="col-md-4 text-center">
                <div class="mb-3" style="font-size:3rem;">🌱</div>
                <h6 class="fw-bold">You Save, Planet Wins</h6>
                <p class="text-muted small">You get a great meal at a reduced price and help reduce food waste in your community</p>
            </div>
        </div>
    </div>
</section>

<style>
.zero-waste-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.zero-waste-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 32px rgba(0,0,0,0.12) !important;
}
</style>
