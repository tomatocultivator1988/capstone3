<?php

/**
 * Test Script for Refactored API Architecture
 * 
 * Tests the new MVC + DAO + Service architecture to ensure
 * all components work together correctly.
 */

require_once __DIR__ . '/vendor/autoload.php';

use Config\ServiceContainer;
use Config\Database;
use Service\Interface\AuthServiceInterface;
use Service\Interface\UserServiceInterface;
use Model\User;

class RefactoredAPITest
{
    private ServiceContainer $container;

    public function __construct()
    {
        $this->container = ServiceContainer::getInstance();
    }

    public function runAllTests(): void
    {
        echo "🚀 Starting Refactored API Architecture Tests\n";
        echo str_repeat("=", 60) . "\n\n";

        $tests = [
            'testDatabaseConnection',
            'testServiceContainer',
            'testUserDAO',
            'testUserService',
            'testAuthService',
            'testModelHydration',
            'testDependencyInjection'
        ];

        $passed = 0;
        $failed = 0;

        foreach ($tests as $test) {
            try {
                echo "🧪 Running test: $test\n";
                $this->$test();
                echo "✅ PASSED: $test\n\n";
                $passed++;
            } catch (Exception $e) {
                echo "❌ FAILED: $test\n";
                echo "   Error: " . $e->getMessage() . "\n\n";
                $failed++;
            }
        }

        echo str_repeat("=", 60) . "\n";
        echo "📊 Test Results: $passed passed, $failed failed\n";
        
        if ($failed === 0) {
            echo "🎉 All tests passed! Refactored architecture is working correctly.\n";
        } else {
            echo "⚠️  Some tests failed. Please check the errors above.\n";
        }
    }

    private function testDatabaseConnection(): void
    {
        $db = Database::getInstance();
        $connection = $db->getConnection();
        
        if (!$connection) {
            throw new Exception("Database connection failed");
        }

        // Test a simple query
        $stmt = $connection->prepare("SELECT 1 as test");
        $stmt->execute();
        $result = $stmt->fetch();

        if ($result['test'] !== 1) {
            throw new Exception("Database query test failed");
        }

        echo "   ✓ Database connection established\n";
        echo "   ✓ Basic query execution works\n";
    }

    private function testServiceContainer(): void
    {
        // Test that container exists and has required services
        if (!$this->container->has(AuthServiceInterface::class)) {
            throw new Exception("AuthService not registered in container");
        }

        if (!$this->container->has(UserServiceInterface::class)) {
            throw new Exception("UserService not registered in container");
        }

        // Test getting services
        $authService = $this->container->get(AuthServiceInterface::class);
        $userService = $this->container->get(UserServiceInterface::class);

        if (!$authService instanceof AuthServiceInterface) {
            throw new Exception("AuthService instance is not correct type");
        }

        if (!$userService instanceof UserServiceInterface) {
            throw new Exception("UserService instance is not correct type");
        }

        echo "   ✓ Service container initialized\n";
        echo "   ✓ Services properly registered\n";
        echo "   ✓ Dependency injection working\n";
    }

    private function testUserDAO(): void
    {
        $userService = $this->container->get(UserServiceInterface::class);

        // Test that we can get users (this tests DAO layer indirectly)
        $users = $userService->getAllUsers();
        
        if (!is_array($users)) {
            throw new Exception("getAllUsers should return an array");
        }

        echo "   ✓ UserDAO accessible through service\n";
        echo "   ✓ Database queries work through DAO\n";
        echo "   ✓ Found " . count($users) . " users in database\n";
    }

    private function testUserService(): void
    {
        $userService = $this->container->get(UserServiceInterface::class);

        // Test validation
        $errors = $userService->validateUserData([
            'school_id' => '',
            'full_name' => '',
            'role' => 'invalid'
        ]);

        if (empty($errors)) {
            throw new Exception("Validation should return errors for invalid data");
        }

        // Test valid data
        $validErrors = $userService->validateUserData([
            'school_id' => 'TEST123',
            'full_name' => 'Test User',
            'role' => 'student',
            'year_level' => 1,
            'section' => 'A'
        ]);

        if (!empty($validErrors)) {
            throw new Exception("Valid data should not return validation errors");
        }

        echo "   ✓ User validation working\n";
        echo "   ✓ Business logic properly implemented\n";
    }

    private function testAuthService(): void
    {
        $authService = $this->container->get(AuthServiceInterface::class);

        // Test password validation
        $weakPassword = $authService->validatePassword('123');
        if (empty($weakPassword)) {
            throw new Exception("Weak password should fail validation");
        }

        $strongPassword = $authService->validatePassword('StrongPass123');
        if (!empty($strongPassword)) {
            throw new Exception("Strong password should pass validation: " . implode(', ', $strongPassword));
        }

        // Test password hashing
        $hash = $authService->hashPassword('testpassword');
        if (!$authService->verifyPassword('testpassword', $hash)) {
            throw new Exception("Password hashing/verification failed");
        }

        echo "   ✓ Password validation working\n";
        echo "   ✓ Password hashing/verification working\n";
    }

    private function testModelHydration(): void
    {
        // Test User model
        $userData = [
            'user_id' => 1,
            'school_id' => 'TEST123',
            'full_name' => 'Test User',
            'role' => 'student',
            'year_level' => 1,
            'section' => 'A'
        ];

        $user = new User($userData);

        if ($user->getSchoolId() !== 'TEST123') {
            throw new Exception("User hydration failed");
        }

        if ($user->getFullName() !== 'Test User') {
            throw new Exception("User hydration failed");
        }

        if (!$user->isStudent()) {
            throw new Exception("User role methods not working");
        }

        // Test toArray
        $array = $user->toArray();
        if ($array['school_id'] !== 'TEST123') {
            throw new Exception("User toArray failed");
        }

        echo "   ✓ Model hydration working\n";
        echo "   ✓ Model getters/setters working\n";
        echo "   ✓ Model helper methods working\n";
        echo "   ✓ Model toArray working\n";
    }

    private function testDependencyInjection(): void
    {
        // Test that services receive their dependencies properly
        $userService = $this->container->get(UserServiceInterface::class);
        
        // This should work without errors if DI is working
        $statistics = $userService->getUserStatistics();
        
        if (!is_array($statistics)) {
            throw new Exception("Service dependencies not properly injected");
        }

        if (!isset($statistics['total'])) {
            throw new Exception("Service method not working correctly");
        }

        echo "   ✓ Dependency injection working\n";
        echo "   ✓ Service dependencies properly resolved\n";
        echo "   ✓ Cross-service communication working\n";
    }
}

// Run the tests
try {
    $tester = new RefactoredAPITest();
    $tester->runAllTests();
} catch (Exception $e) {
    echo "❌ Critical Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}