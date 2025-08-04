<?php

namespace Dao\Interface;

use Model\ExamResult;

/**
 * ExamResult DAO Interface
 * 
 * Defines all database operations for ExamResult entities.
 * Only database access methods, no business logic.
 */
interface ExamResultDAOInterface
{
    /**
     * Find exam result by ID
     */
    public function findById(int $result_id): ?ExamResult;

    /**
     * Find exam result by exam and student
     */
    public function findByExamAndStudent(int $exam_id, int $student_id): ?ExamResult;

    /**
     * Get all results for an exam
     */
    public function findByExamId(int $exam_id): array;

    /**
     * Get all results for a student
     */
    public function findByStudentId(int $student_id): array;

    /**
     * Get results by status
     */
    public function findByStatus(string $status): array;

    /**
     * Create a new exam result
     */
    public function create(ExamResult $result): bool;

    /**
     * Update an existing exam result
     */
    public function update(ExamResult $result): bool;

    /**
     * Delete an exam result by ID
     */
    public function deleteById(int $result_id): bool;

    /**
     * Delete all results for an exam
     */
    public function deleteByExamId(int $exam_id): bool;

    /**
     * Get total count of results
     */
    public function getTotalCount(): int;

    /**
     * Get results with pagination
     */
    public function findWithPagination(int $limit, int $offset): array;

    /**
     * Check if student has submitted exam
     */
    public function hasStudentSubmitted(int $exam_id, int $student_id): bool;

    /**
     * Get exam statistics
     */
    public function getExamStatistics(int $exam_id): array;

    /**
     * Get student statistics
     */
    public function getStudentStatistics(int $student_id): array;

    /**
     * Get results with exam and student details
     */
    public function findAllWithDetails(): array;

    /**
     * Get result with exam and student details
     */
    public function findByIdWithDetails(int $result_id): ?array;

    /**
     * Update result score and percentage
     */
    public function updateScore(int $result_id, int $score, float $percentage): bool;

    /**
     * Get top performers for an exam
     */
    public function getTopPerformers(int $exam_id, int $limit = 10): array;

    /**
     * Get average score for an exam
     */
    public function getAverageScore(int $exam_id): float;
}