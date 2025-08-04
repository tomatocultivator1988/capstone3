<?php
/**
 * Login Page Entry Point
 * 
 * This file serves as the entry point for the login page.
 * It delegates to the AuthController to handle the request.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Controller\AuthController;

// Create controller instance
$authController = new AuthController();

// Show login page
$authController->showLogin();
?>