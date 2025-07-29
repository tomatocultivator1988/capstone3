<?php

use PHPUnit\Framework\TestCase;
use App\Models\ExamResult;

/**
 * TDD Step 104: ExamResult model tests
 * RED PHASE: These tests will fail (no ExamResult model exists)
 */
class ExamResultModelTest extends TestCase
{
    private $examResultModel;

    public function setUp(): void
    {
        $this->examResultModel = new ExamResult();
    }

    /**
     * TDD Step 105: Test exam result creation
     * RED PHASE: Will fail - no create method exists
     */
    public function test_exam_result_can_be_created()
    {
        $resultData = [
            'exam_id' => 1,
            'student_id' => 3,
            'answers' => json_encode([
                '1' => 'A',
                '2' => 'C',
                '3' => 'B'
            ]),
            'score' => 85,
            'total_points' => 100,
            'time_taken' => 75,
            'status' => 'completed'
        ];

        // This will fail initially
        $resultId = $this->examResultModel->create($resultData);
        $this->assertIsNumeric($resultId);
        $this->assertGreaterThan(0, $resultId);
    }

    /**
     * TDD Step 106: Test get results by exam
     * RED PHASE: Will fail - method doesn't exist
     */
    public function test_can_get_results_by_exam()
    {
        // This will fail initially
        $results = $this->examResultModel->getResultsByExam(1);
        $this->assertIsArray($results);
    }

    /**
     * TDD Step 107: Test get results by student
     * RED PHASE: Will fail - method doesn't exist
     */
    public function test_can_get_results_by_student()
    {
        // This will fail initially
        $results = $this->examResultModel->getResultsByStudent(3);
        $this->assertIsArray($results);
    }

    /**
     * TDD Step 108: Test exam analytics
     * RED PHASE: Will fail - method doesn't exist
     */
    public function test_can_get_exam_analytics()
    {
        // This will fail initially
        $analytics = $this->examResultModel->getExamAnalytics(1);
        $this->assertIsArray($analytics);
        $this->assertArrayHasKey('average_score', $analytics);
        $this->assertArrayHasKey('total_attempts', $analytics);
    }

    /**
     * TDD Step 109: Test student performance analytics
     * RED PHASE: Will fail - method doesn't exist
     */
    public function test_can_get_student_performance()
    {
        // This will fail initially
        $performance = $this->examResultModel->getStudentPerformance(3);
        $this->assertIsArray($performance);
    }
}