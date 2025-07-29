<?php

namespace App\Controllers;

use App\Models\ExamResult;
use App\Controllers\AuthController;

/**
 * TDD Step 120: ExamResult Controller
 * GREEN PHASE: Handle exam results and analytics API requests
 */
class ExamResultController
{
    private $examResultModel;
    private $authController;

    public function __construct()
    {
        $this->examResultModel = new ExamResult();
        $this->authController = new AuthController();
    }

    /**
     * Submit exam result
     */
    public function store()
    {
        header('Content-Type: application/json');
        $this->authController->requireAuth();

        $required = ['exam_id', 'answers', 'score', 'total_points', 'time_taken'];
        foreach ($required as $field) {
            if (!isset($_POST[$field])) {
                http_response_code(400);
                echo json_encode([
                    'status' => 'error',
                    'message' => "Field {$field} is required."
                ]);
                return;
            }
        }

        try {
            $studentId = $_SESSION['user_id'];
            $examId = $_POST['exam_id'];

            // Check if student has already taken this exam
            if ($this->examResultModel->hasStudentTakenExam($examId, $studentId)) {
                http_response_code(409);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'You have already taken this exam.'
                ]);
                return;
            }

            $resultData = [
                'exam_id' => $examId,
                'student_id' => $studentId,
                'answers' => is_array($_POST['answers']) ? json_encode($_POST['answers']) : $_POST['answers'],
                'score' => $_POST['score'],
                'total_points' => $_POST['total_points'],
                'time_taken' => $_POST['time_taken'],
                'status' => 'completed'
            ];

            $resultId = $this->examResultModel->create($resultData);

            if ($resultId) {
                http_response_code(201);
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Exam result submitted successfully.',
                    'result_id' => $resultId
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Failed to submit exam result.'
                ]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'An error occurred while submitting exam result.'
            ]);
        }
    }

    /**
     * Get results by exam
     */
    public function getByExam()
    {
        header('Content-Type: application/json');
        $this->authController->requireRole(['admin', 'faculty']);

        $examId = $_GET['exam_id'] ?? null;
        if (!$examId) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Exam ID is required.'
            ]);
            return;
        }

        try {
            $results = $this->examResultModel->getResultsByExam($examId);
            
            echo json_encode([
                'status' => 'success',
                'data' => $results
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to retrieve exam results.'
            ]);
        }
    }

    /**
     * Get results by student
     */
    public function getByStudent()
    {
        header('Content-Type: application/json');
        $this->authController->requireAuth();

        $studentId = $_GET['student_id'] ?? $_SESSION['user_id'];

        // Only allow students to view their own results, or admin/faculty to view any
        if ($_SESSION['role'] === 'student' && $studentId != $_SESSION['user_id']) {
            http_response_code(403);
            echo json_encode([
                'status' => 'error',
                'message' => 'You can only view your own results.'
            ]);
            return;
        }

        try {
            $results = $this->examResultModel->getResultsByStudent($studentId);
            
            echo json_encode([
                'status' => 'success',
                'data' => $results
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to retrieve student results.'
            ]);
        }
    }

    /**
     * Get exam analytics
     */
    public function getExamAnalytics()
    {
        header('Content-Type: application/json');
        $this->authController->requireRole(['admin', 'faculty']);

        $examId = $_GET['exam_id'] ?? null;
        if (!$examId) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Exam ID is required.'
            ]);
            return;
        }

        try {
            $analytics = $this->examResultModel->getExamAnalytics($examId);
            
            echo json_encode([
                'status' => 'success',
                'data' => $analytics
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to retrieve exam analytics.'
            ]);
        }
    }

    /**
     * Get student performance analytics
     */
    public function getStudentPerformance()
    {
        header('Content-Type: application/json');
        $this->authController->requireAuth();

        $studentId = $_GET['student_id'] ?? $_SESSION['user_id'];

        // Only allow students to view their own performance, or admin/faculty to view any
        if ($_SESSION['role'] === 'student' && $studentId != $_SESSION['user_id']) {
            http_response_code(403);
            echo json_encode([
                'status' => 'error',
                'message' => 'You can only view your own performance.'
            ]);
            return;
        }

        try {
            $performance = $this->examResultModel->getStudentPerformance($studentId);
            
            echo json_encode([
                'status' => 'success',
                'data' => $performance
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to retrieve student performance.'
            ]);
        }
    }

    /**
     * Get result by ID
     */
    public function show()
    {
        header('Content-Type: application/json');
        $this->authController->requireAuth();

        $resultId = $_GET['result_id'] ?? null;
        if (!$resultId) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Result ID is required.'
            ]);
            return;
        }

        try {
            $result = $this->examResultModel->findById($resultId);
            
            if (!$result) {
                http_response_code(404);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Result not found.'
                ]);
                return;
            }

            // Check if student can view this result
            if ($_SESSION['role'] === 'student' && $result['student_id'] != $_SESSION['user_id']) {
                http_response_code(403);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'You can only view your own results.'
                ]);
                return;
            }

            echo json_encode([
                'status' => 'success',
                'data' => $result
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to retrieve result.'
            ]);
        }
    }

    /**
     * Check if student has taken exam
     */
    public function checkExamStatus()
    {
        header('Content-Type: application/json');
        $this->authController->requireAuth();

        $examId = $_GET['exam_id'] ?? null;
        if (!$examId) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Exam ID is required.'
            ]);
            return;
        }

        try {
            $studentId = $_SESSION['user_id'];
            $hasTaken = $this->examResultModel->hasStudentTakenExam($examId, $studentId);
            
            echo json_encode([
                'status' => 'success',
                'data' => [
                    'has_taken' => $hasTaken,
                    'can_take' => !$hasTaken
                ]
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to check exam status.'
            ]);
        }
    }
}