<?php

namespace Dao\Impl;

use Dao\Interface\ExamDAOInterface;
use Model\Exam;
use Config\Database;
use PDO;
use PDOException;

class ExamDAOImpl implements ExamDAOInterface
{
    private PDO $db;
    private string $table = 'exams';

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function findById(int $exam_id): ?Exam
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE exam_id = ?");
            $stmt->execute([$exam_id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $data ? new Exam($data) : null;
        } catch (PDOException $e) {
            error_log("ExamDAO::findById error: " . $e->getMessage());
            return null;
        }
    }

    public function findAll(): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} ORDER BY created_at DESC");
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return array_map(fn($row) => new Exam($row), $data);
        } catch (PDOException $e) {
            error_log("ExamDAO::findAll error: " . $e->getMessage());
            return [];
        }
    }

    public function findBySubjectId(int $subject_id): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE subject_id = ? ORDER BY created_at DESC");
            $stmt->execute([$subject_id]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return array_map(fn($row) => new Exam($row), $data);
        } catch (PDOException $e) {
            error_log("ExamDAO::findBySubjectId error: " . $e->getMessage());
            return [];
        }
    }

    public function findByCreatorId(int $created_by): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE created_by = ? ORDER BY created_at DESC");
            $stmt->execute([$created_by]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return array_map(fn($row) => new Exam($row), $data);
        } catch (PDOException $e) {
            error_log("ExamDAO::findByCreatorId error: " . $e->getMessage());
            return [];
        }
    }

    public function findByStatus(string $status): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE status = ? ORDER BY created_at DESC");
            $stmt->execute([$status]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return array_map(fn($row) => new Exam($row), $data);
        } catch (PDOException $e) {
            error_log("ExamDAO::findByStatus error: " . $e->getMessage());
            return [];
        }
    }

    public function create(Exam $exam): bool
    {
        try {
            $sql = "INSERT INTO {$this->table} (exam_title, subject_id, created_by, duration, total_points, instructions, exam_date, start_time, end_time, status, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $exam->getExamTitle(),
                $exam->getSubjectId(),
                $exam->getCreatedBy(),
                $exam->getDuration(),
                $exam->getTotalPoints(),
                $exam->getInstructions(),
                $exam->getExamDate(),
                $exam->getStartTime(),
                $exam->getEndTime(),
                $exam->getStatus()
            ]);

            if ($result) {
                $exam->setExamId((int) $this->db->lastInsertId());
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("ExamDAO::create error: " . $e->getMessage());
            return false;
        }
    }

    public function update(Exam $exam): bool
    {
        try {
            $sql = "UPDATE {$this->table} 
                    SET exam_title = ?, subject_id = ?, duration = ?, total_points = ?, instructions = ?, exam_date = ?, start_time = ?, end_time = ?, status = ?, updated_at = NOW()
                    WHERE exam_id = ?";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $exam->getExamTitle(),
                $exam->getSubjectId(),
                $exam->getDuration(),
                $exam->getTotalPoints(),
                $exam->getInstructions(),
                $exam->getExamDate(),
                $exam->getStartTime(),
                $exam->getEndTime(),
                $exam->getStatus(),
                $exam->getExamId()
            ]);
        } catch (PDOException $e) {
            error_log("ExamDAO::update error: " . $e->getMessage());
            return false;
        }
    }

    public function deleteById(int $exam_id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE exam_id = ?");
            return $stmt->execute([$exam_id]);
        } catch (PDOException $e) {
            error_log("ExamDAO::deleteById error: " . $e->getMessage());
            return false;
        }
    }

    public function getTotalCount(): int
    {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table}");
            $stmt->execute();
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("ExamDAO::getTotalCount error: " . $e->getMessage());
            return 0;
        }
    }

    public function findWithPagination(int $limit, int $offset): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} ORDER BY created_at DESC LIMIT ? OFFSET ?");
            $stmt->execute([$limit, $offset]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return array_map(fn($row) => new Exam($row), $data);
        } catch (PDOException $e) {
            error_log("ExamDAO::findWithPagination error: " . $e->getMessage());
            return [];
        }
    }

    public function findPublishedExams(): array
    {
        return $this->findByStatus('published');
    }

    public function findDraftsByCreator(int $created_by): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE created_by = ? AND status = 'draft' ORDER BY created_at DESC");
            $stmt->execute([$created_by]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return array_map(fn($row) => new Exam($row), $data);
        } catch (PDOException $e) {
            error_log("ExamDAO::findDraftsByCreator error: " . $e->getMessage());
            return [];
        }
    }

    public function updateStatus(int $exam_id, string $status): bool
    {
        try {
            $stmt = $this->db->prepare("UPDATE {$this->table} SET status = ?, updated_at = NOW() WHERE exam_id = ?");
            return $stmt->execute([$status, $exam_id]);
        } catch (PDOException $e) {
            error_log("ExamDAO::updateStatus error: " . $e->getMessage());
            return false;
        }
    }

    public function findByDate(string $exam_date): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE exam_date = ? ORDER BY start_time");
            $stmt->execute([$exam_date]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return array_map(fn($row) => new Exam($row), $data);
        } catch (PDOException $e) {
            error_log("ExamDAO::findByDate error: " . $e->getMessage());
            return [];
        }
    }

    public function findAllWithDetails(): array
    {
        try {
            $sql = "SELECT e.*, s.subject_name, s.subject_code, u.full_name as creator_name
                    FROM {$this->table} e
                    LEFT JOIN subjects s ON e.subject_id = s.subject_id
                    LEFT JOIN users u ON e.created_by = u.user_id
                    ORDER BY e.created_at DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("ExamDAO::findAllWithDetails error: " . $e->getMessage());
            return [];
        }
    }

    public function findByIdWithDetails(int $exam_id): ?array
    {
        try {
            $sql = "SELECT e.*, s.subject_name, s.subject_code, u.full_name as creator_name
                    FROM {$this->table} e
                    LEFT JOIN subjects s ON e.subject_id = s.subject_id
                    LEFT JOIN users u ON e.created_by = u.user_id
                    WHERE e.exam_id = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$exam_id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $data ?: null;
        } catch (PDOException $e) {
            error_log("ExamDAO::findByIdWithDetails error: " . $e->getMessage());
            return null;
        }
    }
}