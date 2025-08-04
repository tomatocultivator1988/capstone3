<?php

namespace App\Services;

use App\Models\ExamResult;
use App\Services\ExamResultService;
use App\Services\QuestionService;
use App\Services\ServiceContainer;
use Exception;

/**
 * ExamResultServiceImpl
 * 
 * Implementation of the ExamResultService interface.
 * Handles all exam result-related business logic.
 */
class ExamResultServiceImpl implements ExamResultService
{
    private ExamResult $examResultModel;
    private QuestionService $questionService;

    public function __construct(?ExamResult $examResultModel = null, ?QuestionService $questionService = null)
    {
        $this->examResultModel = $examResultModel ?? new ExamResult();
        $this->questionService = $questionService ?? ServiceContainer::getInstance()->get(QuestionService::class);
    }

    /**
     * {@inheritdoc}
     */
    public function createExamResult(array $resultData)
    {
        try {
            // Validate result data
            $validationErrors = $this->validateExamResultData($resultData);
            if (!empty($validationErrors)) {
                throw new Exception('Validation failed: ' . implode(', ', $validationErrors));
            }

            // Check if student has already taken this exam
            if ($this->hasStudentTakenExam($resultData['exam_id'], $resultData['student_id'])) {
                throw new Exception('Student has already taken this exam');
            }

            return $this->examResultModel->create($resultData);
        } catch (Exception $e) {
            error_log("ExamResultService::createExamResult error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function updateExamResult(int $resultId, array $resultData): bool
    {
        try {
            // Check if result exists
            if (!$this->examResultExists($resultId)) {
                throw new Exception('Exam result not found');
            }

            // Validate result data
            $validationErrors = $this->validateExamResultData($resultData);
            if (!empty($validationErrors)) {
                throw new Exception('Validation failed: ' . implode(', ', $validationErrors));
            }

            return $this->examResultModel->update($resultId, $resultData);
        } catch (Exception $e) {
            error_log("ExamResultService::updateExamResult error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deleteExamResult(int $resultId): bool
    {
        try {
            // Check if result exists
            if (!$this->examResultExists($resultId)) {
                throw new Exception('Exam result not found');
            }

            return $this->examResultModel->delete($resultId);
        } catch (Exception $e) {
            error_log("ExamResultService::deleteExamResult error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getExamResultById(int $resultId)
    {
        try {
            return $this->examResultModel->getExamResultById($resultId);
        } catch (Exception $e) {
            error_log("ExamResultService::getExamResultById error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAllExamResults(): array
    {
        try {
            return $this->examResultModel->getAllExamResults() ?? [];
        } catch (Exception $e) {
            error_log("ExamResultService::getAllExamResults error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getExamResultsByStudent(int $studentId): array
    {
        try {
            return $this->examResultModel->getExamResultsByStudent($studentId) ?? [];
        } catch (Exception $e) {
            error_log("ExamResultService::getExamResultsByStudent error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getExamResultsByExam(int $examId): array
    {
        try {
            return $this->examResultModel->getExamResultsByExam($examId) ?? [];
        } catch (Exception $e) {
            error_log("ExamResultService::getExamResultsByExam error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getExamResultsBySubject(int $subjectId): array
    {
        try {
            return $this->examResultModel->getExamResultsBySubject($subjectId) ?? [];
        } catch (Exception $e) {
            error_log("ExamResultService::getExamResultsBySubject error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function calculateAndSetScore(int $examId, int $studentId, array $answers): array
    {
        try {
            // Get exam questions
            $questions = $this->questionService->getQuestionsByExam($examId);
            if (empty($questions)) {
                throw new Exception('No questions found for this exam');
            }

            $totalQuestions = count($questions);
            $correctAnswers = 0;
            $totalPoints = 0;
            $earnedPoints = 0;

            // Calculate score
            foreach ($questions as $question) {
                $questionId = $question['question_id'];
                $studentAnswer = $answers[$questionId] ?? '';
                $correctAnswer = $question['correct_answer'];
                $points = $question['points'] ?? 1;

                $totalPoints += $points;

                // Check if answer is correct
                if ($this->isAnswerCorrect($studentAnswer, $correctAnswer, $question['question_type'])) {
                    $correctAnswers++;
                    $earnedPoints += $points;
                }
            }

            // Calculate percentage
            $percentage = $totalPoints > 0 ? ($earnedPoints / $totalPoints) * 100 : 0;

            // Prepare result data
            $resultData = [
                'exam_id' => $examId,
                'student_id' => $studentId,
                'score' => round($percentage, 2),
                'total_questions' => $totalQuestions,
                'correct_answers' => $correctAnswers,
                'total_points' => $totalPoints,
                'earned_points' => $earnedPoints,
                'answers' => json_encode($answers),
                'completed_at' => date('Y-m-d H:i:s')
            ];

            // Create exam result
            $resultId = $this->createExamResult($resultData);
            if ($resultId) {
                $resultData['result_id'] = $resultId;
                return $resultData;
            }

            throw new Exception('Failed to save exam result');
        } catch (Exception $e) {
            error_log("ExamResultService::calculateAndSetScore error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getExamStatistics(int $examId): array
    {
        try {
            $results = $this->getExamResultsByExam($examId);
            
            if (empty($results)) {
                return [
                    'total_attempts' => 0,
                    'average_score' => 0,
                    'highest_score' => 0,
                    'lowest_score' => 0,
                    'passing_rate' => 0
                ];
            }

            $scores = array_column($results, 'score');
            $totalAttempts = count($scores);
            $averageScore = array_sum($scores) / $totalAttempts;
            $highestScore = max($scores);
            $lowestScore = min($scores);

            // Calculate passing rate (assuming 60% is passing)
            $passingScores = array_filter($scores, function($score) {
                return $score >= 60;
            });
            $passingRate = ($totalAttempts > 0) ? (count($passingScores) / $totalAttempts) * 100 : 0;

            return [
                'total_attempts' => $totalAttempts,
                'average_score' => round($averageScore, 2),
                'highest_score' => $highestScore,
                'lowest_score' => $lowestScore,
                'passing_rate' => round($passingRate, 2)
            ];
        } catch (Exception $e) {
            error_log("ExamResultService::getExamStatistics error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getStudentExamHistory(int $studentId, ?int $subjectId = null): array
    {
        try {
            if ($subjectId) {
                return $this->examResultModel->getStudentExamHistoryBySubject($studentId, $subjectId) ?? [];
            }
            return $this->getExamResultsByStudent($studentId);
        } catch (Exception $e) {
            error_log("ExamResultService::getStudentExamHistory error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function hasStudentTakenExam(int $examId, int $studentId): bool
    {
        try {
            return $this->examResultModel->hasStudentTakenExam($examId, $studentId);
        } catch (Exception $e) {
            error_log("ExamResultService::hasStudentTakenExam error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getExamPassingRate(int $examId, float $passingScore = 60.0): array
    {
        try {
            $results = $this->getExamResultsByExam($examId);
            
            if (empty($results)) {
                return [
                    'total_attempts' => 0,
                    'passed' => 0,
                    'failed' => 0,
                    'passing_rate' => 0
                ];
            }

            $totalAttempts = count($results);
            $passed = 0;

            foreach ($results as $result) {
                if ($result['score'] >= $passingScore) {
                    $passed++;
                }
            }

            $failed = $totalAttempts - $passed;
            $passingRate = ($totalAttempts > 0) ? ($passed / $totalAttempts) * 100 : 0;

            return [
                'total_attempts' => $totalAttempts,
                'passed' => $passed,
                'failed' => $failed,
                'passing_rate' => round($passingRate, 2),
                'passing_score_threshold' => $passingScore
            ];
        } catch (Exception $e) {
            error_log("ExamResultService::getExamPassingRate error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function validateExamResultData(array $resultData): array
    {
        $errors = [];

        // Validate exam_id
        if (empty($resultData['exam_id']) || !is_numeric($resultData['exam_id'])) {
            $errors[] = 'Valid exam ID is required';
        }

        // Validate student_id
        if (empty($resultData['student_id']) || !is_numeric($resultData['student_id'])) {
            $errors[] = 'Valid student ID is required';
        }

        // Validate score
        if (isset($resultData['score']) && (!is_numeric($resultData['score']) || $resultData['score'] < 0 || $resultData['score'] > 100)) {
            $errors[] = 'Score must be a number between 0 and 100';
        }

        // Validate total_questions
        if (isset($resultData['total_questions']) && (!is_numeric($resultData['total_questions']) || $resultData['total_questions'] < 0)) {
            $errors[] = 'Total questions must be a positive number';
        }

        // Validate correct_answers
        if (isset($resultData['correct_answers']) && (!is_numeric($resultData['correct_answers']) || $resultData['correct_answers'] < 0)) {
            $errors[] = 'Correct answers must be a positive number';
        }

        return $errors;
    }

    /**
     * {@inheritdoc}
     */
    public function examResultExists(int $resultId): bool
    {
        try {
            $result = $this->getExamResultById($resultId);
            return $result !== false && $result !== null;
        } catch (Exception $e) {
            error_log("ExamResultService::examResultExists error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getTopPerformers(int $examId, int $limit = 10): array
    {
        try {
            return $this->examResultModel->getTopPerformers($examId, $limit) ?? [];
        } catch (Exception $e) {
            error_log("ExamResultService::getTopPerformers error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function generateDetailedReport(int $resultId): array
    {
        try {
            $result = $this->getExamResultById($resultId);
            if (!$result) {
                throw new Exception('Exam result not found');
            }

            $questions = $this->questionService->getQuestionsByExam($result['exam_id']);
            $studentAnswers = json_decode($result['answers'], true) ?? [];

            $questionAnalysis = [];
            foreach ($questions as $question) {
                $questionId = $question['question_id'];
                $studentAnswer = $studentAnswers[$questionId] ?? '';
                $correctAnswer = $question['correct_answer'];
                $isCorrect = $this->isAnswerCorrect($studentAnswer, $correctAnswer, $question['question_type']);

                $questionAnalysis[] = [
                    'question_id' => $questionId,
                    'question_text' => $question['question_text'],
                    'question_type' => $question['question_type'],
                    'student_answer' => $studentAnswer,
                    'correct_answer' => $correctAnswer,
                    'is_correct' => $isCorrect,
                    'points' => $question['points'] ?? 1,
                    'points_earned' => $isCorrect ? ($question['points'] ?? 1) : 0
                ];
            }

            return [
                'result' => $result,
                'question_analysis' => $questionAnalysis,
                'summary' => [
                    'total_questions' => count($questions),
                    'correct_answers' => $result['correct_answers'],
                    'score_percentage' => $result['score'],
                    'total_points' => $result['total_points'] ?? 0,
                    'earned_points' => $result['earned_points'] ?? 0
                ]
            ];
        } catch (Exception $e) {
            error_log("ExamResultService::generateDetailedReport error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Helper method to check if an answer is correct
     */
    private function isAnswerCorrect(string $studentAnswer, string $correctAnswer, string $questionType): bool
    {
        switch ($questionType) {
            case 'multiple_choice':
            case 'true_false':
                return strtolower(trim($studentAnswer)) === strtolower(trim($correctAnswer));
            
            case 'short_answer':
                // More flexible matching for short answers
                return strtolower(trim($studentAnswer)) === strtolower(trim($correctAnswer));
            
            case 'essay':
                // Essay questions require manual grading, so we return false here
                // In a real system, this would be handled differently
                return false;
            
            default:
                return false;
        }
    }
}