<?php

namespace App\Services;

/**
 * SubjectService Interface
 * 
 * Defines the contract for subject-related business logic operations.
 */
interface SubjectService
{
    /**
     * Create a new subject
     *
     * @param array $subjectData The subject data
     * @return int|false Subject ID if successful, false otherwise
     */
    public function createSubject(array $subjectData);

    /**
     * Update an existing subject
     *
     * @param int $subjectId The subject ID
     * @param array $subjectData The updated subject data
     * @return bool True if successful, false otherwise
     */
    public function updateSubject(int $subjectId, array $subjectData): bool;

    /**
     * Delete a subject
     *
     * @param int $subjectId The subject ID
     * @return bool True if successful, false otherwise
     */
    public function deleteSubject(int $subjectId): bool;

    /**
     * Get subject by ID
     *
     * @param int $subjectId The subject ID
     * @return array|false Subject data if found, false otherwise
     */
    public function getSubjectById(int $subjectId);

    /**
     * Get all subjects
     *
     * @return array Array of subjects
     */
    public function getAllSubjects(): array;

    /**
     * Get subjects by teacher
     *
     * @param int $teacherId The teacher ID
     * @return array Array of subjects assigned to the teacher
     */
    public function getSubjectsByTeacher(int $teacherId): array;

    /**
     * Validate subject data
     *
     * @param array $subjectData The subject data to validate
     * @return array Array of validation errors (empty if valid)
     */
    public function validateSubjectData(array $subjectData): array;

    /**
     * Check if subject exists
     *
     * @param int $subjectId The subject ID
     * @return bool True if subject exists, false otherwise
     */
    public function subjectExists(int $subjectId): bool;

    /**
     * Check if subject name already exists
     *
     * @param string $subjectName The subject name
     * @param int|null $excludeId Subject ID to exclude from check (for updates)
     * @return bool True if name exists, false otherwise
     */
    public function subjectNameExists(string $subjectName, ?int $excludeId = null): bool;

    /**
     * Assign faculty to subject
     *
     * @param int $subjectId The subject ID
     * @param int $facultyId The faculty ID
     * @return bool True if successful, false otherwise
     */
    public function assignFacultyToSubject(int $subjectId, int $facultyId): bool;

    /**
     * Get subjects by faculty
     *
     * @param int $facultyId The faculty ID
     * @return array Array of subjects assigned to the faculty
     */
    public function getSubjectsByFaculty(int $facultyId): array;
}