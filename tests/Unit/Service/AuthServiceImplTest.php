<?php

/**
 * AuthServiceImpl Unit Tests
 * 
 * Tests all methods in AuthServiceImpl class following TDD principles.
 * Focuses on authentication, password management, and session handling.
 */

require_once __DIR__ . '/../../BaseTest.php';
require_once __DIR__ . '/../../../src/dao/interface/UserDAOInterface.php';
require_once __DIR__ . '/../../../src/service/AuthService.php';
require_once __DIR__ . '/../../../src/service/AuthServiceImpl.php';
require_once __DIR__ . '/../../../src/model/User.php';

use Service\Impl\AuthServiceImpl;
use Model\User;
use Dao\Interface\UserDAOInterface;

class AuthServiceImplTest extends BaseTest
{
    private $mockUserDAO;
    private AuthServiceImpl $authService;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create mock DAO for isolated testing
        $this->mockUserDAO = new class implements UserDAOInterface {
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
            public function findByRole(string $role): array { return []; }
            public function create(User $user): bool { return true; }
            public function update(User $user): bool { return true; }
            public function deleteById(int $user_id): bool { return true; }
            public function existsBySchoolId(string $school_id): bool { return false; }
            public function getTotalCount(): int { return count($this->users); }
            public function findWithPagination(int $limit, int $offset): array { return []; }
            public function findStudentsByYearAndSection(int $year_level, string $section): array { return []; }
            public function updatePassword(int $user_id, string $hashedPassword): bool { return true; }
            
            // Test helper methods
            public function addTestUser(User $user): void {
                $this->users[$user->getUserId()] = $user;
            }
            
            public function clearUsers(): void {
                $this->users = [];
            }
        };
        
        $this->authService = new AuthServiceImpl($this->mockUserDAO);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->mockUserDAO->clearUsers();
    }

    // ===== LOGIN TESTS =====

    public function testLogin_WithValidCredentials_ShouldSucceed(): void
    {
        // Arrange
        $user = new User([
            'user_id' => 1,
            'school_id' => 'STU123',
            'full_name' => 'John Doe',
            'role' => 'student',
            'year_level' => 1,
            'section' => 'A',
            'password' => password_hash('testpass', PASSWORD_DEFAULT)
        ]);
        $this->mockUserDAO->addTestUser($user);

        // Act
        $result = $this->authService->login('STU123', 'testpass');

        // Assert
        $this->assertNotNull($result, "Login should succeed with valid credentials");
        $this->assertEquals('STU123', $result['school_id']);
        $this->assertEquals('John Doe', $result['full_name']);
        $this->assertEquals('student', $result['role']);
        $this->assertEquals(1, $result['year_level']);
        $this->assertEquals('A', $result['section']);
        
        // Verify password is not in response
        $this->assertFalse(isset($result['password']), "Password should not be in login response");
    }

    public function testLogin_WithInvalidPassword_ShouldFail(): void
    {
        // Arrange
        $user = new User([
            'user_id' => 1,
            'school_id' => 'STU123',
            'password' => password_hash('correctpass', PASSWORD_DEFAULT)
        ]);
        $this->mockUserDAO->addTestUser($user);

        // Act
        $result = $this->authService->login('STU123', 'wrongpass');

        // Assert
        $this->assertNull($result, "Login should fail with invalid password");
    }

    public function testLogin_WithNonExistentUser_ShouldFail(): void
    {
        // Act
        $result = $this->authService->login('NONEXISTENT', 'anypass');

        // Assert
        $this->assertNull($result, "Login should fail for non-existent user");
    }

    public function testLogin_WithEmptyCredentials_ShouldFail(): void
    {
        // Act
        $resultEmptySchoolId = $this->authService->login('', 'password');
        $resultEmptyPassword = $this->authService->login('STU123', '');
        $resultBothEmpty = $this->authService->login('', '');

        // Assert
        $this->assertNull($resultEmptySchoolId, "Should fail with empty school ID");
        $this->assertNull($resultEmptyPassword, "Should fail with empty password");
        $this->assertNull($resultBothEmpty, "Should fail with both empty");
    }

    // ===== LOGOUT TESTS =====

    public function testLogout_ShouldSucceed(): void
    {
        // Act
        $result = $this->authService->logout();

        // Assert
        $this->assertTrue($result, "Logout should succeed");
    }

    // ===== AUTHENTICATION STATUS TESTS =====

    public function testIsAuthenticated_WithoutSession_ShouldReturnFalse(): void
    {
        // Act
        $result = $this->authService->isAuthenticated();

        // Assert
        $this->assertFalse($result, "Should return false when no session exists");
    }

    public function testIsAuthenticated_WithSession_ShouldReturnTrue(): void
    {
        // Arrange - Simulate session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['user_id'] = 1;

        // Act
        $result = $this->authService->isAuthenticated();

        // Assert
        $this->assertTrue($result, "Should return true when session exists");
    }

    public function testGetCurrentUser_WithoutSession_ShouldReturnNull(): void
    {
        // Act
        $result = $this->authService->getCurrentUser();

        // Assert
        $this->assertNull($result, "Should return null when no session exists");
    }

    public function testGetCurrentUser_WithValidSession_ShouldReturnUser(): void
    {
        // Arrange
        $user = new User([
            'user_id' => 1,
            'school_id' => 'STU123',
            'full_name' => 'John Doe',
            'role' => 'student'
        ]);
        $this->mockUserDAO->addTestUser($user);

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['user_id'] = 1;

        // Act
        $result = $this->authService->getCurrentUser();

        // Assert
        $this->assertNotNull($result, "Should return user when valid session exists");
        $this->assertEquals('STU123', $result->getSchoolId());
        $this->assertEquals('John Doe', $result->getFullName());
    }

    // ===== ROLE AND PERMISSION TESTS =====

    public function testHasRole_WithMatchingRole_ShouldReturnTrue(): void
    {
        // Arrange
        $user = new User(['user_id' => 1, 'role' => 'admin']);
        $this->mockUserDAO->addTestUser($user);

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['user_id'] = 1;

        // Act
        $result = $this->authService->hasRole('admin');

        // Assert
        $this->assertTrue($result, "Should return true for matching role");
    }

    public function testHasRole_WithNonMatchingRole_ShouldReturnFalse(): void
    {
        // Arrange
        $user = new User(['user_id' => 1, 'role' => 'student']);
        $this->mockUserDAO->addTestUser($user);

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['user_id'] = 1;

        // Act
        $result = $this->authService->hasRole('admin');

        // Assert
        $this->assertFalse($result, "Should return false for non-matching role");
    }

    public function testHasRole_WithoutSession_ShouldReturnFalse(): void
    {
        // Act
        $result = $this->authService->hasRole('admin');

        // Assert
        $this->assertFalse($result, "Should return false when no session exists");
    }

    public function testHasPermission_WithAdminUser_ShouldReturnTrueForAll(): void
    {
        // Arrange
        $user = new User(['user_id' => 1, 'role' => 'admin']);
        $this->mockUserDAO->addTestUser($user);

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['user_id'] = 1;

        // Act & Assert
        $this->assertTrue($this->authService->hasPermission('create_exam'), "Admin should have create_exam permission");
        $this->assertTrue($this->authService->hasPermission('delete_user'), "Admin should have delete_user permission");
        $this->assertTrue($this->authService->hasPermission('any_permission'), "Admin should have any permission");
    }

    public function testHasPermission_WithFacultyUser_ShouldReturnTrueForFacultyPermissions(): void
    {
        // Arrange
        $user = new User(['user_id' => 1, 'role' => 'faculty']);
        $this->mockUserDAO->addTestUser($user);

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['user_id'] = 1;

        // Act & Assert
        $this->assertTrue($this->authService->hasPermission('create_exam'), "Faculty should have create_exam permission");
        $this->assertTrue($this->authService->hasPermission('view_results'), "Faculty should have view_results permission");
        $this->assertFalse($this->authService->hasPermission('delete_user'), "Faculty should not have delete_user permission");
    }

    public function testHasPermission_WithStudentUser_ShouldReturnTrueForStudentPermissions(): void
    {
        // Arrange
        $user = new User(['user_id' => 1, 'role' => 'student']);
        $this->mockUserDAO->addTestUser($user);

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['user_id'] = 1;

        // Act & Assert
        $this->assertTrue($this->authService->hasPermission('take_exam'), "Student should have take_exam permission");
        $this->assertTrue($this->authService->hasPermission('view_results'), "Student should have view_results permission");
        $this->assertFalse($this->authService->hasPermission('create_exam'), "Student should not have create_exam permission");
        $this->assertFalse($this->authService->hasPermission('delete_user'), "Student should not have delete_user permission");
    }

    // ===== PASSWORD VALIDATION TESTS =====

    public function testValidatePassword_WithTooShortPassword_ShouldReturnErrors(): void
    {
        // Arrange
        $shortPassword = '123';

        // Act
        $errors = $this->authService->validatePassword($shortPassword);

        // Assert
        $this->assertNotEmpty($errors, "Short password should have errors");
        $this->assertContains('Password must be at least 6 characters long', $errors);
    }

    public function testValidatePassword_WithPasswordWithoutLetters_ShouldReturnErrors(): void
    {
        // Arrange
        $noLettersPassword = '123456';

        // Act
        $errors = $this->authService->validatePassword($noLettersPassword);

        // Assert
        $this->assertContains('Password must contain at least one letter', $errors);
    }

    public function testValidatePassword_WithPasswordWithoutNumbers_ShouldReturnErrors(): void
    {
        // Arrange
        $noNumbersPassword = 'abcdef';

        // Act
        $errors = $this->authService->validatePassword($noNumbersPassword);

        // Assert
        $this->assertContains('Password must contain at least one number', $errors);
    }

    public function testValidatePassword_WithWeakCommonPassword_ShouldReturnErrors(): void
    {
        // Arrange
        $weakPasswords = ['123456', 'password', 'admin', 'qwerty'];

        foreach ($weakPasswords as $weakPassword) {
            // Act
            $errors = $this->authService->validatePassword($weakPassword);

            // Assert
            $this->assertContains('Password is too common and weak', $errors, "Password '$weakPassword' should be rejected");
        }
    }

    public function testValidatePassword_WithStrongPassword_ShouldReturnNoErrors(): void
    {
        // Arrange
        $strongPasswords = ['StrongPass123', 'MySecure1Pass', 'Complex9Word'];

        foreach ($strongPasswords as $strongPassword) {
            // Act
            $errors = $this->authService->validatePassword($strongPassword);

            // Assert
            $this->assertEmpty($errors, "Strong password '$strongPassword' should have no errors");
        }
    }

    // ===== PASSWORD HASHING TESTS =====

    public function testHashPassword_ShouldReturnValidHash(): void
    {
        // Arrange
        $password = 'testpassword123';

        // Act
        $hash = $this->authService->hashPassword($password);

        // Assert
        $this->assertNotNull($hash, "Hash should not be null");
        $this->assertTrue(strlen($hash) > 10, "Hash should be substantial length");
        $this->assertNotEquals($password, $hash, "Hash should be different from original password");
        $this->assertTrue(password_verify($password, $hash), "Hash should verify correctly");
    }

    public function testVerifyPassword_WithCorrectPassword_ShouldReturnTrue(): void
    {
        // Arrange
        $password = 'testpassword123';
        $hash = password_hash($password, PASSWORD_DEFAULT);

        // Act
        $result = $this->authService->verifyPassword($password, $hash);

        // Assert
        $this->assertTrue($result, "Correct password should verify successfully");
    }

    public function testVerifyPassword_WithIncorrectPassword_ShouldReturnFalse(): void
    {
        // Arrange
        $password = 'testpassword123';
        $wrongPassword = 'wrongpassword456';
        $hash = password_hash($password, PASSWORD_DEFAULT);

        // Act
        $result = $this->authService->verifyPassword($wrongPassword, $hash);

        // Assert
        $this->assertFalse($result, "Incorrect password should not verify");
    }

    // ===== PASSWORD RESET TESTS =====

    public function testGenerateResetToken_WithValidUser_ShouldReturnToken(): void
    {
        // Arrange
        $user = new User(['user_id' => 1, 'school_id' => 'STU123']);
        $this->mockUserDAO->addTestUser($user);

        // Act
        $token = $this->authService->generateResetToken('STU123');

        // Assert
        $this->assertNotNull($token, "Should generate token for valid user");
        $this->assertTrue(strlen($token) > 20, "Token should be substantial length");
    }

    public function testGenerateResetToken_WithNonExistentUser_ShouldReturnNull(): void
    {
        // Act
        $token = $this->authService->generateResetToken('NONEXISTENT');

        // Assert
        $this->assertNull($token, "Should return null for non-existent user");
    }

    public function testResetPasswordWithToken_WithValidPassword_ShouldSucceed(): void
    {
        // Arrange
        $token = 'validtoken123';
        $newPassword = 'NewStrongPass123';

        // Act
        $result = $this->authService->resetPasswordWithToken($token, $newPassword);

        // Assert
        $this->assertTrue($result, "Should succeed with valid token and password");
    }

    public function testResetPasswordWithToken_WithWeakPassword_ShouldFail(): void
    {
        // Arrange
        $token = 'validtoken123';
        $weakPassword = '123';

        // Act
        $result = $this->authService->resetPasswordWithToken($token, $weakPassword);

        // Assert
        $this->assertFalse($result, "Should fail with weak password");
    }

    // ===== SESSION MANAGEMENT TESTS =====

    public function testStartSession_WithValidUser_ShouldSucceed(): void
    {
        // Arrange
        $user = new User([
            'user_id' => 1,
            'school_id' => 'STU123',
            'full_name' => 'John Doe',
            'role' => 'student'
        ]);

        // Act
        $result = $this->authService->startSession($user);

        // Assert
        $this->assertTrue($result, "Should successfully start session");
        
        // Verify session data
        $this->assertEquals(1, $_SESSION['user_id']);
        $this->assertEquals('STU123', $_SESSION['school_id']);
        $this->assertEquals('John Doe', $_SESSION['full_name']);
        $this->assertEquals('student', $_SESSION['role']);
        $this->assertNotNull($_SESSION['login_time']);
    }

    public function testDestroySession_ShouldClearSessionData(): void
    {
        // Arrange - Set up session first
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['user_id'] = 1;
        $_SESSION['school_id'] = 'STU123';

        // Act
        $result = $this->authService->destroySession();

        // Assert
        $this->assertTrue($result, "Should successfully destroy session");
        $this->assertEmpty($_SESSION, "Session should be empty after destruction");
    }

    // ===== EDGE CASE TESTS =====

    public function testLogin_WithNullPassword_ShouldFail(): void
    {
        // Act
        $result = $this->authService->login('STU123', null);

        // Assert
        $this->assertNull($result, "Should fail with null password");
    }

    public function testValidatePassword_WithEmptyPassword_ShouldReturnErrors(): void
    {
        // Act
        $errors = $this->authService->validatePassword('');

        // Assert
        $this->assertNotEmpty($errors, "Empty password should have errors");
        $this->assertContains('Password must be at least 6 characters long', $errors);
    }

    public function testHashPassword_WithEmptyPassword_ShouldReturnHash(): void
    {
        // Act
        $hash = $this->authService->hashPassword('');

        // Assert
        $this->assertNotNull($hash, "Should return hash even for empty password");
        $this->assertTrue(password_verify('', $hash), "Empty password should verify with its hash");
    }
}