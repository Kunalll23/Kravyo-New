<?php
/**
 * Kravyo - Global Helper Functions
 */

if (!function_exists('sanitize')) {
    /**
     * Escape a string for safe HTML output.
     * Phase 10: Added ENT_SUBSTITUTE to handle malformed Unicode without stripping.
     */
    function sanitize(string $input): string {
        return htmlspecialchars(trim($input), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('sanitizeInput')) {
    /**
     * Phase 10: Batch-sanitise an associative array of user inputs (e.g. $_POST, $_GET).
     * Recursively trims and escapes all string values.
     *
     * @param array $data  Raw input array
     * @return array       Sanitised copy — safe for HTML output
     */
    function sanitizeInput(array $data): array {
        $clean = [];
        foreach ($data as $key => $value) {
            $cleanKey = htmlspecialchars((string) $key, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            if (is_array($value)) {
                $clean[$cleanKey] = sanitizeInput($value);
            } else {
                $clean[$cleanKey] = htmlspecialchars(trim((string) $value), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            }
        }
        return $clean;
    }
}

if (!function_exists('sanitizeInt')) {
    /**
     * Phase 10: Safely cast a value to a non-negative integer.
     * Returns 0 if the value is not a valid positive integer.
     */
    function sanitizeInt(mixed $value, int $min = 0): int {
        $int = (int) $value;
        return max($min, $int);
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string {
        return APP_URL . '/' . ltrim($path, '/');
    }
}

if (!function_exists('ctx_url')) {
    /**
     * Context-aware URL helper.
     *
     * Rewrites public customer-facing paths so they stay within the current
     * session's URL prefix (KRAVYO_CHEF → /chef/*, KRAVYO_ADMIN → /admin/*).
     * This prevents session-context switching when a Chef or Admin navigates
     * to a shared page like "Browse Dishes" or "View Kitchen".
     *
     * Usage in views:  ctx_url('/dish/' . $id)   instead of  url('/dish/' . $id)
     *                  ctx_url('/kitchens')       instead of  url('/kitchens')
     *
     * Customer context (default) returns the original path unchanged.
     */
    function ctx_url(string $path): string {
        $ctx = Session::currentContext();

        if ($ctx === Session::CHEF_SESSION) {
            // Map customer public paths → chef-prefixed equivalents
            $path = preg_replace('#^/dish/(.+)#',     '/chef/dish/$1',           $path);
            $path = preg_replace('#^/kitchen/(\d+)#',  '/chef/kitchen/$1',        $path);
            $path = preg_replace('#^/kitchens$#',      '/chef/kitchens',          $path);
            $path = preg_replace('#^/menu$#',          '/chef/menu-browse',       $path);
            $path = preg_replace('#^/zero-waste$#',    '/chef/zero-waste-deals',  $path);
            $path = preg_replace('#^/subscriptions$#', '/chef/subscriptions-browse', $path);
            $path = preg_replace('#^/subscription/(\d+)#', '/chef/subscription/$1', $path);
        } elseif ($ctx === Session::ADMIN_SESSION) {
            $path = preg_replace('#^/dish/(.+)#',     '/admin/dish/$1',            $path);
            $path = preg_replace('#^/kitchen/(\d+)#',  '/admin/kitchen/$1',        $path);
            $path = preg_replace('#^/kitchens$#',      '/admin/kitchens-browse',   $path);
            $path = preg_replace('#^/menu$#',          '/admin/menu-browse',       $path);
            $path = preg_replace('#^/zero-waste$#',    '/admin/zero-waste-browse', $path);
        }

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
