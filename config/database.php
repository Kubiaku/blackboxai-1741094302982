<?php
// Database configuration for SQLite
$db_path = __DIR__ . '/../database/sales_app.sqlite';
$db_directory = dirname($db_path);

// Create database directory if it doesn't exist
if (!file_exists($db_directory)) {
    mkdir($db_directory, 0777, true);
}

try {
    // Create PDO connection for SQLite
    $conn = new PDO(
        "sqlite:$db_path",
        null,
        null,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
    
    // Enable foreign key support
    $conn->exec('PRAGMA foreign_keys = ON;');
    
} catch(PDOException $e) {
    // Log the error and show a generic message
    error_log("Connection failed: " . $e->getMessage());
    die("Sorry, there was a problem connecting to the database. Please try again later.");
}

// Helper functions for database operations
function executeQuery($sql, $params = []) {
    global $conn;
    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch(PDOException $e) {
        error_log("Query execution failed: " . $e->getMessage());
        return false;
    }
}

function fetchAll($sql, $params = []) {
    $stmt = executeQuery($sql, $params);
    return $stmt ? $stmt->fetchAll() : [];
}

function fetchOne($sql, $params = []) {
    $stmt = executeQuery($sql, $params);
    return $stmt ? $stmt->fetch() : null;
}

function insert($table, $data) {
    global $conn;
    try {
        $columns = implode(', ', array_keys($data));
        $values = implode(', ', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO $table ($columns) VALUES ($values)";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute(array_values($data));
        return $conn->lastInsertId();
    } catch(PDOException $e) {
        error_log("Insert failed: " . $e->getMessage());
        return false;
    }
}

function update($table, $data, $where, $whereParams = []) {
    global $conn;
    try {
        $set = implode('=?, ', array_keys($data)) . '=?';
        $sql = "UPDATE $table SET $set WHERE $where";
        
        $params = array_merge(array_values($data), $whereParams);
        $stmt = $conn->prepare($sql);
        return $stmt->execute($params);
    } catch(PDOException $e) {
        error_log("Update failed: " . $e->getMessage());
        return false;
    }
}

function delete($table, $where, $params = []) {
    global $conn;
    try {
        $sql = "DELETE FROM $table WHERE $where";
        $stmt = $conn->prepare($sql);
        return $stmt->execute($params);
    } catch(PDOException $e) {
        error_log("Delete failed: " . $e->getMessage());
        return false;
    }
}
