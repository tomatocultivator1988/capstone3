<?php

namespace Service\Impl;

use Service\Interface\AuthServiceInterface;
use Dao\Interface\UserDAOInterface;
use Dao\Impl\UserDAOImpl;
use Model\User;
use Exception;

/**
 * Authentication Service Implementation
 * 
 * Contains authentication and authorization business logic.
 * Uses UserDAO for database access.
 */
class AuthServiceImpl implements AuthServiceInterface
{
    private UserDAOInterface $userDAO;

    public function __construct(?UserDAOInterface $userDAO = null)
    {
        $this->userDAO = $userDAO ?? new UserDAOImpl();
    }

    public function login(string $school_id, string $password): ?array
    {
        try {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            // Find user by school ID
            $user = $this->userDAO->findBySchoolId($school_id);
            if (!$user) {
                error_log("AuthService::login - User not found: $school_id");
                return null;
            }

            // Verify password
            if (!password_verify($password, $user->getPassword())) {
                error_log("AuthService::login - Invalid password for user: $school_id");
                return null;
            }

            // Start session
            $this->startSession($user);

            // Return user data (without password)
            return [
                'user_id' => $user->getUserId(),
                'school_id' => $user->getSchoolId(),
                'full_name' => $user->getFullName(),
                'role' => $user->getRole(),
                'year_level' => $user->getYearLevel(),
                'section' => $user->getSection()
            ];
        } catch (Exception $e) {
            error_log("AuthService::login error: " . $e->getMessage());
            return null;
        }
    }

    public function logout(): bool
    {
        try {
            return $this->destroySession();
        } catch (Exception $e) {
            error_log("AuthService::logout error: " . $e->getMessage());
            return false;
        }
    }

    public function isAuthenticated(): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    public function getCurrentUser(): ?User
    {
        if (!$this->isAuthenticated()) {
            return null;
        }

        $userId = $_SESSION['user_id'];
        return $this->userDAO->findById($userId);
    }

    public function hasRole(string $role): bool
    {
        $user = $this->getCurrentUser();
        return $user && $user->hasRole($role);
    }

    public function hasPermission(string $permission): bool
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return false;
        }

        // Define role-based permissions
        $permissions = [
            'admin' => ['*'], // Admin has all permissions
            'faculty' => [
                'create_exam', 'edit_exam', 'delete_exam', 'view_exam',
                'create_question', 'edit_question', 'delete_question',
                'view_results', 'grade_exam', 'manage_subjects'
            ],
            'student' => [
                'take_exam', 'view_results', 'view_exam_list'
            ]
        ];

        $userRole = $user->getRole();
        $rolePermissions = $permissions[$userRole] ?? [];

        // Admin has all permissions
        if (in_array('*', $rolePermissions)) {
            return true;
        }

        return in_array($permission, $rolePermissions);
    }

    public function validatePassword(string $password): array
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

        // Check for common weak passwords
        $weakPasswords = ['123456', 'password', 'admin', 'qwerty'];
        if (in_array(strtolower($password), $weakPasswords)) {
            $errors[] = 'Password is too common and weak';
        }

        return $errors;
    }

    public function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    public function generateResetToken(string $school_id): ?string
    {
        try {
            // Find user
            $user = $this->userDAO->findBySchoolId($school_id);
            if (!$user) {
                return null;
            }

            // Generate secure token
            $token = bin2hex(random_bytes(32));
            
            // Store token with expiration (you'd need a tokens table for this)
            // For now, just return the token
            error_log("Password reset token generated for $school_id: $token");
            
            return $token;
        } catch (Exception $e) {
            error_log("AuthService::generateResetToken error: " . $e->getMessage());
            return null;
        }
    }

    public function resetPasswordWithToken(string $token, string $newPassword): bool
    {
        try {
            // Validate new password
            $passwordErrors = $this->validatePassword($newPassword);
            if (!empty($passwordErrors)) {
                throw new Exception('Password validation failed: ' . implode(', ', $passwordErrors));
            }

            // In a real implementation, you'd verify the token against a tokens table
            // For now, just log the operation
            error_log("Password reset with token: $token");
            
            return true;
        } catch (Exception $e) {
            error_log("AuthService::resetPasswordWithToken error: " . $e->getMessage());
            return false;
        }
    }

    public function startSession(User $user): bool
    {
        try {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            // Regenerate session ID for security
            session_regenerate_id(true);

            // Store user information in session
            $_SESSION['user_id'] = $user->getUserId();
            $_SESSION['school_id'] = $user->getSchoolId();
            $_SESSION['full_name'] = $user->getFullName();
            $_SESSION['role'] = $user->getRole();
            $_SESSION['login_time'] = time();

            return true;
        } catch (Exception $e) {
            error_log("AuthService::startSession error: " . $e->getMessage());
            return false;
        }
    }

    public function destroySession(): bool
    {
        try {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            // Clear all session variables
            $_SESSION = [];

            // Destroy session cookie
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params["path"], $params["domain"],
                    $params["secure"], $params["httponly"]
                );
            }

            // Destroy session
            session_destroy();

            return true;
        } catch (Exception $e) {
            error_log("AuthService::destroySession error: " . $e->getMessage());
            return false;
        }
    }
}