<?php
$issues = [];
$dir = new RecursiveDirectoryIterator(__DIR__ . '/..');
$iterator = new RecursiveIteratorIterator($dir);

foreach ($iterator as $file) {
    if ($file->getExtension() === 'php') {
        $path = $file->getPathname();
        // PHP syntax check
        exec('php -l "' . $path . '" 2>&1', $output, $returnVar);
        if ($returnVar !== 0) {
            $issues[] = [
                'type' => 'Syntax Error',
                'file' => $path,
                'message' => implode(" ", $output)
            ];
        }
    }
}

// Check Routes
$routes = require __DIR__ . '/../config/routes.php';
$controllersDir = __DIR__ . '/../app/controllers/';
foreach ($routes as $route => $action) {
    list($controller, $method) = explode('@', $action);
    $controllerPath = $controllersDir . $controller . '.php';
    if (!file_exists($controllerPath)) {
        $issues[] = [
            'type' => 'Broken Route',
            'file' => 'config/routes.php',
            'message' => "Missing Controller: $controller for route $route"
        ];
    } else {
        $content = file_get_contents($controllerPath);
        if (!preg_match('/function\s+' . preg_quote($method, '/') . '\s*\(/i', $content)) {
            $issues[] = [
                'type' => 'Broken Route',
                'file' => $controllerPath,
                'message' => "Missing Method: $method in $controller for route $route"
            ];
        }
    }
}

file_put_contents(__DIR__ . '/health_check_results.json', json_encode($issues, JSON_PRETTY_PRINT));
echo "Health check complete. Found " . count($issues) . " issues.\n";
