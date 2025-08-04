<?php

namespace Dao\Impl;

use Dao\Interface\SubjectDAOInterface;
use Model\Subject;
use Config\Database;
use PDO;
use PDOException;

/**
 * Subject DAO Implementation
 * 
 * Handles all database operations for Subject entities.
 * Contains only database access code, no business logic.
 */
class SubjectDAOImpl implements SubjectDAOInterface
{
    private PDO $db;
    private string $table = 'subjects';

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function findById(int $subject_id): ?Subject
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE subject_id = ?");
            $stmt->execute([$subject_id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $data ? new Subject($data) : null;
        } catch (PDOException $e) {
            error_log("SubjectDAO::findById error: " . $e->getMessage());
            return null;
        }
    }

    public function findByCode(string $subject_code): ?Subject
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE subject_code = ?");
            $stmt->execute([$subject_code]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $data ? new Subject($data) : null;
        } catch (PDOException $e) {
            error_log("SubjectDAO::findByCode error: " . $e->getMessage());
            return null;
        }
    }

    public function findAll(): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} ORDER BY subject_name");
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return array_map(fn($row) => new Subject($row), $data);
        } catch (PDOException $e) {
            error_log("SubjectDAO::findAll error: " . $e->getMessage());
            return [];
        }
    }

    public function findByFacultyId(int $faculty_id): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE faculty_id = ? ORDER BY subject_name");
            $stmt->execute([$faculty_id]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return array_map(fn($row) => new Subject($row), $data);
        } catch (PDOException $e) {
            error_log("SubjectDAO::findByFacultyId error: " . $e->getMessage());
            return [];
        }
    }

    public function create(Subject $subject): bool
    {
        try {
            $sql = "INSERT INTO {$this->table} (subject_code, subject_name, description, units, faculty_id, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, ?, NOW(), NOW())";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $subject->getSubjectCode(),
                $subject->getSubjectName(),
                $subject->getDescription(),
                $subject->getUnits(),
                $subject->getFacultyId()
            ]);

            if ($result) {
                $subject->setSubjectId((int) $this->db->lastInsertId());
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("SubjectDAO::create error: " . $e->getMessage());
            return false;
        }
    }

    public function update(Subject $subject): bool
    {
        try {
            $sql = "UPDATE {$this->table} 
                    SET subject_code = ?, subject_name = ?, description = ?, units = ?, faculty_id = ?, updated_at = NOW() 
                    WHERE subject_id = ?";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $subject->getSubjectCode(),
                $subject->getSubjectName(),
                $subject->getDescription(),
                $subject->getUnits(),
                $subject->getFacultyId(),
                $subject->getSubjectId()
            ]);
        } catch (PDOException $e) {
            error_log("SubjectDAO::update error: " . $e->getMessage());
            return false;
        }
    }

    public function deleteById(int $subject_id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE subject_id = ?");
            return $stmt->execute([$subject_id]);
        } catch (PDOException $e) {
            error_log("SubjectDAO::deleteById error: " . $e->getMessage());
            return false;
        }
    }

    public function existsByCode(string $subject_code): bool
    {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} WHERE subject_code = ?");
            $stmt->execute([$subject_code]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("SubjectDAO::existsByCode error: " . $e->getMessage());
            return false;
        }
    }

    public function getTotalCount(): int
    {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table}");
            $stmt->execute();
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("SubjectDAO::getTotalCount error: " . $e->getMessage());
            return 0;
        }
    }

    public function findWithPagination(int $limit, int $offset): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} ORDER BY subject_name LIMIT ? OFFSET ?");
            $stmt->execute([$limit, $offset]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return array_map(fn($row) => new Subject($row), $data);
        } catch (PDOException $e) {
            error_log("SubjectDAO::findWithPagination error: " . $e->getMessage());
            return [];
        }
    }

    public function findUnassigned(): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE faculty_id IS NULL ORDER BY subject_name");
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return array_map(fn($row) => new Subject($row), $data);
        } catch (PDOException $e) {
            error_log("SubjectDAO::findUnassigned error: " . $e->getMessage());
            return [];
        }
    }

    public function assignToFaculty(int $subject_id, int $faculty_id): bool
    {
        try {
            $stmt = $this->db->prepare("UPDATE {$this->table} SET faculty_id = ?, updated_at = NOW() WHERE subject_id = ?");
            return $stmt->execute([$faculty_id, $subject_id]);
        } catch (PDOException $e) {
            error_log("SubjectDAO::assignToFaculty error: " . $e->getMessage());
            return false;
        }
    }

    public function unassignFromFaculty(int $subject_id): bool
    {
        try {
            $stmt = $this->db->prepare("UPDATE {$this->table} SET faculty_id = NULL, updated_at = NOW() WHERE subject_id = ?");
            return $stmt->execute([$subject_id]);
        } catch (PDOException $e) {
            error_log("SubjectDAO::unassignFromFaculty error: " . $e->getMessage());
            return false;
        }
    }
}