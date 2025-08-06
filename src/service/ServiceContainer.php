<?php

namespace Service;

use Dao\Impl\UserDAOImpl;

/**
 * Service Container
 * 
 * Handles the creation and management of service instances
 * with their dependencies properly injected.
 */
class ServiceContainer
{
    private static $instance = null;
    private $services = [];

    private function __construct()
    {
        // Initialize services with their dependencies
        $this->initializeServices();
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialize all services with their dependencies
     */
    private function initializeServices()
    {
        // Create DAO instances
        $userDAO = new UserDAOImpl();

        // Create Service instances with their dependencies
        $this->services['authService'] = new \Service\Impl\AuthServiceImpl($userDAO);
        $this->services['userService'] = new \Service\Impl\UserServiceImpl($userDAO);
    }

    /**
     * Get a service instance
     */
    public function get($serviceName)
    {
        if (!isset($this->services[$serviceName])) {
            throw new \Exception("Service '$serviceName' not found");
        }
        return $this->services[$serviceName];
    }

    /**
     * Get AuthService instance
     */
    public function getAuthService(): \Service\Impl\AuthServiceImpl
    {
        return $this->get('authService');
    }

    /**
     * Get UserService instance
     */
    public function getUserService(): \Service\Impl\UserServiceImpl
    {
        return $this->get('userService');
    }
}
?>