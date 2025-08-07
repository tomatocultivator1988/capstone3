<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Router;
use App\Core\View;

// Start session
session_start();

// Create router instance
$router = new Router();

// Define routes
$router->get('/', function() {
    // Redirect to login page
    header("Location: login_mvc.php");
    exit;
});

$router->get('/login', function() {
    // If user is already logged in, show welcome message
    if (isset($_SESSION['user_id'])) {
        $role = $_SESSION['role'] ?? 'student';
        echo "<h1>Welcome! You are logged in as: " . htmlspecialchars($role) . "</h1>";
        echo "<p><a href='../api/auth/logout.php'>Logout</a></p>";
        exit;
    }
    
    // Show login page
    $view = new View();
    $view->display('auth.login', [
        'title' => 'Login - Examination System'
    ]);
});

// Handle the request
$router->handleRequest();
?>