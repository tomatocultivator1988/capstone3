<?php

namespace Dao\Interface;

use Model\Subject;

/**
 * Subject DAO Interface
 * 
 * Defines all database operations for Subject entities.
 * Only database access methods, no business logic.
 */
interface SubjectDAOInterface
{
    /**
     * Find subject by ID
     */
    public function findById(int $subject_id): ?Subject;

    /**
     * Find subject by code
     */
    public function findByCode(string $subject_code): ?Subject;

    /**
     * Get all subjects
     */
    public function findAll(): array;

    /**
     * Get subjects by faculty ID
     */
    public function findByFacultyId(int $faculty_id): array;

    /**
     * Create a new subject
     */
    public function create(Subject $subject): bool;

    /**
     * Update an existing subject
     */
    public function update(Subject $subject): bool;

    /**
     * Delete a subject by ID
     */
    public function deleteById(int $subject_id): bool;

    /**
     * Check if subject exists by code
     */
    public function existsByCode(string $subject_code): bool;

    /**
     * Get total count of subjects
     */
    public function getTotalCount(): int;

    /**
     * Get subjects with pagination
     */
    public function findWithPagination(int $limit, int $offset): array;

    /**
     * Get unassigned subjects (no faculty)
     */
    public function findUnassigned(): array;

    /**
     * Assign subject to faculty
     */
    public function assignToFaculty(int $subject_id, int $faculty_id): bool;

    /**
     * Unassign subject from faculty
     */
    public function unassignFromFaculty(int $subject_id): bool;
}