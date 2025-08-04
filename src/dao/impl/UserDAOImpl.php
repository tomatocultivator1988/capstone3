<?php

namespace Dao\Impl;

use Dao\Interface\UserDAOInterface;
use Model\User;
use Config\Database;
use PDO;
use PDOException;
use Exception;

/**
 * User DAO Implementation
 * 
 * Handles all database operations for User entities.
 * Contains only database access code, no business logic.
 */
class UserDAOImpl implements UserDAOInterface
{
    private PDO $db;
    private string $table = 'users';

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Find user by ID
     */
    public function findById(int $user_id): ?User
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $data ? new User($data) : null;
        } catch (PDOException $e) {
            error_log("UserDAO::findById error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Find user by school ID
     */
    public function findBySchoolId(string $school_id): ?User
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE school_id = ?");
            $stmt->execute([$school_id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $data ? new User($data) : null;
        } catch (PDOException $e) {
            error_log("UserDAO::findBySchoolId error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get all users
     */
    public function findAll(): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} ORDER BY created_at DESC");
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return array_map(fn($row) => new User($row), $data);
        } catch (PDOException $e) {
            error_log("UserDAO::findAll error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get users by role
     */
    public function findByRole(string $role): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE role = ? ORDER BY full_name");
            $stmt->execute([$role]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return array_map(fn($row) => new User($row), $data);
        } catch (PDOException $e) {
            error_log("UserDAO::findByRole error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Create a new user
     */
    public function create(User $user): bool
    {
        try {
            $sql = "INSERT INTO {$this->table} (school_id, full_name, password, role, year_level, section, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $user->getSchoolId(),
                $user->getFullName(),
                $user->getPassword(),
                $user->getRole(),
                $user->getYearLevel(),
                $user->getSection()
            ]);

            if ($result) {
                $user->setUserId((int) $this->db->lastInsertId());
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("UserDAO::create error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update an existing user
     */
    public function update(User $user): bool
    {
        try {
            $sql = "UPDATE {$this->table} 
                    SET school_id = ?, full_name = ?, role = ?, year_level = ?, section = ?, updated_at = NOW() 
                    WHERE user_id = ?";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $user->getSchoolId(),
                $user->getFullName(),
                $user->getRole(),
                $user->getYearLevel(),
                $user->getSection(),
                $user->getUserId()
            ]);
        } catch (PDOException $e) {
            error_log("UserDAO::update error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete a user by ID
     */
    public function deleteById(int $user_id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE user_id = ?");
            return $stmt->execute([$user_id]);
        } catch (PDOException $e) {
            error_log("UserDAO::deleteById error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if user exists by school ID
     */
    public function existsBySchoolId(string $school_id): bool
    {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} WHERE school_id = ?");
            $stmt->execute([$school_id]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("UserDAO::existsBySchoolId error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get total count of users
     */
    public function getTotalCount(): int
    {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table}");
            $stmt->execute();
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("UserDAO::getTotalCount error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get users with pagination
     */
    public function findWithPagination(int $limit, int $offset): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} ORDER BY created_at DESC LIMIT ? OFFSET ?");
            $stmt->execute([$limit, $offset]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return array_map(fn($row) => new User($row), $data);
        } catch (PDOException $e) {
            error_log("UserDAO::findWithPagination error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get students by year level and section
     */
    public function findStudentsByYearAndSection(int $year_level, string $section): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE role = 'student' AND year_level = ? AND section = ? ORDER BY full_name");
            $stmt->execute([$year_level, $section]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return array_map(fn($row) => new User($row), $data);
        } catch (PDOException $e) {
            error_log("UserDAO::findStudentsByYearAndSection error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Update user password
     */
    public function updatePassword(int $user_id, string $hashedPassword): bool
    {
        try {
            $stmt = $this->db->prepare("UPDATE {$this->table} SET password = ?, updated_at = NOW() WHERE user_id = ?");
            return $stmt->execute([$hashedPassword, $user_id]);
        } catch (PDOException $e) {
            error_log("UserDAO::updatePassword error: " . $e->getMessage());
            return false;
        }
    }
}