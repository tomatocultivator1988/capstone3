<?php

namespace App\Controller;

use App\Service\Interface\UserServiceInterface;
use App\Service\Interface\AuthServiceInterface;

/**
 * UserController
 * 
 * Handles user-related HTTP requests.
 * Responsible for CRUD operations on users.
 */
class UserController
{
    private UserServiceInterface $userService;
    private AuthServiceInterface $authService;

    public function __construct(UserServiceInterface $userService, AuthServiceInterface $authService)
    {
        $this->userService = $userService;
        $this->authService = $authService;
    }

    /**
     * Get all users (Admin only)
     */
    public function index(): void
    {
        header('Content-Type: application/json');
        
        try {
            $this->authService->requireRole('admin');
            
            $users = $this->userService->getAllUsers();
            
            echo json_encode([
                'status' => 'success',
                'data' => $users
            ]);
        } catch (\Exception $e) {
            error_log("UserController::index error: " . $e->getMessage());
            http_response_code($e->getCode() ?: 500);
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage() ?: 'Failed to fetch users.'
            ]);
        }
    }

    /**
     * Get user by ID
     */
    public function show(string $id): void
    {
        header('Content-Type: application/json');
        
        try {
            $this->authService->requireAuth();
            
            $user = $this->userService->getUserById((int)$id);
            
            if ($user) {
                echo json_encode([
                    'status' => 'success',
                    'data' => $user
                ]);
            } else {
                http_response_code(404);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'User not found.'
                ]);
            }
        } catch (\Exception $e) {
            error_log("UserController::show error: " . $e->getMessage());
            http_response_code($e->getCode() ?: 500);
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage() ?: 'Failed to fetch user.'
            ]);
        }
    }

    /**
     * Create new user
     */
    public function store(): void
    {
        header('Content-Type: application/json');
        
        try {
            $this->authService->requireRole('admin');
            
            // Get POST data
            $school_id = $_POST['school_id'] ?? '';
            $full_name = $_POST['full_name'] ?? '';
            $role = $_POST['role'] ?? '';
            $year_level = !empty($_POST['year_level']) ? (int)$_POST['year_level'] : null;
            $section = $_POST['section'] ?? null;

            // Validate required fields
            if (empty($school_id) || empty($full_name) || empty($role)) {
                http_response_code(400);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'School ID, full name, and role are required.'
                ]);
                return;
            }

            $result = $this->userService->createUser($school_id, $full_name, $role, $year_level, $section);
            
            if ($result) {
                http_response_code(201);
                echo json_encode([
                    'status' => 'success',
                    'message' => 'User created successfully.'
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Failed to create user.'
                ]);
            }
        } catch (\Exception $e) {
            error_log("UserController::store error: " . $e->getMessage());
            http_response_code($e->getCode() ?: 500);
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage() ?: 'Failed to create user.'
            ]);
        }
    }

    /**
     * Update user
     */
    public function update(string $id): void
    {
        header('Content-Type: application/json');
        
        try {
            $this->authService->requireRole('admin');
            
            // Get POST data
            $school_id = $_POST['school_id'] ?? '';
            $full_name = $_POST['full_name'] ?? '';
            $role = $_POST['role'] ?? '';
            $year_level = !empty($_POST['year_level']) ? (int)$_POST['year_level'] : null;
            $section = $_POST['section'] ?? null;

            // Validate required fields
            if (empty($school_id) || empty($full_name) || empty($role)) {
                http_response_code(400);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'School ID, full name, and role are required.'
                ]);
                return;
            }

            $result = $this->userService->updateUser((int)$id, $school_id, $full_name, $role, $year_level, $section);
            
            if ($result) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'User updated successfully.'
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Failed to update user.'
                ]);
            }
        } catch (\Exception $e) {
            error_log("UserController::update error: " . $e->getMessage());
            http_response_code($e->getCode() ?: 500);
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage() ?: 'Failed to update user.'
            ]);
        }
    }

    /**
     * Delete user
     */
    public function delete(string $id): void
    {
        header('Content-Type: application/json');
        
        try {
            $this->authService->requireRole('admin');
            
            $result = $this->userService->deleteUser((int)$id);
            
            if ($result) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'User deleted successfully.'
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Failed to delete user.'
                ]);
            }
        } catch (\Exception $e) {
            error_log("UserController::delete error: " . $e->getMessage());
            http_response_code($e->getCode() ?: 500);
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage() ?: 'Failed to delete user.'
            ]);
        }
    }

    /**
     * Get users by role
     */
    public function getUsersByRole(string $role): void
    {
        header('Content-Type: application/json');
        
        try {
            $this->authService->requireAuth();
            
            if (empty($role)) {
                http_response_code(400);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Role parameter is required.'
                ]);
                return;
            }

            $users = $this->userService->getUsersByRole($role);
            
            echo json_encode([
                'status' => 'success',
                'data' => $users,
                'count' => count($users)
            ]);
        } catch (\Exception $e) {
            error_log("UserController::getUsersByRole error: " . $e->getMessage());
            http_response_code($e->getCode() ?: 500);
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage() ?: 'Failed to fetch users.'
            ]);
        }
    }

    /**
     * Get students by year and section
     */
    public function getStudentsByYearSection(string $year_level, string $section): void
    {
        header('Content-Type: application/json');
        
        try {
            $this->authService->requireAuth();
            
            if (empty($year_level) || empty($section)) {
                http_response_code(400);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Year level and section parameters are required.'
                ]);
                return;
            }

            $users = $this->userService->getStudentsByYearSection((int)$year_level, $section);
            
            echo json_encode([
                'status' => 'success',
                'data' => $users,
                'count' => count($users)
            ]);
        } catch (\Exception $e) {
            error_log("UserController::getStudentsByYearSection error: " . $e->getMessage());
            http_response_code($e->getCode() ?: 500);
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage() ?: 'Failed to fetch students.'
            ]);
        }
    }
}