<?php
/**
 * Exam Management System - Main Entry Point
 * 
 * This file serves as the main entry point for the Exam Management System.
 * It uses the Router to handle ALL requests including API routes.
 */

// Error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Check if autoloader is working
if (!class_exists('App\Core\Router')) {
    die("❌ Error: Router class not found. Please run 'composer dump-autoload' to regenerate autoloader.\n");
}

use App\Core\Router;

try {
    // Create router instance
    $router = new Router();

    // Load routes from configuration
    $routesFile = __DIR__ . '/../src/config/routes.php';
    if (!file_exists($routesFile)) {
        die("❌ Error: Routes configuration file not found at: $routesFile\n");
    }
    
    $router->loadRoutes($routesFile);

    // Handle the request
    $router->handleRequest();
    
} catch (Exception $e) {
    // Log the error
    error_log("Application error: " . $e->getMessage());
    
    // Show user-friendly error
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'An error occurred while processing your request.'
    ]);
}
?>