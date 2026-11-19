<?php
require_once "config/database.php";

$tables_result = $conn->query("SHOW TABLES");
if ($tables_result) {
    while ($row = $tables_result->fetch_array()) {
        $table_name = $row[0];
        echo "<h3>Table: $table_name</h3>";
        $columns_result = $conn->query("DESCRIBE $table_name");
        if ($columns_result) {
            echo "<ul>";
            while ($col = $columns_result->fetch_assoc()) {
                echo "<li><strong>{$col['Field']}</strong> - {$col['Type']} (Null: {$col['Null']}, Key: {$col['Key']}, Default: {$col['Default']}, Extra: {$col['Extra']})</li>";
            }
            echo "</ul>";
        }
    }
} else {
    echo "Error querying tables: " . $conn->error;
}
?>
