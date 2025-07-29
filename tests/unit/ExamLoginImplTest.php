<?php

use PHPUnit\Framework\TestCase;
use App\ExamLoginImpl;

class ExamLoginImplTest extends TestCase
{
    /** Test login with valid credentials */
    public function test_login_with_valid_credentials()
    {
        $school_id = '2020-001';
        $plainPassword = 'password123';
        $hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);

        $mockStmt = $this->createMock(PDOStatement::class);
        $mockStmt->method('execute')->willReturn(true);
        $mockStmt->method('fetch')->willReturn([
            'school_id' => $school_id,
            'password' => $hashedPassword,
            'role' => 'student'
        ]);

        $mockPdo = $this->createMock(PDO::class);
        $mockPdo->method('prepare')->willReturn($mockStmt);

        $login = new ExamLoginImpl($mockPdo);
        $result = $login->login($school_id, $plainPassword);

        $this->assertIsArray($result);
        $this->assertEquals('student', $result['role']);
    }

    /** Test login with wrong password */
    public function test_login_with_wrong_password()
    {
        $school_id = '2020-001';
        $plainPassword = 'wrongpassword';
        $hashedPassword = password_hash('password123', PASSWORD_DEFAULT);

        $mockStmt = $this->createMock(PDOStatement::class);
        $mockStmt->method('execute')->willReturn(true);
        $mockStmt->method('fetch')->willReturn([
            'school_id' => $school_id,
            'password' => $hashedPassword,
            'role' => 'student'
        ]);

        $mockPdo = $this->createMock(PDO::class);
        $mockPdo->method('prepare')->willReturn($mockStmt);

        $login = new ExamLoginImpl($mockPdo);
        $result = $login->login($school_id, $plainPassword);

        $this->assertFalse($result);
    }

    /** Test login with unknown user */
    public function test_login_with_unknown_user()
    {
        $school_id = 'NOT_A_USER';
        $plainPassword = 'any';

        $mockStmt = $this->createMock(PDOStatement::class);
        $mockStmt->method('execute')->willReturn(true);
        $mockStmt->method('fetch')->willReturn(false);

        $mockPdo = $this->createMock(PDO::class);
        $mockPdo->method('prepare')->willReturn($mockStmt);

        $login = new ExamLoginImpl($mockPdo);
        $result = $login->login($school_id, $plainPassword);

        $this->assertFalse($result);
    }

    /** Test student login returns student role */
    public function test_student_login_returns_student_role()
    {
        $school_id = '2020-001';
        $plainPassword = 'password123';
        $hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);

        $mockStmt = $this->createMock(PDOStatement::class);
        $mockStmt->method('execute')->willReturn(true);
        $mockStmt->method('fetch')->willReturn([
            'school_id' => $school_id,
            'password' => $hashedPassword,
            'role' => 'student'
        ]);

        $mockPdo = $this->createMock(PDO::class);
        $mockPdo->method('prepare')->willReturn($mockStmt);

        $login = new ExamLoginImpl($mockPdo);
        $result = $login->login($school_id, $plainPassword);

        $this->assertIsArray($result);
        $this->assertEquals('student', $result['role']);
    }

    /** Test faculty login returns faculty role */
    public function test_faculty_login_returns_faculty_role()
    {
        $school_id = 'FAC001';
        $plainPassword = 'password123';
        $hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);

        $mockStmt = $this->createMock(PDOStatement::class);
        $mockStmt->method('execute')->willReturn(true);
        $mockStmt->method('fetch')->willReturn([
            'school_id' => $school_id,
            'password' => $hashedPassword,
            'role' => 'faculty'
        ]);

        $mockPdo = $this->createMock(PDO::class);
        $mockPdo->method('prepare')->willReturn($mockStmt);

        $login = new ExamLoginImpl($mockPdo);
        $result = $login->login($school_id, $plainPassword);

        $this->assertIsArray($result);
        $this->assertEquals('faculty', $result['role']);
    }

    /** Test admin login returns admin role */
    public function test_admin_login_returns_admin_role()
    {
        $school_id = 'ADMIN001';
        $plainPassword = 'password123';
        $hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);

        $mockStmt = $this->createMock(PDOStatement::class);
        $mockStmt->method('execute')->willReturn(true);
        $mockStmt->method('fetch')->willReturn([
            'school_id' => $school_id,
            'password' => $hashedPassword,
            'role' => 'admin'
        ]);

        $mockPdo = $this->createMock(PDO::class);
        $mockPdo->method('prepare')->willReturn($mockStmt);

        $login = new ExamLoginImpl($mockPdo);
        $result = $login->login($school_id, $plainPassword);

        $this->assertIsArray($result);
        $this->assertEquals('admin', $result['role']);
    }

    /** Test login with invalid credentials */
    public function test_login_with_invalid_credentials_returns_false()
    {
        $school_id = 'NOT_A_USER';
        $plainPassword = 'wrongpassword';

        $mockStmt = $this->createMock(PDOStatement::class);
        $mockStmt->method('execute')->willReturn(true);
        $mockStmt->method('fetch')->willReturn(false);

        $mockPdo = $this->createMock(PDO::class);
        $mockPdo->method('prepare')->willReturn($mockStmt);

        $login = new ExamLoginImpl($mockPdo);
        $result = $login->login($school_id, $plainPassword);

        $this->assertFalse($result);
    }

    /** Test login with missing parameters */
    public function test_login_with_missing_parameters()
    {
        $mockStmt = $this->createMock(PDOStatement::class);
        $mockStmt->method('execute')->willReturn(true);
        $mockStmt->method('fetch')->willReturn(false);

        $mockPdo = $this->createMock(PDO::class);
        $mockPdo->method('prepare')->willReturn($mockStmt);

        $login = new ExamLoginImpl($mockPdo);

        // Missing school_id
        $result1 = $login->login('', 'password123');
        $this->assertFalse($result1);

        // Missing password
        $result2 = $login->login('2020-001', '');
        $this->assertFalse($result2);

        // Both missing
        $result3 = $login->login('', '');
        $this->assertFalse($result3);
    }
}