<?php

namespace App\DAO\Impl;

use App\DAO\Interface\QuestionDAOInterface;
use App\Model\Question;
use App\Config\Database;
use PDO;
use PDOException;

/**
 * QuestionDAO Implementation
 * 
 * Handles all database operations for Question entities.
 * Responsible for data access only, no business logic.
 */
class QuestionDAOImpl implements QuestionDAOInterface
{
    private PDO $db;
    private string $table = 'questions';

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Find question by ID
     */
    public function findById(int $question_id): ?Question
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE question_id = ?");
            $stmt->execute([$question_id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $data ? new Question($data) : null;
        } catch (PDOException $e) {
            error_log("QuestionDAOImpl::findById error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get questions by exam
     */
    public function getByExam(int $exam_id): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE exam_id = ? ORDER BY order_number ASC");
            $stmt->execute([$exam_id]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return array_map(fn($row) => new Question($row), $data);
        } catch (PDOException $e) {
            error_log("QuestionDAOImpl::getByExam error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get question count by exam
     */
    public function getCountByExam(int $exam_id): int
    {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} WHERE exam_id = ?");
            $stmt->execute([$exam_id]);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("QuestionDAOImpl::getCountByExam error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get total points by exam
     */
    public function getTotalPointsByExam(int $exam_id): int
    {
        try {
            $stmt = $this->db->prepare("SELECT SUM(points) FROM {$this->table} WHERE exam_id = ?");
            $stmt->execute([$exam_id]);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("QuestionDAOImpl::getTotalPointsByExam error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Create new question
     */
    public function create(Question $question): ?int
    {
        try {
            $sql = "INSERT INTO {$this->table} (exam_id, question_text, question_type, points, order_number, options, correct_answer, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $question->getExamId(),
                $question->getQuestionText(),
                $question->getQuestionType(),
                $question->getPoints(),
                $question->getOrderNumber(),
                $question->getOptions(),
                $question->getCorrectAnswer()
            ]);

            return $result ? (int)$this->db->lastInsertId() : null;
        } catch (PDOException $e) {
            error_log("QuestionDAOImpl::create error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Update question
     */
    public function update(Question $question): bool
    {
        try {
            $sql = "UPDATE {$this->table} SET exam_id = ?, question_text = ?, question_type = ?, points = ?, order_number = ?, options = ?, correct_answer = ?, updated_at = NOW() 
                    WHERE question_id = ?";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $question->getExamId(),
                $question->getQuestionText(),
                $question->getQuestionType(),
                $question->getPoints(),
                $question->getOrderNumber(),
                $question->getOptions(),
                $question->getCorrectAnswer(),
                $question->getQuestionId()
            ]);
        } catch (PDOException $e) {
            error_log("QuestionDAOImpl::update error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete question
     */
    public function delete(int $question_id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE question_id = ?");
            return $stmt->execute([$question_id]);
        } catch (PDOException $e) {
            error_log("QuestionDAOImpl::delete error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Reorder questions
     */
    public function reorder(int $exam_id, array $questionOrder): bool
    {
        try {
            $this->db->beginTransaction();
            
            foreach ($questionOrder as $order => $question_id) {
                $stmt = $this->db->prepare("UPDATE {$this->table} SET order_number = ? WHERE question_id = ? AND exam_id = ?");
                $stmt->execute([$order + 1, $question_id, $exam_id]);
            }
            
            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("QuestionDAOImpl::reorder error: " . $e->getMessage());
            return false;
        }
    }
}