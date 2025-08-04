<?php

namespace App\Service\Interface;

use App\Model\User;

/**
 * UserService Interface
 * 
 * Defines the contract for all user business logic operations.
 * This interface ensures that all UserService implementations follow the same contract.
 */
interface UserServiceInterface
{
    /**
     * Create a new user
     */
    public function createUser(string $school_id, string $full_name, string $role, ?int $year_level = null, ?string $section = null): bool;

    /**
     * Update an existing user
     */
    public function updateUser(int $user_id, string $school_id, string $full_name, string $role, ?int $year_level = null, ?string $section = null): bool;

    /**
     * Delete a user
     */
    public function deleteUser(int $user_id): bool;

    /**
     * Get user by ID
     */
    public function getUserById(int $user_id): ?array;

    /**
     * Get user by school ID
     */
    public function getUserBySchoolId(string $school_id): ?array;

    /**
     * Get all users
     */
    public function getAllUsers(): array;

    /**
     * Get users by role
     */
    public function getUsersByRole(string $role): array;

    /**
     * Get students by year level and section
     */
    public function getStudentsByYearSection(int $year_level, string $section): array;

    /**
     * Authenticate user
     */
    public function authenticateUser(string $school_id, string $password): ?array;

    /**
     * Check if user exists
     */
    public function userExists(string $school_id): bool;

    /**
     * Validate user data
     */
    public function validateUserData(array $userData): array;
}