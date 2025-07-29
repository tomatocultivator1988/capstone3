<?php

use PHPUnit\Framework\TestCase;
use App\Services\QuestionServiceImpl;
use App\Models\Question;

class QuestionServiceTest extends TestCase
{
    private $mockQuestionModel;
    private $questionService;

    protected function setUp(): void
    {
        $this->mockQuestionModel = $this->createMock(Question::class);
        $this->questionService = new QuestionServiceImpl($this->mockQuestionModel);
    }

    public function testCreateQuestionSuccess()
    {
        // Arrange
        $questionData = [
            'question_text' => 'What is 2 + 2?',
            'question_type' => 'multiple_choice',
            'exam_id' => 1,
            'subject_id' => 1,
            'teacher_id' => 1,
            'correct_answer' => '4',
            'options' => ['2', '3', '4', '5'],
            'points' => 1
        ];

        $this->mockQuestionModel->method('create')->willReturn(123);

        // Act
        $result = $this->questionService->createQuestion($questionData);

        // Assert
        $this->assertEquals(123, $result);
    }

    public function testCreateQuestionValidationFailure()
    {
        // Arrange
        $invalidData = [
            'question_text' => '', // Too short
            'question_type' => 'invalid_type',
            'exam_id' => '',
            'subject_id' => '',
            'teacher_id' => ''
        ];

        // Act
        $result = $this->questionService->createQuestion($invalidData);

        // Assert
        $this->assertFalse($result);
    }

    public function testCreateMultipleChoiceWithInvalidOptions()
    {
        // Arrange
        $questionData = [
            'question_text' => 'What is 2 + 2?',
            'question_type' => 'multiple_choice',
            'exam_id' => 1,
            'subject_id' => 1,
            'teacher_id' => 1,
            'correct_answer' => '4',
            'options' => ['2'] // Only one option (invalid)
        ];

        // Act
        $result = $this->questionService->createQuestion($questionData);

        // Assert
        $this->assertFalse($result);
    }

    public function testUpdateQuestionSuccess()
    {
        // Arrange
        $questionData = [
            'question_text' => 'Updated question?',
            'question_type' => 'short_answer',
            'exam_id' => 1,
            'subject_id' => 1,
            'teacher_id' => 1,
            'correct_answer' => 'answer'
        ];

        $this->mockQuestionModel->method('findById')->willReturn(['question_id' => 1]);
        $this->mockQuestionModel->method('update')->willReturn(true);

        // Act
        $result = $this->questionService->updateQuestion(1, $questionData);

        // Assert
        $this->assertTrue($result);
    }

    public function testUpdateQuestionNotFound()
    {
        // Arrange
        $this->mockQuestionModel->method('findById')->willReturn(false);

        // Act
        $result = $this->questionService->updateQuestion(999, []);

        // Assert
        $this->assertFalse($result);
    }

    public function testDeleteQuestionSuccess()
    {
        // Arrange
        $this->mockQuestionModel->method('findById')->willReturn(['question_id' => 1]);
        $this->mockQuestionModel->method('delete')->willReturn(true);

        // Act
        $result = $this->questionService->deleteQuestion(1);

        // Assert
        $this->assertTrue($result);
    }

    public function testDeleteQuestionNotFound()
    {
        // Arrange
        $this->mockQuestionModel->method('findById')->willReturn(false);

        // Act
        $result = $this->questionService->deleteQuestion(999);

        // Assert
        $this->assertFalse($result);
    }

    public function testGetQuestionById()
    {
        // Arrange
        $questionData = ['question_id' => 1, 'question_text' => 'Test question?'];
        $this->mockQuestionModel->method('findById')->willReturn($questionData);

        // Act
        $result = $this->questionService->getQuestionById(1);

        // Assert
        $this->assertEquals($questionData, $result);
    }

    public function testGetAllQuestions()
    {
        // Arrange
        $questionsData = [
            ['question_id' => 1, 'question_text' => 'Question 1'],
            ['question_id' => 2, 'question_text' => 'Question 2']
        ];
        $this->mockQuestionModel->method('getAllQuestions')->willReturn($questionsData);

        // Act
        $result = $this->questionService->getAllQuestions();

        // Assert
        $this->assertEquals($questionsData, $result);
    }

    public function testGetQuestionsByExam()
    {
        // Arrange
        $questionsData = [
            ['question_id' => 1, 'exam_id' => 1],
            ['question_id' => 2, 'exam_id' => 1]
        ];
        $this->mockQuestionModel->method('getQuestionsByExam')->willReturn($questionsData);

        // Act
        $result = $this->questionService->getQuestionsByExam(1);

        // Assert
        $this->assertEquals($questionsData, $result);
    }

    public function testValidateQuestionDataValid()
    {
        // Arrange
        $validData = [
            'question_text' => 'What is the capital of France?',
            'question_type' => 'multiple_choice',
            'exam_id' => 1,
            'subject_id' => 1,
            'teacher_id' => 1,
            'correct_answer' => 'Paris',
            'points' => 2
        ];

        // Act
        $errors = $this->questionService->validateQuestionData($validData);

        // Assert
        $this->assertEmpty($errors);
    }

    public function testValidateQuestionDataInvalid()
    {
        // Arrange
        $invalidData = [
            'question_text' => 'Short', // Too short
            'question_type' => 'invalid',
            'exam_id' => 'not_numeric',
            'subject_id' => '',
            'teacher_id' => '',
            'points' => -1 // Negative points
        ];

        // Act
        $errors = $this->questionService->validateQuestionData($invalidData);

        // Assert
        $this->assertNotEmpty($errors);
        $this->assertContains('Question text must be at least 10 characters long', $errors);
        $this->assertContains('Invalid question type', $errors);
        $this->assertContains('Valid exam ID is required', $errors);
        $this->assertContains('Points must be a positive number', $errors);
    }

    public function testGetQuestionTypes()
    {
        // Act
        $types = $this->questionService->getQuestionTypes();

        // Assert
        $this->assertIsArray($types);
        $this->assertContains('multiple_choice', $types);
        $this->assertContains('true_false', $types);
        $this->assertContains('short_answer', $types);
        $this->assertContains('essay', $types);
    }

    public function testValidateQuestionOptionsValid()
    {
        // Arrange
        $options = ['Apple', 'Banana', 'Cherry', 'Date'];
        $correctAnswer = 'Apple';

        // Act
        $errors = $this->questionService->validateQuestionOptions($options, $correctAnswer);

        // Assert
        $this->assertEmpty($errors);
    }

    public function testValidateQuestionOptionsTooFew()
    {
        // Arrange
        $options = ['Apple']; // Only one option
        $correctAnswer = 'Apple';

        // Act
        $errors = $this->questionService->validateQuestionOptions($options, $correctAnswer);

        // Assert
        $this->assertNotEmpty($errors);
        $this->assertContains('Multiple choice questions must have at least 2 options', $errors);
    }

    public function testValidateQuestionOptionsTooMany()
    {
        // Arrange
        $options = ['1', '2', '3', '4', '5', '6', '7']; // Too many options
        $correctAnswer = '1';

        // Act
        $errors = $this->questionService->validateQuestionOptions($options, $correctAnswer);

        // Assert
        $this->assertNotEmpty($errors);
        $this->assertContains('Multiple choice questions must not have more than 6 options', $errors);
    }

    public function testValidateQuestionOptionsEmptyOption()
    {
        // Arrange
        $options = ['Apple', '', 'Cherry']; // Empty option
        $correctAnswer = 'Apple';

        // Act
        $errors = $this->questionService->validateQuestionOptions($options, $correctAnswer);

        // Assert
        $this->assertNotEmpty($errors);
        $this->assertContains('Option 2 cannot be empty', $errors);
    }

    public function testValidateQuestionOptionsIncorrectAnswer()
    {
        // Arrange
        $options = ['Apple', 'Banana', 'Cherry'];
        $correctAnswer = 'Orange'; // Not in options

        // Act
        $errors = $this->questionService->validateQuestionOptions($options, $correctAnswer);

        // Assert
        $this->assertNotEmpty($errors);
        $this->assertContains('Correct answer must be one of the provided options', $errors);
    }

    public function testValidateQuestionOptionsDuplicate()
    {
        // Arrange
        $options = ['Apple', 'Banana', 'Apple']; // Duplicate
        $correctAnswer = 'Apple';

        // Act
        $errors = $this->questionService->validateQuestionOptions($options, $correctAnswer);

        // Assert
        $this->assertNotEmpty($errors);
        $this->assertContains('All options must be unique', $errors);
    }

    public function testQuestionExists()
    {
        // Arrange
        $this->mockQuestionModel->method('getQuestionById')->willReturn(['question_id' => 1]);

        // Act
        $result = $this->questionService->questionExists(1);

        // Assert
        $this->assertTrue($result);
    }

    public function testQuestionNotExists()
    {
        // Arrange
        $this->mockQuestionModel->method('getQuestionById')->willReturn(false);

        // Act
        $result = $this->questionService->questionExists(999);

        // Assert
        $this->assertFalse($result);
    }

    public function testBulkCreateQuestionsSuccess()
    {
        // Arrange
        $questionsData = [
            [
                'question_text' => 'Question 1 text here for testing',
                'question_type' => 'multiple_choice',
                'subject_id' => 1,
                'teacher_id' => 1,
                'correct_answer' => 'A',
                'options' => ['A', 'B', 'C', 'D']
            ],
            [
                'question_text' => 'Question 2 text here for testing',
                'question_type' => 'short_answer',
                'subject_id' => 1,
                'teacher_id' => 1,
                'correct_answer' => 'answer'
            ]
        ];

        $this->mockQuestionModel->method('create')
            ->willReturnOnConsecutiveCalls(1, 2);

        // Act
        $result = $this->questionService->bulkCreateQuestions(1, $questionsData);

        // Assert
        $this->assertCount(2, $result);
        $this->assertEquals([1, 2], $result);
    }
}