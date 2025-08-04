<?php

namespace Service;

use Service\UserService;
use Service\Impl\UserServiceImpl;
use Service\ExamService;
use Service\Impl\ExamServiceImpl;
use Service\SubjectService;
use Service\Impl\SubjectServiceImpl;
use Service\AuthService;
use Service\Impl\AuthServiceImpl;
use Service\QuestionService;
use Service\Impl\QuestionServiceImpl;
use Service\ExamResultService;
use Service\Impl\ExamResultServiceImpl;

/**
 * ServiceContainer
 * 
 * A simple service container for managing service dependencies.
 * Provides a centralized way to register and retrieve service instances.
 */
class ServiceContainer
{
    private static ?ServiceContainer $instance = null;
    private array $services = [];
    private array $singletons = [];

    private function __construct()
    {
        // Register default services
        $this->registerDefaults();
    }

    /**
     * Get the singleton instance of the service container
     */
    public static function getInstance(): ServiceContainer
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Register a service with the container
     *
     * @param string $interface The interface or class name
     * @param callable|string $factory Factory function or class name
     * @param bool $singleton Whether to treat as singleton
     */
    public function register(string $interface, $factory, bool $singleton = true): void
    {
        $this->services[$interface] = [
            'factory' => $factory,
            'singleton' => $singleton
        ];
    }

    /**
     * Get a service instance
     *
     * @param string $interface The interface or class name
     * @return mixed The service instance
     * @throws \Exception If service is not registered
     */
    public function get(string $interface)
    {
        if (!isset($this->services[$interface])) {
            throw new \Exception("Service '{$interface}' is not registered");
        }

        $config = $this->services[$interface];

        // Return singleton if already created
        if ($config['singleton'] && isset($this->singletons[$interface])) {
            return $this->singletons[$interface];
        }

        // Create new instance
        $factory = $config['factory'];
        if (is_callable($factory)) {
            $instance = $factory($this);
        } elseif (is_string($factory) && class_exists($factory)) {
            $instance = new $factory();
        } else {
            throw new \Exception("Invalid factory for service '{$interface}'");
        }

        // Store singleton
        if ($config['singleton']) {
            $this->singletons[$interface] = $instance;
        }

        return $instance;
    }

    /**
     * Check if a service is registered
     */
    public function has(string $interface): bool
    {
        return isset($this->services[$interface]);
    }

    /**
     * Register default services
     */
    private function registerDefaults(): void
    {
        // Register UserService
        $this->register(UserService::class, UserServiceImpl::class, true);
        
        // Register ExamService
        $this->register(ExamService::class, ExamServiceImpl::class, true);
        
        // Register SubjectService
        $this->register(SubjectService::class, SubjectServiceImpl::class, true);
        
        // Register AuthService
        $this->register(AuthService::class, AuthServiceImpl::class, true);
        
        // Register QuestionService
        $this->register(QuestionService::class, QuestionServiceImpl::class, true);
        
        // Register ExamResultService
        $this->register(ExamResultService::class, ExamResultServiceImpl::class, true);
    }

    /**
     * Create a service-aware controller
     * 
     * This is a convenience method to inject the service container into controllers
     */
    public function createController(string $controllerClass)
    {
        if (!class_exists($controllerClass)) {
            throw new \Exception("Controller class '{$controllerClass}' does not exist");
        }

        // For now, just create the controller normally
        // In a more advanced setup, you could use reflection to inject dependencies
        return new $controllerClass();
    }
}