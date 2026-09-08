<?php
/**
 * Kravyo - Customer Controller (Food Discovery & Browsing)
 * Phase 5: Kitchen browsing, dish catalog, chef profiles, dish detail
 */

class CustomerController extends Controller {

    /**
     * Browse all approved home kitchens with search & filter
     * GET /kitchens
     */
    public function browseKitchens(): void {
        require_once APP_PATH . '/models/Kitchen.php';
        require_once APP_PATH . '/models/Review.php';

        $kitchenModel = new Kitchen();
        $reviewModel = new Review();

        // Gather filter parameters from query string
        $filters = [
            'keyword'   => trim($_GET['q'] ?? ''),
            'city'      => trim($_GET['city'] ?? ''),
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

        // Gather filter parameters
        $filters = [
            'keyword'              => trim($_GET['q'] ?? ''),
            'category_id'          => (int) ($_GET['category'] ?? 0) ?: null,
            'is_veg'               => isset($_GET['veg']) ? 1 : 0,
            'is_jain_available'    => isset($_GET['jain']) ? 1 : 0,
            'is_diabetic_friendly' => isset($_GET['diabetic']) ? 1 : 0,
            'city'                 => trim($_GET['city'] ?? ''),
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

        // Build filters from query string
        $filters = [
            'is_veg' => isset($_GET['veg']) ? 1 : 0,
            'city'   => trim($_GET['city'] ?? ''),
            'sort'   => $_GET['sort'] ?? 'expiry',
        ];

        $deals  = $zeroWasteModel->findActiveAll($filters);
        $cities = $kitchenModel->getDistinctCities();
        $totalActive = $zeroWasteModel->countActive();

        $this->render('customer/zero_waste', [
            'title'       => 'Zero Waste Deals — Discounted End-of-Day Meals',
            'deals'       => $deals,
            'cities'      => $cities,
            'filters'     => $filters,
            'totalActive' => $totalActive,
        ]);
    }
}
