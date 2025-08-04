<?php

namespace App\Controllers;

use App\Services\ExamService;
use App\Services\AuthService;
use App\Services\ServiceContainer;
use Exception;

class ExamController
{
    private ExamService $examService;
    private AuthService $authService;

    public function __construct(?ExamService $examService = null, ?AuthService $authService = null)
    {
        $container = ServiceContainer::getInstance();
        $this->examService = $examService ?? $container->get(ExamService::class);
        $this->authService = $authService ?? $container->get(AuthService::class);
    }

    /**
     * Get all exams
     */
    public function index()
    {
        header('Content-Type: application/json');
        
        try {
            $this->authService->requireAuth();
            $exams = $this->examService->getAllExams();
            
            echo json_encode([
                'status' => 'success',
                'data' => $exams
            ]);
        } catch (Exception $e) {
            http_response_code($e->getCode() ?: 500);
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage() ?: 'Failed to fetch exams'
            ]);
        }
    }

    /**
     * Get exam by ID
     */
    public function show()
    {
        header('Content-Type: application/json');
        $this->authController->requireAuth();

        $exam_id = $_GET['id'] ?? null;
        
        if (!$exam_id) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Exam ID is required'
            ]);
            return;
        }

        try {
            $exam = $this->examModel->findById($exam_id);
            
            if (!$exam) {
                http_response_code(404);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Exam not found'
                ]);
                return;
            }

            echo json_encode([
                'status' => 'success',
                'data' => $exam
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to fetch exam'
            ]);
        }
    }

    /**
     * Get exams by subject
     */
    public function getBySubject()
    {
        header('Content-Type: application/json');
        $this->authController->requireAuth();

        $subject_id = $_GET['subject_id'] ?? null;
        
        if (!$subject_id) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Subject ID is required'
            ]);
            return;
        }

        try {
            $exams = $this->examModel->getBySubject($subject_id);
            
            echo json_encode([
                'status' => 'success',
                'data' => $exams
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to fetch exams'
            ]);
        }
    }

    /**
     * Create new exam (Admin/Faculty only)
     */
    public function store()
    {
        header('Content-Type: application/json');
        $this->authController->requireRole(['admin', 'faculty']);

        $input = json_decode(file_get_contents('php://input'), true);
        
        $required_fields = ['exam_title', 'subject_id', 'duration_minutes'];
        foreach ($required_fields as $field) {
            if (empty($input[$field])) {
                http_response_code(400);
                echo json_encode([
                    'status' => 'error',
                    'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required'
                ]);
                return;
            }
        }

        // Add created_by from session
        $input['created_by'] = $_SESSION['user_id'];

        try {
            $exam_id = $this->examModel->create($input);
            
            if ($exam_id) {
                http_response_code(201);
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Exam created successfully',
                    'data' => ['exam_id' => $exam_id]
                ]);
            } else {
                http_response_code(400);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Failed to create exam'
                ]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to create exam'
            ]);
        }
    }

    /**
     * Update exam (Admin/Faculty only)
     */
    public function update()
    {
        header('Content-Type: application/json');
        $this->authController->requireRole(['admin', 'faculty']);

        $exam_id = $_GET['id'] ?? null;
        if (!$exam_id) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Exam ID is required'
            ]);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        try {
            $success = $this->examModel->update($exam_id, $input);
            
            if ($success) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Exam updated successfully'
                ]);
            } else {
                http_response_code(400);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Failed to update exam'
                ]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to update exam'
            ]);
        }
    }

    /**
     * Delete exam (Admin only)
     */
    public function delete()
    {
        header('Content-Type: application/json');
        $this->authController->requireRole('admin');

        $exam_id = $_GET['id'] ?? null;
        if (!$exam_id) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Exam ID is required'
            ]);
            return;
        }

        try {
            $success = $this->examModel->delete($exam_id);
            
            if ($success) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Exam deleted successfully'
                ]);
            } else {
                http_response_code(400);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Failed to delete exam'
                ]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to delete exam'
            ]);
        }
    }

    /**
     * TDD: Update exam status
     */
    public function updateStatus()
    {
        header('Content-Type: application/json');
        $this->authController->requireRole(['admin', 'faculty']);

        $examId = $_POST['exam_id'] ?? null;
        $status = $_POST['status'] ?? null;

        if (!$examId || !$status) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Exam ID and status are required.'
            ]);
            return;
        }

        try {
            $result = $this->examModel->updateStatus($examId, $status);

            if ($result) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Exam status updated successfully.'
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Failed to update exam status.'
                ]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'An error occurred while updating exam status.'
            ]);
        }
    }

    /**
     * TDD: Get active exams
     */
    public function getActiveExams()
    {
        header('Content-Type: application/json');
        $this->authController->requireAuth();

        try {
            $exams = $this->examModel->getActiveExams();
            
            echo json_encode([
                'status' => 'success',
                'data' => $exams
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to retrieve active exams.'
            ]);
        }
    }
}