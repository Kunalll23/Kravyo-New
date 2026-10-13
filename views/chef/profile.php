<div class="container py-4">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">
                <i class="bi bi-pencil-square text-warning me-2"></i>Edit Kitchen Profile
            </h2>
            <p class="text-muted mb-0">Update your kitchen details, story, and upload images</p>
        </div>
        <a href="<?= url('/chef/dashboard') ?>" class="btn btn-outline-secondary mt-3 mt-md-0">
            <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
        </a>
    </div>

    <?php if (!$kitchen): ?>
        <div class="alert alert-danger">Kitchen profile not found. Please contact support.</div>
    <?php else: ?>

    <form action="<?= url('/chef/profile/update') ?>" method="POST" enctype="multipart/form-data" id="kitchenProfileForm">
        <?= csrf_field() ?>

        <div class="row g-4">
            <!-- Left Column: Kitchen Details -->
            <div class="col-lg-7">
                <div class="card kravyo-card border-0">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-4"><i class="bi bi-shop me-2 text-warning"></i>Kitchen Details</h5>

                        <div class="mb-3">
                            <label for="kitchen_name" class="form-label fw-semibold">Kitchen Name <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-shop text-muted"></i></span>
                                <input type="text" class="form-control form-control-lg border-start-0" id="kitchen_name" name="kitchen_name"
                                       value="<?= sanitize($kitchen['kitchen_name'] ?? '') ?>" placeholder="e.g., Sunita's Kitchen" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="personal_story" class="form-label fw-semibold">
                                <i class="bi bi-chat-quote me-1 text-muted"></i>Your Kitchen Story
                            </label>
                            <textarea class="form-control" id="personal_story" name="personal_story" rows="5"
                                      placeholder="Share your cooking journey, specialties, and what makes your food special..."><?= sanitize($kitchen['personal_story'] ?? '') ?></textarea>
                            <small class="form-text text-muted">This story will be visible to customers on your kitchen page.</small>
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label fw-semibold">Street Address <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-geo-alt text-muted"></i></span>
                                <input type="text" class="form-control border-start-0" id="address" name="address"
                                       value="<?= sanitize($kitchen['address'] ?? '') ?>" placeholder="e.g., 102 Green Park Society, Ring Road" required>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="city" class="form-label fw-semibold">City <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="city" name="city"
                                       value="<?= sanitize($kitchen['city'] ?? '') ?>" placeholder="e.g., Surat" required>
                            </div>
                            <div class="col-md-6">
                                <label for="pincode" class="form-label fw-semibold">Pincode <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="pincode" name="pincode"
                                       value="<?= sanitize($kitchen['pincode'] ?? '') ?>" placeholder="e.g., 395007" pattern="[0-9]{6}" title="6-digit pincode" required>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Kitchen Map Location Card -->
                <div class="card kravyo-card border-0 mt-4">
                    <div class="card-body p-4">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                            <h5 class="fw-bold mb-0">
                                <i class="bi bi-geo-alt-fill me-2 text-danger"></i>Kitchen Map Location
                            </h5>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="btnChefDetectLoc">
                                <i class="bi bi-crosshair me-1"></i> Detect My Location
                            </button>
                        </div>
                        <p class="text-muted small mb-3">
                            Pin your kitchen on the map so local customers can discover your home kitchen and see accurate distance calculations.
                        </p>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="latitude" class="form-label fw-semibold small">Latitude</label>
                                <input type="number" step="any" class="form-control form-control-sm" id="latitude" name="latitude"
                                       value="<?= sanitize($kitchen['latitude'] ?? '') ?>" placeholder="e.g. 18.531400" min="-90" max="90">
                            </div>
                            <div class="col-md-6">
                                <label for="longitude" class="form-label fw-semibold small">Longitude</label>
                                <input type="number" step="any" class="form-control form-control-sm" id="longitude" name="longitude"
                                       value="<?= sanitize($kitchen['longitude'] ?? '') ?>" placeholder="e.g. 73.844600" min="-180" max="180">
                            </div>
                        </div>

                        <!-- Leaflet Map Container -->
                        <div id="chefLocationMap" style="height: 260px; border-radius: 8px; border: 1px solid #ced4da; z-index: 1;" class="shadow-sm"></div>
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <small class="text-muted"><i class="bi bi-info-circle me-1"></i>Click map or drag the marker to pin your kitchen</small>
                            <span id="chefLocationBadge" class="badge bg-light text-muted small">No pin set</span>
                        </div>
                    </div>
                </div>

                <!-- FSSAI & Hygiene Section -->
                <div class="card kravyo-card border-0 mt-4">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-4"><i class="bi bi-shield-check me-2 text-success"></i>FSSAI & Hygiene Verification</h5>

                        <div class="mb-3">
                            <label for="fssai_license" class="form-label fw-semibold">FSSAI License Number</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-upc-scan text-muted"></i></span>
                                <input type="text" class="form-control border-start-0" id="fssai_license" name="fssai_license"
                                       value="<?= sanitize($kitchen['fssai_license'] ?? '') ?>" placeholder="e.g., 21522001000123">
                            </div>
                            <small class="form-text text-muted">14-digit FSSAI license number. Leave blank if not available yet.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Hygiene Certificate / Document Upload</label>
                            <div class="upload-area" id="certUploadArea">
                                <?php if (!empty($kitchen['hygiene_certificate_image'])): ?>
                                    <img src="<?= UPLOAD_URL . '/kitchens/' . $kitchen['hygiene_certificate_image'] ?>"
                                         alt="Hygiene Certificate" class="upload-preview-img" id="certPreview">
                                    <p class="small text-success mt-2 mb-0"><i class="bi bi-check-circle me-1"></i>Certificate uploaded. Choose a new file to replace.</p>
                                <?php else: ?>
                                    <div id="certPlaceholder">
                                        <i class="bi bi-cloud-arrow-up display-4 text-muted"></i>
                                        <p class="text-muted mt-2 mb-0">Click or drag to upload hygiene certificate</p>
                                        <small class="text-muted">JPG, PNG, or WebP — Max 5 MB</small>
                                    </div>
                                    <img src="" alt="Certificate Preview" class="upload-preview-img d-none" id="certPreview">
                                <?php endif; ?>
                                <input type="file" class="upload-input" id="hygiene_certificate_image" name="hygiene_certificate_image"
                                       accept="image/jpeg,image/png,image/webp">
                            </div>
                        </div>

                        <!-- Current Badge Status -->
                        <div class="d-flex align-items-center gap-2 mt-3 p-3 bg-light rounded-3">
                            <i class="bi bi-info-circle text-primary"></i>
                            <span class="small">Current Hygiene Status:
                                <?php if ($kitchen['hygiene_badge'] === 'verified'): ?>
                                    <span class="badge-hygiene ms-2"><i class="bi bi-patch-check-fill me-1"></i>Verified</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary ms-2">Not Verified</span>
                                    <span class="text-muted"> — Will be reviewed by admin upon kitchen approval.</span>
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Banner Upload & Summary -->
            <div class="col-lg-5">
                <div class="card kravyo-card border-0">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-4"><i class="bi bi-image me-2 text-warning"></i>Kitchen Banner Image</h5>

                        <div class="upload-area upload-area-large" id="bannerUploadArea">
                            <?php if (!empty($kitchen['banner_image'])): ?>
                                <img src="<?= UPLOAD_URL . '/kitchens/' . $kitchen['banner_image'] ?>"
                                     alt="Kitchen Banner" class="upload-preview-img" id="bannerPreview">
                                <p class="small text-success mt-2 mb-0"><i class="bi bi-check-circle me-1"></i>Banner uploaded. Choose a new file to replace.</p>
                            <?php else: ?>
                                <div id="bannerPlaceholder">
                                    <i class="bi bi-camera display-3 text-muted"></i>
                                    <p class="text-muted mt-2 mb-0">Upload your kitchen banner photo</p>
                                    <small class="text-muted">Recommended: 1200x400 pixels</small>
                                </div>
                                <img src="" alt="Banner Preview" class="upload-preview-img d-none" id="bannerPreview">
                            <?php endif; ?>
                            <input type="file" class="upload-input" id="banner_image" name="banner_image"
                                   accept="image/jpeg,image/png,image/webp,image/gif">
                        </div>
                    </div>
                </div>

                <!-- Approval Status Card -->
                <div class="card kravyo-card border-0 mt-4">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3"><i class="bi bi-clipboard-check me-2 text-warning"></i>Approval Status</h5>
                        <div class="d-flex align-items-center gap-3 p-3 rounded-3 bg-light">
                            <div class="status-dot status-dot-<?= $kitchen['approval_status'] ?>"></div>
                            <div>
                                <strong class="d-block"><?= ucfirst($kitchen['approval_status']) ?></strong>
                                <?php if ($kitchen['approval_status'] === 'pending'): ?>
                                    <small class="text-muted">Under review by admin team</small>
                                <?php elseif ($kitchen['approval_status'] === 'approved'): ?>
                                    <small class="text-success">Your kitchen is live!</small>
                                <?php elseif ($kitchen['approval_status'] === 'rejected'): ?>
                                    <small class="text-danger"><?= sanitize($kitchen['admin_notes'] ?? 'Please update and resubmit') ?></small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tips Card -->
                <div class="card kravyo-card border-0 mt-4">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-3"><i class="bi bi-lightbulb me-2 text-warning"></i>Tips for Quick Approval</h6>
                        <ul class="list-unstyled mb-0 small text-muted">
                            <li class="mb-2"><i class="bi bi-check2 text-success me-2"></i>Fill in all kitchen details completely</li>
                            <li class="mb-2"><i class="bi bi-check2 text-success me-2"></i>Upload a clear banner image of your kitchen</li>
                            <li class="mb-2"><i class="bi bi-check2 text-success me-2"></i>Add your FSSAI license number if available</li>
                            <li class="mb-2"><i class="bi bi-check2 text-success me-2"></i>Upload hygiene certificate or clean kitchen photo</li>
                            <li class="mb-0"><i class="bi bi-check2 text-success me-2"></i>Write a compelling personal story</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="mt-4 d-flex gap-3 justify-content-end">
            <a href="<?= url('/chef/dashboard') ?>" class="btn btn-outline-secondary btn-lg px-4">Cancel</a>
            <button type="submit" class="btn btn-kravyo-primary btn-lg px-5">
                <i class="bi bi-check-lg me-1"></i> Save Kitchen Profile
            </button>
        </div>
    </form>

    <?php endif; ?>
</div>

<!-- Google Maps for Chef Location Picker -->
<?php include __DIR__ . '/../partials/google_maps_loader.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var latInput = document.getElementById('latitude');
    var lngInput = document.getElementById('longitude');
    var statusBadge = document.getElementById('chefLocationBadge');
    var detectBtn = document.getElementById('btnChefDetectLoc');

    var defaultLat = 21.1702; // Default fallback (Surat / Gujarat)
    var defaultLng = 72.8311;
    var defaultZoom = 13;

    var curLat = parseFloat(latInput.value);
    var curLng = parseFloat(lngInput.value);
    var hasExistingCoords = (!isNaN(curLat) && !isNaN(curLng) && curLat !== 0);

    var initLat = hasExistingCoords ? curLat : defaultLat;
    var initLng = hasExistingCoords ? curLng : defaultLng;
    var initZoom = hasExistingCoords ? 15 : defaultZoom;

    if (typeof KravyoMaps === 'undefined') return;

    KravyoMaps.init().then(() => {
        var map = KravyoMaps.createMap('chefLocationMap', initLat, initLng, initZoom);
        var marker = null;
        var infoWindow = new google.maps.InfoWindow();

        function updateMarker(lat, lng, label) {
            latInput.value = lat.toFixed(6);
            lngInput.value = lng.toFixed(6);
            if (statusBadge) {
                statusBadge.className = 'badge bg-success small';
                statusBadge.innerHTML = '<i class="bi bi-check-circle me-1"></i>' + (label || 'Pinned: ' + lat.toFixed(4) + ', ' + lng.toFixed(4));
            }

            if (!marker) {
                marker = new google.maps.Marker({
                    position: { lat: lat, lng: lng },
                    map: map,
                    draggable: true,
                    title: 'Kitchen Location'
                });
                marker.addListener('dragend', function(e) {
                    updateMarker(e.latLng.lat(), e.latLng.lng(), 'Pinned: ' + e.latLng.lat().toFixed(4) + ', ' + e.latLng.lng().toFixed(4));
                });
            } else {
                marker.setPosition({ lat: lat, lng: lng });
            }
            infoWindow.setContent('<strong>📍 Kitchen Location</strong><br>' + lat.toFixed(5) + ', ' + lng.toFixed(5));
            infoWindow.open(map, marker);
        }

        if (hasExistingCoords) {
            updateMarker(curLat, curLng, 'Saved: ' + curLat.toFixed(4) + ', ' + curLng.toFixed(4));
        }

        // Map click sets marker
        map.addListener('click', function(e) {
            updateMarker(e.latLng.lat(), e.latLng.lng(), 'Pinned: ' + e.latLng.lat().toFixed(4) + ', ' + e.latLng.lng().toFixed(4));
        });

        // Inputs change updates marker
        function onManualCoordChange() {
            var l1 = parseFloat(latInput.value);
            var l2 = parseFloat(lngInput.value);
            if (!isNaN(l1) && !isNaN(l2) && l1 >= -90 && l1 <= 90 && l2 >= -180 && l2 <= 180) {
                updateMarker(l1, l2, 'Manual: ' + l1.toFixed(4) + ', ' + l2.toFixed(4));
                map.panTo({lat: l1, lng: l2});
            }
        }
        latInput.addEventListener('change', onManualCoordChange);
        lngInput.addEventListener('change', onManualCoordChange);

        // Geolocation detect button
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
                        var uLat = pos.coords.latitude;
                        var uLng = pos.coords.longitude;
                        updateMarker(uLat, uLng, 'Current GPS Location');
                        map.setCenter({lat: uLat, lng: uLng});
                        map.setZoom(16);
                        
                        // Reverse Geocode
                        if (KravyoMaps.getLibs && KravyoMaps.getLibs().Geocoder) {
                            const geocoder = new (KravyoMaps.getLibs().Geocoder)();
                            geocoder.geocode({ location: {lat: uLat, lng: uLng} }, function(results, status) {
                                if (status === 'OK' && results[0]) {
                                    const place = results[0];
                                    const addressInput = document.querySelector('gmp-place-autocomplete');
                                    if (addressInput) {
                                        addressInput.value = place.formatted_address;
                                    } else {
                                        document.getElementById('address').value = place.formatted_address;
                                    }
                                    
                                    let city = '';
                                    let pincode = '';
                                    place.address_components.forEach(component => {
                                        const types = component.types;
                                        if (types.includes('locality')) {
                                            city = component.long_name;
                                        }
                                        if (types.includes('postal_code')) {
                                            pincode = component.long_name;
                                        }
                                    });
                                    
                                    const cityInput = document.getElementById('city');
                                    const zipInput = document.getElementById('pincode');
                                    if (city && cityInput) cityInput.value = city;
                                    if (pincode && zipInput) zipInput.value = pincode;
                                }
                            });
                        }
                    },
                    function(err) {
                        detectBtn.disabled = false;
                        detectBtn.innerHTML = '<i class="bi bi-crosshair me-1"></i> Detect My Location';
                        alert('Could not get your location: ' + err.message + '. Please click on the map manually.');
                    },
                    { enableHighAccuracy: true, timeout: 10000 }
                );
            });
        }
        
        // Attach Autocomplete
        KravyoMaps.initAutocomplete('address', map, marker, function(pos, place) {
            updateMarker(pos.lat, pos.lng, 'Pinned: ' + pos.lat.toFixed(4) + ', ' + pos.lng.toFixed(4));
            
            // Auto-fill city and pincode
            if (place.address_components) {
                let city = '';
                let pincode = '';
                place.address_components.forEach(component => {
                    const types = component.types;
                    if (types.includes('locality')) {
                        city = component.long_name;
                    }
                    if (types.includes('postal_code')) {
                        pincode = component.long_name;
                    }
                });
                
                const cityInput = document.getElementById('city');
                const zipInput = document.getElementById('pincode');
                if (city && cityInput) cityInput.value = city;
                if (pincode && zipInput) zipInput.value = pincode;
            }
        });
    });
});
</script>
