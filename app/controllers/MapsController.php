<?php
/**
 * Kravyo - Maps API Proxy Controller
 *
 * Exposes Google Routes / Geocoding to the browser WITHOUT leaking the server key.
 * Mounted under several URL prefixes so each portal keeps its own session cookie:
 *   /api/maps/*           -> Customer session
 *   /delivery/api/maps/*  -> Delivery session
 *   /chef/api/maps/*      -> Chef session
 *
 * Every endpoint requires a logged-in user and is rate-limited per session,
 * and GoogleMaps caches results on disk — both keep usage inside the free tier.
 */

class MapsController extends Controller {

    private const ROUTE_MIN_INTERVAL   = 0; // seconds between route calls per session (lowered for testing)
    private const GEOCODE_MIN_INTERVAL = 1; // seconds between geocode calls per session

    /**
     * GET ?olat=&olng=&dlat=&dlng=
     * Returns { success, polyline, distance_m, duration_s }
     */
    public function route(): void {
        $this->requireUser();

        $oLat = $this->coord('olat', 90);
        $oLng = $this->coord('olng', 180);
        $dLat = $this->coord('dlat', 90);
        $dLng = $this->coord('dlng', 180);
        $wLat = $this->coord('wlat', 90);
        $wLng = $this->coord('wlng', 180);

        if ($oLat === null || $oLng === null || $dLat === null || $dLng === null) {
            $this->json(['success' => false, 'message' => 'Invalid coordinates.'], 422);
        }

        $this->throttle('maps_route_last', self::ROUTE_MIN_INTERVAL);

        $route = GoogleMaps::computeRoute($oLat, $oLng, $dLat, $dLng, $wLat, $wLng);
        if ($route === null) {
            $this->json(['success' => false, 'message' => 'Route unavailable right now.'], 503);
        }

        $this->json(['success' => true] + $route);
    }

    /**
     * GET ?address=
     * Returns { success, lat, lng, formatted_address }
     */
    public function geocode(): void {
        $this->requireUser();

        $address = trim((string) ($_GET['address'] ?? ''));
        $len = function_exists('mb_strlen') ? mb_strlen($address) : strlen($address);
        if ($len < 3 || $len > 300) {
            $this->json(['success' => false, 'message' => 'Please enter a valid address.'], 422);
        }

        $this->throttle('maps_geocode_last', self::GEOCODE_MIN_INTERVAL);

        $result = GoogleMaps::geocode($address);
        if ($result === null) {
            $this->json(['success' => false, 'message' => 'We could not find that address. Please drag the pin manually.'], 404);
        }

        $this->json(['success' => true] + $result);
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    private function requireUser(): void {
        if (!Session::has('user_id') && !Session::has('admin_id') && !Session::has('delivery_partner_id') && !Session::has('chef_id')) {
            $this->json(['success' => false, 'message' => 'Authentication required.'], 401);
        }
    }

    private function coord(string $key, int $limit): ?float {
        $val = $_GET[$key] ?? null;
        if ($val === null || !is_numeric($val)) {
            return null;
        }
        $f = (float) $val;
        return ($f >= -$limit && $f <= $limit) ? $f : null;
    }

    private function throttle(string $sessionKey, int $seconds): void {
        $last = (int) Session::get($sessionKey, 0);
        $now  = time();
        if ($now - $last < $seconds) {
            header('Retry-After: ' . ($seconds - ($now - $last)));
            $this->json(['success' => false, 'message' => 'Too many requests.', 'retry_after' => $seconds - ($now - $last)], 429);
        }
        Session::set($sessionKey, $now);
    }
}
