<?php
header('Content-Type: text/plain');
require_once "config/database.php";

$tables = ['appointments', 'customers', 'employees', 'payments', 'rental_items', 'rental_order_items', 'rental_orders', 'tailoring_orders'];
foreach ($tables as $table) {
    $res = $conn->query("SELECT COUNT(*) FROM `$table`");
    if ($res) {
        $row = $res->fetch_row();
        echo "$table: {$row[0]} rows\n";
    } else {
        echo "$table: error ({$conn->error})\n";
    }
}
?>
