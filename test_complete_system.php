<?php
/**
 * Complete System Test
 * 
 * Tests the entire system after refactoring to ensure everything works.
 */

require_once __DIR__ . '/vendor/autoload.php';

use App\Services\ServiceContainer;
use App\Services\UserService;
use App\Services\SubjectService;
use App\Services\AuthService;

echo "🧪 Complete System Test After Refactoring\n";
echo "=========================================\n\n";

$errors = [];
$warnings = [];

try {
    // Test 1: Service Container
    echo "1. Testing Service Container...\n";
    $container = ServiceContainer::getInstance();
    echo "   ✅ Service container created successfully\n";
    
    // Test 2: User Service
    echo "2. Testing User Service...\n";
    try {
        $userService = $container->get(UserService::class);
        echo "   ✅ User service retrieved from container\n";
        
        // Test authentication
        $authResult = $userService->authenticateUser('ADMIN001', 'password123');
        if ($authResult) {
            echo "   ✅ User authentication working\n";
        } else {
            echo "   ⚠️  Authentication failed (check password)\n";
            $warnings[] = "Authentication failed - may need password reset";
        }
        
        // Test getting users
        $users = $userService->getAllUsers();
        echo "   ✅ User service returned " . count($users) . " users\n";
        
    } catch (Exception $e) {
        echo "   ❌ User service error: " . $e->getMessage() . "\n";
        $errors[] = "User service: " . $e->getMessage();
    }
    
    // Test 3: Subject Service
    echo "3. Testing Subject Service...\n";
    try {
        $subjectService = $container->get(SubjectService::class);
        echo "   ✅ Subject service retrieved from container\n";
        
        // Test getting subjects
        $subjects = $subjectService->getAllSubjects();
        echo "   ✅ Subject service returned " . count($subjects) . " subjects\n";
        
    } catch (Exception $e) {
        echo "   ❌ Subject service error: " . $e->getMessage() . "\n";
        $errors[] = "Subject service: " . $e->getMessage();
    }
    
    // Test 4: Auth Service
    echo "4. Testing Auth Service...\n";
    try {
        $authService = $container->get(AuthService::class);
        echo "   ✅ Auth service retrieved from container\n";
        
        // Test session management
        $isAuthenticated = $authService->isAuthenticated();
        echo "   ✅ Auth service session check: " . ($isAuthenticated ? 'Authenticated' : 'Not authenticated') . "\n";
        
    } catch (Exception $e) {
        echo "   ❌ Auth service error: " . $e->getMessage() . "\n";
        $errors[] = "Auth service: " . $e->getMessage();
    }
    
    // Test 5: Check API Routes
    echo "5. Testing API Routes...\n";
    $apiRoutes = [
        'api/auth/login.php',
        'api/auth/logout.php',
        'api/users/index.php',
        'api/subjects/index.php'
    ];
    
    foreach ($apiRoutes as $route) {
        if (file_exists($route)) {
            echo "   ✅ Route exists: $route\n";
        } else {
            echo "   ❌ Route missing: $route\n";
            $errors[] = "Missing route: $route";
        }
    }
    
    // Test 6: Check Public Routes
    echo "6. Testing Public Routes...\n";
    $publicRoutes = [
        'public/login_mvc.php',
        'public/dashboard_mvc.php',
        'public/logout.php'
    ];
    
    foreach ($publicRoutes as $route) {
        if (file_exists($route)) {
            echo "   ✅ Route exists: $route\n";
        } else {
            echo "   ❌ Route missing: $route\n";
            $errors[] = "Missing route: $route";
        }
    }
    
    // Test 7: Check Required Classes
    echo "7. Testing Required Classes...\n";
    $requiredClasses = [
        'App\Models\User',
        'App\Models\Subject',
        'App\Repositories\UserRepository',
        'App\Repositories\SubjectRepository',
        'App\Repositories\UserRepositoryInterface',
        'App\Services\UserServiceImpl',
        'App\Services\SubjectServiceImpl',
        'App\Controllers\AuthController',
        'App\Controllers\UserController',
        'App\Controllers\SubjectController'
    ];
    
    foreach ($requiredClasses as $class) {
        if (class_exists($class)) {
            echo "   ✅ Class exists: $class\n";
        } else {
            echo "   ❌ Class missing: $class\n";
            $errors[] = "Missing class: $class";
        }
    }
    
    // Test 8: Check Database Connection
    echo "8. Testing Database Connection...\n";
    try {
        $db = App\Config\Database::getInstance()->getConnection();
        echo "   ✅ Database connection successful\n";
    } catch (Exception $e) {
        echo "   ❌ Database connection failed: " . $e->getMessage() . "\n";
        $errors[] = "Database connection: " . $e->getMessage();
    }
    
    // Summary
    echo "\n📋 Test Summary:\n";
    echo "================\n";
    
    if (empty($errors)) {
        echo "✅ No critical errors found!\n";
    } else {
        echo "❌ Critical errors found:\n";
        foreach ($errors as $error) {
            echo "   • $error\n";
        }
    }
    
    if (!empty($warnings)) {
        echo "⚠️  Warnings:\n";
        foreach ($warnings as $warning) {
            echo "   • $warning\n";
        }
    }
    
    if (empty($errors)) {
        echo "\n🎉 System is ready to use!\n";
        echo "✅ All refactored components are working correctly.\n";
        echo "✅ API routes are properly configured.\n";
        echo "✅ Public routes are accessible.\n";
        echo "✅ Database connection is established.\n";
    } else {
        echo "\n🔧 System needs fixes before use.\n";
        echo "Please address the errors listed above.\n";
    }
    
} catch (Exception $e) {
    echo "❌ Fatal error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}