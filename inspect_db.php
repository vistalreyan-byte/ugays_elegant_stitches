<?php
header('Content-Type: text/plain');
require_once "config/database.php";

$tables_result = $conn->query("SHOW TABLES");
if ($tables_result) {
    while ($row = $tables_result->fetch_array()) {
        $table_name = $row[0];
        echo "=== Table: $table_name ===\n";
        $columns_result = $conn->query("DESCRIBE `$table_name`");
        if ($columns_result) {
            while ($col = $columns_result->fetch_assoc()) {
                echo "  Column: {$col['Field']} | Type: {$col['Type']} | Null: {$col['Null']} | Key: {$col['Key']} | Default: {$col['Default']} | Extra: {$col['Extra']}\n";
            }
        }
        echo "\n";
    }
} else {
    echo "Error querying tables: " . $conn->error;
}
?>
