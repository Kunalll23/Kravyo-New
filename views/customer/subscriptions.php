<!-- Phase 7: Browse Tiffin Subscription Plans -->
<section class="subscription-hero">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <h1 class="fw-800 text-white mb-3">
                    <i class="bi bi-calendar2-heart text-warning me-2"></i>Tiffin Subscription Plans
                </h1>
                <p class="text-white-50 fs-5 mb-0">Subscribe to daily home-cooked meals from your favourite kitchens. Fresh, healthy, and delivered to your door.</p>
            </div>
            <div class="col-lg-5 text-end d-none d-lg-block">
                <i class="bi bi-box-seam display-1 text-warning opacity-25"></i>
            </div>
        </div>
    </div>
</section>

<div class="container py-4">
    <!-- Filters -->
    <div class="card kravyo-card border-0 mb-4">
        <div class="card-body p-3">
            <form method="GET" action="<?= url('/subscriptions') ?>" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-semibold small mb-1">Search</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" class="form-control border-start-0" name="q" placeholder="Search plans or kitchens..."
                               value="<?= sanitize($filters['keyword']) ?>">
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold small mb-1">Plan Type</label>
                    <select class="form-select" name="plan_type">
                        <option value="">All Types</option>
                        <option value="weekly" <?= $filters['plan_type'] === 'weekly' ? 'selected' : '' ?>>Weekly</option>
                        <option value="monthly" <?= $filters['plan_type'] === 'monthly' ? 'selected' : '' ?>>Monthly</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold small mb-1">City</label>
                    <select class="form-select" name="city">
                        <option value="">All Cities</option>
                        <?php foreach ($cities as $city): ?>
                            <option value="<?= sanitize($city) ?>" <?= $filters['city'] === $city ? 'selected' : '' ?>>
                                <?= sanitize($city) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold small mb-1">Sort By</label>
                    <select class="form-select" name="sort">
                        <option value="newest" <?= $filters['sort'] === 'newest' ? 'selected' : '' ?>>Newest</option>
                        <option value="price_low" <?= $filters['sort'] === 'price_low' ? 'selected' : '' ?>>Price: Low → High</option>
                        <option value="price_high" <?= $filters['sort'] === 'price_high' ? 'selected' : '' ?>>Price: High → Low</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-kravyo-primary w-100">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Results Count -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <p class="text-muted mb-0">
            <strong><?= count($plans) ?></strong> tiffin plan<?= count($plans) !== 1 ? 's' : '' ?> available
        </p>
        <?php if (Session::has('user_id')): ?>
            <a href="<?= url('/my-subscriptions') ?>" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-journal-check me-1"></i> My Subscriptions
            </a>
        <?php endif; ?>
    </div>

    <!-- Plans Grid -->
    <?php if (empty($plans)): ?>
        <div class="empty-state text-center py-5">
            <div class="empty-state-icon mb-3"><i class="bi bi-calendar2-x"></i></div>
            <h5 class="text-muted fw-bold">No Tiffin Plans Available</h5>
            <p class="text-muted">Home chefs haven't created any tiffin plans yet. Check back soon!</p>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($plans as $plan): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="card subscription-card h-100">
                        <div class="card-body p-4">
                            <!-- Plan Type Badge -->
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <span class="plan-type-badge plan-type-<?= $plan['plan_type'] ?>">
                                    <i class="bi bi-<?= $plan['plan_type'] === 'weekly' ? 'calendar-week' : 'calendar-month' ?> me-1"></i>
                                    <?= ucfirst($plan['plan_type']) ?> Plan
                                </span>
                                <?php if ($plan['hygiene_badge'] === 'verified'): ?>
                                    <span class="badge bg-success bg-opacity-10 text-success small">
                                        <i class="bi bi-shield-check me-1"></i>Verified
                                    </span>
                                <?php endif; ?>
                            </div>

                            <!-- Plan Name & Price -->
                            <h5 class="fw-bold mb-1"><?= sanitize($plan['plan_name']) ?></h5>
                            <div class="subscription-price mb-2">
                                <?= format_currency($plan['price']) ?>
                                <span class="text-muted small fw-normal">/ <?= $plan['plan_type'] === 'weekly' ? '7 days' : '30 days' ?></span>
                            </div>

                            <!-- Description -->
                            <?php if (!empty($plan['description'])): ?>
                                <p class="text-muted small mb-3"><?= sanitize($plan['description']) ?></p>
                            <?php endif; ?>

                            <!-- Details -->
                            <div class="d-flex flex-wrap gap-2 mb-3">
                                <span class="badge bg-light text-dark border">
                                    <i class="bi bi-egg-fried me-1 text-warning"></i>
                                    <?= $plan['meals_per_day'] ?> meal<?= $plan['meals_per_day'] > 1 ? 's' : '' ?>/day
                                </span>
                                <span class="badge bg-light text-dark border">
                                    <i class="bi bi-geo-alt me-1 text-danger"></i>
                                    <?= sanitize($plan['city']) ?>
                                </span>
                            </div>

                            <!-- Kitchen Info -->
                            <div class="d-flex align-items-center gap-2 mb-3 border-top pt-3">
                                <div class="avatar-circle-sm"><?= strtoupper(substr($plan['chef_name'], 0, 1)) ?></div>
                                <div>
                                    <div class="fw-semibold small"><?= sanitize($plan['kitchen_name']) ?></div>
                                    <div class="text-muted" style="font-size: 0.75rem;">by <?= sanitize($plan['chef_name']) ?></div>
                                </div>
                            </div>

                            <!-- CTA -->
                            <a href="<?= url('/subscription/' . $plan['id']) ?>" class="btn btn-kravyo-primary w-100">
                                <i class="bi bi-arrow-right-circle me-1"></i> View & Subscribe
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
