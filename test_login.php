<?php
/**
 * Test Login Feature
 * 
 * This script tests if the login feature works with the actual database.
 * Run this to verify everything is working correctly.
 */

require_once __DIR__ . '/vendor/autoload.php';

use Service\ServiceContainer;
use Service\Impl\AuthServiceImpl;

echo "🧪 Testing Login Feature with Actual Database\n";
echo "=============================================\n\n";

try {
    // Get AuthService from container
    $serviceContainer = ServiceContainer::getInstance();
    $authService = $serviceContainer->getAuthService();
    
    echo "✅ Service Container and AuthService loaded successfully\n\n";
    
    // Test credentials
    $testUsers = [
        ['school_id' => 'ADMIN001', 'password' => 'password123', 'role' => 'admin'],
        ['school_id' => 'FAC001', 'password' => 'password123', 'role' => 'faculty'],
        ['school_id' => '2020-001', 'password' => 'password123', 'role' => 'student'],
    ];
    
    foreach ($testUsers as $user) {
        echo "🔐 Testing login for: {$user['school_id']} (Role: {$user['role']})\n";
        
        // Test login
        $result = $authService->login($user['school_id'], $user['password']);
        
        if ($result) {
            echo "✅ Login SUCCESSFUL!\n";
            echo "   - School ID: {$result['school_id']}\n";
            echo "   - Full Name: {$result['full_name']}\n";
            echo "   - Role: {$result['role']}\n";
            
            // Test session
            if ($authService->isAuthenticated()) {
                echo "✅ Session created successfully\n";
            } else {
                echo "❌ Session creation failed\n";
            }
            
            // Test role checking
            if ($authService->hasRole($user['role'])) {
                echo "✅ Role verification successful\n";
            } else {
                echo "❌ Role verification failed\n";
            }
            
        } else {
            echo "❌ Login FAILED!\n";
        }
        
        echo "\n";
        
        // Clear session for next test
        $authService->destroySession();
    }
    
    // Test invalid credentials
    echo "🔐 Testing invalid credentials\n";
    $invalidResult = $authService->login('INVALID', 'WRONGPASSWORD');
    if (!$invalidResult) {
        echo "✅ Invalid credentials correctly rejected\n";
    } else {
        echo "❌ Invalid credentials were accepted (this is wrong!)\n";
    }
    
    echo "\n🎉 Login feature test completed!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>