<?php

namespace Config;

use Service\Interface\UserServiceInterface;
use Service\Interface\AuthServiceInterface;
use Service\Impl\UserServiceImpl;
use Service\Impl\AuthServiceImpl;
use Dao\Interface\UserDAOInterface;
use Dao\Interface\SubjectDAOInterface;
use Dao\Interface\ExamDAOInterface;
use Dao\Interface\QuestionDAOInterface;
use Dao\Interface\ExamResultDAOInterface;
use Dao\Impl\UserDAOImpl;
use Dao\Impl\SubjectDAOImpl;
use Dao\Impl\ExamDAOImpl;
use Exception;

/**
 * Service Container for Dependency Injection
 * 
 * Manages creation and lifecycle of services and DAOs.
 * Implements singleton pattern with lazy loading.
 */
class ServiceContainer
{
    private static ?ServiceContainer $instance = null;
    private array $services = [];
    private array $singletons = [];

    private function __construct()
    {
        // Register service bindings
        $this->registerBindings();
    }

    public static function getInstance(): ServiceContainer
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Register service and DAO bindings
     */
    private function registerBindings(): void
    {
        // DAO bindings
        $this->bind(UserDAOInterface::class, UserDAOImpl::class);
        $this->bind(SubjectDAOInterface::class, SubjectDAOImpl::class);
        $this->bind(ExamDAOInterface::class, ExamDAOImpl::class);
        // Add other DAO bindings as needed

        // Service bindings  
        $this->bind(UserServiceInterface::class, UserServiceImpl::class);
        $this->bind(AuthServiceInterface::class, AuthServiceImpl::class);
        // Add other service bindings as needed
    }

    /**
     * Bind interface to implementation
     */
    public function bind(string $interface, string $implementation): void
    {
        $this->services[$interface] = $implementation;
    }

    /**
     * Register a singleton instance
     */
    public function singleton(string $interface, $instance): void
    {
        $this->singletons[$interface] = $instance;
    }

    /**
     * Get service instance
     */
    public function get(string $interface)
    {
        // Return singleton if exists
        if (isset($this->singletons[$interface])) {
            return $this->singletons[$interface];
        }

        // Check if binding exists
        if (!isset($this->services[$interface])) {
            throw new Exception("Service binding not found for: $interface");
        }

        $implementation = $this->services[$interface];

        // Create instance with dependency injection
        return $this->createInstance($implementation);
    }

    /**
     * Create instance with dependency injection
     */
    private function createInstance(string $className)
    {
        try {
            $reflection = new \ReflectionClass($className);
            $constructor = $reflection->getConstructor();

            if ($constructor === null) {
                // No constructor, create simple instance
                return new $className();
            }

            $parameters = $constructor->getParameters();
            $dependencies = [];

            foreach ($parameters as $parameter) {
                $type = $parameter->getType();
                
                if ($type === null) {
                    // No type hint, pass null if optional
                    if ($parameter->isOptional()) {
                        $dependencies[] = $parameter->getDefaultValue();
                    } else {
                        $dependencies[] = null;
                    }
                    continue;
                }

                $typeName = $type->getName();

                // Check if it's a class/interface we can resolve
                if (class_exists($typeName) || interface_exists($typeName)) {
                    try {
                        $dependencies[] = $this->get($typeName);
                    } catch (Exception $e) {
                        // If we can't resolve it and it's optional, use default
                        if ($parameter->isOptional()) {
                            $dependencies[] = $parameter->getDefaultValue();
                        } else {
                            // Create instance directly if no binding exists
                            $dependencies[] = new $typeName();
                        }
                    }
                } else {
                    // Primitive type, use default if available
                    if ($parameter->isOptional()) {
                        $dependencies[] = $parameter->getDefaultValue();
                    } else {
                        $dependencies[] = null;
                    }
                }
            }

            return $reflection->newInstanceArgs($dependencies);
        } catch (Exception $e) {
            error_log("ServiceContainer::createInstance error for $className: " . $e->getMessage());
            throw new Exception("Failed to create instance of $className: " . $e->getMessage());
        }
    }

    /**
     * Check if service is bound
     */
    public function has(string $interface): bool
    {
        return isset($this->services[$interface]) || isset($this->singletons[$interface]);
    }

    /**
     * Remove binding
     */
    public function unbind(string $interface): void
    {
        unset($this->services[$interface]);
        unset($this->singletons[$interface]);
    }

    /**
     * Get all registered services
     */
    public function getRegisteredServices(): array
    {
        return array_keys($this->services);
    }

    /**
     * Get all singleton instances
     */
    public function getSingletonInstances(): array
    {
        return array_keys($this->singletons);
    }

    /**
     * Clear all bindings and singletons
     */
    public function clear(): void
    {
        $this->services = [];
        $this->singletons = [];
        $this->registerBindings();
    }

    // Prevent cloning and unserialization
    private function __clone() {}
    public function __wakeup() {}
}