<?php

namespace App\Services;

/**
 * AuthService Interface
 * 
 * Defines the contract for authentication and authorization operations.
 */
interface AuthService
{
    /**
     * Authenticate user with credentials
     *
     * @param string $school_id The school ID
     * @param string $password The password
     * @return array|false User data if authentication successful, false otherwise
     */
    public function login(string $school_id, string $password);

    /**
     * Logout current user
     *
     * @return bool True if successful, false otherwise
     */
    public function logout(): bool;

    /**
     * Check if user is authenticated
     *
     * @return bool True if authenticated, false otherwise
     */
    public function isAuthenticated(): bool;

    /**
     * Get current authenticated user
     *
     * @return array|false Current user data if authenticated, false otherwise
     */
    public function getCurrentUser();

    /**
     * Check if current user has specific role
     *
     * @param string $role The role to check
     * @return bool True if user has role, false otherwise
     */
    public function hasRole(string $role): bool;

    /**
     * Require authentication (throws exception if not authenticated)
     *
     * @throws \Exception If user is not authenticated
     */
    public function requireAuth(): void;

    /**
     * Require specific role (throws exception if user doesn't have role)
     *
     * @param string $role The required role
     * @throws \Exception If user doesn't have the required role
     */
    public function requireRole(string $role): void;

    /**
     * Start user session
     *
     * @param array $userData The user data to store in session
     * @return bool True if successful, false otherwise
     */
    public function startSession(array $userData): bool;

    /**
     * Destroy user session
     *
     * @return bool True if successful, false otherwise
     */
    public function destroySession(): bool;

    /**
     * Change user password
     *
     * @param int $userId The user ID
     * @param string $currentPassword Current password
     * @param string $newPassword New password
     * @return bool True if successful, false otherwise
     */
    public function changePassword(int $userId, string $currentPassword, string $newPassword): bool;

    /**
     * Validate password strength
     *
     * @param string $password The password to validate
     * @return array Array of validation errors (empty if valid)
     */
    public function validatePassword(string $password): array;
}