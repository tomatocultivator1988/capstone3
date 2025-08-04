<?php

namespace App\Service\Impl;

use App\Service\Interface\SubjectServiceInterface;
use App\DAO\Interface\SubjectDAOInterface;
use App\Model\Subject;
use Exception;

/**
 * SubjectService Implementation
 * 
 * Implementation of the SubjectService interface.
 * Handles all subject-related business logic and coordinates with the Subject DAO.
 */
class SubjectServiceImpl implements SubjectServiceInterface
{
    private SubjectDAOInterface $subjectDAO;

    public function __construct(SubjectDAOInterface $subjectDAO)
    {
        $this->subjectDAO = $subjectDAO;
    }

    /**
     * {@inheritdoc}
     */
    public function createSubject(string $subject_code, string $subject_name, string $description, int $units, ?int $faculty_id = null): bool
    {
        try {
            // Validate input data
            $subjectData = [
                'subject_code' => $subject_code,
                'subject_name' => $subject_name,
                'description' => $description,
                'units' => $units,
                'faculty_id' => $faculty_id
            ];

            $validationErrors = $this->validateSubjectData($subjectData);
            if (!empty($validationErrors)) {
                throw new Exception('Validation failed: ' . implode(', ', $validationErrors));
            }

            // Check if subject already exists
            if ($this->subjectExists($subject_code)) {
                throw new Exception('Subject with this code already exists');
            }

            // Create subject model
            $subject = new Subject();
            $subject->setSubjectCode($subject_code);
            $subject->setSubjectName($subject_name);
            $subject->setDescription($description);
            $subject->setUnits($units);
            $subject->setFacultyId($faculty_id);

            // Create subject
            return $this->subjectDAO->create($subject) !== null;
        } catch (Exception $e) {
            error_log("SubjectService::createSubject error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function updateSubject(int $subject_id, string $subject_code, string $subject_name, string $description, int $units, ?int $faculty_id = null): bool
    {
        try {
            // Validate input data
            $subjectData = [
                'subject_code' => $subject_code,
                'subject_name' => $subject_name,
                'description' => $description,
                'units' => $units,
                'faculty_id' => $faculty_id
            ];

            $validationErrors = $this->validateSubjectData($subjectData);
            if (!empty($validationErrors)) {
                throw new Exception('Validation failed: ' . implode(', ', $validationErrors));
            }

            // Check if the subject exists
            $existingSubject = $this->getSubjectById($subject_id);
            if (!$existingSubject) {
                throw new Exception('Subject not found');
            }

            // Check if subject_code is being changed and if new subject_code already exists
            if ($existingSubject['subject_code'] !== $subject_code && $this->subjectExists($subject_code)) {
                throw new Exception('Another subject with this code already exists');
            }

            // Update subject model
            $subject = new Subject();
            $subject->setSubjectId($subject_id);
            $subject->setSubjectCode($subject_code);
            $subject->setSubjectName($subject_name);
            $subject->setDescription($description);
            $subject->setUnits($units);
            $subject->setFacultyId($faculty_id);

            // Update subject
            return $this->subjectDAO->update($subject);
        } catch (Exception $e) {
            error_log("SubjectService::updateSubject error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deleteSubject(int $subject_id): bool
    {
        try {
            // Check if the subject exists
            $existingSubject = $this->getSubjectById($subject_id);
            if (!$existingSubject) {
                throw new Exception('Subject not found');
            }

            // Delete subject
            return $this->subjectDAO->delete($subject_id);
        } catch (Exception $e) {
            error_log("SubjectService::deleteSubject error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getSubjectById(int $subject_id): ?array
    {
        try {
            $subject = $this->subjectDAO->findById($subject_id);
            return $subject ? $subject->toArray() : null;
        } catch (Exception $e) {
            error_log("SubjectService::getSubjectById error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getSubjectByCode(string $subject_code): ?array
    {
        try {
            $subject = $this->subjectDAO->findByCode($subject_code);
            return $subject ? $subject->toArray() : null;
        } catch (Exception $e) {
            error_log("SubjectService::getSubjectByCode error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAllSubjects(): array
    {
        try {
            $subjects = $this->subjectDAO->getAll();
            return array_map(fn($subject) => $subject->toArray(), $subjects);
        } catch (Exception $e) {
            error_log("SubjectService::getAllSubjects error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getSubjectsByFaculty(int $faculty_id): array
    {
        try {
            $subjects = $this->subjectDAO->getByFaculty($faculty_id);
            return array_map(fn($subject) => $subject->toArray(), $subjects);
        } catch (Exception $e) {
            error_log("SubjectService::getSubjectsByFaculty error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function subjectExists(string $subject_code): bool
    {
        try {
            return $this->subjectDAO->existsByCode($subject_code);
        } catch (Exception $e) {
            error_log("SubjectService::subjectExists error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function validateSubjectData(array $subjectData): array
    {
        $errors = [];

        // Validate subject_code
        if (empty($subjectData['subject_code'])) {
            $errors[] = 'Subject code is required';
        } elseif (strlen($subjectData['subject_code']) < 2) {
            $errors[] = 'Subject code must be at least 2 characters';
        }

        // Validate subject_name
        if (empty($subjectData['subject_name'])) {
            $errors[] = 'Subject name is required';
        } elseif (strlen($subjectData['subject_name']) < 2) {
            $errors[] = 'Subject name must be at least 2 characters';
        }

        // Validate description
        if (empty($subjectData['description'])) {
            $errors[] = 'Description is required';
        }

        // Validate units
        if ($subjectData['units'] < 1) {
            $errors[] = 'Units must be at least 1';
        }

        return $errors;
    }
}