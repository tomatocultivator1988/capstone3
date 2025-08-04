<?php

namespace Model;

/**
 * ExamResult Model
 * 
 * Simple data container for exam result information.
 * Contains only getters and setters, no business logic or database operations.
 */
class ExamResult
{
    private ?int $result_id = null;
    private ?int $exam_id = null;
    private ?int $student_id = null;
    private ?string $answers = null; // JSON string of student answers
    private int $score = 0;
    private int $total_points = 0;
    private ?float $percentage = null;
    private ?string $status = 'submitted'; // submitted, graded
    private ?string $submitted_at = null;
    private ?string $graded_at = null;
    private ?string $created_at = null;
    private ?string $updated_at = null;

    public function __construct(array $data = [])
    {
        if (!empty($data)) {
            $this->hydrate($data);
        }
    }

    /**
     * Hydrate the model with data
     */
    public function hydrate(array $data): void
    {
        $this->result_id = $data['result_id'] ?? null;
        $this->exam_id = $data['exam_id'] ?? null;
        $this->student_id = $data['student_id'] ?? null;
        $this->answers = $data['answers'] ?? null;
        $this->score = $data['score'] ?? 0;
        $this->total_points = $data['total_points'] ?? 0;
        $this->percentage = $data['percentage'] ?? null;
        $this->status = $data['status'] ?? 'submitted';
        $this->submitted_at = $data['submitted_at'] ?? null;
        $this->graded_at = $data['graded_at'] ?? null;
        $this->created_at = $data['created_at'] ?? null;
        $this->updated_at = $data['updated_at'] ?? null;
    }

    /**
     * Convert model to array
     */
    public function toArray(): array
    {
        return [
            'result_id' => $this->result_id,
            'exam_id' => $this->exam_id,
            'student_id' => $this->student_id,
            'answers' => $this->answers,
            'score' => $this->score,
            'total_points' => $this->total_points,
            'percentage' => $this->percentage,
            'status' => $this->status,
            'submitted_at' => $this->submitted_at,
            'graded_at' => $this->graded_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }

    // Getters
    public function getResultId(): ?int
    {
        return $this->result_id;
    }

    public function getExamId(): ?int
    {
        return $this->exam_id;
    }

    public function getStudentId(): ?int
    {
        return $this->student_id;
    }

    public function getAnswers(): ?string
    {
        return $this->answers;
    }

    public function getScore(): int
    {
        return $this->score;
    }

    public function getTotalPoints(): int
    {
        return $this->total_points;
    }

    public function getPercentage(): ?float
    {
        return $this->percentage;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function getSubmittedAt(): ?string
    {
        return $this->submitted_at;
    }

    public function getGradedAt(): ?string
    {
        return $this->graded_at;
    }

    public function getCreatedAt(): ?string
    {
        return $this->created_at;
    }

    public function getUpdatedAt(): ?string
    {
        return $this->updated_at;
    }

    // Setters
    public function setResultId(?int $result_id): void
    {
        $this->result_id = $result_id;
    }

    public function setExamId(?int $exam_id): void
    {
        $this->exam_id = $exam_id;
    }

    public function setStudentId(?int $student_id): void
    {
        $this->student_id = $student_id;
    }

    public function setAnswers(?string $answers): void
    {
        $this->answers = $answers;
    }

    public function setScore(int $score): void
    {
        $this->score = $score;
    }

    public function setTotalPoints(int $total_points): void
    {
        $this->total_points = $total_points;
    }

    public function setPercentage(?float $percentage): void
    {
        $this->percentage = $percentage;
    }

    public function setStatus(?string $status): void
    {
        $this->status = $status;
    }

    public function setSubmittedAt(?string $submitted_at): void
    {
        $this->submitted_at = $submitted_at;
    }

    public function setGradedAt(?string $graded_at): void
    {
        $this->graded_at = $graded_at;
    }

    public function setCreatedAt(?string $created_at): void
    {
        $this->created_at = $created_at;
    }

    public function setUpdatedAt(?string $updated_at): void
    {
        $this->updated_at = $updated_at;
    }

    // Helper methods
    public function isSubmitted(): bool
    {
        return $this->status === 'submitted';
    }

    public function isGraded(): bool
    {
        return $this->status === 'graded';
    }

    /**
     * Calculate percentage if not already set
     */
    public function calculatePercentage(): float
    {
        if ($this->total_points > 0) {
            return round(($this->score / $this->total_points) * 100, 2);
        }
        return 0.0;
    }

    /**
     * Get letter grade based on percentage
     */
    public function getLetterGrade(): string
    {
        $percentage = $this->percentage ?? $this->calculatePercentage();
        
        if ($percentage >= 95) return 'A+';
        if ($percentage >= 90) return 'A';
        if ($percentage >= 85) return 'B+';
        if ($percentage >= 80) return 'B';
        if ($percentage >= 75) return 'C+';
        if ($percentage >= 70) return 'C';
        if ($percentage >= 65) return 'D+';
        if ($percentage >= 60) return 'D';
        return 'F';
    }

    /**
     * Get parsed answers as array
     */
    public function getParsedAnswers(): array
    {
        if ($this->answers === null) {
            return [];
        }
        
        $decoded = json_decode($this->answers, true);
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Set answers from array
     */
    public function setAnswersFromArray(array $answers): void
    {
        $this->answers = json_encode($answers);
    }
}