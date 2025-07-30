<?php

namespace App\Controllers;

use App\Services\AuthService;
use App\Services\ServiceContainer;
use Exception;

class AuthController
{
    private AuthService $authService;

    public function __construct(?AuthService $authService = null)
    {
        $this->authService = $authService ?? ServiceContainer::getInstance()->get(AuthService::class);
    }

    /**
     * Handle login request
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
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid request method.'
            ]);
            return;
        }

        try {
            // Get POST data
            $school_id = $_POST['school_id'] ?? '';
            $password = $_POST['password'] ?? '';

            // Debug logging
            error_log("Login attempt - School ID: $school_id, Password length: " . strlen($password));

            // Validate inputs
            if (empty($school_id) || empty($password)) {
                http_response_code(400);
                echo json_encode([
                    'status' => 'fail',
                    'message' => 'Both School ID and password are required.'
                ]);
                return;
            }

            // Authenticate user using AuthService
            $user = $this->authService->login($school_id, $password);
            
            // Debug logging
            error_log("AuthService result: " . ($user ? 'success' : 'failed'));

            if ($user) {
                // Return successful login response
                http_response_code(200);
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Login successful!',
                    'role' => $user['role'],
                    'user' => [
                        'user_id' => $user['user_id'],
                        'school_id' => $user['school_id'],
                        'full_name' => $user['full_name'],
                        'role' => $user['role'],
                        'year_level' => $user['year_level'] ?? null,
                        'section' => $user['section'] ?? null
                    ]
                ]);
            } else {
                // Return error message if credentials are incorrect
                http_response_code(401);
                echo json_encode([
                    'status' => 'fail',
                    'message' => 'Invalid School ID or password.'
                ]);
            }
        } catch (Exception $e) {
            // Handle any unexpected errors
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'An error occurred during login.'
            ]);
        }
    }

    /**
     * Handle logout request
     */
    public function logout()
    {
        header('Content-Type: application/json');

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Destroy session
        session_destroy();

        echo json_encode([
            'status' => 'success',
            'message' => 'Logged out successfully.'
        ]);
    }

    /**
     * Check if user is authenticated
     */
    public function checkAuth()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        return isset($_SESSION['user_id']);
    }

    /**
     * Get current user data
     */
    public function getCurrentUser()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!$this->checkAuth()) {
            return null;
        }

        return [
            'user_id' => $_SESSION['user_id'],
            'school_id' => $_SESSION['school_id'],
            'full_name' => $_SESSION['full_name'],
            'role' => $_SESSION['role']
        ];
    }

    /**
     * Require authentication middleware
     */
    public function requireAuth()
    {
        try {
            $this->authService->requireAuth();
        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
            exit;
        }
    }

    /**
     * Require specific role
     */
    public function requireRole($requiredRole)
    {
        try {
            $this->authService->requireRole($requiredRole);
        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
            exit;
        }
    }
}