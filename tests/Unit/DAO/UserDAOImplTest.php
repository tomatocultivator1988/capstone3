<?php

/**
 * UserDAOImpl Unit Tests
 * 
 * Tests all methods in UserDAOImpl class following TDD principles.
 * Tests database operations with actual database connections.
 */

require_once __DIR__ . '/../../BaseTest.php';

use Dao\Impl\UserDAOImpl;
use Model\User;
use App\Config\Database;

class UserDAOImplTest extends BaseTest
{
    private UserDAOImpl $userDAO;
    private $testUserId = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userDAO = new UserDAOImpl();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        
        // Clean up test user if created
        if ($this->testUserId !== null) {
            try {
                $this->userDAO->deleteById($this->testUserId);
            } catch (Exception $e) {
                // Ignore cleanup errors
            }
            $this->testUserId = null;
        }
    }

    // ===== CREATE TESTS =====

    public function testCreate_WithValidStudentUser_ShouldSucceed(): void
    {
        // Arrange
        $user = new User([
            'school_id' => 'TEST_STU_' . uniqid(),
            'full_name' => 'Test Student',
            'password' => password_hash('testpass', PASSWORD_DEFAULT),
            'role' => 'student',
            'year_level' => 1,
            'section' => 'A'
        ]);

        // Act
        $result = $this->userDAO->create($user);

        // Assert
        $this->assertTrue($result, "Should successfully create student user");
        $this->assertNotNull($user->getUserId(), "User ID should be set after creation");
        
        // Store for cleanup
        $this->testUserId = $user->getUserId();
        
        // Verify user was actually created
        $retrievedUser = $this->userDAO->findById($user->getUserId());
        $this->assertNotNull($retrievedUser, "Created user should be retrievable");
        $this->assertEquals($user->getSchoolId(), $retrievedUser->getSchoolId());
        $this->assertEquals($user->getFullName(), $retrievedUser->getFullName());
    }

    public function testCreate_WithValidFacultyUser_ShouldSucceed(): void
    {
        // Arrange
        $user = new User([
            'school_id' => 'TEST_FAC_' . uniqid(),
            'full_name' => 'Test Faculty',
            'password' => password_hash('testpass', PASSWORD_DEFAULT),
            'role' => 'faculty'
        ]);

        // Act
        $result = $this->userDAO->create($user);

        // Assert
        $this->assertTrue($result, "Should successfully create faculty user");
        $this->assertNotNull($user->getUserId(), "User ID should be set after creation");
        
        // Store for cleanup
        $this->testUserId = $user->getUserId();
        
        // Verify faculty-specific fields
        $retrievedUser = $this->userDAO->findById($user->getUserId());
        $this->assertEquals('faculty', $retrievedUser->getRole());
        $this->assertNull($retrievedUser->getYearLevel(), "Faculty should not have year level");
        $this->assertNull($retrievedUser->getSection(), "Faculty should not have section");
    }

    public function testCreate_WithDuplicateSchoolId_ShouldFail(): void
    {
        // Arrange - Create first user
        $user1 = new User([
            'school_id' => 'DUPLICATE_TEST_' . uniqid(),
            'full_name' => 'First User',
            'password' => password_hash('pass1', PASSWORD_DEFAULT),
            'role' => 'student',
            'year_level' => 1,
            'section' => 'A'
        ]);
        
        $this->userDAO->create($user1);
        $this->testUserId = $user1->getUserId(); // Store for cleanup
        
        // Create second user with same school ID
        $user2 = new User([
            'school_id' => $user1->getSchoolId(),
            'full_name' => 'Second User',
            'password' => password_hash('pass2', PASSWORD_DEFAULT),
            'role' => 'student',
            'year_level' => 2,
            'section' => 'B'
        ]);

        // Act
        $result = $this->userDAO->create($user2);

        // Assert
        $this->assertFalse($result, "Should fail to create user with duplicate school ID");
        $this->assertNull($user2->getUserId(), "User ID should not be set on failed creation");
    }

    // ===== FIND TESTS =====

    public function testFindById_WithExistingUser_ShouldReturnUser(): void
    {
        // Arrange - Create a test user
        $user = new User([
            'school_id' => 'FIND_TEST_' . uniqid(),
            'full_name' => 'Find Test User',
            'password' => password_hash('findpass', PASSWORD_DEFAULT),
            'role' => 'student',
            'year_level' => 2,
            'section' => 'B'
        ]);
        
        $this->userDAO->create($user);
        $this->testUserId = $user->getUserId();

        // Act
        $foundUser = $this->userDAO->findById($user->getUserId());

        // Assert
        $this->assertNotNull($foundUser, "Should find existing user");
        $this->assertEquals($user->getUserId(), $foundUser->getUserId());
        $this->assertEquals($user->getSchoolId(), $foundUser->getSchoolId());
        $this->assertEquals($user->getFullName(), $foundUser->getFullName());
        $this->assertEquals($user->getRole(), $foundUser->getRole());
        $this->assertEquals($user->getYearLevel(), $foundUser->getYearLevel());
        $this->assertEquals($user->getSection(), $foundUser->getSection());
    }

    public function testFindById_WithNonExistentUser_ShouldReturnNull(): void
    {
        // Act
        $foundUser = $this->userDAO->findById(999999);

        // Assert
        $this->assertNull($foundUser, "Should return null for non-existent user");
    }

    public function testFindBySchoolId_WithExistingUser_ShouldReturnUser(): void
    {
        // Arrange
        $schoolId = 'SCHOOL_FIND_' . uniqid();
        $user = new User([
            'school_id' => $schoolId,
            'full_name' => 'School Find Test',
            'password' => password_hash('schoolpass', PASSWORD_DEFAULT),
            'role' => 'faculty'
        ]);
        
        $this->userDAO->create($user);
        $this->testUserId = $user->getUserId();

        // Act
        $foundUser = $this->userDAO->findBySchoolId($schoolId);

        // Assert
        $this->assertNotNull($foundUser, "Should find user by school ID");
        $this->assertEquals($schoolId, $foundUser->getSchoolId());
        $this->assertEquals($user->getFullName(), $foundUser->getFullName());
    }

    public function testFindBySchoolId_WithNonExistentSchoolId_ShouldReturnNull(): void
    {
        // Act
        $foundUser = $this->userDAO->findBySchoolId('NONEXISTENT_SCHOOL_ID');

        // Assert
        $this->assertNull($foundUser, "Should return null for non-existent school ID");
    }

    public function testFindAll_ShouldReturnArrayOfUsers(): void
    {
        // Act
        $users = $this->userDAO->findAll();

        // Assert
        $this->assertTrue(is_array($users), "Should return array");
        
        if (count($users) > 0) {
            $this->assertInstanceOf(User::class, $users[0], "Should return User objects");
        }
    }

    public function testFindByRole_WithSpecificRole_ShouldReturnFilteredUsers(): void
    {
        // Act
        $students = $this->userDAO->findByRole('student');
        $faculty = $this->userDAO->findByRole('faculty');
        $admins = $this->userDAO->findByRole('admin');

        // Assert
        $this->assertTrue(is_array($students), "Should return array for students");
        $this->assertTrue(is_array($faculty), "Should return array for faculty");
        $this->assertTrue(is_array($admins), "Should return array for admins");
        
        // Verify role filtering if users exist
        if (count($students) > 0) {
            foreach ($students as $student) {
                $this->assertEquals('student', $student->getRole(), "All returned users should be students");
            }
        }
        
        if (count($faculty) > 0) {
            foreach ($faculty as $facultyMember) {
                $this->assertEquals('faculty', $facultyMember->getRole(), "All returned users should be faculty");
            }
        }
    }

    // ===== UPDATE TESTS =====

    public function testUpdate_WithValidChanges_ShouldSucceed(): void
    {
        // Arrange - Create user to update
        $user = new User([
            'school_id' => 'UPDATE_TEST_' . uniqid(),
            'full_name' => 'Original Name',
            'password' => password_hash('originalpass', PASSWORD_DEFAULT),
            'role' => 'student',
            'year_level' => 1,
            'section' => 'A'
        ]);
        
        $this->userDAO->create($user);
        $this->testUserId = $user->getUserId();
        
        // Modify user data
        $user->setFullName('Updated Name');
        $user->setYearLevel(2);
        $user->setSection('B');

        // Act
        $result = $this->userDAO->update($user);

        // Assert
        $this->assertTrue($result, "Should successfully update user");
        
        // Verify changes were persisted
        $updatedUser = $this->userDAO->findById($user->getUserId());
        $this->assertEquals('Updated Name', $updatedUser->getFullName());
        $this->assertEquals(2, $updatedUser->getYearLevel());
        $this->assertEquals('B', $updatedUser->getSection());
    }

    public function testUpdate_WithNonExistentUser_ShouldFail(): void
    {
        // Arrange
        $nonExistentUser = new User([
            'user_id' => 999999,
            'school_id' => 'NONEXISTENT',
            'full_name' => 'Non Existent',
            'role' => 'student'
        ]);

        // Act
        $result = $this->userDAO->update($nonExistentUser);

        // Assert
        $this->assertFalse($result, "Should fail to update non-existent user");
    }

    // ===== DELETE TESTS =====

    public function testDeleteById_WithExistingUser_ShouldSucceed(): void
    {
        // Arrange - Create user to delete
        $user = new User([
            'school_id' => 'DELETE_TEST_' . uniqid(),
            'full_name' => 'Delete Test User',
            'password' => password_hash('deletepass', PASSWORD_DEFAULT),
            'role' => 'student',
            'year_level' => 1,
            'section' => 'A'
        ]);
        
        $this->userDAO->create($user);
        $userId = $user->getUserId();

        // Act
        $result = $this->userDAO->deleteById($userId);

        // Assert
        $this->assertTrue($result, "Should successfully delete user");
        
        // Verify user was actually deleted
        $deletedUser = $this->userDAO->findById($userId);
        $this->assertNull($deletedUser, "Deleted user should not be found");
        
        // Don't set testUserId since we already deleted
    }

    public function testDeleteById_WithNonExistentUser_ShouldFail(): void
    {
        // Act
        $result = $this->userDAO->deleteById(999999);

        // Assert
        $this->assertFalse($result, "Should fail to delete non-existent user");
    }

    // ===== UTILITY TESTS =====

    public function testExistsBySchoolId_WithExistingUser_ShouldReturnTrue(): void
    {
        // Arrange
        $schoolId = 'EXISTS_TEST_' . uniqid();
        $user = new User([
            'school_id' => $schoolId,
            'full_name' => 'Exists Test User',
            'password' => password_hash('existspass', PASSWORD_DEFAULT),
            'role' => 'student',
            'year_level' => 1,
            'section' => 'A'
        ]);
        
        $this->userDAO->create($user);
        $this->testUserId = $user->getUserId();

        // Act
        $exists = $this->userDAO->existsBySchoolId($schoolId);

        // Assert
        $this->assertTrue($exists, "Should return true for existing school ID");
    }

    public function testExistsBySchoolId_WithNonExistentUser_ShouldReturnFalse(): void
    {
        // Act
        $exists = $this->userDAO->existsBySchoolId('DEFINITELY_NONEXISTENT_' . uniqid());

        // Assert
        $this->assertFalse($exists, "Should return false for non-existent school ID");
    }

    public function testGetTotalCount_ShouldReturnValidCount(): void
    {
        // Act
        $count = $this->userDAO->getTotalCount();

        // Assert
        $this->assertTrue($count >= 0, "Count should be non-negative");
        $this->assertTrue(is_int($count), "Count should be integer");
    }

    // ===== PAGINATION TESTS =====

    public function testFindWithPagination_ShouldReturnLimitedResults(): void
    {
        // Act
        $page1 = $this->userDAO->findWithPagination(2, 0); // First 2 users
        $page2 = $this->userDAO->findWithPagination(2, 2); // Next 2 users

        // Assert
        $this->assertTrue(is_array($page1), "First page should be array");
        $this->assertTrue(is_array($page2), "Second page should be array");
        $this->assertTrue(count($page1) <= 2, "First page should have max 2 users");
        $this->assertTrue(count($page2) <= 2, "Second page should have max 2 users");
        
        if (count($page1) > 0) {
            $this->assertInstanceOf(User::class, $page1[0], "Should return User objects");
        }
    }

    // ===== STUDENT-SPECIFIC TESTS =====

    public function testFindStudentsByYearAndSection_ShouldReturnFilteredStudents(): void
    {
        // Arrange - Create test student
        $user = new User([
            'school_id' => 'YEAR_SECTION_TEST_' . uniqid(),
            'full_name' => 'Year Section Test',
            'password' => password_hash('yspass', PASSWORD_DEFAULT),
            'role' => 'student',
            'year_level' => 3,
            'section' => 'C'
        ]);
        
        $this->userDAO->create($user);
        $this->testUserId = $user->getUserId();

        // Act
        $students = $this->userDAO->findStudentsByYearAndSection(3, 'C');

        // Assert
        $this->assertTrue(is_array($students), "Should return array");
        
        // Verify filtering
        $found = false;
        foreach ($students as $student) {
            $this->assertEquals('student', $student->getRole(), "Should only return students");
            $this->assertEquals(3, $student->getYearLevel(), "Should only return year 3 students");
            $this->assertEquals('C', $student->getSection(), "Should only return section C students");
            
            if ($student->getUserId() === $user->getUserId()) {
                $found = true;
            }
        }
        
        $this->assertTrue($found, "Should find our test student in results");
    }

    // ===== PASSWORD TESTS =====

    public function testUpdatePassword_WithValidUser_ShouldSucceed(): void
    {
        // Arrange - Create user
        $user = new User([
            'school_id' => 'PASSWORD_TEST_' . uniqid(),
            'full_name' => 'Password Test User',
            'password' => password_hash('oldpass', PASSWORD_DEFAULT),
            'role' => 'student',
            'year_level' => 1,
            'section' => 'A'
        ]);
        
        $this->userDAO->create($user);
        $this->testUserId = $user->getUserId();
        
        $newHashedPassword = password_hash('newpass', PASSWORD_DEFAULT);

        // Act
        $result = $this->userDAO->updatePassword($user->getUserId(), $newHashedPassword);

        // Assert
        $this->assertTrue($result, "Should successfully update password");
        
        // Verify password was updated
        $updatedUser = $this->userDAO->findById($user->getUserId());
        $this->assertTrue(password_verify('newpass', $updatedUser->getPassword()), "New password should verify");
        $this->assertFalse(password_verify('oldpass', $updatedUser->getPassword()), "Old password should not verify");
    }

    public function testUpdatePassword_WithNonExistentUser_ShouldFail(): void
    {
        // Act
        $result = $this->userDAO->updatePassword(999999, password_hash('anypass', PASSWORD_DEFAULT));

        // Assert
        $this->assertFalse($result, "Should fail to update password for non-existent user");
    }

    // ===== ERROR HANDLING TESTS =====

    public function testCreate_WithInvalidData_ShouldHandleGracefully(): void
    {
        // Arrange - User without required fields
        $invalidUser = new User([]);

        // Act
        $result = $this->userDAO->create($invalidUser);

        // Assert
        $this->assertFalse($result, "Should handle invalid data gracefully");
    }

    public function testFindById_WithInvalidId_ShouldReturnNull(): void
    {
        // Act
        $result = $this->userDAO->findById(-1);

        // Assert
        $this->assertNull($result, "Should return null for invalid ID");
    }

    public function testFindByRole_WithInvalidRole_ShouldReturnEmptyArray(): void
    {
        // Act
        $result = $this->userDAO->findByRole('invalid_role');

        // Assert
        $this->assertTrue(is_array($result), "Should return array even for invalid role");
        $this->assertEmpty($result, "Should return empty array for invalid role");
    }
}