<?php

namespace App\Service\Interface;

/**
 * QuestionService Interface
 * 
 * Defines the contract for all question business logic operations.
 * This interface ensures that all QuestionService implementations follow the same contract.
 */
interface QuestionServiceInterface
{
    /**
     * Create a new question
     */
    public function createQuestion(array $questionData): bool;

    /**
     * Update an existing question
     */
    public function updateQuestion(int $question_id, array $questionData): bool;

    /**
     * Delete a question
     */
    public function deleteQuestion(int $question_id): bool;

    /**
     * Get question by ID
     */
    public function getQuestionById(int $question_id): ?array;

    /**
     * Get questions by exam
     */
    public function getQuestionsByExam(int $exam_id): array;

    /**
     * Reorder questions
     */
    public function reorderQuestions(int $exam_id, array $questionOrder): bool;

    /**
     * Validate question data
     */
    public function validateQuestionData(array $questionData): array;
}