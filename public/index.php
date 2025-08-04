<?php
/**
 * Exam Management System - Main Entry Point
 * 
 * This file serves as the main entry point for the Exam Management System.
 * It uses the Router to handle ALL requests including API routes.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Router;

// Create router instance
$router = new Router();

// Load routes from configuration
$router->loadRoutes(__DIR__ . '/../src/config/routes.php');

// Handle the request
$router->handleRequest();
?>