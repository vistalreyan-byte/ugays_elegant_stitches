<?php
require_once "config/auth.php";
require_once "config/database.php";

$page_title = "Business Reports & Analytics";

// 1. Total Revenue Summary
$res = $conn->query("SELECT COALESCE(SUM(amount), 0) FROM payments");
$total_revenue = $res ? (float)$res->fetch_row()[0] : 0.00;

$res = $conn->query("SELECT COALESCE(SUM(total_amount), 0) FROM tailoring_orders WHERE status != 'Cancelled'");
$total_tailoring_value = $res ? (float)$res->fetch_row()[0] : 0.00;

$res = $conn->query("SELECT COALESCE(SUM(rental_fee), 0) FROM rentals WHERE status != 'Cancelled'");
$total_rental_value = $res ? (float)$res->fetch_row()[0] : 0.00;

// 2. Payments by Method
$payment_methods = $conn->query("
    SELECT payment_method, COUNT(*) as count, SUM(amount) as total
    FROM payments
    GROUP BY payment_method
");

// 3. Tailoring Orders by Status
$tailoring_status_counts = $conn->query("
    SELECT status, COUNT(*) as count, COALESCE(SUM(total_amount), 0) as total
    FROM tailoring_orders
    GROUP BY status
");

// 4. Rentals by Status
$rental_status_counts = $conn->query("
    SELECT status, COUNT(*) as count, COALESCE(SUM(rental_fee), 0) as total
    FROM rentals
    GROUP BY status
");

// 5. Top 5 Paying Customers
$top_customers = $conn->query("
    SELECT c.customer_id, c.customer_name, c.phone, COUNT(p.payment_id) as transactions, SUM(p.amount) as total_spent
    FROM payments p
    JOIN customers c ON p.customer_id = c.customer_id
    GROUP BY p.customer_id
    ORDER BY total_spent DESC
    LIMIT 5
");

include __DIR__ . '/includes/header.php';
?>

<!-- Revenue Summary Cards -->
<div class="stats-grid" style="margin-bottom: 24px;">
    <div class="stat-card">
        <div class="stat-icon orange"><i class="fa-solid fa-coins"></i></div>
        <div class="stat-content">
            <p>Total Cash Collected</p>
            <h2>₱<?= number_format($total_revenue, 2) ?></h2>
            <span class="positive">All recorded payments</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon purple"><i class="fa-solid fa-scissors"></i></div>
        <div class="stat-content">
            <p>Tailoring Volume</p>
            <h2>₱<?= number_format($total_tailoring_value, 2) ?></h2>
            <span class="purple-text">Active & completed orders</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon teal"><i class="fa-solid fa-person-dress"></i></div>
        <div class="stat-content">
            <p>Rental Volume</p>
            <h2>₱<?= number_format($total_rental_value, 2) ?></h2>
            <span class="teal-text">Active & completed rentals</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon pink"><i class="fa-solid fa-print"></i></div>
        <div class="stat-content">
            <p>Export Report</p>
            <h2 style="font-size: 18px; margin-top: 4px;">Print Summary</h2>
            <button onclick="window.print()" class="btn btn-sm btn-pink" style="margin-top: 6px;"><i class="fa-solid fa-print"></i> Print Report</button>
        </div>
    </div>
</div>

<div class="middle-grid">

    <!-- Payment Methods Breakdown -->
    <div class="panel">
        <div class="panel-header">
            <div>
                <i class="fa-solid fa-credit-card" style="color: #3b82f6;"></i>
                <h2>Collections by Payment Channel</h2>
            </div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>CHANNEL</th>
                        <th>TRANSACTIONS</th>
                        <th style="text-align: right;">TOTAL COLLECTED</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($payment_methods && $payment_methods->num_rows > 0): ?>
                        <?php while ($pm = $payment_methods->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($pm['payment_method']) ?></strong></td>
                                <td><?= (int)$pm['count'] ?> payments</td>
                                <td style="text-align: right;"><strong>₱<?= number_format($pm['total'], 2) ?></strong></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="3" style="text-align:center; padding: 20px; color:#9ca3af;">No payment records.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tailoring Orders Breakdown -->
    <div class="panel">
        <div class="panel-header">
            <div>
                <i class="fa-solid fa-scissors" style="color: #8b5cf6;"></i>
                <h2>Tailoring Pipeline Breakdown</h2>
            </div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>STATUS</th>
                        <th>COUNT</th>
                        <th style="text-align: right;">ESTIMATED VALUE</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($tailoring_status_counts && $tailoring_status_counts->num_rows > 0): ?>
                        <?php while ($ts = $tailoring_status_counts->fetch_assoc()): ?>
                            <tr>
                                <td><span class="badge badge-progress"><?= htmlspecialchars($ts['status']) ?></span></td>
                                <td><?= (int)$ts['count'] ?> orders</td>
                                <td style="text-align: right;"><strong>₱<?= number_format($ts['total'], 2) ?></strong></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="3" style="text-align:center; padding: 20px; color:#9ca3af;">No tailoring orders.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Top Customers -->
<div class="panel">
    <div class="panel-header">
        <div>
            <i class="fa-solid fa-star" style="color: #f59e0b;"></i>
            <h2>Top Valued Customers</h2>
        </div>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>CUSTOMER ID</th>
                    <th>CUSTOMER NAME</th>
                    <th>CONTACT</th>
                    <th>COMPLETED TRANSACTIONS</th>
                    <th style="text-align: right;">TOTAL PAYMENTS</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($top_customers && $top_customers->num_rows > 0): ?>
                    <?php while ($tc = $top_customers->fetch_assoc()): ?>
                        <tr>
                            <td>#C-<?= str_pad($tc['customer_id'], 4, '0', STR_PAD_LEFT) ?></td>
                            <td><strong><?= htmlspecialchars($tc['customer_name']) ?></strong></td>
                            <td><?= htmlspecialchars($tc['phone'] ?: '—') ?></td>
                            <td><?= (int)$tc['transactions'] ?> times</td>
                            <td style="text-align: right;"><strong style="color: #059669; font-size:13px;">₱<?= number_format($tc['total_spent'], 2) ?></strong></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" style="text-align:center; padding: 20px; color:#9ca3af;">No transactions recorded yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
