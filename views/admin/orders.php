<?php /* Phase 9: Admin — Platform-Wide Orders Management */ ?>
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
                                        <span class="fw-bold">₹<?= number_format((float)$order['total_amount'], 2) ?></span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $order['payment_status'] === 'completed' ? 'success' : 'warning' ?> bg-opacity-15 text-<?= $order['payment_status'] === 'completed' ? 'success' : 'warning' ?> fw-semibold">
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
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
