<?php

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;

class AuthControllerTest extends TestCase
{
    private $client;

    /**
     * Set up the Guzzle HTTP client before each test.
     */
    public function setUp(): void
    {
        $this->client = new Client([
            'base_uri' => 'http://localhost/capstonemvc2/api/',
            'timeout' => 5.0,
            'http_errors' => false,
        ]);
    }

    /**
     * Test MVC login with valid credentials.
     */
    public function test_mvc_login_with_valid_credentials()
    {
        $response = $this->client->post('auth/login.php', [
            'form_params' => [
                'school_id' => '2020-001',
                'password' => 'password123',
            ]
        ]);

        $responseData = json_decode($response->getBody(), true);

        $this->assertIsArray($responseData);
        $this->assertEquals('success', $responseData['status']);
        $this->assertEquals('student', $responseData['role']);
        $this->assertEquals('Login successful!', $responseData['message']);
    }

    /**
     * Test MVC login with wrong password.
     */
    public function test_mvc_login_with_wrong_password()
    {
        $response = $this->client->post('auth/login.php', [
            'form_params' => [
                'school_id' => '2020-001',
                'password' => 'wrongpassword',
            ]
        ]);

        $responseData = json_decode($response->getBody(), true);

        $this->assertIsArray($responseData);
        $this->assertEquals('fail', $responseData['status']);
        $this->assertEquals('Invalid School ID or password.', $responseData['message']);
    }

    /**
     * Test MVC logout functionality.
     */
    public function test_mvc_logout()
    {
        $response = $this->client->post('auth/logout.php');
        $responseData = json_decode($response->getBody(), true);

        $this->assertIsArray($responseData);
        $this->assertEquals('success', $responseData['status']);
        $this->assertEquals('Logged out successfully.', $responseData['message']);
    }

    /**
     * Test MVC admin login returns admin role.
     */
    public function test_mvc_admin_login_returns_admin_role()
    {
        $response = $this->client->post('auth/login.php', [
            'form_params' => [
                'school_id' => 'ADMIN001',
                'password' => 'password123',
            ]
        ]);

        $responseData = json_decode($response->getBody(), true);

        $this->assertIsArray($responseData);
        $this->assertEquals('success', $responseData['status']);
        $this->assertEquals('admin', $responseData['role']);
    }

    /**
     * Test MVC faculty login returns faculty role.
     */
    public function test_mvc_faculty_login_returns_faculty_role()
    {
        $response = $this->client->post('auth/login.php', [
            'form_params' => [
                'school_id' => 'FAC001',
                'password' => 'password123',
            ]
        ]);

        $responseData = json_decode($response->getBody(), true);

        $this->assertIsArray($responseData);
        $this->assertEquals('success', $responseData['status']);
        $this->assertEquals('faculty', $responseData['role']);
    }
}