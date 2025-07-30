<?php
// Debug script to test the API endpoint
echo "=== API Debug Test ===\n\n";

// Test 1: Check if we can access the API file
echo "1. Testing API file accessibility...\n";
$apiFile = __DIR__ . '/api/auth/login.php';
if (file_exists($apiFile)) {
    echo "✅ API file exists: $apiFile\n";
} else {
    echo "❌ API file not found: $apiFile\n";
    exit;
}

// Test 2: Check autoloader
echo "\n2. Testing autoloader...\n";
try {
    require_once __DIR__ . '/vendor/autoload.php';
    echo "✅ Autoloader loaded successfully\n";
} catch (Exception $e) {
    echo "❌ Autoloader error: " . $e->getMessage() . "\n";
    exit;
}

// Test 3: Check if AuthController exists
echo "\n3. Testing AuthController...\n";
try {
    $controller = new \App\Controllers\AuthController();
    echo "✅ AuthController instantiated successfully\n";
} catch (Exception $e) {
    echo "❌ AuthController error: " . $e->getMessage() . "\n";
    exit;
}

// Test 4: Check database connection
echo "\n4. Testing database connection...\n";
try {
    $db = \App\Config\Database::getInstance();
    $connection = $db->getConnection();
    echo "✅ Database connection successful\n";
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    exit;
}

// Test 5: Check if users exist
echo "\n5. Testing users table...\n";
try {
    $user = new \App\Models\User();
    $allUsers = $user->getAllUsers();
    echo "✅ Found " . count($allUsers) . " users in database\n";
    
    if (count($allUsers) > 0) {
        echo "Sample users:\n";
        foreach (array_slice($allUsers, 0, 3) as $sampleUser) {
            echo "- {$sampleUser['school_id']} ({$sampleUser['role']})\n";
        }
    }
} catch (Exception $e) {
    echo "❌ Users table error: " . $e->getMessage() . "\n";
}

// Test 6: Test authentication directly
echo "\n6. Testing authentication directly...\n";
try {
    $user = new \App\Models\User();
    $result = $user->authenticate('ADMIN001', 'password123');
    
    if ($result) {
        echo "✅ Direct authentication successful\n";
        echo "User data: " . json_encode($result) . "\n";
    } else {
        echo "❌ Direct authentication failed\n";
    }
} catch (Exception $e) {
    echo "❌ Authentication error: " . $e->getMessage() . "\n";
}

// Test 7: Test service layer
echo "\n7. Testing service layer...\n";
try {
    $userService = new \App\Services\UserServiceImpl();
    $result = $userService->authenticateUser('ADMIN001', 'password123');
    
    if ($result) {
        echo "✅ UserService authentication successful\n";
    } else {
        echo "❌ UserService authentication failed\n";
    }
} catch (Exception $e) {
    echo "❌ UserService error: " . $e->getMessage() . "\n";
}

// Test 8: Test AuthService
echo "\n8. Testing AuthService...\n";
try {
    $authService = new \App\Services\AuthServiceImpl();
    $result = $authService->login('ADMIN001', 'password123');
    
    if ($result) {
        echo "✅ AuthService login successful\n";
    } else {
        echo "❌ AuthService login failed\n";
    }
} catch (Exception $e) {
    echo "❌ AuthService error: " . $e->getMessage() . "\n";
}

echo "\n=== Debug Complete ===\n";