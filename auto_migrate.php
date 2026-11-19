<?php
// auto_migrate.php — Automatically initializes the database schema in cloud environments (Railway, Render, etc.)

require_once __DIR__ . '/config/database.php';

echo "[Migration] Checking database connection...\n";

// Retry connecting if DB is still starting up
$max_retries = 30;
$retry = 0;
while ($retry < $max_retries) {
    if ($conn && !$conn->connect_error) {
        break;
    }
    $retry++;
    echo "[Migration] Waiting for database to be ready (attempt $retry/$max_retries)...\n";
    sleep(2);
    // Reconnect
    $host = getenv('DB_HOST') ?: getenv('MYSQLHOST') ?: 'localhost';
    $port = (int)(getenv('DB_PORT') ?: getenv('MYSQLPORT') ?: 3306);
    $dbname = getenv('DB_NAME') ?: getenv('MYSQLDATABASE') ?: 'tailoring_rental_db';
    $username = getenv('DB_USER') ?: getenv('MYSQLUSER') ?: 'root';
    $password = getenv('DB_PASSWORD') ?: getenv('MYSQLPASSWORD') ?: '';
    $conn = @new mysqli($host, $username, $password, $dbname, $port);
}

if (!$conn || $conn->connect_error) {
    echo "[Migration] ERROR: Could not connect to database after $max_retries attempts.\n";
    exit(1);
}

// Check if users table already exists
$table_check = $conn->query("SHOW TABLES LIKE 'users'");
if ($table_check && $table_check->num_rows > 0) {
    echo "[Migration] Database already initialized. Skipping auto-migration.\n";
    exit(0);
}

echo "[Migration] Initializing database tables from init_db.sql...\n";
$sql_file = __DIR__ . '/init_db.sql';
if (!file_exists($sql_file)) {
    $sql_file = __DIR__ . '/tailoring_rental_db.sql';
}

if (!file_exists($sql_file)) {
    echo "[Migration] Warning: No SQL dump file found.\n";
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
    echo "[Migration] Notice/Error during SQL execution: " . $conn->error . "\n";
}

$conn->close();
echo "[Migration] Completed.\n";
