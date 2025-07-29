<?php

namespace App\Services;

use App\Models\User;
use App\Services\UserService;
use Exception;
use PDOException;

/**
 * UserServiceImpl
 * 
 * Implementation of the UserService interface.
 * Handles all user-related business logic and coordinates with the User model.
 */
class UserServiceImpl implements UserService
{
    private User $userModel;

    public function __construct(?User $userModel = null)
    {
        $this->userModel = $userModel ?? new User();
    }

    /**
     * {@inheritdoc}
     */
    public function createUser(string $school_id, string $full_name, string $role, ?int $year_level = null, ?string $section = null)
    {
        try {
            // Validate input data
            $userData = [
                'school_id' => $school_id,
                'full_name' => $full_name,
                'role' => $role,
                'year_level' => $year_level,
                'section' => $section
            ];

            $validationErrors = $this->validateUserData($userData);
            if (!empty($validationErrors)) {
                throw new Exception('Validation failed: ' . implode(', ', $validationErrors));
            }

            // Check if user already exists
            if ($this->userExists($school_id)) {
                throw new Exception('User with this school ID already exists');
            }

            // The User model handles password generation internally
            $userData = [
                'school_id' => $school_id,
                'full_name' => $full_name,
                'role' => $role,
                'year_level' => $year_level,
                'section' => $section
            ];
            
            return $this->userModel->create($userData);
        } catch (Exception $e) {
            error_log("UserService::createUser error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function updateUser(int $user_id, string $school_id, string $full_name, string $role, ?int $year_level = null, ?string $section = null): bool
    {
        try {
            // Validate input data
            $userData = [
                'school_id' => $school_id,
                'full_name' => $full_name,
                'role' => $role,
                'year_level' => $year_level,
                'section' => $section
            ];

            $validationErrors = $this->validateUserData($userData);
            if (!empty($validationErrors)) {
                throw new Exception('Validation failed: ' . implode(', ', $validationErrors));
            }

            // Check if the user exists
            $existingUser = $this->getUserById($user_id);
            if (!$existingUser) {
                throw new Exception('User not found');
            }

            // Check if school_id is being changed and if new school_id already exists
            if ($existingUser['school_id'] !== $school_id && $this->userExists($school_id)) {
                throw new Exception('Another user with this school ID already exists');
            }

            $userData = [
                'school_id' => $school_id,
                'full_name' => $full_name,
                'role' => $role,
                'year_level' => $year_level,
                'section' => $section
            ];
            
            return $this->userModel->update($user_id, $userData);
        } catch (Exception $e) {
            error_log("UserService::updateUser error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deleteUser(int $user_id): bool
    {
        try {
            // Check if user exists before attempting deletion
            if (!$this->getUserById($user_id)) {
                throw new Exception('User not found');
            }

            return $this->userModel->delete($user_id);
        } catch (Exception $e) {
            error_log("UserService::deleteUser error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getUserById(int $user_id)
    {
        try {
            return $this->userModel->findById($user_id);
        } catch (Exception $e) {
            error_log("UserService::getUserById error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getUserBySchoolId(string $school_id)
    {
        try {
            return $this->userModel->findBySchoolId($school_id);
        } catch (Exception $e) {
            error_log("UserService::getUserBySchoolId error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAllUsers(): array
    {
        try {
            return $this->userModel->getAllUsers() ?? [];
        } catch (Exception $e) {
            error_log("UserService::getAllUsers error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getUsersByRole(string $role): array
    {
        try {
            return $this->userModel->getUsersByRole($role) ?? [];
        } catch (Exception $e) {
            error_log("UserService::getUsersByRole error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function authenticateUser(string $school_id, string $password)
    {
        try {
            error_log("UserService::authenticateUser - Attempting authentication for: $school_id");
            $result = $this->userModel->authenticate($school_id, $password);
            error_log("UserService::authenticateUser - Result: " . ($result ? 'success' : 'failed'));
            return $result;
        } catch (Exception $e) {
            error_log("UserService::authenticateUser error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function userExists(string $school_id): bool
    {
        try {
            $user = $this->getUserBySchoolId($school_id);
            return $user !== false && $user !== null;
        } catch (Exception $e) {
            error_log("UserService::userExists error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function validateUserData(array $userData): array
    {
        $errors = [];

        // Validate school_id
        if (empty($userData['school_id'])) {
            $errors[] = 'School ID is required';
        } elseif (strlen($userData['school_id']) < 3) {
            $errors[] = 'School ID must be at least 3 characters long';
        }

        // Validate full_name
        if (empty($userData['full_name'])) {
            $errors[] = 'Full name is required';
        } elseif (strlen($userData['full_name']) < 2) {
            $errors[] = 'Full name must be at least 2 characters long';
        }

        // Validate role
        $validRoles = ['admin', 'faculty', 'student'];
        if (empty($userData['role'])) {
            $errors[] = 'Role is required';
        } elseif (!in_array($userData['role'], $validRoles)) {
            $errors[] = 'Role must be one of: ' . implode(', ', $validRoles);
        }

        // Validate student-specific fields
        if ($userData['role'] === 'student') {
            if (empty($userData['year_level'])) {
                $errors[] = 'Year level is required for students';
            } elseif (!is_numeric($userData['year_level']) || $userData['year_level'] < 1 || $userData['year_level'] > 12) {
                $errors[] = 'Year level must be between 1 and 12';
            }

            if (empty($userData['section'])) {
                $errors[] = 'Section is required for students';
            }
        }

        return $errors;
    }
}