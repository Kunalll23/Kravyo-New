<?php /* Phase 9: Admin Platform Analytics & Revenue Reports */ ?>
<div class="container py-4">

    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h2 class="fw-bold mb-1"><i class="bi bi-graph-up-arrow text-success me-2"></i>Analytics & Reports</h2>
            <p class="text-muted mb-0">Platform-wide revenue, order, and user statistics</p>
        </div>
        <a href="<?= url('/admin/dashboard') ?>" class="btn btn-outline-secondary btn-sm mt-3 mt-md-0">
            <i class="bi bi-arrow-left me-1"></i> Dashboard
        </a>
    </div>

    <!-- Revenue Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card kravyo-card border-0 h-100">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="stat-icon-box bg-success bg-opacity-10 text-success rounded-3 p-3 fs-4">
                        <i class="bi bi-currency-rupee"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-800 text-success">₹<?= number_format($revenue['total'], 0) ?></div>
                        <div class="text-muted small">Total Revenue</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card kravyo-card border-0 h-100">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="stat-icon-box bg-primary bg-opacity-10 text-primary rounded-3 p-3 fs-4">
                        <i class="bi bi-calendar-month"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-800">₹<?= number_format($revenue['this_month'], 0) ?></div>
                        <div class="text-muted small">This Month</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card kravyo-card border-0 h-100">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="stat-icon-box bg-warning bg-opacity-10 text-warning rounded-3 p-3 fs-4">
                        <i class="bi bi-bag-check"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-800"><?= number_format($revenue['total_orders']) ?></div>
                        <div class="text-muted small">Delivered Orders</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card kravyo-card border-0 h-100">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="stat-icon-box bg-info bg-opacity-10 text-info rounded-3 p-3 fs-4">
                        <i class="bi bi-receipt"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-800">₹<?= number_format($revenue['avg_order_value'], 0) ?></div>
                        <div class="text-muted small">Avg Order Value</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- Monthly Order Trend Chart -->
        <div class="col-lg-7">
            <div class="card kravyo-card border-0 h-100">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h5 class="fw-bold mb-0"><i class="bi bi-bar-chart me-2 text-primary"></i>Monthly Order Trend (Last 6 Months)</h5>
                </div>
                <div class="card-body p-4">
                    <?php if (empty($monthlyTrend)): ?>
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-bar-chart display-3 opacity-25 d-block mb-3"></i>
                            <p>No order data available yet</p>
                        </div>
                    <?php else: ?>
                        <?php
                        $maxOrders = max(array_column($monthlyTrend, 'order_count') ?: [1]);
                        ?>
                        <div class="d-flex align-items-end gap-2" style="height: 180px;">
                            <?php foreach ($monthlyTrend as $row): ?>
                                <?php $barH = $maxOrders > 0 ? round(($row['order_count'] / $maxOrders) * 160) : 4; ?>
                                <div class="d-flex flex-column align-items-center flex-grow-1 gap-1" style="min-width:0">
                                    <div class="fw-semibold text-primary small"><?= (int) $row['order_count'] ?></div>
                                    <div class="w-100 rounded-top bg-primary bg-opacity-75" style="height: <?= $barH ?>px; min-height:6px; transition:height 0.3s;"></div>
                                    <div class="text-muted" style="font-size:0.65rem; text-align:center;"><?= sanitize($row['month_label']) ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Order Status Funnel -->
        <div class="col-lg-5">
            <div class="card kravyo-card border-0 h-100">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h5 class="fw-bold mb-0"><i class="bi bi-funnel me-2 text-warning"></i>Order Status Breakdown</h5>
                </div>
                <div class="card-body p-4">
                    <?php
                    $totalForFunnel = array_sum($orderStats) ?: 1;
                    $funnelConfig = [
                        'delivered'        => ['label' => 'Delivered',        'color' => 'success'],
                        'pending'          => ['label' => 'Pending',          'color' => 'warning'],
                        'preparing'        => ['label' => 'Preparing',        'color' => 'primary'],
                        'accepted'         => ['label' => 'Accepted',         'color' => 'info'],
                        'out_for_delivery' => ['label' => 'Out for Delivery', 'color' => 'success'],
                        'cancelled'        => ['label' => 'Cancelled',        'color' => 'danger'],
                    ];
                    ?>
                    <?php foreach ($funnelConfig as $key => $cfg): ?>
                        <?php $cnt = $orderStats[$key] ?? 0; ?>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="small fw-semibold"><?= $cfg['label'] ?></span>
                                <span class="small text-muted"><?= $cnt ?></span>
                            </div>
                            <div class="progress" style="height: 10px; border-radius: 8px;">
                                <div class="progress-bar bg-<?= $cfg['color'] ?>"
                                     style="width: <?= round(($cnt / $totalForFunnel) * 100) ?>%"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Top Kitchens by Revenue -->
        <div class="col-lg-7">
            <div class="card kravyo-card border-0">
                <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0"><i class="bi bi-trophy me-2 text-warning"></i>Top Kitchens by Revenue</h5>
                    <a href="<?= url('/admin/chefs') ?>" class="btn btn-sm btn-outline-secondary">View All</a>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($topKitchens)): ?>
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-shop display-3 opacity-25 d-block mb-3"></i>
                            <p>No revenue data yet</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">#</th>
                                        <th>Kitchen</th>
                                        <th>City</th>
                                        <th>Orders</th>
                                        <th class="text-end pe-4">Revenue</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($topKitchens as $i => $k): ?>
                                        <tr>
                                            <td class="ps-4">
                                                <?php if ($i === 0): ?>
                                                    <span class="text-warning fw-bold">🥇</span>
                                                <?php elseif ($i === 1): ?>
                                                    <span class="text-secondary fw-bold">🥈</span>
                                                <?php elseif ($i === 2): ?>
                                                    <span style="color:#cd7f32;" class="fw-bold">🥉</span>
                                                <?php else: ?>
                                                    <span class="text-muted"><?= $i + 1 ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="fw-semibold small"><?= sanitize($k['kitchen_name']) ?></div>
                                                <div class="text-muted" style="font-size:0.7rem;"><?= sanitize($k['chef_name']) ?></div>
                                            </td>
                                            <td><span class="text-muted small"><?= sanitize($k['city']) ?></span></td>
                                            <td><span class="badge bg-primary bg-opacity-10 text-primary"><?= (int)$k['order_count'] ?> orders</span></td>
                                            <td class="text-end pe-4 fw-800 text-success">₹<?= number_format((float)$k['total_revenue'], 0) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- User Growth Stats -->
        <div class="col-lg-5">
            <div class="card kravyo-card border-0">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h5 class="fw-bold mb-0"><i class="bi bi-people me-2 text-info"></i>User Statistics</h5>
                </div>
                <div class="card-body p-4">
                    <!-- Donut-style bar display -->
                    <div class="text-center mb-3">
                        <div class="display-4 fw-800"><?= $userStats['total'] ?></div>
                        <div class="text-muted">Total Platform Users</div>
                    </div>

                    <?php
                    $totalUsers = $userStats['total'] ?: 1;
                    $customerPct = round(($userStats['customers'] / $totalUsers) * 100);
                    $chefPct = round(($userStats['chefs'] / $totalUsers) * 100);
                    ?>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="small fw-semibold text-danger"><i class="bi bi-person-heart me-1"></i>Customers</span>
                            <span class="small fw-bold"><?= $userStats['customers'] ?> (<?= $customerPct ?>%)</span>
                        </div>
                        <div class="progress" style="height:12px; border-radius:8px;">
                            <div class="progress-bar bg-danger" style="width:<?= $customerPct ?>%"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="small fw-semibold text-warning"><i class="bi bi-shop me-1"></i>Home Chefs</span>
                            <span class="small fw-bold"><?= $userStats['chefs'] ?> (<?= $chefPct ?>%)</span>
                        </div>
                        <div class="progress" style="height:12px; border-radius:8px;">
                            <div class="progress-bar bg-warning" style="width:<?= $chefPct ?>%"></div>
                        </div>
                    </div>

                    <div class="border-top pt-3 mt-3 d-flex justify-content-between align-items-center">
                        <a href="<?= url('/admin/users') ?>" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-people me-1"></i>Manage Users
                        </a>
                        <a href="<?= url('/admin/chefs') ?>" class="btn btn-sm btn-outline-warning">
                            <i class="bi bi-shop me-1"></i>Manage Kitchens
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
