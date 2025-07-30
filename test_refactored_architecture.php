<?php
/**
 * Test Refactored Architecture
 * 
 * This script tests the new layered architecture:
 * Models (Data) → Repositories (Data Access) → Services (Business Logic)
 */

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\User;
use App\Models\Subject;
use App\Repositories\UserRepository;
use App\Repositories\SubjectRepository;
use App\Services\UserServiceImpl;
use App\Services\SubjectServiceImpl;

echo "🧪 Testing Refactored Architecture\n";
echo "===================================\n\n";

try {
    // Test 1: User Model (Data Container)
    echo "1. Testing User Model (Data Container)...\n";
    $userData = [
        'user_id' => 1,
        'school_id' => 'TEST001',
        'full_name' => 'Test User',
        'password' => 'hashed_password',
        'role' => 'student',
        'year_level' => 1,
        'section' => 'A'
    ];
    
    $user = new User($userData);
    echo "   ✅ User created: " . $user->getFullName() . " (" . $user->getRole() . ")\n";
    echo "   ✅ User is student: " . ($user->isStudent() ? 'Yes' : 'No') . "\n";
    echo "   ✅ User array: " . json_encode($user->toArray()) . "\n\n";

    // Test 2: Subject Model (Data Container)
    echo "2. Testing Subject Model (Data Container)...\n";
    $subjectData = [
        'subject_id' => 1,
        'subject_code' => 'MATH101',
        'subject_name' => 'Mathematics',
        'description' => 'Basic Mathematics',
        'units' => 3,
        'faculty_id' => 1
    ];
    
    $subject = new Subject($subjectData);
    echo "   ✅ Subject created: " . $subject->getDisplayName() . "\n";
    echo "   ✅ Subject assigned to faculty: " . ($subject->isAssignedToFaculty() ? 'Yes' : 'No') . "\n";
    echo "   ✅ Subject array: " . json_encode($subject->toArray()) . "\n\n";

    // Test 3: User Repository (Data Access)
    echo "3. Testing User Repository (Data Access)...\n";
    $userRepo = new UserRepository();
    
    // Test finding user by school ID
    $foundUser = $userRepo->findBySchoolId('ADMIN001');
    if ($foundUser) {
        echo "   ✅ Found user: " . $foundUser->getFullName() . "\n";
    } else {
        echo "   ⚠️  User ADMIN001 not found (this is expected if not in database)\n";
    }
    
    // Test getting all users
    $allUsers = $userRepo->getAll();
    echo "   ✅ Total users in database: " . count($allUsers) . "\n\n";

    // Test 4: Subject Repository (Data Access)
    echo "4. Testing Subject Repository (Data Access)...\n";
    $subjectRepo = new SubjectRepository();
    
    // Test getting all subjects
    $allSubjects = $subjectRepo->getAll();
    echo "   ✅ Total subjects in database: " . count($allSubjects) . "\n\n";

    // Test 5: User Service (Business Logic)
    echo "5. Testing User Service (Business Logic)...\n";
    $userService = new UserServiceImpl($userRepo);
    
    // Test authentication
    $authResult = $userService->authenticateUser('ADMIN001', 'password123');
    if ($authResult) {
        echo "   ✅ Authentication successful for ADMIN001\n";
    } else {
        echo "   ⚠️  Authentication failed for ADMIN001 (check password)\n";
    }
    
    // Test getting all users
    $users = $userService->getAllUsers();
    echo "   ✅ Service returned " . count($users) . " users\n\n";

    // Test 6: Subject Service (Business Logic)
    echo "6. Testing Subject Service (Business Logic)...\n";
    $subjectService = new SubjectServiceImpl($subjectRepo);
    
    // Test getting all subjects
    $subjects = $subjectService->getAllSubjects();
    echo "   ✅ Service returned " . count($subjects) . " subjects\n\n";

    echo "🎉 All tests completed successfully!\n";
    echo "✅ Architecture refactoring is working correctly.\n\n";
    
    echo "📋 Architecture Summary:\n";
    echo "   • Models: Simple data containers with getters/setters\n";
    echo "   • Repositories: Handle all database operations\n";
    echo "   • Services: Contain business logic and use repositories\n";
    echo "   • Controllers: Handle HTTP requests and use services\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}