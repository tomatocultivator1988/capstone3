<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Controllers\SubjectController;

$subjectController = new SubjectController();

// Route based on request method and parameters
$action = $_GET['action'] ?? 'index';

switch ($action) {
    case 'index':
        $subjectController->index();
        break;
    case 'show':
        $subjectController->show();
        break;
    case 'store':
        $subjectController->store();
        break;
    case 'update':
        $subjectController->update();
        break;
    case 'delete':
        $subjectController->delete();
        break;
    case 'assign_faculty':
        $subjectController->assignFaculty();
        break;
    case 'by_faculty':
        $subjectController->getByFaculty();
        break;
    default:
        http_response_code(404);
        echo json_encode([
            'status' => 'error',
            'message' => 'Action not found'
        ]);
}