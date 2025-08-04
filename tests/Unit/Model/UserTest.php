<?php

/**
 * User Model Unit Tests
 * 
 * Tests all methods and functionality in User model class.
 * Focuses on data validation, hydration, and utility methods.
 */

require_once __DIR__ . '/../../BaseTest.php';

use Model\User;

class UserTest extends BaseTest
{
    // ===== CONSTRUCTOR AND HYDRATION TESTS =====

    public function testConstructor_WithEmptyData_ShouldCreateUser(): void
    {
        // Act
        $user = new User([]);

        // Assert
        $this->assertInstanceOf(User::class, $user, "Should create User instance");
        $this->assertNull($user->getUserId(), "User ID should be null by default");
        $this->assertNull($user->getSchoolId(), "School ID should be null by default");
    }

    public function testConstructor_WithCompleteStudentData_ShouldHydrateCorrectly(): void
    {
        // Arrange
        $userData = [
            'user_id' => 1,
            'school_id' => 'STU123',
            'full_name' => 'John Doe',
            'password' => 'hashedpassword',
            'role' => 'student',
            'year_level' => 2,
            'section' => 'A',
            'created_at' => '2024-01-01 10:00:00',
            'updated_at' => '2024-01-02 11:00:00'
        ];

        // Act
        $user = new User($userData);

        // Assert
        $this->assertEquals(1, $user->getUserId());
        $this->assertEquals('STU123', $user->getSchoolId());
        $this->assertEquals('John Doe', $user->getFullName());
        $this->assertEquals('hashedpassword', $user->getPassword());
        $this->assertEquals('student', $user->getRole());
        $this->assertEquals(2, $user->getYearLevel());
        $this->assertEquals('A', $user->getSection());
        $this->assertEquals('2024-01-01 10:00:00', $user->getCreatedAt());
        $this->assertEquals('2024-01-02 11:00:00', $user->getUpdatedAt());
    }

    public function testConstructor_WithFacultyData_ShouldHydrateCorrectly(): void
    {
        // Arrange
        $userData = [
            'user_id' => 2,
            'school_id' => 'FAC456',
            'full_name' => 'Jane Professor',
            'role' => 'faculty'
        ];

        // Act
        $user = new User($userData);

        // Assert
        $this->assertEquals('faculty', $user->getRole());
        $this->assertNull($user->getYearLevel(), "Faculty should not have year level");
        $this->assertNull($user->getSection(), "Faculty should not have section");
    }

    public function testHydrate_WithNewData_ShouldUpdateProperties(): void
    {
        // Arrange
        $user = new User(['full_name' => 'Old Name']);
        $newData = [
            'user_id' => 5,
            'full_name' => 'New Name',
            'role' => 'admin'
        ];

        // Act
        $user->hydrate($newData);

        // Assert
        $this->assertEquals(5, $user->getUserId());
        $this->assertEquals('New Name', $user->getFullName());
        $this->assertEquals('admin', $user->getRole());
    }

    // ===== SETTER TESTS =====

    public function testSetUserId_WithValidId_ShouldSet(): void
    {
        // Arrange
        $user = new User([]);

        // Act
        $user->setUserId(10);

        // Assert
        $this->assertEquals(10, $user->getUserId());
    }

    public function testSetSchoolId_WithValidId_ShouldSet(): void
    {
        // Arrange
        $user = new User([]);

        // Act
        $user->setSchoolId('NEW123');

        // Assert
        $this->assertEquals('NEW123', $user->getSchoolId());
    }

    public function testSetFullName_WithValidName_ShouldSet(): void
    {
        // Arrange
        $user = new User([]);

        // Act
        $user->setFullName('Updated Name');

        // Assert
        $this->assertEquals('Updated Name', $user->getFullName());
    }

    public function testSetPassword_WithValidPassword_ShouldSet(): void
    {
        // Arrange
        $user = new User([]);

        // Act
        $user->setPassword('newhashedpassword');

        // Assert
        $this->assertEquals('newhashedpassword', $user->getPassword());
    }

    public function testSetRole_WithValidRole_ShouldSet(): void
    {
        // Arrange
        $user = new User([]);

        // Act
        $user->setRole('faculty');

        // Assert
        $this->assertEquals('faculty', $user->getRole());
    }

    public function testSetYearLevel_WithValidLevel_ShouldSet(): void
    {
        // Arrange
        $user = new User([]);

        // Act
        $user->setYearLevel(3);

        // Assert
        $this->assertEquals(3, $user->getYearLevel());
    }

    public function testSetSection_WithValidSection_ShouldSet(): void
    {
        // Arrange
        $user = new User([]);

        // Act
        $user->setSection('B');

        // Assert
        $this->assertEquals('B', $user->getSection());
    }

    // ===== ROLE CHECKING TESTS =====

    public function testIsStudent_WithStudentRole_ShouldReturnTrue(): void
    {
        // Arrange
        $user = new User(['role' => 'student']);

        // Act & Assert
        $this->assertTrue($user->isStudent(), "Should return true for student role");
    }

    public function testIsStudent_WithNonStudentRole_ShouldReturnFalse(): void
    {
        // Arrange
        $faculty = new User(['role' => 'faculty']);
        $admin = new User(['role' => 'admin']);

        // Act & Assert
        $this->assertFalse($faculty->isStudent(), "Faculty should not be student");
        $this->assertFalse($admin->isStudent(), "Admin should not be student");
    }

    public function testIsFaculty_WithFacultyRole_ShouldReturnTrue(): void
    {
        // Arrange
        $user = new User(['role' => 'faculty']);

        // Act & Assert
        $this->assertTrue($user->isFaculty(), "Should return true for faculty role");
    }

    public function testIsFaculty_WithNonFacultyRole_ShouldReturnFalse(): void
    {
        // Arrange
        $student = new User(['role' => 'student']);
        $admin = new User(['role' => 'admin']);

        // Act & Assert
        $this->assertFalse($student->isFaculty(), "Student should not be faculty");
        $this->assertFalse($admin->isFaculty(), "Admin should not be faculty");
    }

    public function testIsAdmin_WithAdminRole_ShouldReturnTrue(): void
    {
        // Arrange
        $user = new User(['role' => 'admin']);

        // Act & Assert
        $this->assertTrue($user->isAdmin(), "Should return true for admin role");
    }

    public function testIsAdmin_WithNonAdminRole_ShouldReturnFalse(): void
    {
        // Arrange
        $student = new User(['role' => 'student']);
        $faculty = new User(['role' => 'faculty']);

        // Act & Assert
        $this->assertFalse($student->isAdmin(), "Student should not be admin");
        $this->assertFalse($faculty->isAdmin(), "Faculty should not be admin");
    }

    // ===== TO ARRAY TESTS =====

    public function testToArray_WithCompleteData_ShouldReturnArray(): void
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
        $user = new User($userData);

        // Act
        $array = $user->toArray();

        // Assert
        $this->assertEquals(1, $array['user_id']);
        $this->assertEquals('STU123', $array['school_id']);
        $this->assertEquals('John Doe', $array['full_name']);
        $this->assertEquals('student', $array['role']);
        $this->assertEquals(2, $array['year_level']);
        $this->assertEquals('A', $array['section']);
    }

    public function testToArray_ShouldNotIncludePassword(): void
    {
        // Arrange
        $user = new User([
            'school_id' => 'STU123',
            'password' => 'secret',
            'role' => 'student'
        ]);

        // Act
        $array = $user->toArray();

        // Assert
        $this->assertFalse(isset($array['password']), "Password should not be in array output");
    }

    public function testToArray_WithIncludePassword_ShouldIncludePassword(): void
    {
        // Arrange
        $user = new User([
            'school_id' => 'STU123',
            'password' => 'secret',
            'role' => 'student'
        ]);

        // Act
        $array = $user->toArray(true);

        // Assert
        $this->assertEquals('secret', $array['password'], "Password should be included when explicitly requested");
    }

    // ===== UTILITY METHOD TESTS =====

    public function testGetDisplayName_WithFullName_ShouldReturnFullName(): void
    {
        // Arrange
        $user = new User([
            'full_name' => 'John Doe',
            'school_id' => 'STU123'
        ]);

        // Act
        $displayName = $user->getDisplayName();

        // Assert
        $this->assertEquals('John Doe', $displayName);
    }

    public function testGetDisplayName_WithoutFullName_ShouldReturnSchoolId(): void
    {
        // Arrange
        $user = new User(['school_id' => 'STU123']);

        // Act
        $displayName = $user->getDisplayName();

        // Assert
        $this->assertEquals('STU123', $displayName);
    }

    public function testGetDisplayName_WithoutBoth_ShouldReturnGuest(): void
    {
        // Arrange
        $user = new User([]);

        // Act
        $displayName = $user->getDisplayName();

        // Assert
        $this->assertEquals('Guest', $displayName);
    }

    public function testGetStudentInfo_WithStudentData_ShouldReturnFormattedString(): void
    {
        // Arrange
        $user = new User([
            'role' => 'student',
            'year_level' => 2,
            'section' => 'A'
        ]);

        // Act
        $info = $user->getStudentInfo();

        // Assert
        $this->assertEquals('Year 2 - Section A', $info);
    }

    public function testGetStudentInfo_WithNonStudent_ShouldReturnEmptyString(): void
    {
        // Arrange
        $faculty = new User(['role' => 'faculty']);
        $admin = new User(['role' => 'admin']);

        // Act & Assert
        $this->assertEquals('', $faculty->getStudentInfo());
        $this->assertEquals('', $admin->getStudentInfo());
    }

    public function testGetStudentInfo_WithIncompleteStudentData_ShouldReturnPartialInfo(): void
    {
        // Arrange
        $userWithoutSection = new User(['role' => 'student', 'year_level' => 2]);
        $userWithoutYear = new User(['role' => 'student', 'section' => 'A']);

        // Act
        $infoWithoutSection = $userWithoutSection->getStudentInfo();
        $infoWithoutYear = $userWithoutYear->getStudentInfo();

        // Assert
        $this->assertEquals('Year 2', $infoWithoutSection);
        $this->assertEquals('Section A', $infoWithoutYear);
    }

    // ===== VALIDATION TESTS =====

    public function testIsDataComplete_WithCompleteStudentData_ShouldReturnTrue(): void
    {
        // Arrange
        $user = new User([
            'school_id' => 'STU123',
            'full_name' => 'John Doe',
            'role' => 'student',
            'year_level' => 1,
            'section' => 'A'
        ]);

        // Act
        $isComplete = $user->isDataComplete();

        // Assert
        $this->assertTrue($isComplete, "Complete student data should be valid");
    }

    public function testIsDataComplete_WithCompleteFacultyData_ShouldReturnTrue(): void
    {
        // Arrange
        $user = new User([
            'school_id' => 'FAC123',
            'full_name' => 'Jane Professor',
            'role' => 'faculty'
        ]);

        // Act
        $isComplete = $user->isDataComplete();

        // Assert
        $this->assertTrue($isComplete, "Complete faculty data should be valid");
    }

    public function testIsDataComplete_WithIncompleteData_ShouldReturnFalse(): void
    {
        // Test missing school ID
        $userNoSchoolId = new User(['full_name' => 'John', 'role' => 'student']);
        $this->assertFalse($userNoSchoolId->isDataComplete(), "Should be incomplete without school ID");

        // Test missing full name
        $userNoName = new User(['school_id' => 'STU123', 'role' => 'student']);
        $this->assertFalse($userNoName->isDataComplete(), "Should be incomplete without full name");

        // Test missing role
        $userNoRole = new User(['school_id' => 'STU123', 'full_name' => 'John']);
        $this->assertFalse($userNoRole->isDataComplete(), "Should be incomplete without role");

        // Test student missing year level
        $studentNoYear = new User(['school_id' => 'STU123', 'full_name' => 'John', 'role' => 'student', 'section' => 'A']);
        $this->assertFalse($studentNoYear->isDataComplete(), "Student should be incomplete without year level");
    }

    // ===== EDGE CASE TESTS =====

    public function testConstructor_WithNullValues_ShouldHandleGracefully(): void
    {
        // Arrange
        $userData = [
            'user_id' => null,
            'school_id' => null,
            'full_name' => null,
            'role' => null
        ];

        // Act
        $user = new User($userData);

        // Assert
        $this->assertNull($user->getUserId());
        $this->assertNull($user->getSchoolId());
        $this->assertNull($user->getFullName());
        $this->assertNull($user->getRole());
    }

    public function testRoleMethods_WithNullRole_ShouldReturnFalse(): void
    {
        // Arrange
        $user = new User([]);

        // Act & Assert
        $this->assertFalse($user->isStudent(), "Should return false when role is null");
        $this->assertFalse($user->isFaculty(), "Should return false when role is null");
        $this->assertFalse($user->isAdmin(), "Should return false when role is null");
    }

    public function testRoleMethods_WithInvalidRole_ShouldReturnFalse(): void
    {
        // Arrange
        $user = new User(['role' => 'invalid']);

        // Act & Assert
        $this->assertFalse($user->isStudent(), "Should return false for invalid role");
        $this->assertFalse($user->isFaculty(), "Should return false for invalid role");
        $this->assertFalse($user->isAdmin(), "Should return false for invalid role");
    }

    public function testGetters_WithUnsetProperties_ShouldReturnNull(): void
    {
        // Arrange
        $user = new User([]);

        // Act & Assert
        $this->assertNull($user->getUserId());
        $this->assertNull($user->getSchoolId());
        $this->assertNull($user->getFullName());
        $this->assertNull($user->getPassword());
        $this->assertNull($user->getRole());
        $this->assertNull($user->getYearLevel());
        $this->assertNull($user->getSection());
        $this->assertNull($user->getCreatedAt());
        $this->assertNull($user->getUpdatedAt());
    }
}