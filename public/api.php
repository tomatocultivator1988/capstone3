<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Config\ServiceContainer;
use App\Config\Router;

// Start session
session_start();

// Set CORS headers for API
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    // Create container and router
    $container = new ServiceContainer();
    $router = new Router($container);

    // Get request method and URI
    $method = $_SERVER['REQUEST_METHOD'];
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    // Remove /capstonemvc10/public from URI if present
    $uri = str_replace('/capstonemvc10/public', '', $uri);

    // Dispatch the request
    $router->dispatch($method, $uri);
} catch (Exception $e) {
    // Handle any uncaught exceptions
    error_log("API Error: " . $e->getMessage());
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => 'Internal server error'
    ]);
}