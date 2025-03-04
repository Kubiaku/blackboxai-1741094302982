<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database credentials
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';

try {
    // Create connection without database
    $conn = new PDO("mysql:host=$db_host", $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if not exists
    $sql = "CREATE DATABASE IF NOT EXISTS sales_app";
    $conn->exec($sql);
    echo "Database created successfully\n";
    
    // Select the database
    $conn->exec("USE sales_app");
    
    // Read and execute SQL schema
    $sql = file_get_contents('database.sql');
    $conn->exec($sql);
    echo "Database schema imported successfully\n";
    
    // Verify admin user exists
    $stmt = $conn->query("SELECT COUNT(*) FROM users WHERE email = 'admin@example.com'");
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        // Create admin user if not exists
        $password = password_hash('password123', PASSWORD_DEFAULT);
        $stmt = $conn->prepare("
            INSERT INTO users (username, email, password, first_name, last_name, role)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute(['admin', 'admin@example.com', $password, 'Admin', 'User', 'admin']);
        echo "Admin user created successfully\n";
    } else {
        echo "Admin user already exists\n";
    }
    
    echo "Database initialization completed successfully!\n";
    echo "\nYou can now log in with:\n";
    echo "Email: admin@example.com\n";
    echo "Password: password123\n";
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
