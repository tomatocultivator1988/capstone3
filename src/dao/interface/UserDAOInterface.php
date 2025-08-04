<?php

namespace Dao\Interface;

use Model\User;

/**
 * User DAO Interface
 * 
 * Defines all database operations for User entities.
 * Only database access methods, no business logic.
 */
interface UserDAOInterface
{
    /**
     * Find user by ID
     */
    public function findById(int $user_id): ?User;

    /**
     * Find user by school ID
     */
    public function findBySchoolId(string $school_id): ?User;

    /**
     * Get all users
     */
    public function findAll(): array;

    /**
     * Get users by role
     */
    public function findByRole(string $role): array;

    /**
     * Create a new user
     */
    public function create(User $user): bool;

    /**
     * Update an existing user
     */
    public function update(User $user): bool;

    /**
     * Delete a user by ID
     */
    public function deleteById(int $user_id): bool;

    /**
     * Check if user exists by school ID
     */
    public function existsBySchoolId(string $school_id): bool;

    /**
     * Get total count of users
     */
    public function getTotalCount(): int;

    /**
     * Get users with pagination
     */
    public function findWithPagination(int $limit, int $offset): array;

    /**
     * Get students by year level and section
     */
    public function findStudentsByYearAndSection(int $year_level, string $section): array;

    /**
     * Update user password
     */
    public function updatePassword(int $user_id, string $hashedPassword): bool;
}