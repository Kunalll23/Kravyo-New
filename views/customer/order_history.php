<!-- Phase 6: Customer Order History Page -->
<section class="kitchen-browse-hero">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <h1 class="fw-800 text-white mb-2">
                    <i class="bi bi-bag-check me-2 text-warning"></i>My Orders
                </h1>
                <p class="text-white-50 mb-0">Track and manage all your food orders</p>
            </div>
            <div class="col-lg-5 text-lg-end mt-3 mt-lg-0">
                <span class="badge bg-white text-dark px-3 py-2 fs-6 rounded-pill">
                    <?= count($orders) ?> Order<?= count($orders) !== 1 ? 's' : '' ?>
                </span>
            </div>
        </div>
    </div>
</section>

<section class="py-4">
    <div class="container">
        <?php if (empty($orders)): ?>
            <div class="empty-state text-center py-5">
                <div class="empty-state-icon mb-3">
                    <i class="bi bi-bag-x"></i>
                </div>
                <h4 class="fw-bold text-muted">No Orders Yet</h4>
                <p class="text-muted mb-4">You haven't placed any orders yet. Start exploring delicious home-cooked meals!</p>
                <a href="<?= url('/menu') ?>" class="btn btn-kravyo-primary">
                    <i class="bi bi-egg-fried me-2"></i> Browse Dishes
                </a>
            </div>
        <?php else: ?>
            <div class="row g-3">
                <?php foreach ($orders as $order): ?>
                    <?php
                    $statusColors = [
                        'pending' => 'warning', 'accepted' => 'info', 'preparing' => 'primary',
                        'out_for_delivery' => 'success', 'delivered' => 'success', 'cancelled' => 'danger'
                    ];
                    $statusIcons = [
                        'pending' => 'bi-clock', 'accepted' => 'bi-check2-circle', 'preparing' => 'bi-fire',
                        'out_for_delivery' => 'bi-bicycle', 'delivered' => 'bi-house-check', 'cancelled' => 'bi-x-circle'
                    ];
                    $color = $statusColors[$order['order_status']] ?? 'secondary';
                    $icon = $statusIcons[$order['order_status']] ?? 'bi-circle';
                    $label = ucwords(str_replace('_', ' ', $order['order_status']));
                    ?>
                    <div class="col-lg-6">
                        <a href="<?= url('/order/track/' . $order['id']) ?>" class="text-decoration-none">
                            <div class="order-history-card">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="fw-bold mb-0 text-dark">#<?= sanitize($order['order_number']) ?></h6>
                                        <small class="text-muted">
                                            <i class="bi bi-clock me-1"></i><?= date('M d, Y — h:i A', strtotime($order['created_at'])) ?>
                                        </small>
                                    </div>
                                    <span class="badge bg-<?= $color ?> rounded-pill px-3 py-2">
                                        <i class="bi <?= $icon ?> me-1"></i> <?= $label ?>
                                    </span>
                                </div>

                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <i class="bi bi-shop text-warning"></i>
                                    <span class="small fw-600 text-dark"><?= sanitize($order['kitchen_name']) ?></span>
                                </div>

                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small text-muted">
                                        <i class="bi bi-bag me-1"></i>
                                        <?= (int) ($order['item_count'] ?? 0) ?> item<?= ($order['item_count'] ?? 0) != 1 ? 's' : '' ?>
                                    </span>
                                    <span class="fw-bold text-dark fs-6"><?= format_currency($order['total_amount']) ?></span>
                                </div>

                                <div class="order-history-arrow">
                                    <i class="bi bi-chevron-right"></i>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
