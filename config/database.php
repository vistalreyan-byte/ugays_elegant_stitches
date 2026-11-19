<?php

// Database configuration with environment variable support (Railway, Render, Docker, XAMPP)
$host = getenv('DB_HOST') ?: getenv('MYSQLHOST') ?: 'localhost';
$port = (int)(getenv('DB_PORT') ?: getenv('MYSQLPORT') ?: 3306);
$dbname = getenv('DB_NAME') ?: getenv('MYSQLDATABASE') ?: 'tailoring_rental_db';
$username = getenv('DB_USER') ?: getenv('MYSQLUSER') ?: 'root';
$password = getenv('DB_PASSWORD') ?: getenv('MYSQLPASSWORD') ?: '';

// Also support full connection URLs (e.g., MYSQL_URL or DATABASE_URL provided by Railway/Render)
$conn_url = getenv('MYSQL_URL') ?: getenv('DATABASE_URL');
if (!empty($conn_url)) {
    $db_parts = parse_url($conn_url);
    if (!empty($db_parts['host'])) $host = $db_parts['host'];
    if (!empty($db_parts['port'])) $port = (int)$db_parts['port'];
    if (!empty($db_parts['user'])) $username = $db_parts['user'];
    if (!empty($db_parts['pass'])) $password = $db_parts['pass'];
    if (!empty($db_parts['path'])) $dbname = ltrim($db_parts['path'], '/');
}

// 1. MySQLi connection
$conn = new mysqli($host, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// 2. PDO connection (for scripts utilizing PDO)
try {
    $pdo = new PDO("mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    $pdo = null;
}

?>