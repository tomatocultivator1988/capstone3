<?php

namespace App\Controller;

use Service\ServiceContainer;
use App\Core\View;

class DashboardController
{
    private $authService;
    private $userService;
    private $view;

    public function __construct()
    {
        // Controllers use ServiceContainer to get Services
        $serviceContainer = ServiceContainer::getInstance();
        $this->authService = $serviceContainer->getAuthService();
        $this->userService = $serviceContainer->getUserService();
        $this->view = new View();
    }

    /**
     * Display dashboard based on user role
     */
    public function showDashboard()
    {
        // Check authentication using Service
        $this->requireAuth();

        $role = $_SESSION['role'] ?? 'student';
        $userName = $_SESSION['full_name'] ?? 'User';

        // Display appropriate dashboard based on role
        switch ($role) {
            case 'admin':
                $this->showAdminDashboard($userName, $role);
                break;
                
            case 'faculty':
                $this->showFacultyDashboard($userName, $role);
                break;
                
            case 'student':
            default:
                $this->showStudentDashboard($userName, $role);
                break;
        }
    }

    /**
     * Show admin dashboard
     */
    private function showAdminDashboard($userName, $role)
    {
        // Get dashboard data using Services
        $userStats = $this->userService->getUserStatistics();
        
        $this->view->display('dashboard.admin', [
            'title' => 'Admin Dashboard - Exam Management System',
            'layout' => 'main',
            'showHeader' => true,
            'showLogout' => true,
            'headerTitle' => 'Admin Dashboard',
            'headerSubtitle' => "Welcome back, $userName",
            'userName' => $userName,
            'role' => $role,
            'userStats' => $userStats
        ]);
    }

    /**
     * Show faculty dashboard
     */
    private function showFacultyDashboard($userName, $role)
    {
        $this->view->display('dashboard.faculty', [
            'title' => 'Faculty Dashboard - Exam Management System',
            'layout' => 'main',
            'showHeader' => true,
            'showLogout' => true,
            'headerTitle' => 'Faculty Dashboard',
            'headerSubtitle' => "Welcome back, $userName",
            'userName' => $userName,
            'role' => $role
        ]);
    }

    /**
     * Show student dashboard
     */
    private function showStudentDashboard($userName, $role)
    {
        $this->view->display('dashboard.student', [
            'title' => 'Student Dashboard - Exam Management System',
            'layout' => 'main',
            'showHeader' => true,
            'showLogout' => true,
            'headerTitle' => 'Student Dashboard',
            'headerSubtitle' => "Welcome back, $userName",
            'userName' => $userName,
            'role' => $role
        ]);
    }

    /**
     * Check if user is authenticated using Service
     */
    private function requireAuth()
    {
        session_start();
        if (!$this->authService->isAuthenticated()) {
            header("Location: /login");
            exit;
        }
    }
}
?>