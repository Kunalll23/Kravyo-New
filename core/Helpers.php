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
