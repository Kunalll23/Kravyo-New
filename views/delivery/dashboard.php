<div class="container py-4">
    <?php View::partial('flash_messages'); ?>

    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h2 class="fw-bold mb-0">Delivery Dashboard</h2>
            <p class="text-muted mb-0">Welcome back, <?= sanitize($partner['full_name']) ?></p>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <form action="<?= url('/delivery/toggle-status') ?>" method="POST" class="d-inline">
                <?= csrf_field() ?>
                <div class="d-inline-flex align-items-center bg-white border rounded-pill px-4 py-2 shadow-sm" style="transition: all 0.3s ease;">
                    <span class="fw-bold fs-6 me-3 <?= $partner['is_online'] ? 'text-muted opacity-50' : 'text-danger' ?>" style="user-select: none;">Offline</span>
                    
                    <div class="form-check form-switch mb-0 p-0 d-flex align-items-center">
                        <input class="form-check-input m-0 shadow-none <?= $partner['is_online'] ? 'bg-success border-success' : 'bg-secondary border-secondary' ?>" 
                               type="checkbox" 
                               role="switch" 
                               id="statusToggle" 
                               <?= $partner['is_online'] ? 'checked' : '' ?> 
                               onchange="this.form.submit()" 
                               style="width: 3.5rem; height: 1.75rem; cursor: pointer; transition: all 0.3s ease; --bs-form-switch-bg: url('data:image/svg+xml,%3csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%27-4 -4 8 8%27%3e%3ccircle r=%273%27 fill=%27%23fff%27/%3e%3c/svg%3e');">
                    </div>
                    
                    <span class="fw-bold fs-6 ms-3 <?= $partner['is_online'] ? 'text-success' : 'text-muted opacity-50' ?>" style="user-select: none;">Online</span>
                </div>
            </form>
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card kravyo-card border-0 h-100 bg-primary text-white">
                <div class="card-body p-4 d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="bg-white text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                            <i class="bi bi-bicycle fs-4"></i>
                        </div>
                    </div>
                    <div>
                        <h6 class="text-white-50 mb-1">Active Deliveries</h6>
                        <h3 class="fw-bold mb-0"><?= count($activeOrders) ?></h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card kravyo-card border-0 h-100 bg-success text-white">
                <div class="card-body p-4 d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="bg-white text-success rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                            <i class="bi bi-check2-circle fs-4"></i>
                        </div>
                    </div>
                    <div>
                        <h6 class="text-white-50 mb-1">Completed Deliveries</h6>
                        <h3 class="fw-bold mb-0"><?= $completedCount ?></h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card kravyo-card border-0 h-100 bg-warning text-dark">
                <div class="card-body p-4 d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="bg-dark text-warning rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                            <i class="bi bi-currency-rupee fs-4"></i>
                        </div>
                    </div>
                    <div>
                        <h6 class="text-dark-50 mb-1">Total Earnings</h6>
                        <h3 class="fw-bold mb-0">₹<?= number_format($totalEarnings, 2) ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (!$partner['is_online']): ?>
        <div class="alert alert-secondary border-0 shadow-sm d-flex align-items-center" role="alert">
            <i class="bi bi-info-circle-fill text-muted fs-4 me-3"></i>
            <div>
                <strong>You are currently offline.</strong><br>
                Go online to start receiving new delivery assignments from admins.
            </div>
        </div>
    <?php endif; ?>

    <!-- Broadcast Offers Container -->
    <div id="offersContainer" class="mb-4"></div>

    <!-- Active Orders -->
    <h4 class="fw-bold mb-3 mt-4"><i class="bi bi-box-seam me-2"></i> Current Deliveries</h4>
    
    <?php if (empty($activeOrders)): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center p-5">
                <i class="bi bi-bicycle text-muted opacity-25" style="font-size: 4rem;"></i>
                <h5 class="mt-3 fw-bold text-muted">No active deliveries</h5>
                <p class="text-muted mb-0">You don't have any deliveries assigned to you right now.</p>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($activeOrders as $order): ?>
                <?php
                // Determine styling based on status
                $statusColors = [
                    'assigned' => 'primary',
                    'accepted' => 'warning',
                    'picked_up' => 'info',
                    'out_for_delivery' => 'primary'
                ];
                $color = $statusColors[$order['delivery_status']] ?? 'secondary';
                ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 border-0 shadow-sm overflow-hidden" style="border-top: 4px solid var(--bs-<?= $color ?>) !important;">
                        <div class="card-header bg-white border-bottom-0 pt-3 pb-0 d-flex justify-content-between align-items-center">
                            <span class="badge bg-<?= $color ?> rounded-pill px-3 py-2">
                                <?= ucwords(str_replace('_', ' ', $order['delivery_status'])) ?>
                            </span>
                            <small class="text-muted fw-bold">#<?= sanitize($order['order_number']) ?></small>
                        </div>
                        <div class="card-body">
                            <!-- Kitchen Details -->
                            <div class="d-flex align-items-start mb-3 border-bottom pb-3">
                                <div class="bg-light text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                    <i class="bi bi-shop"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 fw-bold text-truncate" style="max-width: 200px;">
                                        Pickup: <?= sanitize($order['kitchen_name']) ?>
                                    </h6>
                                    <p class="text-muted small mb-0 lh-sm">
                                        <?= sanitize($order['kitchen_address']) ?>, <?= sanitize($order['kitchen_city']) ?>
                                    </p>
                                </div>
                            </div>

                            <!-- Customer Details -->
                            <div class="d-flex align-items-start mb-3">
                                <div class="bg-light text-success rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                    <i class="bi bi-geo-alt"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 fw-bold text-truncate" style="max-width: 200px;">
                                        Drop: <?= sanitize($order['customer_name']) ?>
                                    </h6>
                                    <p class="text-muted small mb-0 lh-sm">
                                        <?= sanitize($order['street_address']) ?><br>
                                        <?= sanitize($order['delivery_city']) ?> - <?= sanitize($order['delivery_pincode']) ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-top p-3 text-center">
                            <a href="<?= url('/delivery/order/' . $order['id']) ?>" class="btn btn-<?= $color ?> w-100 fw-bold">
                                View Order Details <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php if ($partner['is_online']): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Check if geolocation is supported
    if ("geolocation" in navigator) {
        console.log("Kravyo GPS Tracker: Started. You are online.");
        
        // Function to send location to server
        function updateLocation(position) {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            const csrfToken = '<?= csrf_token() ?>';
            
            const formData = new URLSearchParams();
            formData.append('_csrf', csrfToken);
            formData.append('latitude', lat);
            formData.append('longitude', lng);
            
            fetch('<?= url('/delivery/location/update') ?>', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    console.log(`Live GPS Synced: ${lat}, ${lng}`);
                }
            })
            .catch(error => console.error('GPS Sync Error:', error));
        }

        function handleError(error) {
            console.warn(`ERROR(${error.code}): ${error.message}`);
        }

        // Send immediately, then every 30 seconds
        navigator.geolocation.getCurrentPosition(updateLocation, handleError, {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 0
        });

        setInterval(function() {
            navigator.geolocation.getCurrentPosition(updateLocation, handleError, {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            });
        }, 30000); // 30 seconds
        
    } else {
        console.error("Geolocation is not supported by this browser.");
    }
});
</script>
<?php endif; ?>

<?php if ($partner['is_online']): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const offersContainer = document.getElementById('offersContainer');
    if (!offersContainer) return;
    
    let activeOffers = new Map();
    let audioPlayed = false;

    function fetchOffers() {
        fetch('<?= url('/delivery/offers') ?>', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.offers) {
                renderOffers(data.offers);
            }
        })
        .catch(err => console.error(err));
    }

    function renderOffers(offers) {
        if (offers.length > 0 && !audioPlayed && typeof kravyoPlayDing === 'function') {
            kravyoPlayDing();
            audioPlayed = true;
        } else if (offers.length === 0) {
            audioPlayed = false;
        }

        let newIds = new Set(offers.map(o => o.order_id));
        
        // Remove offers that are gone
        for (let [id, el] of activeOffers.entries()) {
            if (!newIds.has(id)) {
                el.remove();
                activeOffers.delete(id);
            }
        }

        // Add or update offers
        offers.forEach(offer => {
            if (!activeOffers.has(offer.order_id)) {
                const card = createOfferCard(offer);
                offersContainer.appendChild(card);
                activeOffers.set(offer.order_id, card);
            } else {
                // Update timer
                const card = activeOffers.get(offer.order_id);
                card.querySelector('.offer-timer').textContent = offer.expires_in;
            }
        });
    }

    function createOfferCard(offer) {
        const div = document.createElement('div');
        let cardBorder = 'border-primary';
        let badgeType = '<span class="badge bg-primary text-white"><i class="bi bi-lightning-charge-fill me-1"></i>DIRECT MATCH</span>';

        if (offer.offer_type === 'batch') {
            cardBorder = 'border-info shadow-lg';
            badgeType = '<span class="badge bg-info text-dark fw-bold"><i class="bi bi-layers-fill me-1"></i>BATCH MATCH (+Bonus)</span>';
        } else if (offer.offer_type === 'broadcast') {
            cardBorder = 'border-danger shadow';
            badgeType = '<span class="badge bg-danger text-white fw-bold"><i class="bi bi-broadcast me-1"></i>PRIORITY BROADCAST</span>';
        } else {
            cardBorder = 'border-primary shadow';
            badgeType = `<span class="badge bg-primary text-white"><i class="bi bi-lightning-charge-fill me-1"></i>DIRECT MATCH (Round ${offer.round || 1})</span>`;
        }

        div.className = `card ${cardBorder} mb-3`;
        div.dataset.orderId = offer.order_id;
        div.dataset.secondsLeft = offer.expires_in;
        div.innerHTML = `
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="d-flex align-items-center gap-2">
                        ${badgeType}
                    </div>
                    <span class="badge bg-danger rounded-pill"><span class="offer-timer">${offer.expires_in}</span>s left</span>
                </div>
                <h6 class="fw-bold mb-1"><i class="bi bi-shop me-1 text-primary"></i>${offer.kitchen_name}</h6>
                <p class="text-muted small mb-2"><i class="bi bi-geo-alt"></i> ${offer.kitchen_address}</p>
                <div class="alert alert-light border py-2 px-3 mb-3 d-flex align-items-center gap-2">
                    <i class="bi bi-house-door-fill text-success"></i>
                    <div>
                        <small class="d-block fw-bold text-muted" style="font-size:0.75rem;">DELIVER TO</small>
                        <span class="fw-semibold text-dark">${offer.customer_address}</span>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted d-block" style="font-size:0.75rem;">EARNING / TOTAL</small>
                        <div class="fs-4 fw-bold text-success">₹${parseFloat(offer.total_amount).toFixed(2)}</div>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-secondary px-3 rounded-pill skip-btn">Skip</button>
                        <button class="btn btn-success px-4 rounded-pill fw-bold accept-btn shadow">ACCEPT</button>
                    </div>
                </div>
            </div>
        `;

        div.querySelector('.skip-btn').addEventListener('click', () => {
            const csrfToken = '<?= csrf_token() ?>';
            fetch('<?= url('/delivery/offers/decline') ?>', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: '_csrf=' + encodeURIComponent(csrfToken) + '&order_id=' + encodeURIComponent(offer.order_id)
            }).then(() => {
                div.remove();
                activeOffers.delete(offer.order_id);
            }).catch(err => console.error(err));
        });

        const acceptBtn = div.querySelector('.accept-btn');
        acceptBtn.addEventListener('click', () => {
            acceptBtn.disabled = true;
            acceptBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Accepting...';

            const csrfToken = '<?= csrf_token() ?>';
            fetch('<?= url('/delivery/offers/accept') ?>', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: '_csrf=' + encodeURIComponent(csrfToken) + '&order_id=' + encodeURIComponent(offer.order_id)
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    window.location.reload();
                } else {
                    div.innerHTML = `<div class="card-body text-center py-4"><h5 class="text-danger fw-bold"><i class="bi bi-emoji-frown"></i> ${data.message}</h5></div>`;
                    setTimeout(() => { div.remove(); activeOffers.delete(offer.order_id); }, 3000);
                }
            })
            .catch(err => {
                console.error(err);
                acceptBtn.disabled = false;
                acceptBtn.innerHTML = 'ACCEPT';
                alert('Could not accept offer. Please check connection and try again.');
            });
        });

        return div;
    }

    // Local 1-second countdown ticker for smooth real-time timer experience
    setInterval(function() {
        for (let [id, card] of activeOffers.entries()) {
            let left = parseInt(card.dataset.secondsLeft || '0', 10);
            if (left > 0) {
                left--;
                card.dataset.secondsLeft = left;
                const timerEl = card.querySelector('.offer-timer');
                if (timerEl) timerEl.textContent = left;
            } else {
                card.remove();
                activeOffers.delete(id);
            }
        }
    }, 1000);

    setInterval(fetchOffers, 4000);
    fetchOffers();
});
</script>
<?php endif; ?>
