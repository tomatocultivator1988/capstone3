<?php

namespace App\DAO\Interface;

use App\Model\ExamResult;

/**
 * ExamResultDAO Interface
 * 
 * Defines the contract for all exam result data access operations.
 * This interface ensures that all ExamResultDAO implementations follow the same contract.
 */
interface ExamResultDAOInterface
{
    /**
     * Find exam result by ID
     */
    public function findById(int $result_id): ?ExamResult;

    /**
     * Get results by exam
     */
    public function getByExam(int $exam_id): array;

    /**
     * Get results by student
     */
    public function getByStudent(int $student_id): array;

    /**
     * Check if student has taken exam
     */
    public function hasStudentTakenExam(int $exam_id, int $student_id): bool;

    /**
     * Create a new exam result
     */
    public function create(ExamResult $result): ?int;

    /**
     * Update an existing exam result
     */
    public function update(ExamResult $result): bool;

    /**
     * Delete an exam result by ID
     */
    public function delete(int $result_id): bool;
}