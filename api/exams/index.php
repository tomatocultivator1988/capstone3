<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Controllers\ExamController;

$examController = new ExamController();

// Route based on request method and parameters
$action = $_GET['action'] ?? 'index';

switch ($action) {
    case 'index':
        $examController->index();
        break;
    case 'show':
        $examController->show();
        break;
    case 'by_subject':
        $examController->getBySubject();
        break;
    case 'store':
        $examController->store();
        break;
    case 'update':
        $examController->update();
        break;
    case 'delete':
        $examController->delete();
        break;
    case 'update_status':
        $examController->updateStatus();
        break;
    case 'active':
        $examController->getActiveExams();
        break;
    default:
        http_response_code(404);
        echo json_encode([
            'status' => 'error',
            'message' => 'Action not found'
        ]);
}