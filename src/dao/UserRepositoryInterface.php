<?php

namespace App\Repositories;

use App\Models\User;

/**
 * UserRepositoryInterface
 * 
 * Defines the contract for user data access operations.
 * This interface ensures consistency across different repository implementations.
 */
interface UserRepositoryInterface
{
    /**
     * Find user by school ID
     */
    public function findBySchoolId(string $school_id): ?User;

    /**
     * Find user by ID
     */
    public function findById(int $user_id): ?User;

    /**
     * Get all users
     */
    public function getAll(): array;

    /**
     * Get users by role
     */
    public function getByRole(string $role): array;

    /**
     * Get students by year and section
     */
    public function getStudentsByYearSection(int $year_level, string $section): array;

    /**
     * Create new user
     */
    public function create(User $user): ?int;

    /**
     * Update user
     */
    public function update(User $user): bool;

    /**
     * Delete user
     */
    public function delete(int $user_id): bool;

    /**
     * Check if user exists by school ID
     */
    public function existsBySchoolId(string $school_id): bool;

    /**
     * Update user password
     */
    public function updatePassword(int $user_id, string $hashedPassword): bool;
}