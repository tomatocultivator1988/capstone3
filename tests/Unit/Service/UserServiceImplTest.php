<?php

/**
 * UserServiceImpl Unit Tests
 * 
 * Tests all methods in UserServiceImpl class following TDD principles.
 * Uses mock dependencies for isolated testing.
 */

require_once __DIR__ . '/../../BaseTest.php';
require_once __DIR__ . '/../../../src/dao/interface/UserDAOInterface.php';
require_once __DIR__ . '/../../../src/service/UserServiceImpl.php';
require_once __DIR__ . '/../../../src/model/User.php';

use Service\Impl\UserServiceImpl;
use Model\User;
use Dao\Interface\UserDAOInterface;

class UserServiceImplTest extends BaseTest
{
    private $mockUserDAO;
    private UserServiceImpl $userService;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create mock DAO for isolated testing
        $this->mockUserDAO = new class implements UserDAOInterface {
            private $users = [];
            private $lastInsertId = 0;
            
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
            
            public function findAll(): array { 
                return array_values($this->users); 
            }
            
            public function findByRole(string $role): array { 
                return array_filter($this->users, fn($u) => $u->getRole() === $role);
            }
            
            public function create(User $user): bool { 
                $this->lastInsertId++;
                $user->setUserId($this->lastInsertId);
                $this->users[$this->lastInsertId] = $user;
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
            
            public function getTotalCount(): int { 
                return count($this->users); 
            }
            
            public function findWithPagination(int $limit, int $offset): array { 
                return array_slice(array_values($this->users), $offset, $limit);
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
            
            public function clearUsers(): void {
                $this->users = [];
                $this->lastInsertId = 0;
            }
        };
        
        $this->userService = new UserServiceImpl($this->mockUserDAO);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->mockUserDAO->clearUsers();
    }

    // ===== CREATE USER TESTS =====

    public function testCreateUser_WithValidStudentData_ShouldSucceed(): void
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
        $this->assertTrue($result, "User creation should succeed with valid data");
        $this->assertTrue($this->userService->userExists($school_id), "User should exist after creation");
        
        $createdUser = $this->userService->getUserBySchoolId($school_id);
        $this->assertEquals($school_id, $createdUser->getSchoolId());
        $this->assertEquals($full_name, $createdUser->getFullName());
        $this->assertEquals($role, $createdUser->getRole());
    }

    public function testCreateUser_WithValidFacultyData_ShouldSucceed(): void
    {
        // Arrange
        $school_id = 'FAC123';
        $full_name = 'Jane Professor';
        $role = 'faculty';

        // Act
        $result = $this->userService->createUser($school_id, $full_name, $role);

        // Assert
        $this->assertTrue($result, "Faculty creation should succeed");
        
        $createdUser = $this->userService->getUserBySchoolId($school_id);
        $this->assertEquals('faculty', $createdUser->getRole());
        $this->assertNull($createdUser->getYearLevel());
        $this->assertNull($createdUser->getSection());
    }

    public function testCreateUser_WithDuplicateSchoolId_ShouldFail(): void
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

    public function testCreateUser_WithInvalidData_ShouldFail(): void
    {
        // Test empty school ID
        $result1 = $this->userService->createUser('', 'John Doe', 'student', 1, 'A');
        $this->assertFalse($result1, "Should fail with empty school ID");

        // Test invalid role
        $result2 = $this->userService->createUser('STU123', 'John Doe', 'invalid', 1, 'A');
        $this->assertFalse($result2, "Should fail with invalid role");

        // Test student without year level
        $result3 = $this->userService->createUser('STU123', 'John Doe', 'student', null, 'A');
        $this->assertFalse($result3, "Should fail student without year level");
    }

    // ===== UPDATE USER TESTS =====

    public function testUpdateUser_WithValidData_ShouldSucceed(): void
    {
        // Arrange
        $user = new User([
            'user_id' => 1,
            'school_id' => 'STU123',
            'full_name' => 'Old Name',
            'role' => 'student',
            'year_level' => 1,
            'section' => 'A'
        ]);
        $this->mockUserDAO->addTestUser($user);

        // Act
        $result = $this->userService->updateUser(1, 'STU123', 'New Name', 'student', 2, 'B');

        // Assert
        $this->assertTrue($result, "User update should succeed");
        
        $updatedUser = $this->userService->getUserById(1);
        $this->assertEquals('New Name', $updatedUser->getFullName());
        $this->assertEquals(2, $updatedUser->getYearLevel());
        $this->assertEquals('B', $updatedUser->getSection());
    }

    public function testUpdateUser_WithNonExistentUser_ShouldFail(): void
    {
        // Act
        $result = $this->userService->updateUser(999, 'STU123', 'John Doe', 'student', 1, 'A');

        // Assert
        $this->assertFalse($result, "Should fail when updating non-existent user");
    }

    public function testUpdateUser_WithDuplicateSchoolId_ShouldFail(): void
    {
        // Arrange
        $user1 = new User(['user_id' => 1, 'school_id' => 'STU123']);
        $user2 = new User(['user_id' => 2, 'school_id' => 'STU456']);
        $this->mockUserDAO->addTestUser($user1);
        $this->mockUserDAO->addTestUser($user2);

        // Act - Try to change user2's school_id to user1's school_id
        $result = $this->userService->updateUser(2, 'STU123', 'John Doe', 'student', 1, 'A');

        // Assert
        $this->assertFalse($result, "Should fail when updating to duplicate school ID");
    }

    // ===== DELETE USER TESTS =====

    public function testDeleteUser_WithValidStudentId_ShouldSucceed(): void
    {
        // Arrange
        $user = new User([
            'user_id' => 1,
            'school_id' => 'STU123',
            'role' => 'student'
        ]);
        $this->mockUserDAO->addTestUser($user);

        // Act
        $result = $this->userService->deleteUser(1);

        // Assert
        $this->assertTrue($result, "Should successfully delete student user");
        $this->assertNull($this->userService->getUserById(1), "User should no longer exist");
    }

    public function testDeleteUser_WithAdminUser_ShouldFail(): void
    {
        // Arrange
        $adminUser = new User([
            'user_id' => 1,
            'school_id' => 'ADM123',
            'role' => 'admin'
        ]);
        $this->mockUserDAO->addTestUser($adminUser);

        // Act
        $result = $this->userService->deleteUser(1);

        // Assert
        $this->assertFalse($result, "Should not allow deletion of admin users");
        $this->assertNotNull($this->userService->getUserById(1), "Admin user should still exist");
    }

    public function testDeleteUser_WithNonExistentUser_ShouldFail(): void
    {
        // Act
        $result = $this->userService->deleteUser(999);

        // Assert
        $this->assertFalse($result, "Should fail when deleting non-existent user");
    }

    // ===== VALIDATION TESTS =====

    public function testValidateUserData_WithValidStudentData_ShouldReturnNoErrors(): void
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
        $this->assertEmpty($errors, "Valid student data should have no errors");
    }

    public function testValidateUserData_WithValidFacultyData_ShouldReturnNoErrors(): void
    {
        // Arrange
        $validData = [
            'school_id' => 'FAC123',
            'full_name' => 'Jane Professor',
            'role' => 'faculty'
        ];

        // Act
        $errors = $this->userService->validateUserData($validData);

        // Assert
        $this->assertEmpty($errors, "Valid faculty data should have no errors");
    }

    public function testValidateUserData_WithEmptySchoolId_ShouldReturnError(): void
    {
        // Arrange
        $invalidData = ['school_id' => '', 'full_name' => 'John', 'role' => 'student'];

        // Act
        $errors = $this->userService->validateUserData($invalidData);

        // Assert
        $this->assertNotEmpty($errors, "Empty school ID should produce errors");
        $this->assertContains('School ID is required', $errors);
    }

    public function testValidateUserData_WithShortName_ShouldReturnError(): void
    {
        // Arrange
        $invalidData = ['school_id' => 'STU123', 'full_name' => 'A', 'role' => 'student'];

        // Act
        $errors = $this->userService->validateUserData($invalidData);

        // Assert
        $this->assertContains('Full name must be at least 2 characters', $errors);
    }

    public function testValidateUserData_WithInvalidRole_ShouldReturnError(): void
    {
        // Arrange
        $invalidData = ['school_id' => 'STU123', 'full_name' => 'John Doe', 'role' => 'invalid'];

        // Act
        $errors = $this->userService->validateUserData($invalidData);

        // Assert
        $this->assertContains('Invalid role. Must be admin, faculty, or student', $errors);
    }

    public function testValidateUserData_WithStudentMissingYearLevel_ShouldReturnError(): void
    {
        // Arrange
        $invalidData = [
            'school_id' => 'STU123',
            'full_name' => 'John Doe',
            'role' => 'student',
            'section' => 'A'
        ];

        // Act
        $errors = $this->userService->validateUserData($invalidData);

        // Assert
        $this->assertContains('Year level is required for students', $errors);
    }

    // ===== RETRIEVAL TESTS =====

    public function testGetAllUsers_WithNoFilter_ShouldReturnAllUsers(): void
    {
        // Arrange
        $users = [
            new User(['user_id' => 1, 'role' => 'admin']),
            new User(['user_id' => 2, 'role' => 'faculty']),
            new User(['user_id' => 3, 'role' => 'student'])
        ];
        foreach ($users as $user) {
            $this->mockUserDAO->addTestUser($user);
        }

        // Act
        $result = $this->userService->getAllUsers();

        // Assert
        $this->assertCount(3, $result, "Should return all users");
    }

    public function testGetAllUsers_WithRoleFilter_ShouldReturnFilteredUsers(): void
    {
        // Arrange
        $users = [
            new User(['user_id' => 1, 'role' => 'admin']),
            new User(['user_id' => 2, 'role' => 'student']),
            new User(['user_id' => 3, 'role' => 'student'])
        ];
        foreach ($users as $user) {
            $this->mockUserDAO->addTestUser($user);
        }

        // Act
        $result = $this->userService->getAllUsers('student');

        // Assert
        $this->assertCount(2, $result, "Should return only student users");
        foreach ($result as $user) {
            $this->assertEquals('student', $user->getRole());
        }
    }

    public function testGetUserStatistics_ShouldReturnCorrectCounts(): void
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
        $this->assertEquals(4, $stats['total'], "Total count should be 4");
        $this->assertEquals(1, $stats['admin'], "Admin count should be 1");
        $this->assertEquals(1, $stats['faculty'], "Faculty count should be 1");
        $this->assertEquals(2, $stats['student'], "Student count should be 2");
    }

    public function testGetUsersWithPagination_ShouldReturnCorrectSlice(): void
    {
        // Arrange
        for ($i = 1; $i <= 10; $i++) {
            $user = new User(['user_id' => $i, 'school_id' => "STU$i"]);
            $this->mockUserDAO->addTestUser($user);
        }

        // Act
        $page1 = $this->userService->getUsersWithPagination(1, 3); // First 3 users
        $page2 = $this->userService->getUsersWithPagination(2, 3); // Next 3 users

        // Assert
        $this->assertCount(3, $page1, "First page should have 3 users");
        $this->assertCount(3, $page2, "Second page should have 3 users");
        $this->assertEquals('STU1', $page1[0]->getSchoolId(), "First page should start with STU1");
        $this->assertEquals('STU4', $page2[0]->getSchoolId(), "Second page should start with STU4");
    }

    // ===== UTILITY TESTS =====

    public function testUserExists_WithExistingUser_ShouldReturnTrue(): void
    {
        // Arrange
        $user = new User(['user_id' => 1, 'school_id' => 'STU123']);
        $this->mockUserDAO->addTestUser($user);

        // Act
        $exists = $this->userService->userExists('STU123');

        // Assert
        $this->assertTrue($exists, "Should return true for existing user");
    }

    public function testUserExists_WithNonExistentUser_ShouldReturnFalse(): void
    {
        // Act
        $exists = $this->userService->userExists('NONEXISTENT');

        // Assert
        $this->assertFalse($exists, "Should return false for non-existent user");
    }

    public function testResetPassword_WithValidUser_ShouldSucceed(): void
    {
        // Arrange
        $user = new User([
            'user_id' => 1,
            'school_id' => 'STU123',
            'full_name' => 'John Doe',
            'password' => 'oldpassword'
        ]);
        $this->mockUserDAO->addTestUser($user);

        // Act
        $result = $this->userService->resetPassword(1);

        // Assert
        $this->assertTrue($result, "Password reset should succeed");
        
        // Verify password was changed (would be hashed in real implementation)
        $updatedUser = $this->userService->getUserById(1);
        $this->assertNotEquals('oldpassword', $updatedUser->getPassword(), "Password should be changed");
    }

    public function testResetPassword_WithNonExistentUser_ShouldFail(): void
    {
        // Act
        $result = $this->userService->resetPassword(999);

        // Assert
        $this->assertFalse($result, "Should fail for non-existent user");
    }
}