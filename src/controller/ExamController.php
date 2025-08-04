<?php

namespace App\Controller;

use App\Service\Interface\ExamServiceInterface;
use App\Service\Interface\AuthServiceInterface;

/**
 * ExamController
 * 
 * Handles exam-related HTTP requests.
 * Responsible for CRUD operations on exams.
 */
class ExamController
{
    private ExamServiceInterface $examService;
    private AuthServiceInterface $authService;

    public function __construct(ExamServiceInterface $examService, AuthServiceInterface $authService)
    {
        $this->examService = $examService;
        $this->authService = $authService;
    }

    // Placeholder methods - implement as needed
    public function index(): void { echo json_encode(['status' => 'not implemented']); }
    public function show(string $id): void { echo json_encode(['status' => 'not implemented']); }
    public function store(): void { echo json_encode(['status' => 'not implemented']); }
    public function update(string $id): void { echo json_encode(['status' => 'not implemented']); }
    public function delete(string $id): void { echo json_encode(['status' => 'not implemented']); }
    public function getByFaculty(string $faculty_id): void { echo json_encode(['status' => 'not implemented']); }
    public function getForStudent(string $year_level, string $section): void { echo json_encode(['status' => 'not implemented']); }
    public function getBySubject(string $subject_id): void { echo json_encode(['status' => 'not implemented']); }
    public function getActive(): void { echo json_encode(['status' => 'not implemented']); }
    public function updateStatus(string $id): void { echo json_encode(['status' => 'not implemented']); }
}