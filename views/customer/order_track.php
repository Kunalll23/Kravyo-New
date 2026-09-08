<!-- Phase 6 + Phase 9: Order Tracking Timeline Page -->
<?php
// Phase 9: Check if customer already reviewed this order
require_once APP_PATH . '/models/Review.php';
$_reviewModel   = new Review();
$_existingReview = $_reviewModel->findByOrderId((int) $order['id']);
?>
<section class="py-4">
    <div class="container">
        <!-- Order Header -->
        <div class="order-track-header mb-4">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <nav aria-label="breadcrumb" class="mb-2">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="<?= url('/orders/history') ?>" class="text-decoration-none">My Orders</a></li>
                            <li class="breadcrumb-item active">#<?= sanitize($order['order_number']) ?></li>
                        </ol>
                    </nav>
                    <h2 class="fw-800 mb-1">Order #<?= sanitize($order['order_number']) ?></h2>
                    <p class="text-muted mb-0">
                        <i class="bi bi-clock me-1"></i> Placed on <?= date('M d, Y \a\t h:i A', strtotime($order['created_at'])) ?>
                        <span class="mx-2">•</span>
                        <i class="bi bi-shop me-1"></i> <?= sanitize($order['kitchen_name']) ?>
                    </p>
                </div>
                <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                    <?php
                    $statusColors = [
                        'pending' => 'warning', 'accepted' => 'info', 'preparing' => 'primary',
                        'out_for_delivery' => 'success', 'delivered' => 'success', 'cancelled' => 'danger'
                    ];
                    $statusColor = $statusColors[$order['order_status']] ?? 'secondary';
                    $statusLabel = ucwords(str_replace('_', ' ', $order['order_status']));
                    ?>
                    <span class="badge bg-<?= $statusColor ?> px-3 py-2 fs-6 rounded-pill">
                        <?= $statusLabel ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Timeline -->
            <div class="col-lg-8">
                <?php if ($order['order_status'] === 'cancelled'): ?>
                    <!-- Cancelled State -->
                    <div class="order-cancelled-card mb-4">
                        <div class="d-flex align-items-center gap-3">
                            <div class="cancelled-icon">
                                <i class="bi bi-x-circle-fill"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-1 text-danger">Order Cancelled</h5>
                                <p class="mb-0 text-muted">This order has been cancelled and will not be processed.</p>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Status Timeline -->
                    <div class="order-timeline mb-4">
                        <?php
                        $statusOrder = ['pending', 'accepted', 'preparing', 'out_for_delivery', 'delivered'];
                        $currentIndex = array_search($order['order_status'], $statusOrder);
                        ?>

                        <?php foreach ($statusTimeline as $statusKey => $step): ?>
                            <?php
                            $stepIndex = array_search($statusKey, $statusOrder);
                            $isCompleted = $stepIndex < $currentIndex;
                            $isActive = $stepIndex === $currentIndex;
                            $isPending = $stepIndex > $currentIndex;
                            $stepClass = $isCompleted ? 'completed' : ($isActive ? 'active' : 'pending');
                            ?>
                            <div class="timeline-step <?= $stepClass ?>">
                                <div class="timeline-node">
                                    <?php if ($isCompleted): ?>
                                        <i class="bi bi-check-lg"></i>
                                    <?php else: ?>
                                        <i class="bi <?= $step['icon'] ?>"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="timeline-content">
                                    <h6 class="fw-bold mb-0"><?= $step['label'] ?></h6>
                                    <p class="small text-muted mb-0"><?= $step['desc'] ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Order Items -->
                <div class="checkout-section">
                    <h5 class="checkout-section-title">
                        <i class="bi bi-bag me-2 text-warning"></i>Order Items
                    </h5>

                    <?php foreach ($orderItems as $item): ?>
                        <div class="order-track-item d-flex align-items-center gap-3 mb-3">
                            <div class="order-track-item-img">
                                <?php if (!empty($item['image'])): ?>
                                    <img src="<?= UPLOAD_URL . '/dishes/' . $item['image'] ?>"
                                         alt="<?= sanitize($item['item_name']) ?>">
                                <?php else: ?>
                                    <div class="order-track-item-placeholder">
                                        <i class="bi bi-egg-fried"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="fw-bold mb-1">
                                    <?php if ($item['is_veg']): ?>
                                        <i class="bi bi-circle-fill text-success me-1" style="font-size:0.5rem;"></i>
                                    <?php else: ?>
                                        <i class="bi bi-circle-fill text-danger me-1" style="font-size:0.5rem;"></i>
                                    <?php endif; ?>
                                    <?= sanitize($item['item_name']) ?>
                                </h6>
                                <div class="d-flex gap-1">
                                    <span class="cart-custom-tag">🌶️ <?= sanitize($item['spice_level']) ?></span>
                                    <span class="cart-custom-tag">🫒 <?= sanitize($item['oil_level']) ?></span>
                                    <?php if ($item['is_jain']): ?>
                                        <span class="cart-custom-tag cart-custom-jain">🌿 Jain</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="small text-muted"><?= format_currency($item['unit_price']) ?> × <?= $item['quantity'] ?></div>
                                <div class="fw-bold"><?= format_currency($item['subtotal']) ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Phase 9: Rate & Review (only when delivered) -->
                <?php if ($order['order_status'] === 'delivered'): ?>
                    <div class="checkout-section mt-4" id="reviewSection">
                        <h5 class="checkout-section-title">
                            <i class="bi bi-star me-2 text-warning"></i>Rate Your Experience
                        </h5>

                        <?php if ($_existingReview): ?>
                            <!-- Already reviewed -->
                            <div class="d-flex align-items-start gap-3 p-3 bg-light rounded-3">
                                <div class="text-warning fs-4">
                                    <?php for ($s = 1; $s <= 5; $s++): ?>
                                        <i class="bi bi-star<?= $s <= (int)$_existingReview['rating'] ? '-fill' : '' ?>"></i>
                                    <?php endfor; ?>
                                </div>
                                <div>
                                    <p class="fw-semibold mb-1">Your review has been submitted — thank you! ✅</p>
                                    <?php if (!empty($_existingReview['review_text'])): ?>
                                        <p class="text-muted small mb-0">"<?= sanitize($_existingReview['review_text']) ?>"</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <form action="<?= url('/review/submit') ?>" method="POST" id="reviewForm">
                                <?= csrf_field() ?>
                                <input type="hidden" name="order_id" value="<?= (int) $order['id'] ?>">

                                <!-- Star Rating -->
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">How was your meal? <span class="text-danger">*</span></label>
                                    <div class="star-rating-input d-flex gap-1" id="starRatingInput">
                                        <?php for ($s = 5; $s >= 1; $s--): ?>
                                            <input type="radio" id="star<?= $s ?>" name="rating" value="<?= $s ?>" class="visually-hidden" required>
                                            <label for="star<?= $s ?>" class="star-label fs-2 text-muted" title="<?= $s ?> star<?= $s > 1 ? 's' : '' ?>">
                                                <i class="bi bi-star-fill" style="cursor:pointer;"></i>
                                            </label>
                                        <?php endfor; ?>
                                    </div>
                                    <div class="form-text" id="ratingText">Click to rate</div>
                                </div>

                                <!-- Review Text -->
                                <div class="mb-3">
                                    <label for="review_text" class="form-label fw-semibold">Share your experience (optional)</label>
                                    <textarea class="form-control" id="review_text" name="review_text"
                                              rows="3" maxlength="500"
                                              placeholder="The food was amazing! Fresh, tasty and delivered on time..."></textarea>
                                </div>

                                <button type="submit" class="btn btn-kravyo-primary">
                                    <i class="bi bi-send me-2"></i>Submit Review
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Order Details Sidebar -->
            <div class="col-lg-4">
                <div class="cart-summary-card mb-4">
                    <h5 class="fw-bold mb-3"><i class="bi bi-receipt me-2"></i>Payment Details</h5>

                    <div class="d-flex justify-content-between small mb-2">
                        <span class="text-muted">Subtotal</span>
                        <span class="fw-600"><?= format_currency($order['total_amount']) ?></span>
                    </div>
                    <div class="d-flex justify-content-between small mb-2">
                        <span class="text-muted">Delivery</span>
                        <span class="fw-600 text-success">Free</span>
                    </div>

                    <hr class="my-2">

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="fw-bold">Total</span>
                        <span class="fw-800 fs-5"><?= format_currency($order['total_amount']) ?></span>
                    </div>

                    <div class="d-flex justify-content-between small mb-2">
                        <span class="text-muted">Payment Method</span>
                        <span class="fw-600"><?= strtoupper($order['payment_method']) ?></span>
                    </div>
                    <div class="d-flex justify-content-between small">
                        <span class="text-muted">Payment Status</span>
                        <span class="badge bg-<?= $order['payment_status'] === 'completed' ? 'success' : 'warning' ?> text-capitalize">
                            <?= $order['payment_status'] ?>
                        </span>
                    </div>
                </div>

                <!-- Delivery Address -->
                <div class="checkout-section mb-4">
                    <h6 class="fw-bold mb-2"><i class="bi bi-geo-alt me-2 text-danger"></i>Delivery Address</h6>
                    <p class="small mb-0 fw-600"><?= sanitize($order['street_address']) ?></p>
                    <?php if (!empty($order['landmark'])): ?>
                        <p class="small mb-0 text-muted">Near: <?= sanitize($order['landmark']) ?></p>
                    <?php endif; ?>
                    <p class="small mb-0 text-muted">
                        <?= sanitize($order['delivery_city']) ?> — <?= sanitize($order['delivery_pincode']) ?>
                    </p>
                </div>

                <?php if (!empty($order['special_instructions'])): ?>
                    <div class="checkout-section mb-4">
                        <h6 class="fw-bold mb-2"><i class="bi bi-chat-left-text me-2 text-primary"></i>Special Instructions</h6>
                        <p class="small mb-0 text-muted"><?= nl2br(sanitize($order['special_instructions'])) ?></p>
                    </div>
                <?php endif; ?>

                <!-- Cancel Button (only if pending) -->
                <?php if ($order['order_status'] === ORDER_STATUS_PENDING): ?>
                    <form method="POST" action="<?= url('/order/cancel') ?>" id="cancelOrderForm">
                        <?= csrf_field() ?>
                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                        <button type="submit" class="btn btn-outline-danger w-100"
                                onclick="return confirm('Are you sure you want to cancel this order?');">
                            <i class="bi bi-x-circle me-2"></i> Cancel Order
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<style>
/* Phase 9: Star rating input */
.star-rating-input {
    flex-direction: row-reverse;
    justify-content: flex-end;
}
.star-rating-input input:checked ~ label .bi,
.star-rating-input label:hover ~ label .bi,
.star-rating-input label:hover .bi {
    color: #f59e0b !important;
}
.star-label { transition: color 0.15s ease; }
</style>

<script>
// Star rating label feedback
const ratingLabels = {1:'Poor',2:'Fair',3:'Good',4:'Great',5:'Excellent!'};
document.querySelectorAll('#starRatingInput input').forEach(radio => {
    radio.addEventListener('change', () => {
        document.getElementById('ratingText').textContent =
            ratingLabels[radio.value] + ' (' + radio.value + ' star' + (radio.value > 1 ? 's' : '') + ')';
        document.getElementById('ratingText').className = 'form-text text-warning fw-semibold';
    });
});
</script>

<section class="py-4">
    <div class="container">
        <!-- Order Header -->
        <div class="order-track-header mb-4">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <nav aria-label="breadcrumb" class="mb-2">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="<?= url('/orders/history') ?>" class="text-decoration-none">My Orders</a></li>
                            <li class="breadcrumb-item active">#<?= sanitize($order['order_number']) ?></li>
                        </ol>
                    </nav>
                    <h2 class="fw-800 mb-1">Order #<?= sanitize($order['order_number']) ?></h2>
                    <p class="text-muted mb-0">
                        <i class="bi bi-clock me-1"></i> Placed on <?= date('M d, Y \a\t h:i A', strtotime($order['created_at'])) ?>
                        <span class="mx-2">•</span>
                        <i class="bi bi-shop me-1"></i> <?= sanitize($order['kitchen_name']) ?>
                    </p>
                </div>
                <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                    <?php
                    $statusColors = [
                        'pending' => 'warning', 'accepted' => 'info', 'preparing' => 'primary',
                        'out_for_delivery' => 'success', 'delivered' => 'success', 'cancelled' => 'danger'
                    ];
                    $statusColor = $statusColors[$order['order_status']] ?? 'secondary';
                    $statusLabel = ucwords(str_replace('_', ' ', $order['order_status']));
                    ?>
                    <span class="badge bg-<?= $statusColor ?> px-3 py-2 fs-6 rounded-pill">
                        <?= $statusLabel ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Timeline -->
            <div class="col-lg-8">
                <?php if ($order['order_status'] === 'cancelled'): ?>
                    <!-- Cancelled State -->
                    <div class="order-cancelled-card mb-4">
                        <div class="d-flex align-items-center gap-3">
                            <div class="cancelled-icon">
                                <i class="bi bi-x-circle-fill"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-1 text-danger">Order Cancelled</h5>
                                <p class="mb-0 text-muted">This order has been cancelled and will not be processed.</p>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Status Timeline -->
                    <div class="order-timeline mb-4">
                        <?php
                        $statusOrder = ['pending', 'accepted', 'preparing', 'out_for_delivery', 'delivered'];
                        $currentIndex = array_search($order['order_status'], $statusOrder);
                        ?>

                        <?php foreach ($statusTimeline as $statusKey => $step): ?>
                            <?php
                            $stepIndex = array_search($statusKey, $statusOrder);
                            $isCompleted = $stepIndex < $currentIndex;
                            $isActive = $stepIndex === $currentIndex;
                            $isPending = $stepIndex > $currentIndex;
                            $stepClass = $isCompleted ? 'completed' : ($isActive ? 'active' : 'pending');
                            ?>
                            <div class="timeline-step <?= $stepClass ?>">
                                <div class="timeline-node">
                                    <?php if ($isCompleted): ?>
                                        <i class="bi bi-check-lg"></i>
                                    <?php else: ?>
                                        <i class="bi <?= $step['icon'] ?>"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="timeline-content">
                                    <h6 class="fw-bold mb-0"><?= $step['label'] ?></h6>
                                    <p class="small text-muted mb-0"><?= $step['desc'] ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Order Items -->
                <div class="checkout-section">
                    <h5 class="checkout-section-title">
                        <i class="bi bi-bag me-2 text-warning"></i>Order Items
                    </h5>

                    <?php foreach ($orderItems as $item): ?>
                        <div class="order-track-item d-flex align-items-center gap-3 mb-3">
                            <div class="order-track-item-img">
                                <?php if (!empty($item['image'])): ?>
                                    <img src="<?= UPLOAD_URL . '/dishes/' . $item['image'] ?>"
                                         alt="<?= sanitize($item['item_name']) ?>">
                                <?php else: ?>
                                    <div class="order-track-item-placeholder">
                                        <i class="bi bi-egg-fried"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="fw-bold mb-1">
                                    <?php if ($item['is_veg']): ?>
                                        <i class="bi bi-circle-fill text-success me-1" style="font-size:0.5rem;"></i>
                                    <?php else: ?>
                                        <i class="bi bi-circle-fill text-danger me-1" style="font-size:0.5rem;"></i>
                                    <?php endif; ?>
                                    <?= sanitize($item['item_name']) ?>
                                </h6>
                                <div class="d-flex gap-1">
                                    <span class="cart-custom-tag">🌶️ <?= sanitize($item['spice_level']) ?></span>
                                    <span class="cart-custom-tag">🫒 <?= sanitize($item['oil_level']) ?></span>
                                    <?php if ($item['is_jain']): ?>
                                        <span class="cart-custom-tag cart-custom-jain">🌿 Jain</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="small text-muted"><?= format_currency($item['unit_price']) ?> × <?= $item['quantity'] ?></div>
                                <div class="fw-bold"><?= format_currency($item['subtotal']) ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Order Details Sidebar -->
            <div class="col-lg-4">
                <div class="cart-summary-card mb-4">
                    <h5 class="fw-bold mb-3"><i class="bi bi-receipt me-2"></i>Payment Details</h5>

                    <div class="d-flex justify-content-between small mb-2">
                        <span class="text-muted">Subtotal</span>
                        <span class="fw-600"><?= format_currency($order['total_amount']) ?></span>
                    </div>
                    <div class="d-flex justify-content-between small mb-2">
                        <span class="text-muted">Delivery</span>
                        <span class="fw-600 text-success">Free</span>
                    </div>

                    <hr class="my-2">

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="fw-bold">Total</span>
                        <span class="fw-800 fs-5"><?= format_currency($order['total_amount']) ?></span>
                    </div>

                    <div class="d-flex justify-content-between small mb-2">
                        <span class="text-muted">Payment Method</span>
                        <span class="fw-600"><?= strtoupper($order['payment_method']) ?></span>
                    </div>
                    <div class="d-flex justify-content-between small">
                        <span class="text-muted">Payment Status</span>
                        <span class="badge bg-<?= $order['payment_status'] === 'completed' ? 'success' : 'warning' ?> text-capitalize">
                            <?= $order['payment_status'] ?>
                        </span>
                    </div>
                </div>

                <!-- Delivery Address -->
                <div class="checkout-section mb-4">
                    <h6 class="fw-bold mb-2"><i class="bi bi-geo-alt me-2 text-danger"></i>Delivery Address</h6>
                    <p class="small mb-0 fw-600"><?= sanitize($order['street_address']) ?></p>
                    <?php if (!empty($order['landmark'])): ?>
                        <p class="small mb-0 text-muted">Near: <?= sanitize($order['landmark']) ?></p>
                    <?php endif; ?>
                    <p class="small mb-0 text-muted">
                        <?= sanitize($order['delivery_city']) ?> — <?= sanitize($order['delivery_pincode']) ?>
                    </p>
                </div>

                <?php if (!empty($order['special_instructions'])): ?>
                    <div class="checkout-section mb-4">
                        <h6 class="fw-bold mb-2"><i class="bi bi-chat-left-text me-2 text-primary"></i>Special Instructions</h6>
                        <p class="small mb-0 text-muted"><?= nl2br(sanitize($order['special_instructions'])) ?></p>
                    </div>
                <?php endif; ?>

                <!-- Cancel Button (only if pending) -->
                <?php if ($order['order_status'] === ORDER_STATUS_PENDING): ?>
                    <form method="POST" action="<?= url('/order/cancel') ?>" id="cancelOrderForm">
                        <?= csrf_field() ?>
                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                        <button type="submit" class="btn btn-outline-danger w-100"
                                onclick="return confirm('Are you sure you want to cancel this order?');">
                            <i class="bi bi-x-circle me-2"></i> Cancel Order
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
