<?php

use PHPUnit\Framework\TestCase;
use App\Models\Exam;

/**
 * TDD Step 75: Exam model tests
 * RED PHASE: These tests will fail (no enhanced Exam model exists)
 */
class ExamModelTest extends TestCase
{
    private $examModel;

    public function setUp(): void
    {
        $this->examModel = new Exam();
    }

    /**
     * TDD Step 76: Test exam creation
     * RED PHASE: Will fail - enhanced create method doesn't exist
     */
    public function test_exam_can_be_created()
    {
        $examData = [
            'exam_title' => 'Midterm Examination',
            'subject_id' => 1,
            'created_by' => 2,
            'duration' => 120,
            'total_points' => 100,
            'instructions' => 'Read all questions carefully',
            'exam_date' => '2024-03-15',
            'start_time' => '09:00:00',
            'end_time' => '11:00:00',
            'status' => 'draft'
        ];

        // This will fail initially
        $examId = $this->examModel->create($examData);
        $this->assertIsNumeric($examId);
        $this->assertGreaterThan(0, $examId);
    }

    /**
     * TDD Step 77: Test get exams by subject
     * RED PHASE: Will fail - method doesn't exist
     */
    public function test_can_get_exams_by_subject()
    {
        // This will fail initially
        $exams = $this->examModel->getExamsBySubject(1);
        $this->assertIsArray($exams);
    }

    /**
     * TDD Step 78: Test exam status update
     * RED PHASE: Will fail - method doesn't exist
     */
    public function test_can_update_exam_status()
    {
        // This will fail initially
        $result = $this->examModel->updateStatus(1, 'published');
        $this->assertTrue($result);
    }

    /**
     * TDD Step 79: Test get active exams
     * RED PHASE: Will fail - method doesn't exist
     */
    public function test_can_get_active_exams()
    {
        // This will fail initially
        $exams = $this->examModel->getActiveExams();
        $this->assertIsArray($exams);
    }
}