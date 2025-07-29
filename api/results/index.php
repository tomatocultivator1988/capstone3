<?php
/**
 * TDD Step 122: Exam Results API endpoint
 * GREEN PHASE: Route result requests to ExamResultController
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use App\Controllers\ExamResultController;

$examResultController = new ExamResultController();

// Route based on action parameter
$action = $_GET['action'] ?? 'index';

switch ($action) {
    case 'store':
        $examResultController->store();
        break;
    case 'by_exam':
        $examResultController->getByExam();
        break;
    case 'by_student':
        $examResultController->getByStudent();
        break;
    case 'analytics':
        $examResultController->getExamAnalytics();
        break;
    case 'performance':
        $examResultController->getStudentPerformance();
        break;
    case 'show':
        $examResultController->show();
        break;
    case 'check_status':
        $examResultController->checkExamStatus();
        break;
    default:
        http_response_code(404);
        echo json_encode([
            'status' => 'error',
            'message' => 'Action not found'
        ]);
}