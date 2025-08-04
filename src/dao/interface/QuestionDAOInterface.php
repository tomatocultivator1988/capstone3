<?php

namespace Dao\Interface;

use Model\Question;

/**
 * Question DAO Interface
 * 
 * Defines all database operations for Question entities.
 * Only database access methods, no business logic.
 */
interface QuestionDAOInterface
{
    /**
     * Find question by ID
     */
    public function findById(int $question_id): ?Question;

    /**
     * Get all questions for an exam
     */
    public function findByExamId(int $exam_id): array;

    /**
     * Get questions by type
     */
    public function findByType(string $question_type): array;

    /**
     * Create a new question
     */
    public function create(Question $question): bool;

    /**
     * Update an existing question
     */
    public function update(Question $question): bool;

    /**
     * Delete a question by ID
     */
    public function deleteById(int $question_id): bool;

    /**
     * Delete all questions for an exam
     */
    public function deleteByExamId(int $exam_id): bool;

    /**
     * Get total count of questions for an exam
     */
    public function getTotalCountByExam(int $exam_id): int;

    /**
     * Get questions with pagination for an exam
     */
    public function findByExamWithPagination(int $exam_id, int $limit, int $offset): array;

    /**
     * Update question order
     */
    public function updateOrderNumber(int $question_id, int $order_number): bool;

    /**
     * Get questions ordered by order_number
     */
    public function findByExamIdOrdered(int $exam_id): array;

    /**
     * Get total points for an exam
     */
    public function getTotalPointsByExam(int $exam_id): int;

    /**
     * Bulk create questions
     */
    public function createBulk(array $questions): bool;

    /**
     * Get next order number for exam
     */
    public function getNextOrderNumber(int $exam_id): int;

    /**
     * Reorder questions for exam
     */
    public function reorderQuestions(int $exam_id, array $questionIds): bool;
}