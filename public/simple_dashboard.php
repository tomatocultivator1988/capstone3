<?php
/**
 * Simple Dashboard Page (Bypasses Router for testing)
 * 
 * This is a temporary solution to test dashboard functionality
 * while we fix the Router autoloading issue.
 */

// Error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load autoloader
require_once __DIR__ . '/../vendor/autoload.php';

use Service\ServiceContainer;
use App\Core\View;

// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: simple_login.php");
    exit;
}

$role = $_SESSION['role'] ?? 'student';
$userName = $_SESSION['full_name'] ?? 'User';

// Display appropriate dashboard based on role
$view = new View();

switch ($role) {
    case 'admin':
        $view->display('dashboard.admin', [
            'title' => 'Admin Dashboard - Exam Management System',
            'layout' => 'main',
            'showHeader' => true,
            'showLogout' => true,
            'headerTitle' => 'Admin Dashboard',
            'headerSubtitle' => "Welcome back, $userName",
            'userName' => $userName,
            'role' => $role
        ]);
        break;
        
    case 'faculty':
        $view->display('dashboard.faculty', [
            'title' => 'Faculty Dashboard - Exam Management System',
            'layout' => 'main',
            'showHeader' => true,
            'showLogout' => true,
            'headerTitle' => 'Faculty Dashboard',
            'headerSubtitle' => "Welcome back, $userName",
            'userName' => $userName,
            'role' => $role
        ]);
        break;
        
    case 'student':
    default:
        $view->display('dashboard.student', [
            'title' => 'Student Dashboard - Exam Management System',
            'layout' => 'main',
            'showHeader' => true,
            'showLogout' => true,
            'headerTitle' => 'Student Dashboard',
            'headerSubtitle' => "Welcome back, $userName",
            'userName' => $userName,
            'role' => $role
        ]);
        break;
}
?>