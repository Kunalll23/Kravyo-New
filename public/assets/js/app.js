/**
 * Kravyo - Client-Side Script
 */

document.addEventListener('DOMContentLoaded', () => {
    console.log('Kravyo Application Initialized successfully.');

    // Auto-dismiss temporary alert messages only if marked .auto-dismiss
    const alerts = document.querySelectorAll('.alert-dismissible.auto-dismiss');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            if (bsAlert) {
                bsAlert.close();
            }
        }, 10000);
    });

    // Initialize Bootstrap tooltips if present
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltipTriggerList.forEach(tooltipTriggerEl => {
        new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // ============================================
    // Phase 3: Image Upload Preview & Availability Toggle
    // ============================================

    /**
     * Image Upload Preview — Shows selected image before form submission
     */
    function setupImagePreview(inputId, previewId, placeholderId) {
        const input = document.getElementById(inputId);
        const preview = document.getElementById(previewId);
        const placeholder = document.getElementById(placeholderId);

        if (!input || !preview) return;

        input.addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (!file) return;

            // Validate file type
            const validTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            if (!validTypes.includes(file.type)) {
                alert('Please select a valid image file (JPG, PNG, WebP, or GIF).');
                input.value = '';
                return;
            }

            // Validate file size (5MB max)
            if (file.size > 5 * 1024 * 1024) {
                alert('Image file size must be under 5 MB.');
                input.value = '';
                return;
            }

            // Show preview
            const reader = new FileReader();
            reader.onload = function (event) {
                preview.src = event.target.result;
                preview.classList.remove('d-none');
                if (placeholder) {
                    placeholder.classList.add('d-none');
                }
            };
            reader.readAsDataURL(file);
        });
    }

    // Initialize upload previews for Chef Profile page
    setupImagePreview('banner_image', 'bannerPreview', 'bannerPlaceholder');
    setupImagePreview('hygiene_certificate_image', 'certPreview', 'certPlaceholder');

    /**
     * Kitchen Availability Toggle — AJAX POST to toggle open/closed
     */
    const availabilityToggle = document.getElementById('availabilityToggle');
    if (availabilityToggle) {
        availabilityToggle.addEventListener('click', function () {
            const track = this.querySelector('.toggle-track');
            const text = this.querySelector('.toggle-text');
            const csrfToken = this.dataset.csrf;

            // Determine base URL from current page
            const baseUrl = window.location.origin + window.location.pathname.split('/chef')[0];

            fetch(baseUrl + '/chef/toggle-availability', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: '_csrf=' + encodeURIComponent(csrfToken)
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update toggle visual state
                        if (data.is_open) {
                            track.classList.add('active');
                            text.className = 'toggle-text fw-bold text-success';
                            text.textContent = '● OPEN';
                        } else {
                            track.classList.remove('active');
                            text.className = 'toggle-text fw-bold text-danger';
                            text.textContent = '● CLOSED';
                        }
                    } else {
                        alert(data.message || 'Failed to update availability.');
                    }
                })
                .catch(error => {
                    console.error('Toggle error:', error);
                    alert('Network error. Please try again.');
                });
        });
    }

    // ============================================
    // Phase 4: Menu Management Image Previews & Out-of-Stock Toggle
    // ============================================
    setupImagePreview('cat_image', 'catAddPreview', 'catAddPlaceholder');
    setupImagePreview('addDishImage', null, null); // Just for validation

    const dishToggles = document.querySelectorAll('.dish-availability-toggle');
    dishToggles.forEach(toggle => {
        toggle.addEventListener('change', function() {
            const dishId = this.dataset.dishId;
            const csrfInput = document.querySelector('input[name="_csrf"]');
            if (!csrfInput) return;
            
            const formData = new URLSearchParams();
            formData.append('_csrf', csrfInput.value);
            
            const baseUrl = window.location.origin + window.location.pathname.split('/chef')[0];
            fetch(baseUrl + '/chef/dish/toggle-availability/' + dishId, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData.toString()
            })
            .then(res => res.json())
            .then(data => {
                const badge = document.getElementById('badgeDish' + dishId);
                if (data.success) {
                    if (data.is_available === 1) {
                        if (badge) badge.classList.add('d-none');
                        showToast('Success', 'Dish is now In Stock', 'success');
                    } else {
                        if (badge) badge.classList.remove('d-none');
                        showToast('Info', 'Dish is now Out of Stock', 'warning');
                    }
                } else {
                    // Revert the toggle
                    this.checked = !this.checked;
                    showToast('Error', data.message || 'Failed to update status', 'danger');
                }
            })
            .catch(err => {
                this.checked = !this.checked;
                showToast('Error', 'Network error. Try again.', 'danger');
            });
        });
    });

    // ============================================
    // Phase 5: Customer Discovery, Cart & Customization
    // ============================================

    /**
     * Dish Detail — Quantity Selector (+/- buttons)
     */
    const qtyInput = document.getElementById('qtyInput');
    const qtyMinus = document.getElementById('qtyMinus');
    const qtyPlus = document.getElementById('qtyPlus');

    if (qtyInput && qtyMinus && qtyPlus) {
        const dishPrice = parseFloat(qtyInput.closest('form')?.querySelector('input[name="menu_item_id"]')
            ?.closest('.col-lg-6')?.querySelector('.dish-detail-price')?.textContent?.replace(/[^0-9.]/g, '') || '0');

        qtyMinus.addEventListener('click', () => {
            let val = parseInt(qtyInput.value, 10);
            if (val > 1) {
                qtyInput.value = val - 1;
                updateItemTotal(val - 1, dishPrice);
            }
        });

        qtyPlus.addEventListener('click', () => {
            let val = parseInt(qtyInput.value, 10);
            if (val < 10) {
                qtyInput.value = val + 1;
                updateItemTotal(val + 1, dishPrice);
            }
        });

        function updateItemTotal(qty, price) {
            const totalEl = document.getElementById('itemTotal');
            if (totalEl && price > 0) {
                totalEl.textContent = '₹' + (qty * price).toFixed(2);
            }
        }
    }

    /**
     * Update Cart Badge Count in Navbar
     * Called after any cart AJAX operation
     */
    function updateCartBadge(count) {
        const badges = document.querySelectorAll('.cart-badge-count');
        badges.forEach(badge => {
            badge.textContent = count;
            if (count > 0) {
                badge.classList.remove('d-none');
            } else {
                badge.classList.add('d-none');
            }
        });
    }

    // Expose updateCartBadge globally for potential use
    window.KravyoCart = {
        updateBadge: updateCartBadge
    };
    // ============================================
    // Phase 6: Order Placement & Checkout
    // ============================================

    /**
     * Checkout — Toggle New Address Form
     */
    const toggleNewAddressBtn = document.getElementById('toggleNewAddress');
    const newAddressForm = document.getElementById('newAddressForm');
    const addressRadios = document.querySelectorAll('.address-radio');

    if (toggleNewAddressBtn && newAddressForm) {
        toggleNewAddressBtn.addEventListener('click', () => {
            newAddressForm.classList.toggle('d-none');
            if (!newAddressForm.classList.contains('d-none')) {
                // Focus first input
                const firstInput = newAddressForm.querySelector('input[name="new_apartment"]') || newAddressForm.querySelector('input[name="new_street_address"]');
                firstInput?.focus();
                // Uncheck all saved addresses
                addressRadios.forEach(radio => {
                    radio.checked = false;
                    radio.closest('.address-card').classList.remove('selected');
                });
            }
        });

        // If user clicks a saved address, hide the new address form
        addressRadios.forEach(radio => {
            radio.addEventListener('change', () => {
                newAddressForm.classList.add('d-none');
                addressRadios.forEach(r => r.closest('.address-card').classList.remove('selected'));
                radio.closest('.address-card').classList.add('selected');
            });
        });
    }

    /**
     * Checkout — Payment Method Selection Styling
     */
    const paymentRadios = document.querySelectorAll('.payment-radio');
    if (paymentRadios.length > 0) {
        paymentRadios.forEach(radio => {
            radio.addEventListener('change', () => {
                paymentRadios.forEach(r => r.closest('.payment-method-card').classList.remove('selected'));
                radio.closest('.payment-method-card').classList.add('selected');
            });
        });
    }

    // ============================================
    // Real-Time Notification Polling
    // ============================================
    
    // Toast helper for real-time notifications
    window.showNotificationToast = function(title, message, link) {
        const toastContainer = document.getElementById('toast-container') || (() => {
            const el = document.createElement('div');
            el.id = 'toast-container';
            el.className = 'toast-container position-fixed bottom-0 end-0 p-3';
            el.style.zIndex = '1090';
            document.body.appendChild(el);
            return el;
        })();

        const toastId = 'toast-' + Date.now();
        const html = `
        <div id="${toastId}" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header bg-dark text-white">
                <i class="bi bi-bell-fill me-2 text-warning"></i>
                <strong class="me-auto">${String(title).replace(/[&<>'"]/g, t => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[t]||t))}</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                ${String(message).replace(/[&<>'"]/g, t => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[t]||t))}
                ${link ? `<div class="mt-2 pt-2 border-top"><a href="${link}" class="btn btn-sm btn-primary">View</a></div>` : ''}
            </div>
        </div>`;
        
        toastContainer.insertAdjacentHTML('beforeend', html);
        const toastEl = document.getElementById(toastId);
        const toast = new bootstrap.Toast(toastEl, { autohide: true, delay: 10000 });
        toast.show();
        
        toastEl.addEventListener('hidden.bs.toast', () => {
            toastEl.remove();
        });
    };

    const notifBellBtn = document.getElementById('notifBellBtn');

    // ------------------------------------------------------------------
    // URL helper — MUST include APP_URL (e.g. http://localhost/Kravyo).
    // Previously we used a root-relative path ('/chef/api/...') which the
    // browser resolved to http://localhost/chef/api/... (XAMPP root) → 404,
    // so polling never reached the app and nothing updated without refresh.
    // ------------------------------------------------------------------
    const KRAVYO_BASE = (window.KRAVYO_APP_URL || '').replace(/\/+$/, '');
    const KRAVYO_PREFIX = window.KRAVYO_CTX_PREFIX || '';
    const apiUrl = (path) => KRAVYO_BASE + KRAVYO_PREFIX + path;
    const appLink = (link) => {
        if (!link) return KRAVYO_BASE + KRAVYO_PREFIX + '/notifications';
        if (/^https?:\/\//i.test(link)) return link;
        return KRAVYO_BASE + '/' + String(link).replace(/^\/+/, '');
    };

    // ------------------------------------------------------------------
    // Notification Sound
    // Browsers block audio until the user has interacted with the page
    // at least once. We "unlock" both an AudioContext and an <audio>
    // element on the first click/keypress, then reuse them for every ding.
    // ------------------------------------------------------------------
    let audioCtx = null;
    let audioUnlocked = false;
    const dingEl = new Audio(KRAVYO_BASE + '/public/assets/sounds/notification.wav');
    dingEl.preload = 'auto';
    dingEl.volume = 0.8;

    const playSynthDing = () => {
        if (!audioCtx) return false;
        try {
            if (audioCtx.state === 'suspended') audioCtx.resume();
            const now = audioCtx.currentTime;
            [[880, 0], [1318.5, 0.18]].forEach(([freq, offset]) => {
                const osc = audioCtx.createOscillator();
                const gain = audioCtx.createGain();
                osc.type = 'sine';
                osc.frequency.value = freq;
                gain.gain.setValueAtTime(0.0001, now + offset);
                gain.gain.exponentialRampToValueAtTime(0.5, now + offset + 0.02);
                gain.gain.exponentialRampToValueAtTime(0.0001, now + offset + 0.45);
                osc.connect(gain).connect(audioCtx.destination);
                osc.start(now + offset);
                osc.stop(now + offset + 0.5);
            });
            return true;
        } catch (e) {
            console.warn('[Kravyo] Synth ding failed:', e);
            return false;
        }
    };

    window.kravyoPlayDing = () => {
        try {
            dingEl.currentTime = 0;
            const p = dingEl.play();
            if (p && p.catch) {
                p.catch(err => {
                    console.warn('[Kravyo] <audio> blocked, falling back to synth:', err && err.name);
                    if (!playSynthDing()) showSoundPrompt();
                });
            }
        } catch (e) {
            if (!playSynthDing()) showSoundPrompt();
        }
    };

    const unlockAudio = () => {
        if (audioUnlocked) return;
        audioUnlocked = true;
        try {
            audioCtx = audioCtx || new (window.AudioContext || window.webkitAudioContext)();
            if (audioCtx.state === 'suspended') audioCtx.resume();
        } catch (e) {}
        // Prime the <audio> element with a silent play so later plays are allowed
        dingEl.muted = true;
        const p = dingEl.play();
        const reset = () => { dingEl.pause(); dingEl.currentTime = 0; dingEl.muted = false; };
        if (p && p.then) p.then(reset).catch(reset); else reset();
        hideSoundPrompt();
        console.log('[Kravyo] Notification sound enabled.');
    };
    ['click', 'keydown', 'touchstart'].forEach(evt =>
        document.addEventListener(evt, unlockAudio, { once: false, passive: true })
    );

    // Small floating prompt so the user knows sound needs one click
    let soundPromptEl = null;
    function showSoundPrompt() {
        if (audioUnlocked || soundPromptEl || !notifBellBtn) return;
        soundPromptEl = document.createElement('button');
        soundPromptEl.type = 'button';
        soundPromptEl.id = 'kravyoEnableSoundBtn';
        soundPromptEl.className = 'btn btn-warning btn-sm shadow position-fixed fw-semibold';
        soundPromptEl.style.cssText = 'left:16px;bottom:16px;z-index:1095;border-radius:999px;';
        soundPromptEl.innerHTML = '<i class="bi bi-volume-up-fill me-1"></i> Click to enable order sounds';
        soundPromptEl.addEventListener('click', () => { unlockAudio(); window.kravyoPlayDing(); });
        document.body.appendChild(soundPromptEl);
    }
    function hideSoundPrompt() {
        if (soundPromptEl) { soundPromptEl.remove(); soundPromptEl = null; }
    }

    // ------------------------------------------------------------------
    // Tab title flash when a notification arrives while tab is hidden
    // ------------------------------------------------------------------
    const originalTitle = document.title;
    let titleFlashTimer = null;
    const flashTitle = (count) => {
        if (!document.hidden) return;
        clearInterval(titleFlashTimer);
        let on = false;
        titleFlashTimer = setInterval(() => {
            document.title = on ? originalTitle : `🔔 (${count}) New notification`;
            on = !on;
        }, 1000);
    };
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) { clearInterval(titleFlashTimer); document.title = originalTitle; }
    });

    // ------------------------------------------------------------------
    // Live-refresh the current page section (e.g. Chef Incoming Orders)
    // Any element with [data-live-refresh] is re-fetched & swapped in place.
    // ------------------------------------------------------------------
    const refreshLiveRegions = () => {
        const regions = document.querySelectorAll('[data-live-refresh]');
        if (!regions.length) return;
        fetch(window.location.href, { credentials: 'same-origin', cache: 'no-store' })
            .then(r => r.text())
            .then(html => {
                const doc = new DOMParser().parseFromString(html, 'text/html');
                regions.forEach(region => {
                    if (!region.id) return;
                    const fresh = doc.getElementById(region.id);
                    if (fresh) region.innerHTML = fresh.innerHTML;
                });
            })
            .catch(err => console.warn('[Kravyo] Live refresh failed:', err));
    };

    // ------------------------------------------------------------------
    // Polling
    // ------------------------------------------------------------------
    if (notifBellBtn) {
        let lastNotifId = -1; // -1 = not initialised yet

        // Track which notification IDs were already toasted in this tab so the
        // same toast is not repeated when the user navigates between pages.
        const SHOWN_KEY = 'kravyo_toasted_ids';
        const getShown = () => {
            try { return new Set(JSON.parse(sessionStorage.getItem(SHOWN_KEY) || '[]').map(String)); }
            catch (e) { return new Set(); }
        };
        const markShown = (id) => {
            try {
                const s = getShown(); s.add(String(id));
                sessionStorage.setItem(SHOWN_KEY, JSON.stringify(Array.from(s).slice(-100)));
            } catch (e) {}
        };
        const RECENT_SECONDS = 20; // toast items created just before a redirect
        const fetchJson = (url) =>
            fetch(url, {
                credentials: 'same-origin',
                cache: 'no-store',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            }).then(res => {
                const ct = res.headers.get('content-type') || '';
                if (!res.ok || !ct.includes('application/json')) {
                    throw new Error(`HTTP ${res.status} (${ct || 'no content-type'}) from ${url}`);
                }
                return res.json();
            });

        const renderDropdownItem = (notif) => {
            const notifList = document.getElementById('notifDropdownList');
            if (!notifList) return;
            const emptyMsg = notifList.querySelector('.bi-bell-slash');
            if (emptyMsg && emptyMsg.parentElement) emptyMsg.parentElement.remove();
            const msg = escapeHtml(notif.message || '');
            const html = `
                <a href="${escapeHtml(appLink(notif.link))}" class="d-flex align-items-start gap-2 px-3 py-2 text-decoration-none border-bottom notif-item bg-light" style="color:inherit;">
                    <i class="bi bi-bag-check-fill text-success mt-1 flex-shrink-0"></i>
                    <div class="flex-grow-1 overflow-hidden">
                        <div class="fw-semibold small text-truncate text-dark">${escapeHtml(notif.title || 'Notification')}</div>
                        <div class="small text-muted" style="font-size:0.78rem;line-height:1.3;">${msg.length > 80 ? msg.substring(0, 80) + '…' : msg}</div>
                        <div class="text-muted" style="font-size:0.7rem;">Just now</div>
                    </div>
                    <span class="flex-shrink-0 mt-1" style="width:8px;height:8px;background:#e85d04;border-radius:50%;display:inline-block;"></span>
                </a>`;
            notifList.insertAdjacentHTML('afterbegin', html);
        };

        const updateBadge = (count) => {
            const badge = document.getElementById('notifBadge');
            if (!badge || count === undefined) return;
            if (count > 0) {
                badge.textContent = count > 99 ? '99+' : count;
                badge.classList.remove('d-none');
            } else {
                badge.classList.add('d-none');
            }
        };

        const poll = () => {
            if (lastNotifId < 0) return;
            fetchJson(apiUrl('/api/notifications/unread?last_id=' + lastNotifId))
                .then(data => {
                    updateBadge(data.unread_count);
                    const fresh = Array.isArray(data.new) ? data.new : [];
                    if (!fresh.length) return;

                    const shownSet = getShown();
                    fresh.forEach(notif => {
                        lastNotifId = Math.max(lastNotifId, parseInt(notif.id, 10) || 0);
                        if (!shownSet.has(String(notif.id))) {
                            showNotificationToast(notif.title, notif.message, appLink(notif.link));
                            markShown(notif.id);
                        }
                        renderDropdownItem(notif);
                    });

                    window.kravyoPlayDing();
                    flashTitle(data.unread_count || fresh.length);
                    refreshLiveRegions();
                })
                .catch(err => console.error('[Kravyo] Notification poll failed:', err.message || err));
        };

        // Initialise tracker with the newest existing notification id
        fetchJson(apiUrl('/notifications/latest'))
            .then(data => {
                const list = data.notifications || [];
                const ids = list.map(n => parseInt(n.id, 10) || 0);
                lastNotifId = ids.length ? Math.max(...ids) : 0;
                console.log('[Kravyo] Notification polling started. last_id =', lastNotifId);

                // Show toasts for unread notifications created moments ago
                // (e.g. "Order Placed" right before the redirect to tracking page).
                const shownSet = getShown();
                const recent = list
                    .filter(n => !parseInt(n.is_read, 10)
                        && n.seconds_ago !== undefined && n.seconds_ago !== null
                        && parseInt(n.seconds_ago, 10) <= RECENT_SECONDS
                        && !shownSet.has(String(n.id)))
                    .reverse(); // oldest first
                recent.forEach(n => {
                    showNotificationToast(n.title, n.message, appLink(n.link));
                    markShown(n.id);
                });
                if (recent.length) window.kravyoPlayDing();
            })
            .catch(err => {
                console.error('[Kravyo] Could not init notification tracker:', err.message || err);
                lastNotifId = 0;
            });

        setInterval(poll, 5000);

        // Show the "enable sound" pill shortly after load if user hasn't clicked yet
        setTimeout(showSoundPrompt, 1500);
    }

    function showNotificationToast(title, message, url) {
        // Create toast container if it doesn't exist
        let toastContainer = document.getElementById('toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toast-container';
            toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
            toastContainer.style.zIndex = '1100';
            document.body.appendChild(toastContainer);
        }

        const toastId = 'toast-' + Date.now();
        const toastHTML = `
            <div id="${toastId}" class="toast border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header bg-dark text-white border-bottom-0">
                    <i class="bi bi-bell-fill text-warning me-2"></i>
                    <strong class="me-auto">${escapeHtml(title)}</strong>
                    <small>Just now</small>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body bg-white text-dark">
                    ${escapeHtml(message)}
                    ${url ? `<div class="mt-2 pt-2 border-top"><a href="${escapeHtml(url)}" class="btn btn-sm btn-kravyo-primary w-100">View Details</a></div>` : ''}
                </div>
            </div>
        `;
        
        toastContainer.insertAdjacentHTML('beforeend', toastHTML);
        const toastEl = document.getElementById(toastId);
        const toast = new bootstrap.Toast(toastEl, { delay: 10000 });
        toast.show();
        
        // Cleanup after hidden
        toastEl.addEventListener('hidden.bs.toast', () => {
            toastEl.remove();
        });
    }

    function escapeHtml(unsafe) {
        if (!unsafe) return '';
        return (unsafe + '').replace(/[&<>"']/g, function(m) {
            switch (m) {
                case '&': return '&amp;';
                case '<': return '&lt;';
                case '>': return '&gt;';
                case '"': return '&quot;';
                case "'": return '&#039;';
            }
        });
    }
});

