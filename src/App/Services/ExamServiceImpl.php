<?php

namespace App\Services;

use App\Models\Exam;
use App\Services\ExamService;
use Exception;

/**
 * ExamServiceImpl
 * 
 * Implementation of the ExamService interface.
 * Handles all exam-related business logic.
 */
class ExamServiceImpl implements ExamService
{
    private Exam $examModel;

    public function __construct(?Exam $examModel = null)
    {
        $this->examModel = $examModel ?? new Exam();
    }

    /**
     * {@inheritdoc}
     */
    public function createExam(array $examData)
    {
        try {
            // Validate exam data
            $validationErrors = $this->validateExamData($examData);
            if (!empty($validationErrors)) {
                throw new Exception('Validation failed: ' . implode(', ', $validationErrors));
            }

            return $this->examModel->create($examData);
        } catch (Exception $e) {
            error_log("ExamService::createExam error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function updateExam(int $examId, array $examData): bool
    {
        try {
            // Check if exam exists
            if (!$this->examExists($examId)) {
                throw new Exception('Exam not found');
            }

            // Validate exam data
            $validationErrors = $this->validateExamData($examData);
            if (!empty($validationErrors)) {
                throw new Exception('Validation failed: ' . implode(', ', $validationErrors));
            }

            return $this->examModel->update($examId, $examData);
        } catch (Exception $e) {
            error_log("ExamService::updateExam error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deleteExam(int $examId): bool
    {
        try {
            // Check if exam exists
            if (!$this->examExists($examId)) {
                throw new Exception('Exam not found');
            }

            return $this->examModel->delete($examId);
        } catch (Exception $e) {
            error_log("ExamService::deleteExam error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getExamById(int $examId)
    {
        try {
            return $this->examModel->getExamById($examId);
        } catch (Exception $e) {
            error_log("ExamService::getExamById error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAllExams(): array
    {
        try {
            return $this->examModel->getAllExams() ?? [];
        } catch (Exception $e) {
            error_log("ExamService::getAllExams error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getExamsBySubject(int $subjectId): array
    {
        try {
            return $this->examModel->getExamsBySubject($subjectId) ?? [];
        } catch (Exception $e) {
            error_log("ExamService::getExamsBySubject error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getExamsByTeacher(int $teacherId): array
    {
        try {
            return $this->examModel->getExamsByTeacher($teacherId) ?? [];
        } catch (Exception $e) {
            error_log("ExamService::getExamsByTeacher error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getActiveExams(): array
    {
        try {
            return $this->examModel->getActiveExams() ?? [];
        } catch (Exception $e) {
            error_log("ExamService::getActiveExams error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function validateExamData(array $examData): array
    {
        $errors = [];

        // Validate title
        if (empty($examData['title'])) {
            $errors[] = 'Exam title is required';
        } elseif (strlen($examData['title']) < 3) {
            $errors[] = 'Exam title must be at least 3 characters long';
        }

        // Validate subject_id
        if (empty($examData['subject_id']) || !is_numeric($examData['subject_id'])) {
            $errors[] = 'Valid subject ID is required';
        }

        // Validate teacher_id
        if (empty($examData['teacher_id']) || !is_numeric($examData['teacher_id'])) {
            $errors[] = 'Valid teacher ID is required';
        }

        // Validate time_limit
        if (isset($examData['time_limit']) && (!is_numeric($examData['time_limit']) || $examData['time_limit'] <= 0)) {
            $errors[] = 'Time limit must be a positive number';
        }

        // Validate max_attempts
        if (isset($examData['max_attempts']) && (!is_numeric($examData['max_attempts']) || $examData['max_attempts'] <= 0)) {
            $errors[] = 'Max attempts must be a positive number';
        }

        return $errors;
    }

    /**
     * {@inheritdoc}
     */
    public function examExists(int $examId): bool
    {
        try {
            $exam = $this->getExamById($examId);
            return $exam !== false && $exam !== null;
        } catch (Exception $e) {
            error_log("ExamService::examExists error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setExamStatus(int $examId, bool $active): bool
    {
        try {
            // Check if exam exists
            if (!$this->examExists($examId)) {
                throw new Exception('Exam not found');
            }

            return $this->examModel->setExamStatus($examId, $active);
        } catch (Exception $e) {
            error_log("ExamService::setExamStatus error: " . $e->getMessage());
            return false;
        }
    }
}