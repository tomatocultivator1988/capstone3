<?php

namespace App\Service\Interface;

/**
 * ExamService Interface
 * 
 * Defines the contract for all exam business logic operations.
 * This interface ensures that all ExamService implementations follow the same contract.
 */
interface ExamServiceInterface
{
    /**
     * Create a new exam
     */
    public function createExam(array $examData): bool;

    /**
     * Update an existing exam
     */
    public function updateExam(int $exam_id, array $examData): bool;

    /**
     * Delete an exam
     */
    public function deleteExam(int $exam_id): bool;

    /**
     * Get exam by ID
     */
    public function getExamById(int $exam_id): ?array;

    /**
     * Get all exams
     */
    public function getAllExams(): array;

    /**
     * Get exams by faculty
     */
    public function getExamsByFaculty(int $faculty_id): array;

    /**
     * Get exams for student
     */
    public function getExamsForStudent(int $year_level, string $section): array;

    /**
     * Get exams by subject
     */
    public function getExamsBySubject(int $subject_id): array;

    /**
     * Get active exams
     */
    public function getActiveExams(): array;

    /**
     * Update exam status
     */
    public function updateExamStatus(int $exam_id, string $status): bool;

    /**
     * Validate exam data
     */
    public function validateExamData(array $examData): array;
}