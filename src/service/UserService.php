<?php

namespace App\Services;

/**
 * UserService Interface
 * 
 * Defines the contract for user-related business logic operations.
 * This interface ensures that any implementation provides consistent
 * methods for user management functionality.
 */
interface UserService
{
    /**
     * Create a new user
     *
     * @param string $school_id The unique school identifier
     * @param string $full_name The user's full name
     * @param string $role The user's role (admin, faculty, student)
     * @param int|null $year_level Year level for students
     * @param string|null $section Section for students
     * @return int|false User ID if successful, false otherwise
     */
    public function createUser(string $school_id, string $full_name, string $role, ?int $year_level = null, ?string $section = null);

    /**
     * Update an existing user
     *
     * @param int $user_id The user's ID
     * @param string $school_id The unique school identifier
     * @param string $full_name The user's full name
     * @param string $role The user's role
     * @param int|null $year_level Year level for students
     * @param string|null $section Section for students
     * @return bool True if successful, false otherwise
     */
    public function updateUser(int $user_id, string $school_id, string $full_name, string $role, ?int $year_level = null, ?string $section = null): bool;

    /**
     * Delete a user
     *
     * @param int $user_id The user's ID
     * @return bool True if successful, false otherwise
     */
    public function deleteUser(int $user_id): bool;

    /**
     * Get user by ID
     *
     * @param int $user_id The user's ID
     * @return array|false User data if found, false otherwise
     */
    public function getUserById(int $user_id);

    /**
     * Get user by school ID
     *
     * @param string $school_id The school ID
     * @return array|false User data if found, false otherwise
     */
    public function getUserBySchoolId(string $school_id);

    /**
     * Get all users
     *
     * @return array Array of users
     */
    public function getAllUsers(): array;

    /**
     * Get users by role
     *
     * @param string $role The role to filter by
     * @return array Array of users with the specified role
     */
    public function getUsersByRole(string $role): array;

    /**
     * Authenticate user credentials
     *
     * @param string $school_id The school ID
     * @param string $password The password
     * @return array|false User data if authentication successful, false otherwise
     */
    public function authenticateUser(string $school_id, string $password);

    /**
     * Check if user exists by school ID
     *
     * @param string $school_id The school ID to check
     * @return bool True if user exists, false otherwise
     */
    public function userExists(string $school_id): bool;

    /**
     * Validate user data
     *
     * @param array $userData The user data to validate
     * @return array Array of validation errors (empty if valid)
     */
    public function validateUserData(array $userData): array;
}