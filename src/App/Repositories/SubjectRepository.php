<?php

namespace App\Repositories;

use App\Models\Subject;
use App\Config\Database;
use PDO;
use PDOException;

/**
 * SubjectRepository
 * 
 * Handles all database operations for Subject entities.
 * Responsible for data access only, no business logic.
 */
class SubjectRepository
{
    private PDO $db;
    private string $table = 'subjects';

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get all subjects
     */
    public function getAll(): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} ORDER BY subject_name ASC");
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return array_map(fn($row) => new Subject($row), $data);
        } catch (PDOException $e) {
            error_log("SubjectRepository::getAll error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Find subject by ID
     */
    public function findById(int $subject_id): ?Subject
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE subject_id = ?");
            $stmt->execute([$subject_id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $data ? new Subject($data) : null;
        } catch (PDOException $e) {
            error_log("SubjectRepository::findById error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Find subject by code
     */
    public function findByCode(string $subject_code): ?Subject
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE subject_code = ?");
            $stmt->execute([$subject_code]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $data ? new Subject($data) : null;
        } catch (PDOException $e) {
            error_log("SubjectRepository::findByCode error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Create new subject
     */
    public function create(Subject $subject): ?int
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

            return $result ? (int)$this->db->lastInsertId() : null;
        } catch (PDOException $e) {
            error_log("SubjectRepository::create error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Update subject
     */
    public function update(Subject $subject): bool
    {
        try {
            $sql = "UPDATE {$this->table} SET subject_code = ?, subject_name = ?, description = ?, units = ?, faculty_id = ?, updated_at = NOW() 
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
            error_log("SubjectRepository::update error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete subject
     */
    public function delete(int $subject_id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE subject_id = ?");
            return $stmt->execute([$subject_id]);
        } catch (PDOException $e) {
            error_log("SubjectRepository::delete error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if subject exists by code
     */
    public function existsByCode(string $subject_code): bool
    {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} WHERE subject_code = ?");
            $stmt->execute([$subject_code]);
            return (int)$stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("SubjectRepository::existsByCode error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Assign faculty to subject
     */
    public function assignFaculty(int $subject_id, int $faculty_id): bool
    {
        try {
            // First update the subject's faculty_id
            $sql = "UPDATE {$this->table} SET faculty_id = ?, updated_at = NOW() WHERE subject_id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$faculty_id, $subject_id]);
        } catch (PDOException $e) {
            error_log("SubjectRepository::assignFaculty error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get subjects by faculty
     */
    public function getByFaculty(int $faculty_id): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE faculty_id = ? ORDER BY subject_code ASC");
            $stmt->execute([$faculty_id]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return array_map(fn($row) => new Subject($row), $data);
        } catch (PDOException $e) {
            error_log("SubjectRepository::getByFaculty error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get unassigned subjects (no faculty assigned)
     */
    public function getUnassigned(): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE faculty_id IS NULL ORDER BY subject_code ASC");
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return array_map(fn($row) => new Subject($row), $data);
        } catch (PDOException $e) {
            error_log("SubjectRepository::getUnassigned error: " . $e->getMessage());
            return [];
        }
    }
}