# TDD ITERATION 2: Advanced Features Implementation
## Subject Management, Exam Creation, Question Bank & Results Analytics

### 📋 Iteration 2 Overview
**Previous**: Authentication System, User Management, Basic Dashboard  
**Current**: Advanced examination system features  
**Methodology**: Strict TDD (RED-GREEN-REFACTOR)  
**Features**: 4 major systems with full test coverage  

---

## 🔄 ITERATION 2 - TDD CYCLE 5: SUBJECT MANAGEMENT SYSTEM

### 🔴 RED PHASE 5.1: Subject Management Tests

**Step 61: Subject Model Tests**
**File**: `tests/mvc/SubjectModelTest.php`
**TDD Principle**: Test subject CRUD operations first

```php
<?php

use PHPUnit\Framework\TestCase;
use App\Models\Subject;

/**
 * TDD Step 61: Subject model tests
 * RED PHASE: These tests will fail (no Subject model exists)
 */
class SubjectModelTest extends TestCase
{
    private $subjectModel;

    public function setUp(): void
    {
        $this->subjectModel = new Subject();
    }

    /**
     * TDD Step 62: Test subject creation
     * RED PHASE: Will fail - no create method exists
     */
    public function test_subject_can_be_created()
    {
        $subjectData = [
            'subject_code' => 'CS101',
            'subject_name' => 'Introduction to Computer Science',
            'description' => 'Basic computer science concepts',
            'units' => 3,
            'year_level' => '1st Year',
            'semester' => '1st Semester'
        ];

        // This will fail initially
        $subjectId = $this->subjectModel->create($subjectData);
        $this->assertIsNumeric($subjectId);
        $this->assertGreaterThan(0, $subjectId);
    }

    /**
     * TDD Step 63: Test get all subjects
     * RED PHASE: Will fail - no getAllSubjects method exists
     */
    public function test_can_get_all_subjects()
    {
        // This will fail initially
        $subjects = $this->subjectModel->getAllSubjects();
        $this->assertIsArray($subjects);
    }

    /**
     * TDD Step 64: Test subject update
     * RED PHASE: Will fail - no update method exists
     */
    public function test_subject_can_be_updated()
    {
        $updateData = [
            'subject_name' => 'Updated Subject Name',
            'units' => 4
        ];

        // This will fail initially
        $result = $this->subjectModel->update(1, $updateData);
        $this->assertTrue($result);
    }

    /**
     * TDD Step 65: Test subject deletion
     * RED PHASE: Will fail - no delete method exists
     */
    public function test_subject_can_be_deleted()
    {
        // This will fail initially
        $result = $this->subjectModel->delete(999);
        $this->assertTrue($result);
    }

    /**
     * TDD Step 66: Test assign faculty to subject
     * RED PHASE: Will fail - no assignFaculty method exists
     */
    public function test_can_assign_faculty_to_subject()
    {
        // This will fail initially
        $result = $this->subjectModel->assignFaculty(1, 2);
        $this->assertTrue($result);
    }
}
```

**TDD Status**: 🔴 RED - Tests created, all FAILING

---

### 🟢 GREEN PHASE 5.1: Subject Model Implementation

**Step 67: Subject Model Creation**
**File**: `src/App/Models/Subject.php`
**TDD Principle**: Create minimal model to make tests pass

```php
<?php

namespace App\Models;

use App\Config\Database;
use PDO;
use PDOException;

/**
 * TDD Step 67: Subject Model class
 * GREEN PHASE: Implementation to make subject tests pass
 * Handles subject data access operations
 */
class Subject
{
    private $db;
    private $table = 'subjects';

    /**
     * TDD: Constructor - Initialize database connection
     */
    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
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
     * TDD Step 69: Get all subjects method
     * GREEN PHASE: Method to make getAllSubjects test pass
     * 
     * @return array List of subjects
     */
    public function getAllSubjects()
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} ORDER BY subject_code ASC");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
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
     * TDD Step 71: Delete subject method
     * GREEN PHASE: Method to make delete test pass
     * 
     * @param int $subject_id
     * @return bool
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
     * TDD Step 73: Find subject by ID method
     * GREEN PHASE: Additional method for completeness
     * 
     * @param int $subject_id
     * @return array|false
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
```

**TDD Status**: 🟢 GREEN - Subject model tests should now PASS!

---

## 🔄 ITERATION 2 - TDD CYCLE 6: EXAM CREATION SYSTEM

### 🔴 RED PHASE 6.1: Exam Management Tests

**Step 75: Exam Model Tests**
**File**: `tests/mvc/ExamModelTest.php`
**TDD Principle**: Test exam CRUD operations first

```php
<?php

use PHPUnit\Framework\TestCase;
use App\Models\Exam;

/**
 * TDD Step 75: Exam model tests
 * RED PHASE: These tests will fail (no enhanced Exam model exists)
 */
class ExamModelTest extends TestCase
{
    private $examModel;

    public function setUp(): void
    {
        $this->examModel = new Exam();
    }

    /**
     * TDD Step 76: Test exam creation
     * RED PHASE: Will fail - enhanced create method doesn't exist
     */
    public function test_exam_can_be_created()
    {
        $examData = [
            'exam_title' => 'Midterm Examination',
            'subject_id' => 1,
            'created_by' => 2,
            'duration' => 120,
            'total_points' => 100,
            'instructions' => 'Read all questions carefully',
            'exam_date' => '2024-03-15',
            'start_time' => '09:00:00',
            'end_time' => '11:00:00',
            'status' => 'draft'
        ];

        // This will fail initially
        $examId = $this->examModel->create($examData);
        $this->assertIsNumeric($examId);
        $this->assertGreaterThan(0, $examId);
    }

    /**
     * TDD Step 77: Test get exams by subject
     * RED PHASE: Will fail - method doesn't exist
     */
    public function test_can_get_exams_by_subject()
    {
        // This will fail initially
        $exams = $this->examModel->getExamsBySubject(1);
        $this->assertIsArray($exams);
    }

    /**
     * TDD Step 78: Test exam status update
     * RED PHASE: Will fail - method doesn't exist
     */
    public function test_can_update_exam_status()
    {
        // This will fail initially
        $result = $this->examModel->updateStatus(1, 'published');
        $this->assertTrue($result);
    }

    /**
     * TDD Step 79: Test get active exams
     * RED PHASE: Will fail - method doesn't exist
     */
    public function test_can_get_active_exams()
    {
        // This will fail initially
        $exams = $this->examModel->getActiveExams();
        $this->assertIsArray($exams);
    }
}
```

**TDD Status**: 🔴 RED - Tests created, all FAILING

---

### 🟢 GREEN PHASE 6.1: Enhanced Exam Model

**Step 80: Enhanced Exam Model**
**File**: `src/App/Models/Exam.php` (Enhanced version)
**TDD Principle**: Enhance existing model to make tests pass

```php
<?php

namespace App\Models;

use App\Config\Database;
use PDO;
use PDOException;

/**
 * TDD Step 80: Enhanced Exam Model class
 * GREEN PHASE: Enhanced implementation for exam management
 * Handles comprehensive exam operations
 */
class Exam
{
    private $db;
    private $table = 'exams';

    /**
     * TDD: Constructor - Initialize database connection
     */
    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * TDD Step 81: Enhanced create exam method
     * GREEN PHASE: Method to make create test pass
     * 
     * @param array $data Exam data
     * @return int|false Exam ID if successful, false otherwise
     */
    public function create($data)
    {
        try {
            $sql = "INSERT INTO {$this->table} (exam_title, subject_id, created_by, duration, total_points, instructions, exam_date, start_time, end_time, status, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $data['exam_title'],
                $data['subject_id'],
                $data['created_by'],
                $data['duration'],
                $data['total_points'],
                $data['instructions'] ?? null,
                $data['exam_date'],
                $data['start_time'],
                $data['end_time'],
                $data['status'] ?? 'draft'
            ]);

            return $result ? $this->db->lastInsertId() : false;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * TDD Step 82: Get all exams method
     * GREEN PHASE: Enhanced method with joins
     * 
     * @return array List of exams with subject and creator info
     */
    public function getAllExams()
    {
        try {
            $sql = "SELECT e.*, s.subject_name, s.subject_code, u.full_name as creator_name
                    FROM {$this->table} e
                    LEFT JOIN subjects s ON e.subject_id = s.subject_id
                    LEFT JOIN users u ON e.created_by = u.user_id
                    ORDER BY e.created_at DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * TDD Step 83: Get exams by subject method
     * GREEN PHASE: Method to make getExamsBySubject test pass
     * 
     * @param int $subject_id
     * @return array
     */
    public function getExamsBySubject($subject_id)
    {
        try {
            $sql = "SELECT e.*, s.subject_name, s.subject_code, u.full_name as creator_name
                    FROM {$this->table} e
                    LEFT JOIN subjects s ON e.subject_id = s.subject_id
                    LEFT JOIN users u ON e.created_by = u.user_id
                    WHERE e.subject_id = ?
                    ORDER BY e.exam_date DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$subject_id]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * TDD Step 84: Update exam status method
     * GREEN PHASE: Method to make updateStatus test pass
     * 
     * @param int $exam_id
     * @param string $status
     * @return bool
     */
    public function updateStatus($exam_id, $status)
    {
        try {
            $sql = "UPDATE {$this->table} SET status = ?, updated_at = NOW() WHERE exam_id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$status, $exam_id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * TDD Step 85: Get active exams method
     * GREEN PHASE: Method to make getActiveExams test pass
     * 
     * @return array
     */
    public function getActiveExams()
    {
        try {
            $sql = "SELECT e.*, s.subject_name, s.subject_code
                    FROM {$this->table} e
                    LEFT JOIN subjects s ON e.subject_id = s.subject_id
                    WHERE e.status = 'published' 
                    AND e.exam_date >= CURDATE()
                    ORDER BY e.exam_date ASC, e.start_time ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * TDD Step 86: Find exam by ID method
     * GREEN PHASE: Method for detailed exam retrieval
     * 
     * @param int $exam_id
     * @return array|false
     */
    public function findById($exam_id)
    {
        try {
            $sql = "SELECT e.*, s.subject_name, s.subject_code, u.full_name as creator_name
                    FROM {$this->table} e
                    LEFT JOIN subjects s ON e.subject_id = s.subject_id
                    LEFT JOIN users u ON e.created_by = u.user_id
                    WHERE e.exam_id = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$exam_id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * TDD Step 87: Update exam method
     * GREEN PHASE: Method for exam updates
     * 
     * @param int $exam_id
     * @param array $data
     * @return bool
     */
    public function update($exam_id, $data)
    {
        try {
            $setParts = [];
            $values = [];

            $allowedFields = ['exam_title', 'duration', 'total_points', 'instructions', 'exam_date', 'start_time', 'end_time', 'status'];
            foreach ($data as $key => $value) {
                if (in_array($key, $allowedFields)) {
                    $setParts[] = "{$key} = ?";
                    $values[] = $value;
                }
            }

            if (empty($setParts)) {
                return false;
            }

            $values[] = $exam_id;
            
            $sql = "UPDATE {$this->table} SET " . implode(', ', $setParts) . ", updated_at = NOW() WHERE exam_id = ?";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($values);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * TDD Step 88: Delete exam method
     * GREEN PHASE: Method for exam deletion
     * 
     * @param int $exam_id
     * @return bool
     */
    public function delete($exam_id)
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE exam_id = ?");
            return $stmt->execute([$exam_id]);
        } catch (PDOException $e) {
            return false;
        }
    }
}
```

**TDD Status**: 🟢 GREEN - Exam model tests should now PASS!

---

## 🔄 ITERATION 2 - TDD CYCLE 7: QUESTION BANK SYSTEM

### 🔴 RED PHASE 7.1: Question Bank Tests

**Step 89: Question Model Tests**
**File**: `tests/mvc/QuestionModelTest.php`
**TDD Principle**: Test question management first

```php
<?php

use PHPUnit\Framework\TestCase;
use App\Models\Question;

/**
 * TDD Step 89: Question model tests
 * RED PHASE: These tests will fail (no Question model exists)
 */
class QuestionModelTest extends TestCase
{
    private $questionModel;

    public function setUp(): void
    {
        $this->questionModel = new Question();
    }

    /**
     * TDD Step 90: Test question creation
     * RED PHASE: Will fail - no create method exists
     */
    public function test_question_can_be_created()
    {
        $questionData = [
            'exam_id' => 1,
            'question_text' => 'What is the capital of France?',
            'question_type' => 'multiple_choice',
            'points' => 5,
            'order_number' => 1,
            'options' => json_encode([
                'A' => 'London',
                'B' => 'Berlin', 
                'C' => 'Paris',
                'D' => 'Madrid'
            ]),
            'correct_answer' => 'C'
        ];

        // This will fail initially
        $questionId = $this->questionModel->create($questionData);
        $this->assertIsNumeric($questionId);
        $this->assertGreaterThan(0, $questionId);
    }

    /**
     * TDD Step 91: Test get questions by exam
     * RED PHASE: Will fail - method doesn't exist
     */
    public function test_can_get_questions_by_exam()
    {
        // This will fail initially
        $questions = $this->questionModel->getQuestionsByExam(1);
        $this->assertIsArray($questions);
    }

    /**
     * TDD Step 92: Test question update
     * RED PHASE: Will fail - method doesn't exist
     */
    public function test_question_can_be_updated()
    {
        $updateData = [
            'question_text' => 'Updated question text',
            'points' => 10
        ];

        // This will fail initially
        $result = $this->questionModel->update(1, $updateData);
        $this->assertTrue($result);
    }

    /**
     * TDD Step 93: Test question deletion
     * RED PHASE: Will fail - method doesn't exist
     */
    public function test_question_can_be_deleted()
    {
        // This will fail initially
        $result = $this->questionModel->delete(999);
        $this->assertTrue($result);
    }

    /**
     * TDD Step 94: Test reorder questions
     * RED PHASE: Will fail - method doesn't exist
     */
    public function test_can_reorder_questions()
    {
        $questionOrder = [3, 1, 2]; // New order for questions

        // This will fail initially
        $result = $this->questionModel->reorderQuestions(1, $questionOrder);
        $this->assertTrue($result);
    }
}
```

**TDD Status**: 🔴 RED - Tests created, all FAILING

---

### 🟢 GREEN PHASE 7.1: Question Model Implementation

**Step 95: Question Model Creation**
**File**: `src/App/Models/Question.php`
**TDD Principle**: Create model to make tests pass

```php
<?php

namespace App\Models;

use App\Config\Database;
use PDO;
use PDOException;

/**
 * TDD Step 95: Question Model class
 * GREEN PHASE: Implementation to make question tests pass
 * Handles question bank operations
 */
class Question
{
    private $db;
    private $table = 'questions';

    /**
     * TDD: Constructor - Initialize database connection
     */
    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * TDD Step 96: Create question method
     * GREEN PHASE: Method to make create test pass
     * 
     * @param array $data Question data
     * @return int|false Question ID if successful, false otherwise
     */
    public function create($data)
    {
        try {
            $sql = "INSERT INTO {$this->table} (exam_id, question_text, question_type, points, order_number, options, correct_answer, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $data['exam_id'],
                $data['question_text'],
                $data['question_type'],
                $data['points'],
                $data['order_number'],
                $data['options'] ?? null,
                $data['correct_answer'] ?? null
            ]);

            return $result ? $this->db->lastInsertId() : false;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * TDD Step 97: Get questions by exam method
     * GREEN PHASE: Method to make getQuestionsByExam test pass
     * 
     * @param int $exam_id
     * @return array
     */
    public function getQuestionsByExam($exam_id)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE exam_id = ? ORDER BY order_number ASC");
            $stmt->execute([$exam_id]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * TDD Step 98: Update question method
     * GREEN PHASE: Method to make update test pass
     * 
     * @param int $question_id
     * @param array $data
     * @return bool
     */
    public function update($question_id, $data)
    {
        try {
            $setParts = [];
            $values = [];

            $allowedFields = ['question_text', 'question_type', 'points', 'order_number', 'options', 'correct_answer'];
            foreach ($data as $key => $value) {
                if (in_array($key, $allowedFields)) {
                    $setParts[] = "{$key} = ?";
                    $values[] = $value;
                }
            }

            if (empty($setParts)) {
                return false;
            }

            $values[] = $question_id;
            
            $sql = "UPDATE {$this->table} SET " . implode(', ', $setParts) . ", updated_at = NOW() WHERE question_id = ?";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($values);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * TDD Step 99: Delete question method
     * GREEN PHASE: Method to make delete test pass
     * 
     * @param int $question_id
     * @return bool
     */
    public function delete($question_id)
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE question_id = ?");
            return $stmt->execute([$question_id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * TDD Step 100: Reorder questions method
     * GREEN PHASE: Method to make reorderQuestions test pass
     * 
     * @param int $exam_id
     * @param array $questionOrder Array of question IDs in new order
     * @return bool
     */
    public function reorderQuestions($exam_id, $questionOrder)
    {
        try {
            $this->db->beginTransaction();

            foreach ($questionOrder as $index => $questionId) {
                $sql = "UPDATE {$this->table} SET order_number = ?, updated_at = NOW() 
                        WHERE question_id = ? AND exam_id = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$index + 1, $questionId, $exam_id]);
            }

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollback();
            return false;
        }
    }

    /**
     * TDD Step 101: Find question by ID method
     * GREEN PHASE: Method for detailed question retrieval
     * 
     * @param int $question_id
     * @return array|false
     */
    public function findById($question_id)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE question_id = ?");
            $stmt->execute([$question_id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * TDD Step 102: Get question count by exam method
     * GREEN PHASE: Method for exam statistics
     * 
     * @param int $exam_id
     * @return int
     */
    public function getQuestionCountByExam($exam_id)
    {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM {$this->table} WHERE exam_id = ?");
            $stmt->execute([$exam_id]);
            $result = $stmt->fetch();
            return $result['count'] ?? 0;
        } catch (PDOException $e) {
            return 0;
        }
    }

    /**
     * TDD Step 103: Get total points by exam method
     * GREEN PHASE: Method for exam scoring
     * 
     * @param int $exam_id
     * @return int
     */
    public function getTotalPointsByExam($exam_id)
    {
        try {
            $stmt = $this->db->prepare("SELECT SUM(points) as total FROM {$this->table} WHERE exam_id = ?");
            $stmt->execute([$exam_id]);
            $result = $stmt->fetch();
            return $result['total'] ?? 0;
        } catch (PDOException $e) {
            return 0;
        }
    }
}
```

**TDD Status**: 🟢 GREEN - Question model tests should now PASS!

---

## 🔄 ITERATION 2 - TDD CYCLE 8: RESULTS & ANALYTICS SYSTEM

### 🔴 RED PHASE 8.1: Results Analytics Tests

**Step 104: ExamResult Model Tests**
**File**: `tests/mvc/ExamResultModelTest.php`
**TDD Principle**: Test results management first

```php
<?php

use PHPUnit\Framework\TestCase;
use App\Models\ExamResult;

/**
 * TDD Step 104: ExamResult model tests
 * RED PHASE: These tests will fail (no ExamResult model exists)
 */
class ExamResultModelTest extends TestCase
{
    private $examResultModel;

    public function setUp(): void
    {
        $this->examResultModel = new ExamResult();
    }

    /**
     * TDD Step 105: Test exam result creation
     * RED PHASE: Will fail - no create method exists
     */
    public function test_exam_result_can_be_created()
    {
        $resultData = [
            'exam_id' => 1,
            'student_id' => 3,
            'answers' => json_encode([
                '1' => 'A',
                '2' => 'C',
                '3' => 'B'
            ]),
            'score' => 85,
            'total_points' => 100,
            'time_taken' => 75,
            'status' => 'completed'
        ];

        // This will fail initially
        $resultId = $this->examResultModel->create($resultData);
        $this->assertIsNumeric($resultId);
        $this->assertGreaterThan(0, $resultId);
    }

    /**
     * TDD Step 106: Test get results by exam
     * RED PHASE: Will fail - method doesn't exist
     */
    public function test_can_get_results_by_exam()
    {
        // This will fail initially
        $results = $this->examResultModel->getResultsByExam(1);
        $this->assertIsArray($results);
    }

    /**
     * TDD Step 107: Test get results by student
     * RED PHASE: Will fail - method doesn't exist
     */
    public function test_can_get_results_by_student()
    {
        // This will fail initially
        $results = $this->examResultModel->getResultsByStudent(3);
        $this->assertIsArray($results);
    }

    /**
     * TDD Step 108: Test exam analytics
     * RED PHASE: Will fail - method doesn't exist
     */
    public function test_can_get_exam_analytics()
    {
        // This will fail initially
        $analytics = $this->examResultModel->getExamAnalytics(1);
        $this->assertIsArray($analytics);
        $this->assertArrayHasKey('average_score', $analytics);
        $this->assertArrayHasKey('total_attempts', $analytics);
    }

    /**
     * TDD Step 109: Test student performance analytics
     * RED PHASE: Will fail - method doesn't exist
     */
    public function test_can_get_student_performance()
    {
        // This will fail initially
        $performance = $this->examResultModel->getStudentPerformance(3);
        $this->assertIsArray($performance);
    }
}
```

**TDD Status**: 🔴 RED - Tests created, all FAILING

---

### 🟢 GREEN PHASE 8.1: ExamResult Model Implementation

**Step 110: ExamResult Model Creation**
**File**: `src/App/Models/ExamResult.php`
**TDD Principle**: Create model to make tests pass

```php
<?php

namespace App\Models;

use App\Config\Database;
use PDO;
use PDOException;

/**
 * TDD Step 110: ExamResult Model class
 * GREEN PHASE: Implementation to make result tests pass
 * Handles exam results and analytics
 */
class ExamResult
{
    private $db;
    private $table = 'exam_results';

    /**
     * TDD: Constructor - Initialize database connection
     */
    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * TDD Step 111: Create exam result method
     * GREEN PHASE: Method to make create test pass
     * 
     * @param array $data Result data
     * @return int|false Result ID if successful, false otherwise
     */
    public function create($data)
    {
        try {
            $sql = "INSERT INTO {$this->table} (exam_id, student_id, answers, score, total_points, time_taken, status, submitted_at, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW(), NOW())";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $data['exam_id'],
                $data['student_id'],
                $data['answers'],
                $data['score'],
                $data['total_points'],
                $data['time_taken'],
                $data['status']
            ]);

            return $result ? $this->db->lastInsertId() : false;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * TDD Step 112: Get results by exam method
     * GREEN PHASE: Method to make getResultsByExam test pass
     * 
     * @param int $exam_id
     * @return array
     */
    public function getResultsByExam($exam_id)
    {
        try {
            $sql = "SELECT er.*, u.full_name as student_name, u.school_id
                    FROM {$this->table} er
                    LEFT JOIN users u ON er.student_id = u.user_id
                    WHERE er.exam_id = ?
                    ORDER BY er.score DESC, er.submitted_at ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$exam_id]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * TDD Step 113: Get results by student method
     * GREEN PHASE: Method to make getResultsByStudent test pass
     * 
     * @param int $student_id
     * @return array
     */
    public function getResultsByStudent($student_id)
    {
        try {
            $sql = "SELECT er.*, e.exam_title, s.subject_name, s.subject_code
                    FROM {$this->table} er
                    LEFT JOIN exams e ON er.exam_id = e.exam_id
                    LEFT JOIN subjects s ON e.subject_id = s.subject_id
                    WHERE er.student_id = ?
                    ORDER BY er.submitted_at DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$student_id]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * TDD Step 114: Get exam analytics method
     * GREEN PHASE: Method to make getExamAnalytics test pass
     * 
     * @param int $exam_id
     * @return array
     */
    public function getExamAnalytics($exam_id)
    {
        try {
            $sql = "SELECT 
                        COUNT(*) as total_attempts,
                        AVG(score) as average_score,
                        MAX(score) as highest_score,
                        MIN(score) as lowest_score,
                        AVG(time_taken) as average_time,
                        COUNT(CASE WHEN score >= (total_points * 0.75) THEN 1 END) as passed_count
                    FROM {$this->table} 
                    WHERE exam_id = ? AND status = 'completed'";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$exam_id]);
            $result = $stmt->fetch();
            
            // Calculate pass rate
            $passRate = $result['total_attempts'] > 0 
                ? ($result['passed_count'] / $result['total_attempts']) * 100 
                : 0;
            
            return [
                'total_attempts' => (int)$result['total_attempts'],
                'average_score' => round((float)$result['average_score'], 2),
                'highest_score' => (int)$result['highest_score'],
                'lowest_score' => (int)$result['lowest_score'],
                'average_time' => round((float)$result['average_time'], 2),
                'pass_rate' => round($passRate, 2),
                'passed_count' => (int)$result['passed_count']
            ];
        } catch (PDOException $e) {
            return [
                'total_attempts' => 0,
                'average_score' => 0,
                'highest_score' => 0,
                'lowest_score' => 0,
                'average_time' => 0,
                'pass_rate' => 0,
                'passed_count' => 0
            ];
        }
    }

    /**
     * TDD Step 115: Get student performance method
     * GREEN PHASE: Method to make getStudentPerformance test pass
     * 
     * @param int $student_id
     * @return array
     */
    public function getStudentPerformance($student_id)
    {
        try {
            $sql = "SELECT 
                        COUNT(*) as total_exams,
                        AVG(score) as average_score,
                        MAX(score) as best_score,
                        MIN(score) as worst_score,
                        AVG((score/total_points)*100) as average_percentage,
                        COUNT(CASE WHEN score >= (total_points * 0.75) THEN 1 END) as passed_exams
                    FROM {$this->table} 
                    WHERE student_id = ? AND status = 'completed'";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$student_id]);
            $result = $stmt->fetch();
            
            // Get subject-wise performance
            $subjectSql = "SELECT 
                            s.subject_name,
                            s.subject_code,
                            COUNT(*) as exam_count,
                            AVG(er.score) as avg_score,
                            AVG((er.score/er.total_points)*100) as avg_percentage
                        FROM {$this->table} er
                        LEFT JOIN exams e ON er.exam_id = e.exam_id
                        LEFT JOIN subjects s ON e.subject_id = s.subject_id
                        WHERE er.student_id = ? AND er.status = 'completed'
                        GROUP BY s.subject_id
                        ORDER BY avg_percentage DESC";
            
            $subjectStmt = $this->db->prepare($subjectSql);
            $subjectStmt->execute([$student_id]);
            $subjectPerformance = $subjectStmt->fetchAll();
            
            return [
                'overall' => [
                    'total_exams' => (int)$result['total_exams'],
                    'average_score' => round((float)$result['average_score'], 2),
                    'best_score' => (int)$result['best_score'],
                    'worst_score' => (int)$result['worst_score'],
                    'average_percentage' => round((float)$result['average_percentage'], 2),
                    'passed_exams' => (int)$result['passed_exams']
                ],
                'by_subject' => $subjectPerformance
            ];
        } catch (PDOException $e) {
            return [
                'overall' => [
                    'total_exams' => 0,
                    'average_score' => 0,
                    'best_score' => 0,
                    'worst_score' => 0,
                    'average_percentage' => 0,
                    'passed_exams' => 0
                ],
                'by_subject' => []
            ];
        }
    }

    /**
     * TDD Step 116: Find result by ID method
     * GREEN PHASE: Method for detailed result retrieval
     * 
     * @param int $result_id
     * @return array|false
     */
    public function findById($result_id)
    {
        try {
            $sql = "SELECT er.*, e.exam_title, s.subject_name, u.full_name as student_name
                    FROM {$this->table} er
                    LEFT JOIN exams e ON er.exam_id = e.exam_id
                    LEFT JOIN subjects s ON e.subject_id = s.subject_id
                    LEFT JOIN users u ON er.student_id = u.user_id
                    WHERE er.result_id = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$result_id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * TDD Step 117: Check if student has taken exam method
     * GREEN PHASE: Method to prevent duplicate attempts
     * 
     * @param int $exam_id
     * @param int $student_id
     * @return bool
     */
    public function hasStudentTakenExam($exam_id, $student_id)
    {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM {$this->table} WHERE exam_id = ? AND student_id = ?");
            $stmt->execute([$exam_id, $student_id]);
            $result = $stmt->fetch();
            return $result['count'] > 0;
        } catch (PDOException $e) {
            return false;
        }
    }
}
```

**TDD Status**: 🟢 GREEN - ExamResult model tests should now PASS!

---

## 📊 CONTROLLERS & API ENDPOINTS

Now let me create the controllers and API endpoints for all the new features:

**Step 118: Subject Controller**
**File**: `src/App/Controllers/SubjectController.php`

```php
<?php

namespace App\Controllers;

use App\Models\Subject;
use App\Controllers\AuthController;

/**
 * TDD Step 118: Subject Controller
 * GREEN PHASE: Handle subject management API requests
 */
class SubjectController
{
    private $subjectModel;
    private $authController;

    public function __construct()
    {
        $this->subjectModel = new Subject();
        $this->authController = new AuthController();
    }

    /**
     * Get all subjects
     */
    public function index()
    {
        header('Content-Type: application/json');
        $this->authController->requireAuth();

        try {
            $subjects = $this->subjectModel->getAllSubjects();
            
            echo json_encode([
                'status' => 'success',
                'data' => $subjects
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to retrieve subjects.'
            ]);
        }
    }

    /**
     * Create new subject
     */
    public function store()
    {
        header('Content-Type: application/json');
        $this->authController->requireRole('admin');

        $required = ['subject_code', 'subject_name', 'units', 'year_level', 'semester'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                http_response_code(400);
                echo json_encode([
                    'status' => 'error',
                    'message' => "Field {$field} is required."
                ]);
                return;
            }
        }

        try {
            $subjectData = [
                'subject_code' => $_POST['subject_code'],
                'subject_name' => $_POST['subject_name'],
                'description' => $_POST['description'] ?? null,
                'units' => $_POST['units'],
                'year_level' => $_POST['year_level'],
                'semester' => $_POST['semester']
            ];

            $subjectId = $this->subjectModel->create($subjectData);

            if ($subjectId) {
                http_response_code(201);
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Subject created successfully.',
                    'subject_id' => $subjectId
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Failed to create subject.'
                ]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'An error occurred while creating subject.'
            ]);
        }
    }

    /**
     * Update subject
     */
    public function update()
    {
        header('Content-Type: application/json');
        $this->authController->requireRole('admin');

        $subjectId = $_POST['subject_id'] ?? null;
        if (!$subjectId) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Subject ID is required.'
            ]);
            return;
        }

        try {
            $updateData = [];
            $allowedFields = ['subject_code', 'subject_name', 'description', 'units', 'year_level', 'semester'];
            
            foreach ($allowedFields as $field) {
                if (isset($_POST[$field])) {
                    $updateData[$field] = $_POST[$field];
                }
            }

            if (empty($updateData)) {
                http_response_code(400);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'No valid fields to update.'
                ]);
                return;
            }

            $result = $this->subjectModel->update($subjectId, $updateData);

            if ($result) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Subject updated successfully.'
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Failed to update subject.'
                ]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'An error occurred while updating subject.'
            ]);
        }
    }

    /**
     * Delete subject
     */
    public function delete()
    {
        header('Content-Type: application/json');
        $this->authController->requireRole('admin');

        $subjectId = $_POST['subject_id'] ?? null;
        if (!$subjectId) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Subject ID is required.'
            ]);
            return;
        }

        try {
            $result = $this->subjectModel->delete($subjectId);

            if ($result) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Subject deleted successfully.'
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Failed to delete subject.'
                ]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'An error occurred while deleting subject.'
            ]);
        }
    }

    /**
     * Assign faculty to subject
     */
    public function assignFaculty()
    {
        header('Content-Type: application/json');
        $this->authController->requireRole('admin');

        $subjectId = $_POST['subject_id'] ?? null;
        $facultyId = $_POST['faculty_id'] ?? null;

        if (!$subjectId || !$facultyId) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Subject ID and Faculty ID are required.'
            ]);
            return;
        }

        try {
            $result = $this->subjectModel->assignFaculty($subjectId, $facultyId);

            if ($result) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Faculty assigned to subject successfully.'
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Failed to assign faculty to subject.'
                ]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'An error occurred while assigning faculty.'
            ]);
        }
    }
}
```

Now I'll update the existing API endpoints and create new ones to support all the features. Let me continue with the implementation: