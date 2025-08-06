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
     * Display dashboard based on user role (GET request)
     */
    public function showDashboard()
    {
        // Controller checks authentication
        $this->requireAuth();

        // Controller gets user data from session
        $role = $_SESSION['role'] ?? 'student';
        $userName = $_SESSION['full_name'] ?? 'User';

        // Controller decides which dashboard to show based on role
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
     * Show admin dashboard (Controller controls the view)
     */
    private function showAdminDashboard($userName, $role)
    {
        // Controller gets data from Services
        $userStats = $this->userService->getUserStatistics();
        
        // Controller decides what data to pass to the view
        $viewData = [
            'title' => 'Admin Dashboard - Exam Management System',
            'layout' => 'main',
            'showHeader' => true,
            'showLogout' => true,
            'headerTitle' => 'Admin Dashboard',
            'headerSubtitle' => "Welcome back, $userName",
            'userName' => $userName,
            'role' => $role,
            'userStats' => $userStats
        ];

        // Controller controls the view - tells it what to display
        $this->view->display('dashboard.admin', $viewData);
    }

    /**
     * Show faculty dashboard (Controller controls the view)
     */
    private function showFacultyDashboard($userName, $role)
    {
        // Controller decides what data to pass to the view
        $viewData = [
            'title' => 'Faculty Dashboard - Exam Management System',
            'layout' => 'main',
            'showHeader' => true,
            'showLogout' => true,
            'headerTitle' => 'Faculty Dashboard',
            'headerSubtitle' => "Welcome back, $userName",
            'userName' => $userName,
            'role' => $role
        ];

        // Controller controls the view - tells it what to display
        $this->view->display('dashboard.faculty', $viewData);
    }

    /**
     * Show student dashboard (Controller controls the view)
     */
    private function showStudentDashboard($userName, $role)
    {
        // Controller decides what data to pass to the view
        $viewData = [
            'title' => 'Student Dashboard - Exam Management System',
            'layout' => 'main',
            'showHeader' => true,
            'showLogout' => true,
            'headerTitle' => 'Student Dashboard',
            'headerSubtitle' => "Welcome back, $userName",
            'userName' => $userName,
            'role' => $role
        ];

        // Controller controls the view - tells it what to display
        $this->view->display('dashboard.student', $viewData);
    }

    /**
     * Handle dashboard actions (POST requests)
     */
    public function handleDashboardAction()
    {
        // Controller checks authentication
        $this->requireAuth();

        // Controller gets action from request
        $action = $_POST['action'] ?? '';

        // Controller decides what to do based on action
        switch ($action) {
            case 'logout':
                $this->handleLogout();
                break;
                
            case 'refresh_stats':
                $this->refreshStats();
                break;
                
            default:
                $this->redirectToDashboard('Invalid action');
                break;
        }
    }

    /**
     * Handle logout from dashboard
     */
    private function handleLogout()
    {
        // Controller uses Service for logout
        $this->authService->destroySession();
        
        // Controller decides where to redirect
        $this->redirectToLogin('You have been logged out successfully');
    }

    /**
     * Refresh dashboard statistics (API endpoint)
     */
    public function refreshStats()
    {
        // Controller checks authentication
        $this->requireAuth();

        // Set JSON headers
        header('Content-Type: application/json');

        try {
            // Controller gets data from Services
            $userStats = $this->userService->getUserStatistics();
            
            // Controller decides what JSON to return
            echo json_encode([
                'status' => 'success',
                'data' => $userStats
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to refresh statistics'
            ]);
        }
    }

    /**
     * Check if user is authenticated (middleware)
     */
    private function requireAuth()
    {
        session_start();
        if (!$this->authService->isAuthenticated()) {
            $this->redirectToLogin('Please log in to continue');
        }
    }

    /**
     * Controller helper methods for controlling flow
     */
    private function redirectToDashboard($message = null)
    {
        $role = $_SESSION['role'] ?? 'student';
        if ($message) {
            header("Location: /dashboard?role=" . $role . "&message=" . urlencode($message));
        } else {
            header("Location: /dashboard?role=" . $role);
        }
        exit;
    }

    private function redirectToLogin($message = null)
    {
        if ($message) {
            header("Location: /login?message=" . urlencode($message));
        } else {
            header("Location: /login");
        }
        exit;
    }
}
?>