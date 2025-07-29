<?php

namespace App\Models;

use App\Config\Database;
use PDO;
use PDOException;

class Subject
{
    private $db;
    private $table = 'subjects';

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get all subjects
     */
    public function getAllSubjects()
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} ORDER BY subject_name ASC");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Find subject by ID
     */
    public function findById($subject_id)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE subject_id = ?");
            $stmt->execute([$subject_id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * TDD Step 68: Create subject method
     * GREEN PHASE: Method to make create test pass
     * 
     * @param array $data Subject data
     * @return int|false Subject ID if successful, false otherwise
     */
    public function create($data)
    {
        try {
            $sql = "INSERT INTO {$this->table} (subject_code, subject_name, description, units, year_level, semester, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $data['subject_code'],
                $data['subject_name'],
                $data['description'] ?? null,
                $data['units'],
                $data['year_level'],
                $data['semester']
            ]);

            return $result ? $this->db->lastInsertId() : false;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * TDD Step 70: Update subject method
     * GREEN PHASE: Method to make update test pass
     * 
     * @param int $subject_id
     * @param array $data
     * @return bool
     */
    public function update($subject_id, $data)
    {
        try {
            $setParts = [];
            $values = [];

            // Build dynamic SET clause
            $allowedFields = ['subject_code', 'subject_name', 'description', 'units', 'year_level', 'semester'];
            foreach ($data as $key => $value) {
                if (in_array($key, $allowedFields)) {
                    $setParts[] = "{$key} = ?";
                    $values[] = $value;
                }
            }

            if (empty($setParts)) {
                return false;
            }

            $values[] = $subject_id;
            
            $sql = "UPDATE {$this->table} SET " . implode(', ', $setParts) . ", updated_at = NOW() WHERE subject_id = ?";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($values);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Delete subject
     */
    public function delete($subject_id)
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE subject_id = ?");
            return $stmt->execute([$subject_id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * TDD Step 72: Assign faculty to subject method
     * GREEN PHASE: Method to make assignFaculty test pass
     * 
     * @param int $subject_id
     * @param int $faculty_id
     * @return bool
     */
    public function assignFaculty($subject_id, $faculty_id)
    {
        try {
            $sql = "INSERT INTO subject_faculty (subject_id, faculty_id, created_at) VALUES (?, ?, NOW()) 
                    ON DUPLICATE KEY UPDATE updated_at = NOW()";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$subject_id, $faculty_id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * TDD Step 74: Get subjects by faculty method
     * GREEN PHASE: Method for faculty-specific subjects
     * 
     * @param int $faculty_id
     * @return array
     */
    public function getSubjectsByFaculty($faculty_id)
    {
        try {
            $sql = "SELECT s.*, sf.assigned_at 
                    FROM {$this->table} s 
                    INNER JOIN subject_faculty sf ON s.subject_id = sf.subject_id 
                    WHERE sf.faculty_id = ? 
                    ORDER BY s.subject_code ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$faculty_id]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
}