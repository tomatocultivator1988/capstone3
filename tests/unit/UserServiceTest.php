<?php

use PHPUnit\Framework\TestCase;
use App\Services\UserServiceImpl;
use App\Models\User;

class UserServiceTest extends TestCase
{
    private $mockUserModel;
    private $userService;

    protected function setUp(): void
    {
        $this->mockUserModel = $this->createMock(User::class);
        $this->userService = new UserServiceImpl($this->mockUserModel);
    }

    public function testCreateUserSuccess()
    {
        // Arrange
        $userData = [
            'school_id' => '2024001',
            'full_name' => 'John Doe',
            'role' => 'student',
            'year_level' => 10,
            'section' => 'A'
        ];

        $this->mockUserModel->method('findBySchoolId')->willReturn(false);
        $this->mockUserModel->method('create')->willReturn(123);

        // Act
        $result = $this->userService->createUser(
            $userData['school_id'],
            $userData['full_name'],
            $userData['role'],
            $userData['year_level'],
            $userData['section']
        );

        // Assert
        $this->assertEquals(123, $result);
    }

    public function testCreateUserValidationFailure()
    {
        // Act
        $result = $this->userService->createUser('', '', 'invalid_role');

        // Assert
        $this->assertFalse($result);
    }

    public function testCreateUserDuplicateSchoolId()
    {
        // Arrange
        $this->mockUserModel->method('findBySchoolId')->willReturn(['user_id' => 1]);

        // Act
        $result = $this->userService->createUser('2024001', 'John Doe', 'student', 10, 'A');

        // Assert
        $this->assertFalse($result);
    }

    public function testUpdateUserSuccess()
    {
        // Arrange
        $this->mockUserModel->method('findById')->willReturn(['user_id' => 1, 'school_id' => '2024001']);
        $this->mockUserModel->method('findBySchoolId')->willReturn(['user_id' => 1]);
        $this->mockUserModel->method('update')->willReturn(true);

        // Act
        $result = $this->userService->updateUser(1, '2024001', 'John Updated', 'student', 11, 'B');

        // Assert
        $this->assertTrue($result);
    }

    public function testUpdateUserNotFound()
    {
        // Arrange
        $this->mockUserModel->method('findById')->willReturn(false);

        // Act
        $result = $this->userService->updateUser(999, '2024001', 'John Doe', 'student', 10, 'A');

        // Assert
        $this->assertFalse($result);
    }

    public function testDeleteUserSuccess()
    {
        // Arrange
        $this->mockUserModel->method('findById')->willReturn(['user_id' => 1]);
        $this->mockUserModel->method('delete')->willReturn(true);

        // Act
        $result = $this->userService->deleteUser(1);

        // Assert
        $this->assertTrue($result);
    }

    public function testDeleteUserNotFound()
    {
        // Arrange
        $this->mockUserModel->method('findById')->willReturn(false);

        // Act
        $result = $this->userService->deleteUser(999);

        // Assert
        $this->assertFalse($result);
    }

    public function testGetUserById()
    {
        // Arrange
        $userData = ['user_id' => 1, 'school_id' => '2024001', 'full_name' => 'John Doe'];
        $this->mockUserModel->method('findById')->willReturn($userData);

        // Act
        $result = $this->userService->getUserById(1);

        // Assert
        $this->assertEquals($userData, $result);
    }

    public function testGetAllUsers()
    {
        // Arrange
        $usersData = [
            ['user_id' => 1, 'school_id' => '2024001'],
            ['user_id' => 2, 'school_id' => '2024002']
        ];
        $this->mockUserModel->method('getAllUsers')->willReturn($usersData);

        // Act
        $result = $this->userService->getAllUsers();

        // Assert
        $this->assertEquals($usersData, $result);
    }

    public function testGetUsersByRole()
    {
        // Arrange
        $studentsData = [
            ['user_id' => 1, 'role' => 'student'],
            ['user_id' => 2, 'role' => 'student']
        ];
        $this->mockUserModel->method('getUsersByRole')->willReturn($studentsData);

        // Act
        $result = $this->userService->getUsersByRole('student');

        // Assert
        $this->assertEquals($studentsData, $result);
    }

    public function testAuthenticateUser()
    {
        // Arrange
        $userData = ['user_id' => 1, 'school_id' => '2024001'];
        $this->mockUserModel->method('authenticate')->willReturn($userData);

        // Act
        $result = $this->userService->authenticateUser('2024001', 'password');

        // Assert
        $this->assertEquals($userData, $result);
    }

    public function testValidateUserDataValid()
    {
        // Arrange
        $validData = [
            'school_id' => '2024001',
            'full_name' => 'John Doe',
            'role' => 'student',
            'year_level' => 10,
            'section' => 'A'
        ];

        // Act
        $errors = $this->userService->validateUserData($validData);

        // Assert
        $this->assertEmpty($errors);
    }

    public function testValidateUserDataInvalid()
    {
        // Arrange
        $invalidData = [
            'school_id' => '',
            'full_name' => '',
            'role' => 'invalid_role'
        ];

        // Act
        $errors = $this->userService->validateUserData($invalidData);

        // Assert
        $this->assertNotEmpty($errors);
        $this->assertContains('School ID is required', $errors);
        $this->assertContains('Full name is required', $errors);
    }

    public function testValidateStudentRequiresYearAndSection()
    {
        // Arrange
        $studentData = [
            'school_id' => '2024001',
            'full_name' => 'John Doe',
            'role' => 'student'
            // Missing year_level and section
        ];

        // Act
        $errors = $this->userService->validateUserData($studentData);

        // Assert
        $this->assertNotEmpty($errors);
        $this->assertContains('Year level is required for students', $errors);
        $this->assertContains('Section is required for students', $errors);
    }

    public function testUserExists()
    {
        // Arrange
        $this->mockUserModel->method('findBySchoolId')->willReturn(['user_id' => 1]);

        // Act
        $result = $this->userService->userExists('2024001');

        // Assert
        $this->assertTrue($result);
    }

    public function testUserNotExists()
    {
        // Arrange
        $this->mockUserModel->method('findBySchoolId')->willReturn(false);

        // Act
        $result = $this->userService->userExists('2024999');

        // Assert
        $this->assertFalse($result);
    }
}