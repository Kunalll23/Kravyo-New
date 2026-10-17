<?php /* Admin Module 3.9: Zero Food Waste — Comprehensive Management, Moderation & Reporting */ ?>
<div class="container py-4">

    <!-- Page Header & Action Controls -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-success bg-opacity-10 text-success px-3 py-1 rounded-pill fw-semibold" style="font-size:0.75rem;">
                    <i class="bi bi-recycle me-1"></i> Sustainability & Food Rescue Engine
                </span>
            </div>
            <h2 class="fw-bold mb-1 text-dark">Zero Food Waste Management</h2>
            <p class="text-muted small mb-0">Monitor end-of-day food rescue listings, track customer savings, and moderate deals across kitchens.</p>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <!-- Sweep Expired Button -->
            <form action="<?= url('/admin/zero-waste/sweep-expired') ?>" method="POST" class="d-inline">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-outline-warning btn-sm rounded-pill px-3 shadow-xs" title="Sweep and mark all past-expiry deals as expired">
                    <i class="bi bi-arrow-repeat me-1"></i> Sweep Expired
                </button>
            </form>

            <!-- Export CSV Report -->
            <a href="<?= url('/admin/zero-waste/export?' . http_build_query($_GET)) ?>" class="btn btn-outline-success btn-sm rounded-pill px-3 shadow-xs" title="Export currently filtered deals to CSV">
                <i class="bi bi-file-earmark-arrow-down me-1"></i> Export CSV
            </a>

            <!-- Return to Dashboard -->
            <a href="<?= url('/admin/dashboard') ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-xs">
                <i class="bi bi-arrow-left me-1"></i> Dashboard
            </a>
        </div>
    </div>

    <!-- ── Key Impact & Operational KPI Cards ────────────────────────────────── -->
    <div class="row g-3 mb-4">
        <!-- Total Listings -->
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white p-3 text-center">
                <div class="text-muted small fw-semibold text-uppercase mb-1">Total Listings</div>
                <div class="fs-3 fw-bold text-dark"><?= (int)($stats['total_listings'] ?? 0) ?></div>
                <div class="text-muted" style="font-size:0.75rem;">Platform created</div>
            </div>
        </div>

        <!-- Active Deals -->
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white p-3 text-center">
                <div class="d-flex align-items-center justify-content-center gap-1 text-muted small fw-semibold text-uppercase mb-1">
                    <span>Active Now</span>
                    <?php if (($stats['active_listings'] ?? 0) > 0): ?>
                        <span class="badge bg-success rounded-circle p-1" style="width:7px; height:7px;"></span>
                    <?php endif; ?>
                </div>
                <div class="fs-3 fw-bold text-success"><?= (int)($stats['active_listings'] ?? 0) ?></div>
                <div class="text-success small fw-semibold" style="font-size:0.75rem;">
                    <?= (int)($stats['total_active_qty'] ?? 0) ?> portions live
                </div>
            </div>
        </div>

        <!-- Sold Out -->
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white p-3 text-center">
                <div class="text-muted small fw-semibold text-uppercase mb-1">Sold Out</div>
                <div class="fs-3 fw-bold text-warning"><?= (int)($stats['sold_out_listings'] ?? 0) ?></div>
                <div class="text-muted" style="font-size:0.75rem;">100% Claimed</div>
            </div>
        </div>

        <!-- Expired Deals -->
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white p-3 text-center">
                <div class="text-muted small fw-semibold text-uppercase mb-1">Expired</div>
                <div class="fs-3 fw-bold text-secondary"><?= (int)($stats['expired_listings'] ?? 0) ?></div>
                <div class="text-muted" style="font-size:0.75rem;">Past time window</div>
            </div>
        </div>

        <!-- Meals Rescued in Orders -->
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white p-3 text-center" style="border-left: 3px solid #10b981 !important;">
                <div class="text-muted small fw-semibold text-uppercase mb-1">Meals Rescued</div>
                <div class="fs-3 fw-bold text-success"><?= (int)($stats['meals_saved_qty'] ?? 0) ?></div>
                <div class="text-muted" style="font-size:0.75rem;">In <?= (int)($stats['zero_waste_orders'] ?? 0) ?> customer orders</div>
            </div>
        </div>

        <!-- Rescued Value & Potential Savings -->
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white p-3 text-center" style="border-left: 3px solid #ff6b35 !important;">
                <div class="text-muted small fw-semibold text-uppercase mb-1">Savings Offered</div>
                <div class="fs-3 fw-bold text-danger">₹<?= number_format((float)($stats['potential_savings'] ?? 0), 0) ?></div>
                <div class="text-success small fw-semibold" style="font-size:0.75rem;">₹<?= number_format((float)($stats['realized_revenue'] ?? 0), 0) ?> earned</div>
            </div>
        </div>
    </div>

    <!-- ── Filter & Search Toolbar ───────────────────────────────────────────── -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white p-3">
        <form action="<?= url('/admin/zero-waste') ?>" method="GET" id="zeroWasteFilterForm" class="row g-2 align-items-center">
            
            <!-- Search Keyword -->
            <div class="col-12 col-md-4 col-xl-3">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control form-control-sm border-start-0 bg-light" 
                           placeholder="Search dish, kitchen, city, #ID..." 
                           value="<?= sanitize($filters['search'] ?? '') ?>">
                    <?php if (!empty($filters['search'])): ?>
                        <a href="<?= url('/admin/zero-waste?' . http_build_query(array_merge($_GET, ['search' => '']))) ?>" class="input-group-text bg-light border-start-0 text-muted text-decoration-none">
                            <i class="bi bi-x-circle-fill"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Status Filter -->
            <div class="col-6 col-md-2 col-xl-2">
                <select name="status" class="form-select form-select-sm bg-light" onchange="this.form.submit()">
                    <option value="all" <?= ($filters['status'] === 'all' || empty($filters['status'])) ? 'selected' : '' ?>>🏷️ All Statuses</option>
                    <option value="active" <?= ($filters['status'] === 'active') ? 'selected' : '' ?>>🟢 Active Deals</option>
                    <option value="sold_out" <?= ($filters['status'] === 'sold_out') ? 'selected' : '' ?>>🟡 Sold Out</option>
                    <option value="expired" <?= ($filters['status'] === 'expired') ? 'selected' : '' ?>>⚪ Expired</option>
                </select>
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
                    <option value="all">📍 All Cities</option>
                    <?php foreach ($cities as $c): ?>
                        <option value="<?= sanitize($c) ?>" <?= ($filters['city'] === $c) ? 'selected' : '' ?>>
                            <?= sanitize($c) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Time Filter -->
            <div class="col-6 col-md-2 col-xl-2">
                <select name="time_range" class="form-select form-select-sm bg-light" onchange="this.form.submit()">
                    <option value="all" <?= ($filters['time_range'] === 'all') ? 'selected' : '' ?>>📅 All Time</option>
                    <option value="today" <?= ($filters['time_range'] === 'today') ? 'selected' : '' ?>>Today</option>
                    <option value="7days" <?= ($filters['time_range'] === '7days') ? 'selected' : '' ?>>Last 7 Days</option>
                    <option value="30days" <?= ($filters['time_range'] === '30days') ? 'selected' : '' ?>>Last 30 Days</option>
                </select>
            </div>

            <!-- Action Buttons -->
            <div class="col-12 col-md-auto ms-auto d-flex align-items-center gap-2">
                <button type="submit" class="btn btn-primary btn-sm px-3 rounded-pill fw-semibold shadow-xs">
                    <i class="bi bi-funnel-fill me-1"></i> Filter
                </button>
                <?php if (!empty($filters['search']) || ($filters['status'] !== 'all' && !empty($filters['status'])) || !empty($filters['kitchen_id']) || (!empty($filters['city']) && $filters['city'] !== 'all') || ($filters['time_range'] !== 'all' && !empty($filters['time_range']))): ?>
                    <a href="<?= url('/admin/zero-waste') ?>" class="btn btn-outline-secondary btn-sm px-3 rounded-pill" title="Reset all filters">
                        <i class="bi bi-x-circle me-1"></i> Reset
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- ── Listings Table Card ──────────────────────────────────────────────── -->
    <div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
        <div class="card-header bg-transparent border-bottom py-3 d-flex flex-wrap gap-2 align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-list-ul me-2 text-success"></i>Zero Food Waste Listings</h5>
                <span class="badge bg-light text-secondary border rounded-pill px-2 py-1 small"><?= count($listings) ?> <?= count($listings) === 1 ? 'deal' : 'deals' ?></span>
            </div>
            <div class="small text-muted">
                Showing <?= count($listings) ?> of <?= (int)($stats['total_listings'] ?? 0) ?> platform deals
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                    <thead class="table-light text-muted small text-uppercase" style="letter-spacing: 0.5px;">
                        <tr>
                            <th class="ps-3" style="width: 60px;">ID</th>
                            <th>Food Item & Dietary</th>
                            <th>Kitchen & Location</th>
                            <th>Pricing & Discount</th>
                            <th>Stock & Rescued</th>
                            <th>Expiry Window</th>
                            <th>Status</th>
                            <th class="text-end pe-3" style="width: 120px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($listings)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-recycle display-4 d-block mb-3 text-success opacity-50"></i>
                                    <h6 class="fw-bold text-dark mb-1">No Zero Waste Listings Found</h6>
                                    <p class="small text-muted mb-3">No deals match the specified filters or search term.</p>
                                    <a href="<?= url('/admin/zero-waste') ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                        Clear All Filters
                                    </a>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($listings as $item): ?>
                                <?php
                                $isExpired = ($item['status'] === 'expired' || strtotime($item['expiry_time']) <= time());
                                $isSoldOut = ($item['status'] === 'sold_out');
                                $isActive  = ($item['status'] === 'active' && !$isExpired);
                                $expiryTs  = strtotime($item['expiry_time']);
                                ?>
                                <tr>
                                    <!-- ID -->
                                    <td class="ps-3 font-monospace text-muted small fw-bold">#<?= $item['id'] ?></td>

                                    <!-- Food Item with Thumbnail & Veg Badge -->
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <img src="<?= !empty($item['dish_image']) ? UPLOAD_URL . '/dishes/' . $item['dish_image'] : asset('images/default-dish.jpg') ?>" 
                                                 alt="<?= sanitize($item['item_name']) ?>" 
                                                 class="rounded-3 shadow-xs border flex-shrink-0" 
                                                 style="width: 44px; height: 44px; object-fit: cover;"
                                                 onerror="this.src='https://placehold.co/44x44/f8f9fa/6c757d?text=Food'">
                                            <div>
                                                <div class="d-flex align-items-center gap-1">
                                                    <?php if (!empty($item['is_veg'])): ?>
                                                        <span title="Pure Vegetarian" class="text-success"><i class="bi bi-circle-fill" style="font-size: 0.55rem;"></i></span>
                                                    <?php else: ?>
                                                        <span title="Non-Vegetarian" class="text-danger"><i class="bi bi-triangle-fill" style="font-size: 0.55rem;"></i></span>
                                                    <?php endif; ?>
                                                    <span class="fw-bold text-dark"><?= sanitize($item['item_name']) ?></span>
                                                </div>
                                                <div class="text-muted" style="font-size: 0.72rem;">
                                                    Listed: <?= date('d M Y, h:i A', strtotime($item['created_at'])) ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Kitchen & City -->
                                    <td>
                                        <div class="fw-semibold text-dark">
                                            <a href="<?= url('/admin/chefs') ?>" class="text-dark text-decoration-none hover-primary">
                                                <?= sanitize($item['kitchen_name']) ?>
                                            </a>
                                        </div>
                                        <span class="badge bg-light text-secondary border px-2 py-0" style="font-size: 0.7rem;">
                                            <i class="bi bi-geo-alt me-1 text-danger"></i><?= sanitize($item['city']) ?>
                                        </span>
                                    </td>

                                    <!-- Pricing & Discount -->
                                    <td>
                                        <div class="d-flex align-items-baseline gap-1">
                                            <span class="fw-bold text-success fs-6">₹<?= number_format((float)$item['discounted_price'], 2) ?></span>
                                            <span class="text-muted text-decoration-line-through small" style="font-size: 0.75rem;">₹<?= number_format((float)$item['original_price'], 2) ?></span>
                                        </div>
                                        <span class="badge bg-danger bg-opacity-10 text-danger fw-bold px-2 py-0" style="font-size: 0.68rem;">
                                            <?= (int)$item['discount_pct'] ?>% OFF
                                        </span>
                                    </td>

                                    <!-- Quantity Stock & Orders Rescued -->
                                    <td>
                                        <div class="d-flex align-items-center gap-1">
                                            <span class="fw-bold text-dark"><?= (int)$item['quantity'] ?> left</span>
                                        </div>
                                        <?php if (($item['claimed_qty'] ?? 0) > 0): ?>
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle px-1" style="font-size: 0.68rem;" title="Portions claimed by customers">
                                                <i class="bi bi-bag-check-fill me-1"></i><?= (int)$item['claimed_qty'] ?> rescued
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted" style="font-size: 0.72rem;">0 ordered</span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Expiry Window -->
                                    <td>
                                        <?php if ($isActive): ?>
                                            <?php
                                            $minLeft = (int)($item['minutes_remaining'] ?? 0);
                                            $timeLeftStr = $minLeft > 60 ? floor($minLeft / 60) . 'h ' . ($minLeft % 60) . 'm' : $minLeft . 'm';
                                            ?>
                                            <span class="badge bg-warning bg-opacity-10 text-dark border border-warning-subtle py-1 px-2 d-inline-block">
                                                <i class="bi bi-hourglass-split text-warning me-1"></i><?= $timeLeftStr ?> remaining
                                            </span>
                                        <?php else: ?>
                                            <div class="text-secondary small fw-semibold">
                                                Expired <?= date('d M, H:i', $expiryTs) ?>
                                            </div>
                                        <?php endif; ?>
                                        <div class="text-muted" style="font-size: 0.7rem;">
                                            Valid till: <?= date('d M Y, h:i A', $expiryTs) ?>
                                        </div>
                                    </td>

                                    <!-- Status -->
                                    <td>
                                        <?php if ($isActive): ?>
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle rounded-pill px-2 py-1">
                                                <i class="bi bi-check-circle-fill me-1"></i>Active
                                            </span>
                                        <?php elseif ($isSoldOut): ?>
                                            <span class="badge bg-warning bg-opacity-10 text-warning border border-warning-subtle rounded-pill px-2 py-1">
                                                <i class="bi bi-exclamation-circle-fill me-1"></i>Sold Out
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary border rounded-pill px-2 py-1">
                                                <i class="bi bi-clock-history me-1"></i>Expired
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Action Buttons -->
                                    <td class="text-end pe-3">
                                        <div class="d-inline-flex align-items-center gap-1">
                                            <!-- Force Expire (Active only) -->
                                            <?php if ($isActive): ?>
                                                <form action="<?= url('/admin/zero-waste/expire/' . $item['id']) ?>" method="POST" class="d-inline" onsubmit="return confirm('Immediately deactivate and expire deal #<?= $item['id'] ?>?');">
                                                    <?= csrf_field() ?>
                                                    <button type="submit" class="btn btn-sm btn-outline-warning rounded-circle p-0 d-inline-flex align-items-center justify-content-center shadow-xs" style="width: 28px; height: 28px;" title="Force Expire Deal">
                                                        <i class="bi bi-stop-circle" style="font-size: 0.8rem;"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>

                                            <!-- Delete Listing -->
                                            <form action="<?= url('/admin/zero-waste/delete/' . $item['id']) ?>" method="POST" class="d-inline" onsubmit="return confirm('Permanently delete deal #<?= $item['id'] ?> (<?= sanitize($item['item_name']) ?>)? This cannot be undone.');">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle p-0 d-inline-flex align-items-center justify-content-center shadow-xs" style="width: 28px; height: 28px;" title="Delete Deal">
                                                    <i class="bi bi-trash" style="font-size: 0.8rem;"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ── Per-Kitchen Report ───────────────────────────────────────────────── -->
    <?php if (!empty($byKitchen)): ?>
    <div class="card border-0 shadow-sm rounded-4 bg-white">
        <div class="card-header bg-transparent border-bottom py-3 d-flex align-items-center justify-content-between">
            <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-shop me-2 text-warning"></i>Zero Waste Summary — By Kitchen</h5>
            <span class="badge bg-light text-muted border px-2 py-1 small"><?= count($byKitchen) ?> Kitchens participating</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                    <thead class="table-light text-muted small text-uppercase" style="letter-spacing: 0.5px;">
                        <tr>
                            <th class="ps-4">Kitchen Name</th>
                            <th>City</th>
                            <th>Total Deals Listed</th>
                            <th>Currently Active</th>
                            <th>Sold Out Deals</th>
                            <th>Active Stock Available</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($byKitchen as $row): ?>
                            <tr>
                                <td class="ps-4 fw-bold text-dark"><?= sanitize($row['kitchen_name']) ?></td>
                                <td>
                                    <span class="badge bg-light text-secondary border px-2 py-1 small">
                                        <i class="bi bi-geo-alt me-1 text-danger"></i><?= sanitize($row['city']) ?>
                                    </span>
                                </td>
                                <td class="fw-bold"><?= (int)$row['total_listings'] ?></td>
                                <td>
                                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2 py-1">
                                        <?= (int)$row['active_listings'] ?> active
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill px-2 py-1">
                                        <?= (int)$row['sold_out_listings'] ?> sold out
                                    </span>
                                </td>
                                <td class="fw-bold text-primary"><?= (int)$row['active_qty'] ?> portions</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

</div>

<style>
.hover-primary:hover {
    color: #ff6b35 !important;
}
.shadow-xs {
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
}
</style>
