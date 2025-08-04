<?php

namespace App\Services;

/**
 * QuestionService Interface
 * 
 * Defines the contract for question-related business logic operations.
 */
interface QuestionService
{
    /**
     * Create a new question
     *
     * @param array $questionData The question data
     * @return int|false Question ID if successful, false otherwise
     */
    public function createQuestion(array $questionData);

    /**
     * Update an existing question
     *
     * @param int $questionId The question ID
     * @param array $questionData The updated question data
     * @return bool True if successful, false otherwise
     */
    public function updateQuestion(int $questionId, array $questionData): bool;

    /**
     * Delete a question
     *
     * @param int $questionId The question ID
     * @return bool True if successful, false otherwise
     */
    public function deleteQuestion(int $questionId): bool;

    /**
     * Get question by ID
     *
     * @param int $questionId The question ID
     * @return array|false Question data if found, false otherwise
     */
    public function getQuestionById(int $questionId);

    /**
     * Get all questions
     *
     * @return array Array of questions
     */
    public function getAllQuestions(): array;

    /**
     * Get questions by exam
     *
     * @param int $examId The exam ID
     * @return array Array of questions for the exam
     */
    public function getQuestionsByExam(int $examId): array;

    /**
     * Get questions by subject
     *
     * @param int $subjectId The subject ID
     * @return array Array of questions for the subject
     */
    public function getQuestionsBySubject(int $subjectId): array;

    /**
     * Get questions by teacher
     *
     * @param int $teacherId The teacher ID
     * @return array Array of questions created by the teacher
     */
    public function getQuestionsByTeacher(int $teacherId): array;

    /**
     * Validate question data
     *
     * @param array $questionData The question data to validate
     * @return array Array of validation errors (empty if valid)
     */
    public function validateQuestionData(array $questionData): array;

    /**
     * Check if question exists
     *
     * @param int $questionId The question ID
     * @return bool True if question exists, false otherwise
     */
    public function questionExists(int $questionId): bool;

    /**
     * Get question types
     *
     * @return array Array of valid question types
     */
    public function getQuestionTypes(): array;

    /**
     * Validate question options for multiple choice questions
     *
     * @param array $options The question options
     * @param string $correctAnswer The correct answer
     * @return array Array of validation errors (empty if valid)
     */
    public function validateQuestionOptions(array $options, string $correctAnswer): array;

    /**
     * Bulk create questions for an exam
     *
     * @param int $examId The exam ID
     * @param array $questionsData Array of question data
     * @return array Array of created question IDs
     */
    public function bulkCreateQuestions(int $examId, array $questionsData): array;
}