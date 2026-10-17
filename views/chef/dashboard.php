<?php /* Phase 9: Chef Dashboard — Fully wired analytics */ ?>
<style>
@media print {
    @page {
        size: A4 portrait;
        margin: 8mm 8mm 8mm 8mm;
    }

    *, *::before, *::after {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        box-sizing: border-box !important;
    }

    html, body {
        background: #ffffff !important;
        color: #0f172a !important;
        font-size: 8.5pt !important;
        line-height: 1.25 !important;
        margin: 0 !important;
        padding: 0 !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    /* Hide web navigation, footers, alerts, banners, and interactive controls */
    header,
    .navbar,
    footer,
    .kravyo-footer,
    .alert,
    .d-print-none,
    #dashboardFilterForm,
    .filter-toolbar-card,
    .availability-toggle-container,
    .kitchen-status-banner,
    #chartScaleButtons,
    .btn,
    .btn-group {
        display: none !important;
    }

    /* Container resets */
    .container, .container-fluid {
        max-width: 100% !important;
        width: 100% !important;
        padding: 0 !important;
        margin: 0 !important;
    }
    .py-4 {
        padding-top: 0 !important;
        padding-bottom: 0 !important;
    }

    /* Statement Report Header (Crisp executive banner) */
    .print-report-header {
        display: block !important;
        border-bottom: 2px solid #ff6b35 !important;
        padding-bottom: 6px !important;
        margin-bottom: 8px !important;
    }

    /* Cards general styling */
    .card, .kravyo-card {
        box-shadow: none !important;
        border: 1px solid #cbd5e1 !important;
        background: #ffffff !important;
        border-radius: 5px !important;
        break-inside: avoid !important;
        page-break-inside: avoid !important;
        margin-bottom: 8px !important;
        height: auto !important;
    }

    .card-header {
        background: #f8fafc !important;
        border-bottom: 1px solid #cbd5e1 !important;
        padding: 5px 8px !important;
    }

    .card-body {
        padding: 6px 8px !important;
    }

    /* Tighten spacings inside cards */
    .card-body .mb-3 { margin-bottom: 3px !important; }
    .card-body .mb-4 { margin-bottom: 3px !important; }
    .card-body .pb-3 { padding-bottom: 3px !important; }
    .card-body .p-3 { padding: 4px 6px !important; }
    .card-body .p-4 { padding: 6px 8px !important; }
    .card-body .pt-3 { padding-top: 3px !important; }
    .card-body .mt-2 { margin-top: 3px !important; }
    .card-body .mt-3 { margin-top: 3px !important; }

    /* Force 4-column KPI row via CSS Grid */
    .kpi-print-row {
        display: grid !important;
        grid-template-columns: repeat(4, 1fr) !important;
        gap: 6px !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
        margin-bottom: 8px !important;
    }

    .kpi-print-row > div {
        width: 100% !important;
        max-width: 100% !important;
        padding: 0 !important;
    }

    .stat-card {
        padding: 6px 8px !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 5px !important;
        background: #f8fafc !important;
        box-shadow: none !important;
    }

    .stat-card .stat-icon {
        display: none !important;
    }

    .stat-card .stat-value {
        font-size: 1.15rem !important;
        font-weight: 800 !important;
        line-height: 1.1 !important;
        color: #0f172a !important;
    }

    .stat-card .stat-label {
        font-size: 0.65rem !important;
        text-transform: uppercase;
        font-weight: 700;
        color: #64748b !important;
        margin-top: 2px !important;
    }

    /* Force Middle Row: Financial Insights (38%) + Chart (62%) side-by-side */
    .middle-print-row {
        display: grid !important;
        grid-template-columns: 38% calc(62% - 8px) !important;
        gap: 8px !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
        margin-bottom: 8px !important;
        break-inside: avoid !important;
        page-break-inside: avoid !important;
    }

    .middle-print-row > div {
        width: 100% !important;
        max-width: 100% !important;
        padding: 0 !important;
    }

    /* Chart Canvas sizing for single-page fit */
    .chart-canvas-wrapper {
        height: 155px !important;
        max-height: 155px !important;
    }

    #chefAnalyticsChart {
        max-height: 155px !important;
        height: 155px !important;
        width: 100% !important;
    }

    /* Force Bottom Row: 3 columns side-by-side */
    .bottom-print-row {
        display: grid !important;
        grid-template-columns: repeat(3, 1fr) !important;
        gap: 8px !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
        margin-bottom: 0 !important;
        break-inside: avoid !important;
        page-break-inside: avoid !important;
    }

    .bottom-print-row > div {
        width: 100% !important;
        max-width: 100% !important;
        padding: 0 !important;
    }

    .bottom-print-row table {
        font-size: 0.72rem !important;
    }

    .bottom-print-row td {
        padding: 2px 3px !important;
    }

    .bottom-print-row .mb-3 {
        margin-bottom: 3px !important;
    }

    /* Prevent breaking inside cards, tables, rows */
    .print-avoid-break,
    .card,
    .kravyo-card,
    .stat-card,
    table,
    tr {
        break-inside: avoid !important;
        page-break-inside: avoid !important;
    }
}
</style>
<div class="container py-4">
    <!-- Dedicated Print / PDF Statement Header (Visible only on print/PDF) -->
    <div class="d-none d-print-block print-report-header mb-3">
        <div class="d-flex justify-content-between align-items-start pb-2 mb-2">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <img src="<?= url('/assets/images/kravyo-logo.png') ?>" alt="Kravyo" height="24" style="object-fit: contain;">
                    <span class="fs-4 fw-800 text-dark" style="letter-spacing:-0.5px;">KRAVYO</span>
                    <span class="badge bg-secondary ms-2 small" style="font-size:0.65rem;">Chef Performance Statement</span>
                </div>
                <div class="small text-muted" style="font-size:0.75rem;">
                    Kitchen: <strong class="text-dark"><?= sanitize($kitchen['kitchen_name']) ?></strong> &bull;
                    City: <strong><?= sanitize($kitchen['city'] ?: '--') ?></strong>
                    <?php if (!empty($kitchen['fssai_license'])): ?>
                        &bull; FSSAI: <strong><?= sanitize($kitchen['fssai_license']) ?></strong>
                    <?php endif; ?>
                </div>
            </div>
            <div class="text-end">
                <div class="fw-bold text-dark" style="font-size: 0.85rem;">
                    Period: <span class="text-primary"><?= sanitize($rangeLabel) ?></span>
                </div>
                <div class="small text-muted" style="font-size: 0.7rem;">
                    Generated on <?= date('d M Y, h:i A') ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Chef Dashboard Web Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 d-print-none">
        <div>
            <h2 class="fw-bold mb-1">
                <i class="bi bi-speedometer2 text-warning me-2"></i>Chef Dashboard
            </h2>
            <p class="text-muted mb-0">Manage your kitchen, track orders, and grow your business</p>
        </div>
        <a href="<?= url('/chef/profile') ?>" class="btn btn-kravyo-primary mt-3 mt-md-0 d-print-none">
            <i class="bi bi-pencil-square me-1"></i> Edit Kitchen Profile
        </a>
    </div>

    <?php if (!$kitchen): ?>
        <!-- No Kitchen Found Alert -->
        <div class="alert alert-warning d-flex align-items-center" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-3 fs-4"></i>
            <div>
                <strong>Kitchen Profile Missing!</strong> Your kitchen profile could not be found.
                Please contact support or try registering again.
            </div>
        </div>
    <?php else: ?>

        <!-- Kitchen Status Banner -->
        <div class="kitchen-status-banner status-<?= $kitchen['approval_status'] ?> mb-4 d-print-none">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <h4 class="fw-bold mb-0"><?= sanitize($kitchen['kitchen_name']) ?></h4>
                        <span class="badge-status badge-status-<?= $kitchen['approval_status'] ?>">
                            <i class="bi bi-<?= $kitchen['approval_status'] === 'approved' ? 'check-circle-fill' : ($kitchen['approval_status'] === 'rejected' ? 'x-circle-fill' : 'hourglass-split') ?> me-1"></i>
                            <?= ucfirst($kitchen['approval_status']) ?>
                        </span>
                    </div>

                    <?php if ($kitchen['approval_status'] === 'pending'): ?>
                        <p class="mb-0 opacity-75">
                            <i class="bi bi-info-circle me-1"></i>
                            Your kitchen is under review. Our team will verify your details and approve your kitchen within 24-48 hours.
                            In the meantime, please complete your <a href="<?= url('/chef/profile') ?>" class="text-white fw-bold">kitchen profile</a>.
                        </p>
                    <?php elseif ($kitchen['approval_status'] === 'rejected'): ?>
                        <p class="mb-0 opacity-75">
                            <i class="bi bi-info-circle me-1"></i>
                            <strong>Reason:</strong> <?= sanitize($kitchen['admin_notes'] ?? 'No reason provided.') ?>
                            <br>Please update your profile and resubmit for review.
                        </p>
                    <?php else: ?>
                        <p class="mb-0 opacity-75">
                            <i class="bi bi-check2-all me-1"></i>
                            Your kitchen is verified and active! Customers can now discover and order from your kitchen.
                        </p>
                    <?php endif; ?>
                </div>

                <!-- Availability Toggle (only for approved kitchens) -->
                <?php if ($kitchen['approval_status'] === 'approved'): ?>
                <div class="col-md-4 text-md-end mt-3 mt-md-0 d-print-none">
                    <div class="availability-toggle-container">
                        <label class="availability-label mb-2 d-block">Kitchen Status</label>
                        <div class="availability-switch" id="availabilityToggle" data-csrf="<?= csrf_token() ?>">
                            <div class="toggle-track <?= $kitchen['is_open'] ? 'active' : '' ?>">
                                <div class="toggle-thumb"></div>
                            </div>
                            <span class="toggle-text fw-bold <?= $kitchen['is_open'] ? 'text-success' : 'text-danger' ?>">
                                <?= $kitchen['is_open'] ? '● OPEN' : '● CLOSED' ?>
                            </span>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Global Date & Period Filter Toolbar -->
        <div class="card kravyo-card border-0 shadow-sm mb-4 d-print-none">
            <div class="card-body p-3">
                <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                    <!-- Left: Date Range Form -->
                    <form method="GET" action="<?= url('/chef/dashboard') ?>" id="dashboardFilterForm" class="d-flex flex-wrap align-items-center gap-2 mb-0">
                        <div class="d-flex align-items-center gap-2">
                            <label for="filterRangeSelect" class="form-label mb-0 small fw-bold text-muted text-nowrap">
                                <i class="bi bi-funnel-fill text-primary me-1"></i>Period:
                            </label>
                            <select name="range" id="filterRangeSelect" class="form-select form-select-sm shadow-none" style="min-width: 170px;" onchange="handleRangeChange(this.value)">
                                <option value="7days" <?= $activeRange === '7days' ? 'selected' : '' ?>>📅 Last 7 Days</option>
                                <option value="today" <?= $activeRange === 'today' ? 'selected' : '' ?>>⚡ Today</option>
                                <option value="yesterday" <?= $activeRange === 'yesterday' ? 'selected' : '' ?>>⏪ Yesterday</option>
                                <option value="this_month" <?= $activeRange === 'this_month' ? 'selected' : '' ?>>🗓️ This Month</option>
                                <option value="last_month" <?= $activeRange === 'last_month' ? 'selected' : '' ?>>📆 Last Month</option>
                                <option value="overall" <?= in_array($activeRange, ['overall', 'all'], true) ? 'selected' : '' ?>>🌐 Overall Period</option>
                                <option value="custom" <?= $activeRange === 'custom' ? 'selected' : '' ?>>⚙️ Custom Range</option>
                            </select>
                        </div>

                        <!-- Custom Date Pickers (Shown when 'custom' is active) -->
                        <div id="customDateRangeWrapper" class="align-items-center gap-2 <?= $activeRange === 'custom' ? 'd-flex' : 'd-none' ?>">
                            <input type="date" name="start_date" id="filterStartDate" class="form-control form-control-sm" value="<?= sanitize($startDate) ?>" max="<?= date('Y-m-d') ?>">
                            <span class="text-muted small">to</span>
                            <input type="date" name="end_date" id="filterEndDate" class="form-control form-control-sm" value="<?= sanitize($endDate) ?>" max="<?= date('Y-m-d') ?>">
                            <button type="submit" class="btn btn-sm btn-kravyo-primary px-3">
                                <i class="bi bi-check2 me-1"></i>Apply
                            </button>
                        </div>

                        <?php if ($activeRange !== '7days'): ?>
                            <a href="<?= url('/chef/dashboard') ?>" class="btn btn-sm btn-outline-secondary" title="Reset to default 7 days">
                                <i class="bi bi-x-circle me-1"></i>Reset
                            </a>
                        <?php endif; ?>

                        <span class="badge bg-light text-dark border px-3 py-2 fw-semibold d-none d-md-inline-flex align-items-center gap-1">
                            <i class="bi bi-clock-history text-primary"></i> <?= sanitize($rangeLabel) ?>
                        </span>
                    </form>

                    <!-- Right: Export & Print Actions -->
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <a href="<?= url('/chef/dashboard/export?range=' . urlencode($activeRange) . '&start_date=' . urlencode($startDate) . '&end_date=' . urlencode($endDate)) ?>"
                           class="btn btn-sm btn-outline-success fw-semibold d-inline-flex align-items-center gap-1 shadow-sm"
                           title="Download Orders CSV for this period">
                            <i class="bi bi-file-earmark-spreadsheet"></i> Export CSV
                        </a>
                        <button type="button" class="btn btn-sm btn-outline-secondary fw-semibold d-inline-flex align-items-center gap-1 shadow-sm" onclick="window.print()" title="Print or save as PDF">
                            <i class="bi bi-printer"></i> Print / PDF
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dynamic Filtered KPIs Row -->
        <div class="row g-3 mb-4 kpi-print-row">
            <!-- Period Earnings -->
            <div class="col-6 col-lg-3">
                <div class="stat-card stat-card-earnings">
                    <div class="stat-icon"><i class="bi bi-currency-rupee"></i></div>
                    <div class="stat-value">₹<?= number_format($filteredStats['total_earnings'], 0) ?></div>
                    <div class="stat-label">Period Earnings</div>
                    <div class="small text-muted mt-1" style="font-size:0.72rem;">
                        Lifetime: ₹<?= number_format($totalEarnings, 0) ?>
                    </div>
                </div>
            </div>

            <!-- Period Delivered Orders -->
            <div class="col-6 col-lg-3">
                <div class="stat-card stat-card-orders">
                    <div class="stat-icon"><i class="bi bi-bag-check"></i></div>
                    <div class="stat-value"><?= number_format($filteredStats['delivered_orders']) ?></div>
                    <div class="stat-label">Delivered Orders</div>
                    <div class="small text-muted mt-1" style="font-size:0.72rem;">
                        <?= (int)$filteredStats['total_placed_orders'] ?> placed (<?= (int)$totalOrders ?> total)
                    </div>
                </div>
            </div>

            <!-- Average Order Value (AOV) -->
            <div class="col-6 col-lg-3">
                <div class="stat-card stat-card-menu">
                    <div class="stat-icon"><i class="bi bi-receipt"></i></div>
                    <div class="stat-value">₹<?= number_format($filteredStats['avg_order_value'], 0) ?></div>
                    <div class="stat-label">Avg Order Value (AOV)</div>
                    <div class="small text-muted mt-1" style="font-size:0.72rem;">
                        Per delivered order
                    </div>
                </div>
            </div>

            <!-- Avg Rating & Menu Items -->
            <div class="col-6 col-lg-3">
                <div class="stat-card stat-card-reviews">
                    <div class="stat-icon"><i class="bi bi-star-fill text-warning"></i></div>
                    <div class="stat-value"><?= $avgRating > 0 ? $avgRating . ' ★' : '--' ?></div>
                    <div class="stat-label">Avg Rating (<?= (int)$reviewCount ?>)</div>
                    <div class="small text-muted mt-1" style="font-size:0.72rem;">
                        <?= (int)$menuItemCount ?> active dishes
                    </div>
                </div>
            </div>
        </div>

        <!-- Middle Row: Financial Insights + Interactive Chart.js -->
        <div class="row g-4 mb-4 middle-print-row">

            <!-- Financial & Settlement Insights Card -->
            <div class="col-lg-4 col-financial">
                <div class="card kravyo-card border-0 h-100">
                    <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0">
                            <i class="bi bi-wallet2 text-success me-2"></i>Financial Insights
                        </h5>
                        <span class="badge bg-success bg-opacity-10 text-success small">Settlements</span>
                    </div>
                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                        <div>
                            <div class="mb-3 pb-3 border-bottom">
                                <div class="text-muted small">Gross Sales (Delivered)</div>
                                <div class="fs-4 fw-bold text-dark">₹<?= number_format($filteredStats['total_earnings'], 2) ?></div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-2 small">
                                <span class="text-muted"><i class="bi bi-credit-card-2-front text-primary me-1"></i> Online Paid (Settled)</span>
                                <span class="fw-semibold text-success">₹<?= number_format($filteredStats['online_settled'], 2) ?></span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-3 small">
                                <span class="text-muted"><i class="bi bi-cash-stack text-warning me-1"></i> COD (Cash in Hand)</span>
                                <span class="fw-semibold text-dark">₹<?= number_format($filteredStats['cod_pending'], 2) ?></span>
                            </div>

                            <div class="p-3 rounded-3 bg-light border mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-bold text-dark">Net Kitchen Earnings</span>
                                    <span class="fs-5 fw-800 text-success">₹<?= number_format($filteredStats['net_payable'], 2) ?></span>
                                </div>
                                <div class="text-muted" style="font-size:0.68rem; margin-top:2px;">
                                    100% chef retained earnings (0% platform commission)
                                </div>
                            </div>

                            <!-- Month-over-month trend -->
                            <div class="p-2 px-3 rounded-2 bg-light-subtle small text-muted d-flex justify-content-between align-items-center">
                                <span>This Month: <strong>₹<?= number_format($monthlyEarnings['this_month'], 0) ?></strong></span>
                                <?php if ($monthTrend != 0): ?>
                                    <span class="badge bg-<?= $monthTrend >= 0 ? 'success' : 'danger' ?> text-white">
                                        <?= $monthTrend >= 0 ? '+' : '' ?><?= $monthTrend ?>% MoM
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="text-muted small pt-3 mt-2 border-top" style="font-size:0.7rem;">
                            <i class="bi bi-shield-check text-success me-1"></i> Payout settlements are processed automatically every Tuesday.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Interactive Chart.js Analytics Card -->
            <div class="col-lg-8 col-chart">
                <div class="card kravyo-card border-0 h-100">
                    <div class="card-header bg-transparent border-bottom py-3">
                        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">
                            <h5 class="fw-bold mb-0">
                                <i class="bi bi-graph-up text-primary me-2"></i>Revenue & Orders Trend
                            </h5>

                            <!-- Metric Switcher & Scale Toggles -->
                            <div class="d-flex flex-wrap align-items-center gap-2 d-print-none">
                                <!-- Metric view toggle -->
                                <div class="btn-group btn-group-sm" role="group" aria-label="Metric Switcher">
                                    <button type="button" class="btn btn-outline-primary active" id="btnMetricRev" onclick="switchMetric('revenue')">₹ Revenue</button>
                                    <button type="button" class="btn btn-outline-primary" id="btnMetricOrders" onclick="switchMetric('orders')">📦 Orders</button>
                                    <button type="button" class="btn btn-outline-primary" id="btnMetricBoth" onclick="switchMetric('both')">Dual View</button>
                                </div>

                                <!-- Quick Scale Switcher -->
                                <div class="btn-group btn-group-sm" role="group" aria-label="Chart Scale" id="chartScaleButtons">
                                    <button type="button" class="btn btn-outline-secondary <?= $activeRange === '7days' ? 'active' : '' ?>" onclick="fetchChartScale('7D', this)">7D</button>
                                    <button type="button" class="btn btn-outline-secondary" onclick="fetchChartScale('15D', this)">15D</button>
                                    <button type="button" class="btn btn-outline-secondary" onclick="fetchChartScale('30D', this)">30D</button>
                                    <button type="button" class="btn btn-outline-secondary <?= $activeRange === 'this_month' ? 'active' : '' ?>" onclick="fetchChartScale('month', this)">Month</button>
                                    <button type="button" class="btn btn-outline-secondary" onclick="fetchChartScale('year', this)">Year</button>
                                    <button type="button" class="btn btn-outline-secondary <?= in_array($activeRange, ['overall', 'all'], true) ? 'active' : '' ?>" onclick="fetchChartScale('overall', this)">Overall</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-4 position-relative">
                        <!-- Chart container with responsive canvas -->
                        <div class="chart-canvas-wrapper" style="position: relative; height: 260px; width: 100%;">
                            <canvas id="chefAnalyticsChart"></canvas>
                        </div>

                        <!-- Chart Summary Micro Stats -->
                        <div class="d-flex flex-wrap justify-content-between align-items-center pt-3 mt-3 border-top small text-muted">
                            <div>
                                Chart Period: <strong class="text-dark" id="chartPeriodLabel"><?= sanitize($rangeLabel) ?></strong>
                            </div>
                            <div class="d-flex gap-3">
                                <span>Total Revenue: <strong class="text-primary" id="chartTotalRev">₹<?= number_format($chartData['total_rev'], 0) ?></strong></span>
                                <span>Total Orders: <strong class="text-dark" id="chartTotalOrders"><?= (int)$chartData['total_orders'] ?></strong></span>
                                <span>Peak Day: <strong class="text-success" id="chartPeakDay">₹<?= number_format($chartData['max_rev'], 0) ?></strong></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Row: Kitchen Info + Top Dishes (Filtered) + Recent Reviews -->
        <div class="row g-4 bottom-print-row">

            <!-- Kitchen Information -->
            <div class="col-lg-4">
                <div class="card kravyo-card border-0 h-100">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3"><i class="bi bi-shop me-2 text-warning"></i>Kitchen Information</h5>
                        <table class="table table-borderless mb-0 small">
                            <tr>
                                <td class="text-muted fw-semibold" style="width:120px">Name</td>
                                <td class="fw-semibold"><?= sanitize($kitchen['kitchen_name']) ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted fw-semibold">Address</td>
                                <td><?= sanitize($kitchen['address'] ?: 'Not provided') ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted fw-semibold">City</td>
                                <td><?= sanitize($kitchen['city'] ?: '--') ?> — <?= sanitize($kitchen['pincode'] ?: '--') ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted fw-semibold">FSSAI</td>
                                <td>
                                    <?php if (!empty($kitchen['fssai_license'])): ?>
                                        <span class="badge bg-success bg-opacity-10 text-success px-2 py-1">
                                            <i class="bi bi-shield-check me-1"></i><?= sanitize($kitchen['fssai_license']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">Not provided</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted fw-semibold">Hygiene</td>
                                <td>
                                    <?php if ($kitchen['hygiene_badge'] === 'verified'): ?>
                                        <span class="badge-hygiene"><i class="bi bi-patch-check-fill me-1"></i>Verified</span>
                                    <?php else: ?>
                                        <span class="text-muted">Not Yet Verified</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Top Selling Dishes -->
            <div class="col-lg-4">
                <div class="card kravyo-card border-0 h-100">
                    <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0"><i class="bi bi-trophy me-2 text-warning"></i>Top Selling Dishes</h5>
                        <span class="badge bg-light text-muted border small">In Period</span>
                    </div>
                    <div class="card-body p-3">
                        <?php if (empty($topDishes)): ?>
                            <div class="text-center text-muted py-4">
                                <i class="bi bi-egg-fried display-4 opacity-25 d-block mb-2"></i>
                                <p class="small">No delivered orders in this period</p>
                                <a href="<?= url('/chef/menu') ?>" class="btn btn-sm btn-kravyo-outline">Manage Dishes</a>
                            </div>
                        <?php else: ?>
                            <?php foreach ($topDishes as $rank => $dish): ?>
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <div class="fw-800 text-muted" style="width:20px;font-size:0.8rem;"><?= $rank + 1 ?></div>
                                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center overflow-hidden"
                                         style="width:36px;height:36px;flex-shrink:0;">
                                        <?php if (!empty($dish['image'])): ?>
                                            <img src="<?= url('/uploads/dishes/' . $dish['image']) ?>"
                                                 class="w-100 h-100 object-fit-cover" alt="">
                                        <?php else: ?>
                                            <span style="font-size:1.1rem;"><?= $dish['is_veg'] ? '🌿' : '🍗' ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex-grow-1 min-w-0">
                                        <div class="fw-semibold small text-truncate"><?= sanitize($dish['item_name']) ?></div>
                                        <div class="text-muted" style="font-size:0.7rem;"><?= (int)$dish['total_qty'] ?> orders · ₹<?= number_format((float)$dish['total_revenue'], 0) ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Recent Reviews -->
            <div class="col-lg-4">
                <div class="card kravyo-card border-0 h-100">
                    <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0"><i class="bi bi-chat-quote me-2 text-info"></i>Recent Reviews</h5>
                        <div class="d-flex align-items-center gap-2">
                            <?php if ($avgRating > 0): ?>
                                <span class="badge bg-warning text-dark fw-bold px-2">
                                    <?= $avgRating ?> ★
                                </span>
                            <?php endif; ?>
                            <a href="<?= url('/chef/reviews') ?>" class="btn btn-outline-primary btn-sm rounded-pill px-2 py-0 fw-semibold" style="font-size:0.75rem;">
                                View All
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-3">
                        <?php if (empty($recentReviews)): ?>
                            <div class="text-center text-muted py-4">
                                <i class="bi bi-star display-4 opacity-25 d-block mb-2"></i>
                                <p class="small">No reviews yet — complete orders to earn reviews!</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($recentReviews as $review): ?>
                                <div class="mb-3 pb-3 border-bottom">
                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                        <span class="fw-semibold small"><?= sanitize($review['customer_name']) ?></span>
                                        <span class="text-warning small">
                                            <?php for ($s = 1; $s <= 5; $s++): ?>
                                                <i class="bi bi-star<?= $s <= (int)$review['rating'] ? '-fill' : '' ?>"></i>
                                            <?php endfor; ?>
                                        </span>
                                    </div>
                                    <?php if (!empty($review['review_text'])): ?>
                                        <p class="text-muted mb-0" style="font-size:0.78rem;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
                                            "<?= sanitize($review['review_text']) ?>"
                                        </p>
                                    <?php endif; ?>
                                    <div class="text-muted mt-1" style="font-size:0.65rem;"><?= date('d M Y', strtotime($review['created_at'])) ?></div>
                                </div>
                            <?php endforeach; ?>
                            <div class="pt-2 text-center">
                                <a href="<?= url('/chef/reviews') ?>" class="btn btn-light btn-sm w-100 fw-semibold rounded-pill text-primary border">
                                    See All Customer Reviews <i class="bi bi-arrow-right ms-1"></i>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

    <?php endif; ?>
</div>

<!-- Chart.js CDN with Local Fallback and Interactive Handler -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
// Initial chart data from PHP backend
let initialChartData = <?= json_encode($chartData) ?>;
let analyticsChart = null;
let currentMetricView = 'revenue'; // 'revenue' | 'orders' | 'both'

function handleRangeChange(val) {
    const wrapper = document.getElementById('customDateRangeWrapper');
    if (val === 'custom') {
        wrapper.classList.remove('d-none');
        wrapper.classList.add('d-flex');
    } else {
        wrapper.classList.remove('d-flex');
        wrapper.classList.add('d-none');
        // Automatically submit form for instant filtering on preset change
        document.getElementById('dashboardFilterForm').submit();
    }
}

function initChefChart(data) {
    const ctx = document.getElementById('chefAnalyticsChart');
    if (!ctx) return;

    if (analyticsChart) {
        analyticsChart.destroy();
    }

    const labels   = data.labels || [];
    const revenues = data.revenues || [];
    const orders   = data.orders || [];

    // Revenue dataset
    const dsRevenue = {
        label: 'Revenue (₹)',
        data: revenues,
        borderColor: '#ff6b35',
        backgroundColor: 'rgba(255, 107, 53, 0.15)',
        fill: true,
        tension: 0.35,
        borderWidth: 2.5,
        pointBackgroundColor: '#ff6b35',
        pointBorderColor: '#ffffff',
        pointBorderWidth: 2,
        pointRadius: 4,
        pointHoverRadius: 6,
        yAxisID: 'yRevenue',
        hidden: currentMetricView === 'orders'
    };

    // Orders dataset
    const dsOrders = {
        label: 'Orders (Qty)',
        data: orders,
        type: 'bar',
        backgroundColor: 'rgba(13, 110, 253, 0.65)',
        borderColor: '#0d6efd',
        borderWidth: 1.5,
        borderRadius: 4,
        maxBarThickness: 24,
        yAxisID: 'yOrders',
        hidden: currentMetricView === 'revenue'
    };

    analyticsChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [dsRevenue, dsOrders]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false
            },
            plugins: {
                legend: {
                    display: currentMetricView === 'both',
                    position: 'top',
                    labels: {
                        boxWidth: 12,
                        font: { size: 11, weight: '600' }
                    }
                },
                tooltip: {
                    backgroundColor: '#1e293b',
                    titleFont: { size: 12, weight: 'bold' },
                    bodyFont: { size: 12 },
                    padding: 10,
                    cornerRadius: 8,
                    callbacks: {
                        label: function(context) {
                            if (context.dataset.label.includes('Revenue')) {
                                return ' Revenue: ₹' + Number(context.raw).toLocaleString('en-IN');
                            } else {
                                return ' Orders: ' + Number(context.raw) + ' orders';
                            }
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 11 } }
                },
                yRevenue: {
                    type: 'linear',
                    position: 'left',
                    display: currentMetricView !== 'orders',
                    grid: { color: 'rgba(0, 0, 0, 0.05)' },
                    ticks: {
                        callback: function(value) { return '₹' + value.toLocaleString('en-IN'); },
                        font: { size: 11 }
                    },
                    beginAtZero: true
                },
                yOrders: {
                    type: 'linear',
                    position: 'right',
                    display: currentMetricView !== 'revenue',
                    grid: { display: false },
                    ticks: {
                        stepSize: 1,
                        precision: 0,
                        font: { size: 11 }
                    },
                    beginAtZero: true
                }
            }
        }
    });
}

function switchMetric(mode) {
    currentMetricView = mode;
    document.getElementById('btnMetricRev').classList.toggle('active', mode === 'revenue');
    document.getElementById('btnMetricOrders').classList.toggle('active', mode === 'orders');
    document.getElementById('btnMetricBoth').classList.toggle('active', mode === 'both');

    if (!analyticsChart) return;

    analyticsChart.data.datasets[0].hidden = (mode === 'orders');
    analyticsChart.data.datasets[1].hidden = (mode === 'revenue');
    analyticsChart.options.scales.yRevenue.display = (mode !== 'orders');
    analyticsChart.options.scales.yOrders.display   = (mode !== 'revenue');
    analyticsChart.options.plugins.legend.display    = (mode === 'both');
    analyticsChart.update();
}

function fetchChartScale(scale, btnEl) {
    // Update active button state
    document.querySelectorAll('#chartScaleButtons button').forEach(b => b.classList.remove('active'));
    if (btnEl) btnEl.classList.add('active');

    const apiUrl = '<?= url('/chef/api/dashboard-chart') ?>?scale=' + encodeURIComponent(scale);
    fetch(apiUrl)
        .then(res => res.json())
        .then(result => {
            if (result && result.success && result.data) {
                initChefChart(result.data);
                // Update micro stats
                document.getElementById('chartTotalRev').textContent = '₹' + Number(result.data.total_rev || 0).toLocaleString('en-IN');
                document.getElementById('chartTotalOrders').textContent = (result.data.total_orders || 0);
                document.getElementById('chartPeakDay').textContent = '₹' + Number(result.data.max_rev || 0).toLocaleString('en-IN');
                document.getElementById('chartPeriodLabel').textContent = result.scale + ' (' + result.startDate + ' to ' + result.endDate + ')';
            }
        })
        .catch(err => {
            console.error('Error updating chart scale:', err);
        });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    if (typeof Chart !== 'undefined') {
        initChefChart(initialChartData);
    } else {
        console.warn('Chart.js CDN not ready; check network connection.');
    }
});

// Resize chart for crisp print rendering
window.addEventListener('beforeprint', function() {
    if (analyticsChart) {
        analyticsChart.resize();
    }
});
window.addEventListener('afterprint', function() {
    if (analyticsChart) {
        analyticsChart.resize();
    }
});
</script>
