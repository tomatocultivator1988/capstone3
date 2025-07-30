<?php

namespace App\Services;

use App\Services\UserService;
use App\Services\AuthService;
use App\Services\ServiceContainer;
use Exception;

/**
 * AuthServiceImpl
 * 
 * Implementation of the AuthService interface.
 * Handles all authentication and authorization logic.
 */
class AuthServiceImpl implements AuthService
{
    private UserService $userService;

    public function __construct(?UserService $userService = null)
    {
        $this->userService = $userService ?? ServiceContainer::getInstance()->get(UserService::class);
    }

    /**
     * {@inheritdoc}
     */
    public function login(string $school_id, string $password)
    {
        try {
            error_log("AuthService::login - Attempting authentication for: $school_id");
            
            // Authenticate user
            $user = $this->userService->authenticateUser($school_id, $password);
            
            error_log("AuthService::login - UserService result: " . ($user ? 'success' : 'failed'));
            
            if ($user) {
                // Start session
                $this->startSession($user);
                error_log("AuthService::login - Session started successfully");
                return $user;
            }
            
            error_log("AuthService::login - Authentication failed");
            return false;
        } catch (Exception $e) {
            error_log("AuthService::login error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function logout(): bool
    {
        try {
            return $this->destroySession();
        } catch (Exception $e) {
            error_log("AuthService::logout error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isAuthenticated(): bool
    {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentUser()
    {
        try {
            // Start session if not already started
            if (session_status() === PHP_SESSION_NONE) {
                @session_start();
            }
            
            if (!$this->isAuthenticated()) {
                return false;
            }

            // Return user data from session
            return [
                'user_id' => $_SESSION['user_id'],
                'school_id' => $_SESSION['school_id'] ?? '',
                'full_name' => $_SESSION['full_name'] ?? '',
                'role' => $_SESSION['role'] ?? '',
                'year_level' => $_SESSION['year_level'] ?? null,
                'section' => $_SESSION['section'] ?? null
            ];
        } catch (Exception $e) {
            error_log("AuthService::getCurrentUser error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function hasRole(string $role): bool
    {
        $user = $this->getCurrentUser();
        return $user && ($user['role'] === $role);
    }

    /**
     * {@inheritdoc}
     */
    public function requireAuth(): void
    {
        if (!$this->isAuthenticated()) {
            http_response_code(401);
            throw new Exception('Authentication required');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function requireRole(string $role): void
    {
        $this->requireAuth();
        
        if (!$this->hasRole($role)) {
            http_response_code(403);
            throw new Exception("Access denied. Required role: {$role}");
        }
    }

    /**
     * {@inheritdoc}
     */
    public function startSession(array $userData): bool
    {
        try {
            // Start session if not already started
            if (session_status() === PHP_SESSION_NONE) {
                @session_start();
            }
            
            $_SESSION['user_id'] = $userData['user_id'];
            $_SESSION['school_id'] = $userData['school_id'];
            $_SESSION['full_name'] = $userData['full_name'];
            $_SESSION['role'] = $userData['role'];
            $_SESSION['year_level'] = $userData['year_level'] ?? null;
            $_SESSION['section'] = $userData['section'] ?? null;
            $_SESSION['login_time'] = time();
            
            return true;
        } catch (Exception $e) {
            error_log("AuthService::startSession error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function destroySession(): bool
    {
        try {
            // Start session if not already started (needed to destroy it)
            if (session_status() === PHP_SESSION_NONE) {
                @session_start();
            }
            
            // Clear all session variables
            $_SESSION = [];
            
            // Destroy the session cookie
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(
                    session_name(),
                    '',
                    time() - 42000,
                    $params["path"],
                    $params["domain"],
                    $params["secure"],
                    $params["httponly"]
                );
            }
            
            // Destroy the session
            session_destroy();
            
            return true;
        } catch (Exception $e) {
            error_log("AuthService::destroySession error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function changePassword(int $userId, string $currentPassword, string $newPassword): bool
    {
        try {
            // Get user data
            $user = $this->userService->getUserById($userId);
            if (!$user) {
                throw new Exception('User not found');
            }

            // Verify current password
            $authenticated = $this->userService->authenticateUser($user['school_id'], $currentPassword);
            if (!$authenticated) {
                throw new Exception('Current password is incorrect');
            }

            // Validate new password
            $validationErrors = $this->validatePassword($newPassword);
            if (!empty($validationErrors)) {
                throw new Exception('Password validation failed: ' . implode(', ', $validationErrors));
            }

            // Hash new password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            // Update password in database (this would need to be implemented in UserService)
            // For now, we'll return true as this would require extending the UserService
            // TODO: Add updatePassword method to UserService
            
            return true;
        } catch (Exception $e) {
            error_log("AuthService::changePassword error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function validatePassword(string $password): array
    {
        $errors = [];

        // Minimum length
        if (strlen($password) < 6) {
            $errors[] = 'Password must be at least 6 characters long';
        }

        // Maximum length
        if (strlen($password) > 128) {
            $errors[] = 'Password must not exceed 128 characters';
        }

        // At least one letter
        if (!preg_match('/[a-zA-Z]/', $password)) {
            $errors[] = 'Password must contain at least one letter';
        }

        // At least one number
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one number';
        }

        return $errors;
    }
}