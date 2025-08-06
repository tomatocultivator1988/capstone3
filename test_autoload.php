<?php
/**
 * Test Autoloading
 * 
 * This script tests if the autoloader is working correctly.
 */

echo "🧪 Testing Autoloader\n";
echo "====================\n\n";

// Load autoloader
require_once __DIR__ . '/vendor/autoload.php';

echo "✅ Autoloader loaded\n\n";

// Test if classes can be found
$classesToTest = [
    'App\Core\Router',
    'App\Controller\AuthController',
    'App\Controller\DashboardController',
    'Service\ServiceContainer',
    'Service\Impl\AuthServiceImpl',
    'Dao\Impl\UserDAOImpl',
    'Model\User'
];

foreach ($classesToTest as $className) {
    echo "🔍 Testing: $className\n";
    
    if (class_exists($className)) {
        echo "✅ Class found: $className\n";
    } else {
        echo "❌ Class NOT found: $className\n";
        
        // Try to find the file manually
        $filePath = str_replace('\\', '/', $className) . '.php';
        $possiblePaths = [
            __DIR__ . '/src/' . $filePath,
            __DIR__ . '/src/' . str_replace('App/', '', $filePath),
        ];
        
        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                echo "   📁 File exists at: $path\n";
            } else {
                echo "   📁 File NOT found at: $path\n";
            }
        }
    }
    echo "\n";
}

// Test Router specifically
echo "🔍 Testing Router instantiation:\n";
try {
    $router = new App\Core\Router();
    echo "✅ Router created successfully\n";
} catch (Exception $e) {
    echo "❌ Router creation failed: " . $e->getMessage() . "\n";
}

echo "\n🎉 Autoloader test completed!\n";
?>