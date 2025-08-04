<?php

namespace App\Services;

use App\Models\Subject;
use App\Repositories\SubjectRepository;
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
    private SubjectRepository $subjectRepository;

    public function __construct(?SubjectRepository $subjectRepository = null)
    {
        $this->subjectRepository = $subjectRepository ?? new SubjectRepository();
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

            // Create subject model
            $subject = new Subject();
            $subject->setSubjectCode($subjectData['subject_code'] ?? '');
            $subject->setSubjectName($subjectData['subject_name']);
            $subject->setDescription($subjectData['description'] ?? '');
            $subject->setUnits($subjectData['units'] ?? 0);
            $subject->setFacultyId($subjectData['faculty_id'] ?? null);

            return $this->subjectRepository->create($subject);
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

            // Get existing subject and update it
            $subject = $this->subjectRepository->findById($subjectId);
            if (!$subject) {
                throw new Exception('Subject not found');
            }

            $subject->setSubjectCode($subjectData['subject_code'] ?? $subject->getSubjectCode());
            $subject->setSubjectName($subjectData['subject_name']);
            $subject->setDescription($subjectData['description'] ?? $subject->getDescription());
            $subject->setUnits($subjectData['units'] ?? $subject->getUnits());
            $subject->setFacultyId($subjectData['faculty_id'] ?? $subject->getFacultyId());

            return $this->subjectRepository->update($subject);
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

            return $this->subjectRepository->delete($subjectId);
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
            $subject = $this->subjectRepository->findById($subjectId);
            return $subject ? $subject->toArray() : false;
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
            $subjects = $this->subjectRepository->getAll();
            return array_map(fn($subject) => $subject->toArray(), $subjects);
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
            // This method should be updated to use faculty instead of teacher
            // For now, return empty array as this needs to be rethought
            return [];
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
            // Check if subject exists by name (excluding current subject if updating)
            $existingSubject = $this->subjectRepository->findByCode($subjectName);
            if (!$existingSubject) {
                return false;
            }
            
            // If excluding an ID (for updates), check if it's the same subject
            if ($excludeId && $existingSubject->getSubjectId() === $excludeId) {
                return false;
            }
            
            return true;
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
            return $this->subjectRepository->assignFaculty($subjectId, $facultyId);
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
            $subjects = $this->subjectRepository->getByFaculty($facultyId);
            return array_map(fn($subject) => $subject->toArray(), $subjects);
        } catch (Exception $e) {
            error_log("SubjectService::getSubjectsByFaculty error: " . $e->getMessage());
            return [];
        }
    }
}