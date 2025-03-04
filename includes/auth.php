<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

function login($email, $password) {
    global $conn;
    
    try {
        // Enable error reporting for debugging
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        
        // Prepare SQL statement - updated to match database schema
        $stmt = $conn->prepare("SELECT id, username, email, password, first_name, last_name, role FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Debug log
        error_log("Login attempt for email: " . $email);
        error_log("User found: " . ($user ? 'yes' : 'no'));
        
        // Verify user exists and password is correct
        if ($user && password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_fullname'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['user_role'] = $user['role'];
            
            error_log("Login successful for user: " . $user['email']);
            return true;
        }
        
        error_log("Login failed for user: " . $email);
        return false;
    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        return false;
    }
}

function logout() {
    // Unset all session variables
    $_SESSION = array();
    
    // Destroy the session
    session_destroy();
    
    // Delete session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time()-3600, '/');
    }
}

// API Authentication middleware
function authenticateAPI() {
    $headers = apache_request_headers();
    
    if (!isset($headers['Authorization'])) {
        http_response_code(401);
        echo json_encode(['error' => 'No authorization header']);
        exit();
    }
    
    $auth = $headers['Authorization'];
    if (strpos($auth, 'Bearer ') !== 0) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid authorization format']);
        exit();
    }
    
    $token = substr($auth, 7);
    // Here you would validate the JWT token
    // For demo purposes, we'll just check if it matches our demo token
    if ($token !== 'demo-jwt-token') {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid token']);
        exit();
    }
    
    return true;
}

// Generate a secure random token
function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

// Create a new user account
function createUser($username, $email, $password, $firstName, $lastName, $role = 'sales_rep') {
    global $conn;
    
    try {
        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Prepare SQL statement
        $stmt = $conn->prepare("
            INSERT INTO users (username, email, password, first_name, last_name, role) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$username, $email, $hashedPassword, $firstName, $lastName, $role]);
        
        return $conn->lastInsertId();
    } catch (PDOException $e) {
        error_log("Create user error: " . $e->getMessage());
        return false;
    }
}

// Update user password
function updatePassword($userId, $newPassword) {
    global $conn;
    
    try {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        return $stmt->execute([$hashedPassword, $userId]);
    } catch (PDOException $e) {
        error_log("Update password error: " . $e->getMessage());
        return false;
    }
}

// Get user details
function getUserDetails($userId) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("
            SELECT id, username, email, first_name, last_name, role, created_at 
            FROM users 
            WHERE id = ?
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Get user details error: " . $e->getMessage());
        return false;
    }
}
