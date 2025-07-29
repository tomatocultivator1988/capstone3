<?php

namespace App\Services;

/**
 * ExamResultService Interface
 * 
 * Defines the contract for exam result-related business logic operations.
 */
interface ExamResultService
{
    /**
     * Create a new exam result
     *
     * @param array $resultData The exam result data
     * @return int|false Result ID if successful, false otherwise
     */
    public function createExamResult(array $resultData);

    /**
     * Update an existing exam result
     *
     * @param int $resultId The result ID
     * @param array $resultData The updated result data
     * @return bool True if successful, false otherwise
     */
    public function updateExamResult(int $resultId, array $resultData): bool;

    /**
     * Delete an exam result
     *
     * @param int $resultId The result ID
     * @return bool True if successful, false otherwise
     */
    public function deleteExamResult(int $resultId): bool;

    /**
     * Get exam result by ID
     *
     * @param int $resultId The result ID
     * @return array|false Result data if found, false otherwise
     */
    public function getExamResultById(int $resultId);

    /**
     * Get all exam results
     *
     * @return array Array of exam results
     */
    public function getAllExamResults(): array;

    /**
     * Get exam results by student
     *
     * @param int $studentId The student ID
     * @return array Array of results for the student
     */
    public function getExamResultsByStudent(int $studentId): array;

    /**
     * Get exam results by exam
     *
     * @param int $examId The exam ID
     * @return array Array of results for the exam
     */
    public function getExamResultsByExam(int $examId): array;

    /**
     * Get exam results by subject
     *
     * @param int $subjectId The subject ID
     * @return array Array of results for the subject
     */
    public function getExamResultsBySubject(int $subjectId): array;

    /**
     * Calculate and set exam score
     *
     * @param int $examId The exam ID
     * @param int $studentId The student ID
     * @param array $answers Student's answers
     * @return array Result data with calculated score
     */
    public function calculateAndSetScore(int $examId, int $studentId, array $answers): array;

    /**
     * Get exam statistics
     *
     * @param int $examId The exam ID
     * @return array Statistics including average, highest, lowest scores
     */
    public function getExamStatistics(int $examId): array;

    /**
     * Get student's exam history
     *
     * @param int $studentId The student ID
     * @param int|null $subjectId Optional subject filter
     * @return array Array of student's exam results
     */
    public function getStudentExamHistory(int $studentId, ?int $subjectId = null): array;

    /**
     * Check if student has taken an exam
     *
     * @param int $examId The exam ID
     * @param int $studentId The student ID
     * @return bool True if student has taken the exam, false otherwise
     */
    public function hasStudentTakenExam(int $examId, int $studentId): bool;

    /**
     * Get passing rate for an exam
     *
     * @param int $examId The exam ID
     * @param float $passingScore The passing score threshold (default 60%)
     * @return array Passing rate statistics
     */
    public function getExamPassingRate(int $examId, float $passingScore = 60.0): array;

    /**
     * Validate exam result data
     *
     * @param array $resultData The result data to validate
     * @return array Array of validation errors (empty if valid)
     */
    public function validateExamResultData(array $resultData): array;

    /**
     * Check if exam result exists
     *
     * @param int $resultId The result ID
     * @return bool True if result exists, false otherwise
     */
    public function examResultExists(int $resultId): bool;

    /**
     * Get top performers for an exam
     *
     * @param int $examId The exam ID
     * @param int $limit Number of top performers to return
     * @return array Array of top performing students
     */
    public function getTopPerformers(int $examId, int $limit = 10): array;

    /**
     * Generate detailed result report
     *
     * @param int $resultId The result ID
     * @return array Detailed result report with question-by-question analysis
     */
    public function generateDetailedReport(int $resultId): array;
}