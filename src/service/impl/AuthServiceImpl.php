<?php

namespace App\Service\Impl;

use App\Service\Interface\AuthServiceInterface;
use App\Service\Interface\UserServiceInterface;

/**
 * AuthService Implementation
 * 
 * Implementation of the AuthService interface.
 * Handles all authentication-related business logic.
 */
class AuthServiceImpl implements AuthServiceInterface
{
    private UserServiceInterface $userService;

    public function __construct(UserServiceInterface $userService)
    {
        $this->userService = $userService;
    }

    /**
     * {@inheritdoc}
     */
    public function login(string $school_id, string $password): ?array
    {
        try {
            // Authenticate user
            $user = $this->userService->authenticateUser($school_id, $password);
            if (!$user) {
                return null;
            }

            // Start session if not already started
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            // Store user data in session
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['school_id'] = $user['school_id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['year_level'] = $user['year_level'];
            $_SESSION['section'] = $user['section'];
            $_SESSION['logged_in'] = true;
            $_SESSION['login_time'] = time();

            return $user;
        } catch (\Exception $e) {
            error_log("AuthService::login error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function logout(): bool
    {
        try {
            // Start session if not already started
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            // Clear session data
            session_unset();
            session_destroy();

            return true;
        } catch (\Exception $e) {
            error_log("AuthService::logout error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isLoggedIn(): bool
    {
        try {
            // Start session if not already started
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
        } catch (\Exception $e) {
            error_log("AuthService::isLoggedIn error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentUser(): ?array
    {
        try {
            if (!$this->isLoggedIn()) {
                return null;
            }

            // Start session if not already started
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            return [
                'user_id' => $_SESSION['user_id'] ?? null,
                'school_id' => $_SESSION['school_id'] ?? null,
                'full_name' => $_SESSION['full_name'] ?? null,
                'role' => $_SESSION['role'] ?? null,
                'year_level' => $_SESSION['year_level'] ?? null,
                'section' => $_SESSION['section'] ?? null,
                'login_time' => $_SESSION['login_time'] ?? null
            ];
        } catch (\Exception $e) {
            error_log("AuthService::getCurrentUser error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function hasRole(string $role): bool
    {
        try {
            $currentUser = $this->getCurrentUser();
            return $currentUser && $currentUser['role'] === $role;
        } catch (\Exception $e) {
            error_log("AuthService::hasRole error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function requireAuth(): bool
    {
        try {
            if (!$this->isLoggedIn()) {
                http_response_code(401);
                header('Content-Type: application/json');
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Authentication required'
                ]);
                return false;
            }
            return true;
        } catch (\Exception $e) {
            error_log("AuthService::requireAuth error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function requireRole(string $role): bool
    {
        try {
            if (!$this->requireAuth()) {
                return false;
            }

            if (!$this->hasRole($role)) {
                http_response_code(403);
                header('Content-Type: application/json');
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Insufficient permissions'
                ]);
                return false;
            }
            return true;
        } catch (\Exception $e) {
            error_log("AuthService::requireRole error: " . $e->getMessage());
            return false;
        }
    }
}