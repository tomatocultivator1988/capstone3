<?php

namespace Service\Interface;

use Model\User;

/**
 * User Service Interface
 * 
 * Defines business logic operations for User entities.
 * Contains validation, business rules, and coordination logic.
 */
interface UserServiceInterface
{
    /**
     * Create a new user with validation and business rules
     */
    public function createUser(string $school_id, string $full_name, string $role, ?int $year_level = null, ?string $section = null): bool;

    /**
     * Update user information with validation
     */
    public function updateUser(int $user_id, string $school_id, string $full_name, string $role, ?int $year_level = null, ?string $section = null): bool;

    /**
     * Delete user with business logic checks
     */
    public function deleteUser(int $user_id): bool;

    /**
     * Get user by ID
     */
    public function getUserById(int $user_id): ?User;

    /**
     * Get user by school ID
     */
    public function getUserBySchoolId(string $school_id): ?User;

    /**
     * Get all users with optional filtering
     */
    public function getAllUsers(?string $role = null): array;

    /**
     * Get users with pagination
     */
    public function getUsersWithPagination(int $page, int $limit): array;

    /**
     * Check if user exists by school ID
     */
    public function userExists(string $school_id): bool;

    /**
     * Validate user data
     */
    public function validateUserData(array $userData): array;

    /**
     * Change user password with validation
     */
    public function changePassword(int $user_id, string $currentPassword, string $newPassword): bool;

    /**
     * Reset user password to default
     */
    public function resetPassword(int $user_id): bool;

    /**
     * Get students by year level and section
     */
    public function getStudentsByYearAndSection(int $year_level, string $section): array;

    /**
     * Get user statistics
     */
    public function getUserStatistics(): array;

    /**
     * Bulk create users
     */
    public function bulkCreateUsers(array $usersData): array;

    /**
     * Activate/deactivate user
     */
    public function toggleUserStatus(int $user_id): bool;
}