<?php /* Admin Module 3.9: Zero Food Waste — Monitor & Report */ ?>
<div class="container py-4">

    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h2 class="fw-bold mb-1"><i class="bi bi-recycle text-success me-2"></i>Zero Food Waste Management</h2>
            <p class="text-muted mb-0">Monitor platform-wide Zero Waste listings and generate reports</p>
        </div>
        <a href="<?= url('/admin/dashboard') ?>" class="btn btn-outline-secondary btn-sm mt-3 mt-md-0">
            <i class="bi bi-arrow-left me-1"></i> Dashboard
        </a>
    </div>

    <!-- ── Stats Summary Cards ─────────────────────────────────────────────── -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4 col-lg-2">
            <div class="card kravyo-card border-0 h-100">
                <div class="card-body p-3 text-center">
                    <div class="fs-3 fw-800 text-dark"><?= (int)($stats['total_listings'] ?? 0) ?></div>
                    <div class="text-muted small">Total Listings</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="card kravyo-card border-0 h-100">
                <div class="card-body p-3 text-center">
                    <div class="fs-3 fw-800 text-success"><?= (int)($stats['active_listings'] ?? 0) ?></div>
                    <div class="text-muted small">Active</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="card kravyo-card border-0 h-100">
                <div class="card-body p-3 text-center">
                    <div class="fs-3 fw-800 text-warning"><?= (int)($stats['sold_out_listings'] ?? 0) ?></div>
                    <div class="text-muted small">Sold Out</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="card kravyo-card border-0 h-100">
                <div class="card-body p-3 text-center">
                    <div class="fs-3 fw-800 text-secondary"><?= (int)($stats['expired_listings'] ?? 0) ?></div>
                    <div class="text-muted small">Expired</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="card kravyo-card border-0 h-100">
                <div class="card-body p-3 text-center">
                    <div class="fs-3 fw-800 text-primary"><?= (int)($stats['total_active_qty'] ?? 0) ?></div>
                    <div class="text-muted small">Active Qty</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="card kravyo-card border-0 h-100">
                <div class="card-body p-3 text-center">
                    <div class="fs-3 fw-800 text-danger">₹<?= number_format((float)($stats['potential_savings'] ?? 0), 0) ?></div>
                    <div class="text-muted small">Savings Offered</div>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Revenue Insight Row ──────────────────────────────────────────────── -->
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card kravyo-card border-0 h-100">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="stat-icon-box bg-danger bg-opacity-10 text-danger rounded-3 p-3 fs-4">
                        <i class="bi bi-tag-fill"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-800 text-danger">₹<?= number_format((float)($stats['total_original_value'] ?? 0), 0) ?></div>
                        <div class="text-muted small">Total Original Value Listed</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card kravyo-card border-0 h-100">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="stat-icon-box bg-success bg-opacity-10 text-success rounded-3 p-3 fs-4">
                        <i class="bi bi-percent"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-800 text-success">₹<?= number_format((float)($stats['total_discounted_value'] ?? 0), 0) ?></div>
                        <div class="text-muted small">Total Discounted Value Listed</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Listings Table ───────────────────────────────────────────────────── -->
    <div class="card kravyo-card border-0 mb-4">
        <div class="card-header bg-transparent border-bottom py-3 d-flex flex-wrap gap-2 align-items-center justify-content-between">
            <h5 class="fw-bold mb-0"><i class="bi bi-list-ul me-2 text-success"></i>All Listings</h5>
            <!-- Status Filter Tabs -->
            <div class="d-flex gap-1 flex-wrap">
                <?php
                $filterOptions = [
                    'all'      => ['label' => 'All', 'color' => 'secondary'],
                    'active'   => ['label' => 'Active', 'color' => 'success'],
                    'sold_out' => ['label' => 'Sold Out', 'color' => 'warning'],
                    'expired'  => ['label' => 'Expired', 'color' => 'danger'],
                ];
                foreach ($filterOptions as $key => $opt):
                    $isActive = ($statusFilter === $key);
                ?>
                    <a href="<?= url('/admin/zero-waste?status=' . $key) ?>"
                       class="btn btn-sm btn-<?= $isActive ? $opt['color'] : 'outline-' . $opt['color'] ?>">
                        <?= $opt['label'] ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">ID</th>
                            <th>Food Item</th>
                            <th>Kitchen</th>
                            <th>Orig. Price</th>
                            <th>Disc. Price</th>
                            <th>Discount</th>
                            <th>Qty</th>
                            <th>Expiry</th>
                            <th>Status</th>
                            <th>Listed On</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($listings)): ?>
                            <tr>
                                <td colspan="10" class="text-center py-5 text-muted">
                                    <i class="bi bi-recycle display-4 d-block mb-3 opacity-25"></i>
                                    No Zero Waste listings found<?= $statusFilter !== 'all' ? ' with status "' . sanitize($statusFilter) . '"' : '' ?>.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($listings as $item): ?>
                                <?php
                                $isExpired  = ($item['status'] === 'expired' || strtotime($item['expiry_time']) <= time());
                                $isSoldOut  = ($item['status'] === 'sold_out');
                                $isActive   = ($item['status'] === 'active' && !$isExpired);
                                ?>
                                <tr>
                                    <td class="ps-4 text-muted small fw-bold">#<?= $item['id'] ?></td>
                                    <td class="fw-semibold"><?= sanitize($item['item_name']) ?></td>
                                    <td>
                                        <div class="fw-semibold text-dark"><?= sanitize($item['kitchen_name']) ?></div>
                                        <small class="text-muted"><?= sanitize($item['city']) ?></small>
                                    </td>
                                    <td class="text-muted"><s>₹<?= number_format((float)$item['original_price'], 2) ?></s></td>
                                    <td class="fw-bold text-success">₹<?= number_format((float)$item['discounted_price'], 2) ?></td>
                                    <td>
                                        <span class="badge bg-danger bg-opacity-10 text-danger fw-bold"><?= (int)$item['discount_pct'] ?>% off</span>
                                    </td>
                                    <td class="fw-semibold"><?= (int)$item['quantity'] ?></td>
                                    <td>
                                        <small class="<?= $isExpired ? 'text-danger' : 'text-muted' ?>">
                                            <?= date('d M, H:i', strtotime($item['expiry_time'])) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php if ($isActive): ?>
                                            <span class="badge bg-success bg-opacity-10 text-success">Active</span>
                                        <?php elseif ($isSoldOut): ?>
                                            <span class="badge bg-warning bg-opacity-10 text-warning">Sold Out</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary">Expired</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><small class="text-muted"><?= date('d M Y', strtotime($item['created_at'])) ?></small></td>
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
    <div class="card kravyo-card border-0">
        <div class="card-header bg-transparent border-bottom py-3">
            <h5 class="fw-bold mb-0"><i class="bi bi-shop me-2 text-warning"></i>Zero Waste Report — By Kitchen</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Kitchen</th>
                            <th>City</th>
                            <th>Total Listings</th>
                            <th>Active</th>
                            <th>Sold Out</th>
                            <th>Active Qty Available</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($byKitchen as $row): ?>
                            <tr>
                                <td class="ps-4 fw-semibold"><?= sanitize($row['kitchen_name']) ?></td>
                                <td class="text-muted"><?= sanitize($row['city']) ?></td>
                                <td class="fw-bold"><?= (int)$row['total_listings'] ?></td>
                                <td>
                                    <span class="badge bg-success bg-opacity-10 text-success"><?= (int)$row['active_listings'] ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-warning bg-opacity-10 text-warning"><?= (int)$row['sold_out_listings'] ?></span>
                                </td>
                                <td class="fw-semibold text-primary"><?= (int)$row['active_qty'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

</div>
