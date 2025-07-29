<?php
// Simple authentication test script
require_once __DIR__ . '/vendor/autoload.php';

use App\Models\User;
use App\Services\UserServiceImpl;
use App\Services\AuthServiceImpl;

echo "=== Authentication Test Script ===\n\n";

try {
    // Test 1: Database Connection
    echo "1. Testing Database Connection...\n";
    $user = new User();
    echo "✅ Database connection successful\n\n";

    // Test 2: Check users table
    echo "2. Checking users table...\n";
    $allUsers = $user->getAllUsers();
    echo "Found " . count($allUsers) . " users in database\n";
    
    if (count($allUsers) > 0) {
        echo "Sample users:\n";
        foreach (array_slice($allUsers, 0, 3) as $sampleUser) {
            echo "- {$sampleUser['school_id']} ({$sampleUser['role']}) - Password: {$sampleUser['password']}\n";
        }
    }
    echo "\n";

    // Test 3: Direct authentication test
    echo "3. Testing direct authentication...\n";
    $testCredentials = [
        ['school_id' => 'ADMIN001', 'password' => 'password123'],
        ['school_id' => 'FAC001', 'password' => 'password123'],
        ['school_id' => '2020-001', 'password' => 'password123']
    ];

    foreach ($testCredentials as $cred) {
        echo "Testing: {$cred['school_id']} / {$cred['password']}\n";
        $result = $user->authenticate($cred['school_id'], $cred['password']);
        
        if ($result) {
            echo "✅ Authentication successful for {$cred['school_id']}\n";
            echo "   User data: " . json_encode($result) . "\n";
        } else {
            echo "❌ Authentication failed for {$cred['school_id']}\n";
        }
        echo "\n";
    }

    // Test 4: Service layer test
    echo "4. Testing service layer...\n";
    $userService = new UserServiceImpl();
    
    foreach ($testCredentials as $cred) {
        echo "Testing UserService: {$cred['school_id']} / {$cred['password']}\n";
        $result = $userService->authenticateUser($cred['school_id'], $cred['password']);
        
        if ($result) {
            echo "✅ UserService authentication successful for {$cred['school_id']}\n";
        } else {
            echo "❌ UserService authentication failed for {$cred['school_id']}\n";
        }
        echo "\n";
    }

    // Test 5: AuthService test
    echo "5. Testing AuthService...\n";
    $authService = new AuthServiceImpl($userService);
    
    foreach ($testCredentials as $cred) {
        echo "Testing AuthService: {$cred['school_id']} / {$cred['password']}\n";
        $result = $authService->login($cred['school_id'], $cred['password']);
        
        if ($result) {
            echo "✅ AuthService login successful for {$cred['school_id']}\n";
        } else {
            echo "❌ AuthService login failed for {$cred['school_id']}\n";
        }
        echo "\n";
    }

    // Test 6: ServiceContainer test
    echo "6. Testing ServiceContainer...\n";
    try {
        $container = \App\Services\ServiceContainer::getInstance();
        $authServiceFromContainer = $container->get(\App\Services\AuthService::class);
        echo "✅ ServiceContainer can retrieve AuthService\n";
        
        // Test login through container
        $result = $authServiceFromContainer->login('ADMIN001', 'password123');
        if ($result) {
            echo "✅ AuthService from container works\n";
        } else {
            echo "❌ AuthService from container failed\n";
        }
    } catch (Exception $e) {
        echo "❌ ServiceContainer error: " . $e->getMessage() . "\n";
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}