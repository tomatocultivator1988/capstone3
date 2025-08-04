<?php

namespace App\Service\Interface;

/**
 * ExamResultService Interface
 * 
 * Defines the contract for all exam result business logic operations.
 * This interface ensures that all ExamResultService implementations follow the same contract.
 */
interface ExamResultServiceInterface
{
    /**
     * Create a new exam result
     */
    public function createExamResult(array $resultData): bool;

    /**
     * Update an existing exam result
     */
    public function updateExamResult(int $result_id, array $resultData): bool;

    /**
     * Delete an exam result
     */
    public function deleteExamResult(int $result_id): bool;

    /**
     * Get exam result by ID
     */
    public function getExamResultById(int $result_id): ?array;

    /**
     * Get results by exam
     */
    public function getResultsByExam(int $exam_id): array;

    /**
     * Get results by student
     */
    public function getResultsByStudent(int $student_id): array;

    /**
     * Check if student has taken exam
     */
    public function hasStudentTakenExam(int $exam_id, int $student_id): bool;

    /**
     * Validate exam result data
     */
    public function validateExamResultData(array $resultData): array;
}