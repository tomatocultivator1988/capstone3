<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\View;

// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login_mvc.php");
    exit;
}

$role = $_SESSION['role'] ?? 'student';
$userName = $_SESSION['full_name'] ?? 'User';

// Create view instance
$view = new View();

// Display appropriate dashboard based on role
switch ($role) {
    case 'admin':
        $view->display('dashboard.admin', [
            'title' => 'Admin Dashboard - Examination System',
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
            'title' => 'Faculty Dashboard - Examination System',
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
            'title' => 'Student Dashboard - Examination System',
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