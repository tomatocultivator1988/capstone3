<?php
// Test API endpoints for admin dashboard
echo "=== API Endpoints Test ===\n\n";

// Test 1: Check if we can access the API files
echo "1. Testing API file accessibility...\n";
$apiFiles = [
    'api/users/index.php',
    'api/subjects/index.php', 
    'api/exams/index.php'
];

foreach ($apiFiles as $file) {
    if (file_exists($file)) {
        echo "✅ $file exists\n";
    } else {
        echo "❌ $file not found\n";
    }
}
echo "\n";

// Test 2: Check if controllers exist
echo "2. Testing controller classes...\n";
try {
    require_once __DIR__ . '/vendor/autoload.php';
    
    $controllers = [
        'App\Controllers\UserController',
        'App\Controllers\SubjectController', 
        'App\Controllers\ExamController'
    ];
    
    foreach ($controllers as $controller) {
        if (class_exists($controller)) {
            echo "✅ $controller exists\n";
        } else {
            echo "❌ $controller not found\n";
        }
    }
} catch (Exception $e) {
    echo "❌ Error loading controllers: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 3: Test UserService directly
echo "3. Testing UserService directly...\n";
try {
    $userService = new \App\Services\UserServiceImpl();
    $users = $userService->getAllUsers();
    echo "✅ UserService::getAllUsers() returned " . count($users) . " users\n";
    
    if (count($users) > 0) {
        echo "Sample users:\n";
        foreach (array_slice($users, 0, 3) as $user) {
            echo "- {$user['school_id']} ({$user['role']})\n";
        }
    }
} catch (Exception $e) {
    echo "❌ UserService error: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 4: Test AuthService role checking
echo "4. Testing AuthService role checking...\n";
try {
    // Start session and set admin role
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $_SESSION['user_id'] = 1;
    $_SESSION['role'] = 'admin';
    $_SESSION['school_id'] = 'ADMIN001';
    
    $authService = new \App\Services\AuthServiceImpl();
    
    // Test if user has admin role
    $hasRole = $authService->hasRole('admin');
    echo "Has admin role: " . ($hasRole ? '✅ Yes' : '❌ No') . "\n";
    
    // Test requireRole (should not throw exception)
    try {
        $authService->requireRole('admin');
        echo "✅ requireRole('admin') passed\n";
    } catch (Exception $e) {
        echo "❌ requireRole('admin') failed: " . $e->getMessage() . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ AuthService error: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 5: Test UserController with proper session
echo "5. Testing UserController with session...\n";
try {
    // Ensure session is set up
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $_SESSION['user_id'] = 1;
    $_SESSION['role'] = 'admin';
    $_SESSION['school_id'] = 'ADMIN001';
    
    $userController = new \App\Controllers\UserController();
    
    // Capture output
    ob_start();
    $userController->index();
    $output = ob_get_clean();
    
    echo "✅ UserController::index() executed\n";
    echo "Output: $output\n";
    
} catch (Exception $e) {
    echo "❌ UserController error: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";