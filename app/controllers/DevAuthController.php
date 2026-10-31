<?php
class DevAuthController extends Controller {

    public function chef(): void {
        $id = !empty($_GET['id']) ? (int)$_GET['id'] : 5;
        Session::set('user_id', $id);
        Session::set('user_name', $id === 56 ? "Mehul Chinchangare" : "Priya Sharma");
        Session::set('user_role', ROLE_CHEF);
        echo "CHEF_SESSION_OK";
    }

    public function delivery(): void {
        Session::set('user_id', 216);
        Session::set('delivery_partner_id', 94);
        Session::set('user_name', "Dinesh Chaudhary");
        Session::set('user_role', ROLE_DELIVERY);
        echo "DELIVERY_SESSION_OK";
    }

    public function admin(): void {
        Session::set('admin_id', 4);
        Session::set('admin_name', "Sanyaa");
        Session::set('admin_role', 'admin');
        echo "ADMIN_SESSION_OK";
    }

    public function customer(): void {
        Session::set('user_id', 1);
        Session::set('user_name', "Neha Singh 14");
        Session::set('user_role', ROLE_CUSTOMER);
        Session::set('cart', [
            'kitchen_id' => 2,
            'kitchen_name' => "Priya's Rasoi",
            'items' => [
                'item_1' => [
                    'id' => 1,
                    'item_id' => 1,
                    'kitchen_id' => 2,
                    'item_name' => 'Paneer Butter Masala Meal',
                    'price' => 180.00,
                    'quantity' => 2,
                    'is_veg' => 1,
                    'spice_level' => 'Medium',
                    'oil_level' => 'Normal',
                    'is_jain' => 0,
                    'item_notes' => 'Extra gravy please'
                ]
            ]
        ]);
        echo "CUSTOMER_SESSION_OK";
    }
}
