<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Controllers\UserController;

$userController = new UserController();

// Route based on request method and parameters
$action = $_GET['action'] ?? 'index';

switch ($action) {
    case 'index':
        $userController->index();
        break;
    case 'show':
        $userController->show();
        break;
    case 'by_role':
        $userController->getUsersByRole();
        break;
    case 'students_by_year_section':
        $userController->getStudentsByYearSection();
        break;
    case 'store':
        $userController->store();
        break;
    case 'update':
        $userController->update();
        break;
    case 'delete':
        $userController->delete();
        break;
    default:
        http_response_code(404);
        echo json_encode([
            'status' => 'error',
            'message' => 'Action not found.'
        ]);
}