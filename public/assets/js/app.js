/**
 * Kravyo - Client-Side Script
 */

document.addEventListener('DOMContentLoaded', () => {
    console.log('Kravyo Application Initialized successfully.');

    // Auto-dismiss Bootstrap alert messages after 5 seconds
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            if (bsAlert) {
                bsAlert.close();
            }
        }, 5000);
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
    // Phase 4: Menu Management Image Previews
    // ============================================
    setupImagePreview('cat_image', 'catAddPreview', 'catAddPlaceholder');
    setupImagePreview('addDishImage', null, null); // Just for validation

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
                newAddressForm.querySelector('input[name="new_street_address"]')?.focus();
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
});

