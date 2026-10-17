<?php
/**
 * Kravyo - Live Dispatch Board
 * Phase 6 / Phase 12: Real-Time Dispatch Monitoring & Smart Cascade Operations
 */
$allCities = $cities ?? ['Surat', 'Bardoli', 'Ahmedabad', 'Mumbai', 'Delhi', 'Bangalore', 'Pune', 'Jaipur', 'Hyderabad'];
$currentCity = $defaultCity ?? 'Surat';
?>
<div class="container-fluid py-4 px-lg-4">
    <!-- Top Header Bar -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1 d-flex align-items-center gap-2">
                <i class="bi bi-broadcast text-danger pulse-dot"></i> Live Dispatch Board
            </h2>
            <p class="text-muted small mb-0">Real-time rider cascade, auto-dispatch tracking & operations monitor</p>
        </div>
        
        <div class="d-flex flex-wrap align-items-center gap-2">
            <!-- Search Order -->
            <div class="input-group input-group-sm" style="min-width: 240px;">
                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" id="orderSearch" class="form-control border-start-0" placeholder="Search Order ID, Chef, Rider...">
            </div>

            <!-- Global City Filter -->
            <div class="d-flex align-items-center gap-1 bg-white border rounded px-2 py-1 shadow-sm">
                <i class="bi bi-geo-alt-fill text-danger small"></i>
                <label for="cityFilter" class="small fw-semibold text-muted mb-0 me-1">City:</label>
                <select id="cityFilter" class="form-select form-select-sm border-0 fw-bold text-dark py-0 pe-4" style="box-shadow:none; cursor:pointer;">
                    <option value="all">All Cities</option>
                    <?php foreach ($allCities as $c): ?>
                        <option value="<?= sanitize($c) ?>" <?= $c === $currentCity ? 'selected' : '' ?>>
                            <?= sanitize($c) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Sound Toggle -->
            <button id="soundToggleBtn" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1" title="Toggle Audio Notifications">
                <i class="bi bi-volume-up-fill" id="soundIcon"></i>
            </button>

            <!-- Manual Refresh -->
            <button id="refreshBtn" class="btn btn-sm btn-light border d-flex align-items-center gap-1 shadow-sm" title="Refresh Now">
                <i class="bi bi-arrow-clockwise" id="refreshIcon"></i> Refresh
            </button>
        </div>
    </div>

    <div class="row g-4">
        <!-- LEFT COLUMN: Riders Grid by City -->
        <div class="col-xl-4 col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-bottom p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="fw-bold mb-0 d-flex align-items-center gap-2">
                            <i class="bi bi-bicycle text-primary"></i> Riders (<span id="riderCount">0</span>)
                        </h5>
                        <span id="activeCityBadge" class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                            <?= sanitize($currentCity) ?>
                        </span>
                    </div>

                    <!-- City Selector Dropdown on Left Side -->
                    <div class="mt-2">
                        <label class="form-label text-muted small fw-semibold mb-1">
                            <i class="bi bi-building me-1"></i>Select City Riders:
                        </label>
                        <select id="leftCitySelect" class="form-select form-select-sm fw-semibold">
                            <option value="all">All Cities</option>
                            <?php foreach ($allCities as $c): ?>
                                <option value="<?= sanitize($c) ?>" <?= $c === $currentCity ? 'selected' : '' ?>>
                                    <?= sanitize($c) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Live Rider Status Counter Ribbon -->
                    <div class="d-flex flex-wrap gap-1 mt-2 pt-2 border-top" id="riderStatusRibbon">
                        <span class="badge bg-success-subtle text-success border border-success-subtle small py-1 px-2">
                            <i class="bi bi-circle-fill me-1" style="font-size:0.5rem;"></i><span id="statOnline">0</span> Free
                        </span>
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle small py-1 px-2">
                            <i class="bi bi-bicycle me-1"></i><span id="statBusy">0</span> Busy
                        </span>
                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle small py-1 px-2">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i><span id="statStale">0</span> Disconnected
                        </span>
                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle small py-1 px-2">
                            <i class="bi bi-circle me-1" style="font-size:0.5rem;"></i><span id="statOffline">0</span> Offline
                        </span>
                    </div>
                </div>

                <div class="card-body p-3" style="max-height: 720px; overflow-y: auto;">
                    <div id="ridersGrid" class="d-flex flex-column gap-2">
                        <div class="text-center py-5 text-muted">
                            <div class="spinner-border spinner-border-sm text-primary mb-2"></div>
                            <div>Loading riders...</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT COLUMN: Live Dispatch Operations (Dropdown Accordion View) -->
        <div class="col-xl-8 col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-bottom p-3">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div>
                            <h5 class="fw-bold mb-0 d-flex align-items-center gap-2">
                                <i class="bi bi-activity text-danger"></i> Live Dispatch Operations (<span id="ordersCount">0</span>)
                            </h5>
                            <span class="text-muted small">Click any order to expand its real-time Offer Timeline</span>
                        </div>

                        <!-- Filter Status Tabs -->
                        <div class="btn-group btn-group-sm" role="group" id="statusFilterTabs">
                            <button type="button" class="btn btn-outline-secondary active" data-filter="all">All</button>
                            <button type="button" class="btn btn-outline-primary" data-filter="active">
                                <span class="spinner-grow spinner-grow-sm me-1" style="width:0.5rem;height:0.5rem;"></span>Active
                            </button>
                            <button type="button" class="btn btn-outline-success" data-filter="assigned">Assigned</button>
                            <button type="button" class="btn btn-outline-danger" data-filter="no_rider">No Rider</button>
                        </div>
                    </div>
                </div>

                <div class="card-body p-0">
                    <!-- Orders Accordion List Container -->
                    <div id="ordersAccordionList" class="p-3 d-flex flex-column gap-3" style="min-height: 400px; max-height: 720px; overflow-y: auto;">
                        <div class="text-center py-5 text-muted">
                            <div class="spinner-border spinner-border-sm text-danger mb-2"></div>
                            <div>Listening for live dispatch events...</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Audio Notification Element -->
<audio id="dispatchNotificationSound" preload="auto">
    <source src="<?= asset('sounds/notification.wav') ?>" type="audio/wav">
    <source src="<?= asset('sounds/beep_short.wav') ?>" type="audio/wav">
</audio>

<style>
/* Pulsing dot for live status */
.pulse-dot {
    animation: livePulse 1.5s infinite;
}
@keyframes livePulse {
    0% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.4; transform: scale(1.15); }
    100% { opacity: 1; transform: scale(1); }
}

/* Pulsing border for active cascading orders */
.order-card-active {
    border: 2px solid #ffc107 !important;
    box-shadow: 0 0 15px rgba(255, 193, 7, 0.35) !important;
    animation: borderGlow 2s infinite alternate;
}
@keyframes borderGlow {
    from { border-color: #ffc107; }
    to { border-color: #fd7e14; }
}

/* Accordion transition styles */
.order-accordion-header {
    cursor: pointer;
    user-select: none;
    transition: background-color 0.2s ease, border-color 0.2s ease;
}
.order-accordion-header:hover {
    background-color: #f8f9fa !important;
}

.order-chevron {
    transition: transform 0.25s ease-in-out;
}
.order-chevron.expanded {
    transform: rotate(180deg);
}

.order-accordion-body {
    transition: max-height 0.3s ease-out, opacity 0.25s ease-in;
    overflow: hidden;
}
.order-accordion-body.collapsed {
    display: none;
}

.rider-card-hover {
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}
.rider-card-hover:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(0,0,0,0.06);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const cityFilter       = document.getElementById('cityFilter');
    const leftCitySelect   = document.getElementById('leftCitySelect');
    const orderSearch      = document.getElementById('orderSearch');
    const ridersGrid       = document.getElementById('ridersGrid');
    const ordersContainer  = document.getElementById('ordersAccordionList');
    const riderCount       = document.getElementById('riderCount');
    const ordersCount      = document.getElementById('ordersCount');
    const activeCityBadge  = document.getElementById('activeCityBadge');
    const soundToggleBtn   = document.getElementById('soundToggleBtn');
    const soundIcon        = document.getElementById('soundIcon');
    const refreshBtn       = document.getElementById('refreshBtn');
    const refreshIcon      = document.getElementById('refreshIcon');
    const notifAudio       = document.getElementById('dispatchNotificationSound');

    // Counters
    const statOnline       = document.getElementById('statOnline');
    const statBusy         = document.getElementById('statBusy');
    const statStale        = document.getElementById('statStale');
    const statOffline      = document.getElementById('statOffline');

    // Read URL parameters if page loaded from a notification link
    const urlParams = new URLSearchParams(window.location.search);
    let targetOrderParam = urlParams.get('order') ? urlParams.get('order').trim() : '';
    let targetCityParam  = urlParams.get('city') ? urlParams.get('city').trim() : '';

    let allOrders = [];
    let currentCity = targetCityParam || cityFilter.value || 'Surat';
    let statusFilter = 'all';
    let soundEnabled = true;

    if (targetCityParam) {
        cityFilter.value = targetCityParam;
        leftCitySelect.value = targetCityParam;
        activeCityBadge.textContent = targetCityParam;
    }
    
    // Store which order IDs are currently expanded in the UI
    let expandedOrderIds = new Set();
    let knownActiveOrderIds = new Set();
    let lastOrderStateMap = new Map();
    let initialLoadDone = false;

    // Global jump handler when user clicks notification inside the bell dropdown while on this page
    window.handleNotificationJump = function(orderNo, city) {
        console.log(`[Dispatch Monitor] Notification clicked: Order ${orderNo}, City: ${city}`);
        targetOrderParam = orderNo ? orderNo.trim() : '';
        
        if (city && city.trim()) {
            currentCity = city.trim();
            cityFilter.value = currentCity;
            leftCitySelect.value = currentCity;
            activeCityBadge.textContent = currentCity;
        }

        // Close Bootstrap notification dropdown menu if open
        const openDropdowns = document.querySelectorAll('.dropdown-menu.show');
        openDropdowns.forEach(dd => dd.classList.remove('show'));

        // Update browser URL query string without reloading
        const newUrl = window.location.pathname + '?order=' + encodeURIComponent(orderNo) + (city ? '&city=' + encodeURIComponent(city) : '');
        window.history.replaceState({}, '', newUrl);

        // Fetch dispatch data and auto-focus this order immediately
        fetchDispatchData();
    };

    // Toggle sound
    soundToggleBtn.addEventListener('click', () => {
        soundEnabled = !soundEnabled;
        if (soundEnabled) {
            soundIcon.className = 'bi bi-volume-up-fill';
            soundToggleBtn.classList.remove('btn-outline-danger');
            soundToggleBtn.classList.add('btn-outline-secondary');
        } else {
            soundIcon.className = 'bi bi-volume-mute-fill text-danger';
            soundToggleBtn.classList.remove('btn-outline-secondary');
            soundToggleBtn.classList.add('btn-outline-danger');
        }
    });

    function playAlertSound() {
        if (!soundEnabled) return;
        // 1. Play using kravyoPlayDing (supports synth fallback & unlocked audio)
        if (typeof window.kravyoPlayDing === 'function') {
            window.kravyoPlayDing();
        }
        // 2. Play local dispatch board audio element
        if (notifAudio) {
            try {
                notifAudio.currentTime = 0;
                const promise = notifAudio.play();
                if (promise !== undefined) {
                    promise.catch(err => console.log('Audio autoplay prevented:', err));
                }
            } catch (e) {}
        }
    }

    // Sync city dropdowns
    function syncCity(newCity) {
        currentCity = newCity;
        cityFilter.value = newCity;
        leftCitySelect.value = newCity;
        activeCityBadge.textContent = newCity === 'all' ? 'All Cities' : newCity;
        fetchDispatchData();
    }

    cityFilter.addEventListener('change', () => syncCity(cityFilter.value));
    leftCitySelect.addEventListener('change', () => syncCity(leftCitySelect.value));

    // Filter tabs click
    document.querySelectorAll('#statusFilterTabs button').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('#statusFilterTabs button').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            statusFilter = this.getAttribute('data-filter');
            renderOrders();
        });
    });

    refreshBtn.addEventListener('click', () => {
        refreshIcon.classList.add('spin-animation');
        fetchDispatchData(() => {
            setTimeout(() => refreshIcon.classList.remove('spin-animation'), 500);
        });
    });

    function fetchDispatchData(callback) {
        const cityParam = encodeURIComponent(currentCity);
        fetch(`<?= url('/admin/dispatch-monitor') ?>?city=${cityParam}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Update city dropdown options if cities returned
                if (data.cities && data.cities.length > 0) {
                    updateCityDropdowns(data.cities);
                }

                renderRiders(data.riders || []);
                allOrders = data.orders || [];
                handleActiveOrderFocus(allOrders);
                renderOrders();
            }
            if (callback) callback();
        })
        .catch(err => {
            console.error('Dispatch Monitor Fetch Error:', err);
            if (callback) callback();
        });
    }

    function updateCityDropdowns(cities) {
        const currentSelected = currentCity;
        [cityFilter, leftCitySelect].forEach(selectEl => {
            if (!selectEl) return;
            const existingVals = Array.from(selectEl.options).map(o => o.value);
            // If already has all cities, don't re-render dropdown to prevent losing focus
            if (existingVals.length >= cities.length + 1) return;

            let html = '<option value="all">All Cities</option>';
            cities.forEach(c => {
                html += `<option value="${escapeHtml(c)}" ${c === currentSelected ? 'selected' : ''}>${escapeHtml(c)}</option>`;
            });
            selectEl.innerHTML = html;
        });
    }

    /**
     * Core Requirement:
     * When notification is clicked or new active dispatch arrives:
     * - Auto-switch city on left to that order's city
     * - Open that order's dropdown accordion on right
     * - Keep all other orders closed
     */
    function handleActiveOrderFocus(orders) {
        // PRIORITY 1: If user clicked a notification for a specific order
        if (targetOrderParam) {
            const matchedItem = orders.find(item => 
                item.order.order_number && item.order.order_number.toLowerCase() === targetOrderParam.toLowerCase()
            );

            if (matchedItem) {
                const o = matchedItem.order;
                const oCity = o.kitchen_city || o.city;

                // 1. Auto-switch left city to this order's city
                if (oCity && currentCity !== oCity) {
                    currentCity = oCity;
                    cityFilter.value = oCity;
                    leftCitySelect.value = oCity;
                    activeCityBadge.textContent = oCity;
                    fetch(`<?= url('/admin/dispatch-monitor') ?>?city=${encodeURIComponent(oCity)}`, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    })
                    .then(r => r.json())
                    .then(d => { if (d.success) renderRiders(d.riders); });
                }

                // 2. Ensure order is visible by resetting status filter to 'all'
                statusFilter = 'all';
                document.querySelectorAll('#statusFilterTabs button').forEach(b => {
                    b.classList.toggle('active', b.getAttribute('data-filter') === 'all');
                });

                // 3. Open ONLY this order, keep all other orders closed
                expandedOrderIds.clear();
                expandedOrderIds.add(parseInt(o.id));

                // 4. Smooth scroll to this order card & highlight
                setTimeout(() => {
                    const card = document.getElementById(`orderCard_${o.id}`);
                    if (card) {
                        card.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        card.classList.add('order-card-active');
                    }
                }, 180);

                targetOrderParam = null; // Handled
                initialLoadDone = true;
                return;
            }
        }

        // PRIORITY 2: New live dispatch event & No-Rider sound triggers
        let newestActiveOrder = null;
        let playNoRiderSound = false;

        orders.forEach(item => {
            const o = item.order;
            const orderId = parseInt(o.id);
            const isLive = (o.dispatch_status === 'cascading' || o.dispatch_status === 'broadcasting');

            if (isLive) {
                if (!newestActiveOrder || (o.id > newestActiveOrder.id)) {
                    newestActiveOrder = o;
                }
            }

            // Only trigger alert sound after Round 4 when dispatch fails and becomes 'no_rider'
            const status = o.dispatch_status;
            if (lastOrderStateMap.has(orderId)) {
                const prevStatus = lastOrderStateMap.get(orderId);
                if (prevStatus !== 'no_rider' && status === 'no_rider') {
                    playNoRiderSound = true;
                }
            }
            lastOrderStateMap.set(orderId, status);
        });

        // Sound ONLY plays if an order failed all rounds and became No Rider Found
        if (playNoRiderSound && initialLoadDone) {
            playAlertSound();
        }

        if (newestActiveOrder) {
            const orderId = parseInt(newestActiveOrder.id);
            const orderCity = newestActiveOrder.kitchen_city || newestActiveOrder.city;

            // Check if this is a newly arrived active order
            const isBrandNew = !knownActiveOrderIds.has(orderId);

            if (isBrandNew) {
                knownActiveOrderIds.add(orderId);
                // No sound here during active rounds (sound only plays if No Rider Found after Round 4)

                // 1. Auto switch city on left side
                if (orderCity && currentCity !== orderCity && currentCity !== 'all') {
                    console.log(`[Dispatch Monitor] Auto-focused new dispatch in ${orderCity}`);
                    currentCity = orderCity;
                    cityFilter.value = orderCity;
                    leftCitySelect.value = orderCity;
                    activeCityBadge.textContent = orderCity;
                    fetch(`<?= url('/admin/dispatch-monitor') ?>?city=${encodeURIComponent(orderCity)}`, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    })
                    .then(r => r.json())
                    .then(d => { if (d.success) renderRiders(d.riders); });
                }

                // 2. Open this order's dropdown while keeping all others closed
                expandedOrderIds.clear();
                expandedOrderIds.add(orderId);
            } else if (!initialLoadDone) {
                // On initial load, open the active order by default
                expandedOrderIds.clear();
                expandedOrderIds.add(orderId);
            }
        }

        initialLoadDone = true;
    }

    function renderRiders(riders) {
        riderCount.textContent = riders.length;
        let onlineCount = 0;
        let busyCount = 0;
        let staleCount = 0;
        let offlineCount = 0;

        if (riders.length === 0) {
            ridersGrid.innerHTML = `
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-bicycle fs-2 d-block mb-1 text-secondary opacity-50"></i>
                    No delivery riders found in <strong>${escapeHtml(currentCity === 'all' ? 'any city' : currentCity)}</strong>.
                </div>
            `;
            statOnline.textContent = 0;
            statBusy.textContent = 0;
            statStale.textContent = 0;
            statOffline.textContent = 0;
            return;
        }

        const html = riders.map(r => {
            let statusClass = 'secondary';
            let statusBadgeClass = 'bg-secondary-subtle text-secondary border border-secondary-subtle';
            let statusText = 'Offline';
            let icon = 'bi-circle text-muted';
            
            // Stale heartbeat check (> 65 seconds)
            let isStale = false;
            if (r.last_seen_at) {
                const tDate = new Date(r.last_seen_at.replace(/-/g, '/'));
                if ((Date.now() - tDate.getTime()) > 65000) {
                    isStale = true;
                }
            } else {
                isStale = true;
            }

            if (r.is_online == 1) {
                if (isStale) {
                    statusClass = 'warning';
                    statusBadgeClass = 'bg-warning-subtle text-warning border border-warning-subtle';
                    statusText = 'Disconnected';
                    icon = 'bi-exclamation-triangle-fill text-warning';
                    staleCount++;
                } else if (r.is_busy == 1) {
                    statusClass = 'danger';
                    statusBadgeClass = 'bg-danger-subtle text-danger border border-danger-subtle';
                    statusText = 'Busy (Delivering)';
                    icon = 'bi-bicycle text-danger';
                    busyCount++;
                } else {
                    statusClass = 'success';
                    statusBadgeClass = 'bg-success text-white shadow-sm';
                    statusText = 'Online & Free';
                    icon = 'bi-circle-fill text-success';
                    onlineCount++;
                }
            } else {
                offlineCount++;
            }

            const phoneHtml = r.phone ? `<a href="tel:${escapeHtml(r.phone)}" class="text-decoration-none text-muted small me-2"><i class="bi bi-telephone me-1"></i>${escapeHtml(r.phone)}</a>` : '';
            const vehicle = r.vehicle_number ? `<span class="badge bg-light text-dark border small fw-normal"><i class="bi bi-truck me-1"></i>${escapeHtml(r.vehicle_number)}</span>` : '';
            const cityBadge = currentCity === 'all' ? `<span class="badge bg-light text-muted border small ms-1">${escapeHtml(r.city)}</span>` : '';

            return `
                <div class="p-2 border rounded-3 bg-white rider-card-hover border-start border-3 border-${statusClass}">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <div>
                            <div class="d-flex align-items-center gap-1">
                                <i class="bi ${icon} small"></i>
                                <span class="fw-bold text-dark">${escapeHtml(r.full_name)}</span>
                                ${cityBadge}
                            </div>
                            <div class="small text-muted ms-3">${escapeHtml(r.email || '')}</div>
                        </div>
                        <span class="badge ${statusBadgeClass} small px-2 py-1">${statusText}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-2 pt-1 border-top border-light">
                        <div>${phoneHtml}</div>
                        <div>${vehicle}</div>
                    </div>
                </div>
            `;
        }).join('');
        
        ridersGrid.innerHTML = html;
        statOnline.textContent = onlineCount;
        statBusy.textContent = busyCount;
        statStale.textContent = staleCount;
        statOffline.textContent = offlineCount;
    }

    function renderOrders() {
        const query = orderSearch.value.trim().toLowerCase();
        let filteredOrders = allOrders;

        // City filter on orders
        if (currentCity && currentCity !== 'all') {
            filteredOrders = filteredOrders.filter(item => {
                const kCity = (item.order.kitchen_city || item.order.city || '').toLowerCase();
                const dCity = (item.order.delivery_city || '').toLowerCase();
                const matchCity = currentCity.toLowerCase();
                return kCity === matchCity || dCity === matchCity;
            });
        }

        // Status tab filter
        if (statusFilter === 'active') {
            filteredOrders = filteredOrders.filter(item => 
                item.order.dispatch_status === 'cascading' || item.order.dispatch_status === 'broadcasting'
            );
        } else if (statusFilter === 'assigned') {
            filteredOrders = filteredOrders.filter(item => item.order.dispatch_status === 'assigned');
        } else if (statusFilter === 'no_rider') {
            filteredOrders = filteredOrders.filter(item => item.order.dispatch_status === 'no_rider');
        }

        // Search query filter
        if (query) {
            filteredOrders = filteredOrders.filter(item => {
                const o = item.order;
                return (o.order_number && o.order_number.toLowerCase().includes(query)) ||
                       (o.kitchen_name && o.kitchen_name.toLowerCase().includes(query)) ||
                       (o.customer_name && o.customer_name.toLowerCase().includes(query)) ||
                       (o.assigned_rider_name && o.assigned_rider_name.toLowerCase().includes(query));
            });
        }

        ordersCount.textContent = filteredOrders.length;

        if (filteredOrders.length === 0) {
            ordersContainer.innerHTML = `
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary opacity-50"></i>
                    ${query ? 'No orders match your search criteria.' : 'No active dispatch operations found in this filter.'}
                </div>
            `;
            return;
        }

        const html = filteredOrders.map(item => {
            const o = item.order;
            const tl = item.timeline || [];
            const orderId = parseInt(o.id);
            const isExpanded = expandedOrderIds.has(orderId);
            const isLive = (o.dispatch_status === 'cascading' || o.dispatch_status === 'broadcasting');

            // Dispatch Status Badge
            let statusBadge = '';
            if (o.dispatch_status === 'cascading') {
                statusBadge = `<span class="badge bg-primary text-white py-1 px-2 d-inline-flex align-items-center gap-1 shadow-sm">
                    <span class="spinner-grow spinner-grow-sm" style="width:0.5rem;height:0.5rem;"></span>
                    Smart Cascade (Round ${o.dispatch_round || 1})
                </span>`;
            } else if (o.dispatch_status === 'broadcasting') {
                statusBadge = `<span class="badge bg-warning text-dark py-1 px-2 d-inline-flex align-items-center gap-1 shadow-sm">
                    <span class="spinner-grow spinner-grow-sm" style="width:0.5rem;height:0.5rem;"></span>
                    Broadcast Fallback (Round ${o.dispatch_round || 4})
                </span>`;
            } else if (o.dispatch_status === 'assigned') {
                statusBadge = `<span class="badge bg-success text-white py-1 px-2"><i class="bi bi-check-circle-fill me-1"></i>Assigned</span>`;
            } else if (o.dispatch_status === 'no_rider') {
                statusBadge = `<span class="badge bg-danger text-white py-1 px-2"><i class="bi bi-x-circle-fill me-1"></i>No Rider Found</span>`;
            }

            // Deduplicate Timeline: Keep latest record per partner or ordered sequence
            const deduplicatedTimeline = [];
            const seenPartnerRounds = new Set();
            tl.forEach(t => {
                const key = `${t.delivery_partner_id}_${t.round}`;
                if (!seenPartnerRounds.has(key)) {
                    seenPartnerRounds.add(key);
                    deduplicatedTimeline.push(t);
                }
            });

            // Timeline rows HTML
            let timelineRowsHtml = '';
            if (deduplicatedTimeline.length > 0) {
                timelineRowsHtml = deduplicatedTimeline.map(t => {
                    let badgeClass = 'bg-secondary text-white';
                    let statusLabel = t.status;
                    let iconHtml = '<i class="bi bi-clock me-1"></i>';

                    if (t.status === 'accepted') {
                        badgeClass = 'bg-success text-white';
                        iconHtml = '<i class="bi bi-check-circle-fill me-1"></i>';
                    } else if (t.status === 'declined') {
                        badgeClass = 'bg-danger text-white';
                        iconHtml = '<i class="bi bi-x-circle-fill me-1"></i>';
                    } else if (t.status === 'pending') {
                        badgeClass = 'bg-warning text-dark pulse-dot';
                        iconHtml = '<span class="spinner-border spinner-border-sm me-1" style="width:0.6rem;height:0.6rem;"></span>';
                    } else if (t.status === 'expired') {
                        badgeClass = 'bg-dark text-white';
                        iconHtml = '<i class="bi bi-hourglass-bottom me-1"></i>';
                    } else if (t.status === 'lost') {
                        badgeClass = 'bg-light text-muted border';
                        iconHtml = '<i class="bi bi-dash-circle me-1"></i>';
                    }

                    const vehicle = t.vehicle_number ? `(${escapeHtml(t.vehicle_number)})` : '';
                    const timeSent = t.sent_at ? formatTime(t.sent_at) : '';
                    const roundBadge = (parseInt(t.round) === 4 || t.offer_type === 'broadcast')
                        ? `<span class="badge bg-warning-subtle text-warning-emphasis border border-warning small fw-bold"><i class="bi bi-broadcast me-1"></i>Round 4 (Broadcast)</span>`
                        : `<span class="badge bg-light text-dark border small fw-bold">Round ${t.round || 1}</span>`;

                    return `
                        <div class="d-flex justify-content-between align-items-center py-2 px-3 border-bottom bg-white">
                            <div class="d-flex align-items-center gap-2">
                                ${roundBadge}
                                <span class="fw-bold text-dark">${escapeHtml(t.partner_name || 'Rider #' + t.delivery_partner_id)}</span>
                                <span class="small text-muted">${vehicle}</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="small text-muted">${timeSent}</span>
                                <span class="badge ${badgeClass} small py-1 px-2 text-capitalize">${iconHtml}${statusLabel}</span>
                            </div>
                        </div>
                    `;
                }).join('');
            } else {
                timelineRowsHtml = '<div class="text-center py-3 text-muted small fst-italic">No offer pings recorded yet.</div>';
            }

            const activeClass = isLive ? 'order-card-active' : '';
            const chevronClass = isExpanded ? 'expanded' : '';
            const bodyClass = isExpanded ? '' : 'collapsed';

            const kitchenCityBadge = o.kitchen_city ? `<span class="badge bg-secondary-subtle text-secondary border small ms-1"><i class="bi bi-geo-alt-fill text-danger me-1"></i>${escapeHtml(o.kitchen_city)}</span>` : '';
            const amountHtml = o.total_amount ? `<span class="fw-bold text-dark ms-2">₹${parseFloat(o.total_amount).toFixed(2)}</span>` : '';
            const timeAgo = o.dispatch_started_at ? formatTime(o.dispatch_started_at) : (o.created_at ? formatTime(o.created_at) : '');

            return `
                <div class="card border rounded-3 shadow-sm ${activeClass}" id="orderCard_${orderId}">
                    <!-- Dropdown Clickable Header Bar -->
                    <div class="order-accordion-header card-header bg-white p-3 d-flex justify-content-between align-items-center border-bottom-0"
                         onclick="toggleOrderAccordion(${orderId})">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <i class="bi bi-chevron-down order-chevron fs-6 text-primary me-1 ${chevronClass}" id="chevron_${orderId}"></i>
                            <span class="fw-bold text-dark fs-6">Order #${escapeHtml(o.order_number)}</span>
                            ${kitchenCityBadge}
                            <span class="small text-muted">• ${escapeHtml(o.kitchen_name || 'Kitchen')}</span>
                            <span class="small text-muted">• <i class="bi bi-clock me-1"></i>${timeAgo}</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            ${amountHtml}
                            ${statusBadge}
                        </div>
                    </div>

                    <!-- Dropdown Collapsible Body -->
                    <div class="order-accordion-body ${bodyClass}" id="accordionBody_${orderId}">
                        <div class="card-body p-3 pt-0 border-top bg-light-subtle">
                            <!-- Quick Meta Information Grid -->
                            <div class="row g-2 small mb-3 mt-1 p-2 bg-white rounded border">
                                <div class="col-md-4">
                                    <span class="text-muted d-block">Customer:</span>
                                    <strong>${escapeHtml(o.customer_name || 'Customer')}</strong>
                                    ${o.customer_phone ? `<span class="text-muted ms-1">(${escapeHtml(o.customer_phone)})</span>` : ''}
                                </div>
                                <div class="col-md-4">
                                    <span class="text-muted d-block">Kitchen Pickup:</span>
                                    <strong>${escapeHtml(o.kitchen_name || 'N/A')}</strong>
                                    <div class="text-muted text-truncate" title="${escapeHtml(o.kitchen_address || '')}">${escapeHtml(o.kitchen_address || '')}</div>
                                </div>
                                <div class="col-md-4">
                                    <span class="text-muted d-block">Delivery Address:</span>
                                    <div class="text-truncate" title="${escapeHtml(o.delivery_address || '')}">${escapeHtml(o.delivery_address || 'Customer location')}</div>
                                </div>
                            </div>

                            <!-- Assigned Rider Banner (if assigned) -->
                            ${o.assigned_rider_name ? `
                                <div class="alert alert-success py-2 px-3 small d-flex align-items-center justify-content-between mb-3 rounded-2">
                                    <div>
                                        <i class="bi bi-check-circle-fill me-1 text-success"></i>
                                        Assigned to: <strong>${escapeHtml(o.assigned_rider_name)}</strong> 
                                        ${o.assigned_rider_phone ? `(${escapeHtml(o.assigned_rider_phone)})` : ''}
                                    </div>
                                    <span class="badge bg-success">Active Partner</span>
                                </div>
                            ` : ''}

                            <!-- Offer Timeline Box (Dropdown Format) -->
                            <div class="border rounded-2 overflow-hidden shadow-sm">
                                <div class="bg-light px-3 py-2 border-bottom d-flex justify-content-between align-items-center">
                                    <span class="fw-bold small text-dark">
                                        <i class="bi bi-clock-history text-primary me-1"></i> Offer Cascade Timeline
                                    </span>
                                    <span class="badge bg-white text-muted border small">
                                        ${deduplicatedTimeline.length} Round${deduplicatedTimeline.length !== 1 ? 's' : ''}
                                    </span>
                                </div>
                                <div style="max-height: 240px; overflow-y: auto;">
                                    ${timelineRowsHtml}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }).join('');

        ordersContainer.innerHTML = html;
    }

    // Toggle Order Accordion function (exposed to window)
    window.toggleOrderAccordion = function(orderId) {
        const body = document.getElementById(`accordionBody_${orderId}`);
        const chevron = document.getElementById(`chevron_${orderId}`);
        if (!body || !chevron) return;

        if (expandedOrderIds.has(orderId)) {
            expandedOrderIds.delete(orderId);
            body.classList.add('collapsed');
            chevron.classList.remove('expanded');
        } else {
            // Optional: Close all other orders to keep only one open
            expandedOrderIds.clear();
            document.querySelectorAll('.order-accordion-body').forEach(b => b.classList.add('collapsed'));
            document.querySelectorAll('.order-chevron').forEach(c => c.classList.remove('expanded'));

            expandedOrderIds.add(orderId);
            body.classList.remove('collapsed');
            chevron.classList.add('expanded');
        }
    };

    function escapeHtml(text) {
        if (!text) return '';
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function formatTime(dateStr) {
        if (!dateStr) return '';
        try {
            const d = new Date(dateStr.replace(/-/g, '/'));
            return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        } catch (e) {
            return dateStr;
        }
    }

    orderSearch.addEventListener('input', renderOrders);

    // Initial fetch and 3s polling loop
    fetchDispatchData();
    setInterval(fetchDispatchData, 3000);
});
</script>
