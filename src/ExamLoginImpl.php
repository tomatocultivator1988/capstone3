<?php

namespace App;

use PDO;
use PDOStatement;

/**
 * ExamLoginImpl
 * 
 * Implementation for exam-specific login functionality.
 * Handles authentication for students, faculty, and admin users.
 */
class ExamLoginImpl
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Authenticate user with school_id and password
     *
     * @param string $school_id The school ID
     * @param string $password The plain text password
     * @return array|false User data if authentication successful, false otherwise
     */
    public function login(string $school_id, string $password)
    {
        // Validate input parameters
        if (empty($school_id) || empty($password)) {
            return false;
        }

        try {
            // Prepare SQL statement to find user by school_id
            $sql = "SELECT school_id, password, role FROM users WHERE school_id = :school_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':school_id', $school_id, PDO::PARAM_STR);
            $stmt->execute();

            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // If user not found, return false
            if (!$user) {
                return false;
            }

            // Verify password
            if (password_verify($password, $user['password'])) {
                // Return user data without password
                return [
                    'school_id' => $user['school_id'],
                    'role' => $user['role']
                ];
            }

            // Password verification failed
            return false;

        } catch (\Exception $e) {
            error_log("ExamLoginImpl::login error: " . $e->getMessage());
            return false;
        }
    }
}