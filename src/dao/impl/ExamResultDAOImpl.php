<?php

namespace App\DAO\Impl;

use App\DAO\Interface\ExamResultDAOInterface;
use App\Model\ExamResult;
use App\Config\Database;
use PDO;
use PDOException;

/**
 * ExamResultDAO Implementation
 * 
 * Handles all database operations for ExamResult entities.
 * Responsible for data access only, no business logic.
 */
class ExamResultDAOImpl implements ExamResultDAOInterface
{
    private PDO $db;
    private string $table = 'exam_results';

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Find exam result by ID
     */
    public function findById(int $result_id): ?ExamResult
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE result_id = ?");
            $stmt->execute([$result_id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $data ? new ExamResult($data) : null;
        } catch (PDOException $e) {
            error_log("ExamResultDAOImpl::findById error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get results by exam
     */
    public function getByExam(int $exam_id): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE exam_id = ? ORDER BY score DESC, submitted_at ASC");
            $stmt->execute([$exam_id]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return array_map(fn($row) => new ExamResult($row), $data);
        } catch (PDOException $e) {
            error_log("ExamResultDAOImpl::getByExam error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get results by student
     */
    public function getByStudent(int $student_id): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE student_id = ? ORDER BY submitted_at DESC");
            $stmt->execute([$student_id]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return array_map(fn($row) => new ExamResult($row), $data);
        } catch (PDOException $e) {
            error_log("ExamResultDAOImpl::getByStudent error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Check if student has taken exam
     */
    public function hasStudentTakenExam(int $exam_id, int $student_id): bool
    {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} WHERE exam_id = ? AND student_id = ?");
            $stmt->execute([$exam_id, $student_id]);
            return (int)$stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("ExamResultDAOImpl::hasStudentTakenExam error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Create new exam result
     */
    public function create(ExamResult $result): ?int
    {
        try {
            $sql = "INSERT INTO {$this->table} (exam_id, student_id, answers, score, total_points, time_taken, status, submitted_at, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW(), NOW())";
            
            $stmt = $this->db->prepare($sql);
            $result_exec = $stmt->execute([
                $result->getExamId(),
                $result->getStudentId(),
                $result->getAnswers(),
                $result->getScore(),
                $result->getTotalPoints(),
                $result->getTimeTaken(),
                $result->getStatus()
            ]);

            return $result_exec ? (int)$this->db->lastInsertId() : null;
        } catch (PDOException $e) {
            error_log("ExamResultDAOImpl::create error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Update exam result
     */
    public function update(ExamResult $result): bool
    {
        try {
            $sql = "UPDATE {$this->table} SET exam_id = ?, student_id = ?, answers = ?, score = ?, total_points = ?, time_taken = ?, status = ?, updated_at = NOW() 
                    WHERE result_id = ?";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $result->getExamId(),
                $result->getStudentId(),
                $result->getAnswers(),
                $result->getScore(),
                $result->getTotalPoints(),
                $result->getTimeTaken(),
                $result->getStatus(),
                $result->getResultId()
            ]);
        } catch (PDOException $e) {
            error_log("ExamResultDAOImpl::update error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete exam result
     */
    public function delete(int $result_id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE result_id = ?");
            return $stmt->execute([$result_id]);
        } catch (PDOException $e) {
            error_log("ExamResultDAOImpl::delete error: " . $e->getMessage());
            return false;
        }
    }
}