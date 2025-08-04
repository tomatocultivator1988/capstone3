<?php

/**
 * TDD Test Runner
 * 
 * Comprehensive test runner for all individual test files.
 * Provides detailed reporting and statistics for each test class.
 */

require_once __DIR__ . '/BaseTest.php';

// Include all test files
require_once __DIR__ . '/Unit/Service/UserServiceImplTest.php';
require_once __DIR__ . '/Unit/Service/AuthServiceImplTest.php';
require_once __DIR__ . '/Unit/Model/UserTest.php';
require_once __DIR__ . '/Unit/DAO/UserDAOImplTest.php';

class TDDTestRunner
{
    private int $totalTests = 0;
    private int $passedTests = 0;
    private int $failedTests = 0;
    private array $failures = [];
    private array $testResults = [];
    private float $startTime;

    public function __construct()
    {
        $this->startTime = microtime(true);
    }

    /**
     * Run all TDD tests
     */
    public function runAllTests(): void
    {
        echo "🧪 TDD Unit Tests - Separated by Class\n";
        echo "=" . str_repeat("=", 60) . "\n";
        echo "📅 " . date('Y-m-d H:i:s') . "\n";
        echo "🏗️  Architecture: MVC + DAO + Service\n";
        echo "=" . str_repeat("=", 60) . "\n\n";

        $testClasses = [
            // Model Tests
            'Model Tests' => [
                UserTest::class => 'User Model - Data validation and hydration'
            ],
            
            // Service Tests (Business Logic)
            'Service Tests' => [
                UserServiceImplTest::class => 'UserService - Business logic and validation',
                AuthServiceImplTest::class => 'AuthService - Authentication and authorization'
            ],
            
            // DAO Tests (Data Access)
            'DAO Tests' => [
                UserDAOImplTest::class => 'UserDAO - Database operations'
            ]
        ];

        foreach ($testClasses as $category => $tests) {
            $this->runTestCategory($category, $tests);
        }

        $this->printDetailedSummary();
    }

    /**
     * Run tests for a specific category
     */
    private function runTestCategory(string $category, array $tests): void
    {
        echo "📂 $category\n";
        echo str_repeat("-", 60) . "\n";

        foreach ($tests as $testClass => $description) {
            $this->runTestClass($testClass, $description);
        }
        
        echo "\n";
    }

    /**
     * Run all tests in a specific test class
     */
    private function runTestClass(string $testClass, string $description): void
    {
        echo "📝 $description\n";
        
        $reflection = new ReflectionClass($testClass);
        $testInstance = $reflection->newInstance();
        
        $classResults = [
            'class' => $testClass,
            'description' => $description,
            'tests' => [],
            'passed' => 0,
            'failed' => 0,
            'total' => 0,
            'duration' => 0
        ];
        
        $classStartTime = microtime(true);
        
        $testMethods = [];
        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if (strpos($method->getName(), 'test') === 0) {
                $testMethods[] = $method->getName();
            }
        }
        
        $classResults['total'] = count($testMethods);
        
        foreach ($testMethods as $methodName) {
            $result = $this->runSingleTest($testInstance, $methodName);
            $classResults['tests'][] = $result;
            
            if ($result['status'] === 'passed') {
                $classResults['passed']++;
                echo "  ✅ " . $this->formatTestName($methodName) . "\n";
            } else {
                $classResults['failed']++;
                echo "  ❌ " . $this->formatTestName($methodName) . "\n";
                echo "     💥 " . $result['message'] . "\n";
            }
        }
        
        $classResults['duration'] = microtime(true) - $classStartTime;
        $this->testResults[] = $classResults;
        
        // Class summary
        $successRate = $classResults['total'] > 0 ? 
            round(($classResults['passed'] / $classResults['total']) * 100, 1) : 0;
        
        echo sprintf(
            "   📊 %d/%d passed (%.1f%%) in %.3fs\n\n", 
            $classResults['passed'], 
            $classResults['total'], 
            $successRate,
            $classResults['duration']
        );
    }

    /**
     * Run a single test method
     */
    private function runSingleTest(object $testInstance, string $methodName): array
    {
        $this->totalTests++;
        $testStartTime = microtime(true);

        try {
            // Setup
            if (method_exists($testInstance, 'setUp')) {
                $testInstance->setUp();
            }

            // Run test
            $testInstance->$methodName();

            // Teardown
            if (method_exists($testInstance, 'tearDown')) {
                $testInstance->tearDown();
            }

            $this->passedTests++;
            
            return [
                'test' => $methodName,
                'status' => 'passed',
                'message' => 'Test passed successfully',
                'duration' => microtime(true) - $testStartTime
            ];

        } catch (Exception $e) {
            // Ensure teardown even on failure
            if (method_exists($testInstance, 'tearDown')) {
                try {
                    $testInstance->tearDown();
                } catch (Exception $teardownException) {
                    // Ignore teardown exceptions
                }
            }

            $this->failedTests++;
            $failure = [
                'test' => $methodName,
                'status' => 'failed',
                'message' => $e->getMessage(),
                'duration' => microtime(true) - $testStartTime
            ];
            
            $this->failures[] = $failure;
            return $failure;
        }
    }

    /**
     * Format test method name for display
     */
    private function formatTestName(string $methodName): string
    {
        // Convert testMethodName_WithCondition_ShouldExpect to readable format
        $name = str_replace('test', '', $methodName);
        $name = preg_replace('/([A-Z])/', ' $1', $name);
        $name = str_replace('_', ' → ', $name);
        $name = trim($name);
        
        return $name;
    }

    /**
     * Print detailed test summary and statistics
     */
    private function printDetailedSummary(): void
    {
        $totalDuration = microtime(true) - $this->startTime;
        
        echo "=" . str_repeat("=", 60) . "\n";
        echo "📊 COMPREHENSIVE TDD TEST RESULTS\n";
        echo "=" . str_repeat("=", 60) . "\n";
        
        // Overall statistics
        echo "⏱️  Total Duration: " . sprintf("%.3f seconds", $totalDuration) . "\n";
        echo "📈 Total Tests: {$this->totalTests}\n";
        echo "✅ Passed: {$this->passedTests}\n";
        echo "❌ Failed: {$this->failedTests}\n";
        
        $successRate = $this->totalTests > 0 ? 
            round(($this->passedTests / $this->totalTests) * 100, 2) : 0;
        echo "📊 Success Rate: {$successRate}%\n\n";
        
        // Detailed breakdown by class
        echo "📋 DETAILED BREAKDOWN:\n";
        echo str_repeat("-", 60) . "\n";
        
        foreach ($this->testResults as $classResult) {
            $successRate = $classResult['total'] > 0 ? 
                round(($classResult['passed'] / $classResult['total']) * 100, 1) : 0;
            
            $status = $classResult['failed'] === 0 ? "✅" : "⚠️";
            
            echo sprintf(
                "%s %s\n   📊 %d/%d tests passed (%.1f%%) in %.3fs\n", 
                $status,
                $classResult['description'],
                $classResult['passed'], 
                $classResult['total'], 
                $successRate,
                $classResult['duration']
            );
            
            if ($classResult['failed'] > 0) {
                echo "   🚨 Failed tests:\n";
                foreach ($classResult['tests'] as $test) {
                    if ($test['status'] === 'failed') {
                        echo "      • " . $this->formatTestName($test['test']) . "\n";
                    }
                }
            }
            echo "\n";
        }
        
        // Show failures if any
        if ($this->failedTests > 0) {
            echo "🚨 FAILURE DETAILS:\n";
            echo str_repeat("-", 60) . "\n";
            
            foreach ($this->failures as $index => $failure) {
                echo sprintf(
                    "%d. %s\n   💥 %s\n\n", 
                    $index + 1,
                    $this->formatTestName($failure['test']),
                    $failure['message']
                );
            }
        }
        
        // Architecture quality assessment
        echo "🏗️  ARCHITECTURE QUALITY ASSESSMENT:\n";
        echo str_repeat("-", 60) . "\n";
        
        $this->printArchitectureAssessment($successRate);
        
        // Next steps
        echo "\n🎯 NEXT STEPS:\n";
        echo str_repeat("-", 60) . "\n";
        $this->printNextSteps();
    }

    /**
     * Print architecture quality assessment
     */
    private function printArchitectureAssessment(float $successRate): void
    {
        if ($successRate >= 95) {
            echo "🟢 EXCELLENT: Your MVC+DAO+Service architecture is solid!\n";
            echo "   • All layers are properly tested\n";
            echo "   • Business logic is well separated\n";
            echo "   • Data access is properly abstracted\n";
        } elseif ($successRate >= 80) {
            echo "🟡 GOOD: Your architecture is mostly solid with minor issues.\n";
            echo "   • Core functionality is working\n";
            echo "   • Some edge cases need attention\n";
            echo "   • Continue improving test coverage\n";
        } elseif ($successRate >= 60) {
            echo "🟠 FAIR: Your architecture needs improvement.\n";
            echo "   • Several components have issues\n";
            echo "   • Review failing tests carefully\n";
            echo "   • Focus on fixing core business logic\n";
        } else {
            echo "🔴 NEEDS WORK: Significant issues in your architecture.\n";
            echo "   • Many core components are failing\n";
            echo "   • Review implementation thoroughly\n";
            echo "   • Consider refactoring problem areas\n";
        }
    }

    /**
     * Print recommended next steps
     */
    private function printNextSteps(): void
    {
        if ($this->failedTests === 0) {
            echo "✅ All tests passing! Consider:\n";
            echo "   • Adding integration tests\n";
            echo "   • Testing with real database scenarios\n";
            echo "   • Adding performance tests\n";
            echo "   • Implementing remaining missing components\n";
        } else {
            echo "🔧 Fix failing tests by:\n";
            echo "   • Reviewing error messages above\n";
            echo "   • Checking method implementations\n";
            echo "   • Verifying database connections\n";
            echo "   • Ensuring proper error handling\n";
        }
        
        echo "\n📚 TDD Best Practices Implemented:\n";
        echo "   ✅ Arrange-Act-Assert pattern\n";
        echo "   ✅ Test isolation with setUp/tearDown\n";
        echo "   ✅ Mock dependencies for unit tests\n";
        echo "   ✅ Descriptive test method names\n";
        echo "   ✅ Separate test files per class\n";
        echo "   ✅ Comprehensive edge case testing\n";
    }

    /**
     * Run tests for a specific test class only
     */
    public function runSpecificTest(string $testClassName): void
    {
        echo "🎯 Running Specific Test: $testClassName\n";
        echo "=" . str_repeat("=", 50) . "\n\n";
        
        if (class_exists($testClassName)) {
            $this->runTestClass($testClassName, "Targeted Test: $testClassName");
            $this->printDetailedSummary();
        } else {
            echo "❌ Test class '$testClassName' not found!\n";
            echo "Available test classes:\n";
            echo "  • UserServiceImplTest\n";
            echo "  • AuthServiceImplTest\n";
            echo "  • UserTest\n";
            echo "  • UserDAOImplTest\n";
        }
    }
}

// Command line interface
if (php_sapi_name() === 'cli') {
    $runner = new TDDTestRunner();
    
    if (isset($argv[1])) {
        // Run specific test class
        $testClass = $argv[1];
        $runner->runSpecificTest($testClass);
    } else {
        // Run all tests
        $runner->runAllTests();
    }
} else {
    // Web interface
    echo "<pre>";
    try {
        $runner = new TDDTestRunner();
        $runner->runAllTests();
    } catch (Exception $e) {
        echo "❌ Critical Test Runner Error: " . $e->getMessage() . "\n";
    }
    echo "</pre>";
}