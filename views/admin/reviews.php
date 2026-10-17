<?php
/**
 * Kravyo - Admin Review Management & Moderation
 * Views: views/admin/reviews.php
 */

$totalReviews  = $metrics['total'] ?? 0;
$avgRating     = $metrics['avg_rating'] ?? 0.0;
$positivePct   = $metrics['positive_pct'] ?? 0;
$criticalCount = $metrics['critical_count'] ?? 0;

// Helper to build URL with query params
function adminReviewFilterUrl(array $overrides = []): string {
    $params = $_GET;
    foreach ($overrides as $k => $v) {
        if ($v === null || $v === '') {
            unset($params[$k]);
        } else {
            $params[$k] = $v;
        }
    }
    $query = http_build_query($params);
    return url('/admin/reviews' . ($query ? '?' . $query : ''));
}
?>

<div class="container-fluid px-3 px-md-4 py-4">
    <!-- Header & Breadcrumbs -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-warning bg-opacity-10 text-dark fw-bold px-2 py-1 rounded-pill">
                    <i class="bi bi-shield-check text-warning me-1"></i> Content & Platform Moderation
                </span>
                <span class="text-muted small">Platform-Wide Review System</span>
            </div>
            <h2 class="fw-bold mb-1">Customer Reviews Management</h2>
            <p class="text-muted small mb-0">Monitor customer satisfaction, order-linked feedback, and moderate reviews across all kitchens.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="<?= url('/admin/dashboard') ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="bi bi-speedometer2 me-1"></i> Admin Dashboard
            </a>
            <a href="<?= url('/admin/orders') ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                <i class="bi bi-bag-check me-1"></i> Platform Orders
            </a>
        </div>
    </div>

    <?php View::partial('flash_messages'); ?>

    <!-- KPI Summary Cards -->
    <div class="row g-3 mb-4">
        <!-- Platform Rating -->
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-white">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold text-uppercase">Platform Rating</span>
                    <span class="badge bg-warning text-dark px-2 py-1 rounded-pill"><i class="bi bi-star-fill"></i> Score</span>
                </div>
                <div class="d-flex align-items-baseline gap-2">
                    <span class="display-6 fw-bold text-dark"><?= number_format($avgRating, 1) ?></span>
                    <span class="text-muted fs-6">/ 5.0</span>
                </div>
                <div class="text-warning small mt-1">
                    <?php for ($s = 1; $s <= 5; $s++): ?>
                        <i class="bi bi-star<?= $s <= round($avgRating) ? '-fill' : '' ?>"></i>
                    <?php endfor; ?>
                    <span class="text-muted ms-1 small">(<?= $totalReviews ?> global)</span>
                </div>
            </div>
        </div>

        <!-- Satisfaction Rate -->
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-white">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold text-uppercase">Satisfaction Rate</span>
                    <span class="badge bg-success bg-opacity-10 text-success px-2 py-1 rounded-pill"><i class="bi bi-heart-fill"></i> Positive</span>
                </div>
                <div class="d-flex align-items-baseline gap-2">
                    <span class="display-6 fw-bold text-success"><?= $positivePct ?>%</span>
                </div>
                <div class="text-muted small mt-1">
                    <?= $metrics['positive_count'] ?? 0 ?> of <?= $totalReviews ?> rated 4★ or 5★
                </div>
            </div>
        </div>

        <!-- Total Reviews -->
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-white">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold text-uppercase">Total Feedbacks</span>
                    <span class="badge bg-info bg-opacity-10 text-info px-2 py-1 rounded-pill"><i class="bi bi-chat-quote-fill"></i> Verified</span>
                </div>
                <div class="d-flex align-items-baseline gap-2">
                    <span class="display-6 fw-bold text-dark"><?= count($reviews) ?></span>
                    <?php if (count($reviews) !== $totalReviews): ?>
                        <span class="text-muted fs-6">filtered</span>
                    <?php endif; ?>
                </div>
                <div class="text-muted small mt-1">
                    Across all verified delivered orders
                </div>
            </div>
        </div>

        <!-- Critical Attention -->
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-white">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold text-uppercase">Critical Reviews</span>
                    <span class="badge <?= $criticalCount > 0 ? 'bg-danger bg-opacity-10 text-danger' : 'bg-success bg-opacity-10 text-success' ?> px-2 py-1 rounded-pill">
                        <i class="bi <?= $criticalCount > 0 ? 'bi-exclamation-triangle-fill' : 'bi-shield-check' ?>"></i>
                        <?= $criticalCount > 0 ? 'Alert' : 'Clean' ?>
                    </span>
                </div>
                <div class="d-flex align-items-baseline gap-2">
                    <span class="display-6 fw-bold <?= $criticalCount > 0 ? 'text-danger' : 'text-success' ?>"><?= $criticalCount ?></span>
                    <span class="text-muted fs-6">low score</span>
                </div>
                <div class="text-muted small mt-1">
                    <?= $criticalCount > 0 ? '1-star and 2-star feedback' : 'No critical alerts active!' ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white p-3" style="position: relative; z-index: 1050;">
        <form action="<?= url('/admin/reviews') ?>" method="GET" id="adminReviewFilterForm" class="row g-2 align-items-center">
            
            <!-- Search Input with Live Auto-Suggestions -->
            <div class="col-12 col-md-4 col-xl-3">
                <div class="position-relative" id="adminReviewSearchWrapper">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" id="adminReviewSearchInput" class="form-control form-control-sm border-start-0 bg-light" 
                               placeholder="Search customer, kitchen, order #..." 
                               autocomplete="off"
                               value="<?= sanitize($filters['search'] ?? '') ?>">
                        <?php if (!empty($filters['search'])): ?>
                            <a href="<?= url('/admin/reviews') ?>" class="input-group-text bg-light border-start-0 text-muted text-decoration-none" title="Clear Search">
                                <i class="bi bi-x-circle-fill"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                    <!-- Live Suggestions Menu Dropdown -->
                    <div id="searchSuggestionsMenu" class="kravyo-admin-suggestions d-none"></div>
                </div>
            </div>

            <!-- Kitchen Filter -->
            <div class="col-6 col-md-3 col-xl-2">
                <select name="kitchen_id" class="form-select form-select-sm bg-light" onchange="this.form.submit()">
                    <option value="">🍽️ All Kitchens</option>
                    <?php foreach ($kitchens as $k): ?>
                        <option value="<?= $k['id'] ?>" <?= ($filters['kitchen_id'] == $k['id']) ? 'selected' : '' ?>>
                            <?= sanitize($k['kitchen_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- City Filter -->
            <div class="col-6 col-md-2 col-xl-2">
                <select name="city" class="form-select form-select-sm bg-light" onchange="this.form.submit()">
                    <option value="">📍 All Cities</option>
                    <?php foreach ($cities as $c): ?>
                        <option value="<?= sanitize($c) ?>" <?= ($filters['city'] === $c) ? 'selected' : '' ?>>
                            <?= sanitize($c) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Rating Filter -->
            <div class="col-6 col-md-2 col-xl-1">
                <select name="rating" class="form-select form-select-sm bg-light" onchange="this.form.submit()">
                    <option value="">⭐ Rating</option>
                    <option value="5" <?= ($filters['rating'] === '5') ? 'selected' : '' ?>>5 Stars</option>
                    <option value="4" <?= ($filters['rating'] === '4') ? 'selected' : '' ?>>4 Stars</option>
                    <option value="3" <?= ($filters['rating'] === '3') ? 'selected' : '' ?>>3 Stars</option>
                    <option value="2" <?= ($filters['rating'] === '2') ? 'selected' : '' ?>>2 Stars</option>
                    <option value="1" <?= ($filters['rating'] === '1') ? 'selected' : '' ?>>1 Star</option>
                    <option value="positive" <?= ($filters['rating'] === 'positive') ? 'selected' : '' ?>>Positive (4-5★)</option>
                    <option value="critical" <?= ($filters['rating'] === 'critical') ? 'selected' : '' ?>>Critical (1-2★)</option>
                </select>
            </div>

            <!-- Time Filter -->
            <div class="col-6 col-md-2 col-xl-2">
                <select name="time_range" id="adminTimeRangeSelect" class="form-select form-select-sm bg-light" onchange="handleAdminTimeChange(this)">
                    <option value="all" <?= ($filters['time_range'] === 'all') ? 'selected' : '' ?>>📅 All Time</option>
                    <option value="today" <?= ($filters['time_range'] === 'today') ? 'selected' : '' ?>>Today</option>
                    <option value="7days" <?= ($filters['time_range'] === '7days') ? 'selected' : '' ?>>Last 7 Days</option>
                    <option value="30days" <?= ($filters['time_range'] === '30days') ? 'selected' : '' ?>>Last 30 Days</option>
                    <option value="this_month" <?= ($filters['time_range'] === 'this_month') ? 'selected' : '' ?>>This Month</option>
                    <option value="custom" <?= ($filters['time_range'] === 'custom') ? 'selected' : '' ?>>Custom Range...</option>
                </select>
            </div>

            <!-- Sort By -->
            <div class="col-6 col-md-2 col-xl-1">
                <select name="sort" class="form-select form-select-sm bg-light" onchange="this.form.submit()">
                    <option value="recent" <?= ($filters['sort'] === 'recent') ? 'selected' : '' ?>>🕒 Newest</option>
                    <option value="oldest" <?= ($filters['sort'] === 'oldest') ? 'selected' : '' ?>>⏳ Oldest</option>
                    <option value="highest" <?= ($filters['sort'] === 'highest') ? 'selected' : '' ?>>⭐ Top</option>
                    <option value="lowest" <?= ($filters['sort'] === 'lowest') ? 'selected' : '' ?>>⚠️ Low</option>
                </select>
            </div>

            <!-- Action Buttons -->
            <div class="col-12 col-md-auto d-flex align-items-center gap-2 ms-auto">
                <button type="submit" class="btn btn-primary btn-sm px-3 rounded-pill">
                    <i class="bi bi-funnel-fill me-1"></i> Filter
                </button>
                <?php 
                    $hasActiveFilters = !empty($filters['kitchen_id']) || !empty($filters['city']) || 
                                        !empty($filters['rating']) || (!empty($filters['time_range']) && $filters['time_range'] !== 'all') || 
                                        !empty($filters['search']) || ($filters['sort'] !== 'recent');
                ?>
                <?php if ($hasActiveFilters): ?>
                    <a href="<?= url('/admin/reviews') ?>" class="btn btn-outline-secondary btn-sm px-3 rounded-pill" title="Reset All Filters">
                        <i class="bi bi-x-circle me-1"></i> Reset
                    </a>
                <?php endif; ?>
            </div>

            <!-- Custom Date Row -->
            <div class="col-12 mt-2 <?= ($filters['time_range'] === 'custom') ? '' : 'd-none' ?>" id="adminCustomDateRow">
                <div class="d-flex align-items-center gap-2 p-2 bg-light rounded-3">
                    <span class="small text-muted fw-semibold">From:</span>
                    <input type="date" name="start_date" class="form-control form-control-sm" style="max-width: 160px;" value="<?= sanitize($filters['start_date'] ?? '') ?>">
                    <span class="small text-muted fw-semibold ms-2">To:</span>
                    <input type="date" name="end_date" class="form-control form-control-sm" style="max-width: 160px;" value="<?= sanitize($filters['end_date'] ?? '') ?>">
                    <button type="submit" class="btn btn-sm btn-dark px-3 rounded-pill ms-2">Apply Date</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Reviews Table Card -->
    <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0">
                <i class="bi bi-star-fill text-warning me-2"></i>Customer Reviews & Ratings
                <span class="badge bg-light text-secondary border rounded-pill ms-2"><?= count($reviews) ?> Reviews</span>
            </h6>
            <?php if (!empty($filters['rating'])): ?>
                <span class="badge bg-warning bg-opacity-10 text-dark px-3 py-1 rounded-pill small">
                    Rating Filter: <?= sanitize($filters['rating']) ?>
                </span>
            <?php endif; ?>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4" style="width: 70px;">ID</th>
                        <th>Customer</th>
                        <th>Kitchen</th>
                        <th>Order</th>
                        <th>Rating</th>
                        <th style="min-width: 250px;">Feedback & Dishes</th>
                        <th>Date</th>
                        <th class="text-end pe-4" style="width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($reviews)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-chat-square-quote display-4 d-block mb-3 opacity-25"></i>
                                <h6 class="fw-bold text-dark">No Customer Reviews Found</h6>
                                <p class="text-muted small max-w-500 mx-auto mb-2">No reviews match the selected filter criteria. Try resetting the filters.</p>
                                <a href="<?= url('/admin/reviews') ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                    Reset Filters
                                </a>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($reviews as $rev): ?>
                            <?php
                                // Parse dishes in this review order
                                $orderedItems = [];
                                if (!empty($rev['ordered_items_info'])) {
                                    $rawItems = explode('||', $rev['ordered_items_info']);
                                    foreach ($rawItems as $raw) {
                                        $parts = explode('::', $raw);
                                        if (count($parts) >= 2) {
                                            $orderedItems[] = [
                                                'id'       => $parts[0],
                                                'name'     => $parts[1],
                                                'quantity' => $parts[2] ?? 1,
                                            ];
                                        }
                                    }
                                }

                                $rScore = (int) $rev['rating'];
                                $badgeClass = 'bg-success';
                                if ($rScore === 4) $badgeClass = 'bg-primary';
                                elseif ($rScore === 3) $badgeClass = 'bg-warning text-dark';
                                elseif ($rScore <= 2) $badgeClass = 'bg-danger';

                                $initials = strtoupper(substr($rev['customer_name'] ?? 'C', 0, 1));
                            ?>
                            <tr>
                                <!-- ID -->
                                <td class="ps-4 text-muted small fw-bold">#<?= $rev['id'] ?></td>

                                <!-- Customer -->
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle bg-light text-dark fw-bold d-flex align-items-center justify-content-center border" style="width:32px; height:32px; font-size:0.8rem;">
                                            <?= $initials ?>
                                        </div>
                                        <div>
                                            <div class="fw-semibold text-dark"><?= sanitize($rev['customer_name']) ?></div>
                                            <small class="text-muted" style="font-size:0.75rem;"><i class="bi bi-telephone me-1"></i><?= sanitize($rev['customer_phone'] ?? 'N/A') ?></small>
                                        </div>
                                    </div>
                                </td>

                                <!-- Kitchen -->
                                <td>
                                    <div>
                                        <a href="<?= url('/kitchen/' . $rev['kitchen_id']) ?>" target="_blank" class="fw-semibold text-decoration-none text-dark hover-primary" title="View Public Kitchen Profile">
                                            <?= sanitize($rev['kitchen_name']) ?> <i class="bi bi-box-arrow-up-right ms-1 text-muted" style="font-size:0.65rem;"></i>
                                        </a>
                                        <?php if (!empty($rev['kitchen_city'])): ?>
                                            <div>
                                                <span class="badge bg-light text-secondary border px-2 py-0 rounded-pill" style="font-size: 0.68rem;">
                                                    <i class="bi bi-geo-alt me-1 text-danger"></i><?= sanitize($rev['kitchen_city']) ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <!-- Order Info (Deep Linked) -->
                                <td>
                                    <?php if (!empty($rev['order_number'])): ?>
                                        <div>
                                            <a href="<?= url('/admin/orders?search=' . urlencode($rev['order_number'])) ?>" 
                                               class="badge bg-light text-dark border px-2 py-1 rounded-pill text-decoration-none hover-shadow" 
                                               title="View in Admin Orders Dashboard">
                                                <i class="bi bi-receipt me-1 text-primary"></i>#<?= sanitize($rev['order_number']) ?>
                                                <i class="bi bi-box-arrow-up-right ms-1 text-muted" style="font-size:0.65rem;"></i>
                                            </a>
                                            <div class="text-muted mt-1" style="font-size:0.72rem;">
                                                ₹<?= number_format((float)($rev['order_total'] ?? 0), 2) ?>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted small">#<?= (int)$rev['order_id'] ?></span>
                                    <?php endif; ?>
                                </td>

                                <!-- Rating -->
                                <td>
                                    <div class="d-flex align-items-center gap-1">
                                        <span class="badge <?= $badgeClass ?> px-2 py-1 rounded-pill fw-bold" style="font-size:0.75rem;">
                                            <?= $rScore ?> ★
                                        </span>
                                        <span class="text-warning small ms-1">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="bi bi-star<?= $i <= $rScore ? '-fill' : '' ?>"></i>
                                            <?php endfor; ?>
                                        </span>
                                    </div>
                                </td>

                                <!-- Feedback Text & Ordered Dishes -->
                                <td>
                                    <?php if (!empty($rev['review_text'])): ?>
                                        <div class="text-dark small mb-1" style="line-height: 1.4; max-width: 320px;">
                                            "<?= sanitize($rev['review_text']) ?>"
                                        </div>
                                    <?php else: ?>
                                        <div class="text-muted fst-italic small mb-1">
                                            Rating without written note
                                        </div>
                                    <?php endif; ?>

                                    <!-- Dishes Ordered Tags -->
                                    <?php if (!empty($orderedItems)): ?>
                                        <div class="d-flex flex-wrap gap-1 mt-1">
                                            <?php foreach ($orderedItems as $it): ?>
                                                <span class="badge bg-light text-secondary border px-2 py-0 rounded-pill" style="font-size: 0.68rem;">
                                                    <?= sanitize($it['name']) ?> ×<?= (int)$it['quantity'] ?>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </td>

                                <!-- Date -->
                                <td>
                                    <small class="text-dark fw-semibold d-block"><?= date('d M Y', strtotime($rev['created_at'])) ?></small>
                                    <small class="text-muted" style="font-size: 0.72rem;"><?= date('h:i A', strtotime($rev['created_at'])) ?></small>
                                </td>

                                <!-- Actions -->
                                <td class="text-end pe-4 text-nowrap">
                                    <!-- View Modal Button -->
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-secondary rounded-pill me-1" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#reviewDetailModal"
                                            data-id="<?= $rev['id'] ?>"
                                            data-customer="<?= sanitize($rev['customer_name']) ?>"
                                            data-phone="<?= sanitize($rev['customer_phone'] ?? 'N/A') ?>"
                                            data-email="<?= sanitize($rev['customer_email'] ?? 'N/A') ?>"
                                            data-kitchen="<?= sanitize($rev['kitchen_name']) ?>"
                                            data-city="<?= sanitize($rev['kitchen_city'] ?? '') ?>"
                                            data-order="<?= sanitize($rev['order_number'] ?? ('Order #' . $rev['order_id'])) ?>"
                                            data-order-url="<?= url('/admin/orders?search=' . urlencode($rev['order_number'] ?? '')) ?>"
                                            data-total="<?= number_format((float)($rev['order_total'] ?? 0), 2) ?>"
                                            data-rating="<?= $rScore ?>"
                                            data-text="<?= sanitize($rev['review_text'] ?? '') ?>"
                                            data-date="<?= date('d M Y, h:i A', strtotime($rev['created_at'])) ?>"
                                            title="View Full Details">
                                        <i class="bi bi-eye"></i>
                                    </button>

                                    <!-- Delete Review Form -->
                                    <form action="<?= url('/admin/review/delete/' . $rev['id']) ?>" method="POST" class="d-inline">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill" onclick="return confirm('Are you sure you want to permanently delete this review? This action cannot be undone.');" title="Delete Review">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Single Root-Level Review Detail Modal (Zero-Flicker) -->
<div class="modal fade" id="reviewDetailModal" tabindex="-1" aria-labelledby="reviewDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="reviewDetailModalLabel">
                    <i class="bi bi-chat-quote-fill text-warning me-2"></i>Review Details #<span id="mdlReviewId"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Score & Date -->
                <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded-3 mb-3">
                    <div>
                        <div class="fw-bold text-dark fs-5">
                            <span id="mdlRatingScore"></span> ★ 
                            <span class="text-warning small ms-1" id="mdlRatingStars"></span>
                        </div>
                        <small class="text-muted" id="mdlDate"></small>
                    </div>
                    <span class="badge bg-white text-secondary border px-3 py-2 rounded-pill shadow-xs" id="mdlOrderBadge"></span>
                </div>

                <!-- Customer & Kitchen -->
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <div class="p-2 border rounded-3 bg-white">
                            <small class="text-muted d-block text-uppercase" style="font-size:0.65rem;">Customer</small>
                            <span class="fw-bold text-dark small" id="mdlCustomer"></span>
                            <small class="text-muted d-block" style="font-size:0.75rem;" id="mdlPhone"></small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-2 border rounded-3 bg-white">
                            <small class="text-muted d-block text-uppercase" style="font-size:0.65rem;">Kitchen</small>
                            <span class="fw-bold text-dark small" id="mdlKitchen"></span>
                            <small class="text-muted d-block" style="font-size:0.75rem;" id="mdlCity"></small>
                        </div>
                    </div>
                </div>

                <!-- Review Text -->
                <div class="mb-3">
                    <label class="form-label text-muted small fw-semibold text-uppercase">Customer Statement</label>
                    <div class="p-3 bg-light rounded-3 text-dark" style="font-size: 0.95rem; line-height: 1.5;" id="mdlReviewText">
                    </div>
                </div>

                <!-- Direct Order Link -->
                <div class="text-center pt-2">
                    <a href="#" id="mdlOrderLink" class="btn btn-outline-primary btn-sm rounded-pill px-4">
                        <i class="bi bi-box-arrow-up-right me-1"></i> Inspect Order in Admin Orders
                    </a>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function handleAdminTimeChange(selectElem) {
    const customRow = document.getElementById('adminCustomDateRow');
    if (selectElem.value === 'custom') {
        customRow.classList.remove('d-none');
    } else {
        customRow.classList.add('d-none');
        selectElem.form.submit();
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const detailModal = document.getElementById('reviewDetailModal');
    if (detailModal) {
        detailModal.addEventListener('show.bs.modal', function (event) {
            const btn = event.relatedTarget;
            if (!btn) return;

            const id = btn.getAttribute('data-id');
            const customer = btn.getAttribute('data-customer');
            const phone = btn.getAttribute('data-phone');
            const kitchen = btn.getAttribute('data-kitchen');
            const city = btn.getAttribute('data-city');
            const order = btn.getAttribute('data-order');
            const orderUrl = btn.getAttribute('data-order-url');
            const total = btn.getAttribute('data-total');
            const rating = parseInt(btn.getAttribute('data-rating') || '0');
            const text = btn.getAttribute('data-text');
            const date = btn.getAttribute('data-date');

            document.getElementById('mdlReviewId').textContent = id;
            document.getElementById('mdlRatingScore').textContent = rating;
            document.getElementById('mdlCustomer').textContent = customer;
            document.getElementById('mdlPhone').textContent = phone;
            document.getElementById('mdlKitchen').textContent = kitchen;
            document.getElementById('mdlCity').textContent = city ? '📍 ' + city : '';
            document.getElementById('mdlOrderBadge').textContent = '#' + order + ' (₹' + total + ')';
            document.getElementById('mdlDate').textContent = date;
            document.getElementById('mdlReviewText').textContent = text ? '"' + text + '"' : 'Customer left a star rating without written feedback.';

            // Stars
            let starsHtml = '';
            for (let i = 1; i <= 5; i++) {
                starsHtml += '<i class="bi bi-star' + (i <= rating ? '-fill' : '') + '"></i>';
            }
            document.getElementById('mdlRatingStars').innerHTML = starsHtml;

            // Order Link
            const linkElem = document.getElementById('mdlOrderLink');
            if (orderUrl && orderUrl !== '#') {
                linkElem.href = orderUrl;
                linkElem.classList.remove('d-none');
            } else {
                linkElem.classList.add('d-none');
            }
        });
    }

    // ── Live Auto-Suggestions for Customers, Kitchens, Orders & Dishes ──────
    const initialSuggestionsData = <?= json_encode($initialSuggestions ?? ['customers' => [], 'kitchens' => [], 'orders' => [], 'dishes' => []], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    const suggestEndpoint = '<?= url('/admin/reviews/suggest') ?>';

    const searchInput = document.getElementById('adminReviewSearchInput');
    const suggestionsMenu = document.getElementById('searchSuggestionsMenu');
    const searchWrapper = document.getElementById('adminReviewSearchWrapper');
    const filterForm = document.getElementById('adminReviewFilterForm');

    let debounceTimer = null;
    let activeIndex = -1;

    function showSuggestions() {
        if (!suggestionsMenu) return;
        suggestionsMenu.classList.remove('d-none');
    }

    function hideSuggestions() {
        if (!suggestionsMenu) return;
        suggestionsMenu.classList.add('d-none');
        activeIndex = -1;
    }

    function escapeHtml(text) {
        if (!text) return '';
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function renderLocalFallback(query) {
        const q = query.toLowerCase();
        const filterList = (arr) => (arr || []).filter(item => 
            (item.label && item.label.toLowerCase().includes(q)) || 
            (item.subtext && item.subtext.toLowerCase().includes(q))
        );

        renderSuggestions({
            customers: filterList(initialSuggestionsData.customers),
            kitchens:  filterList(initialSuggestionsData.kitchens),
            orders:    filterList(initialSuggestionsData.orders),
            dishes:    filterList(initialSuggestionsData.dishes)
        }, query);
    }

    function fetchSuggestions(query) {
        const cleanQ = query.trim();
        if (cleanQ.length === 0) {
            renderSuggestions(initialSuggestionsData, '');
            return;
        }

        const url = suggestEndpoint + '?q=' + encodeURIComponent(cleanQ);

        fetch(url, { credentials: 'same-origin' })
            .then(res => {
                if (!res.ok) throw new Error('HTTP ' + res.status);
                return res.json();
            })
            .then(data => {
                if (data && typeof data === 'object') {
                    renderSuggestions(data, cleanQ);
                } else {
                    renderLocalFallback(cleanQ);
                }
            })
            .catch(err => {
                console.warn('AJAX suggestion fallback triggered:', err);
                renderLocalFallback(cleanQ);
            });
    }

    function renderSuggestions(data, query) {
        if (!suggestionsMenu) return;

        query = (query || '').trim();
        const customers = (data && data.customers) ? data.customers : [];
        const kitchens  = (data && data.kitchens)  ? data.kitchens  : [];
        const orders    = (data && data.orders)    ? data.orders    : [];
        const dishes    = (data && data.dishes)    ? data.dishes    : [];

        const totalFound = customers.length + kitchens.length + orders.length + dishes.length;

        let html = '';

        // Top Action: Search full term
        if (query.length > 0) {
            html += `<a href="javascript:void(0)" class="suggest-item search-term-item mb-1" data-value="${escapeHtml(query)}">
                        <span class="d-flex align-items-center gap-2 text-primary fw-bold">
                            <i class="bi bi-search"></i>
                            <span>Search all reviews for: "<strong>${escapeHtml(query)}</strong>"</span>
                        </span>
                        <span class="badge bg-primary bg-opacity-10 text-primary border" style="font-size:0.65rem;">Press Enter</span>
                     </a>`;
        }

        // Customers Section
        if (customers.length > 0) {
            html += `<div class="suggest-header"><i class="bi bi-person-fill text-primary me-1"></i> Customers</div>`;
            customers.forEach(c => {
                html += `<a href="javascript:void(0)" class="suggest-item" data-value="${escapeHtml(c.label)}">
                            <span class="d-flex align-items-center gap-2">
                                <span class="avatar-mini bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center" style="width:22px; height:22px; font-size:0.7rem;">
                                    <i class="bi bi-person"></i>
                                </span>
                                <span class="fw-semibold text-dark">${escapeHtml(c.label)}</span>
                            </span>
                            ${c.subtext ? `<small class="text-muted" style="font-size:0.72rem;">${escapeHtml(c.subtext)}</small>` : ''}
                         </a>`;
            });
        }

        // Kitchens Section
        if (kitchens.length > 0) {
            if (customers.length > 0) html += `<div class="suggest-divider"></div>`;
            html += `<div class="suggest-header"><i class="bi bi-shop text-warning me-1"></i> Kitchens</div>`;
            kitchens.forEach(k => {
                html += `<a href="javascript:void(0)" class="suggest-item" data-value="${escapeHtml(k.label)}">
                            <span class="d-flex align-items-center gap-2">
                                <span class="avatar-mini bg-warning bg-opacity-10 text-warning rounded-circle d-inline-flex align-items-center justify-content-center" style="width:22px; height:22px; font-size:0.7rem;">
                                    <i class="bi bi-shop"></i>
                                </span>
                                <span class="fw-semibold text-dark">${escapeHtml(k.label)}</span>
                            </span>
                            ${k.subtext ? `<span class="badge bg-light text-secondary border px-1" style="font-size:0.65rem;">${escapeHtml(k.subtext)}</span>` : ''}
                         </a>`;
            });
        }

        // Orders Section
        if (orders.length > 0) {
            if (customers.length > 0 || kitchens.length > 0) html += `<div class="suggest-divider"></div>`;
            html += `<div class="suggest-header"><i class="bi bi-receipt text-success me-1"></i> Orders</div>`;
            orders.forEach(o => {
                html += `<a href="javascript:void(0)" class="suggest-item" data-value="${escapeHtml(o.label)}">
                            <span class="d-flex align-items-center gap-2">
                                <span class="avatar-mini bg-success bg-opacity-10 text-success rounded-circle d-inline-flex align-items-center justify-content-center" style="width:22px; height:22px; font-size:0.7rem;">
                                    <i class="bi bi-receipt"></i>
                                </span>
                                <span class="fw-bold text-dark font-monospace" style="font-size:0.8rem;">#${escapeHtml(o.label)}</span>
                            </span>
                            ${o.subtext ? `<span class="text-success fw-bold" style="font-size:0.72rem;">${escapeHtml(o.subtext)}</span>` : ''}
                         </a>`;
            });
        }

        // Dishes Section
        if (dishes.length > 0) {
            if (customers.length > 0 || kitchens.length > 0 || orders.length > 0) html += `<div class="suggest-divider"></div>`;
            html += `<div class="suggest-header"><i class="bi bi-egg-fried text-danger me-1"></i> Dishes</div>`;
            dishes.forEach(d => {
                html += `<a href="javascript:void(0)" class="suggest-item" data-value="${escapeHtml(d.label)}">
                            <span class="d-flex align-items-center gap-2">
                                <span class="avatar-mini bg-danger bg-opacity-10 text-danger rounded-circle d-inline-flex align-items-center justify-content-center" style="width:22px; height:22px; font-size:0.7rem;">
                                    <i class="bi bi-egg-fried"></i>
                                </span>
                                <span class="fw-semibold text-dark">${escapeHtml(d.label)}</span>
                            </span>
                            <span class="badge bg-light text-muted border px-1" style="font-size:0.65rem;">Dish</span>
                         </a>`;
            });
        }

        // Empty state
        if (totalFound === 0 && query.length === 0) {
            hideSuggestions();
            return;
        }

        if (totalFound === 0 && query.length > 0) {
            html += `<div class="text-muted small text-center py-3">
                        <i class="bi bi-info-circle text-secondary me-1"></i> No matching kitchen, customer, or order found.
                     </div>`;
        }

        suggestionsMenu.innerHTML = html;
        showSuggestions();
        activeIndex = -1;

        // Attach mousedown handlers so click executes before input blur
        suggestionsMenu.querySelectorAll('.suggest-item').forEach(item => {
            item.addEventListener('mousedown', function (e) {
                e.preventDefault();
                const val = this.getAttribute('data-value');
                if (val) {
                    searchInput.value = val;
                    hideSuggestions();
                    if (filterForm) filterForm.submit();
                }
            });
        });
    }

    if (searchInput && suggestionsMenu) {
        // Trigger on focus
        searchInput.addEventListener('focus', function () {
            const q = this.value.trim();
            if (q.length === 0) {
                renderSuggestions(initialSuggestionsData, '');
            } else {
                fetchSuggestions(q);
            }
        });

        // Trigger on click
        searchInput.addEventListener('click', function () {
            if (suggestionsMenu.classList.contains('d-none')) {
                const q = this.value.trim();
                if (q.length === 0) {
                    renderSuggestions(initialSuggestionsData, '');
                } else {
                    fetchSuggestions(q);
                }
            }
        });

        // Typing with snappy debounce
        searchInput.addEventListener('input', function () {
            const q = this.value.trim();
            clearTimeout(debounceTimer);
            if (q.length === 0) {
                renderSuggestions(initialSuggestionsData, '');
            } else {
                debounceTimer = setTimeout(() => {
                    fetchSuggestions(q);
                }, 120);
            }
        });

        // Keyboard navigation (ArrowDown, ArrowUp, Enter, Escape)
        searchInput.addEventListener('keydown', function (e) {
            if (suggestionsMenu.classList.contains('d-none')) {
                if (e.key === 'ArrowDown') {
                    showSuggestions();
                }
                return;
            }

            const items = suggestionsMenu.querySelectorAll('.suggest-item');
            if (items.length === 0) return;

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                activeIndex = (activeIndex + 1) % items.length;
                updateItemHighlight(items);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                activeIndex = (activeIndex - 1 + items.length) % items.length;
                updateItemHighlight(items);
            } else if (e.key === 'Enter') {
                if (activeIndex >= 0 && items[activeIndex]) {
                    e.preventDefault();
                    items[activeIndex].dispatchEvent(new MouseEvent('mousedown'));
                }
            } else if (e.key === 'Escape') {
                hideSuggestions();
            }
        });

        function updateItemHighlight(items) {
            items.forEach((it, idx) => {
                if (idx === activeIndex) {
                    it.classList.add('active');
                    it.scrollIntoView({ block: 'nearest' });
                } else {
                    it.classList.remove('active');
                }
            });
        }

        // Hide when clicking outside
        document.addEventListener('click', function (e) {
            if (searchWrapper && !searchWrapper.contains(e.target)) {
                hideSuggestions();
            }
        });
    }
});
</script>

<style>
.kravyo-admin-suggestions {
    position: absolute;
    top: calc(100% + 4px);
    left: 0;
    right: 0;
    width: 100%;
    background: #ffffff !important;
    border-radius: 12px;
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.18), 0 4px 12px rgba(0, 0, 0, 0.08) !important;
    border: 1px solid #e2e8f0;
    max-height: 380px;
    overflow-y: auto;
    z-index: 999999 !important;
    padding: 6px;
}
.kravyo-admin-suggestions .suggest-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 7px 10px;
    border-radius: 8px;
    text-decoration: none;
    color: #1e293b;
    font-size: 0.85rem;
    cursor: pointer;
    transition: background-color 0.12s ease;
}
.kravyo-admin-suggestions .suggest-item:hover,
.kravyo-admin-suggestions .suggest-item.active {
    background-color: #ff6b35 !important;
    color: #ffffff !important;
}
.kravyo-admin-suggestions .suggest-item:hover .text-muted,
.kravyo-admin-suggestions .suggest-item.active .text-muted,
.kravyo-admin-suggestions .suggest-item:hover .text-dark,
.kravyo-admin-suggestions .suggest-item.active .text-dark,
.kravyo-admin-suggestions .suggest-item:hover i,
.kravyo-admin-suggestions .suggest-item.active i,
.kravyo-admin-suggestions .suggest-item:hover .avatar-mini,
.kravyo-admin-suggestions .suggest-item.active .avatar-mini {
    color: #ffffff !important;
    background-color: rgba(255,255,255,0.2) !important;
}
.kravyo-admin-suggestions .suggest-item.search-term-item {
    background-color: #f0f7ff;
}
.kravyo-admin-suggestions .suggest-item.search-term-item:hover,
.kravyo-admin-suggestions .suggest-item.search-term-item.active {
    background-color: #ff6b35 !important;
}
.kravyo-admin-suggestions .suggest-item.search-term-item:hover span,
.kravyo-admin-suggestions .suggest-item.search-term-item.active span {
    color: #ffffff !important;
}
.kravyo-admin-suggestions .suggest-header {
    font-size: 0.68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #64748b;
    padding: 6px 10px 3px 10px;
    margin: 0;
}
.kravyo-admin-suggestions .suggest-divider {
    height: 1px;
    background-color: #f1f5f9;
    margin: 4px 6px;
}
.hover-primary:hover {
    color: #e65100 !important;
}
.hover-shadow:hover {
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    border-color: #dee2e6 !important;
}
.shadow-xs {
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
}
</style>
