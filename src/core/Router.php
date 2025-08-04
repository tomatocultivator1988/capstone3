<?php

namespace App\Core;

class Router
{
    private $routes = [];

    /**
     * Add a GET route
     */
    public function get($path, $callback)
    {
        $this->routes['GET'][$path] = $callback;
    }

    /**
     * Add a POST route
     */
    public function post($path, $callback)
    {
        $this->routes['POST'][$path] = $callback;
    }

    /**
     * Add routes for all HTTP methods
     */
    public function any($path, $callback)
    {
        $this->routes['GET'][$path] = $callback;
        $this->routes['POST'][$path] = $callback;
        $this->routes['PUT'][$path] = $callback;
        $this->routes['DELETE'][$path] = $callback;
    }

    /**
     * Handle the current request
     */
    public function handleRequest()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Remove trailing slash
        $path = rtrim($path, '/');
        if (empty($path)) {
            $path = '/';
        }

        // Check if route exists
        if (isset($this->routes[$method][$path])) {
            $callback = $this->routes[$method][$path];
            
            if (is_callable($callback)) {
                call_user_func($callback);
            } elseif (is_string($callback) && strpos($callback, '@') !== false) {
                // Handle Controller@method format
                list($controller, $method) = explode('@', $callback);
                
                if (class_exists($controller)) {
                    $instance = new $controller();
                    if (method_exists($instance, $method)) {
                        call_user_func([$instance, $method]);
                    } else {
                        $this->notFound();
                    }
                } else {
                    $this->notFound();
                }
            } else {
                $this->notFound();
            }
        } else {
            $this->notFound();
        }
    }

    /**
     * Handle 404 Not Found
     */
    private function notFound()
    {
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => 'Route not found.'
        ]);
    }

    /**
     * Get all registered routes
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * Load routes from a configuration file
     */
    public function loadRoutes($routesFile)
    {
        if (file_exists($routesFile)) {
            $routes = require $routesFile;
            foreach ($routes as $method => $routeList) {
                foreach ($routeList as $path => $callback) {
                    $this->routes[$method][$path] = $callback;
                }
            }
        }
    }
}
?>