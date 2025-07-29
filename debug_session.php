<?php
// Debug session issues
echo "=== Session Debug ===\n\n";

// Test 1: Check session status
echo "1. Session status: " . session_status() . "\n";
if (session_status() === PHP_SESSION_NONE) {
    echo "Starting session...\n";
    session_start();
    echo "Session started\n";
} else {
    echo "Session already active\n";
}

// Test 2: Check session data
echo "\n2. Current session data:\n";
if (isset($_SESSION) && !empty($_SESSION)) {
    foreach ($_SESSION as $key => $value) {
        echo "- $key: " . (is_array($value) ? json_encode($value) : $value) . "\n";
    }
} else {
    echo "No session data found\n";
}

// Test 3: Check if user is authenticated
echo "\n3. Testing authentication...\n";
try {
    require_once __DIR__ . '/vendor/autoload.php';
    
    $authService = new \App\Services\AuthServiceImpl();
    
    $isAuthenticated = $authService->isAuthenticated();
    echo "Is authenticated: " . ($isAuthenticated ? '✅ Yes' : '❌ No') . "\n";
    
    if ($isAuthenticated) {
        $currentUser = $authService->getCurrentUser();
        echo "Current user: " . json_encode($currentUser) . "\n";
        
        $hasAdminRole = $authService->hasRole('admin');
        echo "Has admin role: " . ($hasAdminRole ? '✅ Yes' : '❌ No') . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ AuthService error: " . $e->getMessage() . "\n";
}

// Test 4: Simulate login and check session
echo "\n4. Simulating login...\n";
try {
    // Clear session first
    session_destroy();
    session_start();
    
    // Simulate successful login
    $_SESSION['user_id'] = 1;
    $_SESSION['school_id'] = 'ADMIN001';
    $_SESSION['full_name'] = 'Admin User';
    $_SESSION['role'] = 'admin';
    
    echo "Session data after login simulation:\n";
    foreach ($_SESSION as $key => $value) {
        echo "- $key: $value\n";
    }
    
    // Test authentication again
    $authService = new \App\Services\AuthServiceImpl();
    $isAuthenticated = $authService->isAuthenticated();
    echo "Is authenticated after login: " . ($isAuthenticated ? '✅ Yes' : '❌ No') . "\n";
    
    $hasAdminRole = $authService->hasRole('admin');
    echo "Has admin role after login: " . ($hasAdminRole ? '✅ Yes' : '❌ No') . "\n";
    
} catch (Exception $e) {
    echo "❌ Login simulation error: " . $e->getMessage() . "\n";
}

echo "\n=== Session Debug Complete ===\n";