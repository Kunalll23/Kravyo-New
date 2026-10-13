<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">Delivery History</h2>
        <a href="<?= url('/delivery/dashboard') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Dashboard
        </a>
    </div>

    <?php if (empty($deliveries)): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center p-5">
                <i class="bi bi-clock-history text-muted opacity-25" style="font-size: 4rem;"></i>
                <h5 class="mt-3 fw-bold text-muted">No delivery history</h5>
                <p class="text-muted mb-0">Completed and failed deliveries will appear here.</p>
            </div>
        </div>
    <?php else: ?>
        <div class="card kravyo-card border-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Order Details</th>
                            <th>Locations</th>
                            <th>Status & Time</th>
                            <th class="text-end pe-4">Earnings</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($deliveries as $del): ?>
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold">#<?= sanitize($del['order_number']) ?></div>
                                <div class="small text-muted"><?= date('d M Y', strtotime($del['created_at'])) ?></div>
                            </td>
                            <td>
                                <div class="small">
                                    <i class="bi bi-shop text-primary me-1"></i> <?= sanitize($del['kitchen_name']) ?><br>
                                    <i class="bi bi-geo-alt-fill text-success me-1"></i> <?= sanitize($del['customer_name']) ?>
                                </div>
                            </td>
                            <td>
                                <?php if ($del['delivery_status'] === 'delivered'): ?>
                                    <span class="badge bg-success rounded-pill mb-1">Delivered</span>
                                <?php else: ?>
                                    <span class="badge bg-danger rounded-pill mb-1" title="<?= sanitize($del['delivery_failed_reason']) ?>">Failed</span>
                                <?php endif; ?>
                                <div class="small text-muted">
                                    <?= $del['delivery_completed_at'] ? date('h:i A', strtotime($del['delivery_completed_at'])) : '-' ?>
                                </div>
                            </td>
                            <td class="text-end pe-4">
                                <?php if ($del['delivery_status'] === 'delivered'): ?>
                                    <div class="fw-bold text-success">+ ₹<?= number_format($del['total_amount'], 2) ?></div>
                                <?php else: ?>
                                    <div class="fw-bold text-muted">₹0.00</div>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>
