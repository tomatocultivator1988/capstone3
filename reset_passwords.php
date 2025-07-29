<?php
// Password reset script
require_once __DIR__ . '/vendor/autoload.php';

use App\Config\Database;

echo "=== Password Reset Script ===\n\n";

try {
    // Get database connection
    $db = Database::getInstance();
    $connection = $db->getConnection();
    
    // New password for all users
    $newPassword = 'password123';
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    
    echo "New password: $newPassword\n";
    echo "Hashed password: $hashedPassword\n\n";
    
    // Update all users
    $sql = "UPDATE users SET password = ?";
    $stmt = $connection->prepare($sql);
    $result = $stmt->execute([$hashedPassword]);
    
    if ($result) {
        $rowCount = $stmt->rowCount();
        echo "✅ Successfully updated $rowCount users\n";
        echo "All users now have password: $newPassword\n\n";
        
        // Verify the update
        $verifySql = "SELECT school_id, role FROM users LIMIT 5";
        $verifyStmt = $connection->prepare($verifySql);
        $verifyStmt->execute();
        $users = $verifyStmt->fetchAll();
        
        echo "Sample users updated:\n";
        foreach ($users as $user) {
            echo "- {$user['school_id']} ({$user['role']})\n";
        }
        
    } else {
        echo "❌ Failed to update passwords\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}