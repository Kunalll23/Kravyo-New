<?php
/**
 * Kravyo - Live Dispatch Board
 * Phase 6: Full page dispatch monitoring
 */
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0"><i class="bi bi-broadcast"></i> Live Dispatch Board</h2>
        
        <div class="d-flex gap-3">
            <div class="input-group input-group-sm w-auto">
                <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                <input type="text" id="orderSearch" class="form-control" placeholder="Search Order ID...">
            </div>
            <select id="cityFilter" class="form-select form-select-sm w-auto">
                <option value="Surat" selected>Surat</option>
                <option value="Ahmedabad">Ahmedabad</option>
                <option value="Vadodara">Vadodara</option>
            </select>
        </div>
    </div>

    <div class="row g-4">
        <!-- Riders Grid -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom-0 pt-3">
                    <h5 class="fw-bold mb-0">Riders (<span id="riderCount">0</span>)</h5>
                </div>
                <div class="card-body">
                    <div id="ridersGrid" class="d-flex flex-column gap-2">
                        <!-- Populated by JS -->
                        <div class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm"></div></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Live Orders -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom-0 pt-3">
                    <h5 class="fw-bold mb-0">Live Dispatch Operations</h5>
                </div>
                <div class="card-body p-0">
                    <div id="ordersList" class="list-group list-group-flush">
                        <!-- Populated by JS -->
                        <div class="text-center py-5 text-muted">Listening for new orders...</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const cityFilter = document.getElementById('cityFilter');
    const orderSearch = document.getElementById('orderSearch');
    const ridersGrid = document.getElementById('ridersGrid');
    const ordersList = document.getElementById('ordersList');
    const riderCount = document.getElementById('riderCount');
    
    let allOrders = [];

    function fetchDispatchData() {
        const city = cityFilter.value;
        fetch(`<?= url('/admin/dispatch-monitor') ?>?city=${encodeURIComponent(city)}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                renderRiders(data.riders);
                allOrders = data.orders;
                renderOrders();
            }
        })
        .catch(console.error);
    }

    function renderRiders(riders) {
        riderCount.textContent = riders.length;
        if (riders.length === 0) {
            ridersGrid.innerHTML = '<div class="text-center py-4 text-muted">No riders found in this city.</div>';
            return;
        }

        const html = riders.map(r => {
            let statusClass = 'secondary';
            let statusText = 'Offline';
            let icon = 'bi-circle';
            
            // Stale heartbeat check
            let isStale = false;
            if (r.last_seen_at) {
                // Ensure date parsing works across browsers
                const tDate = new Date(r.last_seen_at.replace(/-/g, '/'));
                if ((Date.now() - tDate.getTime()) > 65000) {
                    isStale = true;
                }
            } else {
                isStale = true;
            }

            if (r.is_online == 1) {
                if (isStale) {
                    statusClass = 'warning text-dark';
                    statusText = 'Disconnected';
                    icon = 'bi-exclamation-triangle-fill text-warning';
                } else if (r.is_busy == 1) {
                    statusClass = 'danger';
                    statusText = 'Busy';
                    icon = 'bi-bicycle text-danger';
                } else {
                    statusClass = 'success';
                    statusText = 'Online & Free';
                    icon = 'bi-circle-fill text-success';
                }
            }
            
            return `
                <div class="p-2 border rounded d-flex justify-content-between align-items-center bg-light">
                    <div>
                        <i class="bi ${icon} me-2"></i>
                        <span class="fw-bold">${r.full_name}</span>
                        <div class="small text-muted ms-4">${r.email || ''} <br> ${r.vehicle_number || ''}</div>
                    </div>
                    <span class="badge bg-${statusClass}">${statusText}</span>
                </div>
            `;
        }).join('');
        
        ridersGrid.innerHTML = html;
    }

    function renderOrders() {
        const query = orderSearch.value.trim().toLowerCase();
        let filteredOrders = allOrders;
        
        if (query) {
            filteredOrders = allOrders.filter(item => 
                item.order.order_number.toLowerCase().includes(query)
            );
        }

        if (filteredOrders.length === 0) {
            if (query) {
                ordersList.innerHTML = '<div class="text-center py-5 text-muted">No orders match your search.</div>';
            } else {
                ordersList.innerHTML = '<div class="text-center py-5 text-muted">No active broadcasts.</div>';
            }
            return;
        }

        const html = filteredOrders.map(item => {
            const o = item.order;
            const tl = item.timeline;
            
            let statusHtml = '';
            if (o.dispatch_status === 'cascading') {
                statusHtml = `<span class="badge bg-primary text-white"><div class="spinner-border spinner-border-sm me-1" style="width:0.7rem;height:0.7rem;"></div>Smart Cascade (Round ${o.dispatch_round || 1})</span>`;
            } else if (o.dispatch_status === 'broadcasting') {
                statusHtml = `<span class="badge bg-warning text-dark"><div class="spinner-border spinner-border-sm me-1" style="width:0.7rem;height:0.7rem;"></div>Broadcast Fallback (Round ${o.dispatch_round})</span>`;
            } else if (o.dispatch_status === 'assigned') {
                statusHtml = `<span class="badge bg-success">Assigned</span>`;
            } else if (o.dispatch_status === 'no_rider') {
                statusHtml = `<span class="badge bg-danger">No Rider Found</span>`;
            }

            // Deduplicate timeline: only show the latest round per rider
            const latestOffers = {};
            tl.forEach(t => {
                if (!latestOffers[t.partner_name] || (t.round > latestOffers[t.partner_name].round)) {
                    latestOffers[t.partner_name] = t;
                }
            });
            const deduplicatedTimeline = Object.values(latestOffers);

            let timelineHtml = deduplicatedTimeline.map(t => {
                let badge = 'bg-secondary';
                if (t.status === 'accepted') badge = 'bg-success';
                else if (t.status === 'declined') badge = 'bg-danger';
                else if (t.status === 'pending') badge = 'bg-warning text-dark';
                else if (t.status === 'lost') badge = 'bg-light text-muted border';
                else if (t.status === 'expired') badge = 'bg-dark text-white';
                
                return `<div class="small d-flex justify-content-between border-bottom py-1">
                            <span>${t.partner_name} <span class="text-muted ms-1">(Round ${t.round || 1})</span></span>
                            <span class="badge ${badge}">${t.status}</span>
                        </div>`;
            }).join('');
            
            if (!timelineHtml) timelineHtml = '<div class="small text-muted fst-italic">No offers sent yet.</div>';

            return `
                <div class="list-group-item p-3">
                    <div class="d-flex justify-content-between mb-2">
                        <h6 class="fw-bold mb-0">Order #${o.order_number}</h6>
                        <div>${statusHtml}</div>
                    </div>
                    <div class="small text-muted mb-2">Started: ${o.dispatch_started_at || ''}</div>
                    
                    <div class="bg-light p-2 rounded mt-2 border">
                        <div class="fw-bold small mb-1">Offer Timeline:</div>
                        <div style="max-height: 200px; overflow-y: auto;">
                            ${timelineHtml}
                        </div>
                    </div>
                </div>
            `;
        }).join('');
        
        ordersList.innerHTML = html;
    }

    cityFilter.addEventListener('change', fetchDispatchData);
    orderSearch.addEventListener('input', renderOrders);
    setInterval(fetchDispatchData, 3000);
    fetchDispatchData();
});
</script>
