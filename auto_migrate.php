<?php
// auto_migrate.php — Automatically initializes the database schema in cloud environments (Railway, Render, etc.)

$host = getenv('DB_HOST') ?: getenv('MYSQLHOST') ?: 'localhost';
$port = (int)(getenv('DB_PORT') ?: getenv('MYSQLPORT') ?: 3306);
$dbname = getenv('DB_NAME') ?: getenv('MYSQLDATABASE') ?: 'tailoring_rental_db';
$username = getenv('DB_USER') ?: getenv('MYSQLUSER') ?: 'root';
$password = getenv('DB_PASSWORD') ?: getenv('MYSQLPASSWORD') ?: '';

$conn_url = getenv('MYSQL_URL') ?: getenv('DATABASE_URL');
if (!empty($conn_url)) {
    $db_parts = parse_url($conn_url);
    if (!empty($db_parts['host'])) $host = $db_parts['host'];
    if (!empty($db_parts['port'])) $port = (int)$db_parts['port'];
    if (!empty($db_parts['user'])) $username = $db_parts['user'];
    if (!empty($db_parts['pass'])) $password = $db_parts['pass'];
    if (!empty($db_parts['path'])) $dbname = ltrim($db_parts['path'], '/');
}

echo "[Migration] Connecting to MySQL at {$host}:{$port}...\n";

// Retry connecting if DB server is still starting up
$max_retries = 25;
$retry = 0;
$conn = null;

while ($retry < $max_retries) {
    try {
        $conn = @new mysqli($host, $username, $password, "", $port);
        if ($conn && !$conn->connect_error) {
            break;
        }
    } catch (Exception $e) {}

    $retry++;
    echo "[Migration] Waiting for database server (attempt $retry/$max_retries)...\n";
    sleep(2);
}

if (!$conn || $conn->connect_error) {
    echo "[Migration] Notice: Could not connect to database server. Skipping migration.\n";
    exit(0);
}

// Ensure database exists
$conn->query("CREATE DATABASE IF NOT EXISTS `{$dbname}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
$conn->select_db($dbname);

// Check if users table already exists
$table_check = $conn->query("SHOW TABLES LIKE 'users'");
if ($table_check && $table_check->num_rows > 0) {
    echo "[Migration] Database already initialized. Skipping SQL import.\n";
    $conn->close();
    exit(0);
}

echo "[Migration] Initializing database tables from init_db.sql...\n";
$sql_file = __DIR__ . '/init_db.sql';
if (!file_exists($sql_file)) {
    $sql_file = __DIR__ . '/tailoring_rental_db.sql';
}

if (!file_exists($sql_file)) {
    echo "[Migration] Warning: No SQL dump file found.\n";
    $conn->close();
    exit(0);
}

$sql_content = file_get_contents($sql_file);

// Execute multi-query
if ($conn->multi_query($sql_content)) {
    do {
        if ($result = $conn->store_result()) {
            $result->free();
        }
    } while ($conn->more_results() && $conn->next_result());
    echo "[Migration] Database schema and initial data imported successfully!\n";
} else {
    echo "[Migration] Notice during SQL execution: " . $conn->error . "\n";
}

$conn->close();
echo "[Migration] Completed.\n";
?>
