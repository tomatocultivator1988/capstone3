<?php

namespace App\DAO\Interface;

use App\Model\User;

/**
 * UserDAO Interface
 * 
 * Defines the contract for all user data access operations.
 * This interface ensures that all UserDAO implementations follow the same contract.
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
    public function getAll(): array;

    /**
     * Get users by role
     */
    public function getByRole(string $role): array;

    /**
     * Get students by year level and section
     */
    public function getStudentsByYearSection(int $year_level, string $section): array;

    /**
     * Create a new user
     */
    public function create(User $user): ?int;

    /**
     * Update an existing user
     */
    public function update(User $user): bool;

    /**
     * Delete a user by ID
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