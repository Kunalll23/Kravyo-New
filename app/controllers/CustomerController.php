<?php
/**
 * Kravyo - Customer Controller (Food Discovery & Browsing)
 * Phase 5: Kitchen browsing, dish catalog, chef profiles, dish detail
 */

class CustomerController extends Controller {

    public function home(): void {
        require_once APP_PATH . '/models/Kitchen.php';
        require_once APP_PATH . '/models/MenuItem.php';
        require_once APP_PATH . '/models/Customer.php';
        require_once APP_PATH . '/models/Review.php';

        $customerId = (int) Session::get('user_id');

        require_once APP_PATH . '/models/Address.php';
        $addressModel = new Address();
        $userAddresses = $addressModel->findByUserId($customerId);
        $customerLat = !empty($userAddresses) && !empty($userAddresses[0]['latitude']) ? (float)$userAddresses[0]['latitude'] : null;
        $customerLng = !empty($userAddresses) && !empty($userAddresses[0]['longitude']) ? (float)$userAddresses[0]['longitude'] : null;

        $sessionCity = Session::get('selected_city');
        if (empty($sessionCity) && !empty($userAddresses) && !empty($userAddresses[0]['city'])) {
            $customerCity = $userAddresses[0]['city'];
            Session::set('selected_city', $customerCity);
        } else {
            $customerCity = $sessionCity ?: 'Surat';
        }

        $menuItemModel = new MenuItem();
        $kitchenModel = new Kitchen();
        $reviewModel = new Review();

        require_once APP_PATH . '/models/Category.php';
        $categoryModel = new Category();
        $adminCategories = $categoryModel->getAllOrdered();

        $searchQuery = trim($_GET['q'] ?? '');

        $featuredKitchens = $kitchenModel->getFeaturedKitchens(4, $customerCity, $searchQuery, $customerLat, $customerLng);
        foreach ($featuredKitchens as &$kitchen) {
            $kitchen['avg_rating'] = $reviewModel->getAverageRating((int) $kitchen['id']);
            $kitchen['review_count'] = $reviewModel->countByKitchenId((int) $kitchen['id']);
        }
        unset($kitchen);

        $topSellingDishes = $menuItemModel->getTopSellingItems(4, $customerCity, $searchQuery, $customerLat, $customerLng);
        $recommendedDishes = $menuItemModel->getRecommendedItems(4, $customerCity, $searchQuery, $customerLat, $customerLng);

        $this->render('customer/home', [
            'title' => 'Kravyo - Authentic Homemade Meals',
            'featuredKitchens' => $featuredKitchens,
            'topSellingDishes' => $topSellingDishes,
            'recommendedDishes' => $recommendedDishes,
            'customerCity' => $customerCity,
            'searchQuery' => $searchQuery,
            'customerLat' => $customerLat,
            'customerLng' => $customerLng,
            'adminCategories' => $adminCategories
        ]);
    }


    /**
     * Browse all approved home kitchens with search & filter
     * GET /kitchens
     */
    public function browseKitchens(): void {
        require_once APP_PATH . '/models/Kitchen.php';
        require_once APP_PATH . '/models/Review.php';

        $kitchenModel = new Kitchen();
        $reviewModel = new Review();

        $selectedCity = Session::get('selected_city') ?: '';

        // Gather filter parameters from query string (defaults to session city if not specified)
        $filters = [
            'keyword'   => trim($_GET['q'] ?? ''),
            'city'      => isset($_GET['city']) ? trim($_GET['city']) : $selectedCity,
            'pincode'   => trim($_GET['pincode'] ?? ''),
            'open_only' => isset($_GET['open_only']) ? 1 : 0,
        ];

        $kitchens = $kitchenModel->findApprovedWithFilters($filters);

        // Attach average rating to each kitchen
        foreach ($kitchens as &$kitchen) {
            $kitchen['avg_rating'] = $reviewModel->getAverageRating((int) $kitchen['id']);
            $kitchen['review_count'] = $reviewModel->countByKitchenId((int) $kitchen['id']);
        }
        unset($kitchen);

        // Get distinct cities for filter dropdown
        $cities = $kitchenModel->getDistinctCities();

        $this->render('customer/kitchens', [
            'title'    => 'Explore Home Kitchens',
            'kitchens' => $kitchens,
            'cities'   => $cities,
            'filters'  => $filters,
        ]);
    }

    /**
     * Display Nearby Cloud Kitchens interactive map page
     * GET /nearby-kitchens
     * GET /kitchens/nearby
     */
    public function nearbyKitchens(): void {
        require_once APP_PATH . '/models/Kitchen.php';
        require_once APP_PATH . '/models/Review.php';

        $kitchenModel = new Kitchen();
        $cities = $kitchenModel->getDistinctCities();

$city = trim($_GET['city'] ?? '');
        $lat  = isset($_GET['lat']) && is_numeric($_GET['lat']) ? (float)$_GET['lat'] : null;
        $lng  = isset($_GET['lng']) && is_numeric($_GET['lng']) ? (float)$_GET['lng'] : null;

        if ($lat === null || $lng === null) {
            $userId = (int) Session::get('user_id');
            if ($userId > 0) {
                require_once APP_PATH . '/models/Address.php';
                $addressModel = new Address();
                $userAddresses = $addressModel->findByUserId($userId);
                if (!empty($userAddresses) && !empty($userAddresses[0]['latitude'])) {
                    $lat = (float)$userAddresses[0]['latitude'];
                    $lng = (float)$userAddresses[0]['longitude'];
                }
            }
        }

        $defaultRadius = isset($_GET['route_to']) ? 50.0 : 10.0;
        $rad  = isset($_GET['radius']) && is_numeric($_GET['radius']) ? max(1.0, min(200.0, (float)$_GET['radius'])) : $defaultRadius;

        $filters = [];
        if (!empty($city)) {
            $filters['city'] = $city;
        }

        // If coordinates provided, find nearby; else load all approved with coordinates
        if ($lat !== null && $lng !== null && $lat >= -90.0 && $lat <= 90.0 && $lng >= -180.0 && $lng <= 180.0) {
            $initialKitchens = $kitchenModel->findNearby($lat, $lng, $rad, $filters, 50);
        } else {
            $initialKitchens = $kitchenModel->findAllWithCoordinates($filters, 50);
        }

        $this->render('customer/nearby_kitchens', [
            
            'title'           => 'Nearby Cloud Kitchens — Kravyo',
            'cities'          => $cities,
            'initialKitchens' => $initialKitchens,
            'selectedCity'    => $city,
            'userLat'         => $lat,
            'userLng'         => $lng,
            'radius'          => $rad,
        ]);
    }

    /**
     * AJAX/JSON API: Fetch nearby approved kitchens based on lat, lng, radius, keyword, sort
     * GET /api/kitchens/nearby
     */
    public function apiNearbyKitchens(): void {
        header('Content-Type: application/json; charset=UTF-8');

        require_once APP_PATH . '/models/Kitchen.php';
        $kitchenModel = new Kitchen();

        $latRaw = $_GET['lat'] ?? null;
        $lngRaw = $_GET['lng'] ?? null;
        $radiusRaw = $_GET['radius'] ?? 10;
        $keyword = trim($_GET['q'] ?? $_GET['keyword'] ?? '');
        $openOnly = !empty($_GET['open_only']) ? 1 : 0;
        $sort = sanitize($_GET['sort'] ?? 'distance');
        $city = trim($_GET['city'] ?? '');

        $filters = [
            'keyword'   => $keyword,
            'open_only' => $openOnly,
            'sort'      => $sort,
        ];
        if (!empty($city)) {
            $filters['city'] = $city;
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
                    'success' => false,
                    'error'   => 'Latitude must be between -90 and 90, and longitude between -180 and 180.'
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
            $distanceKm = isset($k['distance']) ? (float)$k['distance'] : null;
            $distanceText = $distanceKm !== null ? ($distanceKm < 1 ? round($distanceKm * 1000) . ' m away' : $distanceKm . ' km away') : null;

            $bannerUrl = !empty($k['banner_image']) 
                ? UPLOAD_URL . '/kitchens/' . $k['banner_image'] 
                : null;

            $kitchens[] = [
                'id'             => (int)$k['id'],
                'name'           => htmlspecialchars($k['kitchen_name'], ENT_QUOTES, 'UTF-8'),
                'chef_name'      => htmlspecialchars($k['chef_name'], ENT_QUOTES, 'UTF-8'),
                'address'        => htmlspecialchars($k['address'], ENT_QUOTES, 'UTF-8'),
                'city'           => htmlspecialchars($k['city'], ENT_QUOTES, 'UTF-8'),
                'pincode'        => htmlspecialchars($k['pincode'], ENT_QUOTES, 'UTF-8'),
                'latitude'       => (float)$k['latitude'],
                'longitude'      => (float)$k['longitude'],
                'distance'       => $distanceKm,
                'distance_text'  => $distanceText,
                'is_open'        => (int)$k['is_open'] === 1,
                'avg_rating'     => $k['avg_rating'] !== null ? (float)$k['avg_rating'] : null,
                'review_count'   => (int)($k['review_count'] ?? 0),
                'dish_count'     => (int)($k['dish_count'] ?? 0),
                'hygiene_badge'  => $k['hygiene_badge'] ?? 'none',
                'story_preview'  => !empty($k['personal_story']) ? htmlspecialchars(function_exists('mb_strimwidth') ? mb_strimwidth($k['personal_story'], 0, 110, '...') : (strlen($k['personal_story']) > 110 ? substr($k['personal_story'], 0, 107) . '...' : $k['personal_story']), ENT_QUOTES, 'UTF-8') : '',
                'banner_url'     => $bannerUrl,
                'kitchen_url'    => ctx_url('/kitchen/' . $k['id']),
            ];
        }

        echo json_encode([
            'success'       => true,
            'count'         => count($kitchens),
            'radius_km'     => $radius,
            'user_location' => $hasCoordinates ? ['lat' => $lat, 'lng' => $lng] : null,
            'kitchens'      => $kitchens,
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Save globally selected city into customer session
     * POST /api/set-city
     */
    public function setCity(): void {
        Middleware::verifyCsrf();
        header('Content-Type: application/json; charset=UTF-8');

        $city = trim($_POST['city'] ?? '');
        if (empty($city)) {
            $input = json_decode(file_get_contents('php://input'), true);
            if (is_array($input) && !empty($input['city'])) {
                $city = trim($input['city']);
            }
        }

        if (empty($city)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'City is required.']);
            exit;
        }

        require_once APP_PATH . '/models/Kitchen.php';
        $kitchenModel = new Kitchen();
        $distinctCities = $kitchenModel->getDistinctCities();

        $selectedCity = null;
        foreach ($distinctCities as $c) {
            if (strcasecmp($c, $city) === 0) {
                $selectedCity = $c;
                break;
            }
        }

        if (!$selectedCity) {
            $selectedCity = sanitize($city);
        }

        Session::set('selected_city', $selectedCity);

        echo json_encode([
            'success' => true,
            'city'    => $selectedCity
        ]);
        exit;
    }

    /**
     * Live search dish suggestions API (JSON)
     * Strictly queries dishes (menu_items) - no kitchens.
     * GET /api/dishes/suggest?q=...&city=...
     */
    public function apiDishSuggestions(): void {
        header('Content-Type: application/json; charset=UTF-8');

        $query = trim($_GET['q'] ?? '');
        $city  = trim($_GET['city'] ?? '');
        if (empty($city)) {
            $city = trim(Session::get('selected_city') ?: 'Surat');
        }

        if (mb_strlen($query) < 1) {
            echo json_encode(['success' => true, 'dishes' => [], 'city' => $city]);
            exit;
        }

        require_once APP_PATH . '/models/MenuItem.php';
        $menuItemModel = new MenuItem();
        $items = $menuItemModel->getDishSuggestions($query, $city, 8);

        $results = [];
        foreach ($items as $item) {
            $imageUrl = !empty($item['image']) 
                ? UPLOAD_URL . '/dishes/' . $item['image'] 
                : url('/assets/images/default-dish.jpg');

            $results[] = [
                'id'            => (int)$item['id'],
                'name'          => $item['item_name'],
                'price'         => (float)$item['price'],
                'price_display' => '₹' . number_format((float)$item['price'], 2),
                'is_veg'        => (int)$item['is_veg'] === 1,
                'category'      => $item['category_name'],
                'kitchen'       => $item['kitchen_name'],
                'city'          => $item['city'],
                'image_url'     => $imageUrl,
                'url'           => url('/dish/' . $item['id']),
            ];
        }

        echo json_encode([
            'success' => true,
            'query'   => $query,
            'city'    => $city,
            'count'   => count($results),
            'dishes'  => $results
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * View a single home chef's kitchen profile with menu & reviews
     * GET /kitchen/{id}
     */
    public function viewKitchen(string $id): void {
        require_once APP_PATH . '/models/Kitchen.php';
        require_once APP_PATH . '/models/MenuItem.php';
        require_once APP_PATH . '/models/Review.php';
        require_once APP_PATH . '/models/Category.php';

        $kitchenModel = new Kitchen();
        $menuItemModel = new MenuItem();
        $reviewModel = new Review();

        $kitchen = $kitchenModel->findWithChefById((int) $id);

        if (!$kitchen) {
            http_response_code(404);
            $this->render('errors/404', ['title' => 'Kitchen Not Found']);
            return;
        }

        // Get menu items grouped by category
        $menuItems = $menuItemModel->findByKitchenId((int) $kitchen['id']);

        // Group items by category name for tabbed display
        $menuByCategory = [];
        foreach ($menuItems as $item) {
            $catName = $item['category_name'] ?? 'Uncategorized';
            $menuByCategory[$catName][] = $item;
        }

        // Get reviews and ratings
        $reviews = $reviewModel->findByKitchenId((int) $kitchen['id']);
        $avgRating = $reviewModel->getAverageRating((int) $kitchen['id']);
        $reviewCount = $reviewModel->countByKitchenId((int) $kitchen['id']);

        $this->render('customer/kitchen_detail', [
            'title'          => sanitize($kitchen['kitchen_name']) . ' — Chef Profile',
            'kitchen'        => $kitchen,
            'menuByCategory' => $menuByCategory,
            'reviews'        => $reviews,
            'avgRating'      => $avgRating,
            'reviewCount'    => $reviewCount,
        ]);
    }

    /**
     * Browse the full dish catalog with filters
     * GET /menu
     */
    public function browseMenu(): void {
        require_once APP_PATH . '/models/MenuItem.php';
        require_once APP_PATH . '/models/Category.php';
        require_once APP_PATH . '/models/Kitchen.php';

        $menuItemModel = new MenuItem();
        $categoryModel = new Category();
        $kitchenModel = new Kitchen();

        $selectedCity = Session::get('selected_city') ?: '';

        $hasVeg    = (isset($_GET['veg']) && $_GET['veg'] === '1');
        $hasNonVeg = (isset($_GET['non_veg']) && $_GET['non_veg'] === '1') || (isset($_GET['veg']) && $_GET['veg'] === '0');

        $isVegFilter = null;
        if ($hasVeg && !$hasNonVeg) {
            $isVegFilter = 1;
        } elseif ($hasNonVeg && !$hasVeg) {
            $isVegFilter = 0;
        }

        // Gather filter parameters (defaults to session city if not specified)
        $filters = [
            'keyword'              => trim($_GET['q'] ?? ''),
            'category_id'          => (int) ($_GET['category'] ?? 0) ?: null,
            'is_veg'               => $isVegFilter,
            'has_veg_checked'      => $hasVeg,
            'has_non_veg_checked'  => $hasNonVeg,
            'is_jain_available'    => isset($_GET['jain']) ? 1 : 0,
            'is_diabetic_friendly' => isset($_GET['diabetic']) ? 1 : 0,
            'city'                 => isset($_GET['city']) ? trim($_GET['city']) : $selectedCity,
            'pincode'              => trim($_GET['pincode'] ?? ''),
            'sort'                 => $_GET['sort'] ?? 'newest',
        ];

        $dishes = $menuItemModel->searchWithFilters($filters);
        $categories = $categoryModel->getAllOrdered();
        $cities = $kitchenModel->getDistinctCities();

        $this->render('customer/menu', [
            'title'      => 'Browse Dishes — Food Catalog',
            'dishes'     => $dishes,
            'categories' => $categories,
            'cities'     => $cities,
            'filters'    => $filters,
        ]);
    }

    /**
     * View a single dish's full details with customization form
     * GET /dish/{id}
     */
    public function viewDish(string $id): void {
        require_once APP_PATH . '/models/MenuItem.php';
        require_once APP_PATH . '/models/Review.php';

        $menuItemModel = new MenuItem();
        $reviewModel = new Review();

        $dish = $menuItemModel->findWithKitchenAndCategory((int) $id);

        if (!$dish) {
            http_response_code(404);
            $this->render('errors/404', ['title' => 'Dish Not Found']);
            return;
        }

        $avgRating = $reviewModel->getAverageRating((int) $dish['kitchen_id']);

        $this->render('customer/dish_detail', [
            'title'     => sanitize($dish['item_name']) . ' — Dish Details',
            'dish'      => $dish,
            'avgRating' => $avgRating,
        ]);
    }

    /**
     * Phase 10: Personalised "Recommended For You" dish page
     * GET /recommendations
     */
    public function recommendations(): void {
        require_once APP_PATH . '/models/Recommendation.php';

        $recModel = new Recommendation();

        $customerId  = (int) Session::get('user_id', 0);
        $isLoggedIn  = $customerId > 0 && Session::get('user_role') === ROLE_CUSTOMER;

        if ($isLoggedIn) {
            $dishes    = $recModel->getForCustomer($customerId, 16);
            $pageTitle = 'Recommended For You';
            $subtitle  = 'Personalised dishes based on your taste & order history';
            $isPersonalised = true;
        } else {
            $dishes    = $recModel->getPopularDishes(16);
            $pageTitle = 'Popular Picks';
            $subtitle  = 'Most-loved dishes ordered by our customers';
            $isPersonalised = false;
        }

        $this->render('customer/recommendations', [
            'title'          => $pageTitle . ' — Kravyo',
            'dishes'         => $dishes,
            'pageTitle'      => $pageTitle,
            'subtitle'       => $subtitle,
            'isPersonalised' => $isPersonalised,
        ]);
    }

    /**
     * Zero Waste deals showcase — live discounted end-of-day meals
     * GET /zero-waste
     */
    public function zeroWasteDeals(): void {
        require_once APP_PATH . '/models/ZeroWasteItem.php';
        require_once APP_PATH . '/models/Kitchen.php';

        $zeroWasteModel = new ZeroWasteItem();
        $kitchenModel   = new Kitchen();

        $selectedCity = Session::get('selected_city') ?: '';
        $cityFilter   = isset($_GET['city']) ? trim($_GET['city']) : $selectedCity;

        // Build filters from query string (defaults to session selected city if not explicitly passed)
        $filters = [
            'is_veg' => isset($_GET['veg']) ? 1 : 0,
            'city'   => $cityFilter,
            'sort'   => $_GET['sort'] ?? 'expiry',
        ];

        $deals  = $zeroWasteModel->findActiveAll($filters);
        $cities = $kitchenModel->getDistinctCities();
        $totalActive = $zeroWasteModel->countActive($filters['city']);

        $this->render('customer/zero_waste', [
            'title'       => 'Zero Waste Deals - Discounted End-of-Day Meals',
            'deals'       => $deals,
            'cities'      => $cities,
            'filters'     => $filters,
            'totalActive' => $totalActive,
        ]);
    }

    // ─── Customer Profile & Address Management ───────────────────────────────

    public function profile(): void {
        Middleware::auth();
        
        require_once APP_PATH . '/models/Customer.php';
        require_once APP_PATH . '/models/Address.php';

        $userModel = new Customer();
        $addressModel = new Address();

        $userId = Session::get('user_id');
        $user = $userModel->findById($userId);
        
        if (!$user) {
            Session::setFlash('danger', 'User not found.');
            $this->redirect('/');
            return;
        }

        $addresses = $addressModel->findByUserId($userId);

        $this->render('customer/profile', [
            'title'     => 'My Profile',
            'user'      => $user,
            'addresses' => $addresses
        ]);
    }

    public function updateProfile(): void {
        Middleware::auth();
        Middleware::verifyCsrf();

        require_once APP_PATH . '/models/Customer.php';
        $userModel = new Customer();
        
        $userId = Session::get('user_id');
        $rawFullName = trim($_POST['full_name'] ?? '');
        $phone       = sanitize(trim($_POST['phone'] ?? ''));

        // Validation
        if (empty($rawFullName) || empty($phone)) {
            Session::setFlash('danger', 'Name and Phone fields are required.');
            $this->redirect('/profile');
            return;
        }

        if (!preg_match('/^[A-Za-z][A-Za-z\' \-]{1,49}$/', $rawFullName)) {
            Session::setFlash('danger', 'Please enter a valid name using letters, spaces, hyphens, or apostrophes only.');
            $this->redirect('/profile');
            return;
        }

        if (!preg_match('/^[6-9][0-9]{9}$/', $phone)) {
            Session::setFlash('danger', 'Please enter a valid 10-digit Indian mobile number.');
            $this->redirect('/profile');
            return;
        }

        $fullName = sanitize($rawFullName);

        // Check if phone belongs to another user
        $existingPhoneUser = $userModel->findByPhone($phone);
        if ($existingPhoneUser && $existingPhoneUser['id'] !== $userId) {
            Session::setFlash('danger', 'This phone number is already registered to another account.');
            $this->redirect('/profile');
            return;
        }

        // Update User
        $userModel->update($userId, [
            'full_name' => $fullName,
            'phone'     => $phone
        ]);

        Session::set('user_name', $fullName);
        Session::setFlash('success', 'Profile updated successfully.');
        $this->redirect('/profile');
    }

    public function addAddress(): void {
        Middleware::auth();
        Middleware::verifyCsrf();
        
        require_once APP_PATH . '/models/Address.php';
        $addressModel = new Address();

        $userId = Session::get('user_id');
        
        $addressType = sanitize($_POST['address_type'] ?? 'Home');
        $apartment   = sanitize(trim($_POST['apartment'] ?? ''));
        $street      = sanitize(trim($_POST['street_address'] ?? ''));
        $landmark    = sanitize(trim($_POST['landmark'] ?? ''));
        $city        = sanitize(trim($_POST['city'] ?? ''));
        $pincode     = sanitize(trim($_POST['pincode'] ?? ''));

        if (empty($street) || empty($city) || empty($pincode)) {
            Session::setFlash('danger', 'Street address, city, and pincode are required.');
            $this->redirect('/profile');
            return;
        }

        $fullStreet = !empty($apartment) && !str_contains($street, $apartment) ? ($apartment . ', ' . $street) : $street;

        $latitude  = isset($_POST['latitude']) && is_numeric($_POST['latitude']) ? (float)$_POST['latitude'] : null;
        $longitude = isset($_POST['longitude']) && is_numeric($_POST['longitude']) ? (float)$_POST['longitude'] : null;

        $addressModel->create([
            'user_id'        => $userId,
            'address_type'   => in_array($addressType, ['Home','Work','Other']) ? $addressType : 'Home',
            'apartment'      => $apartment ?: null,
            'street_address' => $fullStreet,
            'landmark'       => $landmark ?: null,
            'city'           => $city,
            'pincode'        => $pincode,
            'latitude'       => $latitude,
            'longitude'      => $longitude
        ]);

        Session::setFlash('success', 'Address added successfully.');
        $this->redirect('/profile');
    }

    public function editAddress(string $id): void {
        Middleware::auth();
        Middleware::verifyCsrf();
        
        require_once APP_PATH . '/models/Address.php';
        $addressModel = new Address();
        
        $userId = Session::get('user_id');
        $addressId = (int)$id;

        // Security check
        $address = $addressModel->findByIdAndUserId($addressId, $userId);
        if (!$address) {
            Session::setFlash('danger', 'Unauthorized access or address not found.');
            $this->redirect('/profile');
            return;
        }

        $addressType = sanitize($_POST['address_type'] ?? 'Home');
        $apartment   = sanitize(trim($_POST['apartment'] ?? ''));
        $street      = sanitize(trim($_POST['street_address'] ?? ''));
        $landmark    = sanitize(trim($_POST['landmark'] ?? ''));
        $city        = sanitize(trim($_POST['city'] ?? ''));
        $pincode     = sanitize(trim($_POST['pincode'] ?? ''));

        if (empty($street) || empty($city) || empty($pincode)) {
            Session::setFlash('danger', 'Street address, city, and pincode are required.');
            $this->redirect('/profile');
            return;
        }

        $fullStreet = !empty($apartment) && !str_contains($street, $apartment) ? ($apartment . ', ' . $street) : $street;

        $addressModel->update($addressId, [
            'address_type'   => in_array($addressType, ['Home','Work','Other']) ? $addressType : 'Home',
            'apartment'      => $apartment ?: null,
            'street_address' => $fullStreet,
            'landmark'       => $landmark ?: null,
            'city'           => $city,
            'pincode'        => $pincode
        ]);

        Session::setFlash('success', 'Address updated successfully.');
        $this->redirect('/profile');
    }

    public function deleteAddress(string $id): void {
        Middleware::auth();
        Middleware::verifyCsrf();

        require_once APP_PATH . '/models/Address.php';
        $addressModel = new Address();
        
        $userId = Session::get('user_id');
        $addressId = (int)$id;

        // Security check
        $address = $addressModel->findByIdAndUserId($addressId, $userId);
        if (!$address) {
            Session::setFlash('danger', 'Unauthorized access or address not found.');
            $this->redirect('/profile');
            return;
        }

        try {
            $addressModel->delete($addressId);
            Session::setFlash('success', 'Address deleted successfully.');
        } catch (PDOException $e) {
            // Error 1451 is "Cannot delete or update a parent row: a foreign key constraint fails"
            if ($e->getCode() == 23000 || str_contains($e->getMessage(), '1451')) {
                Session::setFlash('danger', 'Cannot delete this address because it is associated with an existing order or subscription.');
            } else {
                Session::setFlash('danger', 'An error occurred while deleting the address.');
            }
        }

        $this->redirect('/profile');
    }

    /**
     * Submit a customer issue report (Kitchen, Dish, or Delivery)
     * POST /customer/report/submit
     */
    public function submitReport(): void {
        Middleware::auth();
        Middleware::verifyCsrf();

        $customerId = (int) Session::get('user_id');
        $kitchenId  = (int) ($_POST['kitchen_id'] ?? 0);
        $orderId    = !empty($_POST['order_id']) ? (int) $_POST['order_id'] : null;
        $targetType = sanitize(trim($_POST['target_type'] ?? 'kitchen'));
        $targetId   = !empty($_POST['target_id']) ? (int) $_POST['target_id'] : null;
        $reason     = sanitize(trim($_POST['reason'] ?? ''));
        $description = sanitize(trim($_POST['description'] ?? ''));

        // Basic validation
        $validTypes = ['kitchen', 'dish', 'delivery'];
        if (!in_array($targetType, $validTypes, true)) {
            $targetType = 'kitchen';
        }

        if ($kitchenId <= 0 || empty($reason) || empty($description)) {
            Session::setFlash('danger', 'Please provide a valid reason and description for your report.');
            $this->redirect($_SERVER['HTTP_REFERER'] ?? '/orders/history');
            return;
        }

        require_once APP_PATH . '/models/CustomerReport.php';
        $reportModel = new CustomerReport();

        // Check for duplicate reports in the last 24 hours
        if ($reportModel->hasDuplicate($customerId, $kitchenId, $targetType, $targetId, $reason)) {
            Session::setFlash('warning', 'You have already submitted a report for this issue recently. Our admin team is reviewing it.');
            $this->redirect($_SERVER['HTTP_REFERER'] ?? '/orders/history');
            return;
        }

        // Create the report record
        $reportId = $reportModel->createReport([
            'customer_id' => $customerId,
            'kitchen_id'  => $kitchenId,
            'order_id'    => $orderId,
            'target_type' => $targetType,
            'target_id'   => $targetId,
            'reason'      => $reason,
            'description' => $description,
            'status'      => 'pending'
        ]);

        if ($reportId) {
            // Send notification to customer confirming submission
            try {
                require_once APP_PATH . '/models/Notification.php';
                $notif = new Notification();
                $notif->create([
                    'user_id' => $customerId,
                    'title'   => 'Report Submitted',
                    'message' => 'Your report regarding "' . $reason . '" has been received. Our support team will investigate.',
                    'type'    => 'system',
                    'is_read' => 0
                ]);
            } catch (Exception $e) {
                // Non-fatal
            }

            Session::setFlash('success', 'Your report has been submitted successfully and will be investigated by our team.');
        } else {
            Session::setFlash('danger', 'Failed to submit report. Please try again.');
        }

        $this->redirect($_SERVER['HTTP_REFERER'] ?? '/orders/history');
    }
}

