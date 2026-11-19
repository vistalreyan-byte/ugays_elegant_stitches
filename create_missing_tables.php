<?php
header('Content-Type: text/plain');
require_once "config/database.php";

// Create users table if not exists
$q1 = "CREATE TABLE IF NOT EXISTS `users` (
    `user_id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` VARCHAR(20) DEFAULT 'admin',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($q1)) {
    echo "Table 'users' created or exists.\n";
} else {
    echo "Error creating table 'users': " . $conn->error . "\n";
}

// Create shop_settings table if not exists
$q2 = "CREATE TABLE IF NOT EXISTS `shop_settings` (
    `setting_id` INT AUTO_INCREMENT PRIMARY KEY,
    `shop_name` VARCHAR(100) NOT NULL,
    `shop_address` VARCHAR(255) NOT NULL,
    `contact_number` VARCHAR(20) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($q2)) {
    echo "Table 'shop_settings' created or exists.\n";
} else {
    echo "Error creating table 'shop_settings': " . $conn->error . "\n";
}

// Check if admin user exists, insert if not
$res = $conn->query("SELECT * FROM `users` WHERE `username` = 'admin'");
if ($res && $res->num_rows == 0) {
    // We will use password_hash for safety, or plain text. Let's insert a hashed version of 'admin' and also plain text check
    $hashed_pass = password_hash('admin', PASSWORD_DEFAULT);
    $q3 = "INSERT INTO `users` (`username`, `password`, `role`) VALUES ('admin', '$hashed_pass', 'admin')";
    if ($conn->query($q3)) {
        echo "Default admin user inserted.\n";
    } else {
        echo "Error inserting default admin: " . $conn->error . "\n";
    }
} else {
    echo "Admin user already exists.\n";
}

// Check if shop settings exist, insert if not
$res = $conn->query("SELECT * FROM `shop_settings` LIMIT 1");
if ($res && $res->num_rows == 0) {
    $q4 = "INSERT INTO `shop_settings` (`shop_name`, `shop_address`, `contact_number`, `email`) 
           VALUES ('Ugay\'s Elegant Tailoring & Rental Shop', '123 Fashion Ave, Manila, Philippines', '09123456789', 'info@ugayselegant.com')";
    if ($conn->query($q4)) {
        echo "Default shop settings inserted.\n";
    } else {
        echo "Error inserting default shop settings: " . $conn->error . "\n";
    }
} else {
    echo "Shop settings already exist.\n";
}
?>
