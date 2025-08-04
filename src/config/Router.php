<?php

namespace Config;

use Controller\AuthController;
use Controller\UserController;
use Exception;

/**
 * Router Class
 * 
 * Handles URL routing and dispatching requests to appropriate controllers.
 * Supports REST-like routing patterns.
 */
class Router
{
    private array $routes = [];
    private string $basePath = '';

    public function __construct(string $basePath = '')
    {
        $this->basePath = rtrim($basePath, '/');
        $this->registerRoutes();
    }

    /**
     * Register all application routes
     */
    private function registerRoutes(): void
    {
        // Authentication routes
        $this->post('/auth/login', AuthController::class, 'login');
        $this->post('/auth/logout', AuthController::class, 'logout');
        $this->get('/auth/check', AuthController::class, 'checkAuth');
        $this->post('/auth/change-password', AuthController::class, 'changePassword');

        // User management routes
        $this->get('/users', UserController::class, 'index');
        $this->get('/users/show', UserController::class, 'show');
        $this->post('/users/create', UserController::class, 'create');
        $this->post('/users/update', UserController::class, 'update');
        $this->post('/users/delete', UserController::class, 'delete');
        $this->get('/users/statistics', UserController::class, 'statistics');

        // Add more routes as needed for other controllers
        // Example:
        // $this->get('/exams', ExamController::class, 'index');
        // $this->post('/exams/create', ExamController::class, 'create');
    }

    /**
     * Add GET route
     */
    public function get(string $path, string $controller, string $method): void
    {
        $this->addRoute('GET', $path, $controller, $method);
    }

    /**
     * Add POST route
     */
    public function post(string $path, string $controller, string $method): void
    {
        $this->addRoute('POST', $path, $controller, $method);
    }

    /**
     * Add PUT route
     */
    public function put(string $path, string $controller, string $method): void
    {
        $this->addRoute('PUT', $path, $controller, $method);
    }

    /**
     * Add DELETE route
     */
    public function delete(string $path, string $controller, string $method): void
    {
        $this->addRoute('DELETE', $path, $controller, $method);
    }

    /**
     * Add route to routing table
     */
    private function addRoute(string $httpMethod, string $path, string $controller, string $method): void
    {
        $path = $this->normalizePath($path);
        $this->routes[$httpMethod][$path] = [
            'controller' => $controller,
            'method' => $method
        ];
    }

    /**
     * Dispatch request to appropriate controller
     */
    public function dispatch(): void
    {
        try {
            $httpMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
            $uri = $_SERVER['REQUEST_URI'] ?? '/';
            
            // Remove query string
            $path = parse_url($uri, PHP_URL_PATH);
            $path = $this->normalizePath($path);

            // Remove base path
            if ($this->basePath && strpos($path, $this->basePath) === 0) {
                $path = substr($path, strlen($this->basePath));
                $path = $this->normalizePath($path);
            }

            // Find matching route
            $route = $this->findRoute($httpMethod, $path);

            if (!$route) {
                $this->sendNotFoundResponse($httpMethod, $path);
                return;
            }

            // Instantiate controller and call method
            $controllerClass = $route['controller'];
            $methodName = $route['method'];

            if (!class_exists($controllerClass)) {
                throw new Exception("Controller class not found: $controllerClass");
            }

            $controller = new $controllerClass();

            if (!method_exists($controller, $methodName)) {
                throw new Exception("Method not found: $controllerClass::$methodName");
            }

            // Call the controller method
            $controller->$methodName();

        } catch (Exception $e) {
            error_log("Router::dispatch error: " . $e->getMessage());
            $this->sendErrorResponse('Internal server error', 500);
        }
    }

    /**
     * Find matching route
     */
    private function findRoute(string $httpMethod, string $path): ?array
    {
        // Exact match
        if (isset($this->routes[$httpMethod][$path])) {
            return $this->routes[$httpMethod][$path];
        }

        // Pattern matching (for future use with parameters)
        foreach ($this->routes[$httpMethod] ?? [] as $routePath => $route) {
            if ($this->matchesPattern($routePath, $path)) {
                return $route;
            }
        }

        return null;
    }

    /**
     * Check if path matches route pattern
     */
    private function matchesPattern(string $pattern, string $path): bool
    {
        // For now, just do exact matching
        // In the future, you could implement parameter matching like /users/{id}
        return $pattern === $path;
    }

    /**
     * Normalize path by removing trailing slashes and ensuring leading slash
     */
    private function normalizePath(string $path): string
    {
        $path = trim($path, '/');
        return '/' . $path;
    }

    /**
     * Send 404 Not Found response
     */
    private function sendNotFoundResponse(string $method, string $path): void
    {
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => "Route not found: $method $path",
            'available_routes' => $this->getAvailableRoutes()
        ]);
    }

    /**
     * Send error response
     */
    private function sendErrorResponse(string $message, int $statusCode = 500): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => $message
        ]);
    }

    /**
     * Get list of available routes (for debugging)
     */
    public function getAvailableRoutes(): array
    {
        $routes = [];
        foreach ($this->routes as $method => $methodRoutes) {
            foreach ($methodRoutes as $path => $route) {
                $routes[] = "$method $path";
            }
        }
        return $routes;
    }

    /**
     * Get all registered routes
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }
}