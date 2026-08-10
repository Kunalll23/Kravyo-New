<?php
/**
 * Kravyo - Cart Controller (Session-Based Shopping Cart)
 * Phase 5: Add, update, remove cart items with meal customization
 */

class CartController extends Controller {

    /**
     * Display the cart page
     * GET /cart
     */
    public function index(): void {
        $cart = $this->getCart();
        $cartTotal = $this->calculateTotal($cart);

        $this->render('customer/cart', [
            'title'     => 'Your Cart',
            'cart'      => $cart,
            'cartTotal' => $cartTotal,
        ]);
    }

    /**
     * Add an item to the cart with meal customization
     * POST /cart/add
     */
    public function add(): void {
        Middleware::verifyCsrf();

        require_once APP_PATH . '/models/MenuItem.php';
        $menuItemModel = new MenuItem();

        $menuItemId = (int) ($_POST['menu_item_id'] ?? 0);
        $quantity = max(1, min(10, (int) ($_POST['quantity'] ?? 1)));
        $spiceLevel = in_array($_POST['spice_level'] ?? '', ['Low', 'Medium', 'High']) ? $_POST['spice_level'] : 'Medium';
        $oilLevel = in_array($_POST['oil_level'] ?? '', ['Normal', 'Less Oil']) ? $_POST['oil_level'] : 'Normal';
        $isJain = isset($_POST['is_jain']) ? 1 : 0;

        // Validate menu item exists and is available
        $dish = $menuItemModel->findWithCategory($menuItemId);
        if (!$dish || !$dish['is_available']) {
            Session::setFlash('danger', 'This dish is currently unavailable.');
            $this->redirect('/menu');
            return;
        }

        // If jain requested but dish doesn't support it
        if ($isJain && !$dish['is_jain_available']) {
            $isJain = 0;
        }

        $cart = $this->getCart();
        $kitchenId = (int) $dish['kitchen_id'];

        // SINGLE-KITCHEN ENFORCEMENT
        // Check if cart already has items from a different kitchen
        if (!empty($cart['items']) && $cart['kitchen_id'] !== $kitchenId) {
            // Return JSON for AJAX requests
            if ($this->isAjax()) {
                $this->json([
                    'success'          => false,
                    'kitchen_conflict' => true,
                    'message'          => 'Your cart has items from a different kitchen. Clear cart to add items from this kitchen.',
                    'current_kitchen'  => $cart['kitchen_name'] ?? 'Another Kitchen',
                ]);
                return;
            }

            Session::setFlash('warning', 'Your cart has items from "' . sanitize($cart['kitchen_name'] ?? 'another kitchen') . '". Please clear your cart first to order from a different kitchen.');
            $this->redirect('/dish/' . $menuItemId);
            return;
        }

        // Build unique cart item key (same dish + same customization = same line item)
        $itemKey = $menuItemId . '_' . $spiceLevel . '_' . $oilLevel . '_' . $isJain;

        if (isset($cart['items'][$itemKey])) {
            // Same dish with same customization — increment quantity
            $cart['items'][$itemKey]['quantity'] = min(10, $cart['items'][$itemKey]['quantity'] + $quantity);
        } else {
            // New line item
            $cart['items'][$itemKey] = [
                'menu_item_id' => $menuItemId,
                'item_name'    => $dish['item_name'],
                'image'        => $dish['image'] ?? null,
                'price'        => (float) $dish['price'],
                'quantity'     => $quantity,
                'spice_level'  => $spiceLevel,
                'oil_level'    => $oilLevel,
                'is_jain'      => $isJain,
                'is_veg'       => (int) $dish['is_veg'],
                'category_name'=> $dish['category_name'] ?? '',
            ];
        }

        // Set kitchen context for the cart
        $cart['kitchen_id'] = $kitchenId;

        // Fetch kitchen name if not already set
        if (empty($cart['kitchen_name'])) {
            require_once APP_PATH . '/models/Kitchen.php';
            $kitchenModel = new Kitchen();
            $kitchen = $kitchenModel->find($kitchenId);
            $cart['kitchen_name'] = $kitchen['kitchen_name'] ?? 'Unknown Kitchen';
        }

        $this->saveCart($cart);

        if ($this->isAjax()) {
            $this->json([
                'success'    => true,
                'message'    => 'Item added to cart!',
                'cart_count' => $this->getCartItemCount(),
                'cart_total' => $this->calculateTotal($cart),
            ]);
            return;
        }

        Session::setFlash('success', '"' . sanitize($dish['item_name']) . '" added to your cart!');
        $this->redirect('/cart');
    }

    /**
     * Update item quantity in cart
     * POST /cart/update
     */
    public function update(): void {
        Middleware::verifyCsrf();

        $itemKey = $_POST['item_key'] ?? '';
        $quantity = max(0, min(10, (int) ($_POST['quantity'] ?? 0)));

        $cart = $this->getCart();

        if (!isset($cart['items'][$itemKey])) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Item not found in cart.'], 404);
                return;
            }
            Session::setFlash('danger', 'Item not found in cart.');
            $this->redirect('/cart');
            return;
        }

        if ($quantity === 0) {
            // Remove item if quantity set to 0
            unset($cart['items'][$itemKey]);
        } else {
            $cart['items'][$itemKey]['quantity'] = $quantity;
        }

        // If cart is now empty, reset kitchen context
        if (empty($cart['items'])) {
            $cart = ['items' => [], 'kitchen_id' => null, 'kitchen_name' => null];
        }

        $this->saveCart($cart);

        if ($this->isAjax()) {
            $this->json([
                'success'    => true,
                'message'    => 'Cart updated.',
                'cart_count' => $this->getCartItemCount(),
                'cart_total' => $this->calculateTotal($cart),
                'item_subtotal' => isset($cart['items'][$itemKey])
                    ? $cart['items'][$itemKey]['price'] * $cart['items'][$itemKey]['quantity']
                    : 0,
            ]);
            return;
        }

        Session::setFlash('success', 'Cart updated successfully.');
        $this->redirect('/cart');
    }

    /**
     * Remove item from cart
     * POST /cart/remove
     */
    public function remove(): void {
        Middleware::verifyCsrf();

        $itemKey = $_POST['item_key'] ?? '';
        $cart = $this->getCart();

        if (isset($cart['items'][$itemKey])) {
            $removedName = $cart['items'][$itemKey]['item_name'] ?? 'Item';
            unset($cart['items'][$itemKey]);

            // If cart is now empty, reset kitchen context
            if (empty($cart['items'])) {
                $cart = ['items' => [], 'kitchen_id' => null, 'kitchen_name' => null];
            }

            $this->saveCart($cart);

            if ($this->isAjax()) {
                $this->json([
                    'success'    => true,
                    'message'    => '"' . $removedName . '" removed from cart.',
                    'cart_count' => $this->getCartItemCount(),
                    'cart_total' => $this->calculateTotal($cart),
                ]);
                return;
            }

            Session::setFlash('success', '"' . sanitize($removedName) . '" removed from your cart.');
        }

        $this->redirect('/cart');
    }

    // =========================================================================
    // Cart Session Helpers
    // =========================================================================

    /**
     * Get cart data from session
     */
    private function getCart(): array {
        $cart = Session::get('cart', null);
        if (!$cart || !is_array($cart)) {
            return ['items' => [], 'kitchen_id' => null, 'kitchen_name' => null];
        }
        return $cart;
    }

    /**
     * Save cart data to session
     */
    private function saveCart(array $cart): void {
        Session::set('cart', $cart);
    }

    /**
     * Calculate cart grand total
     */
    private function calculateTotal(array $cart): float {
        $total = 0;
        foreach ($cart['items'] as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        return round($total, 2);
    }

    /**
     * Get total item count in cart (sum of quantities)
     */
    private function getCartItemCount(): int {
        $cart = $this->getCart();
        $count = 0;
        foreach ($cart['items'] as $item) {
            $count += $item['quantity'];
        }
        return $count;
    }

    /**
     * Check if request is AJAX
     */
    private function isAjax(): bool {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}
