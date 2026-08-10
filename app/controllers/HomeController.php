<?php
/**
 * Kravyo - Home Controller
 */

class HomeController extends Controller {

    /**
     * Display home landing page
     */
    public function index(): void {
        $this->render('home/index', [
            'title' => 'Kravyo - Cloud Kitchen Platform for Homemakers'
        ]);
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
