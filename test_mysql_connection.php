<?php
// Quick MySQL Connection Test
// Run: php test_mysql_connection.php

$host = '127.0.0.1';
$port = '3306';
$database = 'db_laracrud';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ MySQL Connection Successful!\n";
    echo "Database: $database\n";
    echo "Host: $host:$port\n";
} catch (PDOException $e) {
    echo "❌ MySQL Connection Failed!\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "\nTroubleshooting:\n";
    echo "1. Make sure MySQL is running in XAMPP Control Panel\n";
    echo "2. Check if database '$database' exists in phpMyAdmin\n";
    echo "3. Verify MySQL port 3306 is not blocked\n";
}

