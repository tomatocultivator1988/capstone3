<?php

namespace Controller;

use Service\Interface\UserServiceInterface;
use Service\Interface\AuthServiceInterface;
use Config\ServiceContainer;
use Exception;

/**
 * User Controller
 * 
 * Handles HTTP requests for user management operations.
 * Uses UserService for business logic.
 */
class UserController
{
    private UserServiceInterface $userService;
    private AuthServiceInterface $authService;

    public function __construct(?UserServiceInterface $userService = null, ?AuthServiceInterface $authService = null)
    {
        $container = ServiceContainer::getInstance();
        $this->userService = $userService ?? $container->get(UserServiceInterface::class);
        $this->authService = $authService ?? $container->get(AuthServiceInterface::class);
    }

    /**
     * Get all users
     */
    public function index(): void
    {
        $this->setJsonHeaders();

        try {
            // Check authentication and authorization
            if (!$this->authService->isAuthenticated()) {
                $this->sendErrorResponse('Authentication required.', 401);
                return;
            }

            if (!$this->authService->hasPermission('manage_users')) {
                $this->sendErrorResponse('Insufficient permissions.', 403);
                return;
            }

            $role = $_GET['role'] ?? null;
            $users = $this->userService->getAllUsers($role);

            // Convert users to arrays (remove sensitive data like passwords)
            $usersData = array_map(function($user) {
                $data = $user->toArray();
                unset($data['password']); // Remove password from response
                return $data;
            }, $users);

            $this->sendSuccessResponse('Users retrieved successfully.', $usersData);

        } catch (Exception $e) {
            error_log("UserController::index error: " . $e->getMessage());
            $this->sendErrorResponse('Failed to retrieve users.', 500);
        }
    }

    /**
     * Get user by ID
     */
    public function show(): void
    {
        $this->setJsonHeaders();

        try {
            // Check authentication
            if (!$this->authService->isAuthenticated()) {
                $this->sendErrorResponse('Authentication required.', 401);
                return;
            }

            $userId = (int) ($_GET['id'] ?? 0);
            if ($userId <= 0) {
                $this->sendErrorResponse('Invalid user ID.', 400);
                return;
            }

            $user = $this->userService->getUserById($userId);
            if (!$user) {
                $this->sendErrorResponse('User not found.', 404);
                return;
            }

            // Check if user can view this user data
            $currentUser = $this->authService->getCurrentUser();
            if (!$this->canViewUser($currentUser, $user)) {
                $this->sendErrorResponse('Insufficient permissions.', 403);
                return;
            }

            $userData = $user->toArray();
            unset($userData['password']); // Remove password from response

            $this->sendSuccessResponse('User retrieved successfully.', $userData);

        } catch (Exception $e) {
            error_log("UserController::show error: " . $e->getMessage());
            $this->sendErrorResponse('Failed to retrieve user.', 500);
        }
    }

    /**
     * Create new user
     */
    public function create(): void
    {
        $this->setJsonHeaders();

        if (!$this->isPostRequest()) {
            $this->sendErrorResponse('Invalid request method. Only POST allowed.', 405);
            return;
        }

        try {
            // Check authentication and authorization
            if (!$this->authService->isAuthenticated()) {
                $this->sendErrorResponse('Authentication required.', 401);
                return;
            }

            if (!$this->authService->hasPermission('create_user')) {
                $this->sendErrorResponse('Insufficient permissions.', 403);
                return;
            }

            // Get POST data
            $school_id = $_POST['school_id'] ?? '';
            $full_name = $_POST['full_name'] ?? '';
            $role = $_POST['role'] ?? '';
            $year_level = !empty($_POST['year_level']) ? (int) $_POST['year_level'] : null;
            $section = $_POST['section'] ?? null;

            // Create user using service
            $result = $this->userService->createUser($school_id, $full_name, $role, $year_level, $section);

            if ($result) {
                $this->sendSuccessResponse('User created successfully.');
            } else {
                $this->sendErrorResponse('Failed to create user. Please check the provided data.', 400);
            }

        } catch (Exception $e) {
            error_log("UserController::create error: " . $e->getMessage());
            $this->sendErrorResponse('An error occurred while creating user.', 500);
        }
    }

    /**
     * Update user
     */
    public function update(): void
    {
        $this->setJsonHeaders();

        if (!$this->isPostRequest()) {
            $this->sendErrorResponse('Invalid request method. Only POST allowed.', 405);
            return;
        }

        try {
            // Check authentication and authorization
            if (!$this->authService->isAuthenticated()) {
                $this->sendErrorResponse('Authentication required.', 401);
                return;
            }

            $userId = (int) ($_POST['user_id'] ?? 0);
            if ($userId <= 0) {
                $this->sendErrorResponse('Invalid user ID.', 400);
                return;
            }

            // Check if user can update this user
            $currentUser = $this->authService->getCurrentUser();
            $targetUser = $this->userService->getUserById($userId);
            
            if (!$targetUser) {
                $this->sendErrorResponse('User not found.', 404);
                return;
            }

            if (!$this->canEditUser($currentUser, $targetUser)) {
                $this->sendErrorResponse('Insufficient permissions.', 403);
                return;
            }

            // Get POST data
            $school_id = $_POST['school_id'] ?? '';
            $full_name = $_POST['full_name'] ?? '';
            $role = $_POST['role'] ?? '';
            $year_level = !empty($_POST['year_level']) ? (int) $_POST['year_level'] : null;
            $section = $_POST['section'] ?? null;

            // Update user using service
            $result = $this->userService->updateUser($userId, $school_id, $full_name, $role, $year_level, $section);

            if ($result) {
                $this->sendSuccessResponse('User updated successfully.');
            } else {
                $this->sendErrorResponse('Failed to update user. Please check the provided data.', 400);
            }

        } catch (Exception $e) {
            error_log("UserController::update error: " . $e->getMessage());
            $this->sendErrorResponse('An error occurred while updating user.', 500);
        }
    }

    /**
     * Delete user
     */
    public function delete(): void
    {
        $this->setJsonHeaders();

        if (!$this->isPostRequest()) {
            $this->sendErrorResponse('Invalid request method. Only POST allowed.', 405);
            return;
        }

        try {
            // Check authentication and authorization
            if (!$this->authService->isAuthenticated()) {
                $this->sendErrorResponse('Authentication required.', 401);
                return;
            }

            if (!$this->authService->hasPermission('delete_user')) {
                $this->sendErrorResponse('Insufficient permissions.', 403);
                return;
            }

            $userId = (int) ($_POST['user_id'] ?? 0);
            if ($userId <= 0) {
                $this->sendErrorResponse('Invalid user ID.', 400);
                return;
            }

            // Delete user using service
            $result = $this->userService->deleteUser($userId);

            if ($result) {
                $this->sendSuccessResponse('User deleted successfully.');
            } else {
                $this->sendErrorResponse('Failed to delete user.', 400);
            }

        } catch (Exception $e) {
            error_log("UserController::delete error: " . $e->getMessage());
            $this->sendErrorResponse('An error occurred while deleting user.', 500);
        }
    }

    /**
     * Get user statistics
     */
    public function statistics(): void
    {
        $this->setJsonHeaders();

        try {
            // Check authentication and authorization
            if (!$this->authService->isAuthenticated()) {
                $this->sendErrorResponse('Authentication required.', 401);
                return;
            }

            if (!$this->authService->hasRole('admin')) {
                $this->sendErrorResponse('Insufficient permissions.', 403);
                return;
            }

            $statistics = $this->userService->getUserStatistics();
            $this->sendSuccessResponse('User statistics retrieved successfully.', $statistics);

        } catch (Exception $e) {
            error_log("UserController::statistics error: " . $e->getMessage());
            $this->sendErrorResponse('Failed to retrieve user statistics.', 500);
        }
    }

    // Helper methods

    private function canViewUser($currentUser, $targetUser): bool
    {
        if (!$currentUser || !$targetUser) {
            return false;
        }

        // Admin can view all users
        if ($currentUser->isAdmin()) {
            return true;
        }

        // Users can view their own profile
        if ($currentUser->getUserId() === $targetUser->getUserId()) {
            return true;
        }

        // Faculty can view students in their classes (you'd need to implement this logic)
        if ($currentUser->isFaculty() && $targetUser->isStudent()) {
            return true; // Simplified for now
        }

        return false;
    }

    private function canEditUser($currentUser, $targetUser): bool
    {
        if (!$currentUser || !$targetUser) {
            return false;
        }

        // Admin can edit all users (except other admins in some cases)
        if ($currentUser->isAdmin()) {
            return true;
        }

        // Users can edit their own profile (limited fields)
        if ($currentUser->getUserId() === $targetUser->getUserId()) {
            return true; // You might want to limit which fields can be edited
        }

        return false;
    }

    private function setJsonHeaders(): void
    {
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');

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