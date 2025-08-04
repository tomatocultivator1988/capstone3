<?php

namespace Dao\Interface;

use Model\Exam;

/**
 * Exam DAO Interface
 * 
 * Defines all database operations for Exam entities.
 * Only database access methods, no business logic.
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
    public function findAll(): array;

    /**
     * Get exams by subject ID
     */
    public function findBySubjectId(int $subject_id): array;

    /**
     * Get exams by creator (faculty) ID
     */
    public function findByCreatorId(int $created_by): array;

    /**
     * Get exams by status
     */
    public function findByStatus(string $status): array;

    /**
     * Create a new exam
     */
    public function create(Exam $exam): bool;

    /**
     * Update an existing exam
     */
    public function update(Exam $exam): bool;

    /**
     * Delete an exam by ID
     */
    public function deleteById(int $exam_id): bool;

    /**
     * Get total count of exams
     */
    public function getTotalCount(): int;

    /**
     * Get exams with pagination
     */
    public function findWithPagination(int $limit, int $offset): array;

    /**
     * Get published exams for students
     */
    public function findPublishedExams(): array;

    /**
     * Get draft exams by creator
     */
    public function findDraftsByCreator(int $created_by): array;

    /**
     * Update exam status
     */
    public function updateStatus(int $exam_id, string $status): bool;

    /**
     * Get exams scheduled for specific date
     */
    public function findByDate(string $exam_date): array;

    /**
     * Get exams with full details (joined with subject and creator)
     */
    public function findAllWithDetails(): array;

    /**
     * Get exam with subject and creator details
     */
    public function findByIdWithDetails(int $exam_id): ?array;
}