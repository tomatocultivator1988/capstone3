<?php
/**
 * Simple Login Page (Bypasses Router for testing)
 * 
 * This is a temporary solution to test login functionality
 * while we fix the Router autoloading issue.
 */

// Error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Check if required classes exist
if (!class_exists('Service\ServiceContainer')) {
    die("❌ Error: ServiceContainer not found. Please run 'composer dump-autoload'\n");
}

use Service\ServiceContainer;
use App\Core\View;

// Start session
session_start();

// If user is already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['role'] ?? 'student';
    header("Location: simple_dashboard.php?role=" . $role);
    exit;
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $serviceContainer = ServiceContainer::getInstance();
        $authService = $serviceContainer->getAuthService();
        
        $school_id = $_POST['school_id'] ?? '';
        $password = $_POST['password'] ?? '';
        
        if (empty($school_id) || empty($password)) {
            $error = "School ID and password are required";
        } else {
            $user = $authService->login($school_id, $password);
            
            if ($user) {
                // Login successful
                header("Location: simple_dashboard.php?role=" . $user['role']);
                exit;
            } else {
                $error = "Invalid school ID or password";
            }
        }
    } catch (Exception $e) {
        $error = "An error occurred: " . $e->getMessage();
    }
}

// Display login page
$view = new View();
$view->display('auth.login', [
    'title' => 'Login - Exam Management System',
    'layout' => 'main',
    'error' => $error ?? null
]);
?>