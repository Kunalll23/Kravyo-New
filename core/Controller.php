<?php
/**
 * Kravyo - Core Controller Base Class
 */

abstract class Controller {
    
    /**
     * Render a view template with layout
     */
    protected function render(string $viewPath, array $data = [], string $layout = 'main'): void {
        View::render($viewPath, $data, $layout);
    }

    /**
     * Return JSON response (for AJAX / API requests)
     */
    protected function json(array $data, int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Redirect to relative URL
     */
    protected function redirect(string $url): void {
        header("Location: " . APP_URL . $url);
        exit;
    }
}
