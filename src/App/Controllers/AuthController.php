<?php

namespace App\Controllers;

use App\Models\User;

class AuthController
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new User();
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

            // Validate inputs
            if (empty($school_id) || empty($password)) {
                http_response_code(400);
                echo json_encode([
                    'status' => 'fail',
                    'message' => 'Both School ID and password are required.'
                ]);
                return;
            }

            // Authenticate user
            $user = $this->userModel->authenticate($school_id, $password);

            if ($user) {
                // Start session
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['school_id'] = $user['school_id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role'];

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
                        'role' => $user['role']
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
        if (!$this->checkAuth()) {
            http_response_code(401);
            echo json_encode([
                'status' => 'error',
                'message' => 'Authentication required.'
            ]);
            exit;
        }
    }

    /**
     * Require specific role
     */
    public function requireRole($requiredRole)
    {
        $this->requireAuth();
        
        $user = $this->getCurrentUser();
        if ($user['role'] !== $requiredRole) {
            http_response_code(403);
            echo json_encode([
                'status' => 'error',
                'message' => 'Insufficient permissions.'
            ]);
            exit;
        }
    }
}