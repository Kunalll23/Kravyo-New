<!-- Phase 6: Chef Incoming Orders Dashboard -->
<?php View::partial('sidebar'); ?>

<section class="py-4">
    <div class="container">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h2 class="fw-800 mb-0">
                <i class="bi bi-receipt-cutoff me-2 text-warning"></i>Incoming Orders
            </h2>
            <?php if (($statusCounts['pending'] ?? 0) > 0): ?>
                <span class="badge bg-danger px-3 py-2 fs-6 rounded-pill pulse-badge">
                    <?= $statusCounts['pending'] ?> New Order<?= $statusCounts['pending'] > 1 ? 's' : '' ?>!
                </span>
            <?php endif; ?>
        </div>

        <!-- Status Filter Tabs -->
        <div class="admin-filter-tabs mb-4">
            <ul class="nav nav-pills">
                <?php
                $tabs = [
                    'all' => ['label' => 'All', 'icon' => 'bi-grid'],
                    'pending' => ['label' => 'New', 'icon' => 'bi-bell'],
                    'accepted' => ['label' => 'Accepted', 'icon' => 'bi-check2-circle'],
                    'preparing' => ['label' => 'Preparing', 'icon' => 'bi-fire'],
                    'out_for_delivery' => ['label' => 'Dispatched', 'icon' => 'bi-bicycle'],
                    'delivered' => ['label' => 'Delivered', 'icon' => 'bi-house-check'],
                    'cancelled' => ['label' => 'Cancelled', 'icon' => 'bi-x-circle'],
                ];
                foreach ($tabs as $key => $tab):
                ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $statusFilter === $key ? 'active' : '' ?>"
                           href="<?= url('/chef/orders?status=' . $key) ?>">
                            <i class="bi <?= $tab['icon'] ?> me-1"></i>
                            <?= $tab['label'] ?>
                            <?php if (($statusCounts[$key] ?? 0) > 0): ?>
                                <span class="badge bg-light text-dark ms-1 rounded-pill"><?= $statusCounts[$key] ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Orders List -->
        <?php if (empty($orders)): ?>
            <div class="empty-state text-center py-5">
                <div class="empty-state-icon mb-3">
                    <i class="bi bi-receipt"></i>
                </div>
                <h4 class="fw-bold text-muted">No Orders Found</h4>
                <p class="text-muted mb-4">
                    <?= $statusFilter === 'all' ? 'No orders have been placed yet.' : 'No ' . ($tabs[$statusFilter]['label'] ?? '') . ' orders right now.' ?>
                </p>
            </div>
        <?php else: ?>
            <div class="row g-3">
                <?php foreach ($orders as $order): ?>
                    <?php
                    $statusColors = [
                        'pending' => 'warning', 'accepted' => 'info', 'preparing' => 'primary',
                        'out_for_delivery' => 'success', 'delivered' => 'success', 'cancelled' => 'danger'
                    ];
                    $color = $statusColors[$order['order_status']] ?? 'secondary';
                    $label = ucwords(str_replace('_', ' ', $order['order_status']));

                    // Time ago calculation
                    $diff = time() - strtotime($order['created_at']);
                    if ($diff < 60) $timeAgo = 'Just now';
                    elseif ($diff < 3600) $timeAgo = floor($diff / 60) . ' min ago';
                    elseif ($diff < 86400) $timeAgo = floor($diff / 3600) . ' hr ago';
                    else $timeAgo = date('M d', strtotime($order['created_at']));
                    ?>
                    <div class="col-lg-6">
                        <div class="chef-order-card <?= $order['order_status'] === 'pending' ? 'order-new' : '' ?>">
                            <!-- Order Header -->
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h6 class="fw-bold mb-0">#<?= sanitize($order['order_number']) ?></h6>
                                    <small class="text-muted">
                                        <i class="bi bi-clock me-1"></i><?= $timeAgo ?>
                                    </small>
                                </div>
                                <span class="badge bg-<?= $color ?> rounded-pill px-3"><?= $label ?></span>
                            </div>

                            <!-- Customer Info -->
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <div class="avatar-circle" style="width:32px;height:32px;font-size:0.8rem;">
                                    <?= strtoupper(substr($order['customer_name'] ?? 'C', 0, 1)) ?>
                                </div>
                                <div>
                                    <span class="small fw-600"><?= sanitize($order['customer_name']) ?></span>
                                    <span class="small text-muted ms-2">
                                        <i class="bi bi-phone me-1"></i><?= sanitize($order['customer_phone']) ?>
                                    </span>
                                </div>
                            </div>

                            <!-- Delivery Address -->
                            <div class="mb-2">
                                <small class="text-muted">
                                    <i class="bi bi-geo-alt me-1"></i>
                                    <?= sanitize($order['street_address']) ?>,
                                    <?= sanitize($order['delivery_city']) ?> — <?= sanitize($order['delivery_pincode']) ?>
                                </small>
                            </div>

                            <!-- Order Items -->
                            <div class="chef-order-items mb-3">
                                <?php foreach ($order['items'] as $item): ?>
                                    <div class="d-flex justify-content-between align-items-center small mb-1">
                                        <span>
                                            <?php if ($item['is_veg']): ?>
                                                <i class="bi bi-circle-fill text-success me-1" style="font-size:0.45rem;"></i>
                                            <?php else: ?>
                                                <i class="bi bi-circle-fill text-danger me-1" style="font-size:0.45rem;"></i>
                                            <?php endif; ?>
                                            <?= sanitize($item['item_name']) ?> × <?= $item['quantity'] ?>
                                            <span class="text-muted ms-1">
                                                (🌶️<?= $item['spice_level'][0] ?> 🫒<?= $item['oil_level'][0] ?><?= $item['is_jain'] ? ' 🌿J' : '' ?>)
                                            </span>
                                        </span>
                                        <span class="fw-600"><?= format_currency($item['subtotal']) ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- Total & Actions -->
                            <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                                <div>
                                    <small class="text-muted">Total</small>
                                    <div class="fw-800 fs-6"><?= format_currency($order['total_amount']) ?></div>
                                </div>

                                <div class="d-flex gap-2">
                                    <?php if ($order['order_status'] === ORDER_STATUS_PENDING): ?>
                                        <!-- Accept / Reject -->
                                        <form method="POST" action="<?= url('/chef/order/status') ?>" class="d-inline">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                            <input type="hidden" name="new_status" value="<?= ORDER_STATUS_CANCELLED ?>">
                                            <button type="submit" class="btn btn-outline-danger btn-sm"
                                                    onclick="return confirm('Reject this order?');">
                                                <i class="bi bi-x-lg"></i> Reject
                                            </button>
                                        </form>
                                        <form method="POST" action="<?= url('/chef/order/status') ?>" class="d-inline">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                            <input type="hidden" name="new_status" value="<?= ORDER_STATUS_ACCEPTED ?>">
                                            <button type="submit" class="btn btn-success btn-sm">
                                                <i class="bi bi-check-lg me-1"></i> Accept
                                            </button>
                                        </form>

                                    <?php elseif ($order['order_status'] === ORDER_STATUS_ACCEPTED): ?>
                                        <form method="POST" action="<?= url('/chef/order/status') ?>">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                            <input type="hidden" name="new_status" value="<?= ORDER_STATUS_PREPARING ?>">
                                            <button type="submit" class="btn btn-primary btn-sm">
                                                <i class="bi bi-fire me-1"></i> Start Preparing
                                            </button>
                                        </form>

                                    <?php elseif ($order['order_status'] === ORDER_STATUS_PREPARING): ?>
                                        <form method="POST" action="<?= url('/chef/order/status') ?>">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                            <input type="hidden" name="new_status" value="<?= ORDER_STATUS_OUT_FOR_DELIVERY ?>">
                                            <button type="submit" class="btn btn-info btn-sm text-white">
                                                <i class="bi bi-bicycle me-1"></i> Out for Delivery
                                            </button>
                                        </form>

                                    <?php elseif ($order['order_status'] === ORDER_STATUS_OUT_FOR_DELIVERY): ?>
                                        <form method="POST" action="<?= url('/chef/order/status') ?>">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                            <input type="hidden" name="new_status" value="<?= ORDER_STATUS_DELIVERED ?>">
                                            <button type="submit" class="btn btn-success btn-sm">
                                                <i class="bi bi-house-check me-1"></i> Mark Delivered
                                            </button>
                                        </form>

                                    <?php elseif ($order['order_status'] === ORDER_STATUS_DELIVERED): ?>
                                        <span class="badge bg-success px-3 py-2">
                                            <i class="bi bi-check-circle me-1"></i> Completed
                                        </span>

                                    <?php elseif ($order['order_status'] === ORDER_STATUS_CANCELLED): ?>
                                        <span class="badge bg-danger px-3 py-2">
                                            <i class="bi bi-x-circle me-1"></i> Cancelled
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
