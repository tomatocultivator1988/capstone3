<?php
/**
 * Exam Management System - Main Entry Point
 * 
 * This file serves as the main entry point for the Exam Management System.
 * It redirects users to the appropriate page based on their authentication status.
 */

// Start session
session_start();

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['role'] ?? 'student';
    header("Location: dashboard_mvc.php?role=" . $role);
    exit;
}

// If not logged in, redirect to login page
header("Location: login_mvc.php");
exit;
?>