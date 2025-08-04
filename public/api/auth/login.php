<?php
/**
 * Login API Endpoint
 * 
 * This file serves as the API endpoint for login functionality.
 * It delegates to the AuthController to handle the request.
 */

require_once __DIR__ . '/../../../vendor/autoload.php';

use App\Controller\AuthController;

// Create controller instance
$authController = new AuthController();

// Handle login request
$authController->login();
?>