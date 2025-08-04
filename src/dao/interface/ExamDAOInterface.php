<?php

namespace App\DAO\Interface;

use App\Model\Exam;

/**
 * ExamDAO Interface
 * 
 * Defines the contract for all exam data access operations.
 * This interface ensures that all ExamDAO implementations follow the same contract.
 */
interface ExamDAOInterface
{
    /**
     * Find exam by ID
     */
    public function findById(int $exam_id): ?Exam;

    /**
     * Get all exams
     */
    public function getAll(): array;

    /**
     * Get exams by faculty
     */
    public function getByFaculty(int $faculty_id): array;

    /**
     * Get exams for student (by year level and section)
     */
    public function getForStudent(int $year_level, string $section): array;

    /**
     * Get exams by subject
     */
    public function getBySubject(int $subject_id): array;

    /**
     * Get active exams
     */
    public function getActive(): array;

    /**
     * Create a new exam
     */
    public function create(Exam $exam): ?int;

    /**
     * Update an existing exam
     */
    public function update(Exam $exam): bool;

    /**
     * Delete an exam by ID
     */
    public function delete(int $exam_id): bool;

    /**
     * Update exam status
     */
    public function updateStatus(int $exam_id, string $status): bool;
}