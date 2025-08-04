<?php

namespace App\Model;

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
    private ?string $answers = null;
    private int $score = 0;
    private int $total_points = 0;
    private int $time_taken = 0;
    private string $status = 'pending';
    private ?string $submitted_at = null;
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
        $this->time_taken = $data['time_taken'] ?? 0;
        $this->status = $data['status'] ?? 'pending';
        $this->submitted_at = $data['submitted_at'] ?? null;
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
            'time_taken' => $this->time_taken,
            'status' => $this->status,
            'submitted_at' => $this->submitted_at,
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

    public function getTimeTaken(): int
    {
        return $this->time_taken;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getSubmittedAt(): ?string
    {
        return $this->submitted_at;
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

    public function setTimeTaken(int $time_taken): void
    {
        $this->time_taken = $time_taken;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function setSubmittedAt(?string $submitted_at): void
    {
        $this->submitted_at = $submitted_at;
    }

    public function setCreatedAt(?string $created_at): void
    {
        $this->created_at = $created_at;
    }

    public function setUpdatedAt(?string $updated_at): void
    {
        $this->updated_at = $updated_at;
    }
}