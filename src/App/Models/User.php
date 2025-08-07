<?php

namespace App\Models;

use App\Config\Database;
use PDO;
use PDOException;

class User
{
    private $db;
    private $table = 'users';

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Find user by school ID
     */
    public function findBySchoolId($school_id)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE school_id = ?");
            $stmt->execute([$school_id]);
            $result = $stmt->fetch();
            
            // Debug: Log the search
            error_log("Searching for school_id: " . $school_id . ", Found: " . ($result ? 'YES' : 'NO'));
            
            return $result;
        } catch (PDOException $e) {
            error_log("Database error in findBySchoolId: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Authenticate user
     */
    public function authenticate($school_id, $password)
    {
        $user = $this->findBySchoolId($school_id);
        
        if (!$user) {
            error_log("User not found for school_id: " . $school_id);
            return false;
        }

        // Debug: Log authentication attempt
        error_log("Authenticating user: " . $school_id . ", Password match: " . ($password === $user['password'] ? 'YES' : 'NO'));
        error_log("Stored password: " . $user['password'] . ", Input password: " . $password);

        // Check if password is hashed (starts with $) or plain text
        if (strpos($user['password'], '$') === 0) {
            $result = password_verify($password, $user['password']) ? $user : false;
            error_log("Hashed password verification: " . ($result ? 'SUCCESS' : 'FAILED'));
            return $result;
        } else {
            // Trim both passwords before comparing
            $inputPassword = trim($password);
            $storedPassword = trim($user['password']);
            $result = $inputPassword === $storedPassword ? $user : false;
            error_log("Plain text password verification (trimmed): " . ($result ? 'SUCCESS' : 'FAILED') . ", Input: '" . $inputPassword . "', Stored: '" . $storedPassword . "'");
            return $result;
        }
    }

    /**
     * Get all users
     */
    public function getAllUsers()
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} ORDER BY created_at DESC");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Get users by role
     */
    public function getUsersByRole($role)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE role = ? ORDER BY full_name ASC");
            $stmt->execute([$role]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Get students by year and section
     */
    public function getStudentsByYearSection($year_level, $section)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE role = 'student' AND year_level = ? AND section = ? ORDER BY full_name ASC");
            $stmt->execute([$year_level, $section]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Create new user
     */
    public function create($data)
    {
        try {
            // Generate default password
            $plainPassword = $data['school_id'] . $data['full_name'];
            $hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);

            $sql = "INSERT INTO {$this->table} (school_id, full_name, password, role, year_level, section, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $data['school_id'],
                $data['full_name'],
                $hashedPassword,
                $data['role'],
                $data['role'] === 'student' ? $data['year_level'] : null,
                $data['role'] === 'student' ? $data['section'] : null
            ]);

            return $result ? $this->db->lastInsertId() : false;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Update user
     */
    public function update($user_id, $data)
    {
        try {
            $sql = "UPDATE {$this->table} SET school_id = ?, full_name = ?, role = ?, year_level = ?, section = ?, updated_at = NOW() 
                    WHERE user_id = ?";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $data['school_id'],
                $data['full_name'],
                $data['role'],
                $data['role'] === 'student' ? $data['year_level'] : null,
                $data['role'] === 'student' ? $data['section'] : null,
                $user_id
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Delete user
     */
    public function delete($user_id)
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE user_id = ?");
            return $stmt->execute([$user_id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Find user by ID
     */
    public function findById($user_id)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE user_id = ?");
            $stmt->execute([$user_id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }
}