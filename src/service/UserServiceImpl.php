<?php

namespace Service\Impl;

use Model\User;
use Dao\Interface\UserDAOInterface;
use Dao\Impl\UserDAOImpl;
use Service\UserService;
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
    private UserDAOInterface $userDAO;

    public function __construct(?UserDAOInterface $userDAO = null)
    {
        $this->userDAO = $userDAO ?? new UserDAOImpl();
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
            return $this->userDAO->create($user);
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

            // Get existing user and update it
            $user = $this->userDAO->findById($user_id);
            if (!$user) {
                throw new Exception('User not found');
            }

            $user->setSchoolId($school_id);
            $user->setFullName($full_name);
            $user->setRole($role);
            $user->setYearLevel($year_level);
            $user->setSection($section);

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
            // Check if user exists before attempting deletion
            if (!$this->getUserById($user_id)) {
                throw new Exception('User not found');
            }

            return $this->userDAO->deleteById($user_id);
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
            $user = $this->userDAO->findById($user_id);
            return $user ? $user->toArray() : false;
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
            $user = $this->userDAO->findBySchoolId($school_id);
            return $user ? $user->toArray() : false;
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
            $users = $this->userDAO->findAll();
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
            $users = $this->userDAO->findByRole($role);
            return array_map(fn($user) => $user->toArray(), $users);
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
            
            // Find user by school ID
            $user = $this->userDAO->findBySchoolId($school_id);
            
            if (!$user) {
                error_log("UserService::authenticateUser - User not found: $school_id");
                return false;
            }

            error_log("UserService::authenticateUser - User found: " . json_encode($user->toArray()));
            error_log("UserService::authenticateUser - Password comparison: input='$password', stored='{$user->getPassword()}'");

            // Check if password is hashed (starts with $) or plain text
            if (strpos($user->getPassword(), '$') === 0) {
                $result = password_verify($password, $user->getPassword());
                error_log("UserService::authenticateUser - Hashed password verification: " . ($result ? 'success' : 'failed'));
                return $result ? $user->toArray() : false;
            } else {
                $result = $password === $user->getPassword();
                error_log("UserService::authenticateUser - Plain text password comparison: " . ($result ? 'success' : 'failed'));
                return $result ? $user->toArray() : false;
            }
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