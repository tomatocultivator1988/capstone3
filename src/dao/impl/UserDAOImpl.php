<?php

namespace Dao\Impl;

use Dao\Interface\UserDAOInterface;
use Model\User;
use App\Config\Database;
use PDO;
use PDOException;

/**
 * UserDAOImpl
 * 
 * Implementation of UserDAOInterface for database operations.
 * Handles all database operations for User entities.
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
            error_log("UserDAOImpl::findById error: " . $e->getMessage());
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
            error_log("UserDAOImpl::findBySchoolId error: " . $e->getMessage());
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
            error_log("UserDAOImpl::findAll error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Find users by role
     */
    public function findByRole(string $role): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE role = ? ORDER BY full_name ASC");
            $stmt->execute([$role]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return array_map(fn($row) => new User($row), $data);
        } catch (PDOException $e) {
            error_log("UserDAOImpl::findByRole error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Create new user
     */
    public function create(User $user): bool
    {
        try {
            // Validate required fields
            if (empty($user->getSchoolId()) || empty($user->getFullName()) || empty($user->getPassword()) || empty($user->getRole())) {
                return false;
            }
            
            $sql = "INSERT INTO {$this->table} (school_id, full_name, password, role, year_level, section, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, NOW())";
            
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
                $user->setUserId((int)$this->db->lastInsertId());
                return true;
            }
            
            return false;
        } catch (PDOException $e) {
            error_log("UserDAOImpl::create error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update existing user
     */
    public function update(User $user): bool
    {
        try {
            $sql = "UPDATE {$this->table} SET 
                    school_id = ?, full_name = ?, role = ?, 
                    year_level = ?, section = ?, updated_at = NOW() 
                    WHERE user_id = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $user->getSchoolId(),
                $user->getFullName(),
                $user->getRole(),
                $user->getYearLevel(),
                $user->getSection(),
                $user->getUserId()
            ]);
            
            // Check if any rows were actually updated
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("UserDAOImpl::update error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete user by ID
     */
    public function deleteById(int $user_id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE user_id = ?");
            $stmt->execute([$user_id]);
            
            // Check if any rows were actually deleted
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("UserDAOImpl::deleteById error: " . $e->getMessage());
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
            error_log("UserDAOImpl::existsBySchoolId error: " . $e->getMessage());
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
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("UserDAOImpl::getTotalCount error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Find users with pagination
     */
    public function findWithPagination(int $limit, int $offset): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} ORDER BY created_at DESC LIMIT ? OFFSET ?");
            $stmt->execute([$limit, $offset]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return array_map(fn($row) => new User($row), $data);
        } catch (PDOException $e) {
            error_log("UserDAOImpl::findWithPagination error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Find students by year level and section
     */
    public function findStudentsByYearAndSection(int $year_level, string $section): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE role = 'student' AND year_level = ? AND section = ? ORDER BY full_name ASC");
            $stmt->execute([$year_level, $section]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return array_map(fn($row) => new User($row), $data);
        } catch (PDOException $e) {
            error_log("UserDAOImpl::findStudentsByYearAndSection error: " . $e->getMessage());
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
            $stmt->execute([$hashedPassword, $user_id]);
            
            // Check if any rows were actually updated
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("UserDAOImpl::updatePassword error: " . $e->getMessage());
            return false;
        }
    }
}