<?php

namespace Service\Interface;

use Model\User;

/**
 * Authentication Service Interface
 * 
 * Defines authentication and authorization business logic.
 */
interface AuthServiceInterface
{
    /**
     * Authenticate user with credentials
     */
    public function login(string $school_id, string $password): ?array;

    /**
     * Logout user
     */
    public function logout(): bool;

    /**
     * Check if user is authenticated
     */
    public function isAuthenticated(): bool;

    /**
     * Get current authenticated user
     */
    public function getCurrentUser(): ?User;

    /**
     * Check if user has specific role
     */
    public function hasRole(string $role): bool;

    /**
     * Check if user has permission for action
     */
    public function hasPermission(string $permission): bool;

    /**
     * Validate password strength
     */
    public function validatePassword(string $password): array;

    /**
     * Generate secure password hash
     */
    public function hashPassword(string $password): string;

    /**
     * Verify password against hash
     */
    public function verifyPassword(string $password, string $hash): bool;

    /**
     * Generate password reset token
     */
    public function generateResetToken(string $school_id): ?string;

    /**
     * Reset password with token
     */
    public function resetPasswordWithToken(string $token, string $newPassword): bool;

    /**
     * Start user session
     */
    public function startSession(User $user): bool;

    /**
     * Destroy user session
     */
    public function destroySession(): bool;
}