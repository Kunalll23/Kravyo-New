<?php
/**
 * Kravyo - Phase 10: Personalised Recommendations Page
 * Route: GET /recommendations
 * Variables: $dishes, $pageTitle, $subtitle, $isPersonalised
 */
?>
<!-- Recommendations Hero Banner -->
<section class="kitchen-browse-hero" style="background: linear-gradient(135deg, #1a0533 0%, #3d0b6e 50%, #6a0dad 100%);">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <?php if ($isPersonalised): ?>
                        <span class="badge px-3 py-2 rounded-pill fw-bold"
                              style="background: rgba(255,255,255,0.15); backdrop-filter: blur(10px); color:#fff; border:1px solid rgba(255,255,255,0.25);">
                            <i class="bi bi-stars me-1 text-warning"></i> AI-Powered
                        </span>
                    <?php endif; ?>
                </div>
                <h1 class="fw-800 text-white mb-2">
                    <?php if ($isPersonalised): ?>
                        <i class="bi bi-magic me-2 text-warning"></i>
                    <?php else: ?>
                        <i class="bi bi-fire me-2 text-warning"></i>
                    <?php endif; ?>
                    <?= sanitize($pageTitle) ?>
                </h1>
                <p class="text-white-50 mb-0"><?= sanitize($subtitle) ?></p>
            </div>
            <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                <span class="badge bg-white text-dark px-3 py-2 fs-6 rounded-pill">
                    <i class="bi bi-egg-fried text-warning me-1"></i>
                    <?= count($dishes) ?> Dish<?= count($dishes) !== 1 ? 'es' : '' ?> Found
                </span>
            </div>
        </div>
    </div>
</section>

<!-- How the Engine Works (personalised only) -->
<?php if ($isPersonalised): ?>
<section style="background: linear-gradient(135deg, #f8f0ff 0%, #faf5ff 100%); border-bottom: 1px solid #e8d5ff;">
    <div class="container py-3">
        <div class="d-flex flex-wrap align-items-center gap-4 text-center text-md-start justify-content-center justify-content-md-start">
            <div class="d-flex align-items-center gap-2 small text-muted">
                <span class="d-flex align-items-center justify-content-center rounded-circle fw-bold text-white"
                      style="width:28px;height:28px;background:#6a0dad;font-size:0.7rem;">1</span>
                <span>📦 <strong>Category Affinity</strong> — your most-ordered cuisines</span>
            </div>
            <div class="d-flex align-items-center gap-2 small text-muted">
                <span class="d-flex align-items-center justify-content-center rounded-circle fw-bold text-white"
                      style="width:28px;height:28px;background:#6a0dad;font-size:0.7rem;">2</span>
                <span>🥗 <strong>Dietary Match</strong> — veg, jain &amp; diabetic preferences</span>
            </div>
            <div class="d-flex align-items-center gap-2 small text-muted">
                <span class="d-flex align-items-center justify-content-center rounded-circle fw-bold text-white"
                      style="width:28px;height:28px;background:#6a0dad;font-size:0.7rem;">3</span>
                <span>🔥 <strong>Platform Popularity</strong> — loved by others too</span>
            </div>
            <div class="d-flex align-items-center gap-2 small text-muted">
                <span class="d-flex align-items-center justify-content-center rounded-circle fw-bold text-white"
                      style="width:28px;height:28px;background:#6a0dad;font-size:0.7rem;">4</span>
                <span>🔄 <strong>Freshness</strong> — avoids recently ordered items</span>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Dish Grid -->
<section class="py-5">
    <div class="container">

        <?php if (empty($dishes)): ?>
            <div class="text-center py-5">
                <div class="empty-state-icon mb-3" style="font-size:4rem; color:#ccc;">
                    <i class="bi bi-magic"></i>
                </div>
                <h4 class="fw-bold text-muted">No Recommendations Yet</h4>
                <p class="text-muted mb-4">
                    <?php if ($isPersonalised): ?>
                        Place a few orders and we'll learn your taste preferences!
                    <?php else: ?>
                        No dishes are available right now. Check back soon.
                    <?php endif; ?>
                </p>
                <a href="<?= url('/menu') ?>" class="btn btn-kravyo-primary">
                    <i class="bi bi-journal-text me-1"></i> Browse All Dishes
                </a>
            </div>

        <?php else: ?>
            <!-- Pinned explanation for guest / cold-start -->
            <?php if (!$isPersonalised): ?>
                <div class="alert border-0 mb-4 px-4 py-3 rounded-3 d-flex align-items-start gap-3"
                     style="background: linear-gradient(135deg,#fff8e1,#fff3cd); border-left: 4px solid #ffc107 !important;">
                    <i class="bi bi-lightbulb-fill text-warning fs-4 mt-1"></i>
                    <div>
                        <strong>Get personalised picks!</strong>
                        <span class="text-muted ms-1">
                            <a href="<?= url('/login') ?>" class="text-decoration-none fw-semibold">Log in</a>
                            and place an order — our AI will learn your taste preferences and personalise this page just for you.
                        </span>
                    </div>
                </div>
            <?php endif; ?>

            <div class="row g-4" id="recommendations-grid">
                <?php foreach ($dishes as $index => $dish): ?>
                    <div class="col-xl-3 col-lg-4 col-md-6 animate-in" style="animation-delay: <?= $index * 0.04 ?>s">
                        <div class="card dish-card h-100 position-relative">

                            <!-- Score badge (only for personalised, debug-style subtle) -->
                            <?php if ($isPersonalised && isset($dish['score']) && $dish['score'] > 0): ?>
                                <div class="position-absolute top-0 end-0 m-2 z-1">
                                    <span class="badge rounded-pill px-2 py-1 small fw-semibold"
                                          style="background: linear-gradient(135deg,#6a0dad,#9b30ff); color:#fff; font-size:0.65rem;">
                                        <i class="bi bi-stars me-1"></i>Match
                                    </span>
                                </div>
                            <?php elseif (!$isPersonalised && isset($dish['score']) && (int)$dish['score'] > 0): ?>
                                <div class="position-absolute top-0 end-0 m-2 z-1">
                                    <span class="badge bg-warning text-dark rounded-pill px-2 py-1 small fw-semibold" style="font-size:0.65rem;">
                                        <i class="bi bi-fire me-1"></i>Popular
                                    </span>
                                </div>
                            <?php endif; ?>

                            <!-- Dish Image -->
                            <a href="<?= url('/dish/' . $dish['id']) ?>" class="text-decoration-none">
                                <div class="dish-card-image">
                                    <?php if (!empty($dish['image'])): ?>
                                        <img src="<?= UPLOAD_URL . '/dishes/' . $dish['image'] ?>"
                                             alt="<?= sanitize($dish['item_name']) ?>">
                                    <?php else: ?>
                                        <div class="dish-image-placeholder">
                                            <i class="bi bi-egg-fried"></i>
                                        </div>
                                    <?php endif; ?>
                                    <span class="dish-price-badge"><?= format_currency($dish['price']) ?></span>
                                </div>
                            </a>

                            <div class="card-body p-3 d-flex flex-column">
                                <a href="<?= url('/dish/' . $dish['id']) ?>" class="text-decoration-none">
                                    <h6 class="fw-bold mb-1 text-dark"><?= sanitize($dish['item_name']) ?></h6>
                                </a>

                                <p class="text-muted small mb-2">
                                    <a href="<?= url('/kitchen/' . $dish['kitchen_id']) ?>"
                                       class="text-decoration-none text-muted">
                                        <i class="bi bi-shop me-1"></i><?= sanitize($dish['kitchen_name']) ?>
                                    </a>
                                    <span class="mx-1">•</span>
                                    <i class="bi bi-geo-alt me-1"></i><?= sanitize($dish['city']) ?>
                                </p>

                                <!-- Dietary Badges -->
                                <div class="d-flex flex-wrap gap-1 mb-3">
                                    <?php if ($dish['is_veg']): ?>
                                        <span class="dietary-badge dietary-veg"><i class="bi bi-circle-fill me-1"></i>Veg</span>
                                    <?php else: ?>
                                        <span class="dietary-badge dietary-nonveg"><i class="bi bi-circle-fill me-1"></i>Non-Veg</span>
                                    <?php endif; ?>
                                    <?php if ($dish['is_jain_available']): ?>
                                        <span class="dietary-badge dietary-jain"><i class="bi bi-flower1 me-1"></i>Jain</span>
                                    <?php endif; ?>
                                    <?php if ($dish['is_diabetic_friendly']): ?>
                                        <span class="dietary-badge dietary-diabetic"><i class="bi bi-heart-pulse me-1"></i>Diabetic</span>
                                    <?php endif; ?>
                                    <?php if (!empty($dish['hygiene_badge']) && $dish['hygiene_badge'] === 'verified'): ?>
                                        <span class="dietary-badge" style="background:#e8f5e9;color:#2e7d32;border-color:#a5d6a7;">
                                            <i class="bi bi-shield-check me-1"></i>Hygiene
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <a href="<?= url('/dish/' . $dish['id']) ?>"
                                   class="btn btn-kravyo-primary btn-sm w-100 mt-auto">
                                    <i class="bi bi-cart-plus me-1"></i> View &amp; Order
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Bottom CTAs -->
            <div class="text-center mt-5 pt-3">
                <p class="text-muted mb-3">Want to explore more options?</p>
                <div class="d-flex flex-wrap gap-3 justify-content-center">
                    <a href="<?= url('/menu') ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-journal-text me-1"></i> Browse Full Catalog
                    </a>
                    <a href="<?= url('/kitchens') ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-shop me-1"></i> Explore Kitchens
                    </a>
                    <a href="<?= url('/zero-waste') ?>" class="btn btn-outline-success">
                        <i class="bi bi-tag-fill me-1"></i> Zero Waste Deals
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<style>
/* Page-level animation for dish cards */
@keyframes slideUpFade {
    from { opacity: 0; transform: translateY(20px); }
    to   { opacity: 1; transform: translateY(0); }
}
.animate-in {
    opacity: 0;
    animation: slideUpFade 0.45s ease forwards;
}
/* Recommendations hero override */
.kitchen-browse-hero { padding: 3.5rem 0; }
</style>
