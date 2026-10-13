<?php
$filePath = __DIR__ . '/../views/chef/profile.php';
$content = file_get_contents($filePath);

$isCrlf = strpos($content, "\r\n") !== false;
$eol = $isCrlf ? "\r\n" : "\n";

// 1. Target right after city and pincode row
$searchTarget = '                        </div>
                    </div>
                </div>

                <!-- FSSAI & Hygiene Section -->';

$mapCard = '                        </div>
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
                                       value="<?= sanitize($kitchen[\'latitude\'] ?? \'\') ?>" placeholder="e.g. 18.531400" min="-90" max="90">
                            </div>
                            <div class="col-md-6">
                                <label for="longitude" class="form-label fw-semibold small">Longitude</label>
                                <input type="number" step="any" class="form-control form-control-sm" id="longitude" name="longitude"
                                       value="<?= sanitize($kitchen[\'longitude\'] ?? \'\') ?>" placeholder="e.g. 73.844600" min="-180" max="180">
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

                <!-- FSSAI & Hygiene Section -->';

// 2. Leaflet scripts at bottom
$bottomSearch = '    <?php endif; ?>
</div>';

$bottomScripts = '    <?php endif; ?>
</div>

<!-- Leaflet CSS & JS for Chef Location Picker -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<script>
document.addEventListener(\'DOMContentLoaded\', function() {
    var latInput = document.getElementById(\'latitude\');
    var lngInput = document.getElementById(\'longitude\');
    var statusBadge = document.getElementById(\'chefLocationBadge\');
    var detectBtn = document.getElementById(\'btnChefDetectLoc\');

    var defaultLat = 21.1702; // Default fallback (Surat / Gujarat)
    var defaultLng = 72.8311;
    var defaultZoom = 13;

    var curLat = parseFloat(latInput.value);
    var curLng = parseFloat(lngInput.value);
    var hasExistingCoords = (!isNaN(curLat) && !isNaN(curLng) && curLat !== 0);

    var initCenter = hasExistingCoords ? [curLat, curLng] : [defaultLat, defaultLng];
    var initZoom = hasExistingCoords ? 15 : defaultZoom;

    // Initialize Leaflet Map
    var map = L.map(\'chefLocationMap\').setView(initCenter, initZoom);

    L.tileLayer(\'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png\', {
        maxZoom: 19,
        attribution: \'&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors\'
    }).addTo(map);

    // Custom kitchen marker icon
    var kitchenIcon = L.divIcon({
        className: \'custom-chef-marker\',
        html: \'<div style="background:#e65100;color:#fff;border-radius:50%;width:34px;height:34px;display:flex;align-items:center;justify-content:center;box-shadow:0 3px 8px rgba(0,0,0,0.3);border:2px solid #fff;font-size:18px;"><i class="bi bi-shop"></i></div>\',
        iconSize: [34, 34],
        iconAnchor: [17, 34],
        popupAnchor: [0, -34]
    });

    var marker = null;

    function updateMarker(lat, lng, label) {
        latInput.value = lat.toFixed(6);
        lngInput.value = lng.toFixed(6);
        if (statusBadge) {
            statusBadge.className = \'badge bg-success small\';
            statusBadge.innerHTML = \'<i class="bi bi-check-circle me-1"></i>\' + (label || \'Pinned: \' + lat.toFixed(4) + \', \' + lng.toFixed(4));
        }

        if (!marker) {
            marker = L.marker([lat, lng], { draggable: true, icon: kitchenIcon }).addTo(map);
            marker.on(\'dragend\', function(e) {
                var pos = e.target.getLatLng();
                updateMarker(pos.lat, pos.lng, \'Pinned: \' + pos.lat.toFixed(4) + \', \' + pos.lng.toFixed(4));
            });
        } else {
            marker.setLatLng([lat, lng]);
        }
        marker.bindPopup(\'<strong>📍 Kitchen Location</strong><br>\' + lat.toFixed(5) + \', \' + lng.toFixed(5)).openPopup();
    }

    if (hasExistingCoords) {
        updateMarker(curLat, curLng, \'Saved: \' + curLat.toFixed(4) + \', \' + curLng.toFixed(4));
    }

    // Map click sets marker
    map.on(\'click\', function(e) {
        updateMarker(e.latlng.lat, e.latlng.lng, \'Pinned: \' + e.latlng.lat.toFixed(4) + \', \' + e.latlng.lng.toFixed(4));
    });

    // Inputs change updates marker
    function onManualCoordChange() {
        var l1 = parseFloat(latInput.value);
        var l2 = parseFloat(lngInput.value);
        if (!isNaN(l1) && !isNaN(l2) && l1 >= -90 && l1 <= 90 && l2 >= -180 && l2 <= 180) {
            updateMarker(l1, l2, \'Manual: \' + l1.toFixed(4) + \', \' + l2.toFixed(4));
            map.panTo([l1, l2]);
        }
    }
    latInput.addEventListener(\'change\', onManualCoordChange);
    lngInput.addEventListener(\'change\', onManualCoordChange);

    // Geolocation detect button
    if (detectBtn) {
        detectBtn.addEventListener(\'click\', function() {
            if (!navigator.geolocation) {
                alert(\'Geolocation is not supported by your browser.\');
                return;
            }
            detectBtn.disabled = true;
            detectBtn.innerHTML = \'<span class="spinner-border spinner-border-sm me-1"></span> Detecting...\';
            navigator.geolocation.getCurrentPosition(
                function(pos) {
                    detectBtn.disabled = false;
                    detectBtn.innerHTML = \'<i class="bi bi-crosshair me-1"></i> Detect My Location\';
                    var uLat = pos.coords.latitude;
                    var uLng = pos.coords.longitude;
                    updateMarker(uLat, uLng, \'Current GPS Location\');
                    map.setView([uLat, uLng], 16);
                },
                function(err) {
                    detectBtn.disabled = false;
                    detectBtn.innerHTML = \'<i class="bi bi-crosshair me-1"></i> Detect My Location\';
                    alert(\'Could not get your location: \' + err.message + \'. Please click on the map manually.\');
                },
                { enableHighAccuracy: true, timeout: 10000 }
            );
        });
    }

    // Fix map rendering when displayed in Bootstrap cards
    setTimeout(function() {
        map.invalidateSize();
    }, 400);
});
</script>';

if ($isCrlf) {
    $searchTarget = str_replace("\n", "\r\n", $searchTarget);
    $mapCard = str_replace("\n", "\r\n", $mapCard);
    $bottomSearch = str_replace("\n", "\r\n", $bottomSearch);
    $bottomScripts = str_replace("\n", "\r\n", $bottomScripts);
}

if (strpos($content, $searchTarget) !== false && strpos($content, $bottomSearch) !== false) {
    $content = str_replace($searchTarget, $mapCard, $content);
    $content = str_replace($bottomSearch, $bottomScripts, $content);
    file_put_contents($filePath, $content);
    echo "SUCCESS: Chef profile view updated with Leaflet Map location picker.\n";
} else {
    echo "ERROR: Target not found in views/chef/profile.php\n";
    exit(1);
}
