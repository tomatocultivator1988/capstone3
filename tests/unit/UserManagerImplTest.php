<?php

use PHPUnit\Framework\TestCase;
use App\UserManagerImpl;

class UserManagerImplTest extends TestCase
{
    public function test_add_student()
    {
        $mockStmt = $this->createMock(PDOStatement::class);
        $mockStmt->method('execute')->willReturn(true);

        $mockPdo = $this->createMock(PDO::class);
        $mockPdo->method('prepare')->willReturn($mockStmt);
        $mockPdo->method('lastInsertId')->willReturn(101);

        $manager = new UserManagerImpl($mockPdo);
        $userId = $manager->addUser('STU-UNIT-001', 'Unit Test Student', 'student', 1, 'A');
        $this->assertEquals(101, $userId);
    }

    public function test_add_faculty()
    {
        $mockStmt = $this->createMock(PDOStatement::class);
        $mockStmt->method('execute')->willReturn(true);

        $mockPdo = $this->createMock(PDO::class);
        $mockPdo->method('prepare')->willReturn($mockStmt);
        $mockPdo->method('lastInsertId')->willReturn(102);

        $manager = new UserManagerImpl($mockPdo);
        $userId = $manager->addUser('FAC-UNIT-001', 'Unit Test Faculty', 'faculty');
        $this->assertEquals(102, $userId);
    }

    public function test_update_student()
    {
        $mockStmt = $this->createMock(PDOStatement::class);
        $mockStmt->method('execute')->willReturn(true);

        $mockPdo = $this->createMock(PDO::class);
        $mockPdo->method('prepare')->willReturn($mockStmt);

        $manager = new UserManagerImpl($mockPdo);
        $result = $manager->updateUser(201, 'STU-UNIT-002', 'Updated Student Name', 'student', 3, 'C');
        $this->assertTrue($result);
    }

    public function test_update_faculty()
    {
        $mockStmt = $this->createMock(PDOStatement::class);
        $mockStmt->method('execute')->willReturn(true);

        $mockPdo = $this->createMock(PDO::class);
        $mockPdo->method('prepare')->willReturn($mockStmt);

        $manager = new UserManagerImpl($mockPdo);
        $result = $manager->updateUser(202, 'FAC-UNIT-002', 'Updated Faculty Name', 'faculty');
        $this->assertTrue($result);
    }

    public function test_delete_student()
    {
        $mockStmt = $this->createMock(PDOStatement::class);
        $mockStmt->method('execute')->willReturn(true);

        $mockPdo = $this->createMock(PDO::class);
        $mockPdo->method('prepare')->willReturn($mockStmt);

        $manager = new UserManagerImpl($mockPdo);
        $result = $manager->deleteUser(301);
        $this->assertTrue($result);
    }

    public function test_delete_faculty()
    {
        $mockStmt = $this->createMock(PDOStatement::class);
        $mockStmt->method('execute')->willReturn(true);

        $mockPdo = $this->createMock(PDO::class);
        $mockPdo->method('prepare')->willReturn($mockStmt);

        $manager = new UserManagerImpl($mockPdo);
        $result = $manager->deleteUser(302);
        $this->assertTrue($result);
    }
}