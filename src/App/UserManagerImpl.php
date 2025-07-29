<?php

namespace App;

use App\Services\UserService;
use App\Services\UserServiceImpl;

/**
 * UserManagerImpl
 * 
 * Legacy wrapper class that now delegates to the UserService implementation.
 * This maintains backward compatibility while leveraging the new service architecture.
 * 
 * @deprecated Use UserService interface directly instead
 */
class UserManagerImpl
{
    private UserService $userService;

    public function __construct(?UserService $userService = null)
    {
        $this->userService = $userService ?? new UserServiceImpl();
    }

    /**
     * Add a new user
     * 
     * @deprecated Use UserService::createUser() instead
     */
    public function addUser($school_id, $full_name, $role, $year_level = null, $section = null)
    {
        return $this->userService->createUser($school_id, $full_name, $role, $year_level, $section);
    }

    /**
     * Update an existing user
     * 
     * @deprecated Use UserService::updateUser() instead
     */
    public function updateUser($user_id, $school_id, $full_name, $role, $year_level = null, $section = null)
    {
        return $this->userService->updateUser($user_id, $school_id, $full_name, $role, $year_level, $section);
    }

    /**
     * Delete a user
     * 
     * @deprecated Use UserService::deleteUser() instead
     */
    public function deleteUser($user_id)
    {
        return $this->userService->deleteUser($user_id);
    }

    /**
     * Get user service instance for advanced operations
     * 
     * @return UserService
     */
    public function getUserService(): UserService
    {
        return $this->userService;
    }
}