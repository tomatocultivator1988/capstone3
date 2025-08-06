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
     * Display login page (GET request)
     */
    public function showLogin()
    {
        // Start session
        session_start();

        // If user is already logged in, redirect to dashboard
        if (isset($_SESSION['user_id'])) {
            $role = $_SESSION['role'] ?? 'student';
            $this->redirectToDashboard($role);
            return;
        }

        // Controller decides what data to pass to the view
        $viewData = [
            'title' => 'Login - Exam Management System',
            'layout' => 'main',
            'error' => null,
            'success' => null
        ];

        // Controller controls the view - passes data and tells it what to display
        $this->view->display('auth.login', $viewData);
    }

    /**
     * Handle login form submission (POST request)
     */
    public function login()
    {
        // Start session
        session_start();

        // Controller handles the HTTP request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirectToLogin('Invalid request method');
            return;
        }

        // Controller gets data from the request
        $school_id = $_POST['school_id'] ?? '';
        $password = $_POST['password'] ?? '';

        // Controller validates input
        if (empty($school_id) || empty($password)) {
            $this->showLoginWithError('School ID and password are required');
            return;
        }

        try {
            // Controller uses Service for business logic
            $user = $this->authService->login($school_id, $password);

            if ($user) {
                // Controller decides what to do on success
                $this->redirectToDashboard($user['role']);
            } else {
                // Controller decides what to do on failure
                $this->showLoginWithError('Invalid school ID or password');
            }

        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            $this->showLoginWithError('An error occurred during login');
        }
    }

    /**
     * Handle logout
     */
    public function logout()
    {
        // Start session
        session_start();

        // Controller uses Service for logout
        $this->authService->destroySession();

        // Controller decides where to redirect
        $this->redirectToLogin('You have been logged out successfully');
    }

    /**
     * API login endpoint (for AJAX requests)
     */
    public function apiLogin()
    {
        // Set headers for JSON response
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST');
        header('Access-Control-Allow-Headers: Content-Type');

        // Controller handles API request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
            return;
        }

        try {
            $school_id = $_POST['school_id'] ?? '';
            $password = $_POST['password'] ?? '';

            if (empty($school_id) || empty($password)) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'School ID and password are required'
                ]);
                return;
            }

            // Controller uses Service for business logic
            $user = $this->authService->login($school_id, $password);

            if ($user) {
                // Controller decides what JSON to return
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
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Invalid school ID or password'
                ]);
            }

        } catch (Exception $e) {
            error_log("API Login error: " . $e->getMessage());
            echo json_encode([
                'status' => 'error',
                'message' => 'An error occurred during login'
            ]);
        }
    }

    /**
     * Check if user is authenticated (middleware)
     */
    public function requireAuth()
    {
        session_start();
        if (!$this->authService->isAuthenticated()) {
            $this->redirectToLogin('Please log in to continue');
        }
    }

    /**
     * Require specific role (middleware)
     */
    public function requireRole($required_role)
    {
        $this->requireAuth();
        
        if (!$this->authService->hasRole($required_role)) {
            http_response_code(403);
            $this->view->json([
                'status' => 'error',
                'message' => 'Insufficient permissions'
            ]);
            exit;
        }
    }

    /**
     * Controller helper methods for controlling flow
     */
    private function redirectToDashboard($role)
    {
        header("Location: /dashboard?role=" . $role);
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

    private function showLoginWithError($error)
    {
        $viewData = [
            'title' => 'Login - Exam Management System',
            'layout' => 'main',
            'error' => $error,
            'success' => null
        ];

        // Controller controls the view - tells it to show error
        $this->view->display('auth.login', $viewData);
    }
}
?>