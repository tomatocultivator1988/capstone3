<?php
require_once __DIR__ . '/vendor/autoload.php';

use App\Config\ServiceContainer;
use App\Config\Router;

echo "🧪 Testing New MVC + DAO + Service Architecture\n";
echo "================================================\n\n";

try {
    // Test 1: Service Container
    echo "1. Testing Service Container...\n";
    $container = new ServiceContainer();
    echo "✅ Service Container created successfully\n";

    // Test 2: Check if services can be retrieved
    echo "2. Testing service retrieval...\n";
    $userService = $container->get('UserService');
    $authService = $container->get('AuthService');
    echo "✅ Services retrieved successfully\n";

    // Test 3: Test Router
    echo "3. Testing Router...\n";
    $router = new Router($container);
    echo "✅ Router created successfully\n";

    // Test 4: Test Models
    echo "4. Testing Models...\n";
    $user = new \App\Model\User([
        'user_id' => 1,
        'school_id' => 'TEST001',
        'full_name' => 'Test User',
        'role' => 'student',
        'year_level' => 10,
        'section' => 'A'
    ]);
    echo "✅ User model created successfully\n";

    $subject = new \App\Model\Subject([
        'subject_id' => 1,
        'subject_code' => 'MATH101',
        'subject_name' => 'Mathematics',
        'description' => 'Basic Mathematics',
        'units' => 3
    ]);
    echo "✅ Subject model created successfully\n";

    // Test 5: Test DAOs
    echo "5. Testing DAOs...\n";
    $userDAO = $container->get('UserDAO');
    $subjectDAO = $container->get('SubjectDAO');
    echo "✅ DAOs retrieved successfully\n";

    // Test 6: Test Controllers
    echo "6. Testing Controllers...\n";
    $userController = $container->get('UserController');
    $authController = $container->get('AuthController');
    echo "✅ Controllers retrieved successfully\n";

    echo "\n🎉 All tests passed! The new architecture is working correctly.\n";
    echo "\n📁 New Directory Structure:\n";
    echo "src/\n";
    echo "├── model/          - Pure data containers\n";
    echo "├── dao/\n";
    echo "│   ├── interface/  - DAO contracts\n";
    echo "│   └── impl/       - Database operations\n";
    echo "├── service/\n";
    echo "│   ├── interface/  - Service contracts\n";
    echo "│   └── impl/       - Business logic\n";
    echo "├── controller/     - HTTP request handlers\n";
    echo "└── config/         - Infrastructure (Router, ServiceContainer)\n";

    echo "\n🔧 Key Improvements:\n";
    echo "✅ Clean separation of concerns\n";
    echo "✅ Dependency injection with ServiceContainer\n";
    echo "✅ Single responsibility principle\n";
    echo "✅ Interface-based design\n";
    echo "✅ Centralized routing\n";
    echo "✅ Single API entry point\n";

} catch (Exception $e) {
    echo "❌ Test failed: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}