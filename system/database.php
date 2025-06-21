<?php
defined("BASEPATH") or exit("No direct script access allowed.");

// Check if mysqli extension is loaded
if (!extension_loaded('mysqli')) {
    die("MySQLi extension is not loaded. Please install php-mysqli extension.");
}

$database = $_CONFIG['db'];

// Set mysqli options for better security
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $db = new mysqli($database['host'], $database['username'], $database['password'], $database['name']);
    
    // Set charset to prevent SQL injection
    $db->set_charset("utf8mb4");
    
    // Set SQL mode for better data integrity
    $db->query("SET sql_mode = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION'");
    
} catch (mysqli_sql_exception $e) {
    error_log("Database connection failed: " . $e->getMessage());
    
    if (config('web', 'environment') == 'development') {
        die("Database connection failed: " . $e->getMessage());
    } else {
        die("Database connection failed. Please try again later.");
    }
}

// Function for safe database queries
function safe_query($db, $query, $params = []) {
    $stmt = $db->prepare($query);
    if ($stmt === false) {
        error_log("Prepare failed: " . $db->error);
        return false;
    }
    
    if (!empty($params)) {
        $types = str_repeat('s', count($params)); // assuming all strings, adjust as needed
        $stmt->bind_param($types, ...$params);
    }
    
    $result = $stmt->execute();
    if ($result === false) {
        error_log("Execute failed: " . $stmt->error);
        return false;
    }
    
    return $stmt;
}
