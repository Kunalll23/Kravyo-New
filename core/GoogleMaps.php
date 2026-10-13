<?php
/**
 * Kravyo - Google Maps Platform Service (server-side)
 *
 * Wraps the Geocoding API and the Routes API with:
 *   - short cURL timeouts (the app must never hang on a maps call)
 *   - a file cache in storage/cache/maps/ to stay inside free monthly caps
 *   - graceful null returns on any failure (callers keep their fallbacks)
 *
 * Billing notes: DRIVE + TRAFFIC_UNAWARE keeps Routes calls on the
 * "Essentials" SKU (10,000 free events / month at the time of writing).
 */

class GoogleMaps {

    private const GEOCODE_URL = 'https://maps.googleapis.com/maps/api/geocode/json';
    private const ROUTES_URL  = 'https://routes.googleapis.com/directions/v2:computeRoutes';
    private const MATRIX_URL  = 'https://routes.googleapis.com/distanceMatrix/v2:computeRouteMatrix';

    private const GEOCODE_TTL = 2592000; // 30 days
    private const ROUTE_TTL   = 120;     // 2 minutes
    private const MATRIX_TTL  = 60;      // 1 minute

    /**
     * Is Google Maps configured with a usable key?
     */
    public static function isEnabled(): bool {
        return self::serverKey() !== '';
    }

    /**
     * Browser key for the Maps JavaScript API ('' if not configured).
     */
    public static function browserKey(): string {
        $key = defined('GOOGLE_MAPS_BROWSER_KEY') ? (string) GOOGLE_MAPS_BROWSER_KEY : '';
        return str_starts_with($key, 'YOUR_') ? '' : $key;
    }

    /**
     * Server key for Geocoding / Routes (falls back to the browser key).
     */
    public static function serverKey(): string {
        $key = defined('GOOGLE_MAPS_SERVER_KEY') ? (string) GOOGLE_MAPS_SERVER_KEY : '';
        if ($key === '' || str_starts_with($key, 'YOUR_')) {
            return self::browserKey();
        }
        return $key;
    }

    public static function mapId(): string {
        return defined('GOOGLE_MAPS_MAP_ID') && GOOGLE_MAPS_MAP_ID !== '' ? (string) GOOGLE_MAPS_MAP_ID : 'DEMO_MAP_ID';
    }

    // ── Geocoding ──────────────────────────────────────────────────────────

    /**
     * Convert a free-text address into coordinates (biased to India).
     *
     * @return array{lat: float, lng: float, formatted_address: string}|null
     */
    public static function geocode(string $address): ?array {
        $address = trim($address);
        if ($address === '' || !self::isEnabled()) {
            return null;
        }

        $cacheKey = 'geo_' . md5(strtolower($address));
        $cached = self::cacheGet($cacheKey);
        if ($cached !== null) {
            return $cached ?: null; // cached "not found" is stored as []
        }

        $url = self::GEOCODE_URL . '?' . http_build_query([
            'address'    => $address,
            'components' => 'country:IN',
            'region'     => 'in',
            'key'        => self::serverKey(),
        ]);

        $response = self::request('GET', $url);
        if ($response === null) {
            return null; // network failure: don't cache
        }

        $status = $response['status'] ?? '';
        if ($status === 'OK' && isset($response['results'][0]['geometry']['location'])) {
            $loc = $response['results'][0]['geometry']['location'];
            $result = [
                'lat'               => (float) $loc['lat'],
                'lng'               => (float) $loc['lng'],
                'formatted_address' => (string) ($response['results'][0]['formatted_address'] ?? $address),
            ];
            self::cacheSet($cacheKey, $result, self::GEOCODE_TTL);
            return $result;
        }

        if ($status === 'ZERO_RESULTS') {
            self::cacheSet($cacheKey, [], 86400); // remember misses for a day
        } else {
            self::log('Geocoding failed: ' . $status . ' ' . ($response['error_message'] ?? ''));
        }
        return null;
    }

    // ── Routes ─────────────────────────────────────────────────────────────

    /**
     * Road route between two points.
     *
     * @return array{polyline: string, distance_m: int, duration_s: int}|null
     */
    public static function computeRoute(float $oLat, float $oLng, float $dLat, float $dLng, ?float $wLat = null, ?float $wLng = null): ?array {
        if (!self::isEnabled()) {
            return null;
        }

        // Round origin to ~100 m so nearby GPS pings share a cache entry
        $cacheKey = sprintf('route_%.3f_%.3f_%.5f_%.5f_%.5f_%.5f', $oLat, $oLng, $dLat, $dLng, $wLat ?? 0, $wLng ?? 0);
        $cached = self::cacheGet($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        $body = [
            'origin'            => self::waypoint($oLat, $oLng),
            'destination'       => self::waypoint($dLat, $dLng),
            'travelMode'        => 'DRIVE',
            'routingPreference' => 'TRAFFIC_UNAWARE',
            'polylineQuality'   => 'OVERVIEW',
            'units'             => 'METRIC',
            'regionCode'        => 'IN',
        ];

        if ($wLat !== null && $wLng !== null) {
            $body['intermediates'] = [
                ['waypoint' => self::waypoint($wLat, $wLng)]
            ];
        }

        $response = self::request('POST', self::ROUTES_URL, $body, [
            'X-Goog-FieldMask: routes.duration,routes.distanceMeters,routes.polyline.encodedPolyline',
        ]);

        if (empty($response['routes'][0])) {
            if (isset($response['error'])) {
                self::log('Routes API failed: ' . ($response['error']['status'] ?? '') . ' ' . ($response['error']['message'] ?? ''));
            }
            return null;
        }

        $route = $response['routes'][0];
        $result = [
            'polyline'   => (string) ($route['polyline']['encodedPolyline'] ?? ''),
            'distance_m' => (int) ($route['distanceMeters'] ?? 0),
            'duration_s' => self::parseDuration($route['duration'] ?? '0s'),
        ];
        self::cacheSet($cacheKey, $result, self::ROUTE_TTL);
        return $result;
    }

    /**
     * Driving distance/duration from many origins to ONE destination in a single call.
     *
     * @param array<int, array{lat: float, lng: float}> $origins
     * @return array<int, array{distance_m: int, duration_s: int}>|null  Keyed by origin index
     */
    public static function computeRouteMatrix(array $origins, float $dLat, float $dLng): ?array {
        if (!self::isEnabled() || empty($origins)) {
            return null;
        }

        $cacheKey = 'matrix_' . md5(json_encode([$origins, round($dLat, 5), round($dLng, 5)]));
        $cached = self::cacheGet($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        $body = [
            'origins'           => array_map(fn($o) => ['waypoint' => self::waypoint((float) $o['lat'], (float) $o['lng'])], array_values($origins)),
            'destinations'      => [['waypoint' => self::waypoint($dLat, $dLng)]],
            'travelMode'        => 'DRIVE',
            'routingPreference' => 'TRAFFIC_UNAWARE',
        ];

        $response = self::request('POST', self::MATRIX_URL, $body, [
            'X-Goog-FieldMask: originIndex,destinationIndex,duration,distanceMeters,condition',
        ], 3);

        if (!is_array($response) || isset($response['error']) || !array_is_list($response)) {
            if (isset($response['error'])) {
                self::log('Route Matrix failed: ' . ($response['error']['status'] ?? '') . ' ' . ($response['error']['message'] ?? ''));
            }
            return null;
        }

        $result = [];
        foreach ($response as $el) {
            if (($el['condition'] ?? '') !== 'ROUTE_EXISTS') {
                continue;
            }
            $result[(int) ($el['originIndex'] ?? 0)] = [
                'distance_m' => (int) ($el['distanceMeters'] ?? 0),
                'duration_s' => self::parseDuration($el['duration'] ?? '0s'),
            ];
        }

        self::cacheSet($cacheKey, $result, self::MATRIX_TTL);
        return $result;
    }

    // ── Internals ──────────────────────────────────────────────────────────

    private static function waypoint(float $lat, float $lng): array {
        return ['location' => ['latLng' => ['latitude' => $lat, 'longitude' => $lng]]];
    }

    /** Routes API durations look like "754s". */
    private static function parseDuration(string $duration): int {
        return (int) round((float) rtrim($duration, 's'));
    }

    /**
     * Perform an HTTP request and decode JSON. Returns null on transport failure.
     */
    private static function request(string $method, string $url, ?array $body = null, array $headers = [], int $timeout = 4): ?array {
        $httpHeaders = array_merge(['Accept: application/json'], $headers);

        if ($method === 'POST') {
            $httpHeaders[] = 'Content-Type: application/json';
            $httpHeaders[] = 'X-Goog-Api-Key: ' . self::serverKey();
        }

        $raw = false;
        $err = '';

        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            if ($method === 'POST') {
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
            }

            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CONNECTTIMEOUT => 2,
                CURLOPT_TIMEOUT        => $timeout,
                CURLOPT_HTTPHEADER     => $httpHeaders,
                CURLOPT_USERAGENT      => 'Kravyo/1.0',
            ]);

            $raw = curl_exec($ch);
            $err = curl_error($ch);
            curl_close($ch);
        } else {
            // Fallback to stream context when cURL extension is not installed/enabled
            $contextOptions = [
                'http' => [
                    'method'        => $method,
                    'header'        => implode("\r\n", $httpHeaders),
                    'timeout'       => $timeout,
                    'ignore_errors' => true,
                ],
                'ssl' => [
                    'verify_peer'      => false,
                    'verify_peer_name' => false,
                ]
            ];
            if ($method === 'POST' && $body !== null) {
                $contextOptions['http']['content'] = json_encode($body);
            }
            $context = stream_context_create($contextOptions);
            $raw = @file_get_contents($url, false, $context);
            if ($raw === false) {
                $err = 'file_get_contents failed';
            }
        }

        if ($raw === false || $raw === '') {
            self::log('HTTP request failed: ' . $err);
            return null;
        }

        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : null;
    }

    private static function cacheDir(): string {
        $dir = (defined('STORAGE_PATH') ? STORAGE_PATH : dirname(__DIR__) . '/storage') . '/cache/maps';
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        return $dir;
    }

    private static function cacheGet(string $key): ?array {
        $file = self::cacheDir() . '/' . preg_replace('/[^a-z0-9_.\-]/i', '_', $key) . '.json';
        if (!is_file($file)) {
            return null;
        }
        $data = json_decode((string) @file_get_contents($file), true);
        if (!is_array($data) || ($data['expires'] ?? 0) < time()) {
            @unlink($file);
            return null;
        }
        return $data['value'] ?? null;
    }

    private static function cacheSet(string $key, array $value, int $ttl): void {
        $file = self::cacheDir() . '/' . preg_replace('/[^a-z0-9_.\-]/i', '_', $key) . '.json';
        @file_put_contents($file, json_encode(['expires' => time() + $ttl, 'value' => $value]), LOCK_EX);
    }

    private static function log(string $message): void {
        $dir = defined('STORAGE_PATH') ? STORAGE_PATH . '/logs' : dirname(__DIR__) . '/storage/logs';
        @file_put_contents($dir . '/google_maps.log', '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL, FILE_APPEND);
    }
}
