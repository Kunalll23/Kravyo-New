<?php /* Phase 9 + Intervention: Admin — Platform-Wide Orders Management */ ?>
<div class="container py-4">

    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h2 class="fw-bold mb-1"><i class="bi bi-bag-check text-primary me-2"></i>Platform Orders</h2>
            <p class="text-muted mb-0">
                <?= $totalOrders ?> total orders across all kitchens
            </p>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="<?= url('/admin/reports') ?>" class="btn btn-outline-success btn-sm">
                <i class="bi bi-graph-up me-1"></i>Analytics
            </a>
            <a href="<?= url('/admin/dashboard') ?>" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Dashboard
            </a>
        </div>
    </div>

    <!-- Helper to preserve active query parameters when changing status tabs -->
    <?php
    $queryParams = $_GET;
    function orderUrlWithStatus(string $status, array $currentGet): string {
        $p = $currentGet;
        if ($status === 'all') {
            unset($p['status']);
        } else {
            $p['status'] = $status;
        }
        $q = http_build_query($p);
        return url('/admin/orders' . ($q ? '?' . $q : ''));
    }

    $activeFilterCount = 0;
    if (!empty($cityFilter)) $activeFilterCount++;
    if (!empty($dispatchFilter)) $activeFilterCount++;
    if (!empty($kitchenFilter)) $activeFilterCount++;
    if (!empty($paymentFilter)) $activeFilterCount++;
    if (!empty($dateFilter)) $activeFilterCount++;
    if (!empty($searchQuery)) $activeFilterCount++;
    ?>

    <!-- Status Filter Tabs -->
    <div class="card kravyo-card border-0 mb-3 shadow-sm">
        <div class="card-body p-0">
            <div class="d-flex flex-wrap border-bottom px-3 overflow-auto">
                <?php
                $tabs = [
                    'all'              => ['label' => 'All',              'color' => 'secondary'],
                    'pending'          => ['label' => 'Pending',          'color' => 'warning'],
                    'accepted'         => ['label' => 'Accepted',         'color' => 'info'],
                    'preparing'        => ['label' => 'Preparing',        'color' => 'primary'],
                    'ready_for_pickup' => ['label' => 'Ready for Pickup', 'color' => 'info'],
                    'out_for_delivery' => ['label' => 'Out for Delivery', 'color' => 'success'],
                    'delivered'        => ['label' => 'Delivered',        'color' => 'success'],
                    'cancelled'        => ['label' => 'Cancelled',        'color' => 'danger'],
                ];
                $allCount = array_sum($orderStats);
                ?>
                <?php foreach ($tabs as $tabKey => $tab): ?>
                    <?php
                    $count = ($tabKey === 'all') ? $allCount : ($orderStats[$tabKey] ?? 0);
                    $isActive = $statusFilter === $tabKey;
                    ?>
                    <a href="<?= orderUrlWithStatus($tabKey, $queryParams) ?>"
                       class="nav-link px-3 py-3 small fw-semibold border-bottom border-3 text-nowrap <?= $isActive ? 'text-primary border-primary' : 'text-muted border-transparent' ?>">
                        <?= $tab['label'] ?>
                        <span class="badge bg-<?= $isActive ? 'primary' : 'secondary' ?> bg-opacity-<?= $isActive ? '100' : '10' ?> text-<?= $isActive ? 'white' : 'secondary' ?> ms-1"><?= $count ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Advanced Operational Filters Toolbar -->
    <div class="card kravyo-card border-0 shadow-sm mb-3">
        <div class="card-body p-3">
            <form action="<?= url('/admin/orders') ?>" method="GET" id="orderFilterForm">
                <?php if (!empty($statusFilter) && $statusFilter !== 'all'): ?>
                    <input type="hidden" name="status" value="<?= sanitize($statusFilter) ?>">
                <?php endif; ?>

                <div class="row g-2 align-items-center">
                    <!-- Search Input -->
                    <div class="col-lg-3 col-md-6 col-12">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" name="search" class="form-control form-control-sm border-start-0" 
                                   placeholder="Order #, Customer, Phone, Kitchen..." 
                                   value="<?= sanitize($searchQuery ?? '') ?>">
                        </div>
                    </div>

                    <!-- City Filter Dropdown -->
                    <div class="col-lg-2 col-md-3 col-6">
                        <select name="city" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="all">📍 All Cities</option>
                            <?php foreach ($cities as $c): ?>
                                <option value="<?= sanitize($c) ?>" <?= ($cityFilter === $c) ? 'selected' : '' ?>>
                                    <?= sanitize($c) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Delivery / Dispatch Status Dropdown -->
                    <div class="col-lg-2 col-md-3 col-6">
                        <select name="dispatch_status" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="all">🛵 All Delivery States</option>
                            <option value="no_rider" <?= ($dispatchFilter === 'no_rider') ? 'selected' : '' ?>>⚠️ No Rider Found</option>
                            <option value="auto_assigning" <?= ($dispatchFilter === 'auto_assigning') ? 'selected' : '' ?>>🔄 Auto-Assigning</option>
                            <option value="assigned" <?= ($dispatchFilter === 'assigned') ? 'selected' : '' ?>>✅ Rider Assigned</option>
                            <option value="out_for_delivery" <?= ($dispatchFilter === 'out_for_delivery') ? 'selected' : '' ?>>🚀 Out for Delivery</option>
                            <option value="unassigned" <?= ($dispatchFilter === 'unassigned') ? 'selected' : '' ?>>⏳ Unassigned</option>
                        </select>
                    </div>

                    <!-- Date Range Dropdown -->
                    <div class="col-lg-2 col-md-3 col-6">
                        <select name="date_range" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="all">📅 All Time</option>
                            <option value="today" <?= ($dateFilter === 'today') ? 'selected' : '' ?>>Today</option>
                            <option value="yesterday" <?= ($dateFilter === 'yesterday') ? 'selected' : '' ?>>Yesterday</option>
                            <option value="7days" <?= ($dateFilter === '7days') ? 'selected' : '' ?>>Last 7 Days</option>
                            <option value="this_month" <?= ($dateFilter === 'this_month') ? 'selected' : '' ?>>This Month</option>
                        </select>
                    </div>

                    <!-- Kitchen Dropdown -->
                    <div class="col-lg-2 col-md-3 col-6">
                        <select name="kitchen_id" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">🍳 All Kitchens</option>
                            <?php foreach ($kitchens as $k): ?>
                                <option value="<?= (int)$k['id'] ?>" <?= ($kitchenFilter == $k['id']) ? 'selected' : '' ?>>
                                    <?= sanitize($k['kitchen_name']) ?><?= !empty($k['city']) ? ' (' . sanitize($k['city']) . ')' : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Payment Status Dropdown -->
                    <div class="col-lg-1 col-md-3 col-6">
                        <select name="payment_status" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="all">💳 Payment</option>
                            <option value="completed" <?= ($paymentFilter === 'completed') ? 'selected' : '' ?>>Paid</option>
                            <option value="pending" <?= ($paymentFilter === 'pending') ? 'selected' : '' ?>>Pending</option>
                            <option value="failed" <?= ($paymentFilter === 'failed') ? 'selected' : '' ?>>Failed</option>
                        </select>
                    </div>

                    <!-- Footer Action Bar -->
                    <div class="col-12 d-flex justify-content-between align-items-center mt-2 pt-2 border-top">
                        <div class="small text-muted">
                            Showing <strong><?= count($orders) ?></strong> orders
                            <?php if ($activeFilterCount > 0): ?>
                                <span class="badge bg-primary-subtle text-primary ms-1"><?= $activeFilterCount ?> active filter(s)</span>
                            <?php endif; ?>
                        </div>
                        <div class="d-flex gap-2">
                            <?php if ($activeFilterCount > 0 || ($statusFilter && $statusFilter !== 'all')): ?>
                                <a href="<?= url('/admin/orders') ?>" class="btn btn-outline-secondary btn-sm px-3">
                                    <i class="bi bi-x-circle me-1"></i>Reset All
                                </a>
                            <?php endif; ?>
                            <button type="submit" class="btn btn-primary btn-sm px-3 fw-bold">
                                <i class="bi bi-funnel me-1"></i>Apply Filters
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="card kravyo-card border-0 shadow-sm">
        <div class="card-body p-0">
            <?php if (empty($orders)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-inbox display-3 opacity-25 d-block mb-3"></i>
                    <h5 class="fw-bold">No orders found</h5>
                    <p class="small text-muted">No orders match your selected filters. Try clearing some filters or searching with a different term.</p>
                    <a href="<?= url('/admin/orders') ?>" class="btn btn-outline-primary btn-sm">Clear All Filters</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Order #</th>
                                <th>Customer</th>
                                <th>Kitchen</th>
                                <th>Delivery Partner</th>
                                <th>Amount</th>
                                <th>Payment</th>
                                <th>Status</th>
                                <th>Placed</th>
                                <th class="text-center pe-4">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                                <?php
                                $statusColors = [
                                    'pending'          => 'warning',
                                    'accepted'         => 'info',
                                    'preparing'        => 'primary',
                                    'ready_for_pickup' => 'info',
                                    'out_for_delivery' => 'success',
                                    'delivered'        => 'success',
                                    'cancelled'        => 'danger',
                                ];
                                $sc = $statusColors[$order['order_status']] ?? 'secondary';
                                $kCity = $order['kitchen_city'] ?? '';
                                ?>
                                <tr>
                                    <td class="ps-4">
                                        <span class="fw-semibold text-primary small"><?= sanitize($order['order_number']) ?></span>
                                    </td>
                                    <td>
                                        <div class="small fw-semibold"><?= sanitize($order['customer_name']) ?></div>
                                        <?php if (!empty($order['customer_phone'])): ?>
                                            <div class="text-muted small" style="font-size:0.75rem;"><i class="bi bi-telephone me-1"></i><?= sanitize($order['customer_phone']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="small fw-semibold text-dark"><?= sanitize($order['kitchen_name']) ?></div>
                                        <?php if (!empty($kCity)): ?>
                                            <span class="badge bg-light text-muted border small"><i class="bi bi-geo-alt-fill text-danger me-1"></i><?= sanitize($kCity) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($order['delivery_partner_name'])): ?>
                                            <span class="small fw-semibold text-primary"><i class="bi bi-bicycle me-1"></i><?= sanitize($order['delivery_partner_name']) ?></span>
                                        <?php elseif (($order['dispatch_status'] ?? '') === 'no_rider'): ?>
                                            <span class="badge bg-danger text-white small px-2 py-1"><i class="bi bi-exclamation-triangle-fill me-1"></i>No Rider Found</span>
                                        <?php elseif (in_array(($order['dispatch_status'] ?? ''), ['cascading', 'broadcasting'])): ?>
                                            <span class="badge bg-warning-subtle text-dark border border-warning small px-2 py-1">
                                                <i class="bi bi-broadcast me-1 text-warning-emphasis"></i>Auto-Assigning (R<?= $order['dispatch_round'] ?? 1 ?>)
                                            </span>
                                        <?php else: ?>
                                            <span class="small text-muted border px-1 rounded bg-light">Unassigned</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="fw-bold">&#8377;<?= number_format((float)$order['total_amount'], 2) ?></span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $order['payment_status'] === 'completed' ? 'success' : 'warning' ?> text-<?= $order['payment_status'] === 'completed' ? 'white' : 'dark' ?> rounded-pill px-3">
                                            <?= ucfirst($order['payment_status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $sc ?> rounded-pill px-3">
                                            <?= ucwords(str_replace('_', ' ', $order['order_status'])) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-muted small"><?= date('d M, h:i A', strtotime($order['created_at'])) ?></span>
                                    </td>
                                    <td class="text-center pe-4">
                                        <?php if (($order['dispatch_status'] ?? '') === 'no_rider'): ?>
                                            <a href="<?= url('/admin/dispatch-monitor?order=' . urlencode($order['order_number']) . '&city=' . urlencode($kCity)) ?>" 
                                               class="btn btn-sm btn-outline-danger py-0 px-2 small mb-1 fw-bold" 
                                               title="Open Live Dispatch Monitor">
                                                <i class="bi bi-broadcast me-1"></i>Dispatch Board
                                            </a><br>
                                        <?php endif; ?>
                                        <?php if ($order['order_status'] !== 'delivered' && $order['order_status'] !== 'cancelled'): ?>
                                            <button
                                                class="btn btn-sm btn-outline-secondary fw-semibold intervene-btn mb-1 py-0 px-2 small"
                                                title="Admin Intervention"
                                                data-bs-toggle="modal"
                                                data-bs-target="#interveneModal"
                                                data-order-id="<?= (int)$order['id'] ?>"
                                                data-order-number="<?= sanitize($order['order_number']) ?>"
                                                data-current-status="<?= sanitize($order['order_status']) ?>"
                                            >
                                                <i class="bi bi-shield-exclamation me-1"></i>Intervene
                                            </button>
                                        <?php else: ?>
                                            <span class="text-muted small">&#8212;</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Admin Intervention Modal -->
<div class="modal fade" id="interveneModal" tabindex="-1" aria-labelledby="interveneModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius:16px; overflow:hidden;">
            <div class="modal-header border-0 py-3 px-4" style="background: linear-gradient(135deg,#7b2d8b,#c0392b);">
                <h5 class="modal-title fw-bold text-white" id="interveneModalLabel">
                    <i class="bi bi-shield-exclamation me-2"></i>Admin Order Intervention
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4 py-4">
                <div class="alert alert-warning border-0 rounded-3 small mb-4 py-2">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                    <strong>Admin action.</strong> This will override the current order status and notify the customer.
                </div>
                <p class="mb-3 small text-muted">
                    Intervening on order:
                    <strong id="modalOrderNumber" class="text-primary"></strong>
                    &nbsp;|&nbsp;Current status:
                    <span id="modalCurrentStatus" class="badge bg-secondary rounded-pill px-2"></span>
                </p>
                <form action="<?= url('/admin/order/intervene') ?>" method="POST" id="interveneForm">
                    <?= csrf_field() ?>
                    <input type="hidden" name="order_id" id="modalOrderId">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Set New Status <span class="text-danger">*</span></label>
                        <select name="new_status" id="newStatusSelect" class="form-select" required>
                            <option value="">&#8212; Select a status &#8212;</option>
                            <option value="pending">Pending</option>
                            <option value="accepted">Accepted</option>
                            <option value="preparing">Preparing</option>
                            <option value="out_for_delivery">Out for Delivery</option>
                            <option value="delivered">Delivered</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold small">Reason for Intervention <span class="text-danger">*</span></label>
                        <textarea
                            name="reason"
                            id="interventionReason"
                            class="form-control"
                            rows="3"
                            placeholder="e.g. Order stuck in Preparing for 3+ hours — force-cancelling after customer complaint."
                            required
                            maxlength="300"
                            style="border-radius:10px; resize:none;"
                        ></textarea>
                        <div class="form-text small text-muted">Max 300 characters. Reason is logged and sent to the customer.</div>
                    </div>
                    <div class="d-flex gap-2 justify-content-end">
                        <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger px-4 fw-bold">
                            <i class="bi bi-shield-check me-1"></i>Apply Intervention
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.intervene-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        document.getElementById('modalOrderId').value          = this.dataset.orderId;
        document.getElementById('modalOrderNumber').textContent = this.dataset.orderNumber;
        document.getElementById('modalCurrentStatus').textContent = this.dataset.currentStatus.replace(/_/g, ' ');
        document.getElementById('newStatusSelect').value       = '';
        document.getElementById('interventionReason').value    = '';
    });
});
</script>


