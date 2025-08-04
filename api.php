<?php

/**
 * Main API Entry Point
 * 
 * Single entry point for all API requests.
 * Replaces all individual api/*.php files.
 * Uses Router to dispatch requests to appropriate controllers.
 */

// Enable error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include the autoloader
require_once __DIR__ . '/vendor/autoload.php';

use Config\Router;
use Exception;

try {
    // Set error handling
    set_error_handler(function($severity, $message, $file, $line) {
        if (!(error_reporting() & $severity)) {
            return false;
        }
        throw new ErrorException($message, 0, $severity, $file, $line);
    });

    // Set exception handler
    set_exception_handler(function($exception) {
        error_log("Uncaught exception: " . $exception->getMessage());
        
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => 'Internal server error'
        ]);
    });

    // Set timezone
    date_default_timezone_set('Asia/Manila');

    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // CORS headers for development
    if (isset($_SERVER['HTTP_ORIGIN'])) {
        header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400');
    }

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
            header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        }

        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
            header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
        }

        exit(0);
    }

    // Initialize and dispatch router
    $router = new Router('/api');
    $router->dispatch();

} catch (Exception $e) {
    error_log("API Error: " . $e->getMessage());
    
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => 'Internal server error',
        'debug' => $e->getMessage() // Remove this in production
    ]);
}