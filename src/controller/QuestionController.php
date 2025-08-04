<?php

namespace App\Controller;

use App\Service\Interface\QuestionServiceInterface;
use App\Service\Interface\AuthServiceInterface;

/**
 * QuestionController
 * 
 * Handles question-related HTTP requests.
 * Responsible for CRUD operations on questions.
 */
class QuestionController
{
    private QuestionServiceInterface $questionService;
    private AuthServiceInterface $authService;

    public function __construct(QuestionServiceInterface $questionService, AuthServiceInterface $authService)
    {
        $this->questionService = $questionService;
        $this->authService = $authService;
    }

    // Placeholder methods - implement as needed
    public function index(): void { echo json_encode(['status' => 'not implemented']); }
    public function show(string $id): void { echo json_encode(['status' => 'not implemented']); }
    public function store(): void { echo json_encode(['status' => 'not implemented']); }
    public function update(string $id): void { echo json_encode(['status' => 'not implemented']); }
    public function delete(string $id): void { echo json_encode(['status' => 'not implemented']); }
    public function getByExam(string $exam_id): void { echo json_encode(['status' => 'not implemented']); }
    public function reorder(string $exam_id): void { echo json_encode(['status' => 'not implemented']); }
}