<?php

namespace Service\Impl;

use Service\Interface\UserServiceInterface;
use Dao\Interface\UserDAOInterface;
use Dao\Impl\UserDAOImpl;
use Model\User;
use Exception;

/**
 * User Service Implementation
 * 
 * Contains business logic for User operations.
 * Uses UserDAO for database access.
 */
class UserServiceImpl implements UserServiceInterface
{
    private UserDAOInterface $userDAO;

    public function __construct(?UserDAOInterface $userDAO = null)
    {
        $this->userDAO = $userDAO ?? new UserDAOImpl();
    }

    public function createUser(string $school_id, string $full_name, string $role, ?int $year_level = null, ?string $section = null): bool
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

            // Create user model
            $user = new User();
            $user->setSchoolId($school_id);
            $user->setFullName($full_name);
            $user->setRole($role);
            $user->setYearLevel($year_level);
            $user->setSection($section);

            // Generate default password
            $plainPassword = $school_id . $full_name;
            $hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);
            $user->setPassword($hashedPassword);

            // Create user through DAO
            return $this->userDAO->create($user);
        } catch (Exception $e) {
            error_log("UserService::createUser error: " . $e->getMessage());
            return false;
        }
    }

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

            // Get existing user
            $user = $this->userDAO->findById($user_id);
            if (!$user) {
                throw new Exception('User not found');
            }

            // Check if school ID is unique (excluding current user)
            $existingUser = $this->userDAO->findBySchoolId($school_id);
            if ($existingUser && $existingUser->getUserId() !== $user_id) {
                throw new Exception('School ID already exists for another user');
            }

            // Update user properties
            $user->setSchoolId($school_id);
            $user->setFullName($full_name);
            $user->setRole($role);
            $user->setYearLevel($year_level);
            $user->setSection($section);

            // Update through DAO
            return $this->userDAO->update($user);
        } catch (Exception $e) {
            error_log("UserService::updateUser error: " . $e->getMessage());
            return false;
        }
    }

    public function deleteUser(int $user_id): bool
    {
        try {
            // Check if user exists
            $user = $this->userDAO->findById($user_id);
            if (!$user) {
                throw new Exception('User not found');
            }

            // Business rule: Don't allow deletion of admin users
            if ($user->isAdmin()) {
                throw new Exception('Cannot delete admin users');
            }

            // TODO: Check for dependencies (exams, results, etc.)
            // This would involve other DAOs to check relationships

            return $this->userDAO->deleteById($user_id);
        } catch (Exception $e) {
            error_log("UserService::deleteUser error: " . $e->getMessage());
            return false;
        }
    }

    public function getUserById(int $user_id): ?User
    {
        return $this->userDAO->findById($user_id);
    }

    public function getUserBySchoolId(string $school_id): ?User
    {
        return $this->userDAO->findBySchoolId($school_id);
    }

    public function getAllUsers(?string $role = null): array
    {
        if ($role) {
            return $this->userDAO->findByRole($role);
        }
        return $this->userDAO->findAll();
    }

    public function getUsersWithPagination(int $page, int $limit): array
    {
        $offset = ($page - 1) * $limit;
        return $this->userDAO->findWithPagination($limit, $offset);
    }

    public function userExists(string $school_id): bool
    {
        return $this->userDAO->existsBySchoolId($school_id);
    }

    public function validateUserData(array $userData): array
    {
        $errors = [];

        // Validate school ID
        if (empty($userData['school_id'])) {
            $errors[] = 'School ID is required';
        } elseif (strlen($userData['school_id']) < 3) {
            $errors[] = 'School ID must be at least 3 characters';
        }

        // Validate full name
        if (empty($userData['full_name'])) {
            $errors[] = 'Full name is required';
        } elseif (strlen($userData['full_name']) < 2) {
            $errors[] = 'Full name must be at least 2 characters';
        }

        // Validate role
        if (empty($userData['role'])) {
            $errors[] = 'Role is required';
        } elseif (!in_array($userData['role'], ['admin', 'faculty', 'student'])) {
            $errors[] = 'Invalid role. Must be admin, faculty, or student';
        }

        // Validate student-specific fields
        if ($userData['role'] === 'student') {
            if (empty($userData['year_level'])) {
                $errors[] = 'Year level is required for students';
            } elseif ($userData['year_level'] < 1 || $userData['year_level'] > 4) {
                $errors[] = 'Year level must be between 1 and 4';
            }

            if (empty($userData['section'])) {
                $errors[] = 'Section is required for students';
            }
        }

        return $errors;
    }

    public function changePassword(int $user_id, string $currentPassword, string $newPassword): bool
    {
        try {
            // Get user
            $user = $this->userDAO->findById($user_id);
            if (!$user) {
                throw new Exception('User not found');
            }

            // Verify current password
            if (!password_verify($currentPassword, $user->getPassword())) {
                throw new Exception('Current password is incorrect');
            }

            // Validate new password
            $passwordErrors = $this->validatePassword($newPassword);
            if (!empty($passwordErrors)) {
                throw new Exception('Password validation failed: ' . implode(', ', $passwordErrors));
            }

            // Hash new password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            // Update password through DAO
            return $this->userDAO->updatePassword($user_id, $hashedPassword);
        } catch (Exception $e) {
            error_log("UserService::changePassword error: " . $e->getMessage());
            return false;
        }
    }

    public function resetPassword(int $user_id): bool
    {
        try {
            // Get user
            $user = $this->userDAO->findById($user_id);
            if (!$user) {
                throw new Exception('User not found');
            }

            // Generate default password (school_id + full_name)
            $defaultPassword = $user->getSchoolId() . $user->getFullName();
            $hashedPassword = password_hash($defaultPassword, PASSWORD_DEFAULT);

            // Update password through DAO
            return $this->userDAO->updatePassword($user_id, $hashedPassword);
        } catch (Exception $e) {
            error_log("UserService::resetPassword error: " . $e->getMessage());
            return false;
        }
    }

    public function getStudentsByYearAndSection(int $year_level, string $section): array
    {
        return $this->userDAO->findStudentsByYearAndSection($year_level, $section);
    }

    public function getUserStatistics(): array
    {
        $totalUsers = $this->userDAO->getTotalCount();
        $adminCount = count($this->userDAO->findByRole('admin'));
        $facultyCount = count($this->userDAO->findByRole('faculty'));
        $studentCount = count($this->userDAO->findByRole('student'));

        return [
            'total' => $totalUsers,
            'admin' => $adminCount,
            'faculty' => $facultyCount,
            'student' => $studentCount
        ];
    }

    public function bulkCreateUsers(array $usersData): array
    {
        $results = [];
        $successCount = 0;
        $errorCount = 0;

        foreach ($usersData as $userData) {
            $result = $this->createUser(
                $userData['school_id'],
                $userData['full_name'],
                $userData['role'],
                $userData['year_level'] ?? null,
                $userData['section'] ?? null
            );

            if ($result) {
                $successCount++;
                $results[] = ['school_id' => $userData['school_id'], 'status' => 'success'];
            } else {
                $errorCount++;
                $results[] = ['school_id' => $userData['school_id'], 'status' => 'error'];
            }
        }

        return [
            'results' => $results,
            'summary' => [
                'total' => count($usersData),
                'success' => $successCount,
                'errors' => $errorCount
            ]
        ];
    }

    public function toggleUserStatus(int $user_id): bool
    {
        // This would require adding an 'active' field to the user table
        // For now, return true as placeholder
        error_log("UserService::toggleUserStatus - Feature not implemented yet");
        return true;
    }

    private function validatePassword(string $password): array
    {
        $errors = [];

        if (strlen($password) < 6) {
            $errors[] = 'Password must be at least 6 characters long';
        }

        if (!preg_match('/[A-Za-z]/', $password)) {
            $errors[] = 'Password must contain at least one letter';
        }

        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one number';
        }

        return $errors;
    }
}