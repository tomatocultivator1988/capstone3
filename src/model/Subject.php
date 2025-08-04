<?php

namespace Model;

/**
 * Subject Model
 * 
 * Simple data container for subject information.
 * Contains only getters and setters, no business logic or database operations.
 */
class Subject
{
    private ?int $subject_id = null;
    private string $subject_code = '';
    private string $subject_name = '';
    private string $description = '';
    private int $units = 0;
    private ?int $faculty_id = null;
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
        $this->subject_id = $data['subject_id'] ?? null;
        $this->subject_code = $data['subject_code'] ?? '';
        $this->subject_name = $data['subject_name'] ?? '';
        $this->description = $data['description'] ?? '';
        $this->units = $data['units'] ?? 0;
        $this->faculty_id = $data['faculty_id'] ?? null;
        $this->created_at = $data['created_at'] ?? null;
        $this->updated_at = $data['updated_at'] ?? null;
    }

    /**
     * Convert model to array
     */
    public function toArray(): array
    {
        return [
            'subject_id' => $this->subject_id,
            'subject_code' => $this->subject_code,
            'subject_name' => $this->subject_name,
            'description' => $this->description,
            'units' => $this->units,
            'faculty_id' => $this->faculty_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }

    // Getters
    public function getSubjectId(): ?int
    {
        return $this->subject_id;
    }

    public function getSubjectCode(): string
    {
        return $this->subject_code;
    }

    public function getSubjectName(): string
    {
        return $this->subject_name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getUnits(): int
    {
        return $this->units;
    }

    public function getFacultyId(): ?int
    {
        return $this->faculty_id;
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
    public function setSubjectId(?int $subject_id): void
    {
        $this->subject_id = $subject_id;
    }

    public function setSubjectCode(string $subject_code): void
    {
        $this->subject_code = $subject_code;
    }

    public function setSubjectName(string $subject_name): void
    {
        $this->subject_name = $subject_name;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function setUnits(int $units): void
    {
        $this->units = $units;
    }

    public function setFacultyId(?int $faculty_id): void
    {
        $this->faculty_id = $faculty_id;
    }

    public function setCreatedAt(?string $created_at): void
    {
        $this->created_at = $created_at;
    }

    public function setUpdatedAt(?string $updated_at): void
    {
        $this->updated_at = $updated_at;
    }

    /**
     * Check if subject is assigned to a faculty
     */
    public function isAssignedToFaculty(): bool
    {
        return $this->faculty_id !== null;
    }

    /**
     * Get display name (code + name)
     */
    public function getDisplayName(): string
    {
        return $this->subject_code . ' - ' . $this->subject_name;
    }
}