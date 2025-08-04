<?php

/**
 * Base Test Class
 * 
 * Provides common assertion methods and utilities for all unit tests.
 * Follows TDD best practices with proper setup/teardown.
 */

require_once __DIR__ . '/../vendor/autoload.php';

abstract class BaseTest
{
    /**
     * Setup method called before each test
     */
    protected function setUp(): void
    {
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
    }

    /**
     * Assert that a callback throws an exception
     */
    protected function assertThrows(callable $callback, string $expectedExceptionClass = Exception::class): void
    {
        try {
            $callback();
            throw new Exception("Expected exception $expectedExceptionClass was not thrown");
        } catch (Exception $e) {
            if (!($e instanceof $expectedExceptionClass)) {
                throw new Exception("Expected $expectedExceptionClass but got " . get_class($e) . ": " . $e->getMessage());
            }
        }
    }

    /**
     * Assert that condition is true
     */
    protected function assertTrue(bool $condition, string $message = ""): void
    {
        if (!$condition) {
            throw new Exception("Assertion failed: $message");
        }
    }

    /**
     * Assert that condition is false
     */
    protected function assertFalse(bool $condition, string $message = ""): void
    {
        if ($condition) {
            throw new Exception("Assertion failed: $message");
        }
    }

    /**
     * Assert that two values are equal
     */
    protected function assertEquals($expected, $actual, string $message = ""): void
    {
        if ($expected !== $actual) {
            $expectedStr = is_array($expected) ? json_encode($expected) : (string)$expected;
            $actualStr = is_array($actual) ? json_encode($actual) : (string)$actual;
            throw new Exception("Assertion failed: Expected '$expectedStr', got '$actualStr'. $message");
        }
    }

    /**
     * Assert that value is not null
     */
    protected function assertNotNull($value, string $message = ""): void
    {
        if ($value === null) {
            throw new Exception("Assertion failed: Value should not be null. $message");
        }
    }

    /**
     * Assert that value is null
     */
    protected function assertNull($value, string $message = ""): void
    {
        if ($value !== null) {
            throw new Exception("Assertion failed: Value should be null. $message");
        }
    }

    /**
     * Assert that array contains a specific value
     */
    protected function assertContains($needle, array $haystack, string $message = ""): void
    {
        if (!in_array($needle, $haystack)) {
            throw new Exception("Assertion failed: Array should contain '$needle'. $message");
        }
    }

    /**
     * Assert that array does not contain a specific value
     */
    protected function assertNotContains($needle, array $haystack, string $message = ""): void
    {
        if (in_array($needle, $haystack)) {
            throw new Exception("Assertion failed: Array should not contain '$needle'. $message");
        }
    }

    /**
     * Assert that array is empty
     */
    protected function assertEmpty(array $array, string $message = ""): void
    {
        if (!empty($array)) {
            throw new Exception("Assertion failed: Array should be empty. $message");
        }
    }

    /**
     * Assert that array is not empty
     */
    protected function assertNotEmpty(array $array, string $message = ""): void
    {
        if (empty($array)) {
            throw new Exception("Assertion failed: Array should not be empty. $message");
        }
    }

    /**
     * Assert that value is an instance of a specific class
     */
    protected function assertInstanceOf(string $expectedClass, $actual, string $message = ""): void
    {
        if (!($actual instanceof $expectedClass)) {
            $actualClass = is_object($actual) ? get_class($actual) : gettype($actual);
            throw new Exception("Assertion failed: Expected instance of '$expectedClass', got '$actualClass'. $message");
        }
    }

    /**
     * Assert that count matches expected value
     */
    protected function assertCount(int $expectedCount, $actual, string $message = ""): void
    {
        $actualCount = is_array($actual) ? count($actual) : (is_countable($actual) ? count($actual) : 0);
        if ($actualCount !== $expectedCount) {
            throw new Exception("Assertion failed: Expected count $expectedCount, got $actualCount. $message");
        }
    }

    /**
     * Run a single test method and capture result
     */
    public function runTest(string $methodName): array
    {
        try {
            $this->setUp();
            $this->$methodName();
            $this->tearDown();
            
            return [
                'status' => 'passed',
                'message' => "$methodName passed successfully"
            ];
        } catch (Exception $e) {
            $this->tearDown(); // Ensure cleanup even on failure
            
            return [
                'status' => 'failed',
                'message' => $e->getMessage(),
                'test' => $methodName
            ];
        }
    }
}