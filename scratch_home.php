<?php
$f = 'app/controllers/CustomerController.php';
$c = file_get_contents($f);

$homeMethod = "
    public function home(): void {
        require_once APP_PATH . '/models/Kitchen.php';
        require_once APP_PATH . '/models/MenuItem.php';
        require_once APP_PATH . '/models/Customer.php';

        \$customerId = (int) Session::get('user_id');
        \$customerCity = Session::get('selected_city') ?: 'Surat';

        \$menuItemModel = new MenuItem();
        \$kitchenModel = new Kitchen();
        
        require_once APP_PATH . '/models/Address.php';
        \$addressModel = new Address();
        \$userAddresses = \$addressModel->findByUserId(\$customerId);
        \$customerLat = !empty(\$userAddresses) && !empty(\$userAddresses[0]['latitude']) ? (float)\$userAddresses[0]['latitude'] : null;
        \$customerLng = !empty(\$userAddresses) && !empty(\$userAddresses[0]['longitude']) ? (float)\$userAddresses[0]['longitude'] : null;

        \$searchQuery = trim(\$_GET['q'] ?? '');

        \$featuredKitchens = \$kitchenModel->getFeaturedKitchens(4, \$customerCity, \$searchQuery, \$customerLat, \$customerLng);
        \$topSellingDishes = \$menuItemModel->getTopSellingItems(4, \$customerCity, \$searchQuery, \$customerLat, \$customerLng);
        \$recommendedDishes = \$menuItemModel->getRecommendedItems(4, \$customerCity, \$searchQuery, \$customerLat, \$customerLng);

        \$this->render('customer/home', [
            'title' => 'Kravyo - Authentic Homemade Meals',
            'featuredKitchens' => \$featuredKitchens,
            'topSellingDishes' => \$topSellingDishes,
            'recommendedDishes' => \$recommendedDishes,
            'customerCity' => \$customerCity,
            'searchQuery' => \$searchQuery,
            'customerLat' => \$customerLat,
            'customerLng' => \$customerLng
        ]);
    }
";

if (strpos($c, 'public function home(') === false) {
    $c = str_replace("class CustomerController extends Controller {", "class CustomerController extends Controller {\n" . $homeMethod, $c);
    file_put_contents($f, $c);
    echo "Restored home method.";
} else {
    echo "home method already exists.";
}
