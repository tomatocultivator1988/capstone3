<?php

namespace App\Controller;

use App\Service\Interface\SubjectServiceInterface;
use App\Service\Interface\AuthServiceInterface;

/**
 * SubjectController
 * 
 * Handles subject-related HTTP requests.
 * Responsible for CRUD operations on subjects.
 */
class SubjectController
{
    private SubjectServiceInterface $subjectService;
    private AuthServiceInterface $authService;

    public function __construct(SubjectServiceInterface $subjectService, AuthServiceInterface $authService)
    {
        $this->subjectService = $subjectService;
        $this->authService = $authService;
    }

    // Placeholder methods - implement as needed
    public function index(): void { echo json_encode(['status' => 'not implemented']); }
    public function show(string $id): void { echo json_encode(['status' => 'not implemented']); }
    public function store(): void { echo json_encode(['status' => 'not implemented']); }
    public function update(string $id): void { echo json_encode(['status' => 'not implemented']); }
    public function delete(string $id): void { echo json_encode(['status' => 'not implemented']); }
    public function getByFaculty(string $faculty_id): void { echo json_encode(['status' => 'not implemented']); }
}