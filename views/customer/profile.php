<div class="container py-5">
    <div class="row mb-4">
        <div class="col-12 text-center text-md-start">
            <h2 class="fw-bold">My Profile</h2>
            <p class="text-muted">Manage your personal information and delivery addresses</p>
        </div>
    </div>

    <div class="row g-4">
        <!-- Profile Settings Column -->
        <div class="col-lg-5">
            <div class="card kravyo-card border-0 h-100">
                <div class="card-header bg-transparent border-0 pt-4 pb-0">
                    <h5 class="fw-bold mb-0"><i class="bi bi-person-circle text-warning me-2"></i>Personal Details</h5>
                </div>
                <div class="card-body p-4">
                    <form action="<?= url('/profile/update') ?>" method="POST">
                        <?= csrf_field() ?>
                        
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold text-uppercase">Account Status</label>
                            <div>
                                <?php if ($user['status'] === 'active'): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-danger"><?= ucfirst(sanitize($user['status'])) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">Email Address (Read-only)</label>
                            <input type="email" class="form-control bg-light" id="email" value="<?= sanitize($user['email']) ?>" readonly disabled>
                        </div>

                        <div class="mb-3">
                            <label for="full_name" class="form-label fw-semibold">Full Name</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" value="<?= sanitize($user['full_name']) ?>" required pattern="^[A-Za-z][A-Za-z' \-]{1,49}$" title="Please enter a valid name using letters, spaces, hyphens, or apostrophes only.">
                        </div>

                        <div class="mb-4">
                            <label for="phone" class="form-label fw-semibold">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone" value="<?= sanitize($user['phone']) ?>" required pattern="[6-9][0-9]{9}" title="Please enter a valid Indian mobile number starting with 6, 7, 8, or 9" maxlength="10" minlength="10" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);">
                        </div>

                        <button type="submit" class="btn btn-kravyo-primary w-100"><i class="bi bi-save me-2"></i>Save Changes</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Address Book Column -->
        <div class="col-lg-7">
            <div class="card kravyo-card border-0 h-100">
                <div class="card-header bg-transparent border-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0"><i class="bi bi-geo-alt-fill text-danger me-2"></i>Saved Addresses</h5>
                    <button type="button" class="btn btn-sm btn-outline-warning text-dark fw-bold" data-bs-toggle="modal" data-bs-target="#addAddressModal">
                        <i class="bi bi-plus-lg me-1"></i> Add New
                    </button>
                </div>
                <div class="card-body p-4">
                    <?php if (empty($addresses)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-house-door display-4 mb-3"></i>
                            <p>You haven't saved any delivery addresses yet.</p>
                            <button type="button" class="btn btn-kravyo-primary" data-bs-toggle="modal" data-bs-target="#addAddressModal">Add Your First Address</button>
                        </div>
                    <?php else: ?>
                        <div class="row g-3">
                            <?php foreach ($addresses as $address): ?>
                                <div class="col-md-6">
                                    <div class="card h-100 border bg-light shadow-sm">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <span class="badge bg-secondary text-uppercase"><?= sanitize($address['address_type']) ?></span>
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-link text-dark p-0" type="button" data-bs-toggle="dropdown">
                                                        <i class="bi bi-three-dots-vertical"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                                        <li>
                                                            <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#editAddressModal<?= $address['id'] ?>">
                                                                <i class="bi bi-pencil me-2 text-primary"></i> Edit
                                                            </button>
                                                        </li>
                                                        <li>
                                                            <form action="<?= url('/profile/address/delete/' . $address['id']) ?>" method="POST" onsubmit="return confirm('Are you sure you want to delete this address?');">
                                                                <?= csrf_field() ?>
                                                                <button type="submit" class="dropdown-item text-danger">
                                                                    <i class="bi bi-trash me-2"></i> Delete
                                                                </button>
                                                            </form>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                            <p class="mb-1 fw-bold"><?= sanitize($address['street_address']) ?></p>
                                            <?php if (!empty($address['landmark'])): ?>
                                                <p class="mb-1 text-muted small">Landmark: <?= sanitize($address['landmark']) ?></p>
                                            <?php endif; ?>
                                            <p class="mb-0 text-muted small"><?= sanitize($address['city']) ?> - <?= sanitize($address['pincode']) ?></p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Edit Address Modal for this specific address -->
                                <div class="modal fade" id="editAddressModal<?= $address['id'] ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow">
                                            <div class="modal-header bg-light border-0">
                                                <h5 class="modal-title fw-bold">Edit Address</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form action="<?= url('/profile/address/edit/' . $address['id']) ?>" method="POST">
                                                <div class="modal-body p-4">
                                                    <?= csrf_field() ?>
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Address Type</label>
                                                        <select class="form-select" name="address_type" required>
                                                            <option value="Home" <?= $address['address_type'] === 'Home' ? 'selected' : '' ?>>Home</option>
                                                            <option value="Work" <?= $address['address_type'] === 'Work' ? 'selected' : '' ?>>Work</option>
                                                            <option value="Other" <?= $address['address_type'] === 'Other' ? 'selected' : '' ?>>Other</option>
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Apartment No / Room No</label>
                                                        <input type="text" class="form-control" name="apartment" value="<?= sanitize($address['apartment'] ?? '') ?>" placeholder="e.g. Flat 402, Block B / Room 12">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Street Address / Society / Area</label>
                                                        <textarea class="form-control" name="street_address" rows="2" required><?= sanitize($address['street_address']) ?></textarea>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Landmark (Optional)</label>
                                                        <input type="text" class="form-control" name="landmark" value="<?= sanitize($address['landmark'] ?? '') ?>">
                                                    </div>
                                                    <div class="row mb-3">
                                                        <div class="col-6">
                                                            <label class="form-label fw-semibold">City</label>
                                                            <input type="text" class="form-control" name="city" value="<?= sanitize($address['city']) ?>" required>
                                                        </div>
                                                        <div class="col-6">
                                                            <label class="form-label fw-semibold">Pincode</label>
                                                            <input type="text" class="form-control" name="pincode" pattern="[0-9]{6}" title="Please enter a valid 6-digit pincode" value="<?= sanitize($address['pincode']) ?>" required>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-0 bg-light">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-kravyo-primary">Save Changes</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Address Modal -->
<div class="modal fade" id="addAddressModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light border-0">
                <h5 class="modal-title fw-bold">Add New Address</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= url('/profile/address/add') ?>" method="POST" id="profileAddAddressForm">
                <div class="modal-body p-4">
                    <?= csrf_field() ?>
                    
                    <div class="row g-3">
                        <!-- Row 1: Address Type (col-md-4) + Apartment No / Room No (col-md-8) -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Address Type</label>
                            <select class="form-select" name="address_type" required>
                                <option value="Home" selected>🏠 Home</option>
                                <option value="Work">🏢 Work</option>
                                <option value="Other">📍 Other</option>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Apartment No / Room No *</label>
                            <input type="text" class="form-control" name="apartment" id="profile_apartment" placeholder="e.g. Flat 402, Block B / Room 12">
                        </div>

                        <!-- Row 2: Street Address (Full-width col-12 for Google Places Autocomplete) -->
                        <div class="col-12">
                            <label class="form-label fw-semibold">Street Address *</label>
                            <input type="text" class="form-control" name="street_address" id="profile_street_address" placeholder="Search society, building name, street, area..." required>
                        </div>

                        <!-- Row 3: Landmark + City + Pincode -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Landmark (Optional)</label>
                            <input type="text" class="form-control" name="landmark" id="profile_landmark" placeholder="Near...">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">City *</label>
                            <input type="text" class="form-control" name="city" id="profile_city" placeholder="e.g. Surat" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Pincode *</label>
                            <input type="text" class="form-control" name="pincode" id="profile_pincode" pattern="[0-9]{6}" title="Please enter a valid 6-digit pincode" placeholder="e.g. 395007" required>
                        </div>
                    </div>

                    <!-- Map Location Picker Section -->
                    <div class="mt-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label fw-semibold mb-0">
                                <i class="bi bi-pin-map-fill text-danger me-1"></i>Pin your exact location on the map *
                            </label>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="btnProfileDetectLoc">
                                <i class="bi bi-crosshair me-1"></i> Detect My Location
                            </button>
                        </div>
                        <p class="small text-muted mb-2">Drag the marker to your exact building to help our delivery partners find you.</p>

                        <!-- Google Maps loader & Container -->
                        <?php include __DIR__ . '/../partials/google_maps_loader.php'; ?>
                        <div id="profileAddressMap" style="height: 240px; border-radius: 8px; border: 2px solid #dee2e6; z-index: 1;"></div>
                        <input type="hidden" name="latitude" id="profile_lat">
                        <input type="hidden" name="longitude" id="profile_lng">
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-kravyo-primary">Save Address</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let profileMap = null;
    let profileMarker = null;
    let profileMapInitialized = false;

    const addAddressModalEl = document.getElementById('addAddressModal');
    if (!addAddressModalEl) return;

    // Initialize or resize map when modal opens
    addAddressModalEl.addEventListener('shown.bs.modal', function () {
        if (!profileMapInitialized && typeof KravyoMaps !== 'undefined') {
            KravyoMaps.init().then(() => {
                const latInput = document.getElementById('profile_lat');
                const lngInput = document.getElementById('profile_lng');
                const initialLat = 21.1702;
                const initialLng = 72.8311;

                if (latInput && !latInput.value) latInput.value = initialLat;
                if (lngInput && !lngInput.value) lngInput.value = initialLng;

                profileMap = KravyoMaps.createMap('profileAddressMap', initialLat, initialLng, 14);
                profileMarker = KravyoMaps.addDraggableMarker(profileMap, initialLat, initialLng, function(lat, lng) {
                    if (latInput) latInput.value = lat.toFixed(8);
                    if (lngInput) lngInput.value = lng.toFixed(8);
                });

                // Attach Google Places Autocomplete to profile_street_address
                KravyoMaps.initAutocomplete('profile_street_address', profileMap, profileMarker, function(pos, place) {
                    if (latInput) latInput.value = pos.lat.toFixed(8);
                    if (lngInput) lngInput.value = pos.lng.toFixed(8);

                    if (place.address_components) {
                        let city = '';
                        let pincode = '';

                        place.address_components.forEach(component => {
                            const types = component.types;
                            if (types.includes('locality')) city = component.long_name;
                            if (types.includes('postal_code')) pincode = component.long_name;
                        });

                        const cityInput = document.getElementById('profile_city');
                        const zipInput = document.getElementById('profile_pincode');
                        const landmarkInput = document.getElementById('profile_landmark');

                        if (city && cityInput) cityInput.value = city;
                        if (pincode && zipInput) zipInput.value = pincode;

                        if (place.name && landmarkInput && place.name !== city) {
                            landmarkInput.value = place.name;
                        }
                    }
                });

                // Map click to move marker
                profileMap.addListener('click', function(e) {
                    const lat = e.latLng.lat();
                    const lng = e.latLng.lng();
                    if (profileMarker) profileMarker.position = e.latLng;
                    if (latInput) latInput.value = lat.toFixed(8);
                    if (lngInput) lngInput.value = lng.toFixed(8);
                });

                profileMapInitialized = true;
            });
        } else if (profileMap) {
            // Re-trigger layout resize for Bootstrap modal
            if (typeof google !== 'undefined' && google.maps && google.maps.event) {
                google.maps.event.trigger(profileMap, 'resize');
                const latInput = document.getElementById('profile_lat');
                const lngInput = document.getElementById('profile_lng');
                if (latInput && lngInput && latInput.value && lngInput.value) {
                    profileMap.setCenter({ lat: parseFloat(latInput.value), lng: parseFloat(lngInput.value) });
                }
            }
        }
    });

    // Detect My Location
    const detectBtn = document.getElementById('btnProfileDetectLoc');
    if (detectBtn) {
        detectBtn.addEventListener('click', function() {
            if (!navigator.geolocation) {
                alert('Geolocation is not supported by your browser.');
                return;
            }
            detectBtn.disabled = true;
            detectBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Detecting...';

            navigator.geolocation.getCurrentPosition(
                function(pos) {
                    detectBtn.disabled = false;
                    detectBtn.innerHTML = '<i class="bi bi-crosshair me-1"></i> Detect My Location';
                    const uLat = pos.coords.latitude;
                    const uLng = pos.coords.longitude;

                    const newPos = { lat: uLat, lng: uLng };
                    const latInput = document.getElementById('profile_lat');
                    const lngInput = document.getElementById('profile_lng');

                    if (profileMarker) profileMarker.position = newPos;
                    if (profileMap) profileMap.setCenter(newPos);

                    if (latInput) latInput.value = uLat.toFixed(8);
                    if (lngInput) lngInput.value = uLng.toFixed(8);

                    // Reverse Geocoding
                    if (KravyoMaps.getLibs && KravyoMaps.getLibs().Geocoder) {
                        const geocoder = new (KravyoMaps.getLibs().Geocoder)();
                        geocoder.geocode({ location: newPos }, function(results, status) {
                            if (status === 'OK' && results[0]) {
                                const place = results[0];
                                const streetInput = document.getElementById('profile_street_address');
                                const cityInput = document.getElementById('profile_city');
                                const zipInput = document.getElementById('profile_pincode');

                                if (streetInput) streetInput.value = place.formatted_address;

                                let city = '';
                                let pincode = '';
                                place.address_components.forEach(component => {
                                    const types = component.types;
                                    if (types.includes('locality')) city = component.long_name;
                                    if (types.includes('postal_code')) pincode = component.long_name;
                                });

                                if (city && cityInput) cityInput.value = city;
                                if (pincode && zipInput) zipInput.value = pincode;
                            }
                        });
                    }
                },
                function(err) {
                    detectBtn.disabled = false;
                    detectBtn.innerHTML = '<i class="bi bi-crosshair me-1"></i> Detect My Location';
                    alert('Unable to detect location: ' + err.message);
                },
                { enableHighAccuracy: true, timeout: 10000 }
            );
        });
    }
});
</script>
