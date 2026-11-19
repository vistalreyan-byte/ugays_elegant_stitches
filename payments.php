<?php
require_once "config/auth.php";
require_once "config/database.php";

$page_title = "Payments & Transactions";
$message = "";
$message_type = "";

// Delete Payment
if (isset($_GET['delete'])) {
    $payment_id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM payments WHERE payment_id = ?");
    $stmt->bind_param("i", $payment_id);
    if ($stmt->execute()) {
        $message = "Payment record deleted successfully.";
        $message_type = "success";
    } else {
        $message = "Unable to delete payment.";
        $message_type = "error";
    }
    $stmt->close();
}

// Add Payment
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $customer_id = (int)($_POST["customer_id"] ?? 0);
    $order_id = !empty($_POST["order_id"]) ? (int)$_POST["order_id"] : null;
    $rental_id = !empty($_POST["rental_id"]) ? (int)$_POST["rental_id"] : null;
    $amount = (float)($_POST["amount"] ?? 0.00);
    $payment_method = $_POST["payment_method"] ?? "Cash";

    if ($customer_id <= 0 || $amount <= 0) {
        $message = "Please select a customer and enter a valid payment amount greater than zero.";
        $message_type = "error";
    } else {
        $stmt = $conn->prepare(
            "INSERT INTO payments (customer_id, order_id, rental_id, amount, payment_method)
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("iiids", $customer_id, $order_id, $rental_id, $amount, $payment_method);
        if ($stmt->execute()) {
            $message = "Payment of ₱" . number_format($amount, 2) . " recorded successfully.";
            $message_type = "success";
        } else {
            $message = "Failed to record payment: " . $conn->error;
            $message_type = "error";
        }
        $stmt->close();
    }
}

// Summary Statistics
$res = $conn->query("SELECT COALESCE(SUM(amount), 0) FROM payments");
$total_collected = $res ? (float)$res->fetch_row()[0] : 0.00;

$res = $conn->query("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE payment_method = 'Cash'");
$cash_total = $res ? (float)$res->fetch_row()[0] : 0.00;

$res = $conn->query("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE payment_method = 'GCash'");
$gcash_total = $res ? (float)$res->fetch_row()[0] : 0.00;

$res = $conn->query("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE DATE(payment_date) = CURDATE()");
$today_total = $res ? (float)$res->fetch_row()[0] : 0.00;

// Dropdowns for form
$customers_list = $conn->query("SELECT customer_id, customer_name, phone FROM customers ORDER BY customer_name ASC");
$orders_list = $conn->query("SELECT order_id, customer_id, service_type, total_amount FROM tailoring_orders ORDER BY order_id DESC");
$rentals_list = $conn->query("SELECT r.rental_id, r.customer_id, i.item_name, r.rental_fee FROM rentals r JOIN inventory i ON r.inventory_id = i.inventory_id ORDER BY r.rental_id DESC");

// Filter and search
$search = trim($_GET['search'] ?? "");
$method_filter = trim($_GET['method'] ?? "");

$sql = "
    SELECT p.*, c.customer_name, c.phone, t.service_type, i.item_name
    FROM payments p
    JOIN customers c ON p.customer_id = c.customer_id
    LEFT JOIN tailoring_orders t ON p.order_id = t.order_id
    LEFT JOIN rentals r ON p.rental_id = r.rental_id
    LEFT JOIN inventory i ON r.inventory_id = i.inventory_id
    WHERE 1=1
";
$params = [];
$types = "";

if ($search !== "") {
    $sql .= " AND (c.customer_name LIKE ? OR p.payment_method LIKE ?)";
    $like = "%" . $search . "%";
    $params[] = $like; $params[] = $like;
    $types .= "ss";
}

if ($method_filter !== "") {
    $sql .= " AND p.payment_method = ?";
    $params[] = $method_filter;
    $types .= "s";
}

$sql .= " ORDER BY p.payment_id DESC";

if (!empty($params)) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $payments = $stmt->get_result();
} else {
    $payments = $conn->query($sql);
}

$methods = ['Cash', 'GCash', 'Bank Transfer', 'Card'];

include __DIR__ . '/includes/header.php';
?>

<?php if ($message !== ""): ?>
    <div class="msg <?= $message_type === "error" ? "error" : "" ?>">
        <i class="fa-solid <?= $message_type === "error" ? "fa-circle-exclamation" : "fa-circle-check" ?>"></i>
        <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>

<!-- Payment Overview Cards -->
<div class="stats-grid" style="margin-bottom: 24px;">
    <div class="stat-card">
        <div class="stat-icon orange"><i class="fa-solid fa-sack-dollar"></i></div>
        <div class="stat-content">
            <p>Total Revenue</p>
            <h2>₱<?= number_format($total_collected, 2) ?></h2>
            <span class="positive">All-time payments</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon teal"><i class="fa-solid fa-coins"></i></div>
        <div class="stat-content">
            <p>Today's Collection</p>
            <h2>₱<?= number_format($today_total, 2) ?></h2>
            <span class="teal-text">Today</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon purple"><i class="fa-solid fa-money-bill-wave"></i></div>
        <div class="stat-content">
            <p>Cash Total</p>
            <h2>₱<?= number_format($cash_total, 2) ?></h2>
            <span class="purple-text">Cash transactions</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon blue"><i class="fa-solid fa-mobile-screen"></i></div>
        <div class="stat-content">
            <p>GCash Total</p>
            <h2>₱<?= number_format($gcash_total, 2) ?></h2>
            <span class="positive">Digital payments</span>
        </div>
    </div>
</div>

<!-- Record Payment Form -->
<div class="panel">
    <h2>
        <i class="fa-solid fa-cash-register" style="color: #22c55e;"></i>
        Record New Payment
    </h2>

    <form method="POST" action="payments.php">
        <div class="form-grid">
            <div class="form-group">
                <label>Customer *</label>
                <select name="customer_id" required>
                    <option value="">-- Select Customer --</option>
                    <?php if ($customers_list): ?>
                        <?php while ($c = $customers_list->fetch_assoc()): ?>
                            <option value="<?= $c['customer_id'] ?>">
                                <?= htmlspecialchars($c['customer_name']) ?> (<?= htmlspecialchars($c['phone'] ?: 'No Phone') ?>)
                            </option>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Payment Amount (₱) *</label>
                <input type="number" step="0.01" min="1" name="amount" required placeholder="0.00">
            </div>

            <div class="form-group">
                <label>Payment Method *</label>
                <select name="payment_method">
                    <?php foreach ($methods as $m): ?>
                        <option value="<?= $m ?>"><?= $m ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Link to Tailoring Order (Optional)</label>
                <select name="order_id">
                    <option value="">-- None / General Payment --</option>
                    <?php if ($orders_list): ?>
                        <?php while ($o = $orders_list->fetch_assoc()): ?>
                            <option value="<?= $o['order_id'] ?>">
                                #TO-<?= str_pad($o['order_id'], 4, '0', STR_PAD_LEFT) ?> (<?= htmlspecialchars($o['service_type']) ?> — ₱<?= number_format($o['total_amount'], 2) ?>)
                            </option>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Link to Rental (Optional)</label>
                <select name="rental_id">
                    <option value="">-- None / General Payment --</option>
                    <?php if ($rentals_list): ?>
                        <?php while ($r = $rentals_list->fetch_assoc()): ?>
                            <option value="<?= $r['rental_id'] ?>">
                                #RO-<?= str_pad($r['rental_id'], 4, '0', STR_PAD_LEFT) ?> (<?= htmlspecialchars($r['item_name']) ?> — ₱<?= number_format($r['rental_fee'], 2) ?>)
                            </option>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </select>
            </div>
        </div>

        <div class="actions">
            <button type="submit" class="btn btn-success">
                <i class="fa-solid fa-receipt"></i> Save & Record Payment
            </button>
        </div>
    </form>
</div>

<!-- Payments History Table -->
<div class="panel">
    <div class="panel-header">
        <div>
            <i class="fa-solid fa-receipt" style="color: #22c55e;"></i>
            <h2>Transactions & Payment History</h2>
        </div>

        <div class="filter-bar" style="margin-bottom:0;">
            <form method="GET" action="payments.php" class="search-form">
                <select name="method" onchange="this.form.submit()" style="padding: 8px 12px; border-radius: 8px; border: 1.5px solid #e5e7eb; font-size: 12px;">
                    <option value="">All Methods</option>
                    <?php foreach ($methods as $m): ?>
                        <option value="<?= $m ?>" <?= ($method_filter === $m) ? 'selected' : '' ?>><?= $m ?></option>
                    <?php endforeach; ?>
                </select>

                <input type="text" name="search" placeholder="Search customer or method..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
                <?php if ($search !== "" || $method_filter !== ""): ?>
                    <a href="payments.php" class="btn btn-secondary btn-sm"><i class="fa-solid fa-xmark"></i> Clear</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>PAYMENT ID</th>
                    <th>CUSTOMER</th>
                    <th>LINKED SERVICE</th>
                    <th>METHOD</th>
                    <th>AMOUNT</th>
                    <th>TRANSACTION DATE</th>
                    <th style="text-align: right;">ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($payments && $payments->num_rows > 0): ?>
                    <?php while ($p = $payments->fetch_assoc()): ?>
                        <tr>
                            <td><strong>#PAY-<?= str_pad($p['payment_id'], 4, '0', STR_PAD_LEFT) ?></strong></td>
                            <td>
                                <strong><?= htmlspecialchars($p['customer_name']) ?></strong><br>
                                <small style="color: #6b7280;"><?= htmlspecialchars($p['phone'] ?: 'No Phone') ?></small>
                            </td>
                            <td>
                                <?php if (!empty($p['order_id'])): ?>
                                    <span style="color:#7c3aed; font-weight:600;"><i class="fa-solid fa-scissors"></i> Order #TO-<?= str_pad($p['order_id'], 4, '0', STR_PAD_LEFT) ?> (<?= htmlspecialchars($p['service_type'] ?? '') ?>)</span>
                                <?php elseif (!empty($p['rental_id'])): ?>
                                    <span style="color:#14b8a6; font-weight:600;"><i class="fa-solid fa-person-dress"></i> Rental #RO-<?= str_pad($p['rental_id'], 4, '0', STR_PAD_LEFT) ?> (<?= htmlspecialchars($p['item_name'] ?? '') ?>)</span>
                                <?php else: ?>
                                    <span style="color: #6b7280;">General / Direct Payment</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php 
                                $pm = strtolower($p['payment_method']);
                                $icon = 'fa-money-bill';
                                if ($pm === 'gcash') $icon = 'fa-mobile-screen';
                                elseif ($pm === 'bank transfer') $icon = 'fa-building-columns';
                                elseif ($pm === 'card') $icon = 'fa-credit-card';
                                ?>
                                <span class="badge" style="background:#e0f2fe; color:#0369a1;"><i class="fa-solid <?= $icon ?>"></i> <?= htmlspecialchars($p['payment_method']) ?></span>
                            </td>
                            <td><strong style="color: #059669; font-size: 13px;">₱<?= number_format($p['amount'], 2) ?></strong></td>
                            <td><?= date('M d, Y h:i A', strtotime($p['payment_date'])) ?></td>
                            <td style="text-align: right;">
                                <a href="payments.php?delete=<?= $p['payment_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete payment #PAY-<?= $p['payment_id'] ?>?');" title="Delete">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align:center; padding: 24px; color: #9ca3af;">
                            No payment transactions found. Use the form above to record a payment.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
