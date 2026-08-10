<?php
/**
 * Kravyo - Core Front Controller Router
 */

class Router {
    private array $routes = [];

    public function __construct() {
        $this->routes = require CONFIG_PATH . '/routes.php';
    }

    /**
     * Dispatch incoming HTTP request to matched Controller Action
     */
    public function dispatch(): void {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Strip subdirectories if installed under XAMPP subfolder
        $scriptName = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
        $baseFolder = str_replace('/public', '', $scriptName);
        if ($baseFolder !== '' && $baseFolder !== '/' && strpos($uri, $baseFolder) === 0) {
            $uri = substr($uri, strlen($baseFolder));
        }
        
        // Clean trailing slash except root
        if ($uri !== '/' && substr($uri, -1) === '/') {
            $uri = rtrim($uri, '/');
        }
        
        if (empty($uri)) {
            $uri = '/';
        }

        $routeKey = "$method $uri";

        // Direct Route Match
        if (array_key_exists($routeKey, $this->routes)) {
            $this->callAction($this->routes[$routeKey]);
            return;
        }

        // Dynamic Parameter Route Matching (e.g., /kitchen/{id})
        foreach ($this->routes as $pattern => $target) {
            list($reqMethod, $reqPath) = explode(' ', $pattern, 2);
            if ($reqMethod !== $method) {
                continue;
            }

            // Convert route pattern {id} into regex match group
            $regex = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<\1>[^/]+)', $reqPath);
            $regex = "#^" . $regex . "$#";

            if (preg_match($regex, $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                $this->callAction($target, $params);
                return;
            }
        }

        // 404 Route Not Found
        http_response_code(404);
        View::render('errors/404', ['title' => '404 - Page Not Found']);
    }

    /**
     * Instantiate Controller and execute Action method
     */
    private function callAction(string $target, array $params = []): void {
        list($controllerName, $methodName) = explode('@', $target);

        $controllerFile = APP_PATH . '/controllers/' . $controllerName . '.php';

        if (!file_exists($controllerFile)) {
            http_response_code(500);
            View::render('errors/500', ['error' => "Controller file {$controllerName}.php not found."]);
            return;
        }

        require_once $controllerFile;

        if (!class_exists($controllerName)) {
            http_response_code(500);
            View::render('errors/500', ['error' => "Controller class {$controllerName} does not exist."]);
            return;
        }

        $controller = new $controllerName();

        if (!method_exists($controller, $methodName)) {
            http_response_code(500);
            View::render('errors/500', ['error' => "Method {$methodName} not found in {$controllerName}."]);
            return;
        }

        call_user_func_array([$controller, $methodName], $params);
    }
}
