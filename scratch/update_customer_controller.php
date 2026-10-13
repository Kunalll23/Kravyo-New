<?php
$filePath = __DIR__ . '/../app/controllers/CustomerController.php';
$content = file_get_contents($filePath);

$search = "        \$this->render('customer/kitchens', [
            'title'    => 'Explore Home Kitchens',
            'kitchens' => \$kitchens,
            'cities'   => \$cities,
            'filters'  => \$filters,
        ]);
    }";

// Normalize line endings for replacement
$isCrlf = strpos($content, "\r\n") !== false;
$eol = $isCrlf ? "\r\n" : "\n";

$newMethods = '

    /**
     * Display Nearby Cloud Kitchens interactive map page
     * GET /nearby-kitchens
     * GET /kitchens/nearby
     */
    public function nearbyKitchens(): void {
        require_once APP_PATH . \'/models/Kitchen.php\';
        require_once APP_PATH . \'/models/Review.php\';

        $kitchenModel = new Kitchen();
        $cities = $kitchenModel->getDistinctCities();

        $city = trim($_GET[\'city\'] ?? \'\');
        $lat  = isset($_GET[\'lat\']) && is_numeric($_GET[\'lat\']) ? (float)$_GET[\'lat\'] : null;
        $lng  = isset($_GET[\'lng\']) && is_numeric($_GET[\'lng\']) ? (float)$_GET[\'lng\'] : null;
        $rad  = isset($_GET[\'radius\']) && is_numeric($_GET[\'radius\']) ? max(1.0, min(200.0, (float)$_GET[\'radius\'])) : 10.0;

        $filters = [];
        if (!empty($city)) {
            $filters[\'city\'] = $city;
        }

        // If coordinates provided, find nearby; else load all approved with coordinates
        if ($lat !== null && $lng !== null && $lat >= -90.0 && $lat <= 90.0 && $lng >= -180.0 && $lng <= 180.0) {
            $initialKitchens = $kitchenModel->findNearby($lat, $lng, $rad, $filters, 50);
        } else {
            $initialKitchens = $kitchenModel->findAllWithCoordinates($filters, 50);
        }

        $this->render(\'customer/nearby_kitchens\', [
            ' . "\n" . '            \'title\'           => \'Nearby Cloud Kitchens — Kravyo\',
            \'cities\'          => $cities,
            \'initialKitchens\' => $initialKitchens,
            \'selectedCity\'    => $city,
            \'userLat\'         => $lat,
            \'userLng\'         => $lng,
            \'radius\'          => $rad,
        ]);
    }

    /**
     * AJAX/JSON API: Fetch nearby approved kitchens based on lat, lng, radius, keyword, sort
     * GET /api/kitchens/nearby
     */
    public function apiNearbyKitchens(): void {
        header(\'Content-Type: application/json; charset=UTF-8\');

        require_once APP_PATH . \'/models/Kitchen.php\';
        $kitchenModel = new Kitchen();

        $latRaw = $_GET[\'lat\'] ?? null;
        $lngRaw = $_GET[\'lng\'] ?? null;
        $radiusRaw = $_GET[\'radius\'] ?? 10;
        $keyword = trim($_GET[\'q\'] ?? $_GET[\'keyword\'] ?? \'\');
        $openOnly = !empty($_GET[\'open_only\']) ? 1 : 0;
        $sort = sanitize($_GET[\'sort\'] ?? \'distance\');
        $city = trim($_GET[\'city\'] ?? \'\');

        $filters = [
            \'keyword\'   => $keyword,
            \'open_only\' => $openOnly,
            \'sort\'      => $sort,
        ];
        if (!empty($city)) {
            $filters[\'city\'] = $city;
        }

        $radius = is_numeric($radiusRaw) ? max(1.0, min(200.0, (float)$radiusRaw)) : 10.0;
        $hasCoordinates = ($latRaw !== null && $lngRaw !== null && is_numeric($latRaw) && is_numeric($lngRaw));

        if ($hasCoordinates) {
            $lat = (float)$latRaw;
            $lng = (float)$lngRaw;

            // Server-side coordinate bounds validation
            if ($lat < -90.0 || $lat > 90.0 || $lng < -180.0 || $lng > 180.0) {
                http_response_code(400);
                echo json_encode([
                    \'success\' => false,
                    \'error\'   => \'Latitude must be between -90 and 90, and longitude between -180 and 180.\'
                ]);
                exit;
            }

            $rawKitchens = $kitchenModel->findNearby($lat, $lng, $radius, $filters, 60);
        } else {
            $lat = null;
            $lng = null;
            $rawKitchens = $kitchenModel->findAllWithCoordinates($filters, 60);
        }

        // Sanitize and format data strictly for customer-facing JSON
        $kitchens = [];
        foreach ($rawKitchens as $k) {
            $distanceKm = isset($k[\'distance\']) ? (float)$k[\'distance\'] : null;
            $distanceText = $distanceKm !== null ? ($distanceKm < 1 ? round($distanceKm * 1000) . \' m away\' : $distanceKm . \' km away\') : null;

            $bannerUrl = !empty($k[\'banner_image\']) 
                ? UPLOAD_URL . \'/kitchens/\' . $k[\'banner_image\'] 
                : null;

            $kitchens[] = [
                \'id\'             => (int)$k[\'id\'],
                \'name\'           => htmlspecialchars($k[\'kitchen_name\'], ENT_QUOTES, \'UTF-8\'),
                \'chef_name\'      => htmlspecialchars($k[\'chef_name\'], ENT_QUOTES, \'UTF-8\'),
                \'address\'        => htmlspecialchars($k[\'address\'], ENT_QUOTES, \'UTF-8\'),
                \'city\'           => htmlspecialchars($k[\'city\'], ENT_QUOTES, \'UTF-8\'),
                \'pincode\'        => htmlspecialchars($k[\'pincode\'], ENT_QUOTES, \'UTF-8\'),
                \'latitude\'       => (float)$k[\'latitude\'],
                \'longitude\'      => (float)$k[\'longitude\'],
                \'distance\'       => $distanceKm,
                \'distance_text\'  => $distanceText,
                \'is_open\'        => (int)$k[\'is_open\'] === 1,
                \'avg_rating\'     => $k[\'avg_rating\'] !== null ? (float)$k[\'avg_rating\'] : null,
                \'review_count\'   => (int)($k[\'review_count\'] ?? 0),
                \'dish_count\'     => (int)($k[\'dish_count\'] ?? 0),
                \'hygiene_badge\'  => $k[\'hygiene_badge\'] ?? \'none\',
                \'story_preview\'  => !empty($k[\'personal_story\']) ? htmlspecialchars(mb_strimwidth($k[\'personal_story\'], 0, 110, \'...\'), ENT_QUOTES, \'UTF-8\') : \'\',
                \'banner_url\'     => $bannerUrl,
                \'kitchen_url\'    => ctx_url(\'/kitchen/\' . $k[\'id\']),
            ];
        }

        echo json_encode([
            \'success\'       => true,
            \'count\'         => count($kitchens),
            \'radius_km\'     => $radius,
            \'user_location\' => $hasCoordinates ? [\'lat\' => $lat, \'lng\' => $lng] : null,
            \'kitchens\'      => $kitchens,
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;
    }';

if ($isCrlf) {
    $search = str_replace("\n", "\r\n", $search);
    $newMethods = str_replace("\n", "\r\n", $newMethods);
}

if (strpos($content, $search) !== false) {
    $content = str_replace($search, $search . $newMethods, $content);
    file_put_contents($filePath, $content);
    echo "SUCCESS: CustomerController updated.\n";
} else {
    echo "ERROR: Target content not found in CustomerController.php\n";
    exit(1);
}
