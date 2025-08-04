<?php

namespace App\DAO\Impl;

use App\DAO\Interface\ExamDAOInterface;
use App\Model\Exam;
use App\Config\Database;
use PDO;
use PDOException;

/**
 * ExamDAO Implementation
 * 
 * Handles all database operations for Exam entities.
 * Responsible for data access only, no business logic.
 */
class ExamDAOImpl implements ExamDAOInterface
{
    private PDO $db;
    private string $table = 'exams';

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Find exam by ID
     */
    public function findById(int $exam_id): ?Exam
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE exam_id = ?");
            $stmt->execute([$exam_id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $data ? new Exam($data) : null;
        } catch (PDOException $e) {
            error_log("ExamDAOImpl::findById error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get all exams
     */
    public function getAll(): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} ORDER BY created_at DESC");
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return array_map(fn($row) => new Exam($row), $data);
        } catch (PDOException $e) {
            error_log("ExamDAOImpl::getAll error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get exams by faculty
     */
    public function getByFaculty(int $faculty_id): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE created_by = ? ORDER BY created_at DESC");
            $stmt->execute([$faculty_id]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return array_map(fn($row) => new Exam($row), $data);
        } catch (PDOException $e) {
            error_log("ExamDAOImpl::getByFaculty error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get exams for student (by year level and section)
     */
    public function getForStudent(int $year_level, string $section): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE year_level = ? AND section = ? AND status = 'active' ORDER BY created_at DESC");
            $stmt->execute([$year_level, $section]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return array_map(fn($row) => new Exam($row), $data);
        } catch (PDOException $e) {
            error_log("ExamDAOImpl::getForStudent error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get exams by subject
     */
    public function getBySubject(int $subject_id): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE subject_id = ? ORDER BY created_at DESC");
            $stmt->execute([$subject_id]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return array_map(fn($row) => new Exam($row), $data);
        } catch (PDOException $e) {
            error_log("ExamDAOImpl::getBySubject error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get active exams
     */
    public function getActive(): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE status = 'active' ORDER BY created_at DESC");
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return array_map(fn($row) => new Exam($row), $data);
        } catch (PDOException $e) {
            error_log("ExamDAOImpl::getActive error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Create new exam
     */
    public function create(Exam $exam): ?int
    {
        try {
            $sql = "INSERT INTO {$this->table} (title, description, subject_id, created_by, duration_minutes, total_questions, passing_score, status, year_level, section, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $exam->getTitle(),
                $exam->getDescription(),
                $exam->getSubjectId(),
                $exam->getCreatedBy(),
                $exam->getDurationMinutes(),
                $exam->getTotalQuestions(),
                $exam->getPassingScore(),
                $exam->getStatus(),
                $exam->getYearLevel(),
                $exam->getSection()
            ]);

            return $result ? (int)$this->db->lastInsertId() : null;
        } catch (PDOException $e) {
            error_log("ExamDAOImpl::create error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Update exam
     */
    public function update(Exam $exam): bool
    {
        try {
            $sql = "UPDATE {$this->table} SET title = ?, description = ?, subject_id = ?, duration_minutes = ?, total_questions = ?, passing_score = ?, status = ?, year_level = ?, section = ?, updated_at = NOW() 
                    WHERE exam_id = ?";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $exam->getTitle(),
                $exam->getDescription(),
                $exam->getSubjectId(),
                $exam->getDurationMinutes(),
                $exam->getTotalQuestions(),
                $exam->getPassingScore(),
                $exam->getStatus(),
                $exam->getYearLevel(),
                $exam->getSection(),
                $exam->getExamId()
            ]);
        } catch (PDOException $e) {
            error_log("ExamDAOImpl::update error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete exam
     */
    public function delete(int $exam_id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE exam_id = ?");
            return $stmt->execute([$exam_id]);
        } catch (PDOException $e) {
            error_log("ExamDAOImpl::delete error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update exam status
     */
    public function updateStatus(int $exam_id, string $status): bool
    {
        try {
            $stmt = $this->db->prepare("UPDATE {$this->table} SET status = ?, updated_at = NOW() WHERE exam_id = ?");
            return $stmt->execute([$status, $exam_id]);
        } catch (PDOException $e) {
            error_log("ExamDAOImpl::updateStatus error: " . $e->getMessage());
            return false;
        }
    }
}