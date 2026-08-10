<!-- Phase 7: Tiffin Plan Detail & Subscribe Page -->
<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= url('/') ?>" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item"><a href="<?= url('/subscriptions') ?>" class="text-decoration-none">Tiffin Plans</a></li>
            <li class="breadcrumb-item active"><?= sanitize($plan['plan_name']) ?></li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Plan Details Column -->
        <div class="col-lg-7">
            <div class="card kravyo-card border-0 mb-4">
                <div class="card-body p-4 p-md-5">
                    <!-- Plan Type Badge -->
                    <span class="plan-type-badge plan-type-<?= $plan['plan_type'] ?> mb-3 d-inline-block">
                        <i class="bi bi-<?= $plan['plan_type'] === 'weekly' ? 'calendar-week' : 'calendar-month' ?> me-1"></i>
                        <?= ucfirst($plan['plan_type']) ?> Plan
                    </span>

                    <h2 class="fw-800 mb-2"><?= sanitize($plan['plan_name']) ?></h2>

                    <div class="subscription-price-large mb-3">
                        <?= format_currency($plan['price']) ?>
                        <span class="text-muted fs-6 fw-normal">/ <?= $plan['plan_type'] === 'weekly' ? '7 days' : '30 days' ?></span>
                    </div>

                    <?php if (!empty($plan['description'])): ?>
                        <p class="text-muted fs-6 mb-4"><?= nl2br(sanitize($plan['description'])) ?></p>
                    <?php endif; ?>

                    <!-- Plan Features -->
                    <div class="row g-3 mb-4">
                        <div class="col-6">
                            <div class="d-flex align-items-center gap-2">
                                <div class="feature-icon-sm bg-warning bg-opacity-10 text-warning">
                                    <i class="bi bi-egg-fried"></i>
                                </div>
                                <div>
                                    <div class="fw-bold"><?= $plan['meals_per_day'] ?> Meal<?= $plan['meals_per_day'] > 1 ? 's' : '' ?></div>
                                    <small class="text-muted">Per Day</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center gap-2">
                                <div class="feature-icon-sm bg-success bg-opacity-10 text-success">
                                    <i class="bi bi-calendar-check"></i>
                                </div>
                                <div>
                                    <div class="fw-bold"><?= $plan['plan_type'] === 'weekly' ? '7' : '30' ?> Days</div>
                                    <small class="text-muted">Duration</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center gap-2">
                                <div class="feature-icon-sm bg-info bg-opacity-10 text-info">
                                    <i class="bi bi-truck"></i>
                                </div>
                                <div>
                                    <div class="fw-bold">Daily Delivery</div>
                                    <small class="text-muted">To Your Door</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center gap-2">
                                <div class="feature-icon-sm bg-primary bg-opacity-10 text-primary">
                                    <i class="bi bi-currency-rupee"></i>
                                </div>
                                <div>
                                    <div class="fw-bold"><?= format_currency(round($plan['price'] / ($plan['plan_type'] === 'weekly' ? 7 : 30), 2)) ?></div>
                                    <small class="text-muted">Per Day Cost</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Kitchen Info Card -->
                    <div class="border rounded-3 p-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar-circle"><?= strtoupper(substr($plan['chef_name'], 0, 1)) ?></div>
                            <div class="flex-grow-1">
                                <h6 class="fw-bold mb-0"><?= sanitize($plan['kitchen_name']) ?></h6>
                                <small class="text-muted">
                                    <i class="bi bi-person-fill me-1"></i>Chef <?= sanitize($plan['chef_name']) ?>
                                    <span class="mx-1">•</span>
                                    <i class="bi bi-geo-alt me-1"></i><?= sanitize($plan['city']) ?>, <?= sanitize($plan['pincode']) ?>
                                </small>
                            </div>
                            <?php if ($avgRating > 0): ?>
                                <div class="text-end">
                                    <div class="fw-bold">
                                        <?= $avgRating ?> <i class="bi bi-star-fill text-warning"></i>
                                    </div>
                                    <small class="text-muted"><?= $reviewCount ?> review<?= $reviewCount !== 1 ? 's' : '' ?></small>
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php if ($plan['hygiene_badge'] === 'verified'): ?>
                            <div class="mt-2">
                                <span class="badge bg-success bg-opacity-10 text-success">
                                    <i class="bi bi-shield-check me-1"></i>Hygiene Verified Kitchen
                                </span>
                            </div>
                        <?php endif; ?>

                        <div class="mt-2">
                            <a href="<?= url('/kitchen/' . $plan['kitchen_id']) ?>" class="text-decoration-none small">
                                <i class="bi bi-arrow-right me-1"></i>View Full Kitchen Profile
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Subscribe Form Column -->
        <div class="col-lg-5">
            <div class="card kravyo-card border-0 sticky-top" style="top: 100px;">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4">
                        <i class="bi bi-cart-check text-warning me-2"></i>Subscribe to This Plan
                    </h5>

                    <?php if (!Session::has('user_id')): ?>
                        <!-- Not logged in -->
                        <div class="text-center py-4">
                            <i class="bi bi-person-lock display-4 text-muted mb-3"></i>
                            <p class="text-muted mb-3">Please log in to subscribe to this tiffin plan.</p>
                            <a href="<?= url('/login') ?>" class="btn btn-kravyo-primary">
                                <i class="bi bi-box-arrow-in-right me-1"></i> Log In
                            </a>
                            <p class="mt-2 small text-muted">Don't have an account? <a href="<?= url('/register') ?>">Register</a></p>
                        </div>
                    <?php elseif (!$plan['is_open']): ?>
                        <!-- Kitchen is closed -->
                        <div class="text-center py-4">
                            <i class="bi bi-shop display-4 text-danger mb-3"></i>
                            <p class="text-muted">This kitchen is currently closed. Please check back later.</p>
                        </div>
                    <?php elseif (empty($addresses)): ?>
                        <!-- No saved addresses -->
                        <div class="text-center py-4">
                            <i class="bi bi-geo-alt display-4 text-muted mb-3"></i>
                            <p class="text-muted mb-3">You need to add a delivery address before subscribing.</p>
                            <p class="small text-muted">Place a regular order first to save your address, then come back to subscribe.</p>
                        </div>
                    <?php else: ?>
                        <form action="<?= url('/subscription/subscribe') ?>" method="POST">
                            <?= csrf_field() ?>
                            <input type="hidden" name="plan_id" value="<?= $plan['id'] ?>">

                            <!-- Summary -->
                            <div class="bg-light rounded-3 p-3 mb-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Plan</span>
                                    <span class="fw-semibold"><?= sanitize($plan['plan_name']) ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Duration</span>
                                    <span class="fw-semibold"><?= ucfirst($plan['plan_type']) ?> (<?= $plan['plan_type'] === 'weekly' ? '7' : '30' ?> days)</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Meals/Day</span>
                                    <span class="fw-semibold"><?= $plan['meals_per_day'] ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Starts</span>
                                    <span class="fw-semibold"><?= date('M d, Y', strtotime('+1 day')) ?></span>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between">
                                    <span class="fw-bold">Total Amount</span>
                                    <span class="fw-800 text-primary fs-5"><?= format_currency($plan['price']) ?></span>
                                </div>
                            </div>

                            <!-- Delivery Address -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Delivery Address</label>
                                <select class="form-select" name="address_id" required>
                                    <option value="">Select your delivery address</option>
                                    <?php foreach ($addresses as $addr): ?>
                                        <option value="<?= $addr['id'] ?>">
                                            <?= sanitize($addr['address_type']) ?> — <?= sanitize($addr['street_address']) ?>, <?= sanitize($addr['city']) ?> <?= sanitize($addr['pincode']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Payment Method -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Payment Method</label>
                                <div class="d-flex flex-wrap gap-2">
                                    <div>
                                        <input type="radio" class="btn-check" name="payment_method" id="pay_cod" value="cod" checked>
                                        <label class="btn btn-outline-secondary btn-sm" for="pay_cod">
                                            <i class="bi bi-cash me-1"></i> Cash on Delivery
                                        </label>
                                    </div>
                                    <div>
                                        <input type="radio" class="btn-check" name="payment_method" id="pay_upi" value="upi">
                                        <label class="btn btn-outline-secondary btn-sm" for="pay_upi">
                                            <i class="bi bi-phone me-1"></i> UPI
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Special Instructions -->
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Special Instructions (Optional)</label>
                                <textarea class="form-control" name="special_instructions" rows="2"
                                          placeholder="e.g. Less oil, no onion/garlic, deliver before 1 PM"></textarea>
                            </div>

                            <button type="submit" class="btn btn-kravyo-primary btn-lg w-100">
                                <i class="bi bi-check-circle me-1"></i> Subscribe Now — <?= format_currency($plan['price']) ?>
                            </button>

                            <p class="text-center text-muted small mt-2 mb-0">
                                Deliveries start from <?= date('M d, Y', strtotime('+1 day')) ?>
                            </p>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
