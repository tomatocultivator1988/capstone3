<?php

namespace App\DAO\Impl;

use App\DAO\Interface\UserDAOInterface;
use App\Model\User;
use App\Config\Database;
use PDO;
use PDOException;

/**
 * UserDAO Implementation
 * 
 * Handles all database operations for User entities.
 * Responsible for data access only, no business logic.
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
            error_log("UserDAOImpl::findBySchoolId error: " . $e->getMessage());
            return null;
        }
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
            error_log("UserDAOImpl::findById error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get all users
     */
    public function getAll(): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} ORDER BY created_at DESC");
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return array_map(fn($row) => new User($row), $data);
        } catch (PDOException $e) {
            error_log("UserDAOImpl::getAll error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get users by role
     */
    public function getByRole(string $role): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE role = ? ORDER BY full_name ASC");
            $stmt->execute([$role]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return array_map(fn($row) => new User($row), $data);
        } catch (PDOException $e) {
            error_log("UserDAOImpl::getByRole error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get students by year and section
     */
    public function getStudentsByYearSection(int $year_level, string $section): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE role = 'student' AND year_level = ? AND section = ? ORDER BY full_name ASC");
            $stmt->execute([$year_level, $section]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return array_map(fn($row) => new User($row), $data);
        } catch (PDOException $e) {
            error_log("UserDAOImpl::getStudentsByYearSection error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Create new user
     */
    public function create(User $user): ?int
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

            return $result ? (int)$this->db->lastInsertId() : null;
        } catch (PDOException $e) {
            error_log("UserDAOImpl::create error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Update user
     */
    public function update(User $user): bool
    {
        try {
            $sql = "UPDATE {$this->table} SET school_id = ?, full_name = ?, role = ?, year_level = ?, section = ?, updated_at = NOW() 
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
            error_log("UserDAOImpl::update error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete user
     */
    public function delete(int $user_id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE user_id = ?");
            return $stmt->execute([$user_id]);
        } catch (PDOException $e) {
            error_log("UserDAOImpl::delete error: " . $e->getMessage());
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
            return (int)$stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("UserDAOImpl::existsBySchoolId error: " . $e->getMessage());
            return false;
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
            error_log("UserDAOImpl::updatePassword error: " . $e->getMessage());
            return false;
        }
    }
}