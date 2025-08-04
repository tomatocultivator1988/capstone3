<?php

namespace App\Models;

/**
 * User Model
 * 
 * Simple data container for user information.
 * Contains only getters and setters, no business logic or database operations.
 */
class User
{
    private ?int $user_id = null;
    private string $school_id = '';
    private string $full_name = '';
    private string $password = '';
    private string $role = '';
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
        $this->user_id = $data['user_id'] ?? null;
        $this->school_id = $data['school_id'] ?? '';
        $this->full_name = $data['full_name'] ?? '';
        $this->password = $data['password'] ?? '';
        $this->role = $data['role'] ?? '';
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
            'user_id' => $this->user_id,
            'school_id' => $this->school_id,
            'full_name' => $this->full_name,
            'password' => $this->password,
            'role' => $this->role,
            'year_level' => $this->year_level,
            'section' => $this->section,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }

    // Getters
    public function getUserId(): ?int
    {
        return $this->user_id;
    }

    public function getSchoolId(): string
    {
        return $this->school_id;
    }

    public function getFullName(): string
    {
        return $this->full_name;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getRole(): string
    {
        return $this->role;
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
    public function setUserId(?int $user_id): void
    {
        $this->user_id = $user_id;
    }

    public function setSchoolId(string $school_id): void
    {
        $this->school_id = $school_id;
    }

    public function setFullName(string $full_name): void
    {
        $this->full_name = $full_name;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function setRole(string $role): void
    {
        $this->role = $role;
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

    /**
     * Check if user is a student
     */
    public function isStudent(): bool
    {
        return $this->role === 'student';
    }

    /**
     * Check if user is faculty
     */
    public function isFaculty(): bool
    {
        return $this->role === 'faculty';
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user has specific role
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }
}