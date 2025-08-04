<?php

namespace App\DAO\Interface;

use App\Model\Subject;

/**
 * SubjectDAO Interface
 * 
 * Defines the contract for all subject data access operations.
 * This interface ensures that all SubjectDAO implementations follow the same contract.
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
    public function getAll(): array;

    /**
     * Get subjects by faculty
     */
    public function getByFaculty(int $faculty_id): array;

    /**
     * Create a new subject
     */
    public function create(Subject $subject): ?int;

    /**
     * Update an existing subject
     */
    public function update(Subject $subject): bool;

    /**
     * Delete a subject by ID
     */
    public function delete(int $subject_id): bool;

    /**
     * Check if subject exists by code
     */
    public function existsByCode(string $subject_code): bool;
}