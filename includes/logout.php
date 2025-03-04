<?php
require_once '../config/database.php';
require_once 'auth.php';

// Log the logout action
if (isset($_SESSION['user_id'])) {
    try {
        $stmt = $conn->prepare("
            INSERT INTO activities (
                activity_type, 
                description, 
                related_to_type, 
                related_to_id, 
                performed_by
            ) VALUES (
                'system', 
                'User logged out', 
                'user', 
                ?, 
                ?
            )
        ");
        $stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
    } catch (PDOException $e) {
        error_log("Failed to log logout activity: " . $e->getMessage());
    }
}

// Perform logout
logout();

// Redirect to login page
header('Location: ../login.php');
exit();
