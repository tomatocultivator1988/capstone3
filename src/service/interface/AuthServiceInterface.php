<?php

namespace App\Service\Interface;

/**
 * AuthService Interface
 * 
 * Defines the contract for all authentication business logic operations.
 * This interface ensures that all AuthService implementations follow the same contract.
 */
interface AuthServiceInterface
{
    /**
     * Login user
     */
    public function login(string $school_id, string $password): ?array;

    /**
     * Logout user
     */
    public function logout(): bool;

    /**
     * Check if user is logged in
     */
    public function isLoggedIn(): bool;

    /**
     * Get current user
     */
    public function getCurrentUser(): ?array;

    /**
     * Check if user has specific role
     */
    public function hasRole(string $role): bool;

    /**
     * Require authentication
     */
    public function requireAuth(): bool;

    /**
     * Require specific role
     */
    public function requireRole(string $role): bool;
}