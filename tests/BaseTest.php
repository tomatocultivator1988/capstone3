<?php

/**
 * Base Test Class
 * 
 * Provides common setup/teardown methods and custom utilities for all unit tests.
 * Follows TDD best practices with proper setup/teardown.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use PHPUnit\Framework\TestCase;

abstract class BaseTest extends TestCase
{
    /**
     * Setup method called before each test
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // Reset any state before each test
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        
        // Clear any global state
        $_SESSION = [];
        $_POST = [];
        $_GET = [];
    }

    /**
     * Teardown method called after each test
     */
    protected function tearDown(): void
    {
        // Clean up after each test
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        
        // Reset global state
        $_SESSION = [];
        $_POST = [];
        $_GET = [];
        
        parent::tearDown();
    }

    /**
     * Assert that a callback throws an exception
     */
    protected function assertThrows(callable $callback, string $expectedExceptionClass = Exception::class): void
    {
        try {
            $callback();
            $this->fail("Expected exception $expectedExceptionClass was not thrown");
        } catch (Exception $e) {
            if (!($e instanceof $expectedExceptionClass)) {
                $this->fail("Expected $expectedExceptionClass but got " . get_class($e) . ": " . $e->getMessage());
            }
        }
    }

    /**
     * Helper method to create a test user with default values
     */
    protected function createTestUser(array $overrides = []): array
    {
        $defaults = [
            'school_id' => 'TEST_' . uniqid(),
            'full_name' => 'Test User',
            'password' => password_hash('testpass', PASSWORD_DEFAULT),
            'role' => 'student',
            'year_level' => 1,
            'section' => 'A'
        ];
        
        return array_merge($defaults, $overrides);
    }

    /**
     * Helper method to create a test faculty user
     */
    protected function createTestFacultyUser(array $overrides = []): array
    {
        $defaults = [
            'school_id' => 'FAC_' . uniqid(),
            'full_name' => 'Test Faculty',
            'password' => password_hash('testpass', PASSWORD_DEFAULT),
            'role' => 'faculty'
        ];
        
        return array_merge($defaults, $overrides);
    }
}