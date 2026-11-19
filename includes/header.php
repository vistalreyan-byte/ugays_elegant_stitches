<?php
if (!isset($page_title)) {
    $page_title = "Ugay's Elegant Stitches";
}
$user_display_name = $_SESSION['full_name'] ?? $_SESSION['username'] ?? 'Administrator';
$user_role = $_SESSION['role'] ?? 'admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> | Ugay's Elegant</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,500;0,600;1,500&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <!-- Stylesheets -->
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="dashboard-wrapper">
    <?php include __DIR__ . '/sidebar.php'; ?>

    <main class="main-content">
        <!-- Top Header -->
        <header class="top-header">
            <div class="header-left">
                <button class="menu-btn" id="menuBtn" type="button">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <h1><?php echo htmlspecialchars($page_title); ?></h1>
            </div>

            <div class="header-right">
                <div class="admin-profile">
                    <div class="admin-avatar">
                        <i class="fa-solid fa-user"></i>
                    </div>
                    <div class="admin-info">
                        <strong><?php echo htmlspecialchars($user_display_name); ?></strong>
                        <small><?php echo ucfirst(htmlspecialchars($user_role)); ?></small>
                    </div>
                    <a href="logout.php" title="Logout" style="margin-left: 12px; color: #ef4444; font-size: 14px;">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i>
                    </a>
                </div>
            </div>
        </header>

        <section class="page-content">
