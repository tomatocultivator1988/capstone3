<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\View;

// Start session
session_start();

// If user is already logged in, redirect to appropriate dashboard
if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['role'] ?? 'student';
    // For now, just show a simple welcome message since dashboard files were removed
    echo "<h1>Welcome! You are logged in as: " . htmlspecialchars($role) . "</h1>";
    echo "<p><a href='../api/auth/logout.php'>Logout</a></p>";
    exit;
}

// Create view instance
$view = new View();

// Display login page
$view->display('auth.login', [
    'title' => 'Login - Examination System'
]);
?>