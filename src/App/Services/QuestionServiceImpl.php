<?php

namespace App\Services;

use App\Models\Question;
use App\Services\QuestionService;
use Exception;

/**
 * QuestionServiceImpl
 * 
 * Implementation of the QuestionService interface.
 * Handles all question-related business logic.
 */
class QuestionServiceImpl implements QuestionService
{
    private Question $questionModel;
    
    private const QUESTION_TYPES = [
        'multiple_choice',
        'true_false', 
        'short_answer',
        'essay'
    ];

    public function __construct(?Question $questionModel = null)
    {
        $this->questionModel = $questionModel ?? new Question();
    }

    /**
     * {@inheritdoc}
     */
    public function createQuestion(array $questionData)
    {
        try {
            // Validate question data
            $validationErrors = $this->validateQuestionData($questionData);
            if (!empty($validationErrors)) {
                throw new Exception('Validation failed: ' . implode(', ', $validationErrors));
            }

            // Validate options for multiple choice questions
            if ($questionData['question_type'] === 'multiple_choice') {
                $options = $questionData['options'] ?? [];
                $correctAnswer = $questionData['correct_answer'] ?? '';
                $optionErrors = $this->validateQuestionOptions($options, $correctAnswer);
                if (!empty($optionErrors)) {
                    throw new Exception('Option validation failed: ' . implode(', ', $optionErrors));
                }
            }

            return $this->questionModel->create($questionData);
        } catch (Exception $e) {
            error_log("QuestionService::createQuestion error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function updateQuestion(int $questionId, array $questionData): bool
    {
        try {
            // Check if question exists
            if (!$this->questionExists($questionId)) {
                throw new Exception('Question not found');
            }

            // Validate question data
            $validationErrors = $this->validateQuestionData($questionData);
            if (!empty($validationErrors)) {
                throw new Exception('Validation failed: ' . implode(', ', $validationErrors));
            }

            // Validate options for multiple choice questions
            if ($questionData['question_type'] === 'multiple_choice') {
                $options = $questionData['options'] ?? [];
                $correctAnswer = $questionData['correct_answer'] ?? '';
                $optionErrors = $this->validateQuestionOptions($options, $correctAnswer);
                if (!empty($optionErrors)) {
                    throw new Exception('Option validation failed: ' . implode(', ', $optionErrors));
                }
            }

            return $this->questionModel->update($questionId, $questionData);
        } catch (Exception $e) {
            error_log("QuestionService::updateQuestion error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deleteQuestion(int $questionId): bool
    {
        try {
            // Check if question exists
            if (!$this->questionExists($questionId)) {
                throw new Exception('Question not found');
            }

            return $this->questionModel->delete($questionId);
        } catch (Exception $e) {
            error_log("QuestionService::deleteQuestion error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getQuestionById(int $questionId)
    {
        try {
            return $this->questionModel->getQuestionById($questionId);
        } catch (Exception $e) {
            error_log("QuestionService::getQuestionById error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAllQuestions(): array
    {
        try {
            return $this->questionModel->getAllQuestions() ?? [];
        } catch (Exception $e) {
            error_log("QuestionService::getAllQuestions error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getQuestionsByExam(int $examId): array
    {
        try {
            return $this->questionModel->getQuestionsByExam($examId) ?? [];
        } catch (Exception $e) {
            error_log("QuestionService::getQuestionsByExam error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getQuestionsBySubject(int $subjectId): array
    {
        try {
            return $this->questionModel->getQuestionsBySubject($subjectId) ?? [];
        } catch (Exception $e) {
            error_log("QuestionService::getQuestionsBySubject error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getQuestionsByTeacher(int $teacherId): array
    {
        try {
            return $this->questionModel->getQuestionsByTeacher($teacherId) ?? [];
        } catch (Exception $e) {
            error_log("QuestionService::getQuestionsByTeacher error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function validateQuestionData(array $questionData): array
    {
        $errors = [];

        // Validate question_text
        if (empty($questionData['question_text'])) {
            $errors[] = 'Question text is required';
        } elseif (strlen($questionData['question_text']) < 10) {
            $errors[] = 'Question text must be at least 10 characters long';
        } elseif (strlen($questionData['question_text']) > 1000) {
            $errors[] = 'Question text must not exceed 1000 characters';
        }

        // Validate question_type
        if (empty($questionData['question_type'])) {
            $errors[] = 'Question type is required';
        } elseif (!in_array($questionData['question_type'], self::QUESTION_TYPES)) {
            $errors[] = 'Invalid question type. Valid types: ' . implode(', ', self::QUESTION_TYPES);
        }

        // Validate exam_id
        if (empty($questionData['exam_id']) || !is_numeric($questionData['exam_id'])) {
            $errors[] = 'Valid exam ID is required';
        }

        // Validate subject_id
        if (empty($questionData['subject_id']) || !is_numeric($questionData['subject_id'])) {
            $errors[] = 'Valid subject ID is required';
        }

        // Validate teacher_id
        if (empty($questionData['teacher_id']) || !is_numeric($questionData['teacher_id'])) {
            $errors[] = 'Valid teacher ID is required';
        }

        // Validate points (optional, but must be positive if provided)
        if (isset($questionData['points']) && (!is_numeric($questionData['points']) || $questionData['points'] <= 0)) {
            $errors[] = 'Points must be a positive number';
        }

        // Validate correct_answer based on question type
        if (in_array($questionData['question_type'], ['multiple_choice', 'true_false', 'short_answer'])) {
            if (empty($questionData['correct_answer'])) {
                $errors[] = 'Correct answer is required for this question type';
            }
        }

        return $errors;
    }

    /**
     * {@inheritdoc}
     */
    public function questionExists(int $questionId): bool
    {
        try {
            $question = $this->getQuestionById($questionId);
            return $question !== false && $question !== null;
        } catch (Exception $e) {
            error_log("QuestionService::questionExists error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getQuestionTypes(): array
    {
        return self::QUESTION_TYPES;
    }

    /**
     * {@inheritdoc}
     */
    public function validateQuestionOptions(array $options, string $correctAnswer): array
    {
        $errors = [];

        // Must have at least 2 options
        if (count($options) < 2) {
            $errors[] = 'Multiple choice questions must have at least 2 options';
        }

        // Must not have more than 6 options
        if (count($options) > 6) {
            $errors[] = 'Multiple choice questions must not have more than 6 options';
        }

        // Each option must not be empty
        foreach ($options as $index => $option) {
            if (empty(trim($option))) {
                $errors[] = "Option " . ($index + 1) . " cannot be empty";
            }
        }

        // Correct answer must be one of the options
        if (!empty($correctAnswer) && !in_array($correctAnswer, $options)) {
            $errors[] = 'Correct answer must be one of the provided options';
        }

        // Options should be unique
        if (count($options) !== count(array_unique($options))) {
            $errors[] = 'All options must be unique';
        }

        return $errors;
    }

    /**
     * {@inheritdoc}
     */
    public function bulkCreateQuestions(int $examId, array $questionsData): array
    {
        $createdIds = [];
        $errors = [];

        try {
            foreach ($questionsData as $index => $questionData) {
                // Add exam_id to each question
                $questionData['exam_id'] = $examId;

                // Validate question data
                $validationErrors = $this->validateQuestionData($questionData);
                if (!empty($validationErrors)) {
                    $errors[] = "Question " . ($index + 1) . ": " . implode(', ', $validationErrors);
                    continue;
                }

                // Create question
                $questionId = $this->createQuestion($questionData);
                if ($questionId) {
                    $createdIds[] = $questionId;
                } else {
                    $errors[] = "Failed to create question " . ($index + 1);
                }
            }

            if (!empty($errors)) {
                error_log("QuestionService::bulkCreateQuestions errors: " . implode('; ', $errors));
            }

            return $createdIds;
        } catch (Exception $e) {
            error_log("QuestionService::bulkCreateQuestions error: " . $e->getMessage());
            return $createdIds; // Return what was successfully created
        }
    }
}