<?php

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class LoginTest extends TestCase
{
    private $client;

    /**
     * Set up the Guzzle HTTP client before each test.
     */
    public function setUp(): void
    {
        $this->client = new Client([
            'base_uri' => 'http://localhost/exam-login-tdd/api/',
            'timeout' => 5.0,
            'http_errors' => false, // Don't throw exceptions for 4xx/5xx responses
        ]);
    }

    /**
     * Test login with valid credentials via API.
     */
    public function test_login_with_valid_credentials()
    {
        $response = $this->client->post('login.php', [
            'form_params' => [
                'school_id' => '2020-001',
                'password' => 'password123',
            ]
        ]);

        $responseData = json_decode($response->getBody(), true);

        // Assert that the response is valid JSON
        $this->assertIsArray($responseData);
        
        // Assert that the login is successful and the role is 'student'
        $this->assertEquals('success', $responseData['status']);
        $this->assertEquals('student', $responseData['role']);
        $this->assertEquals('Login successful!', $responseData['message']);
    }

    /**
     * Test login with wrong password via API.
     */
    public function test_login_with_wrong_password()
    {
        $response = $this->client->post('login.php', [
            'form_params' => [
                'school_id' => '2020-001',
                'password' => 'wrongpassword',
            ]
        ]);

        $responseData = json_decode($response->getBody(), true);

        // Assert that the response is valid JSON
        $this->assertIsArray($responseData);
        
        // Assert that login fails and returns expected message
        $this->assertEquals('fail', $responseData['status']);
        $this->assertEquals('Invalid School ID or password.', $responseData['message']);
    }

    /**
     * Test login with unknown user via API.
     */
    public function test_login_with_unknown_user()
    {
        $response = $this->client->post('login.php', [
            'form_params' => [
                'school_id' => 'NOT_A_USER',
                'password' => 'any',
            ]
        ]);

        $responseData = json_decode($response->getBody(), true);

        // Assert that the response is valid JSON
        $this->assertIsArray($responseData);
        
        // Assert that login fails for an unknown user
        $this->assertEquals('fail', $responseData['status']);
        $this->assertEquals('Invalid School ID or password.', $responseData['message']);
    }

    /**
     * Test student login returns student role via API.
     */
    public function test_student_login_returns_student_role()
    {
        $response = $this->client->post('login.php', [
            'form_params' => [
                'school_id' => '2020-001',
                'password' => 'password123',
            ]
        ]);

        $responseData = json_decode($response->getBody(), true);

        // Assert that the response is valid JSON
        $this->assertIsArray($responseData);
        
        // Assert that the role returned is 'student'
        $this->assertEquals('success', $responseData['status']);
        $this->assertEquals('student', $responseData['role']);
    }

    /**
     * Test faculty login returns faculty role via API.
     */
    public function test_faculty_login_returns_faculty_role()
    {
        $response = $this->client->post('login.php', [
            'form_params' => [
                'school_id' => 'FAC001',
                'password' => 'password123',
            ]
        ]);

        $responseData = json_decode($response->getBody(), true);

        // Assert that the response is valid JSON
        $this->assertIsArray($responseData);
        
        // Assert that the role returned is 'faculty'
        $this->assertEquals('success', $responseData['status']);
        $this->assertEquals('faculty', $responseData['role']);
    }

    /**
     * Test admin login returns admin role via API.
     */
    public function test_admin_login_returns_admin_role()
    {
        $response = $this->client->post('login.php', [
            'form_params' => [
                'school_id' => 'ADMIN001',
                'password' => 'password123',
            ]
        ]);

        $responseData = json_decode($response->getBody(), true);

        // Assert that the response is valid JSON
        $this->assertIsArray($responseData);
        
        // Assert that the role returned is 'admin'
        $this->assertEquals('success', $responseData['status']);
        $this->assertEquals('admin', $responseData['role']);
    }

    /**
     * Test login with invalid credentials via API returns false.
     */
    public function test_login_with_invalid_credentials_returns_false()
    {
        $response = $this->client->post('login.php', [
            'form_params' => [
                'school_id' => 'NOT_A_USER',
                'password' => 'wrongpassword',
            ]
        ]);

        $responseData = json_decode($response->getBody(), true);

        // Assert that the response is valid JSON
        $this->assertIsArray($responseData);
        
        // Assert that invalid credentials return fail status
        $this->assertEquals('fail', $responseData['status']);
        $this->assertEquals('Invalid School ID or password.', $responseData['message']);
    }

    /**
     * Test login with missing parameters.
     */
    public function test_login_with_missing_parameters()
    {
        $response = $this->client->post('login.php', [
            'form_params' => [
                'school_id' => '2020-001',
                // Missing password parameter
            ]
        ]);

        $responseData = json_decode($response->getBody(), true);

        // Assert that the response is valid JSON
        $this->assertIsArray($responseData);
        
        // Assert that missing parameters return fail status
        $this->assertEquals('fail', $responseData['status']);
        $this->assertEquals('Both School ID and password are required.', $responseData['message']);
    }
}