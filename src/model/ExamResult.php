<?php

namespace App\Models;

use App\Config\Database;
use PDO;
use PDOException;

/**
 * TDD Step 110: ExamResult Model class
 * GREEN PHASE: Implementation to make result tests pass
 * Handles exam results and analytics
 */
class ExamResult
{
    private $db;
    private $table = 'exam_results';

    /**
     * TDD: Constructor - Initialize database connection
     */
    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * TDD Step 111: Create exam result method
     * GREEN PHASE: Method to make create test pass
     * 
     * @param array $data Result data
     * @return int|false Result ID if successful, false otherwise
     */
    public function create($data)
    {
        try {
            $sql = "INSERT INTO {$this->table} (exam_id, student_id, answers, score, total_points, time_taken, status, submitted_at, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW(), NOW())";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $data['exam_id'],
                $data['student_id'],
                $data['answers'],
                $data['score'],
                $data['total_points'],
                $data['time_taken'],
                $data['status']
            ]);

            return $result ? $this->db->lastInsertId() : false;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * TDD Step 112: Get results by exam method
     * GREEN PHASE: Method to make getResultsByExam test pass
     * 
     * @param int $exam_id
     * @return array
     */
    public function getResultsByExam($exam_id)
    {
        try {
            $sql = "SELECT er.*, u.full_name as student_name, u.school_id
                    FROM {$this->table} er
                    LEFT JOIN users u ON er.student_id = u.user_id
                    WHERE er.exam_id = ?
                    ORDER BY er.score DESC, er.submitted_at ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$exam_id]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * TDD Step 113: Get results by student method
     * GREEN PHASE: Method to make getResultsByStudent test pass
     * 
     * @param int $student_id
     * @return array
     */
    public function getResultsByStudent($student_id)
    {
        try {
            $sql = "SELECT er.*, e.exam_title, s.subject_name, s.subject_code
                    FROM {$this->table} er
                    LEFT JOIN exams e ON er.exam_id = e.exam_id
                    LEFT JOIN subjects s ON e.subject_id = s.subject_id
                    WHERE er.student_id = ?
                    ORDER BY er.submitted_at DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$student_id]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * TDD Step 114: Get exam analytics method
     * GREEN PHASE: Method to make getExamAnalytics test pass
     * 
     * @param int $exam_id
     * @return array
     */
    public function getExamAnalytics($exam_id)
    {
        try {
            $sql = "SELECT 
                        COUNT(*) as total_attempts,
                        AVG(score) as average_score,
                        MAX(score) as highest_score,
                        MIN(score) as lowest_score,
                        AVG(time_taken) as average_time,
                        COUNT(CASE WHEN score >= (total_points * 0.75) THEN 1 END) as passed_count
                    FROM {$this->table} 
                    WHERE exam_id = ? AND status = 'completed'";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$exam_id]);
            $result = $stmt->fetch();
            
            // Calculate pass rate
            $passRate = $result['total_attempts'] > 0 
                ? ($result['passed_count'] / $result['total_attempts']) * 100 
                : 0;
            
            return [
                'total_attempts' => (int)$result['total_attempts'],
                'average_score' => round((float)$result['average_score'], 2),
                'highest_score' => (int)$result['highest_score'],
                'lowest_score' => (int)$result['lowest_score'],
                'average_time' => round((float)$result['average_time'], 2),
                'pass_rate' => round($passRate, 2),
                'passed_count' => (int)$result['passed_count']
            ];
        } catch (PDOException $e) {
            return [
                'total_attempts' => 0,
                'average_score' => 0,
                'highest_score' => 0,
                'lowest_score' => 0,
                'average_time' => 0,
                'pass_rate' => 0,
                'passed_count' => 0
            ];
        }
    }

    /**
     * TDD Step 115: Get student performance method
     * GREEN PHASE: Method to make getStudentPerformance test pass
     * 
     * @param int $student_id
     * @return array
     */
    public function getStudentPerformance($student_id)
    {
        try {
            $sql = "SELECT 
                        COUNT(*) as total_exams,
                        AVG(score) as average_score,
                        MAX(score) as best_score,
                        MIN(score) as worst_score,
                        AVG((score/total_points)*100) as average_percentage,
                        COUNT(CASE WHEN score >= (total_points * 0.75) THEN 1 END) as passed_exams
                    FROM {$this->table} 
                    WHERE student_id = ? AND status = 'completed'";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$student_id]);
            $result = $stmt->fetch();
            
            // Get subject-wise performance
            $subjectSql = "SELECT 
                            s.subject_name,
                            s.subject_code,
                            COUNT(*) as exam_count,
                            AVG(er.score) as avg_score,
                            AVG((er.score/er.total_points)*100) as avg_percentage
                        FROM {$this->table} er
                        LEFT JOIN exams e ON er.exam_id = e.exam_id
                        LEFT JOIN subjects s ON e.subject_id = s.subject_id
                        WHERE er.student_id = ? AND er.status = 'completed'
                        GROUP BY s.subject_id
                        ORDER BY avg_percentage DESC";
            
            $subjectStmt = $this->db->prepare($subjectSql);
            $subjectStmt->execute([$student_id]);
            $subjectPerformance = $subjectStmt->fetchAll();
            
            return [
                'overall' => [
                    'total_exams' => (int)$result['total_exams'],
                    'average_score' => round((float)$result['average_score'], 2),
                    'best_score' => (int)$result['best_score'],
                    'worst_score' => (int)$result['worst_score'],
                    'average_percentage' => round((float)$result['average_percentage'], 2),
                    'passed_exams' => (int)$result['passed_exams']
                ],
                'by_subject' => $subjectPerformance
            ];
        } catch (PDOException $e) {
            return [
                'overall' => [
                    'total_exams' => 0,
                    'average_score' => 0,
                    'best_score' => 0,
                    'worst_score' => 0,
                    'average_percentage' => 0,
                    'passed_exams' => 0
                ],
                'by_subject' => []
            ];
        }
    }

    /**
     * TDD Step 116: Find result by ID method
     * GREEN PHASE: Method for detailed result retrieval
     * 
     * @param int $result_id
     * @return array|false
     */
    public function findById($result_id)
    {
        try {
            $sql = "SELECT er.*, e.exam_title, s.subject_name, u.full_name as student_name
                    FROM {$this->table} er
                    LEFT JOIN exams e ON er.exam_id = e.exam_id
                    LEFT JOIN subjects s ON e.subject_id = s.subject_id
                    LEFT JOIN users u ON er.student_id = u.user_id
                    WHERE er.result_id = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$result_id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * TDD Step 117: Check if student has taken exam method
     * GREEN PHASE: Method to prevent duplicate attempts
     * 
     * @param int $exam_id
     * @param int $student_id
     * @return bool
     */
    public function hasStudentTakenExam($exam_id, $student_id)
    {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM {$this->table} WHERE exam_id = ? AND student_id = ?");
            $stmt->execute([$exam_id, $student_id]);
            $result = $stmt->fetch();
            return $result['count'] > 0;
        } catch (PDOException $e) {
            return false;
        }
    }
}