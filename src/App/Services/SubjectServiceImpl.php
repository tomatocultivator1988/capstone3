<?php

namespace App\Services;

use App\Models\Subject;
use App\Services\SubjectService;
use Exception;

/**
 * SubjectServiceImpl
 * 
 * Implementation of the SubjectService interface.
 * Handles all subject-related business logic.
 */
class SubjectServiceImpl implements SubjectService
{
    private Subject $subjectModel;

    public function __construct(?Subject $subjectModel = null)
    {
        $this->subjectModel = $subjectModel ?? new Subject();
    }

    /**
     * {@inheritdoc}
     */
    public function createSubject(array $subjectData)
    {
        try {
            // Validate subject data
            $validationErrors = $this->validateSubjectData($subjectData);
            if (!empty($validationErrors)) {
                throw new Exception('Validation failed: ' . implode(', ', $validationErrors));
            }

            // Check if subject name already exists
            if ($this->subjectNameExists($subjectData['subject_name'])) {
                throw new Exception('Subject name already exists');
            }

            return $this->subjectModel->create($subjectData);
        } catch (Exception $e) {
            error_log("SubjectService::createSubject error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function updateSubject(int $subjectId, array $subjectData): bool
    {
        try {
            // Check if subject exists
            if (!$this->subjectExists($subjectId)) {
                throw new Exception('Subject not found');
            }

            // Validate subject data
            $validationErrors = $this->validateSubjectData($subjectData);
            if (!empty($validationErrors)) {
                throw new Exception('Validation failed: ' . implode(', ', $validationErrors));
            }

            // Check if subject name already exists (excluding current subject)
            if ($this->subjectNameExists($subjectData['subject_name'], $subjectId)) {
                throw new Exception('Subject name already exists');
            }

            return $this->subjectModel->update($subjectId, $subjectData);
        } catch (Exception $e) {
            error_log("SubjectService::updateSubject error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deleteSubject(int $subjectId): bool
    {
        try {
            // Check if subject exists
            if (!$this->subjectExists($subjectId)) {
                throw new Exception('Subject not found');
            }

            return $this->subjectModel->delete($subjectId);
        } catch (Exception $e) {
            error_log("SubjectService::deleteSubject error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getSubjectById(int $subjectId)
    {
        try {
            return $this->subjectModel->getSubjectById($subjectId);
        } catch (Exception $e) {
            error_log("SubjectService::getSubjectById error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAllSubjects(): array
    {
        try {
            return $this->subjectModel->getAllSubjects() ?? [];
        } catch (Exception $e) {
            error_log("SubjectService::getAllSubjects error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getSubjectsByTeacher(int $teacherId): array
    {
        try {
            return $this->subjectModel->getSubjectsByTeacher($teacherId) ?? [];
        } catch (Exception $e) {
            error_log("SubjectService::getSubjectsByTeacher error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function validateSubjectData(array $subjectData): array
    {
        $errors = [];

        // Validate subject_name
        if (empty($subjectData['subject_name'])) {
            $errors[] = 'Subject name is required';
        } elseif (strlen($subjectData['subject_name']) < 2) {
            $errors[] = 'Subject name must be at least 2 characters long';
        } elseif (strlen($subjectData['subject_name']) > 100) {
            $errors[] = 'Subject name must not exceed 100 characters';
        }

        // Validate description (optional)
        if (isset($subjectData['description']) && strlen($subjectData['description']) > 500) {
            $errors[] = 'Description must not exceed 500 characters';
        }

        return $errors;
    }

    /**
     * {@inheritdoc}
     */
    public function subjectExists(int $subjectId): bool
    {
        try {
            $subject = $this->getSubjectById($subjectId);
            return $subject !== false && $subject !== null;
        } catch (Exception $e) {
            error_log("SubjectService::subjectExists error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function subjectNameExists(string $subjectName, ?int $excludeId = null): bool
    {
        try {
            return $this->subjectModel->subjectNameExists($subjectName, $excludeId);
        } catch (Exception $e) {
            error_log("SubjectService::subjectNameExists error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function assignFacultyToSubject(int $subjectId, int $facultyId): bool
    {
        try {
            return $this->subjectModel->assignFaculty($subjectId, $facultyId);
        } catch (Exception $e) {
            error_log("SubjectService::assignFacultyToSubject error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getSubjectsByFaculty(int $facultyId): array
    {
        try {
            return $this->subjectModel->getSubjectsByFaculty($facultyId) ?? [];
        } catch (Exception $e) {
            error_log("SubjectService::getSubjectsByFaculty error: " . $e->getMessage());
            return [];
        }
    }
}