<?php
// Test subjects API
echo "=== Subjects API Test ===\n\n";

require_once __DIR__ . '/vendor/autoload.php';

use App\Services\AuthServiceImpl;
use App\Services\SubjectServiceImpl;
use App\Controllers\SubjectController;

try {
    // Step 1: Login to get session
    echo "1. Logging in...\n";
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }
    session_start();
    
    $authService = new AuthServiceImpl();
    $user = $authService->login('ADMIN001', 'password123');
    
    if ($user) {
        echo "✅ Login successful\n";
    } else {
        echo "❌ Login failed\n";
        exit;
    }
    echo "\n";
    
    // Step 2: Test SubjectService directly
    echo "2. Testing SubjectService...\n";
    $subjectService = new SubjectServiceImpl();
    $subjects = $subjectService->getAllSubjects();
    echo "Total subjects: " . count($subjects) . "\n";
    
    if (count($subjects) > 0) {
        echo "Sample subjects:\n";
        foreach (array_slice($subjects, 0, 3) as $subject) {
            echo "- {$subject['subject_name']} ({$subject['subject_code']})\n";
        }
    }
    echo "\n";
    
    // Step 3: Test SubjectController
    echo "3. Testing SubjectController...\n";
    try {
        $subjectController = new SubjectController();
        
        // Capture output
        ob_start();
        $subjectController->index();
        $output = ob_get_clean();
        
        echo "✅ SubjectController::index() executed successfully\n";
        echo "Output: $output\n";
        
        // Parse JSON response
        $response = json_decode($output, true);
        if ($response && isset($response['status'])) {
            echo "Response status: " . $response['status'] . "\n";
            if (isset($response['data'])) {
                echo "Subjects count: " . count($response['data']) . "\n";
            }
        }
        
    } catch (Exception $e) {
        echo "❌ SubjectController error: " . $e->getMessage() . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== Test Complete ===\n";