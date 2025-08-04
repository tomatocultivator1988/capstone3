<?php

namespace Model;

/**
 * Exam Model
 * 
 * Simple data container for exam information.
 * Contains only getters and setters, no business logic or database operations.
 */
class Exam
{
    private ?int $exam_id = null;
    private string $exam_title = '';
    private ?int $subject_id = null;
    private ?int $created_by = null;
    private int $duration = 60; // in minutes
    private int $total_points = 100;
    private ?string $instructions = null;
    private ?string $exam_date = null;
    private ?string $start_time = null;
    private ?string $end_time = null;
    private string $status = 'draft'; // draft, published, archived
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
        $this->exam_title = $data['exam_title'] ?? '';
        $this->subject_id = $data['subject_id'] ?? null;
        $this->created_by = $data['created_by'] ?? null;
        $this->duration = $data['duration'] ?? 60;
        $this->total_points = $data['total_points'] ?? 100;
        $this->instructions = $data['instructions'] ?? null;
        $this->exam_date = $data['exam_date'] ?? null;
        $this->start_time = $data['start_time'] ?? null;
        $this->end_time = $data['end_time'] ?? null;
        $this->status = $data['status'] ?? 'draft';
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
            'exam_title' => $this->exam_title,
            'subject_id' => $this->subject_id,
            'created_by' => $this->created_by,
            'duration' => $this->duration,
            'total_points' => $this->total_points,
            'instructions' => $this->instructions,
            'exam_date' => $this->exam_date,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }

    // Getters
    public function getExamId(): ?int
    {
        return $this->exam_id;
    }

    public function getExamTitle(): string
    {
        return $this->exam_title;
    }

    public function getSubjectId(): ?int
    {
        return $this->subject_id;
    }

    public function getCreatedBy(): ?int
    {
        return $this->created_by;
    }

    public function getDuration(): int
    {
        return $this->duration;
    }

    public function getTotalPoints(): int
    {
        return $this->total_points;
    }

    public function getInstructions(): ?string
    {
        return $this->instructions;
    }

    public function getExamDate(): ?string
    {
        return $this->exam_date;
    }

    public function getStartTime(): ?string
    {
        return $this->start_time;
    }

    public function getEndTime(): ?string
    {
        return $this->end_time;
    }

    public function getStatus(): string
    {
        return $this->status;
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

    public function setExamTitle(string $exam_title): void
    {
        $this->exam_title = $exam_title;
    }

    public function setSubjectId(?int $subject_id): void
    {
        $this->subject_id = $subject_id;
    }

    public function setCreatedBy(?int $created_by): void
    {
        $this->created_by = $created_by;
    }

    public function setDuration(int $duration): void
    {
        $this->duration = $duration;
    }

    public function setTotalPoints(int $total_points): void
    {
        $this->total_points = $total_points;
    }

    public function setInstructions(?string $instructions): void
    {
        $this->instructions = $instructions;
    }

    public function setExamDate(?string $exam_date): void
    {
        $this->exam_date = $exam_date;
    }

    public function setStartTime(?string $start_time): void
    {
        $this->start_time = $start_time;
    }

    public function setEndTime(?string $end_time): void
    {
        $this->end_time = $end_time;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
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
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function isArchived(): bool
    {
        return $this->status === 'archived';
    }

    public function getFormattedDateTime(): string
    {
        if ($this->exam_date && $this->start_time) {
            return $this->exam_date . ' ' . $this->start_time;
        }
        return '';
    }
}