<?php
/**
 * Kravyo - Global Helper Functions
 */

if (!function_exists('sanitize')) {
    function sanitize(string $input): string {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string {
        return APP_URL . '/' . ltrim($path, '/');
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string {
        return ASSET_URL . '/' . ltrim($path, '/');
    }
}

if (!function_exists('redirect')) {
    function redirect(string $path): void {
        header('Location: ' . url($path));
        exit;
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string {
        return Session::generateCsrfToken();
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string {
        return '<input type="hidden" name="_csrf" value="' . csrf_token() . '">';
    }
}

if (!function_exists('dd')) {
    function dd(mixed ...$vars): void {
        echo '<pre style="background: #1e1e1e; color: #00ff66; padding: 15px; border-radius: 8px; font-family: monospace;">';
        foreach ($vars as $var) {
            var_dump($var);
        }
        echo '</pre>';
        exit;
    }
}

if (!function_exists('format_currency')) {
    function format_currency(float|int $amount): string {
        return '₹' . number_format($amount, 2);
    }
}
