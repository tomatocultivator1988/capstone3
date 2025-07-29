<?php

use PHPUnit\Framework\TestCase;
use App\Services\AuthServiceImpl;
use App\Services\UserService;

class AuthServiceTest extends TestCase
{
    private $mockUserService;
    private $authService;

    protected function setUp(): void
    {
        $this->mockUserService = $this->createMock(UserService::class);
        $this->authService = new AuthServiceImpl($this->mockUserService);
        
        // Clear session for clean state
        $_SESSION = [];
    }

    protected function tearDown(): void
    {
        // Clean up session after each test
        $_SESSION = [];
    }

    public function testLoginSuccess()
    {
        // Arrange
        $userData = [
            'user_id' => 1,
            'school_id' => '2024001',
            'full_name' => 'John Doe',
            'role' => 'student',
            'year_level' => 10,
            'section' => 'A'
        ];

        $this->mockUserService->method('authenticateUser')->willReturn($userData);

        // Act
        $result = $this->authService->login('2024001', 'password');

        // Assert
        $this->assertEquals($userData, $result);
        $this->assertEquals(1, $_SESSION['user_id']);
        $this->assertEquals('2024001', $_SESSION['school_id']);
    }

    public function testLoginFailure()
    {
        // Arrange
        $this->mockUserService->method('authenticateUser')->willReturn(false);

        // Act
        $result = $this->authService->login('invalid', 'wrong');

        // Assert
        $this->assertFalse($result);
        $this->assertEmpty($_SESSION);
    }

    public function testIsAuthenticatedTrue()
    {
        // Arrange
        $_SESSION['user_id'] = 1;

        // Act
        $result = $this->authService->isAuthenticated();

        // Assert
        $this->assertTrue($result);
    }

    public function testIsAuthenticatedFalse()
    {
        // Act
        $result = $this->authService->isAuthenticated();

        // Assert
        $this->assertFalse($result);
    }

    public function testGetCurrentUserWhenAuthenticated()
    {
        // Arrange
        $_SESSION['user_id'] = 1;
        $_SESSION['school_id'] = '2024001';
        $_SESSION['full_name'] = 'John Doe';
        $_SESSION['role'] = 'student';

        // Act
        $result = $this->authService->getCurrentUser();

        // Assert
        $this->assertIsArray($result);
        $this->assertEquals(1, $result['user_id']);
        $this->assertEquals('2024001', $result['school_id']);
    }

    public function testGetCurrentUserWhenNotAuthenticated()
    {
        // Act
        $result = $this->authService->getCurrentUser();

        // Assert
        $this->assertFalse($result);
    }

    public function testHasRoleTrue()
    {
        // Arrange
        $_SESSION['user_id'] = 1;
        $_SESSION['role'] = 'admin';

        // Act
        $result = $this->authService->hasRole('admin');

        // Assert
        $this->assertTrue($result);
    }

    public function testHasRoleFalse()
    {
        // Arrange
        $_SESSION['user_id'] = 1;
        $_SESSION['role'] = 'student';

        // Act
        $result = $this->authService->hasRole('admin');

        // Assert
        $this->assertFalse($result);
    }

    public function testRequireAuthSuccess()
    {
        // Arrange
        $_SESSION['user_id'] = 1;

        // Act & Assert - Should not throw exception
        $this->authService->requireAuth();
        $this->assertTrue(true); // If we get here, no exception was thrown
    }

    public function testRequireAuthFailure()
    {
        // Arrange - No session data

        // Act & Assert
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Authentication required');
        $this->authService->requireAuth();
    }

    public function testRequireRoleSuccess()
    {
        // Arrange
        $_SESSION['user_id'] = 1;
        $_SESSION['role'] = 'admin';

        // Act & Assert - Should not throw exception
        $this->authService->requireRole('admin');
        $this->assertTrue(true); // If we get here, no exception was thrown
    }

    public function testRequireRoleFailureWrongRole()
    {
        // Arrange
        $_SESSION['user_id'] = 1;
        $_SESSION['role'] = 'student';

        // Act & Assert
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Access denied. Required role: admin');
        $this->authService->requireRole('admin');
    }

    public function testRequireRoleFailureNotAuthenticated()
    {
        // Arrange - No session data

        // Act & Assert
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Authentication required');
        $this->authService->requireRole('admin');
    }

    public function testStartSession()
    {
        // Arrange
        $userData = [
            'user_id' => 1,
            'school_id' => '2024001',
            'full_name' => 'John Doe',
            'role' => 'student',
            'year_level' => 10,
            'section' => 'A'
        ];

        // Act
        $result = $this->authService->startSession($userData);

        // Assert
        $this->assertTrue($result);
        $this->assertEquals(1, $_SESSION['user_id']);
        $this->assertEquals('2024001', $_SESSION['school_id']);
        $this->assertEquals('John Doe', $_SESSION['full_name']);
        $this->assertEquals('student', $_SESSION['role']);
        $this->assertArrayHasKey('login_time', $_SESSION);
    }

    public function testLogout()
    {
        // Arrange
        $_SESSION['user_id'] = 1;
        $_SESSION['school_id'] = '2024001';

        // Act
        $result = $this->authService->logout();

        // Assert
        $this->assertTrue($result);
        // Note: In real test environment, session might not be fully destroyed
        // but the method should return true indicating success
    }

    public function testValidatePasswordValid()
    {
        // Act
        $errors = $this->authService->validatePassword('password123');

        // Assert
        $this->assertEmpty($errors);
    }

    public function testValidatePasswordTooShort()
    {
        // Act
        $errors = $this->authService->validatePassword('123');

        // Assert
        $this->assertNotEmpty($errors);
        $this->assertContains('Password must be at least 6 characters long', $errors);
    }

    public function testValidatePasswordNoLetter()
    {
        // Act
        $errors = $this->authService->validatePassword('123456');

        // Assert
        $this->assertNotEmpty($errors);
        $this->assertContains('Password must contain at least one letter', $errors);
    }

    public function testValidatePasswordNoNumber()
    {
        // Act
        $errors = $this->authService->validatePassword('password');

        // Assert
        $this->assertNotEmpty($errors);
        $this->assertContains('Password must contain at least one number', $errors);
    }

    public function testValidatePasswordTooLong()
    {
        // Arrange
        $longPassword = str_repeat('a1', 65); // 130 characters

        // Act
        $errors = $this->authService->validatePassword($longPassword);

        // Assert
        $this->assertNotEmpty($errors);
        $this->assertContains('Password must not exceed 128 characters', $errors);
    }
}