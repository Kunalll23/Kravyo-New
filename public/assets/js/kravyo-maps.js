/**
 * Kravyo Maps — shared Google Maps helper
 *
 * Wraps the Google Maps JavaScript API (advanced markers + geometry) with the
 * building blocks every Kravyo map needs:
 *   - KravyoMaps.ready()          load libraries once
 *   - KravyoMaps.createMap()      map with Kravyo defaults + graceful error card
 *   - KravyoMaps.htmlMarker()     AdvancedMarkerElement from an HTML string
 *   - KravyoMaps.icons.*          bike / kitchen / customer / pulse-dot markers
 *   - KravyoMaps.animateMarker()  smooth glide + heading rotation for riders
 *   - KravyoMaps.RouteLine        Swiggy-style route (halo + line, dashed fallback)
 *   - KravyoMaps.fetchRoute()     server-proxied Routes API call (key stays server-side)
 *   - KravyoMaps.geocode()        server-proxied Geocoding call
 *
 * Config comes from window.KRAVYO_MAPS_CONFIG (views/partials/google_maps_loader.php).
 */
(function (window, document) {
    'use strict';

    var CONFIG = window.KRAVYO_MAPS_CONFIG || { enabled: false, mapId: 'DEMO_MAP_ID', defaultCenter: { lat: 21.1702, lng: 72.8311 } };
    var libsPromise = null;
    var mapContainers = [];
    var authFailed = false;

    // ── Styles for custom markers (injected once) ─────────────────────────
    var css = '' +
        '.kv-map-error{display:flex;flex-direction:column;align-items:center;justify-content:center;height:100%;min-height:180px;' +
            'background:linear-gradient(135deg,#f8fafc,#eef2f7);border-radius:inherit;color:#475569;text-align:center;padding:1.5rem;font-size:.9rem}' +
        '.kv-map-error i{font-size:2rem;color:#f59e0b;margin-bottom:.5rem}' +
        '.kv-pulse{position:relative;width:24px;height:24px}' +
        '.kv-pulse .kv-dot{position:absolute;inset:4px;background:#2563eb;border:3px solid #fff;border-radius:50%;box-shadow:0 2px 8px rgba(37,99,235,.6)}' +
        '.kv-pulse .kv-ring{position:absolute;inset:-4px;border:3px solid rgba(37,99,235,.5);border-radius:50%;animation:kvPulse 1.8s ease-out infinite}' +
        '@keyframes kvPulse{0%{transform:scale(.5);opacity:1}100%{transform:scale(1.6);opacity:0}}' +
        '.kv-pin{position:relative;width:38px;height:48px;filter:drop-shadow(0 4px 6px rgba(0,0,0,.3));transform-origin:bottom center;transition:transform .2s}' +
        '.kv-pin:hover,.kv-pin.kv-active{transform:scale(1.18)}' +
        '.kv-pin .kv-pin-head{position:absolute;top:0;left:0;width:38px;height:38px;border-radius:50% 50% 50% 0;transform:rotate(-45deg);border:2px solid #fff}' +
        '.kv-pin .kv-pin-icon{position:absolute;top:0;left:0;width:38px;height:38px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:17px}' +
        '.kv-bike{width:48px;height:48px;transition:transform .6s linear;will-change:transform}' +
        '.kv-eta-chip{display:inline-flex;align-items:center;gap:.4rem;background:#0f172a;color:#fff;border-radius:999px;padding:.35rem .85rem;font-weight:700;font-size:.85rem;box-shadow:0 4px 14px rgba(15,23,42,.25)}' +
        '.kv-eta-chip .kv-eta-sub{color:#94a3b8;font-weight:500}';
    var styleEl = document.createElement('style');
    styleEl.textContent = css;
    document.head.appendChild(styleEl);

    // ── Auth failure (invalid key / API not enabled / referrer blocked) ────
    window.gm_authFailure = function () {
        authFailed = true;
        mapContainers.forEach(function (el) {
            showError(el, 'Google Maps could not be loaded (API key problem). Please contact support.');
        });
        console.error('[KravyoMaps] Google Maps authentication failed. Check the API key, enabled APIs and key restrictions.');
    };

    function showError(el, msg) {
        if (!el) return;
        el.innerHTML = '<div class="kv-map-error"><i class="bi bi-map"></i><div>' + escapeHtml(msg) + '</div></div>';
    }

    function cleanText(str) {
        if (!str) return '';
        var s = String(str);
        var prev = '';
        var iterations = 0;
        while (s !== prev && iterations < 3) {
            prev = s;
            var txt = document.createElement('textarea');
            txt.innerHTML = s;
            s = txt.value;
            iterations++;
        }
        return s.replace(/&#0*39;/g, "'")
                .replace(/&apos;/g, "'")
                .replace(/&#0*34;/g, '"')
                .replace(/&quot;/g, '"')
                .replace(/&amp;/g, '&');
    }

    function escapeHtml(text) {
        var cleaned = cleanText(text);
        return String(cleaned == null ? '' : cleaned).replace(/[<>"']/g, function (m) {
            return { '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": "'" }[m];
        });
    }

    // ── Library loading ───────────────────────────────────────────────────
    function ready() {
        if (libsPromise) return libsPromise;
        if (!CONFIG.enabled || !window.google || !google.maps || !google.maps.importLibrary) {
            libsPromise = Promise.reject(new Error('Google Maps is not configured.'));
            libsPromise.catch(function () {});
            return libsPromise;
        }
        libsPromise = Promise.all([
            google.maps.importLibrary('maps'),
            google.maps.importLibrary('marker'),
            google.maps.importLibrary('geometry'),
            google.maps.importLibrary('core'),
            google.maps.importLibrary('places'),
            google.maps.importLibrary('geocoding')
        ]).then(function (libs) {
            return {
                Map: libs[0].Map,
                InfoWindow: libs[0].InfoWindow,
                Circle: libs[0].Circle,
                Polyline: libs[0].Polyline,
                AdvancedMarkerElement: libs[1].AdvancedMarkerElement,
                encoding: libs[2].encoding,
                spherical: libs[2].spherical,
                LatLng: libs[3].LatLng,
                LatLngBounds: libs[3].LatLngBounds,
                PlaceAutocompleteElement: libs[4].PlaceAutocompleteElement,
                Place: libs[4].Place,
                Geocoder: libs[5].Geocoder
            };
        });
        return libsPromise;
    }

    /**
     * Create a map inside `el`. Resolves to { map, libs } or rejects (error card shown).
     */
    function createMap(el, options) {
        if (typeof el === 'string') el = document.getElementById(el);
        if (!el) return Promise.reject(new Error('Map container not found'));
        mapContainers.push(el);

        return ready().then(function (libs) {
            if (authFailed) throw new Error('Google Maps auth failed');
            
            // Zomato style silver/light map theme
            var silverMapStyle = [
                { elementType: 'geometry', stylers: [{ color: '#f5f5f5' }] },
                { elementType: 'labels.icon', stylers: [{ visibility: 'off' }] },
                { elementType: 'labels.text.fill', stylers: [{ color: '#616161' }] },
                { elementType: 'labels.text.stroke', stylers: [{ color: '#f5f5f5' }] },
                { featureType: 'administrative.land_parcel', elementType: 'labels.text.fill', stylers: [{ color: '#bdbdbd' }] },
                { featureType: 'poi', elementType: 'geometry', stylers: [{ color: '#eeeeee' }] },
                { featureType: 'poi', elementType: 'labels.text.fill', stylers: [{ color: '#757575' }] },
                { featureType: 'poi.park', elementType: 'geometry', stylers: [{ color: '#e5e5e5' }] },
                { featureType: 'poi.park', elementType: 'labels.text.fill', stylers: [{ color: '#9e9e9e' }] },
                { featureType: 'road', elementType: 'geometry', stylers: [{ color: '#ffffff' }] },
                { featureType: 'road.arterial', elementType: 'labels.text.fill', stylers: [{ color: '#757575' }] },
                { featureType: 'road.highway', elementType: 'geometry', stylers: [{ color: '#dadada' }] },
                { featureType: 'road.highway', elementType: 'labels.text.fill', stylers: [{ color: '#616161' }] },
                { featureType: 'road.local', elementType: 'labels.text.fill', stylers: [{ color: '#9e9e9e' }] },
                { featureType: 'transit.line', elementType: 'geometry', stylers: [{ color: '#e5e5e5' }] },
                { featureType: 'transit.station', elementType: 'geometry', stylers: [{ color: '#eeeeee' }] },
                { featureType: 'water', elementType: 'geometry', stylers: [{ color: '#c9c9c9' }] },
                { featureType: 'water', elementType: 'labels.text.fill', stylers: [{ color: '#9e9e9e' }] }
            ];

            var opts = Object.assign({
                center: CONFIG.defaultCenter,
                zoom: 13,
                mapId: CONFIG.mapId,
                mapTypeControl: false,
                streetViewControl: false,
                fullscreenControl: true,
                clickableIcons: false,
                gestureHandling: 'cooperative',
                styles: silverMapStyle // Inject the theme here
            }, options || {});
            var map = new libs.Map(el, opts);
            return { map: map, libs: libs };
        }).catch(function (err) {
            showError(el, CONFIG.enabled
                ? 'Map failed to load. Please check your internet connection and refresh.'
                : 'Maps are not configured yet.');
            console.error('[KravyoMaps]', err);
            throw err;
        });
    }

    // ── Markers ───────────────────────────────────────────────────────────
    function htmlToElement(html) {
        var wrap = document.createElement('div');
        wrap.innerHTML = html.trim();
        return wrap.firstElementChild || wrap;
    }

    /**
     * Create an AdvancedMarkerElement from HTML. Tracks its position on `_kvPos`.
     */
    function htmlMarker(libs, map, position, html, extra) {
        var content = htmlToElement(html);
        var marker = new libs.AdvancedMarkerElement(Object.assign({
            map: map,
            position: position,
            content: content
        }, extra || {}));
        marker._kvPos = { lat: Number(position.lat), lng: Number(position.lng) };
        return marker;
    }

    function setMarkerPosition(marker, pos) {
        marker.position = pos;
        marker._kvPos = { lat: Number(pos.lat), lng: Number(pos.lng) };
    }

    // Zomato-style Red Scooter SVG (Facing UP / North)
    var BIKE_SVG = '<svg width="48" height="48" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg" style="filter: drop-shadow(0px 4px 6px rgba(0,0,0,0.3));">' +
        // Front dash/light
        '<path d="M 20 14 L 28 14 L 26 6 L 22 6 Z" fill="#dc2626"/>' +
        '<rect x="22" y="2" width="4" height="4" rx="1" fill="#fbbf24"/>' +
        // Handlebars
        '<rect x="12" y="13" width="24" height="3" rx="1.5" fill="#1f2937"/>' +
        '<circle cx="12" cy="11.5" r="3" fill="#111827"/>' +
        '<circle cx="36" cy="11.5" r="3" fill="#111827"/>' +
        // Shoulders/Jacket (red)
        '<path d="M 16 22 C 16 30, 32 30, 32 22 L 35 14 L 13 14 Z" fill="#b91c1c"/>' +
        // Rider body (red helmet)
        '<circle cx="24" cy="28" r="7" fill="#dc2626"/>' +
        // Delivery box (square red box on back)
        '<rect x="15" y="31" width="18" height="12" rx="2" fill="#ef4444" stroke="#991b1b" stroke-width="1.5"/>' +
        // Back tire (dark red/black)
        '<rect x="22" y="35" width="4" height="10" rx="2" fill="#3a0d16"/>' +
        '</svg>';

    var icons = {
        /** Top-down delivery rider; rotated by animateMarker() to face travel direction. */
        bike: function () {
            return '<div style="transform:translateY(50%)"><div class="kv-bike">' + BIKE_SVG + '</div></div>';
        },
        /** Teardrop pin with a Bootstrap icon. */
        pin: function (color, icon, title) {
            var cTitle = cleanText(title || '');
            return '<div class="kv-pin" title="' + cTitle.replace(/"/g, '&quot;') + '">' +
                '<div class="kv-pin-head" style="background:' + (color || '#ff6b35') + '"></div>' +
                '<div class="kv-pin-icon"><i class="bi ' + (icon || 'bi-shop') + '"></i></div></div>';
        },
        kitchen: function (title, closed) {
            return icons.pin(closed ? '#64748b' : '#ff6b35', 'bi-shop', title);
        },
        customer: function (title) {
            // Sleek black home icon like Zomato
            return icons.pin('#111827', 'bi-house-door-fill', title || 'Delivery location');
        },
        /** Pulsing blue "you are here" dot. */
        pulse: function () {
            return '<div style="transform:translateY(50%)"><div class="kv-pulse"><div class="kv-ring"></div><div class="kv-dot"></div></div></div>';
        }
    };

    /**
     * Glide a marker to `to` over `duration` ms; rotates `.kv-bike` to face the direction.
     */
    function animateMarker(libs, marker, to, duration) {
        duration = duration || 1000;
        var from = marker._kvPos || to;
        var target = { lat: Number(to.lat), lng: Number(to.lng) };
        if (marker._kvAnim) cancelAnimationFrame(marker._kvAnim);

        var moved = distanceMeters(from, target);
        if (moved < 0.5) { setMarkerPosition(marker, target); return; }

        // Face direction of travel (only for meaningful moves to avoid GPS jitter spins)
        if (moved > 5 && libs.spherical && marker.content) {
            var bike = marker.content.querySelector ? marker.content.querySelector('.kv-bike') : null;
            if (bike) {
                var heading = libs.spherical.computeHeading(new libs.LatLng(from), new libs.LatLng(target));
                bike.style.transform = 'rotate(' + heading + 'deg)';
            }
        }

        // Large jumps (e.g. first fix after reconnect) snap instead of sliding across the city
        if (moved > 2000) { setMarkerPosition(marker, target); return; }

        var start = null;
        function step(ts) {
            if (!start) start = ts;
            var t = Math.min(1, (ts - start) / duration);
            var e = t < 0.5 ? 2 * t * t : -1 + (4 - 2 * t) * t; // easeInOutQuad
            setMarkerPosition(marker, {
                lat: from.lat + (target.lat - from.lat) * e,
                lng: from.lng + (target.lng - from.lng) * e
            });
            if (t < 1) marker._kvAnim = requestAnimationFrame(step);
        }
        marker._kvAnim = requestAnimationFrame(step);
    }

    // ── Route line ────────────────────────────────────────────────────────
    /**
     * Swiggy/Zomato-style route: dark border + solid vibrant inner line. Falls back to a dashed straight line.
     */
    function RouteLine(libs, map, color, haloColor) {
        this.libs = libs;
        this.map = map;
        this.color = color || '#3b82f6';
        var border = haloColor || (this.color === '#ef4444' || this.color === '#f59e0b' ? '#78350f' : (this.color === '#10b981' ? '#064e3b' : '#1e3a8a'));
        this.halo = new libs.Polyline({ map: map, strokeColor: border, strokeOpacity: 0.9, strokeWeight: 8, zIndex: 1 });
        this.line = new libs.Polyline({ map: map, strokeColor: this.color, strokeOpacity: 1.0, strokeWeight: 5, zIndex: 2 });
        this.path = [];
    }
    RouteLine.prototype.setEncoded = function (encoded) {
        var path = this.libs.encoding.decodePath(encoded);
        this.path = path;
        this.line.setOptions({ icons: [], strokeOpacity: 0.95 });
        this.halo.setPath(path);
        this.line.setPath(path);
        this.halo.setVisible(true);
    };
    RouteLine.prototype.setStraight = function (from, to) {
        this.path = [from, to];
        this.halo.setVisible(false);
        this.line.setPath(this.path);
        this.line.setOptions({
            strokeOpacity: 0,
            icons: [{ icon: { path: 'M 0,-1 0,1', strokeOpacity: 0.9, strokeColor: this.color, scale: 3 }, offset: '0', repeat: '14px' }]
        });
    };
    RouteLine.prototype.setColor = function (color) {
        this.color = color;
        this.halo.setOptions({ strokeColor: color });
        this.line.setOptions({ strokeColor: color });
    };
    RouteLine.prototype.clear = function () {
        this.path = [];
        this.halo.setPath([]);
        this.line.setPath([]);
    };

    // ── Server-proxied APIs ───────────────────────────────────────────────
    function fetchRoute(origin, dest, waypoint) {
        var paramsObj = { olat: origin.lat, olng: origin.lng, dlat: dest.lat, dlng: dest.lng };
        if (waypoint) {
            paramsObj.wlat = waypoint.lat;
            paramsObj.wlng = waypoint.lng;
        }
        var params = new URLSearchParams(paramsObj);
        return fetch(CONFIG.routeUrl + '?' + params.toString(), { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
            .then(function (res) { return res.json().catch(function () { return { success: false }; }); });
    }

    function geocode(address) {
        return fetch(CONFIG.geocodeUrl + '?address=' + encodeURIComponent(address), { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
            .then(function (res) { return res.json().catch(function () { return { success: false }; }); });
    }

    /**
     * Throttled route tracker: only re-requests when the origin moved > minMeters
     * or maxAgeMs elapsed (or the destination changed). Keeps Routes API usage tiny.
     */
    function RouteTracker(opts) {
        this.minMeters = opts.minMeters || 150;
        this.maxAgeMs = opts.maxAgeMs || 60000;
        this.onRoute = opts.onRoute;
        this.onFallback = opts.onFallback;
        this.last = null; // { origin, dest, time }
        this.inFlight = false;
    }
    RouteTracker.prototype.update = function (origin, dest) {
        if (!origin || !dest) return;
        var self = this;
        var now = Date.now();
        var destChanged = !this.last || distanceMeters(this.last.dest, dest) > 5;
        var due = destChanged || !this.last ||
            distanceMeters(this.last.origin, origin) > this.minMeters ||
            (now - this.last.time) > this.maxAgeMs;
        if (!due || this.inFlight) return;

        this.inFlight = true;
        this.last = { origin: origin, dest: dest, time: now };
        fetchRoute(origin, dest).then(function (data) {
            self.inFlight = false;
            if (data && data.success && data.polyline) {
                self.onRoute && self.onRoute(data);
            } else {
                if (data && data.retry_after) self.last.time = now - self.maxAgeMs + (data.retry_after * 1000);
                self.onFallback && self.onFallback(estimate(origin, dest));
            }
        }).catch(function () {
            self.inFlight = false;
            self.onFallback && self.onFallback(estimate(origin, dest));
        });
    };

    // ── Geo utilities ─────────────────────────────────────────────────────
    function distanceMeters(a, b) {
        if (!a || !b) return Infinity;
        var R = 6371000, toRad = Math.PI / 180;
        var dLat = (b.lat - a.lat) * toRad, dLng = (b.lng - a.lng) * toRad;
        var s = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
            Math.cos(a.lat * toRad) * Math.cos(b.lat * toRad) * Math.sin(dLng / 2) * Math.sin(dLng / 2);
        return 2 * R * Math.atan2(Math.sqrt(s), Math.sqrt(1 - s));
    }

    /** Straight-line fallback estimate: ×1.3 road factor at ~22 km/h city speed. */
    function estimate(origin, dest) {
        var d = distanceMeters(origin, dest) * 1.3;
        return { distance_m: Math.round(d), duration_s: Math.round(d / (22000 / 3600)), estimated: true };
    }

    function formatDuration(seconds) {
        var m = Math.max(1, Math.round(seconds / 60));
        if (m < 60) return m + ' min';
        var h = Math.floor(m / 60);
        return h + ' hr ' + (m % 60) + ' min';
    }

    function formatDistance(meters) {
        return meters < 1000 ? Math.round(meters) + ' m' : (meters / 1000).toFixed(1) + ' km';
    }

    function fitTo(libs, map, points, padding, maxZoom) {
        var valid = points.filter(function (p) { return p && isFinite(p.lat) && isFinite(p.lng); });
        if (valid.length === 0) return;
        if (valid.length === 1) { map.setCenter(valid[0]); map.setZoom(maxZoom || 15); return; }
        var bounds = new libs.LatLngBounds();
        valid.forEach(function (p) { bounds.extend(p); });
        map.fitBounds(bounds, padding == null ? 60 : padding);
        if (maxZoom) {
            var once = map.addListener('idle', function () {
                if (map.getZoom() > maxZoom) map.setZoom(maxZoom);
                once.remove();
            });
        }
    }

    /** Google Maps app deep link for turn-by-turn navigation (free, no API usage). */
    function navigationUrl(dest, origin) {
        var url = 'https://www.google.com/maps/dir/?api=1&travelmode=driving&destination=' + dest.lat + ',' + dest.lng;
        if (origin) url += '&origin=' + origin.lat + ',' + origin.lng;
        return url;
    }

    var globalLibs = null;
    var driverMarkers = {};

    window.KravyoMaps = {
        config: CONFIG,
        ready: ready,
        createMapAsync: createMap,
        htmlMarker: htmlMarker,
        setMarkerPosition: setMarkerPosition,
        icons: icons,
        animateMarker: animateMarker,
        RouteLine: RouteLine,
        RouteTracker: RouteTracker,
        fetchRoute: fetchRoute,
        distanceMeters: distanceMeters,
        estimate: estimate,
        formatDuration: formatDuration,
        formatDistance: formatDistance,
        fitTo: fitTo,
        navigationUrl: navigationUrl,
        showError: showError,
        escapeHtml: escapeHtml,
        
        // --- Wrappers for views ---
        init: function() {
            return ready().then(function(libs) {
                globalLibs = libs;
                return libs;
            });
        },
        createMap: function(elId, lat, lng, zoom) {
            var el = document.getElementById(elId);
            if (!el || !globalLibs) return null;
            if (mapContainers.indexOf(el) === -1) mapContainers.push(el);
            return new globalLibs.Map(el, {
                center: { lat: Number(lat), lng: Number(lng) },
                zoom: zoom || 13,
                mapId: CONFIG.mapId,
                mapTypeControl: false,
                streetViewControl: false,
                fullscreenControl: true,
                clickableIcons: false
            });
        },
        addStaticMarker: function(map, lat, lng, title, type) {
            if (!globalLibs) return null;
            var cTitle = cleanText(title || '');
            var iconHtml = type === 'restaurant' ? icons.kitchen(cTitle) : (type === 'home' ? icons.customer(cTitle) : icons.pin('#000', 'bi-geo-alt-fill', cTitle));
            return htmlMarker(globalLibs, map, { lat: Number(lat), lng: Number(lng) }, iconHtml, { title: cTitle });
        },
        addDraggableMarker: function(map, lat, lng, onChange) {
            if (!globalLibs) return null;
            var m = htmlMarker(globalLibs, map, { lat: Number(lat), lng: Number(lng) }, icons.pin('#ff6b35', 'bi-geo-alt-fill'), { gmpDraggable: true });
            m.addListener('dragend', function() {
                var pos = m.position;
                onChange(pos.lat, pos.lng);
            });
            return m;
        },
        updateDriverLocation: function(map, id, lat, lng) {
            if (!globalLibs) return;
            var pos = { lat: Number(lat), lng: Number(lng) };
            if (!driverMarkers[id]) {
                driverMarkers[id] = htmlMarker(globalLibs, map, pos, icons.bike());
            } else {
                animateMarker(globalLibs, driverMarkers[id], pos);
            }
        },
        drawRoute: function(map, origin, dest, waypoint, color, haloColor) {
            return fetchRoute(origin, dest, waypoint).then(function(data) {
                if (!globalLibs) return { setMap: function(){}, data: data };
                var renderer = new RouteLine(globalLibs, map, color || '#2563eb', haloColor);
                if (data && data.polyline) {
                    renderer.setEncoded(data.polyline);
                } else {
                    renderer.setStraight(origin, dest);
                }
                return {
                    renderer: renderer,
                    data: data,
                    setMap: function(m) {
                        if (!m) renderer.clear();
                    }
                };
            });
        },
        geocode: function(address) {
            return geocode(address).then(function(res) {
                if (res && res.success) {
                    var lat = res.location ? res.location.lat : res.lat;
                    var lng = res.location ? res.location.lng : res.lng;
                    if (lat != null && lng != null) {
                        return { lat: Number(lat), lng: Number(lng), formatted_address: res.formatted_address };
                    }
                }
                throw new Error("Server geocode failed");
            }).catch(function(err) {
                if (globalLibs && globalLibs.Geocoder) {
                    var geocoder = new globalLibs.Geocoder();
                    return geocoder.geocode({ address: address }).then(function(res) {
                        if (res && res.results && res.results[0] && res.results[0].geometry && res.results[0].geometry.location) {
                            var loc = res.results[0].geometry.location;
                            return {
                                lat: typeof loc.lat === 'function' ? loc.lat() : loc.lat,
                                lng: typeof loc.lng === 'function' ? loc.lng() : loc.lng
                            };
                        }
                        throw new Error("Client geocode found no results");
                    });
                }
                throw err;
            });
        },
        fitToBounds: function(map, points, padding, maxZoom) {
            if (!globalLibs) return;
            fitTo(globalLibs, map, points, padding, maxZoom);
        },
        initAutocomplete: function(inputEl, map, marker, onPlaceSelected) {
            if (!globalLibs || !globalLibs.PlaceAutocompleteElement) return null;
            if (typeof inputEl === 'string') inputEl = document.getElementById(inputEl);
            if (!inputEl) return null;
            
            // Create the new Places API Web Component
            var autocomplete = new globalLibs.PlaceAutocompleteElement({
                componentRestrictions: { country: "in" }
            });
            
            // Insert it before the original input and hide original
            inputEl.parentNode.insertBefore(autocomplete, inputEl);
            inputEl.style.display = 'none';
            
            // Fix background and text color by injecting a style tag
            if (!document.getElementById('gmp-autocomplete-style')) {
                var style = document.createElement('style');
                style.id = 'gmp-autocomplete-style';
                style.innerHTML = 'gmp-place-autocomplete { --background-color: #ffffff; --color: #212529; --border: 1px solid #ced4da; --border-radius: 0.375rem; color-scheme: light; }';
                document.head.appendChild(style);
            }
            autocomplete.style.width = "100%";
            
            autocomplete.addEventListener("gmp-select", async function(e) {
                var placePrediction = e.placePrediction;
                if (!placePrediction) return;
                
                var place = placePrediction.toPlace();
                
                await place.fetchFields({
                    fields: ["addressComponents", "location", "displayName", "formattedAddress"]
                });
                
                if (!place.location) return;
                
                // Update original input value for form submission
                inputEl.value = place.formattedAddress || place.displayName;
                
                var pos = {
                    lat: place.location.lat(),
                    lng: place.location.lng()
                };
                
                if (map) {
                    map.setCenter(pos);
                    map.setZoom(17);
                }
                
                if (marker) {
                    marker.position = pos;
                }
                
                if (onPlaceSelected) {
                    // Map the new Place object format to our expected callback format
                    var mappedComponents = [];
                    if (place.addressComponents) {
                        place.addressComponents.forEach(function(comp) {
                            mappedComponents.push({
                                long_name: comp.longText,
                                short_name: comp.shortText,
                                types: comp.types
                            });
                        });
                    }
                    
                    var mappedPlace = {
                        geometry: { location: place.location },
                        address_components: mappedComponents,
                        formatted_address: place.formattedAddress,
                        name: place.displayName
                    };
                    onPlaceSelected(pos, mappedPlace);
                }
            });
            
            return autocomplete;
        },
        getLibs: function() {
            return globalLibs;
        }
    };
})(window, document);
