<?php

namespace App\Service\Impl;

use App\Service\Interface\UserServiceInterface;
use App\DAO\Interface\UserDAOInterface;
use App\Model\User;
use Exception;

/**
 * UserService Implementation
 * 
 * Implementation of the UserService interface.
 * Handles all user-related business logic and coordinates with the User DAO.
 */
class UserServiceImpl implements UserServiceInterface
{
    private UserDAOInterface $userDAO;

    public function __construct(UserDAOInterface $userDAO)
    {
        $this->userDAO = $userDAO;
    }

    /**
     * {@inheritdoc}
     */
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

            // Create user
            return $this->userDAO->create($user) !== null;
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

            // Update user model
            $user = new User();
            $user->setUserId($user_id);
            $user->setSchoolId($school_id);
            $user->setFullName($full_name);
            $user->setRole($role);
            $user->setYearLevel($year_level);
            $user->setSection($section);

            // Update user
            return $this->userDAO->update($user);
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
            // Check if the user exists
            $existingUser = $this->getUserById($user_id);
            if (!$existingUser) {
                throw new Exception('User not found');
            }

            // Delete user
            return $this->userDAO->delete($user_id);
        } catch (Exception $e) {
            error_log("UserService::deleteUser error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getUserById(int $user_id): ?array
    {
        try {
            $user = $this->userDAO->findById($user_id);
            return $user ? $user->toArray() : null;
        } catch (Exception $e) {
            error_log("UserService::getUserById error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getUserBySchoolId(string $school_id): ?array
    {
        try {
            $user = $this->userDAO->findBySchoolId($school_id);
            return $user ? $user->toArray() : null;
        } catch (Exception $e) {
            error_log("UserService::getUserBySchoolId error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAllUsers(): array
    {
        try {
            $users = $this->userDAO->getAll();
            return array_map(fn($user) => $user->toArray(), $users);
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
            $users = $this->userDAO->getByRole($role);
            return array_map(fn($user) => $user->toArray(), $users);
        } catch (Exception $e) {
            error_log("UserService::getUsersByRole error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getStudentsByYearSection(int $year_level, string $section): array
    {
        try {
            $users = $this->userDAO->getStudentsByYearSection($year_level, $section);
            return array_map(fn($user) => $user->toArray(), $users);
        } catch (Exception $e) {
            error_log("UserService::getStudentsByYearSection error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function authenticateUser(string $school_id, string $password): ?array
    {
        try {
            $user = $this->userDAO->findBySchoolId($school_id);
            if (!$user) {
                return null;
            }

            // Verify password
            if (password_verify($password, $user->getPassword())) {
                return $user->toArray();
            }

            return null;
        } catch (Exception $e) {
            error_log("UserService::authenticateUser error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function userExists(string $school_id): bool
    {
        try {
            return $this->userDAO->existsBySchoolId($school_id);
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
            $errors[] = 'School ID must be at least 3 characters';
        }

        // Validate full_name
        if (empty($userData['full_name'])) {
            $errors[] = 'Full name is required';
        } elseif (strlen($userData['full_name']) < 2) {
            $errors[] = 'Full name must be at least 2 characters';
        }

        // Validate role
        $validRoles = ['student', 'faculty', 'admin'];
        if (empty($userData['role'])) {
            $errors[] = 'Role is required';
        } elseif (!in_array($userData['role'], $validRoles)) {
            $errors[] = 'Invalid role. Must be one of: ' . implode(', ', $validRoles);
        }

        // Validate year_level for students
        if ($userData['role'] === 'student') {
            if (empty($userData['year_level'])) {
                $errors[] = 'Year level is required for students';
            } elseif ($userData['year_level'] < 1 || $userData['year_level'] > 12) {
                $errors[] = 'Year level must be between 1 and 12';
            }

            if (empty($userData['section'])) {
                $errors[] = 'Section is required for students';
            }
        }

        return $errors;
    }
}