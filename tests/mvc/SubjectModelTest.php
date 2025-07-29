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
            'subject_code' => 'CS1011',
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