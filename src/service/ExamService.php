<?php

namespace App\Services;

/**
 * ExamService Interface
 * 
 * Defines the contract for exam-related business logic operations.
 */
interface ExamService
{
    /**
     * Create a new exam
     *
     * @param array $examData The exam data
     * @return int|false Exam ID if successful, false otherwise
     */
    public function createExam(array $examData);

    /**
     * Update an existing exam
     *
     * @param int $examId The exam ID
     * @param array $examData The updated exam data
     * @return bool True if successful, false otherwise
     */
    public function updateExam(int $examId, array $examData): bool;

    /**
     * Delete an exam
     *
     * @param int $examId The exam ID
     * @return bool True if successful, false otherwise
     */
    public function deleteExam(int $examId): bool;

    /**
     * Get exam by ID
     *
     * @param int $examId The exam ID
     * @return array|false Exam data if found, false otherwise
     */
    public function getExamById(int $examId);

    /**
     * Get all exams
     *
     * @return array Array of exams
     */
    public function getAllExams(): array;

    /**
     * Get exams by subject
     *
     * @param int $subjectId The subject ID
     * @return array Array of exams for the subject
     */
    public function getExamsBySubject(int $subjectId): array;

    /**
     * Get exams by teacher
     *
     * @param int $teacherId The teacher ID
     * @return array Array of exams created by the teacher
     */
    public function getExamsByTeacher(int $teacherId): array;

    /**
     * Get active exams
     *
     * @return array Array of active exams
     */
    public function getActiveExams(): array;

    /**
     * Validate exam data
     *
     * @param array $examData The exam data to validate
     * @return array Array of validation errors (empty if valid)
     */
    public function validateExamData(array $examData): array;

    /**
     * Check if exam exists
     *
     * @param int $examId The exam ID
     * @return bool True if exam exists, false otherwise
     */
    public function examExists(int $examId): bool;

    /**
     * Activate/Deactivate exam
     *
     * @param int $examId The exam ID
     * @param bool $active Whether to activate or deactivate
     * @return bool True if successful, false otherwise
     */
    public function setExamStatus(int $examId, bool $active): bool;
}