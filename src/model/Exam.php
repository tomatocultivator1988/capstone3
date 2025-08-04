<?php

namespace App\Model;

/**
 * Exam Model
 * 
 * Simple data container for exam information.
 * Contains only getters and setters, no business logic or database operations.
 */
class Exam
{
    private ?int $exam_id = null;
    private string $title = '';
    private string $description = '';
    private ?int $subject_id = null;
    private ?int $created_by = null;
    private int $duration_minutes = 0;
    private int $total_questions = 0;
    private int $passing_score = 0;
    private string $status = 'draft';
    private ?int $year_level = null;
    private ?string $section = null;
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
        $this->exam_id = $data['exam_id'] ?? null;
        $this->title = $data['title'] ?? '';
        $this->description = $data['description'] ?? '';
        $this->subject_id = $data['subject_id'] ?? null;
        $this->created_by = $data['created_by'] ?? null;
        $this->duration_minutes = $data['duration_minutes'] ?? 0;
        $this->total_questions = $data['total_questions'] ?? 0;
        $this->passing_score = $data['passing_score'] ?? 0;
        $this->status = $data['status'] ?? 'draft';
        $this->year_level = $data['year_level'] ?? null;
        $this->section = $data['section'] ?? null;
        $this->created_at = $data['created_at'] ?? null;
        $this->updated_at = $data['updated_at'] ?? null;
    }

    /**
     * Convert model to array
     */
    public function toArray(): array
    {
        return [
            'exam_id' => $this->exam_id,
            'title' => $this->title,
            'description' => $this->description,
            'subject_id' => $this->subject_id,
            'created_by' => $this->created_by,
            'duration_minutes' => $this->duration_minutes,
            'total_questions' => $this->total_questions,
            'passing_score' => $this->passing_score,
            'status' => $this->status,
            'year_level' => $this->year_level,
            'section' => $this->section,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }

    // Getters
    public function getExamId(): ?int
    {
        return $this->exam_id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getSubjectId(): ?int
    {
        return $this->subject_id;
    }

    public function getCreatedBy(): ?int
    {
        return $this->created_by;
    }

    public function getDurationMinutes(): int
    {
        return $this->duration_minutes;
    }

    public function getTotalQuestions(): int
    {
        return $this->total_questions;
    }

    public function getPassingScore(): int
    {
        return $this->passing_score;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getYearLevel(): ?int
    {
        return $this->year_level;
    }

    public function getSection(): ?string
    {
        return $this->section;
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
    public function setExamId(?int $exam_id): void
    {
        $this->exam_id = $exam_id;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function setSubjectId(?int $subject_id): void
    {
        $this->subject_id = $subject_id;
    }

    public function setCreatedBy(?int $created_by): void
    {
        $this->created_by = $created_by;
    }

    public function setDurationMinutes(int $duration_minutes): void
    {
        $this->duration_minutes = $duration_minutes;
    }

    public function setTotalQuestions(int $total_questions): void
    {
        $this->total_questions = $total_questions;
    }

    public function setPassingScore(int $passing_score): void
    {
        $this->passing_score = $passing_score;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function setYearLevel(?int $year_level): void
    {
        $this->year_level = $year_level;
    }

    public function setSection(?string $section): void
    {
        $this->section = $section;
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