<?php

namespace App\Controller;

use Service\ServiceContainer;
use App\Core\View;

class AuthController
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
     * Display login page
     */
    public function showLogin()
    {
        // Start session
        session_start();

        // If user is already logged in, redirect to dashboard
        if (isset($_SESSION['user_id'])) {
            $role = $_SESSION['role'] ?? 'student';
            header("Location: /dashboard?role=" . $role);
            exit;
        }

        // Display login page
        $this->view->display('auth.login', [
            'title' => 'Login - Exam Management System',
            'layout' => 'main'
        ]);
    }

    /**
     * Handle login form submission
     */
    public function login()
    {
        // Set headers for JSON response
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST');
        header('Access-Control-Allow-Headers: Content-Type');

        // Only accept POST requests
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
            return;
        }

        try {
            // Get POST data
            $school_id = $_POST['school_id'] ?? '';
            $password = $_POST['password'] ?? '';

            // Validate input
            if (empty($school_id) || empty($password)) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'School ID and password are required'
                ]);
                return;
            }

            // Use Service for authentication (Controller doesn't know about DAOs)
            $user = $this->authService->login($school_id, $password);

            if ($user) {
                // Login successful
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Login successful',
                    'role' => $user['role'],
                    'user' => [
                        'school_id' => $user['school_id'],
                        'full_name' => $user['full_name'],
                        'role' => $user['role']
                    ]
                ]);
            } else {
                // Login failed
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Invalid school ID or password'
                ]);
            }

        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            echo json_encode([
                'status' => 'error',
                'message' => 'An error occurred during login'
            ]);
        }
    }

    /**
     * Handle logout
     */
    public function logout()
    {
        // Start session
        session_start();

        // Use Service for logout (Controller doesn't know about DAOs)
        $this->authService->destroySession();

        // Redirect to login page
        header("Location: /login");
        exit;
    }

    /**
     * Check if user is authenticated
     */
    public function requireAuth()
    {
        session_start();
        if (!$this->authService->isAuthenticated()) {
            header("Location: /login");
            exit;
        }
    }

    /**
     * Require specific role
     */
    public function requireRole($required_role)
    {
        $this->requireAuth();
        
        if (!$this->authService->hasRole($required_role)) {
            http_response_code(403);
            echo json_encode([
                'status' => 'error',
                'message' => 'Insufficient permissions'
            ]);
            exit;
        }
    }
}
?>