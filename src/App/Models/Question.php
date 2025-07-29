<?php

namespace App\Models;

use App\Config\Database;
use PDO;
use PDOException;

/**
 * TDD Step 95: Question Model class
 * GREEN PHASE: Implementation to make question tests pass
 * Handles question bank operations
 */
class Question
{
    private $db;
    private $table = 'questions';

    /**
     * TDD: Constructor - Initialize database connection
     */
    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * TDD Step 96: Create question method
     * GREEN PHASE: Method to make create test pass
     * 
     * @param array $data Question data
     * @return int|false Question ID if successful, false otherwise
     */
    public function create($data)
    {
        try {
            $sql = "INSERT INTO {$this->table} (exam_id, question_text, question_type, points, order_number, options, correct_answer, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $data['exam_id'],
                $data['question_text'],
                $data['question_type'],
                $data['points'],
                $data['order_number'],
                $data['options'] ?? null,
                $data['correct_answer'] ?? null
            ]);

            return $result ? $this->db->lastInsertId() : false;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * TDD Step 97: Get questions by exam method
     * GREEN PHASE: Method to make getQuestionsByExam test pass
     * 
     * @param int $exam_id
     * @return array
     */
    public function getQuestionsByExam($exam_id)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE exam_id = ? ORDER BY order_number ASC");
            $stmt->execute([$exam_id]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * TDD Step 98: Update question method
     * GREEN PHASE: Method to make update test pass
     * 
     * @param int $question_id
     * @param array $data
     * @return bool
     */
    public function update($question_id, $data)
    {
        try {
            $setParts = [];
            $values = [];

            $allowedFields = ['question_text', 'question_type', 'points', 'order_number', 'options', 'correct_answer'];
            foreach ($data as $key => $value) {
                if (in_array($key, $allowedFields)) {
                    $setParts[] = "{$key} = ?";
                    $values[] = $value;
                }
            }

            if (empty($setParts)) {
                return false;
            }

            $values[] = $question_id;
            
            $sql = "UPDATE {$this->table} SET " . implode(', ', $setParts) . ", updated_at = NOW() WHERE question_id = ?";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($values);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * TDD Step 99: Delete question method
     * GREEN PHASE: Method to make delete test pass
     * 
     * @param int $question_id
     * @return bool
     */
    public function delete($question_id)
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE question_id = ?");
            return $stmt->execute([$question_id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * TDD Step 100: Reorder questions method
     * GREEN PHASE: Method to make reorderQuestions test pass
     * 
     * @param int $exam_id
     * @param array $questionOrder Array of question IDs in new order
     * @return bool
     */
    public function reorderQuestions($exam_id, $questionOrder)
    {
        try {
            $this->db->beginTransaction();

            foreach ($questionOrder as $index => $questionId) {
                $sql = "UPDATE {$this->table} SET order_number = ?, updated_at = NOW() 
                        WHERE question_id = ? AND exam_id = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$index + 1, $questionId, $exam_id]);
            }

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollback();
            return false;
        }
    }

    /**
     * TDD Step 101: Find question by ID method
     * GREEN PHASE: Method for detailed question retrieval
     * 
     * @param int $question_id
     * @return array|false
     */
    public function findById($question_id)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE question_id = ?");
            $stmt->execute([$question_id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * TDD Step 102: Get question count by exam method
     * GREEN PHASE: Method for exam statistics
     * 
     * @param int $exam_id
     * @return int
     */
    public function getQuestionCountByExam($exam_id)
    {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM {$this->table} WHERE exam_id = ?");
            $stmt->execute([$exam_id]);
            $result = $stmt->fetch();
            return $result['count'] ?? 0;
        } catch (PDOException $e) {
            return 0;
        }
    }

    /**
     * TDD Step 103: Get total points by exam method
     * GREEN PHASE: Method for exam scoring
     * 
     * @param int $exam_id
     * @return int
     */
    public function getTotalPointsByExam($exam_id)
    {
        try {
            $stmt = $this->db->prepare("SELECT SUM(points) as total FROM {$this->table} WHERE exam_id = ?");
            $stmt->execute([$exam_id]);
            $result = $stmt->fetch();
            return $result['total'] ?? 0;
        } catch (PDOException $e) {
            return 0;
        }
    }
}