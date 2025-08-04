<?php

namespace Controller;

use Service\Interface\AuthServiceInterface;
use Config\ServiceContainer;
use Exception;

/**
 * Authentication Controller
 * 
 * Handles HTTP requests for authentication operations.
 * Uses AuthService for business logic.
 */
class AuthController
{
    private AuthServiceInterface $authService;

    public function __construct(?AuthServiceInterface $authService = null)
    {
        $this->authService = $authService ?? ServiceContainer::getInstance()->get(AuthServiceInterface::class);
    }

    /**
     * Handle login request
     */
    public function login(): void
    {
        $this->setJsonHeaders();

        if (!$this->isPostRequest()) {
            $this->sendErrorResponse('Invalid request method. Only POST allowed.', 405);
            return;
        }

        try {
            // Get POST data
            $school_id = $_POST['school_id'] ?? '';
            $password = $_POST['password'] ?? '';

            // Validate inputs
            if (empty($school_id) || empty($password)) {
                $this->sendErrorResponse('Both School ID and password are required.', 400);
                return;
            }

            // Authenticate user using AuthService
            $user = $this->authService->login($school_id, $password);

            if ($user) {
                $this->sendSuccessResponse('Login successful!', $user);
            } else {
                $this->sendErrorResponse('Invalid credentials. Please check your School ID and password.', 401);
            }
        } catch (Exception $e) {
            error_log("AuthController::login error: " . $e->getMessage());
            $this->sendErrorResponse('An error occurred during login. Please try again.', 500);
        }
    }

    /**
     * Handle logout request
     */
    public function logout(): void
    {
        $this->setJsonHeaders();

        try {
            $result = $this->authService->logout();

            if ($result) {
                $this->sendSuccessResponse('Logout successful!');
            } else {
                $this->sendErrorResponse('Failed to logout properly.', 500);
            }
        } catch (Exception $e) {
            error_log("AuthController::logout error: " . $e->getMessage());
            $this->sendErrorResponse('An error occurred during logout.', 500);
        }
    }

    /**
     * Check authentication status
     */
    public function checkAuth(): void
    {
        $this->setJsonHeaders();

        try {
            $isAuthenticated = $this->authService->isAuthenticated();
            $user = $this->authService->getCurrentUser();

            if ($isAuthenticated && $user) {
                $this->sendSuccessResponse('User is authenticated', [
                    'user_id' => $user->getUserId(),
                    'school_id' => $user->getSchoolId(),
                    'full_name' => $user->getFullName(),
                    'role' => $user->getRole(),
                    'year_level' => $user->getYearLevel(),
                    'section' => $user->getSection()
                ]);
            } else {
                $this->sendErrorResponse('User is not authenticated.', 401);
            }
        } catch (Exception $e) {
            error_log("AuthController::checkAuth error: " . $e->getMessage());
            $this->sendErrorResponse('Failed to check authentication status.', 500);
        }
    }

    /**
     * Handle password change request
     */
    public function changePassword(): void
    {
        $this->setJsonHeaders();

        if (!$this->isPostRequest()) {
            $this->sendErrorResponse('Invalid request method. Only POST allowed.', 405);
            return;
        }

        try {
            // Check if user is authenticated
            if (!$this->authService->isAuthenticated()) {
                $this->sendErrorResponse('User must be logged in to change password.', 401);
                return;
            }

            // Get POST data
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            // Validate inputs
            if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
                $this->sendErrorResponse('All password fields are required.', 400);
                return;
            }

            if ($newPassword !== $confirmPassword) {
                $this->sendErrorResponse('New password and confirmation do not match.', 400);
                return;
            }

            $currentUser = $this->authService->getCurrentUser();
            if (!$currentUser) {
                $this->sendErrorResponse('Unable to get current user information.', 500);
                return;
            }

            // Note: This would require a UserService method, but for now we'll use a placeholder
            $this->sendSuccessResponse('Password change functionality needs to be implemented.');

        } catch (Exception $e) {
            error_log("AuthController::changePassword error: " . $e->getMessage());
            $this->sendErrorResponse('An error occurred while changing password.', 500);
        }
    }

    // Helper methods

    private function setJsonHeaders(): void
    {
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');

        // Handle preflight OPTIONS request
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
    }

    private function isPostRequest(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    private function sendSuccessResponse(string $message, $data = null): void
    {
        http_response_code(200);
        $response = [
            'status' => 'success',
            'message' => $message
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        echo json_encode($response);
    }

    private function sendErrorResponse(string $message, int $statusCode = 400): void
    {
        http_response_code($statusCode);
        echo json_encode([
            'status' => 'error',
            'message' => $message
        ]);
    }
}