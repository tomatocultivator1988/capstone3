<?php

namespace App\DAO\Interface;

use App\Model\Question;

/**
 * QuestionDAO Interface
 * 
 * Defines the contract for all question data access operations.
 * This interface ensures that all QuestionDAO implementations follow the same contract.
 */
interface QuestionDAOInterface
{
    /**
     * Find question by ID
     */
    public function findById(int $question_id): ?Question;

    /**
     * Get questions by exam
     */
    public function getByExam(int $exam_id): array;

    /**
     * Get question count by exam
     */
    public function getCountByExam(int $exam_id): int;

    /**
     * Get total points by exam
     */
    public function getTotalPointsByExam(int $exam_id): int;

    /**
     * Create a new question
     */
    public function create(Question $question): ?int;

    /**
     * Update an existing question
     */
    public function update(Question $question): bool;

    /**
     * Delete a question by ID
     */
    public function delete(int $question_id): bool;

    /**
     * Reorder questions
     */
    public function reorder(int $exam_id, array $questionOrder): bool;
}