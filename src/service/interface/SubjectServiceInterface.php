<?php

namespace App\Service\Interface;

/**
 * SubjectService Interface
 * 
 * Defines the contract for all subject business logic operations.
 * This interface ensures that all SubjectService implementations follow the same contract.
 */
interface SubjectServiceInterface
{
    /**
     * Create a new subject
     */
    public function createSubject(string $subject_code, string $subject_name, string $description, int $units, ?int $faculty_id = null): bool;

    /**
     * Update an existing subject
     */
    public function updateSubject(int $subject_id, string $subject_code, string $subject_name, string $description, int $units, ?int $faculty_id = null): bool;

    /**
     * Delete a subject
     */
    public function deleteSubject(int $subject_id): bool;

    /**
     * Get subject by ID
     */
    public function getSubjectById(int $subject_id): ?array;

    /**
     * Get subject by code
     */
    public function getSubjectByCode(string $subject_code): ?array;

    /**
     * Get all subjects
     */
    public function getAllSubjects(): array;

    /**
     * Get subjects by faculty
     */
    public function getSubjectsByFaculty(int $faculty_id): array;

    /**
     * Check if subject exists
     */
    public function subjectExists(string $subject_code): bool;

    /**
     * Validate subject data
     */
    public function validateSubjectData(array $subjectData): array;
}