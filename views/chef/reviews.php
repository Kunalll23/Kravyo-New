<?php
/**
 * Kravyo - Chef Reviews & Food Quality Insights
 * Views: views/chef/reviews.php
 */

$dist = $metrics['distribution'] ?? [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
$totalReviews = $metrics['total'] ?? 0;
$avgRating = $metrics['avg_rating'] ?? 0.0;
$positivePct = $metrics['positive_pct'] ?? 0;
$criticalCount = $metrics['critical_count'] ?? 0;

// Helper to build URL with query params
function chefReviewFilterUrl(array $overrides = []): string {
    $params = $_GET;
    foreach ($overrides as $k => $v) {
        if ($v === null || $v === '') {
            unset($params[$k]);
        } else {
            $params[$k] = $v;
        }
    }
    $query = http_build_query($params);
    return url('/chef/reviews' . ($query ? '?' . $query : ''));
}
?>

<div class="container-fluid px-3 px-md-4 py-4">
    <!-- Top Header & Breadcrumb -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-warning bg-opacity-10 text-dark fw-bold px-2 py-1 rounded-pill">
                    <i class="bi bi-star-fill text-warning me-1"></i> Food Quality & Feedback
                </span>
                <span class="text-muted small">Kitchen: <strong><?= sanitize($kitchen['kitchen_name']) ?></strong></span>
            </div>
            <h2 class="fw-bold mb-1">Customer Reviews & Ratings</h2>
            <p class="text-muted small mb-0">Track real-time customer dining satisfaction, dish performance, and review feedback.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="<?= url('/chef/dashboard') ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="bi bi-speedometer2 me-1"></i> Dashboard
            </a>
            <a href="<?= url('/chef/menu') ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                <i class="bi bi-egg-fried me-1"></i> Manage Menu
            </a>
        </div>
    </div>

    <!-- Metric Summary Cards -->
    <div class="row g-3 mb-4">
        <!-- Average Rating -->
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-white">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold text-uppercase">Overall Score</span>
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
                    <span class="text-muted ms-1 small">(<?= $totalReviews ?> total)</span>
                </div>
            </div>
        </div>

        <!-- Satisfaction Rate -->
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-white">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold text-uppercase">Satisfaction</span>
                    <span class="badge bg-success bg-opacity-10 text-success px-2 py-1 rounded-pill"><i class="bi bi-heart-fill"></i> Positive</span>
                </div>
                <div class="d-flex align-items-baseline gap-2">
                    <span class="display-6 fw-bold text-success"><?= $positivePct ?>%</span>
                </div>
                <div class="text-muted small mt-1">
                    <?= $metrics['positive_count'] ?> of <?= $totalReviews ?> rated 4★ or 5★
                </div>
            </div>
        </div>

        <!-- Total Reviews -->
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-white">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold text-uppercase">Total Feedbacks</span>
                    <span class="badge bg-info bg-opacity-10 text-info px-2 py-1 rounded-pill"><i class="bi bi-chat-dots-fill"></i> Verified</span>
                </div>
                <div class="d-flex align-items-baseline gap-2">
                    <span class="display-6 fw-bold text-dark"><?= count($reviews) ?></span>
                    <?php if (count($reviews) !== $totalReviews): ?>
                        <span class="text-muted fs-6">shown (filtered)</span>
                    <?php endif; ?>
                </div>
                <div class="text-muted small mt-1">
                    Verified meal order experiences
                </div>
            </div>
        </div>

        <!-- Critical Attention -->
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-white">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold text-uppercase">Needs Attention</span>
                    <span class="badge <?= $criticalCount > 0 ? 'bg-danger bg-opacity-10 text-danger' : 'bg-success bg-opacity-10 text-success' ?> px-2 py-1 rounded-pill">
                        <i class="bi <?= $criticalCount > 0 ? 'bi-exclamation-triangle-fill' : 'bi-shield-check' ?>"></i>
                        <?= $criticalCount > 0 ? 'Attention' : 'Clean' ?>
                    </span>
                </div>
                <div class="d-flex align-items-baseline gap-2">
                    <span class="display-6 fw-bold <?= $criticalCount > 0 ? 'text-danger' : 'text-success' ?>"><?= $criticalCount ?></span>
                    <span class="text-muted fs-6">critical</span>
                </div>
                <div class="text-muted small mt-1">
                    <?= $criticalCount > 0 ? 'Reviews with 1 or 2 stars' : 'No low rating alerts!' ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Rating Distribution & Top Dishes Row -->
    <div class="row g-3 mb-4">
        <!-- Star Distribution -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 p-3 p-md-4 h-100 bg-white">
                <h6 class="fw-bold mb-3 d-flex align-items-center justify-content-between">
                    <span><i class="bi bi-bar-chart-fill text-warning me-2"></i> Rating Distribution</span>
                    <?php if (!empty($filters['rating'])): ?>
                        <a href="<?= chefReviewFilterUrl(['rating' => null]) ?>" class="badge bg-light text-secondary text-decoration-none border">Clear Rating Filter</a>
                    <?php endif; ?>
                </h6>

                <div class="d-flex flex-column gap-2">
                    <?php for ($star = 5; $star >= 1; $star--): ?>
                        <?php 
                            $cnt = $dist[$star] ?? 0;
                            $pct = $totalReviews > 0 ? round(($cnt / $totalReviews) * 100) : 0;
                            $isFiltered = ($filters['rating'] == (string)$star);
                        ?>
                        <a href="<?= chefReviewFilterUrl(['rating' => $isFiltered ? null : $star]) ?>" 
                           class="text-decoration-none d-flex align-items-center gap-2 p-1 rounded-2 <?= $isFiltered ? 'bg-warning bg-opacity-10 fw-bold' : 'hover-bg-light' ?>" 
                           title="Filter by <?= $star ?> stars">
                            <span class="text-nowrap small text-dark fw-semibold" style="width: 48px;">
                                <?= $star ?> <i class="bi bi-star-fill text-warning" style="font-size:0.75rem;"></i>
                            </span>
                            <div class="progress flex-grow-1" style="height: 8px; border-radius: 6px; background-color: #f1f3f5;">
                                <div class="progress-bar <?= $star >= 4 ? 'bg-success' : ($star === 3 ? 'bg-warning' : 'bg-danger') ?>" 
                                     role="progressbar" 
                                     style="width: <?= $pct ?>%; border-radius: 6px;"></div>
                            </div>
                            <span class="text-muted small text-end" style="width: 60px;">
                                <?= $cnt ?> <span class="opacity-50">(<?= $pct ?>%)</span>
                            </span>
                        </a>
                    <?php endfor; ?>
                </div>
            </div>
        </div>

        <!-- Dish Ratings Breakdown -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 p-3 p-md-4 h-100 bg-white">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="fw-bold mb-0">
                        <i class="bi bi-egg-fried text-primary me-2"></i> Performance By Dish
                    </h6>
                    <?php if (!empty($filters['dish_id'])): ?>
                        <a href="<?= chefReviewFilterUrl(['dish_id' => null]) ?>" class="badge bg-light text-secondary text-decoration-none border">Show All Dishes</a>
                    <?php endif; ?>
                </div>

                <?php if (empty($dishSummaries)): ?>
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-cup-hot display-6 opacity-25 d-block mb-2"></i>
                        <p class="small mb-0">No dish-specific review data recorded yet.</p>
                    </div>
                <?php else: ?>
                    <div class="d-flex flex-wrap gap-2" style="max-height: 175px; overflow-y: auto;">
                        <?php foreach ($dishSummaries as $d): ?>
                            <?php $isSelected = ($filters['dish_id'] == $d['id']); ?>
                            <a href="<?= chefReviewFilterUrl(['dish_id' => $isSelected ? null : $d['id']]) ?>" 
                               class="badge p-2 text-decoration-none border d-flex align-items-center gap-2 <?= $isSelected ? 'bg-primary text-white border-primary' : 'bg-light text-dark border-light-subtle' ?>"
                               style="border-radius: 10px; font-weight: normal; cursor: pointer;">
                                <?php if ($d['is_veg']): ?>
                                    <span class="badge border border-success text-success p-0 px-1 bg-white" style="font-size:0.6rem;">●</span>
                                <?php else: ?>
                                    <span class="badge border border-danger text-danger p-0 px-1 bg-white" style="font-size:0.6rem;">▲</span>
                                <?php endif; ?>
                                <span class="fw-semibold text-truncate" style="max-width: 140px;"><?= sanitize($d['item_name']) ?></span>
                                <span class="<?= $isSelected ? 'text-white' : 'text-warning' ?> fw-bold small">
                                    <i class="bi bi-star-fill"></i> <?= $d['avg_rating'] ?>
                                </span>
                                <span class="badge bg-secondary bg-opacity-25 <?= $isSelected ? 'text-white' : 'text-muted' ?> rounded-pill" style="font-size:0.65rem;">
                                    <?= $d['review_count'] ?>
                                </span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white p-3">
        <form action="<?= url('/chef/reviews') ?>" method="GET" id="reviewsFilterForm" class="row g-2 align-items-center">
            
            <!-- Search Input -->
            <div class="col-12 col-md-4 col-xl-3">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control form-control-sm border-start-0 bg-light" 
                           placeholder="Search customer, note, #order..." 
                           value="<?= sanitize($filters['search'] ?? '') ?>">
                </div>
            </div>

            <!-- Dish Filter -->
            <div class="col-6 col-md-3 col-xl-2">
                <select name="dish_id" class="form-select form-select-sm bg-light" onchange="this.form.submit()">
                    <option value="">🍽️ All Dishes</option>
                    <?php foreach ($menuItems as $item): ?>
                        <option value="<?= $item['id'] ?>" <?= ($filters['dish_id'] == $item['id']) ? 'selected' : '' ?>>
                            <?= sanitize($item['item_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Rating Filter -->
            <div class="col-6 col-md-2 col-xl-2">
                <select name="rating" class="form-select form-select-sm bg-light" onchange="this.form.submit()">
                    <option value="">⭐ All Ratings</option>
                    <option value="5" <?= ($filters['rating'] === '5') ? 'selected' : '' ?>>5 Stars (Excellent)</option>
                    <option value="4" <?= ($filters['rating'] === '4') ? 'selected' : '' ?>>4 Stars (Good)</option>
                    <option value="3" <?= ($filters['rating'] === '3') ? 'selected' : '' ?>>3 Stars (Average)</option>
                    <option value="2" <?= ($filters['rating'] === '2') ? 'selected' : '' ?>>2 Stars (Poor)</option>
                    <option value="1" <?= ($filters['rating'] === '1') ? 'selected' : '' ?>>1 Star (Critical)</option>
                    <option value="positive" <?= ($filters['rating'] === 'positive') ? 'selected' : '' ?>>Positive (4-5★)</option>
                    <option value="critical" <?= ($filters['rating'] === 'critical') ? 'selected' : '' ?>>Critical (1-2★)</option>
                </select>
            </div>

            <!-- Time Filter -->
            <div class="col-6 col-md-3 col-xl-2">
                <select name="time_range" id="timeRangeSelect" class="form-select form-select-sm bg-light" onchange="handleTimeRangeChange(this)">
                    <option value="all" <?= ($filters['time_range'] === 'all') ? 'selected' : '' ?>>📅 All Time</option>
                    <option value="today" <?= ($filters['time_range'] === 'today') ? 'selected' : '' ?>>Today</option>
                    <option value="7days" <?= ($filters['time_range'] === '7days') ? 'selected' : '' ?>>Last 7 Days</option>
                    <option value="30days" <?= ($filters['time_range'] === '30days') ? 'selected' : '' ?>>Last 30 Days</option>
                    <option value="this_month" <?= ($filters['time_range'] === 'this_month') ? 'selected' : '' ?>>This Month</option>
                    <option value="custom" <?= ($filters['time_range'] === 'custom') ? 'selected' : '' ?>>Custom Range...</option>
                </select>
            </div>

            <!-- Sort By -->
            <div class="col-6 col-md-3 col-xl-2">
                <select name="sort" class="form-select form-select-sm bg-light" onchange="this.form.submit()">
                    <option value="recent" <?= ($filters['sort'] === 'recent') ? 'selected' : '' ?>>🕒 Most Recent</option>
                    <option value="oldest" <?= ($filters['sort'] === 'oldest') ? 'selected' : '' ?>>⏳ Oldest First</option>
                    <option value="highest" <?= ($filters['sort'] === 'highest') ? 'selected' : '' ?>>⭐ Highest Rating</option>
                    <option value="lowest" <?= ($filters['sort'] === 'lowest') ? 'selected' : '' ?>>⚠️ Lowest Rating</option>
                </select>
            </div>

            <!-- Filter Buttons -->
            <div class="col-12 col-md-auto d-flex align-items-center gap-2 ms-auto">
                <button type="submit" class="btn btn-primary btn-sm px-3 rounded-pill">
                    <i class="bi bi-funnel-fill me-1"></i> Filter
                </button>
                <?php 
                    $hasActiveFilter = !empty($filters['dish_id']) || !empty($filters['rating']) || 
                                       (!empty($filters['time_range']) && $filters['time_range'] !== 'all') || 
                                       !empty($filters['search']) || ($filters['sort'] !== 'recent');
                ?>
                <?php if ($hasActiveFilter): ?>
                    <a href="<?= url('/chef/reviews') ?>" class="btn btn-outline-secondary btn-sm px-3 rounded-pill" title="Reset All Filters">
                        <i class="bi bi-x-circle me-1"></i> Reset
                    </a>
                <?php endif; ?>
            </div>

            <!-- Custom Date Inputs (collapsible if custom selected) -->
            <div class="col-12 mt-2 <?= ($filters['time_range'] === 'custom') ? '' : 'd-none' ?>" id="customDateRow">
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

    <!-- Reviews List Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="fw-bold mb-0 text-dark">
            <span>Customer Feedbacks</span>
            <span class="badge bg-secondary bg-opacity-10 text-dark rounded-pill ms-2"><?= count($reviews) ?> Found</span>
        </h6>
        <?php if (!empty($filters['dish_id'])): ?>
            <?php 
                $selectedDishName = 'Selected Dish';
                foreach ($menuItems as $m) {
                    if ($m['id'] == $filters['dish_id']) { $selectedDishName = $m['item_name']; break; }
                }
            ?>
            <span class="badge bg-primary text-white rounded-pill px-3 py-1 small">
                Filtered by: <?= sanitize($selectedDishName) ?>
            </span>
        <?php endif; ?>
    </div>

    <!-- Reviews Feed Cards -->
    <?php if (empty($reviews)): ?>
        <div class="card border-0 shadow-sm rounded-4 text-center py-5 bg-white">
            <div class="py-4">
                <i class="bi bi-chat-square-quote display-3 text-muted opacity-25 d-block mb-3"></i>
                <h5 class="fw-bold text-dark">No Reviews Found</h5>
                <p class="text-muted small max-w-500 mx-auto mb-3">
                    No customer reviews match your active filter criteria. Try adjusting the date range, rating, or dish filter.
                </p>
                <a href="<?= url('/chef/reviews') ?>" class="btn btn-outline-primary btn-sm rounded-pill px-4">
                    Clear All Filters
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($reviews as $rev): ?>
                <?php
                    // Parse ordered items
                    // Format in query: mi.id::mi.item_name::oi.quantity::mi.image
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
                                    'image'    => $parts[3] ?? '',
                                ];
                            }
                        }
                    }

                    // Rating styling
                    $rScore = (int) $rev['rating'];
                    $badgeClass = 'bg-success';
                    if ($rScore === 4) $badgeClass = 'bg-primary';
                    elseif ($rScore === 3) $badgeClass = 'bg-warning text-dark';
                    elseif ($rScore <= 2) $badgeClass = 'bg-danger';

                    // Avatar initials
                    $initials = strtoupper(substr($rev['customer_name'] ?? 'C', 0, 1));
                ?>
                <div class="col-12">
                    <div class="card border-0 shadow-sm rounded-4 p-3 p-md-4 bg-white transition-all">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3">
                            
                            <!-- Left: Customer info & Star score -->
                            <div class="d-flex align-items-start gap-3">
                                <!-- Avatar -->
                                <div class="rounded-circle bg-warning bg-opacity-25 text-warning fw-bold d-flex align-items-center justify-content-center flex-shrink-0" 
                                     style="width: 46px; height: 46px; font-size: 1.1rem; color: #854d0e !important;">
                                    <?= $initials ?>
                                </div>
                                
                                <div>
                                    <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                                        <span class="fw-bold text-dark fs-6"><?= sanitize($rev['customer_name']) ?></span>
                                        <span class="badge <?= $badgeClass ?> px-2 py-1 rounded-pill fw-bold" style="font-size: 0.75rem;">
                                            <?= $rScore ?> ★
                                        </span>
                                        <span class="text-warning small">
                                            <?php for ($s = 1; $s <= 5; $s++): ?>
                                                <i class="bi bi-star<?= $s <= $rScore ? '-fill' : '' ?>"></i>
                                            <?php endfor; ?>
                                        </span>
                                    </div>
                                    <div class="text-muted small d-flex align-items-center gap-2 flex-wrap">
                                        <span><i class="bi bi-calendar3 me-1"></i><?= date('d M Y, h:i A', strtotime($rev['created_at'])) ?></span>
                                        <span>•</span>
                                        <a href="<?= url('/chef/orders?search=' . urlencode($rev['order_number'])) . '#order-' . $rev['order_id'] ?>" 
                                           class="badge bg-light text-dark border px-2 py-1 rounded-pill text-decoration-none hover-shadow"
                                           title="View this order in incoming orders">
                                            <i class="bi bi-receipt me-1 text-primary"></i>Order #<?= sanitize($rev['order_number']) ?>
                                            <i class="bi bi-box-arrow-up-right ms-1 text-muted" style="font-size:0.65rem;"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Right: Order Total & Date Relative -->
                            <div class="text-md-end text-muted small flex-shrink-0">
                                <span class="badge bg-light text-secondary border px-2 py-1 rounded-pill">
                                    Total: ₹<?= number_format($rev['order_total'], 2) ?>
                                </span>
                            </div>
                        </div>

                        <!-- Review Text Quote -->
                        <div class="mt-3 p-3 bg-light rounded-3">
                            <?php if (!empty($rev['review_text'])): ?>
                                <p class="mb-0 text-dark" style="font-size: 0.95rem; line-height: 1.5;">
                                    <i class="bi bi-quote fs-5 text-warning opacity-75 me-1"></i>
                                    <?= nl2br(sanitize($rev['review_text'])) ?>
                                </p>
                            <?php else: ?>
                                <span class="text-muted fst-italic small">
                                    <i class="bi bi-star-fill text-warning me-1"></i> Customer left a <?= $rScore ?>-star rating without written notes.
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- Dishes in This Order -->
                        <?php if (!empty($orderedItems)): ?>
                            <div class="mt-3 pt-2 border-top d-flex align-items-center flex-wrap gap-2">
                                <span class="text-muted small fw-semibold me-1"><i class="bi bi-basket2 me-1"></i> Dishes In Order:</span>
                                <?php foreach ($orderedItems as $it): ?>
                                    <a href="<?= chefReviewFilterUrl(['dish_id' => $it['id']]) ?>" 
                                       class="badge bg-white text-dark border text-decoration-none d-flex align-items-center gap-1 py-1 px-2 rounded-pill shadow-xs hover-shadow"
                                       title="Filter reviews for <?= sanitize($it['name']) ?>">
                                        <span class="fw-semibold"><?= sanitize($it['name']) ?></span>
                                        <span class="text-muted" style="font-size:0.75rem;">×<?= (int)$it['quantity'] ?></span>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
function handleTimeRangeChange(selectElem) {
    const customRow = document.getElementById('customDateRow');
    if (selectElem.value === 'custom') {
        customRow.classList.remove('d-none');
    } else {
        customRow.classList.add('d-none');
        selectElem.form.submit();
    }
}
</script>

<style>
.hover-bg-light:hover {
    background-color: #f8f9fa;
}
.shadow-xs {
    box-shadow: 0 1px 2px rgba(0,0,0,0.05);
}
.hover-shadow:hover {
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    border-color: #dee2e6 !important;
}
.transition-all {
    transition: all 0.2s ease-in-out;
}
</style>
