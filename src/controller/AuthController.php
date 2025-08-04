<?php

namespace App\Controller;

use App\Service\Interface\AuthServiceInterface;

/**
 * AuthController
 * 
 * Handles authentication-related HTTP requests.
 * Responsible for login, logout, and authentication checks.
 */
class AuthController
{
    private AuthServiceInterface $authService;

    public function __construct(AuthServiceInterface $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Handle login request
     */
    public function login(): void
    {
        // Set headers for JSON response
        header('Content-Type: application/json');

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

            // Authenticate user using AuthService
            $user = $this->authService->login($school_id, $password);

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
        } catch (\Exception $e) {
            // Handle any unexpected errors
            error_log("AuthController::login error: " . $e->getMessage());
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
    public function logout(): void
    {
        // Set headers for JSON response
        header('Content-Type: application/json');

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
            // Logout user using AuthService
            $result = $this->authService->logout();

            if ($result) {
                // Return successful logout response
                http_response_code(200);
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Logout successful!'
                ]);
            } else {
                // Return error message
                http_response_code(500);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'An error occurred during logout.'
                ]);
            }
        } catch (\Exception $e) {
            // Handle any unexpected errors
            error_log("AuthController::logout error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'An error occurred during logout.'
            ]);
        }
    }
}