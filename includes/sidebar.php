<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar" id="sidebar">

    <!-- Logo & Shop Identity -->
    <div class="brand">
        <div class="brand-dress">
            <i class="fa-solid fa-person-dress"></i>
        </div>
        <div class="brand-name">
            <h1>Ugay's</h1>
            <h2>Elegant</h2>
            <p>Tailoring & Rental Shop</p>
        </div>
    </div>

    <!-- Navigation Menu -->
    <nav class="sidebar-nav">

        <a href="dashboard.php" class="nav-item <?php echo ($current_page === 'dashboard.php') ? 'active' : ''; ?>">
            <i class="fa-solid fa-house"></i>
            <span>Dashboard</span>
        </a>

        <a href="customers.php" class="nav-item <?php echo ($current_page === 'customers.php') ? 'active' : ''; ?>">
            <i class="fa-solid fa-users"></i>
            <span>Customers</span>
        </a>

        <a href="orders.php" class="nav-item <?php echo ($current_page === 'orders.php') ? 'active' : ''; ?>">
            <i class="fa-solid fa-scissors"></i>
            <span>Tailoring Orders</span>
        </a>

        <a href="rentals.php" class="nav-item <?php echo ($current_page === 'rentals.php') ? 'active' : ''; ?>">
            <i class="fa-solid fa-person-dress"></i>
            <span>Rental Management</span>
        </a>

        <a href="inventory.php" class="nav-item <?php echo ($current_page === 'inventory.php') ? 'active' : ''; ?>">
            <i class="fa-solid fa-box"></i>
            <span>Inventory</span>
        </a>

        <a href="appointment.php" class="nav-item <?php echo ($current_page === 'appointment.php' || $current_page === 'appointments.php') ? 'active' : ''; ?>">
            <i class="fa-regular fa-calendar-days"></i>
            <span>Appointments</span>
        </a>

        <a href="payments.php" class="nav-item <?php echo ($current_page === 'payments.php') ? 'active' : ''; ?>">
            <i class="fa-regular fa-credit-card"></i>
            <span>Payments</span>
        </a>

        <a href="reports.php" class="nav-item <?php echo ($current_page === 'reports.php') ? 'active' : ''; ?>">
            <i class="fa-solid fa-chart-column"></i>
            <span>Reports</span>
        </a>

    </nav>

    <!-- Logout -->
    <div class="sidebar-bottom">
        <a href="logout.php" class="logout-btn">
            <i class="fa-solid fa-arrow-right-from-bracket"></i>
            <span>Logout (<?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?>)</span>
        </a>
    </div>

    <!-- Decorative Illustration -->
    <div class="sewing-decoration">
        <i class="fa-solid fa-person-dress"></i>
        <i class="fa-solid fa-scissors"></i>
    </div>

</aside>
