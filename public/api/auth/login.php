<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../../vendor/autoload.php';

use Service\Impl\AuthServiceImpl;
use Dao\Impl\UserDAOImpl;

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

try {
    // Get POST data
    $school_id = $_POST['school_id'] ?? '';
    $password = $_POST['password'] ?? '';

    // Validate input
    if (empty($school_id) || empty($password)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'School ID and password are required'
        ]);
        exit;
    }

    // Create real instances for authentication
    $userDAO = new UserDAOImpl();
    $authService = new AuthServiceImpl($userDAO);

    // Attempt login
    $user = $authService->login($school_id, $password);

    if ($user) {
        // Login successful
        echo json_encode([
            'status' => 'success',
            'message' => 'Login successful',
            'role' => $user['role'],
            'user' => [
                'school_id' => $user['school_id'],
                'full_name' => $user['full_name'],
                'role' => $user['role']
            ]
        ]);
    } else {
        // Login failed
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid school ID or password'
        ]);
    }

} catch (Exception $e) {
    error_log("Login API error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'An error occurred during login'
    ]);
}
?>