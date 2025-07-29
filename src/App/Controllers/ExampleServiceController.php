<?php

namespace App\Controllers;

use App\Services\UserService;
use App\Services\ServiceContainer;
use Exception;

/**
 * ExampleServiceController
 * 
 * Demonstrates how to properly use services with dependency injection.
 * This controller shows best practices for service usage in controllers.
 */
class ExampleServiceController
{
    private UserService $userService;

    public function __construct(?UserService $userService = null)
    {
        // Use dependency injection if provided, otherwise get from service container
        $this->userService = $userService ?? ServiceContainer::getInstance()->get(UserService::class);
    }

    /**
     * Create a new user - Example of service usage
     */
    public function createUser()
    {
        header('Content-Type: application/json');

        try {
            // Get and validate input
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                throw new Exception('Invalid JSON input');
            }

            $school_id = $input['school_id'] ?? '';
            $full_name = $input['full_name'] ?? '';
            $role = $input['role'] ?? '';
            $year_level = $input['year_level'] ?? null;
            $section = $input['section'] ?? null;

            // Use the service to create the user
            $userId = $this->userService->createUser($school_id, $full_name, $role, $year_level, $section);

            if ($userId) {
                http_response_code(201);
                echo json_encode([
                    'status' => 'success',
                    'message' => 'User created successfully',
                    'data' => ['user_id' => $userId]
                ]);
            } else {
                throw new Exception('Failed to create user');
            }

        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Update a user - Example of service usage
     */
    public function updateUser()
    {
        header('Content-Type: application/json');

        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                throw new Exception('Invalid JSON input');
            }

            $user_id = (int)($input['user_id'] ?? 0);
            $school_id = $input['school_id'] ?? '';
            $full_name = $input['full_name'] ?? '';
            $role = $input['role'] ?? '';
            $year_level = $input['year_level'] ?? null;
            $section = $input['section'] ?? null;

            if ($user_id <= 0) {
                throw new Exception('Valid user ID is required');
            }

            // Use the service to update the user
            $success = $this->userService->updateUser($user_id, $school_id, $full_name, $role, $year_level, $section);

            if ($success) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'User updated successfully'
                ]);
            } else {
                throw new Exception('Failed to update user');
            }

        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get user by ID - Example of service usage
     */
    public function getUserById($user_id)
    {
        header('Content-Type: application/json');

        try {
            $user_id = (int)$user_id;
            
            if ($user_id <= 0) {
                throw new Exception('Valid user ID is required');
            }

            // Use the service to get the user
            $user = $this->userService->getUserById($user_id);

            if ($user) {
                echo json_encode([
                    'status' => 'success',
                    'data' => $user
                ]);
            } else {
                http_response_code(404);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'User not found'
                ]);
            }

        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Validate user data - Example of service validation usage
     */
    public function validateUserData()
    {
        header('Content-Type: application/json');

        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                throw new Exception('Invalid JSON input');
            }

            // Use the service to validate user data
            $errors = $this->userService->validateUserData($input);

            echo json_encode([
                'status' => 'success',
                'data' => [
                    'valid' => empty($errors),
                    'errors' => $errors
                ]
            ]);

        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get all users by role - Example of service usage
     */
    public function getUsersByRole($role)
    {
        header('Content-Type: application/json');

        try {
            if (empty($role)) {
                throw new Exception('Role is required');
            }

            // Use the service to get users by role
            $users = $this->userService->getUsersByRole($role);

            echo json_encode([
                'status' => 'success',
                'data' => $users,
                'count' => count($users)
            ]);

        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }
}