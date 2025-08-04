<?php
/**
 * Dashboard Entry Point
 * 
 * This file serves as the entry point for the dashboard.
 * It delegates to the DashboardController to handle the request.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Controller\DashboardController;

// Create controller instance
$dashboardController = new DashboardController();

// Show dashboard
$dashboardController->showDashboard();
?>