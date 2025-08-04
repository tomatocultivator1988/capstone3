<?php

namespace App\Models;

use App\Config\Database;
use PDO;
use PDOException;

class Exam
{
    private $db;
    private $table = 'exams';

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get all exams
     */
    public function getAllExams()
    {
        try {
            $sql = "SELECT e.*, s.subject_name, s.subject_code, u.full_name as creator_name
                    FROM {$this->table} e
                    LEFT JOIN subjects s ON e.subject_id = s.subject_id
                    LEFT JOIN users u ON e.created_by = u.user_id
                    ORDER BY e.created_at DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Get exams by faculty
     */
    public function getExamsByFaculty($faculty_id)
    {
        try {
            $sql = "SELECT e.*, s.subject_name, s.subject_code
                    FROM {$this->table} e
                    LEFT JOIN subjects s ON e.subject_id = s.subject_id
                    WHERE e.created_by = ?
                    ORDER BY e.created_at DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$faculty_id]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Get exams for student (by year level and section)
     */
    public function getExamsForStudent($year_level, $section)
    {
        try {
            $sql = "SELECT e.*, s.subject_name, s.subject_code, u.full_name as creator_name
                    FROM {$this->table} e
                    LEFT JOIN subjects s ON e.subject_id = s.subject_id
                    LEFT JOIN users u ON e.created_by = u.user_id
                    WHERE e.year_level = ? AND e.section = ? AND e.status = 'active'
                    ORDER BY e.created_at DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$year_level, $section]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Find exam by ID
     */
    public function findById($exam_id)
    {
        try {
            $sql = "SELECT e.*, s.subject_name, s.subject_code, u.full_name as creator_name
                    FROM {$this->table} e
                    LEFT JOIN subjects s ON e.subject_id = s.subject_id
                    LEFT JOIN users u ON e.created_by = u.user_id
                    WHERE e.exam_id = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$exam_id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * TDD Step 81: Enhanced create exam method
     * GREEN PHASE: Method to make create test pass
     * 
     * @param array $data Exam data
     * @return int|false Exam ID if successful, false otherwise
     */
    public function create($data)
    {
        try {
            $sql = "INSERT INTO {$this->table} (exam_title, subject_id, created_by, duration, total_points, instructions, exam_date, start_time, end_time, status, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $data['exam_title'],
                $data['subject_id'],
                $data['created_by'],
                $data['duration'],
                $data['total_points'],
                $data['instructions'] ?? null,
                $data['exam_date'],
                $data['start_time'],
                $data['end_time'],
                $data['status'] ?? 'draft'
            ]);

            return $result ? $this->db->lastInsertId() : false;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Update exam
     */
    public function update($exam_id, $data)
    {
        try {
            $sql = "UPDATE {$this->table} SET title = ?, instructions = ?, subject_id = ?, year_level = ?, section = ?, status = ?, time_limit = ?, updated_at = NOW() 
                    WHERE exam_id = ?";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $data['title'],
                $data['instructions'],
                $data['subject_id'],
                $data['year_level'],
                $data['section'],
                $data['status'],
                $data['time_limit'],
                $exam_id
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Delete exam
     */
    public function delete($exam_id)
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE exam_id = ?");
            return $stmt->execute([$exam_id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * TDD Step 84: Update exam status method
     * GREEN PHASE: Method to make updateStatus test pass
     * 
     * @param int $exam_id
     * @param string $status
     * @return bool
     */
    public function updateStatus($exam_id, $status)
    {
        try {
            $sql = "UPDATE {$this->table} SET status = ?, updated_at = NOW() WHERE exam_id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$status, $exam_id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * TDD Step 83: Get exams by subject method
     * GREEN PHASE: Method to make getExamsBySubject test pass
     * 
     * @param int $subject_id
     * @return array
     */
    public function getExamsBySubject($subject_id)
    {
        try {
            $sql = "SELECT e.*, s.subject_name, s.subject_code, u.full_name as creator_name
                    FROM {$this->table} e
                    LEFT JOIN subjects s ON e.subject_id = s.subject_id
                    LEFT JOIN users u ON e.created_by = u.user_id
                    WHERE e.subject_id = ?
                    ORDER BY e.exam_date DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$subject_id]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * TDD Step 85: Get active exams method
     * GREEN PHASE: Method to make getActiveExams test pass
     * 
     * @return array
     */
    public function getActiveExams()
    {
        try {
            $sql = "SELECT e.*, s.subject_name, s.subject_code
                    FROM {$this->table} e
                    LEFT JOIN subjects s ON e.subject_id = s.subject_id
                    WHERE e.status = 'published' 
                    AND e.exam_date >= CURDATE()
                    ORDER BY e.exam_date ASC, e.start_time ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
}