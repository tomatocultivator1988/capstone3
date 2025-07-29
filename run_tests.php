<?php

/**
 * Test Runner Script
 * 
 * This script verifies that all service tests can be loaded and run properly.
 * It checks for class compatibility and method signatures.
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Autoload classes (simulate Composer autoloading)
spl_autoload_register(function ($class) {
    // Convert namespace to file path
    $file = str_replace(['App\\', '\\'], ['src/App/', '/'], $class) . '.php';
    
    if (file_exists($file)) {
        require_once $file;
        return true;
    }
    
    // Handle test classes
    if (strpos($class, 'Test') !== false) {
        $file = 'tests/unit/' . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return true;
        }
    }
    
    return false;
});

// Start session for auth tests
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

echo "🧪 Service Test Compatibility Checker\n";
echo "=====================================\n\n";

$testResults = [];

/**
 * Test 1: Check if all service classes can be loaded
 */
echo "📋 Test 1: Loading Service Classes...\n";
$serviceClasses = [
    'App\\Services\\UserService',
    'App\\Services\\UserServiceImpl',
    'App\\Services\\AuthService', 
    'App\\Services\\AuthServiceImpl',
    'App\\Services\\ExamService',
    'App\\Services\\ExamServiceImpl',
    'App\\Services\\SubjectService',
    'App\\Services\\SubjectServiceImpl',
    'App\\Services\\QuestionService',
    'App\\Services\\QuestionServiceImpl',
    'App\\Services\\ExamResultService',
    'App\\Services\\ExamResultServiceImpl',
    'App\\Services\\ServiceContainer'
];

foreach ($serviceClasses as $class) {
    try {
        if (class_exists($class) || interface_exists($class)) {
            echo "  ✅ $class - Loaded successfully\n";
            $testResults['service_loading'][] = ['class' => $class, 'status' => 'success'];
        } else {
            echo "  ❌ $class - Failed to load\n";
            $testResults['service_loading'][] = ['class' => $class, 'status' => 'failed'];
        }
    } catch (Exception $e) {
        echo "  ❌ $class - Error: " . $e->getMessage() . "\n";
        $testResults['service_loading'][] = ['class' => $class, 'status' => 'error', 'message' => $e->getMessage()];
    }
}

/**
 * Test 2: Check if model classes can be loaded
 */
echo "\n📋 Test 2: Loading Model Classes...\n";
$modelClasses = [
    'App\\Models\\User',
    'App\\Models\\Exam',
    'App\\Models\\Subject',
    'App\\Models\\Question',
    'App\\Models\\ExamResult'
];

foreach ($modelClasses as $class) {
    try {
        if (class_exists($class)) {
            echo "  ✅ $class - Loaded successfully\n";
            $testResults['model_loading'][] = ['class' => $class, 'status' => 'success'];
        } else {
            echo "  ❌ $class - Failed to load\n";
            $testResults['model_loading'][] = ['class' => $class, 'status' => 'failed'];
        }
    } catch (Exception $e) {
        echo "  ❌ $class - Error: " . $e->getMessage() . "\n";
        $testResults['model_loading'][] = ['class' => $class, 'status' => 'error', 'message' => $e->getMessage()];
    }
}

/**
 * Test 3: Check service instantiation
 */
echo "\n📋 Test 3: Testing Service Instantiation...\n";

try {
    // Test UserService with mock
    $mockUser = new class {
        public function create($data) { return 123; }
        public function findBySchoolId($id) { return false; }
        public function findById($id) { return ['user_id' => 1]; }
        public function getAllUsers() { return []; }
        public function getUsersByRole($role) { return []; }
        public function authenticate($id, $pass) { return ['user_id' => 1]; }
        public function update($id, $data) { return true; }
        public function delete($id) { return true; }
    };
    
    $userService = new App\Services\UserServiceImpl($mockUser);
    echo "  ✅ UserServiceImpl - Instantiated successfully\n";
    $testResults['instantiation'][] = ['service' => 'UserServiceImpl', 'status' => 'success'];
} catch (Exception $e) {
    echo "  ❌ UserServiceImpl - Error: " . $e->getMessage() . "\n";
    $testResults['instantiation'][] = ['service' => 'UserServiceImpl', 'status' => 'error', 'message' => $e->getMessage()];
}

try {
    // Test AuthService with mock UserService
    $mockUserService = new class {
        public function authenticateUser($id, $pass) { return ['user_id' => 1]; }
    };
    
    $authService = new App\Services\AuthServiceImpl($mockUserService);
    echo "  ✅ AuthServiceImpl - Instantiated successfully\n";
    $testResults['instantiation'][] = ['service' => 'AuthServiceImpl', 'status' => 'success'];
} catch (Exception $e) {
    echo "  ❌ AuthServiceImpl - Error: " . $e->getMessage() . "\n";
    $testResults['instantiation'][] = ['service' => 'AuthServiceImpl', 'status' => 'error', 'message' => $e->getMessage()];
}

/**
 * Test 4: Check ServiceContainer
 */
echo "\n📋 Test 4: Testing ServiceContainer...\n";

try {
    $container = App\Services\ServiceContainer::getInstance();
    echo "  ✅ ServiceContainer - Singleton created successfully\n";
    $testResults['container'][] = ['test' => 'singleton', 'status' => 'success'];
} catch (Exception $e) {
    echo "  ❌ ServiceContainer - Error: " . $e->getMessage() . "\n";
    $testResults['container'][] = ['test' => 'singleton', 'status' => 'error', 'message' => $e->getMessage()];
}

/**
 * Test 5: Basic method testing
 */
echo "\n📋 Test 5: Testing Basic Service Methods...\n";

try {
    // Test UserService validation
    $mockUser = new class {
        public function create($data) { return 123; }
        public function findBySchoolId($id) { return false; }
        public function findById($id) { return ['user_id' => 1]; }
        public function getAllUsers() { return []; }
        public function getUsersByRole($role) { return []; }
        public function authenticate($id, $pass) { return ['user_id' => 1]; }
        public function update($id, $data) { return true; }
        public function delete($id) { return true; }
    };
    
    $userService = new App\Services\UserServiceImpl($mockUser);
    
    // Test validation method
    $validData = [
        'school_id' => '2024001',
        'full_name' => 'Test User',
        'role' => 'student',
        'year_level' => 10,
        'section' => 'A'
    ];
    
    $errors = $userService->validateUserData($validData);
    if (is_array($errors)) {
        echo "  ✅ UserService::validateUserData - Returns array\n";
        $testResults['methods'][] = ['method' => 'validateUserData', 'status' => 'success'];
    } else {
        echo "  ❌ UserService::validateUserData - Invalid return type\n";
        $testResults['methods'][] = ['method' => 'validateUserData', 'status' => 'failed'];
    }
    
} catch (Exception $e) {
    echo "  ❌ UserService methods - Error: " . $e->getMessage() . "\n";
    $testResults['methods'][] = ['method' => 'UserService', 'status' => 'error', 'message' => $e->getMessage()];
}

/**
 * Summary
 */
echo "\n📊 Test Summary:\n";
echo "================\n";

$totalTests = 0;
$passedTests = 0;
$failedTests = 0;

foreach ($testResults as $category => $tests) {
    $categoryPassed = 0;
    $categoryTotal = count($tests);
    
    foreach ($tests as $test) {
        $totalTests++;
        if ($test['status'] === 'success') {
            $passedTests++;
            $categoryPassed++;
        } else {
            $failedTests++;
        }
    }
    
    echo sprintf("  %s: %d/%d passed\n", ucfirst(str_replace('_', ' ', $category)), $categoryPassed, $categoryTotal);
}

echo "\n🎯 Overall Results:\n";
echo sprintf("  Total Tests: %d\n", $totalTests);
echo sprintf("  ✅ Passed: %d\n", $passedTests);
echo sprintf("  ❌ Failed: %d\n", $failedTests);
echo sprintf("  Success Rate: %.1f%%\n", ($passedTests / $totalTests) * 100);

if ($failedTests === 0) {
    echo "\n🎉 ALL TESTS PASSED! Your services are ready for unit testing!\n";
    exit(0);
} else {
    echo "\n⚠️  Some tests failed. Check the errors above.\n";
    exit(1);
}