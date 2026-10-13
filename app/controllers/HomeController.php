<?php
/**
 * Kravyo - Home Controller
 */

class HomeController extends Controller {

    /**
     * Display home landing page
     */
    public function index(): void {
        require_once APP_PATH . '/models/MenuItem.php';
        $menuItemModel = new MenuItem();
        
        $topSellingDishes = $menuItemModel->getTopSellingItems(4); // Top 4 items

        $this->render('home/index', [
            'title' => 'Kravyo - Cloud Kitchen Platform for Homemakers',
            'topSellingDishes' => $topSellingDishes
        ], 'public_portal');
    }

    /**
     * Display About / Hygiene info page
     */
    public function about(): void {
        $this->render('home/about', [
            'title' => 'About Kravyo & Hygiene Standards'
        ]);
    }

    /**
     * Display Contact page
     */
    public function contact(): void {
        $this->render('home/contact', [
            'title' => 'Contact Us'
        ]);
    }
}
