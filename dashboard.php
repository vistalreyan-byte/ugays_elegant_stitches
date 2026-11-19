<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

$total_customers = 120;
$tailoring_orders = 25;
$active_rentals = 18;
$today_sales = 8450.00;

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard | Ugay's Elegant</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,500;0,600;1,500&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >

    <!-- Dashboard CSS -->
    <link rel="stylesheet" href="assets/css/dashboard.css">
</head>

<body>

<div class="dashboard-wrapper">


    <!-- ==========================================
         SIDEBAR
    =========================================== -->

    <aside class="sidebar" id="sidebar">

        <!-- Logo -->
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


        <!-- Navigation -->
        <nav class="sidebar-nav">

            <a href="dashboard.php" class="nav-item active">
                <i class="fa-solid fa-house"></i>
                <span>Dashboard</span>
            </a>

            <a href="#" class="nav-item">
                <i class="fa-solid fa-users"></i>
                <span>Customers</span>
            </a>

            <a href="#" class="nav-item">
                <i class="fa-solid fa-scissors"></i>
                <span>Tailoring Orders</span>
            </a>

            <a href="#" class="nav-item">
                <i class="fa-solid fa-person-dress"></i>
                <span>Rental Management</span>
            </a>

            <a href="#" class="nav-item">
                <i class="fa-solid fa-box"></i>
                <span>Inventory</span>
            </a>

            <a href="#" class="nav-item">
                <i class="fa-regular fa-calendar-days"></i>
                <span>Appointments</span>
            </a>

            <a href="#" class="nav-item">
                <i class="fa-regular fa-credit-card"></i>
                <span>Payments</span>
            </a>

            <a href="#" class="nav-item">
                <i class="fa-solid fa-chart-column"></i>
                <span>Reports</span>
            </a>

            <a href="#" class="nav-item">
                <i class="fa-solid fa-user-tie"></i>
                <span>Employees</span>
            </a>

            <a href="#" class="nav-item">
                <i class="fa-solid fa-gear"></i>
                <span>Settings</span>
            </a>

        </nav>


        <!-- Logout -->
        <div class="sidebar-bottom">

            <a href="index.php" class="logout-btn">
                <i class="fa-solid fa-arrow-right-from-bracket"></i>
                <span>Logout</span>
            </a>

        </div>


        <!-- Decorative sewing illustration -->
        <div class="sewing-decoration">

            <i class="fa-solid fa-person-dress"></i>
            <i class="fa-solid fa-scissors"></i>

        </div>

    </aside>



    <!-- ==========================================
         MAIN CONTENT
    =========================================== -->

    <main class="main-content">


        <!-- ======================================
             TOP HEADER
        ======================================= -->

        <header class="top-header">

            <div class="header-left">

                <button
                    class="menu-btn"
                    id="menuBtn"
                    type="button"
                >
                    <i class="fa-solid fa-bars"></i>
                </button>

                <h1>Dashboard</h1>

            </div>


            <div class="header-right">

                <!-- Search -->
                <div class="search-box">

                    <i class="fa-solid fa-magnifying-glass"></i>

                    <input
                        type="text"
                        id="globalSearch"
                        placeholder="Search anything..."
                    >

                </div>


                <!-- Notification -->
                <button class="notification-btn">

                    <i class="fa-regular fa-bell"></i>

                    <span>3</span>

                </button>


                <!-- Admin -->
                <div class="admin-profile">

                    <div class="admin-avatar">
                        <i class="fa-solid fa-user"></i>
                    </div>

                    <div class="admin-info">

                        <strong>Admin User</strong>

                        <small>Administrator</small>

                    </div>

                    <i class="fa-solid fa-chevron-down"></i>

                </div>

            </div>

        </header>



        <!-- ======================================
             PAGE CONTENT
        ======================================= -->

        <section class="page-content">


            <!-- ==================================
                 STAT CARDS
            =================================== -->

            <div class="stats-grid">


                <!-- Customers -->
                <div class="stat-card">

                    <div class="stat-icon pink">
                        <i class="fa-solid fa-users"></i>
                    </div>

                    <div class="stat-content">

                        <p>Total Customers</p>

                        <h2>
                            <?php echo $total_customers; ?>
                        </h2>

                        <span class="positive">
                            +12 this month
                        </span>

                    </div>

                    <div class="mini-chart pink-chart">
                        <span></span>
                        <span></span>
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>

                </div>


                <!-- Tailoring -->
                <div class="stat-card">

                    <div class="stat-icon purple">
                        <i class="fa-solid fa-scissors"></i>
                    </div>

                    <div class="stat-content">

                        <p>Tailoring Orders</p>

                        <h2>
                            <?php echo $tailoring_orders; ?>
                        </h2>

                        <span class="purple-text">
                            8 In Progress
                        </span>

                    </div>

                    <div class="mini-chart purple-chart">
                        <span></span>
                        <span></span>
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>

                </div>


                <!-- Rentals -->
                <div class="stat-card">

                    <div class="stat-icon teal">
                        <i class="fa-solid fa-person-dress"></i>
                    </div>

                    <div class="stat-content">

                        <p>Active Rentals</p>

                        <h2>
                            <?php echo $active_rentals; ?>
                        </h2>

                        <span class="teal-text">
                            5 Reserved
                        </span>

                    </div>

                    <div class="mini-chart teal-chart">
                        <span></span>
                        <span></span>
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>

                </div>


                <!-- Sales -->
                <div class="stat-card">

                    <div class="stat-icon orange">
                        <i class="fa-solid fa-peso-sign"></i>
                    </div>

                    <div class="stat-content">

                        <p>Today's Sales</p>

                        <h2>
                            ₱<?php echo number_format($today_sales, 2); ?>
                        </h2>

                        <span class="positive">
                            +14% vs yesterday
                        </span>

                    </div>

                    <div class="mini-chart orange-chart">
                        <span></span>
                        <span></span>
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>

                </div>

            </div>



            <!-- ==================================
                 MIDDLE SECTION
            =================================== -->

            <div class="middle-grid">


                <!-- Recent Orders -->
                <div class="panel recent-orders">

                    <div class="panel-header">

                        <div>

                            <i class="fa-solid fa-list"></i>

                            <h2>Recent Orders</h2>

                        </div>

                        <a href="#">
                            View All
                        </a>

                    </div>


                    <div class="table-container">

                        <table>

                            <thead>

                                <tr>
                                    <th>ORDER ID</th>
                                    <th>CUSTOMER</th>
                                    <th>TYPE</th>
                                    <th>ITEM / SERVICE</th>
                                    <th>STATUS</th>
                                    <th>DUE / RETURN</th>
                                    <th>AMOUNT</th>
                                </tr>

                            </thead>

                            <tbody>

                                <tr>

                                    <td>TO-2024-025</td>

                                    <td>Maria Santos</td>

                                    <td>Tailoring</td>

                                    <td>Evening Gown</td>

                                    <td>
                                        <span class="status purple-status">
                                            In Progress
                                        </span>
                                    </td>

                                    <td>May 20, 2024</td>

                                    <td>₱800.00</td>

                                </tr>


                                <tr>

                                    <td>RO-2024-018</td>

                                    <td>John Dela Cruz</td>

                                    <td>Rental</td>

                                    <td>Classic Suit</td>

                                    <td>
                                        <span class="status orange-status">
                                            Reserved
                                        </span>
                                    </td>

                                    <td>May 18, 2024</td>

                                    <td>₱1,200.00</td>

                                </tr>


                                <tr>

                                    <td>TO-2024-024</td>

                                    <td>Angela Garcia</td>

                                    <td>Tailoring</td>

                                    <td>Barong Tagalog</td>

                                    <td>
                                        <span class="status blue-status">
                                            Pending
                                        </span>
                                    </td>

                                    <td>May 22, 2024</td>

                                    <td>₱2,500.00</td>

                                </tr>


                                <tr>

                                    <td>RO-2024-017</td>

                                    <td>Mark Reyes</td>

                                    <td>Rental</td>

                                    <td>Debut Gown</td>

                                    <td>
                                        <span class="status green-status">
                                            Rented
                                        </span>
                                    </td>

                                    <td>May 19, 2024</td>

                                    <td>₱1,800.00</td>

                                </tr>


                                <tr>

                                    <td>TO-2024-023</td>

                                    <td>Jessica Lee</td>

                                    <td>Tailoring</td>

                                    <td>Bridesmaid Dress</td>

                                    <td>
                                        <span class="status orange-status">
                                            For Fitting
                                        </span>
                                    </td>

                                    <td>May 24, 2024</td>

                                    <td>₱1,200.00</td>

                                </tr>

                            </tbody>

                        </table>

                    </div>

                </div>



                <!-- Upcoming Appointments -->
                <div class="panel appointments">

                    <div class="panel-header">

                        <div>

                            <i class="fa-regular fa-calendar"></i>

                            <h2>Upcoming Appointments</h2>

                        </div>

                        <a href="#">
                            View All
                        </a>

                    </div>


                    <div class="appointment-list">


                        <div class="appointment-item">

                            <div class="date-box">
                                <small>MAY</small>
                                <strong>18</strong>
                                <span>SAT</span>
                            </div>

                            <div class="appointment-info">

                                <strong>Fitting</strong>

                                <p>Maria Santos</p>

                                <small>10:00 AM - 10:30 AM</small>

                            </div>

                            <span class="scheduled">
                                Scheduled
                            </span>

                        </div>


                        <div class="appointment-item">

                            <div class="date-box">
                                <small>MAY</small>
                                <strong>18</strong>
                                <span>SAT</span>
                            </div>

                            <div class="appointment-info">

                                <strong>Measurement</strong>

                                <p>John Dela Cruz</p>

                                <small>11:00 AM - 11:30 AM</small>

                            </div>

                            <span class="scheduled">
                                Scheduled
                            </span>

                        </div>


                        <div class="appointment-item">

                            <div class="date-box">
                                <small>MAY</small>
                                <strong>19</strong>
                                <span>SUN</span>
                            </div>

                            <div class="appointment-info">

                                <strong>Pickup</strong>

                                <p>Angela Garcia</p>

                                <small>02:00 PM - 02:30 PM</small>

                            </div>

                            <span class="scheduled">
                                Scheduled
                            </span>

                        </div>


                        <div class="appointment-item">

                            <div class="date-box">
                                <small>MAY</small>
                                <strong>20</strong>
                                <span>MON</span>
                            </div>

                            <div class="appointment-info">

                                <strong>Return</strong>

                                <p>Mark Reyes</p>

                                <small>03:00 PM - 03:30 PM</small>

                            </div>

                            <span class="scheduled">
                                Scheduled
                            </span>

                        </div>

                    </div>

                </div>

            </div>



            <!-- ==================================
                 BOTTOM SECTION
            =================================== -->

            <div class="bottom-grid">


                <!-- Sales Overview -->
                <div class="panel sales-panel">

                    <div class="panel-header">

                        <div>
                            <h2>Sales Overview</h2>
                        </div>

                        <select>
                            <option>This Month</option>
                            <option>Last Month</option>
                            <option>This Year</option>
                        </select>

                    </div>


                    <div class="sales-chart">

                        <div class="y-axis">

                            <span>20K</span>
                            <span>15K</span>
                            <span>10K</span>
                            <span>5K</span>
                            <span>0</span>

                        </div>

                        <div class="chart-area">

                            <div class="chart-lines"></div>

                            <svg
                                viewBox="0 0 700 250"
                                preserveAspectRatio="none"
                            >

                                <polyline
                                    points="0,200 50,130 100,160 150,130 200,90 250,70 300,130 350,105 400,125 450,80 500,150 550,115 600,145 650,100 700,75"
                                    fill="none"
                                    stroke="#e83e8c"
                                    stroke-width="4"
                                />

                                <polygon
                                    points="0,200 50,130 100,160 150,130 200,90 250,70 300,130 350,105 400,125 450,80 500,150 550,115 600,145 650,100 700,75 700,250 0,250"
                                    fill="rgba(232,62,140,0.08)"
                                />

                            </svg>

                            <div class="x-axis">

                                <span>May 1</span>
                                <span>May 7</span>
                                <span>May 13</span>
                                <span>May 19</span>
                                <span>May 25</span>
                                <span>May 31</span>

                            </div>

                        </div>

                    </div>

                </div>



                <!-- Orders By Status -->
                <div class="panel status-panel">

                    <div class="panel-header">

                        <h2>Orders by Status</h2>

                    </div>


                    <div class="status-content">

                        <div class="donut-chart">

                            <div class="donut-center">

                                <strong>50</strong>

                                <span>Total</span>

                            </div>

                        </div>


                        <div class="status-list">

                            <div>
                                <span class="dot blue-dot"></span>
                                Pending
                                <strong>10 (20%)</strong>
                            </div>

                            <div>
                                <span class="dot purple-dot"></span>
                                In Progress
                                <strong>15 (30%)</strong>
                            </div>

                            <div>
                                <span class="dot orange-dot"></span>
                                For Fitting
                                <strong>8 (16%)</strong>
                            </div>

                            <div>
                                <span class="dot green-dot"></span>
                                Completed
                                <strong>12 (24%)</strong>
                            </div>

                            <div>
                                <span class="dot pink-dot"></span>
                                Cancelled
                                <strong>5 (10%)</strong>
                            </div>

                        </div>

                    </div>

                </div>



                <!-- Top Rental Items -->
                <div class="panel rental-panel">

                    <div class="panel-header">

                        <h2>Top Rental Items</h2>

                        <a href="#">View All</a>

                    </div>


                    <div class="rental-list">


                        <div class="rental-item">

                            <div class="item-image blue-dress">
                                <i class="fa-solid fa-person-dress"></i>
                            </div>

                            <div class="item-info">

                                <strong>Debut Gown (Blue)</strong>

                                <small>
                                    ₱1,800.00 / 2 days
                                </small>

                            </div>

                            <span>12</span>

                        </div>


                        <div class="rental-item">

                            <div class="item-image red-dress">
                                <i class="fa-solid fa-person-dress"></i>
                            </div>

                            <div class="item-info">

                                <strong>Elegant Evening Gown (Red)</strong>

                                <small>
                                    ₱1,500.00 / 2 days
                                </small>

                            </div>

                            <span>9</span>

                        </div>


                        <div class="rental-item">

                            <div class="item-image black-suit">
                                <i class="fa-solid fa-user-tie"></i>
                            </div>

                            <div class="item-info">

                                <strong>Classic Suit (Black)</strong>

                                <small>
                                    ₱1,200.00 / 2 days
                                </small>

                            </div>

                            <span>8</span>

                        </div>


                        <div class="rental-item">

                            <div class="item-image cream-barong">
                                <i class="fa-solid fa-shirt"></i>
                            </div>

                            <div class="item-info">

                                <strong>Formal Barong (Cream)</strong>

                                <small>
                                    ₱800.00 / 2 days
                                </small>

                            </div>

                            <span>7</span>

                        </div>

                    </div>

                </div>

            </div>



            <!-- ==================================
                 WELCOME BANNER
            =================================== -->

            <div class="welcome-banner">

                <div class="welcome-icon">
                    <i class="fa-solid fa-person-dress"></i>
                </div>

                <div class="welcome-text">

                    <h2>
                        Welcome back, Admin! 👋
                    </h2>

                    <p>
                        Manage your tailoring and rental shop easily.
                    </p>

                </div>

                <div class="quote">

                    <p>
                        "Style is a way to say who you are
                        without having to speak."
                    </p>

                </div>

                <div class="banner-dress">
                    <i class="fa-solid fa-person-dress"></i>
                </div>

            </div>


        </section>

    </main>

</div>


<script src="assets/js/dashboard.js"></script>

</body>
</html>