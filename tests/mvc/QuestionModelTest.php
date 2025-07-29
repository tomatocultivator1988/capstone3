<?php

use PHPUnit\Framework\TestCase;
use App\Models\Question;

/**
 * TDD Step 89: Question model tests
 * RED PHASE: These tests will fail (no Question model exists)
 */
class QuestionModelTest extends TestCase
{
    private $questionModel;

    public function setUp(): void
    {
        $this->questionModel = new Question();
    }

    /**
     * TDD Step 90: Test question creation
     * RED PHASE: Will fail - no create method exists
     */
    public function test_question_can_be_created()
    {
        $questionData = [
            'exam_id' => 1,
            'question_text' => 'What is the capital of France?',
            'question_type' => 'multiple_choice',
            'points' => 5,
            'order_number' => 1,
            'options' => json_encode([
                'A' => 'London',
                'B' => 'Berlin', 
                'C' => 'Paris',
                'D' => 'Madrid'
            ]),
            'correct_answer' => 'C'
        ];

        // This will fail initially
        $questionId = $this->questionModel->create($questionData);
        $this->assertIsNumeric($questionId);
        $this->assertGreaterThan(0, $questionId);
    }

    /**
     * TDD Step 91: Test get questions by exam
     * RED PHASE: Will fail - method doesn't exist
     */
    public function test_can_get_questions_by_exam()
    {
        // This will fail initially
        $questions = $this->questionModel->getQuestionsByExam(1);
        $this->assertIsArray($questions);
    }

    /**
     * TDD Step 92: Test question update
     * RED PHASE: Will fail - method doesn't exist
     */
    public function test_question_can_be_updated()
    {
        $updateData = [
            'question_text' => 'Updated question text',
            'points' => 10
        ];

        // This will fail initially
        $result = $this->questionModel->update(1, $updateData);
        $this->assertTrue($result);
    }

    /**
     * TDD Step 93: Test question deletion
     * RED PHASE: Will fail - method doesn't exist
     */
    public function test_question_can_be_deleted()
    {
        // This will fail initially
        $result = $this->questionModel->delete(999);
        $this->assertTrue($result);
    }

    /**
     * TDD Step 94: Test reorder questions
     * RED PHASE: Will fail - method doesn't exist
     */
    public function test_can_reorder_questions()
    {
        $questionOrder = [3, 1, 2]; // New order for questions

        // This will fail initially
        $result = $this->questionModel->reorderQuestions(1, $questionOrder);
        $this->assertTrue($result);
    }
}