<?php
/**
 * TDD Step 121: Questions API endpoint
 * GREEN PHASE: Route question requests to QuestionController
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use App\Controllers\QuestionController;

$questionController = new QuestionController();

// Route based on action parameter
$action = $_GET['action'] ?? 'index';

switch ($action) {
    case 'by_exam':
        $questionController->getByExam();
        break;
    case 'store':
        $questionController->store();
        break;
    case 'update':
        $questionController->update();
        break;
    case 'delete':
        $questionController->delete();
        break;
    case 'reorder':
        $questionController->reorder();
        break;
    case 'exam_stats':
        $questionController->getExamStats();
        break;
    default:
        http_response_code(404);
        echo json_encode([
            'status' => 'error',
            'message' => 'Action not found'
        ]);
}