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
        
        require_once APP_PATH . '/models/User.php';
        require_once APP_PATH . '/models/Address.php';

        $userModel = new User();
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

        require_once APP_PATH . '/models/User.php';
        $userModel = new User();
        
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
        $street      = sanitize(trim($_POST['street_address'] ?? ''));
        $landmark    = sanitize(trim($_POST['landmark'] ?? ''));
        $city        = sanitize(trim($_POST['city'] ?? ''));
        $pincode     = sanitize(trim($_POST['pincode'] ?? ''));

        if (empty($street) || empty($city) || empty($pincode)) {
            Session::setFlash('danger', 'Street address, city, and pincode are required.');
            $this->redirect('/profile');
            return;
        }

        $addressModel->create([
            'user_id'        => $userId,
            'address_type'   => in_array($addressType, ['Home','Work','Other']) ? $addressType : 'Home',
            'street_address' => $street,
            'landmark'       => $landmark ?: null,
            'city'           => $city,
            'pincode'        => $pincode
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
        $street      = sanitize(trim($_POST['street_address'] ?? ''));
        $landmark    = sanitize(trim($_POST['landmark'] ?? ''));
        $city        = sanitize(trim($_POST['city'] ?? ''));
        $pincode     = sanitize(trim($_POST['pincode'] ?? ''));

        if (empty($street) || empty($city) || empty($pincode)) {
            Session::setFlash('danger', 'Street address, city, and pincode are required.');
            $this->redirect('/profile');
            return;
        }

        $addressModel->update($addressId, [
            'address_type'   => in_array($addressType, ['Home','Work','Other']) ? $addressType : 'Home',
            'street_address' => $street,
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
}
