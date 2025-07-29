<?php
// Test the complete flow from login to dashboard
echo "=== Login to Dashboard Flow Test ===\n\n";

require_once __DIR__ . '/vendor/autoload.php';

use App\Services\AuthServiceImpl;
use App\Services\UserServiceImpl;
use App\Controllers\UserController;

try {
    // Step 1: Clear any existing session
    echo "1. Clearing existing session...\n";
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }
    session_start();
    echo "✅ Session cleared and started\n\n";
    
    // Step 2: Test login
    echo "2. Testing login...\n";
    $authService = new AuthServiceImpl();
    $user = $authService->login('ADMIN001', 'password123');
    
    if ($user) {
        echo "✅ Login successful\n";
        echo "User data: " . json_encode($user) . "\n";
    } else {
        echo "❌ Login failed\n";
        exit;
    }
    echo "\n";
    
    // Step 3: Check session after login
    echo "3. Checking session after login...\n";
    if (isset($_SESSION) && !empty($_SESSION)) {
        foreach ($_SESSION as $key => $value) {
            echo "- $key: " . (is_array($value) ? json_encode($value) : $value) . "\n";
        }
    } else {
        echo "❌ No session data found\n";
    }
    echo "\n";
    
    // Step 4: Test authentication
    echo "4. Testing authentication...\n";
    $isAuthenticated = $authService->isAuthenticated();
    echo "Is authenticated: " . ($isAuthenticated ? '✅ Yes' : '❌ No') . "\n";
    
    $currentUser = $authService->getCurrentUser();
    echo "Current user: " . json_encode($currentUser) . "\n";
    
    $hasAdminRole = $authService->hasRole('admin');
    echo "Has admin role: " . ($hasAdminRole ? '✅ Yes' : '❌ No') . "\n";
    echo "\n";
    
    // Step 5: Test UserService directly
    echo "5. Testing UserService...\n";
    $userService = new UserServiceImpl();
    $allUsers = $userService->getAllUsers();
    echo "Total users: " . count($allUsers) . "\n";
    
    if (count($allUsers) > 0) {
        echo "Sample users:\n";
        foreach (array_slice($allUsers, 0, 3) as $user) {
            echo "- {$user['school_id']} ({$user['role']})\n";
        }
    }
    echo "\n";
    
    // Step 6: Test UserController
    echo "6. Testing UserController...\n";
    try {
        $userController = new UserController();
        
        // Capture output
        ob_start();
        $userController->index();
        $output = ob_get_clean();
        
        echo "✅ UserController::index() executed successfully\n";
        echo "Output: $output\n";
        
        // Parse JSON response
        $response = json_decode($output, true);
        if ($response && isset($response['status'])) {
            echo "Response status: " . $response['status'] . "\n";
            if (isset($response['data'])) {
                echo "Users count: " . count($response['data']) . "\n";
            }
        }
        
    } catch (Exception $e) {
        echo "❌ UserController error: " . $e->getMessage() . "\n";
    }
    echo "\n";
    
    // Step 7: Test requireRole
    echo "7. Testing requireRole...\n";
    try {
        $authService->requireRole('admin');
        echo "✅ requireRole('admin') passed\n";
    } catch (Exception $e) {
        echo "❌ requireRole('admin') failed: " . $e->getMessage() . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== Test Complete ===\n";