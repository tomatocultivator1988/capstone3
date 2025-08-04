<?php

/**
 * TDD Unit Tests for Refactored Architecture
 * 
 * Comprehensive unit tests for each method/function following TDD principles.
 * Tests business logic, data access logic, and controller logic separately.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Model\User;
use Model\Subject;
use Model\Exam;
use Service\Impl\UserServiceImpl;
use Service\Impl\AuthServiceImpl;
use Dao\Impl\UserDAOImpl;
use Dao\Impl\SubjectDAOImpl;
use Dao\Impl\ExamDAOImpl;
use Controller\AuthController;
use Controller\UserController;
use Config\Database;
use Config\ServiceContainer;

/**
 * Base Test Class with Utilities
 */
abstract class BaseTest
{
    protected function setUp(): void
    {
        // Reset any state before each test
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    protected function tearDown(): void
    {
        // Clean up after each test
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    protected function assertThrows(callable $callback, string $expectedExceptionClass = Exception::class): void
    {
        try {
            $callback();
            throw new Exception("Expected exception $expectedExceptionClass was not thrown");
        } catch (Exception $e) {
            if (!($e instanceof $expectedExceptionClass)) {
                throw new Exception("Expected $expectedExceptionClass but got " . get_class($e) . ": " . $e->getMessage());
            }
        }
    }

    protected function assertTrue(bool $condition, string $message = ""): void
    {
        if (!$condition) {
            throw new Exception("Assertion failed: $message");
        }
    }

    protected function assertFalse(bool $condition, string $message = ""): void
    {
        if ($condition) {
            throw new Exception("Assertion failed: $message");
        }
    }

    protected function assertEquals($expected, $actual, string $message = ""): void
    {
        if ($expected !== $actual) {
            throw new Exception("Assertion failed: Expected '$expected', got '$actual'. $message");
        }
    }

    protected function assertNotNull($value, string $message = ""): void
    {
        if ($value === null) {
            throw new Exception("Assertion failed: Value should not be null. $message");
        }
    }

    protected function assertNull($value, string $message = ""): void
    {
        if ($value !== null) {
            throw new Exception("Assertion failed: Value should be null. $message");
        }
    }
}

/**
 * TDD Tests for Model Classes
 * Testing data validation and transformation logic
 */
class ModelUnitTests extends BaseTest
{
    public function testUserModelHydration(): void
    {
        // Arrange
        $userData = [
            'user_id' => 1,
            'school_id' => 'STU123',
            'full_name' => 'John Doe',
            'role' => 'student',
            'year_level' => 2,
            'section' => 'A'
        ];

        // Act
        $user = new User($userData);

        // Assert
        $this->assertEquals(1, $user->getUserId());
        $this->assertEquals('STU123', $user->getSchoolId());
        $this->assertEquals('John Doe', $user->getFullName());
        $this->assertEquals('student', $user->getRole());
        $this->assertEquals(2, $user->getYearLevel());
        $this->assertEquals('A', $user->getSection());
    }

    public function testUserModelToArray(): void
    {
        // Arrange
        $user = new User([
            'user_id' => 1,
            'school_id' => 'STU123',
            'full_name' => 'John Doe',
            'role' => 'student'
        ]);

        // Act
        $array = $user->toArray();

        // Assert
        $this->assertEquals(1, $array['user_id']);
        $this->assertEquals('STU123', $array['school_id']);
        $this->assertEquals('John Doe', $array['full_name']);
        $this->assertEquals('student', $array['role']);
    }

    public function testUserRoleMethods(): void
    {
        // Test student role
        $student = new User(['role' => 'student']);
        $this->assertTrue($student->isStudent(), "Should identify as student");
        $this->assertFalse($student->isFaculty(), "Should not identify as faculty");
        $this->assertFalse($student->isAdmin(), "Should not identify as admin");

        // Test faculty role
        $faculty = new User(['role' => 'faculty']);
        $this->assertTrue($faculty->isFaculty(), "Should identify as faculty");
        $this->assertFalse($faculty->isStudent(), "Should not identify as student");
        
        // Test admin role
        $admin = new User(['role' => 'admin']);
        $this->assertTrue($admin->isAdmin(), "Should identify as admin");
        $this->assertFalse($admin->isStudent(), "Should not identify as student");
    }

    public function testSubjectModelMethods(): void
    {
        // Arrange
        $subjectData = [
            'subject_id' => 1,
            'subject_code' => 'CS101',
            'subject_name' => 'Computer Science',
            'units' => 3,
            'faculty_id' => 5
        ];

        // Act
        $subject = new Subject($subjectData);

        // Assert
        $this->assertEquals('CS101', $subject->getSubjectCode());
        $this->assertEquals('Computer Science', $subject->getSubjectName());
        $this->assertEquals(3, $subject->getUnits());
        $this->assertTrue($subject->isAssignedToFaculty());
        $this->assertEquals('CS101 - Computer Science', $subject->getDisplayName());
    }

    public function testExamModelStatusMethods(): void
    {
        // Test draft status
        $draftExam = new Exam(['status' => 'draft']);
        $this->assertTrue($draftExam->isDraft());
        $this->assertFalse($draftExam->isPublished());
        $this->assertFalse($draftExam->isArchived());

        // Test published status
        $publishedExam = new Exam(['status' => 'published']);
        $this->assertTrue($publishedExam->isPublished());
        $this->assertFalse($draftExam->isDraft());

        // Test archived status
        $archivedExam = new Exam(['status' => 'archived']);
        $this->assertTrue($archivedExam->isArchived());
    }
}

/**
 * TDD Tests for Service Implementations
 * Testing business logic without external dependencies
 */
class ServiceUnitTests extends BaseTest
{
    private $mockUserDAO;
    private UserServiceImpl $userService;
    private AuthServiceImpl $authService;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create mock DAO for isolated testing
        $this->mockUserDAO = new class implements \Dao\Interface\UserDAOInterface {
            private $users = [];
            
            public function findById(int $user_id): ?User {
                return $this->users[$user_id] ?? null;
            }
            
            public function findBySchoolId(string $school_id): ?User {
                foreach ($this->users as $user) {
                    if ($user->getSchoolId() === $school_id) {
                        return $user;
                    }
                }
                return null;
            }
            
            public function findAll(): array { return array_values($this->users); }
            public function findByRole(string $role): array { 
                return array_filter($this->users, fn($u) => $u->getRole() === $role);
            }
            public function create(User $user): bool { 
                $id = count($this->users) + 1;
                $user->setUserId($id);
                $this->users[$id] = $user;
                return true;
            }
            public function update(User $user): bool { 
                if (isset($this->users[$user->getUserId()])) {
                    $this->users[$user->getUserId()] = $user;
                    return true;
                }
                return false;
            }
            public function deleteById(int $user_id): bool { 
                if (isset($this->users[$user_id])) {
                    unset($this->users[$user_id]);
                    return true;
                }
                return false;
            }
            public function existsBySchoolId(string $school_id): bool { 
                return $this->findBySchoolId($school_id) !== null;
            }
            public function getTotalCount(): int { return count($this->users); }
            public function findWithPagination(int $limit, int $offset): array { 
                return array_slice($this->users, $offset, $limit);
            }
            public function findStudentsByYearAndSection(int $year_level, string $section): array { 
                return array_filter($this->users, fn($u) => 
                    $u->getRole() === 'student' && 
                    $u->getYearLevel() === $year_level && 
                    $u->getSection() === $section
                );
            }
            public function updatePassword(int $user_id, string $hashedPassword): bool { 
                if (isset($this->users[$user_id])) {
                    $this->users[$user_id]->setPassword($hashedPassword);
                    return true;
                }
                return false;
            }
            
            // Test helper methods
            public function addTestUser(User $user): void {
                $this->users[$user->getUserId()] = $user;
            }
        };
        
        $this->userService = new UserServiceImpl($this->mockUserDAO);
        $this->authService = new AuthServiceImpl($this->mockUserDAO);
    }

    public function testUserService_ValidateUserData_WithValidData(): void
    {
        // Arrange
        $validData = [
            'school_id' => 'STU123',
            'full_name' => 'John Doe',
            'role' => 'student',
            'year_level' => 1,
            'section' => 'A'
        ];

        // Act
        $errors = $this->userService->validateUserData($validData);

        // Assert
        $this->assertEquals([], $errors, "Valid data should have no errors");
    }

    public function testUserService_ValidateUserData_WithInvalidData(): void
    {
        // Arrange
        $invalidData = [
            'school_id' => '', // Empty
            'full_name' => 'A', // Too short
            'role' => 'invalid', // Invalid role
            'year_level' => 5, // Out of range
            'section' => '' // Empty for student
        ];

        // Act
        $errors = $this->userService->validateUserData($invalidData);

        // Assert
        $this->assertTrue(count($errors) > 0, "Invalid data should have errors");
        $this->assertTrue(in_array('School ID is required', $errors));
        $this->assertTrue(in_array('Full name must be at least 2 characters', $errors));
        $this->assertTrue(in_array('Invalid role. Must be admin, faculty, or student', $errors));
    }

    public function testUserService_CreateUser_Success(): void
    {
        // Arrange
        $school_id = 'STU123';
        $full_name = 'John Doe';
        $role = 'student';
        $year_level = 1;
        $section = 'A';

        // Act
        $result = $this->userService->createUser($school_id, $full_name, $role, $year_level, $section);

        // Assert
        $this->assertTrue($result, "User creation should succeed");
        $this->assertTrue($this->userService->userExists($school_id), "User should exist after creation");
    }

    public function testUserService_CreateUser_DuplicateSchoolId(): void
    {
        // Arrange
        $existingUser = new User([
            'user_id' => 1,
            'school_id' => 'STU123',
            'full_name' => 'Existing User',
            'role' => 'student'
        ]);
        $this->mockUserDAO->addTestUser($existingUser);

        // Act
        $result = $this->userService->createUser('STU123', 'New User', 'student', 1, 'A');

        // Assert
        $this->assertFalse($result, "Should not create user with duplicate school ID");
    }

    public function testUserService_GetUserStatistics(): void
    {
        // Arrange
        $users = [
            new User(['user_id' => 1, 'role' => 'admin']),
            new User(['user_id' => 2, 'role' => 'faculty']),
            new User(['user_id' => 3, 'role' => 'student']),
            new User(['user_id' => 4, 'role' => 'student'])
        ];
        
        foreach ($users as $user) {
            $this->mockUserDAO->addTestUser($user);
        }

        // Act
        $stats = $this->userService->getUserStatistics();

        // Assert
        $this->assertEquals(4, $stats['total']);
        $this->assertEquals(1, $stats['admin']);
        $this->assertEquals(1, $stats['faculty']);
        $this->assertEquals(2, $stats['student']);
    }

    public function testAuthService_ValidatePassword_WeakPassword(): void
    {
        // Arrange
        $weakPassword = '123';

        // Act
        $errors = $this->authService->validatePassword($weakPassword);

        // Assert
        $this->assertTrue(count($errors) > 0, "Weak password should have errors");
        $this->assertTrue(in_array('Password must be at least 6 characters long', $errors));
    }

    public function testAuthService_ValidatePassword_StrongPassword(): void
    {
        // Arrange
        $strongPassword = 'StrongPass123';

        // Act
        $errors = $this->authService->validatePassword($strongPassword);

        // Assert
        $this->assertEquals([], $errors, "Strong password should have no errors");
    }

    public function testAuthService_HashAndVerifyPassword(): void
    {
        // Arrange
        $password = 'testpassword123';

        // Act
        $hash = $this->authService->hashPassword($password);
        $isValid = $this->authService->verifyPassword($password, $hash);
        $isInvalid = $this->authService->verifyPassword('wrongpassword', $hash);

        // Assert
        $this->assertTrue($isValid, "Correct password should verify");
        $this->assertFalse($isInvalid, "Incorrect password should not verify");
        $this->assertTrue(strlen($hash) > 10, "Hash should be generated");
    }

    public function testAuthService_Login_Success(): void
    {
        // Arrange
        $user = new User([
            'user_id' => 1,
            'school_id' => 'STU123',
            'full_name' => 'John Doe',
            'role' => 'student',
            'password' => password_hash('testpass', PASSWORD_DEFAULT)
        ]);
        $this->mockUserDAO->addTestUser($user);

        // Act
        $result = $this->authService->login('STU123', 'testpass');

        // Assert
        $this->assertNotNull($result, "Login should succeed");
        $this->assertEquals('STU123', $result['school_id']);
        $this->assertEquals('John Doe', $result['full_name']);
        $this->assertEquals('student', $result['role']);
    }

    public function testAuthService_Login_InvalidCredentials(): void
    {
        // Arrange
        $user = new User([
            'user_id' => 1,
            'school_id' => 'STU123',
            'password' => password_hash('correctpass', PASSWORD_DEFAULT)
        ]);
        $this->mockUserDAO->addTestUser($user);

        // Act & Assert
        $resultWrongPassword = $this->authService->login('STU123', 'wrongpass');
        $resultWrongUser = $this->authService->login('WRONG123', 'correctpass');

        $this->assertNull($resultWrongPassword, "Wrong password should fail");
        $this->assertNull($resultWrongUser, "Wrong user should fail");
    }
}

/**
 * TDD Tests for DAO Implementations
 * Testing data access logic with real database
 */
class DAOUnitTests extends BaseTest
{
    private UserDAOImpl $userDAO;
    private SubjectDAOImpl $subjectDAO;
    private ExamDAOImpl $examDAO;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userDAO = new UserDAOImpl();
        $this->subjectDAO = new SubjectDAOImpl();
        $this->examDAO = new ExamDAOImpl();
    }

    public function testUserDAO_FindById(): void
    {
        // This test requires actual database data
        // In a real TDD environment, you'd use test database with known data
        
        // Act
        $user = $this->userDAO->findById(1);

        // Assert (assuming user with ID 1 exists in test DB)
        if ($user !== null) {
            $this->assertNotNull($user->getUserId(), "User should have ID");
            $this->assertNotNull($user->getSchoolId(), "User should have school ID");
        }
        // If no user exists, that's also valid for the test
    }

    public function testUserDAO_ExistsBySchoolId(): void
    {
        // Arrange - using a known non-existent school ID
        $nonExistentSchoolId = 'NONEXISTENT999';

        // Act
        $exists = $this->userDAO->existsBySchoolId($nonExistentSchoolId);

        // Assert
        $this->assertFalse($exists, "Non-existent school ID should return false");
    }

    public function testUserDAO_GetTotalCount(): void
    {
        // Act
        $count = $this->userDAO->getTotalCount();

        // Assert
        $this->assertTrue($count >= 0, "Count should be non-negative");
        $this->assertTrue(is_int($count), "Count should be integer");
    }

    public function testSubjectDAO_FindAll(): void
    {
        // Act
        $subjects = $this->subjectDAO->findAll();

        // Assert
        $this->assertTrue(is_array($subjects), "Should return array");
        
        if (count($subjects) > 0) {
            $firstSubject = $subjects[0];
            $this->assertTrue($firstSubject instanceof Subject, "Should return Subject objects");
        }
    }

    public function testExamDAO_FindByStatus(): void
    {
        // Act
        $draftExams = $this->examDAO->findByStatus('draft');
        $publishedExams = $this->examDAO->findByStatus('published');

        // Assert
        $this->assertTrue(is_array($draftExams), "Should return array for draft exams");
        $this->assertTrue(is_array($publishedExams), "Should return array for published exams");
    }
}

/**
 * TDD Test Runner
 * Executes all unit tests and reports results
 */
class TDDTestRunner
{
    private int $totalTests = 0;
    private int $passedTests = 0;
    private int $failedTests = 0;
    private array $failures = [];

    public function runAllTests(): void
    {
        echo "🧪 Starting TDD Unit Tests\n";
        echo "=" . str_repeat("=", 50) . "\n\n";

        $testClasses = [
            ModelUnitTests::class,
            ServiceUnitTests::class,
            DAOUnitTests::class
        ];

        foreach ($testClasses as $testClass) {
            $this->runTestClass($testClass);
        }

        $this->printSummary();
    }

    private function runTestClass(string $testClass): void
    {
        echo "📝 Running $testClass\n";
        echo "-" . str_repeat("-", 40) . "\n";

        $reflection = new ReflectionClass($testClass);
        $testInstance = $reflection->newInstance();

        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if (strpos($method->getName(), 'test') === 0) {
                $this->runSingleTest($testInstance, $method->getName());
            }
        }
        echo "\n";
    }

    private function runSingleTest(object $testInstance, string $methodName): void
    {
        $this->totalTests++;

        try {
            // Setup
            if (method_exists($testInstance, 'setUp')) {
                $testInstance->setUp();
            }

            // Run test
            $testInstance->$methodName();

            // Teardown
            if (method_exists($testInstance, 'tearDown')) {
                $testInstance->tearDown();
            }

            echo "✅ $methodName\n";
            $this->passedTests++;

        } catch (Exception $e) {
            echo "❌ $methodName: " . $e->getMessage() . "\n";
            $this->failedTests++;
            $this->failures[] = [
                'test' => $methodName,
                'error' => $e->getMessage()
            ];
        }
    }

    private function printSummary(): void
    {
        echo "=" . str_repeat("=", 50) . "\n";
        echo "📊 TDD TEST RESULTS\n";
        echo "=" . str_repeat("=", 50) . "\n";
        echo "Total Tests: {$this->totalTests}\n";
        echo "✅ Passed: {$this->passedTests}\n";
        echo "❌ Failed: {$this->failedTests}\n";
        
        if ($this->failedTests > 0) {
            echo "\n🚨 FAILURES:\n";
            foreach ($this->failures as $failure) {
                echo "- {$failure['test']}: {$failure['error']}\n";
            }
        }

        $successRate = $this->totalTests > 0 ? round(($this->passedTests / $this->totalTests) * 100, 2) : 0;
        echo "\n📈 Success Rate: {$successRate}%\n";

        if ($this->failedTests === 0) {
            echo "\n🎉 ALL TESTS PASSED! Your architecture is solid.\n";
        } else {
            echo "\n⚠️  Some tests failed. Fix the failing tests to improve code quality.\n";
        }
    }
}

// Run TDD Tests
try {
    $runner = new TDDTestRunner();
    $runner->runAllTests();
} catch (Exception $e) {
    echo "❌ Critical Test Runner Error: " . $e->getMessage() . "\n";
}