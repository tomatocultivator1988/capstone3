<?php

namespace Model;

/**
 * Question Model
 * 
 * Simple data container for question information.
 * Contains only getters and setters, no business logic or database operations.
 */
class Question
{
    private ?int $question_id = null;
    private ?int $exam_id = null;
    private string $question_text = '';
    private string $question_type = ''; // multiple_choice, true_false, essay, fill_blank
    private int $points = 1;
    private int $order_number = 1;
    private ?string $options = null; // JSON string for multiple choice options
    private ?string $correct_answer = null;
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
        $this->question_id = $data['question_id'] ?? null;
        $this->exam_id = $data['exam_id'] ?? null;
        $this->question_text = $data['question_text'] ?? '';
        $this->question_type = $data['question_type'] ?? '';
        $this->points = $data['points'] ?? 1;
        $this->order_number = $data['order_number'] ?? 1;
        $this->options = $data['options'] ?? null;
        $this->correct_answer = $data['correct_answer'] ?? null;
        $this->created_at = $data['created_at'] ?? null;
        $this->updated_at = $data['updated_at'] ?? null;
    }

    /**
     * Convert model to array
     */
    public function toArray(): array
    {
        return [
            'question_id' => $this->question_id,
            'exam_id' => $this->exam_id,
            'question_text' => $this->question_text,
            'question_type' => $this->question_type,
            'points' => $this->points,
            'order_number' => $this->order_number,
            'options' => $this->options,
            'correct_answer' => $this->correct_answer,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }

    // Getters
    public function getQuestionId(): ?int
    {
        return $this->question_id;
    }

    public function getExamId(): ?int
    {
        return $this->exam_id;
    }

    public function getQuestionText(): string
    {
        return $this->question_text;
    }

    public function getQuestionType(): string
    {
        return $this->question_type;
    }

    public function getPoints(): int
    {
        return $this->points;
    }

    public function getOrderNumber(): int
    {
        return $this->order_number;
    }

    public function getOptions(): ?string
    {
        return $this->options;
    }

    public function getCorrectAnswer(): ?string
    {
        return $this->correct_answer;
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
    public function setQuestionId(?int $question_id): void
    {
        $this->question_id = $question_id;
    }

    public function setExamId(?int $exam_id): void
    {
        $this->exam_id = $exam_id;
    }

    public function setQuestionText(string $question_text): void
    {
        $this->question_text = $question_text;
    }

    public function setQuestionType(string $question_type): void
    {
        $this->question_type = $question_type;
    }

    public function setPoints(int $points): void
    {
        $this->points = $points;
    }

    public function setOrderNumber(int $order_number): void
    {
        $this->order_number = $order_number;
    }

    public function setOptions(?string $options): void
    {
        $this->options = $options;
    }

    public function setCorrectAnswer(?string $correct_answer): void
    {
        $this->correct_answer = $correct_answer;
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
    public function isMultipleChoice(): bool
    {
        return $this->question_type === 'multiple_choice';
    }

    public function isTrueFalse(): bool
    {
        return $this->question_type === 'true_false';
    }

    public function isEssay(): bool
    {
        return $this->question_type === 'essay';
    }

    public function isFillBlank(): bool
    {
        return $this->question_type === 'fill_blank';
    }

    /**
     * Get parsed options as array for multiple choice questions
     */
    public function getParsedOptions(): array
    {
        if ($this->options === null) {
            return [];
        }
        
        $decoded = json_decode($this->options, true);
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Set options from array for multiple choice questions
     */
    public function setOptionsFromArray(array $options): void
    {
        $this->options = json_encode($options);
    }
}