<?php
require_once "config/auth.php";
require_once "config/database.php";

$page_title = "Dashboard";

// Fetch live statistics
$res = $conn->query("SELECT COUNT(*) FROM customers");
$total_customers = $res ? (int)$res->fetch_row()[0] : 0;

$res = $conn->query("SELECT COUNT(*) FROM tailoring_orders");
$tailoring_orders = $res ? (int)$res->fetch_row()[0] : 0;

$res = $conn->query("SELECT COUNT(*) FROM rentals WHERE status IN ('Reserved', 'Rented')");
$active_rentals = $res ? (int)$res->fetch_row()[0] : 0;

$res = $conn->query("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE DATE(payment_date) = CURDATE()");
$today_sales = $res ? (float)$res->fetch_row()[0] : 0.00;

// Fetch Recent Orders (combine tailoring orders and rentals)
$recent_tailoring = $conn->query("
    SELECT t.order_id, c.customer_name, 'Tailoring' as order_type, t.service_type as item_name, t.status, t.due_date, t.total_amount
    FROM tailoring_orders t
    LEFT JOIN customers c ON t.customer_id = c.customer_id
    ORDER BY t.created_at DESC
    LIMIT 5
");

// Fetch Upcoming Appointments
$upcoming_appointments = $conn->query("
    SELECT a.appointment_id, c.customer_name, a.appointment_date, a.appointment_time, a.service_type, a.status
    FROM appointments a
    LEFT JOIN customers c ON a.customer_id = c.customer_id
    WHERE a.status IN ('Scheduled', 'Confirmed')
    ORDER BY a.appointment_date ASC, a.appointment_time ASC
    LIMIT 4
");

// Fetch Inventory items
$top_inventory = $conn->query("
    SELECT item_name, category, rental_price, quantity, status
    FROM inventory
    ORDER BY quantity DESC
    LIMIT 4
");

include __DIR__ . '/includes/header.php';
?>

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
            <h2><?php echo $total_customers; ?></h2>
            <span class="positive"><i class="fa-solid fa-circle-check"></i> Registered</span>
        </div>
        <div class="mini-chart pink-chart">
            <span></span><span></span><span></span><span></span><span></span>
        </div>
    </div>

    <!-- Tailoring -->
    <div class="stat-card">
        <div class="stat-icon purple">
            <i class="fa-solid fa-scissors"></i>
        </div>
        <div class="stat-content">
            <p>Tailoring Orders</p>
            <h2><?php echo $tailoring_orders; ?></h2>
            <span class="purple-text"><i class="fa-solid fa-clock"></i> Total recorded</span>
        </div>
        <div class="mini-chart purple-chart">
            <span></span><span></span><span></span><span></span><span></span>
        </div>
    </div>

    <!-- Rentals -->
    <div class="stat-card">
        <div class="stat-icon teal">
            <i class="fa-solid fa-person-dress"></i>
        </div>
        <div class="stat-content">
            <p>Active Rentals</p>
            <h2><?php echo $active_rentals; ?></h2>
            <span class="teal-text"><i class="fa-solid fa-calendar-check"></i> Reserved / Rented</span>
        </div>
        <div class="mini-chart teal-chart">
            <span></span><span></span><span></span><span></span><span></span>
        </div>
    </div>

    <!-- Sales -->
    <div class="stat-card">
        <div class="stat-icon orange">
            <i class="fa-solid fa-peso-sign"></i>
        </div>
        <div class="stat-content">
            <p>Today's Payments</p>
            <h2>₱<?php echo number_format($today_sales, 2); ?></h2>
            <span class="positive"><i class="fa-solid fa-coins"></i> Today</span>
        </div>
        <div class="mini-chart orange-chart">
            <span></span><span></span><span></span><span></span><span></span>
        </div>
    </div>

</div>

<!-- ==================================
     MIDDLE SECTION
=================================== -->
<div class="middle-grid">

    <!-- Recent Tailoring Orders -->
    <div class="panel recent-orders">
        <div class="panel-header">
            <div>
                <i class="fa-solid fa-list"></i>
                <h2>Recent Tailoring Orders</h2>
            </div>
            <a href="orders.php" class="btn btn-sm btn-secondary">View All</a>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ORDER ID</th>
                        <th>CUSTOMER</th>
                        <th>TYPE</th>
                        <th>SERVICE / ITEM</th>
                        <th>STATUS</th>
                        <th>DUE DATE</th>
                        <th>AMOUNT</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($recent_tailoring && $recent_tailoring->num_rows > 0): ?>
                        <?php while ($row = $recent_tailoring->fetch_assoc()): ?>
                            <tr>
                                <td><strong>#TO-<?php echo str_pad($row['order_id'], 4, '0', STR_PAD_LEFT); ?></strong></td>
                                <td><?php echo htmlspecialchars($row['customer_name'] ?? 'Walk-in Customer'); ?></td>
                                <td>Tailoring</td>
                                <td><?php echo htmlspecialchars($row['item_name'] ?? 'Custom Order'); ?></td>
                                <td>
                                    <?php 
                                    $st = strtolower($row['status'] ?? '');
                                    $badge = 'badge-pending';
                                    if ($st === 'in progress') $badge = 'badge-progress';
                                    elseif ($st === 'ready') $badge = 'badge-ready';
                                    elseif ($st === 'completed') $badge = 'badge-completed';
                                    elseif ($st === 'cancelled') $badge = 'badge-cancelled';
                                    ?>
                                    <span class="badge <?php echo $badge; ?>"><?php echo htmlspecialchars($row['status'] ?? 'Pending'); ?></span>
                                </td>
                                <td><?php echo $row['due_date'] ? date('M d, Y', strtotime($row['due_date'])) : '—'; ?></td>
                                <td><strong>₱<?php echo number_format($row['total_amount'] ?? 0, 2); ?></strong></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align:center; padding: 24px; color: #9ca3af;">
                                No tailoring orders recorded yet. <a href="orders.php" style="color: #8b5cf6; font-weight:600;">Create an Order</a>
                            </td>
                        </tr>
                    <?php endif; ?>
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
            <a href="appointment.php" class="btn btn-sm btn-secondary">View All</a>
        </div>

        <div class="appointment-list">
            <?php if ($upcoming_appointments && $upcoming_appointments->num_rows > 0): ?>
                <?php while ($app = $upcoming_appointments->fetch_assoc()): ?>
                    <div class="appointment-item">
                        <div class="date-box">
                            <small><?php echo strtoupper(date('M', strtotime($app['appointment_date']))); ?></small>
                            <strong><?php echo date('d', strtotime($app['appointment_date'])); ?></strong>
                            <span><?php echo strtoupper(date('D', strtotime($app['appointment_date']))); ?></span>
                        </div>
                        <div class="appointment-info">
                            <strong><?php echo htmlspecialchars($app['service_type']); ?></strong>
                            <p><?php echo htmlspecialchars($app['customer_name'] ?? 'Customer'); ?></p>
                            <small><?php echo date('h:i A', strtotime($app['appointment_time'])); ?></small>
                        </div>
                        <span class="scheduled"><?php echo htmlspecialchars($app['status']); ?></span>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="text-align:center; padding: 30px 10px; color: #9ca3af;">
                    <i class="fa-regular fa-calendar-xmark" style="font-size: 28px; margin-bottom: 8px; opacity: 0.6;"></i>
                    <p>No upcoming appointments.</p>
                    <a href="appointment.php" class="btn btn-sm btn-primary" style="margin-top: 10px;">Schedule One</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<!-- ==================================
     BOTTOM SECTION / INVENTORY OVERVIEW
=================================== -->
<div class="middle-grid" style="grid-template-columns: 1fr;">
    <div class="panel">
        <div class="panel-header">
            <div>
                <i class="fa-solid fa-box"></i>
                <h2>Rental Inventory Highlights</h2>
            </div>
            <a href="inventory.php" class="btn btn-sm btn-secondary">Manage Inventory</a>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ITEM NAME</th>
                        <th>CATEGORY</th>
                        <th>RENTAL PRICE</th>
                        <th>AVAILABLE STOCK</th>
                        <th>STATUS</th>
                        <th>ACTION</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($top_inventory && $top_inventory->num_rows > 0): ?>
                        <?php while ($inv = $top_inventory->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($inv['item_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($inv['category']); ?></td>
                                <td>₱<?php echo number_format($inv['rental_price'], 2); ?></td>
                                <td><?php echo (int)$inv['quantity']; ?> pcs</td>
                                <td>
                                    <?php 
                                    $st = strtolower($inv['status'] ?? '');
                                    $badge = 'badge-available';
                                    if ($st === 'low stock') $badge = 'badge-lowstock';
                                    elseif ($st === 'out of stock') $badge = 'badge-outstock';
                                    ?>
                                    <span class="badge <?php echo $badge; ?>"><?php echo htmlspecialchars($inv['status']); ?></span>
                                </td>
                                <td>
                                    <a href="rentals.php" class="btn btn-sm btn-primary">Rent Item</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align:center; padding: 20px; color: #9ca3af;">
                                No inventory items added yet. <a href="inventory.php" style="color:#8b5cf6; font-weight:600;">Add Items</a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
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
        <h2>Welcome back, <?php echo htmlspecialchars($_SESSION['full_name'] ?? 'Admin'); ?>! 👋</h2>
        <p>Manage your tailoring and rental shop easily.</p>
    </div>
    <div class="quote">
        <p>"Style is a way to say who you are without having to speak."</p>
    </div>
    <div class="banner-dress">
        <i class="fa-solid fa-person-dress"></i>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
