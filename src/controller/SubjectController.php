<?php

namespace App\Controllers;

use App\Services\SubjectService;
use App\Services\AuthService;
use App\Services\ServiceContainer;
use Exception;

class SubjectController
{
    private SubjectService $subjectService;
    private AuthService $authService;

    public function __construct(?SubjectService $subjectService = null, ?AuthService $authService = null)
    {
        $container = ServiceContainer::getInstance();
        $this->subjectService = $subjectService ?? $container->get(SubjectService::class);
        $this->authService = $authService ?? $container->get(AuthService::class);
    }

    /**
     * Get all subjects
     */
    public function index()
    {
        header('Content-Type: application/json');
        $this->authService->requireAuth();

        try {
            $subjects = $this->subjectService->getAllSubjects();
            
            echo json_encode([
                'status' => 'success',
                'data' => $subjects
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to fetch subjects'
            ]);
        }
    }

    /**
     * Get subject by ID
     */
    public function show()
    {
        header('Content-Type: application/json');
        $this->authService->requireAuth();

        $subject_id = $_GET['id'] ?? null;
        
        if (!$subject_id) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Subject ID is required'
            ]);
            return;
        }

        try {
            $subject = $this->subjectService->getSubjectById($subject_id);
            
            if (!$subject) {
                http_response_code(404);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Subject not found'
                ]);
                return;
            }

            echo json_encode([
                'status' => 'success',
                'data' => $subject
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to fetch subject'
            ]);
        }
    }

    /**
     * Create new subject (Admin/Faculty only)
     */
    public function store()
    {
        header('Content-Type: application/json');
        $this->authService->requireRole(['admin', 'faculty']);

        $input = json_decode(file_get_contents('php://input'), true);
        
        $required_fields = ['subject_name', 'subject_code'];
        foreach ($required_fields as $field) {
            if (empty($input[$field])) {
                http_response_code(400);
                echo json_encode([
                    'status' => 'error',
                    'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required'
                ]);
                return;
            }
        }

        try {
            $subject_id = $this->subjectService->createSubject($input);
            
            if ($subject_id) {
                http_response_code(201);
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Subject created successfully',
                    'data' => ['subject_id' => $subject_id]
                ]);
            } else {
                http_response_code(400);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Failed to create subject'
                ]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to create subject'
            ]);
        }
    }

    /**
     * Update subject (Admin/Faculty only)
     */
    public function update()
    {
        header('Content-Type: application/json');
        $this->authService->requireRole(['admin', 'faculty']);

        $subject_id = $_GET['id'] ?? null;
        if (!$subject_id) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Subject ID is required'
            ]);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        try {
            $success = $this->subjectService->updateSubject($subject_id, $input);
            
            if ($success) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Subject updated successfully'
                ]);
            } else {
                http_response_code(400);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Failed to update subject'
                ]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to update subject'
            ]);
        }
    }

    /**
     * Delete subject (Admin only)
     */
    public function delete()
    {
        header('Content-Type: application/json');
        $this->authService->requireRole('admin');

        $subject_id = $_GET['id'] ?? null;
        if (!$subject_id) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Subject ID is required'
            ]);
            return;
        }

        try {
            $success = $this->subjectService->deleteSubject($subject_id);
            
            if ($success) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Subject deleted successfully'
                ]);
            } else {
                http_response_code(400);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Failed to delete subject'
                ]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to delete subject'
            ]);
        }
    }

    /**
     * TDD: Assign faculty to subject
     */
    public function assignFaculty()
    {
        header('Content-Type: application/json');
        $this->authService->requireRole('admin');

        $subjectId = $_POST['subject_id'] ?? null;
        $facultyId = $_POST['faculty_id'] ?? null;

        if (!$subjectId || !$facultyId) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Subject ID and Faculty ID are required.'
            ]);
            return;
        }

        try {
            $result = $this->subjectService->assignFacultyToSubject($subjectId, $facultyId);

            if ($result) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Faculty assigned to subject successfully.'
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Failed to assign faculty to subject.'
                ]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'An error occurred while assigning faculty.'
            ]);
        }
    }

    /**
     * TDD: Get subjects by faculty
     */
    public function getByFaculty()
    {
        header('Content-Type: application/json');
        $this->authService->requireAuth();

        $facultyId = $_GET['faculty_id'] ?? $_SESSION['user_id'];

        try {
            $subjects = $this->subjectService->getSubjectsByFaculty($facultyId);
            
            echo json_encode([
                'status' => 'success',
                'data' => $subjects
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to retrieve subjects by faculty.'
            ]);
        }
    }
}