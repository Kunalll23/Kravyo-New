<!-- Phase 6: Chef Incoming Orders Dashboard -->


<section class="py-4" id="chefOrdersLive" data-live-refresh>
    <div class="container">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
            <div class="d-flex align-items-center gap-3">
                <h2 class="fw-800 mb-0">
                    <i class="bi bi-receipt-cutoff me-2 text-warning"></i>Incoming Orders
                </h2>
                <?php if (($statusCounts['pending'] ?? 0) > 0): ?>
                    <span class="badge bg-danger px-3 py-2 fs-6 rounded-pill pulse-badge">
                        <?= $statusCounts['pending'] ?> New Order<?= $statusCounts['pending'] > 1 ? 's' : '' ?>!
                    </span>
                <?php endif; ?>
            </div>

            <!-- Order Search Bar -->
            <form action="<?= url('/chef/orders') ?>" method="GET" class="d-flex align-items-center gap-2 m-0" style="min-width: 280px; max-width: 440px;">
                <?php if (!empty($statusFilter) && $statusFilter !== 'all' && empty($search)): ?>
                    <input type="hidden" name="status" value="<?= sanitize($statusFilter) ?>">
                <?php endif; ?>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control form-control-sm border-start-0" 
                           placeholder="Search order #, customer, phone..." 
                           value="<?= sanitize($search ?? '') ?>">
                </div>
                <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3">Search</button>
                <?php if (!empty($search)): ?>
                    <a href="<?= url('/chef/orders') ?>" class="btn btn-sm btn-outline-secondary rounded-pill px-2 text-nowrap" title="Reset Search">
                        <i class="bi bi-x-circle"></i>
                    </a>
                <?php endif; ?>
            </form>
        </div>

        <?php if (!empty($search)): ?>
            <div class="alert alert-info py-2 px-3 d-flex align-items-center justify-content-between rounded-pill mb-3">
                <span class="small">
                    <i class="bi bi-funnel-fill me-1"></i> Showing orders matching: <strong>"<?= sanitize($search) ?>"</strong> (<?= count($orders) ?> found)
                </span>
                <a href="<?= url('/chef/orders') ?>" class="btn btn-sm btn-outline-secondary py-0 px-2 rounded-pill" style="font-size:0.75rem;">Clear Filter</a>
            </div>
        <?php endif; ?>

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
                           href="<?= url('/chef/orders?status=' . $key . (!empty($search) ? '&search=' . urlencode($search) : '')) ?>">
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
                    
                    $exactTime = date('h:i A', strtotime($order['created_at']));
                    ?>
                    <?php
                    $isTargetMatch = (!empty($search) && (stripos($order['order_number'], $search) !== false || (string)$order['id'] === $search));
                    ?>
                    <div class="col-12" id="order-<?= $order['id'] ?>">
                        <div class="chef-order-card <?= $order['order_status'] === 'pending' ? 'order-new' : '' ?> <?= $isTargetMatch ? 'border-2 border-warning shadow-lg' : '' ?>"
                             <?= $isTargetMatch ? 'style="border: 2.5px solid #f59e0b !important; background-color: #fffdf5 !important;"' : '' ?>>
                            <!-- Order Header -->
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <h6 class="fw-bold mb-0">#<?= sanitize($order['order_number']) ?></h6>
                                        <?php if ($isTargetMatch): ?>
                                            <span class="badge bg-warning text-dark px-2 py-1 rounded-pill small">
                                                <i class="bi bi-pin-angle-fill me-1"></i> Target Order
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <small class="text-muted fw-bold">
                                        <i class="bi bi-clock me-1"></i><?= $exactTime ?> <span class="text-secondary fw-normal">(<?= $timeAgo ?>)</span>
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
                                            <?php if (!empty($item['is_zero_waste'])): ?>
                                                <span class="badge rounded-pill ms-1 px-1" style="font-size:0.65rem; background-color: #ecfdf5; color: #047857; border: 1px solid #a7f3d0;">
                                                    <i class="bi bi-recycle"></i> ZW Deal
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted ms-1">
                                                    (🌶️<?= $item['spice_level'][0] ?> 🫒<?= $item['oil_level'][0] ?><?= $item['is_jain'] ? ' 🌿J' : '' ?>)
                                                </span>
                                            <?php endif; ?>
                                        </span>
                                        <span class="fw-600"><?= format_currency($item['subtotal']) ?></span>
                                    </div>
                                    <?php if (!empty($item['item_notes'])): ?>
                                        <div class="small text-danger fst-italic mb-2 ps-3" style="font-size: 0.75rem;">
                                            <i class="bi bi-chat-text me-1"></i> Note: "<?= sanitize($item['item_notes']) ?>"
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>

                            <!-- Dispatch / Delivery Partner Status -->
                            <?php if (($order['dispatch_status'] ?? '') === 'broadcasting'): ?>
                                <div class="mb-3 px-2 py-2 bg-warning bg-opacity-10 rounded small border border-warning text-warning-emphasis">
                                    <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                                    <strong>Finding rider...</strong> (Round <?= $order['dispatch_round'] ?? 1 ?>)
                                    
                                    <?php if (!empty($order['timeline'])): ?>
                                        <div class="mt-2 pt-2 border-top border-warning text-dark" style="max-height: 150px; overflow-y: auto;">
                                            <div class="fw-bold mb-1" style="font-size:0.75rem;">Live Broadcast Pings:</div>
                                            <?php 
                                            // Deduplicate timeline by latest round
                                            $latestOffers = [];
                                            foreach($order['timeline'] as $t) {
                                                if (!isset($latestOffers[$t['partner_name']]) || $t['round'] > $latestOffers[$t['partner_name']]['round']) {
                                                    $latestOffers[$t['partner_name']] = $t;
                                                }
                                            }
                                            foreach ($latestOffers as $t): 
                                                $badge = 'bg-secondary';
                                                if ($t['status'] === 'accepted') $badge = 'bg-success';
                                                elseif ($t['status'] === 'declined') $badge = 'bg-danger';
                                                elseif ($t['status'] === 'pending') $badge = 'bg-warning text-dark';
                                                elseif ($t['status'] === 'lost') $badge = 'bg-light text-muted border';
                                                elseif ($t['status'] === 'expired') $badge = 'bg-dark text-white';
                                            ?>
                                                <div class="d-flex justify-content-between align-items-center mb-1" style="font-size: 0.75rem;">
                                                    <span><?= sanitize($t['partner_name']) ?> <span class="text-muted">(Round <?= $t['round'] ?>)</span></span>
                                                    <span class="badge <?= $badge ?>"><?= $t['status'] ?></span>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php elseif (($order['dispatch_status'] ?? '') === 'no_rider'): ?>
                                <div class="mb-3 px-2 py-2 bg-danger bg-opacity-10 rounded small border border-danger text-danger d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="bi bi-exclamation-triangle me-2"></i>
                                        <strong>No Rider Found.</strong> Admin alerted.
                                    </div>
                                    <?php if (in_array($order['order_status'], [ORDER_STATUS_PREPARING, ORDER_STATUS_READY_FOR_PICKUP])): ?>
                                        <form method="POST" action="<?= url('/chef/order/status') ?>" class="d-inline mb-0">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                            <button type="submit" name="new_status" value="request_rider" class="btn btn-warning btn-sm py-0 px-2 fw-bold" style="font-size:0.75rem;">
                                                <i class="bi bi-arrow-clockwise me-1"></i> Retry
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            <?php elseif (!empty($order['delivery_partner_id'])): ?>
                                <div class="mb-3 px-2 py-1 bg-light rounded small border d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="bi bi-bicycle text-primary me-2"></i>
                                        <strong>Rider:</strong> <?= sanitize($order['delivery_partner_name'] ?? 'Assigned') ?>
                                        <a href="tel:<?= sanitize($order['delivery_partner_phone'] ?? '') ?>" class="ms-2 text-success text-decoration-none">
                                            <i class="bi bi-telephone"></i> <?= sanitize($order['delivery_partner_phone'] ?? '') ?>
                                        </a>
                                    </div>
                                    <?php if (!in_array($order['order_status'], ['delivered', 'cancelled'])): ?>
                                        <a href="<?= url('/chef/order/track/' . $order['id']) ?>" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size: 0.75rem;">
                                            <i class="bi bi-geo-alt-fill"></i> Track
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

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
                                        <form method="POST" action="<?= url('/chef/order/status') ?>" class="d-inline d-flex gap-2">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                            
                                            <?php if (in_array($order['dispatch_status'] ?? 'none', ['none', 'no_rider'])): ?>
                                                <button type="submit" name="new_status" value="request_rider" class="btn btn-warning btn-sm fw-bold">
                                                    <i class="bi bi-bicycle me-1"></i> Request Rider
                                                </button>
                                                <button type="submit" name="new_status" value="<?= ORDER_STATUS_READY_FOR_PICKUP ?>" class="btn btn-outline-success btn-sm" title="Skip early request and mark ready now">
                                                    <i class="bi bi-box-seam"></i> Ready
                                                </button>
                                            <?php else: ?>
                                                <button type="submit" name="new_status" value="<?= ORDER_STATUS_READY_FOR_PICKUP ?>" class="btn btn-success btn-sm fw-bold w-100">
                                                    <i class="bi bi-box-seam me-1"></i> Mark Food Packed & Ready
                                                </button>
                                            <?php endif; ?>
                                        </form>

                                    <?php elseif ($order['order_status'] === ORDER_STATUS_READY_FOR_PICKUP): ?>
                                        <!-- Waiting for rider to pick up -->
                                        <span class="badge border border-warning text-warning px-2 py-1">
                                            Awaiting Pickup
                                        </span>

                                    <?php elseif ($order['order_status'] === ORDER_STATUS_OUT_FOR_DELIVERY): ?>
                                        <!-- Rider marked it picked up -->
                                        <span class="badge border border-success text-success px-2 py-1">
                                            Out for Delivery
                                        </span>

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

<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentPendingCount = document.querySelectorAll('.chef-order-card.order-new').length;

    setInterval(() => {
        fetch(window.location.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(res => res.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                
                const newPendingCount = doc.querySelectorAll('.chef-order-card.order-new').length;
                
                if (newPendingCount > currentPendingCount) {
                    // Play sound
                    try {
                        const audio = new Audio('https://assets.mixkit.co/sfx/preview/mixkit-software-interface-start-2574.mp3');
                        audio.play();
                    } catch (e) {}
                    
                    // Reload to ensure all state is clean
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    // Update content dynamically to reflect status changes without full reload
                    const currentContainer = document.querySelector('#chefOrdersLive .container');
                    const newContainer = doc.querySelector('#chefOrdersLive .container');
                    if (currentContainer && newContainer) {
                        currentContainer.innerHTML = newContainer.innerHTML;
                    }
                    currentPendingCount = newPendingCount; // Keep tracking
                }
            })
            .catch(err => console.error("Polling error", err));
    }, 10000); // 10 seconds
});
</script>
