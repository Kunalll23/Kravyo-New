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

    <!-- Status Filter Tabs -->
    <div class="card kravyo-card border-0 mb-3">
        <div class="card-body p-0">
            <div class="d-flex flex-wrap border-bottom px-3">
                <?php
                $tabs = [
                    'all'              => ['label' => 'All',              'color' => 'secondary'],
                    'pending'          => ['label' => 'Pending',          'color' => 'warning'],
                    'accepted'         => ['label' => 'Accepted',         'color' => 'info'],
                    'preparing'        => ['label' => 'Preparing',        'color' => 'primary'],
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
                    <a href="<?= url('/admin/orders?status=' . $tabKey) ?>"
                       class="nav-link px-3 py-3 small fw-semibold border-bottom border-3 <?= $isActive ? 'text-primary border-primary' : 'text-muted border-transparent' ?>">
                        <?= $tab['label'] ?>
                        <span class="badge bg-<?= $isActive ? 'primary' : 'secondary' ?> bg-opacity-<?= $isActive ? '100' : '10' ?> text-<?= $isActive ? 'white' : 'secondary' ?> ms-1"><?= $count ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="mb-3 d-flex justify-content-end">
        <form action="<?= url('/admin/orders') ?>" method="GET" class="d-flex w-100" style="max-width: 400px;">
            <?php if (!empty($statusFilter)): ?>
                <input type="hidden" name="status" value="<?= $statusFilter ?>">
            <?php endif; ?>
            <div class="input-group shadow-sm">
                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Search by Order ID (e.g. 15)" value="<?= sanitize($_GET['search'] ?? '') ?>">
                <button type="submit" class="btn btn-primary px-3">Search</button>
                <?php if (!empty($_GET['search'])): ?>
                    <a href="<?= url('/admin/orders' . (!empty($statusFilter) && $statusFilter !== 'all' ? '?status=' . $statusFilter : '')) ?>" class="btn btn-outline-secondary px-3" title="Clear Search">
                        <i class="bi bi-x-lg"></i>
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Orders Table -->
    <div class="card kravyo-card border-0">
        <div class="card-body p-0">
            <?php if (empty($orders)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-inbox display-3 opacity-25 d-block mb-3"></i>
                    <h5 class="fw-bold">No orders found</h5>
                    <p class="small">No orders match this status filter yet.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Order #</th>
                                <th>Customer</th>
                                <th>Kitchen</th>
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
                                    'out_for_delivery' => 'success',
                                    'delivered'        => 'success',
                                    'cancelled'        => 'danger',
                                ];
                                $sc = $statusColors[$order['order_status']] ?? 'secondary';
                                ?>
                                <tr>
                                    <td class="ps-4">
                                        <span class="fw-semibold text-primary small"><?= sanitize($order['order_number']) ?></span>
                                    </td>
                                    <td>
                                        <span class="small fw-semibold"><?= sanitize($order['customer_name']) ?></span>
                                    </td>
                                    <td>
                                        <span class="small"><?= sanitize($order['kitchen_name']) ?></span>
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
                                        <?php if ($order['order_status'] !== 'delivered' && $order['order_status'] !== 'cancelled'): ?>
                                            <button
                                                class="btn btn-sm btn-outline-danger fw-semibold intervene-btn"
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
