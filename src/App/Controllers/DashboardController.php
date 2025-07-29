<?php

namespace App\Controllers;

use App\Controllers\AuthController;
use App\Core\View;

class DashboardController
{
    private $authController;
    private $view;

    public function __construct()
    {
        $this->authController = new AuthController();
        $this->view = new View();
    }

    /**
     * Show admin dashboard
     */
    public function admin()
    {
        $this->authController->requireRole('admin');
        
        $this->view->display('dashboard.admin', [
            'title' => 'Admin Dashboard - Examination System',
            'layout' => 'main',
            'showHeader' => true,
            'showLogout' => true,
            'headerTitle' => 'Admin Dashboard',
            'headerSubtitle' => 'Manage users, subjects, and exams'
        ]);
    }

    /**
     * Show faculty dashboard
     */
    public function faculty()
    {
        $this->authController->requireRole('faculty');
        
        $this->view->display('dashboard.faculty', [
            'title' => 'Faculty Dashboard - Examination System',
            'layout' => 'main',
            'showHeader' => true,
            'showLogout' => true,
            'headerTitle' => 'Faculty Dashboard',
            'headerSubtitle' => 'Manage your subjects and exams'
        ]);
    }

    /**
     * Show student dashboard
     */
    public function student()
    {
        $this->authController->requireRole('student');
        
        $this->view->display('dashboard.student', [
            'title' => 'Student Dashboard - Examination System',
            'layout' => 'main',
            'showHeader' => true,
            'showLogout' => true,
            'headerTitle' => 'Student Dashboard',
            'headerSubtitle' => 'View available exams and results'
        ]);
    }

    /**
     * Route to appropriate dashboard based on user role
     */
    public function index()
    {
        $this->authController->requireAuth();
        
        $user_role = $_SESSION['user_role'] ?? null;
        
        switch ($user_role) {
            case 'admin':
                $this->admin();
                break;
            case 'faculty':
                $this->faculty();
                break;
            case 'student':
                $this->student();
                break;
            default:
                // Redirect to login if no valid role
                header('Location: /login');
                exit;
        }
    }
}