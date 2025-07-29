<?php

namespace App\Controllers;

use App\Models\User;
use App\Controllers\AuthController;

class UserController
{
    private $userModel;
    private $authController;

    public function __construct()
    {
        $this->userModel = new User();
        $this->authController = new AuthController();
    }

    /**
     * Get all users (Admin only)
     */
    public function index()
    {
        header('Content-Type: application/json');
        $this->authController->requireRole('admin');

        try {
            $users = $this->userModel->getAllUsers();
            
            echo json_encode([
                'status' => 'success',
                'data' => $users
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to fetch users.'
            ]);
        }
    }

    /**
     * Get users by role
     */
    public function getUsersByRole()
    {
        header('Content-Type: application/json');
        $this->authController->requireAuth();

        $role = $_GET['role'] ?? '';
        
        if (empty($role)) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Role parameter is required.'
            ]);
            return;
        }

        try {
            $users = $this->userModel->getUsersByRole($role);
            
            echo json_encode([
                'status' => 'success',
                'data' => $users
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to fetch users.'
            ]);
        }
    }

    /**
     * Get students by year and section
     */
    public function getStudentsByYearSection()
    {
        header('Content-Type: application/json');
        $this->authController->requireAuth();

        $year_level = $_GET['year_level'] ?? '';
        $section = $_GET['section'] ?? '';
        
        if (empty($year_level) || empty($section)) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Year level and section parameters are required.'
            ]);
            return;
        }

        try {
            $students = $this->userModel->getStudentsByYearSection($year_level, $section);
            
            echo json_encode([
                'status' => 'success',
                'data' => $students
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to fetch students.'
            ]);
        }
    }

    /**
     * Create new user (Admin only)
     */
    public function store()
    {
        header('Content-Type: application/json');
        $this->authController->requireRole('admin');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid request method.'
            ]);
            return;
        }

        try {
            // Validate required fields
            $required_fields = ['school_id', 'full_name', 'role'];
            foreach ($required_fields as $field) {
                if (empty($_POST[$field])) {
                    http_response_code(400);
                    echo json_encode([
                        'status' => 'error',
                        'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required.'
                    ]);
                    return;
                }
            }

            // Additional validation for students
            if ($_POST['role'] === 'student') {
                if (empty($_POST['year_level']) || empty($_POST['section'])) {
                    http_response_code(400);
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Year level and section are required for students.'
                    ]);
                    return;
                }
            }

            $data = [
                'school_id' => $_POST['school_id'],
                'full_name' => $_POST['full_name'],
                'role' => $_POST['role'],
                'year_level' => $_POST['year_level'] ?? null,
                'section' => $_POST['section'] ?? null
            ];

            $user_id = $this->userModel->create($data);

            if ($user_id) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'User created successfully.',
                    'user_id' => $user_id
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Failed to create user.'
                ]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'An error occurred while creating user.'
            ]);
        }
    }

    /**
     * Update user (Admin only)
     */
    public function update()
    {
        header('Content-Type: application/json');
        $this->authController->requireRole('admin');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid request method.'
            ]);
            return;
        }

        try {
            $user_id = $_POST['user_id'] ?? '';
            
            if (empty($user_id)) {
                http_response_code(400);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'User ID is required.'
                ]);
                return;
            }

            // Validate required fields
            $required_fields = ['school_id', 'full_name', 'role'];
            foreach ($required_fields as $field) {
                if (empty($_POST[$field])) {
                    http_response_code(400);
                    echo json_encode([
                        'status' => 'error',
                        'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required.'
                    ]);
                    return;
                }
            }

            $data = [
                'school_id' => $_POST['school_id'],
                'full_name' => $_POST['full_name'],
                'role' => $_POST['role'],
                'year_level' => $_POST['year_level'] ?? null,
                'section' => $_POST['section'] ?? null
            ];

            $result = $this->userModel->update($user_id, $data);

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
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'An error occurred while updating user.'
            ]);
        }
    }

    /**
     * Delete user (Admin only)
     */
    public function delete()
    {
        header('Content-Type: application/json');
        $this->authController->requireRole('admin');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid request method.'
            ]);
            return;
        }

        try {
            $user_id = $_POST['user_id'] ?? '';
            
            if (empty($user_id)) {
                http_response_code(400);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'User ID is required.'
                ]);
                return;
            }

            $result = $this->userModel->delete($user_id);

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
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'An error occurred while deleting user.'
            ]);
        }
    }

    /**
     * Get single user by ID
     */
    public function show()
    {
        header('Content-Type: application/json');
        $this->authController->requireAuth();

        $user_id = $_GET['id'] ?? '';
        
        if (empty($user_id)) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'User ID is required.'
            ]);
            return;
        }

        try {
            $user = $this->userModel->findById($user_id);

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
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to fetch user.'
            ]);
        }
    }
}