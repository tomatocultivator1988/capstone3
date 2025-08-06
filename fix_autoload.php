<?php
/**
 * Fix Autoloading Issues
 * 
 * This script helps fix autoloading problems.
 */

echo "🔧 Fixing Autoloading Issues\n";
echo "===========================\n\n";

// Check if composer is available
if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    echo "❌ Error: vendor/autoload.php not found!\n";
    echo "Please run: composer install\n";
    exit(1);
}

echo "✅ vendor/autoload.php found\n";

// Load autoloader
require_once __DIR__ . '/vendor/autoload.php';

echo "✅ Autoloader loaded\n\n";

// Test key classes
$classes = [
    'App\Core\Router' => 'src/core/Router.php',
    'App\Controller\AuthController' => 'src/controller/AuthController.php',
    'Service\ServiceContainer' => 'src/service/ServiceContainer.php',
    'Dao\Impl\UserDAOImpl' => 'src/dao/impl/UserDAOImpl.php',
    'Model\User' => 'src/model/User.php'
];

foreach ($classes as $className => $expectedPath) {
    echo "🔍 Testing: $className\n";
    
    // Check if file exists
    if (file_exists($expectedPath)) {
        echo "✅ File exists: $expectedPath\n";
    } else {
        echo "❌ File missing: $expectedPath\n";
        continue;
    }
    
    // Check if class can be loaded
    if (class_exists($className)) {
        echo "✅ Class loaded: $className\n";
    } else {
        echo "❌ Class NOT loaded: $className\n";
        
        // Try to include the file manually
        try {
            require_once $expectedPath;
            if (class_exists($className)) {
                echo "✅ Class loaded after manual include\n";
            } else {
                echo "❌ Class still not found after manual include\n";
            }
        } catch (Exception $e) {
            echo "❌ Error including file: " . $e->getMessage() . "\n";
        }
    }
    echo "\n";
}

// Test Router specifically
echo "🔍 Testing Router instantiation:\n";
try {
    $router = new App\Core\Router();
    echo "✅ Router created successfully\n";
    
    // Test loading routes
    $routesFile = __DIR__ . '/src/config/routes.php';
    if (file_exists($routesFile)) {
        $router->loadRoutes($routesFile);
        echo "✅ Routes loaded successfully\n";
    } else {
        echo "❌ Routes file not found: $routesFile\n";
    }
    
} catch (Exception $e) {
    echo "❌ Router creation failed: " . $e->getMessage() . "\n";
}

echo "\n🎉 Autoloading test completed!\n";
echo "\n💡 If you see errors, try running:\n";
echo "   composer dump-autoload\n";
?>