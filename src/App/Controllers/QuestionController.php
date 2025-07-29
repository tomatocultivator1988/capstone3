<?php

namespace App\Controllers;

use App\Models\Question;
use App\Controllers\AuthController;

/**
 * TDD Step 119: Question Controller
 * GREEN PHASE: Handle question management API requests
 */
class QuestionController
{
    private $questionModel;
    private $authController;

    public function __construct()
    {
        $this->questionModel = new Question();
        $this->authController = new AuthController();
    }

    /**
     * Get questions by exam
     */
    public function getByExam()
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
            $questions = $this->questionModel->getQuestionsByExam($examId);
            
            echo json_encode([
                'status' => 'success',
                'data' => $questions
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to retrieve questions.'
            ]);
        }
    }

    /**
     * Create new question
     */
    public function store()
    {
        header('Content-Type: application/json');
        $this->authController->requireRole('faculty');

        $required = ['exam_id', 'question_text', 'question_type', 'points'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                http_response_code(400);
                echo json_encode([
                    'status' => 'error',
                    'message' => "Field {$field} is required."
                ]);
                return;
            }
        }

        try {
            // Get next order number for this exam
            $questionCount = $this->questionModel->getQuestionCountByExam($_POST['exam_id']);
            
            $questionData = [
                'exam_id' => $_POST['exam_id'],
                'question_text' => $_POST['question_text'],
                'question_type' => $_POST['question_type'],
                'points' => $_POST['points'],
                'order_number' => $questionCount + 1,
                'options' => isset($_POST['options']) ? json_encode($_POST['options']) : null,
                'correct_answer' => $_POST['correct_answer'] ?? null
            ];

            $questionId = $this->questionModel->create($questionData);

            if ($questionId) {
                http_response_code(201);
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Question created successfully.',
                    'question_id' => $questionId
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Failed to create question.'
                ]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'An error occurred while creating question.'
            ]);
        }
    }

    /**
     * Update question
     */
    public function update()
    {
        header('Content-Type: application/json');
        $this->authController->requireRole('faculty');

        $questionId = $_POST['question_id'] ?? null;
        if (!$questionId) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Question ID is required.'
            ]);
            return;
        }

        try {
            $updateData = [];
            $allowedFields = ['question_text', 'question_type', 'points', 'order_number', 'correct_answer'];
            
            foreach ($allowedFields as $field) {
                if (isset($_POST[$field])) {
                    $updateData[$field] = $_POST[$field];
                }
            }

            // Handle options separately for JSON encoding
            if (isset($_POST['options'])) {
                $updateData['options'] = json_encode($_POST['options']);
            }

            if (empty($updateData)) {
                http_response_code(400);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'No valid fields to update.'
                ]);
                return;
            }

            $result = $this->questionModel->update($questionId, $updateData);

            if ($result) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Question updated successfully.'
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Failed to update question.'
                ]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'An error occurred while updating question.'
            ]);
        }
    }

    /**
     * Delete question
     */
    public function delete()
    {
        header('Content-Type: application/json');
        $this->authController->requireRole('faculty');

        $questionId = $_POST['question_id'] ?? null;
        if (!$questionId) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Question ID is required.'
            ]);
            return;
        }

        try {
            $result = $this->questionModel->delete($questionId);

            if ($result) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Question deleted successfully.'
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Failed to delete question.'
                ]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'An error occurred while deleting question.'
            ]);
        }
    }

    /**
     * Reorder questions
     */
    public function reorder()
    {
        header('Content-Type: application/json');
        $this->authController->requireRole('faculty');

        $examId = $_POST['exam_id'] ?? null;
        $questionOrder = $_POST['question_order'] ?? null;

        if (!$examId || !$questionOrder || !is_array($questionOrder)) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Exam ID and question order array are required.'
            ]);
            return;
        }

        try {
            $result = $this->questionModel->reorderQuestions($examId, $questionOrder);

            if ($result) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Questions reordered successfully.'
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Failed to reorder questions.'
                ]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'An error occurred while reordering questions.'
            ]);
        }
    }

    /**
     * Get question statistics for an exam
     */
    public function getExamStats()
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
            $questionCount = $this->questionModel->getQuestionCountByExam($examId);
            $totalPoints = $this->questionModel->getTotalPointsByExam($examId);
            
            echo json_encode([
                'status' => 'success',
                'data' => [
                    'question_count' => $questionCount,
                    'total_points' => $totalPoints
                ]
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to retrieve exam statistics.'
            ]);
        }
    }
}