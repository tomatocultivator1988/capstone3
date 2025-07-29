<?php

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;

class AdminDashboardUsersTest extends TestCase
{
    private $client;

    public function setUp(): void
    {
        $this->client = new Client([
            'base_uri' => 'http://localhost/exam-login-tdd/api/',
            'timeout' => 5.0,
            'http_errors' => false,
        ]);
    }

    public function test_add_student()
    {
        $response = $this->client->post('add_user.php', [
            'form_params' => [
                'school_id' => 'STU-UNIT-001',
                'full_name' => 'Unit Test Student',
                'role' => 'student',
                'year_level' => 1,
                'section' => 'A'
            ]
        ]);
        $data = json_decode($response->getBody(), true);

        $this->assertIsArray($data);
        $this->assertTrue($data['success']);
        $this->assertArrayHasKey('user_id', $data);

        // Clean up: delete the user
        $this->client->post('delete_user.php', [
            'form_params' => ['user_id' => $data['user_id']]
        ]);
    }

    public function test_add_faculty()
    {
        $response = $this->client->post('add_user.php', [
            'form_params' => [
                'school_id' => 'FAC-UNIT-001',
                'full_name' => 'Unit Test Faculty',
                'role' => 'faculty'
            ]
        ]);
        $data = json_decode($response->getBody(), true);

        $this->assertIsArray($data);
        $this->assertTrue($data['success']);
        $this->assertArrayHasKey('user_id', $data);

        // Clean up: delete the user
        $this->client->post('delete_user.php', [
            'form_params' => ['user_id' => $data['user_id']]
        ]);
    }

    public function test_update_student()
    {
        // Add a student first
        $add = $this->client->post('add_user.php', [
            'form_params' => [
                'school_id' => 'STU-UNIT-002',
                'full_name' => 'Student To Update',
                'role' => 'student',
                'year_level' => 2,
                'section' => 'B'
            ]
        ]);
        $addData = json_decode($add->getBody(), true);
        $this->assertTrue($addData['success']);
        $studentId = $addData['user_id'];

        // Now update
        $response = $this->client->post('update_user.php', [
            'form_params' => [
                'user_id' => $studentId,
                'school_id' => 'STU-UNIT-002',
                'full_name' => 'Updated Student Name',
                'role' => 'student',
                'year_level' => 3,
                'section' => 'C'
            ]
        ]);
        $data = json_decode($response->getBody(), true);

        $this->assertIsArray($data);
        $this->assertTrue($data['success']);

        // Clean up
        $this->client->post('delete_user.php', [
            'form_params' => ['user_id' => $studentId]
        ]);
    }

    public function test_update_faculty()
    {
        // Add a faculty first
        $add = $this->client->post('add_user.php', [
            'form_params' => [
                'school_id' => 'FAC-UNIT-002',
                'full_name' => 'Faculty To Update',
                'role' => 'faculty'
            ]
        ]);
        $addData = json_decode($add->getBody(), true);
        $this->assertTrue($addData['success']);
        $facultyId = $addData['user_id'];

        // Now update
        $response = $this->client->post('update_user.php', [
            'form_params' => [
                'user_id' => $facultyId,
                'school_id' => 'FAC-UNIT-002',
                'full_name' => 'Updated Faculty Name',
                'role' => 'faculty'
            ]
        ]);
        $data = json_decode($response->getBody(), true);

        $this->assertIsArray($data);
        $this->assertTrue($data['success']);

        // Clean up
        $this->client->post('delete_user.php', [
            'form_params' => ['user_id' => $facultyId]
        ]);
    }

    public function test_delete_student()
    {
        // Add a student to delete
        $add = $this->client->post('add_user.php', [
            'form_params' => [
                'school_id' => 'STU-DEL-001',
                'full_name' => 'Student To Delete',
                'role' => 'student',
                'year_level' => 1,
                'section' => 'D'
            ]
        ]);
        $addData = json_decode($add->getBody(), true);
        $this->assertTrue($addData['success']);
        $studentId = $addData['user_id'];

        // Now delete
        $response = $this->client->post('delete_user.php', [
            'form_params' => [
                'user_id' => $studentId
            ]
        ]);
        $data = json_decode($response->getBody(), true);

        $this->assertIsArray($data);
        $this->assertTrue($data['success']);
    }

    public function test_delete_faculty()
    {
        // Add a faculty to delete
        $add = $this->client->post('add_user.php', [
            'form_params' => [
                'school_id' => 'FAC-DEL-001',
                'full_name' => 'Faculty To Delete',
                'role' => 'faculty'
            ]
        ]);
        $addData = json_decode($add->getBody(), true);
        $this->assertTrue($addData['success']);
        $facultyId = $addData['user_id'];

        // Now delete
        $response = $this->client->post('delete_user.php', [
            'form_params' => [
                'user_id' => $facultyId
            ]
        ]);
        $data = json_decode($response->getBody(), true);

        $this->assertIsArray($data);
        $this->assertTrue($data['success']);
    }
}