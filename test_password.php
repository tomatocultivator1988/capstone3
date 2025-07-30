<?php
// Test password hashing
echo "=== Password Hash Test ===\n\n";

$storedHash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
$testPasswords = [
    'password123',
    'password',
    'admin123',
    'admin',
    'ADMIN001',
    'ADMIN001Admin User',  // school_id + full_name (from User model create method)
    'password123password123',
    '123456',
    'test'
];

echo "Stored hash: $storedHash\n\n";

foreach ($testPasswords as $password) {
    $result = password_verify($password, $storedHash);
    echo "Testing '$password': " . ($result ? '✅ MATCH' : '❌ NO MATCH') . "\n";
}

echo "\n=== Generating new hash for 'password123' ===\n";
$newHash = password_hash('password123', PASSWORD_DEFAULT);
echo "New hash for 'password123': $newHash\n";

echo "\n=== Testing the new hash ===\n";
$testResult = password_verify('password123', $newHash);
echo "Verification result: " . ($testResult ? '✅ SUCCESS' : '❌ FAILED') . "\n";

echo "\n=== Checking what the stored hash might be for ===\n";
// Let's check if it's a common password
$commonPasswords = ['password', 'admin', '123456', 'password123'];
foreach ($commonPasswords as $pwd) {
    $result = password_verify($pwd, $storedHash);
    if ($result) {
        echo "✅ The stored hash is for: '$pwd'\n";
        break;
    }
}