<?php

namespace App\Controller;

use App\Service\Interface\ExamResultServiceInterface;
use App\Service\Interface\AuthServiceInterface;

/**
 * ExamResultController
 * 
 * Handles exam result-related HTTP requests.
 * Responsible for CRUD operations on exam results.
 */
class ExamResultController
{
    private ExamResultServiceInterface $examResultService;
    private AuthServiceInterface $authService;

    public function __construct(ExamResultServiceInterface $examResultService, AuthServiceInterface $authService)
    {
        $this->examResultService = $examResultService;
        $this->authService = $authService;
    }

    // Placeholder methods - implement as needed
    public function index(): void { echo json_encode(['status' => 'not implemented']); }
    public function show(string $id): void { echo json_encode(['status' => 'not implemented']); }
    public function store(): void { echo json_encode(['status' => 'not implemented']); }
    public function update(string $id): void { echo json_encode(['status' => 'not implemented']); }
    public function delete(string $id): void { echo json_encode(['status' => 'not implemented']); }
    public function getByExam(string $exam_id): void { echo json_encode(['status' => 'not implemented']); }
    public function getByStudent(string $student_id): void { echo json_encode(['status' => 'not implemented']); }
}