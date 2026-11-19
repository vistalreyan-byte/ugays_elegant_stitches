<?php
require_once "config/auth.php";
require_once "config/database.php";

$page_title = "Tailoring Orders";
$message = "";
$message_type = "";

// Delete Order
if (isset($_GET['delete'])) {
    $order_id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM tailoring_orders WHERE order_id = ?");
    $stmt->bind_param("i", $order_id);
    if ($stmt->execute()) {
        $message = "Tailoring order deleted successfully.";
        $message_type = "success";
    } else {
        $message = "Unable to delete order.";
        $message_type = "error";
    }
    $stmt->close();
}

// Add or Update Order
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $order_id = isset($_POST["order_id"]) ? (int)$_POST["order_id"] : 0;
    $customer_id = (int)($_POST["customer_id"] ?? 0);
    $service_type = trim($_POST["service_type"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $total_amount = (float)($_POST["total_amount"] ?? 0.00);
    $status = $_POST["status"] ?? "Pending";
    $due_date = !empty($_POST["due_date"]) ? $_POST["due_date"] : null;

    if ($customer_id <= 0 || empty($service_type)) {
        $message = "Please select a customer and specify the service type.";
        $message_type = "error";
    } else {
        if ($order_id > 0) {
            $stmt = $conn->prepare(
                "UPDATE tailoring_orders
                 SET customer_id = ?, service_type = ?, description = ?, total_amount = ?, status = ?, due_date = ?
                 WHERE order_id = ?"
            );
            $stmt->bind_param("issdssi", $customer_id, $service_type, $description, $total_amount, $status, $due_date, $order_id);
            if ($stmt->execute()) {
                $message = "Order updated successfully.";
                $message_type = "success";
            } else {
                $message = "Failed to update order: " . $conn->error;
                $message_type = "error";
            }
            $stmt->close();
        } else {
            $stmt = $conn->prepare(
                "INSERT INTO tailoring_orders (customer_id, service_type, description, total_amount, status, due_date)
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
            $stmt->bind_param("issdss", $customer_id, $service_type, $description, $total_amount, $status, $due_date);
            if ($stmt->execute()) {
                $message = "New tailoring order created successfully.";
                $message_type = "success";
            } else {
                $message = "Failed to create order: " . $conn->error;
                $message_type = "error";
            }
            $stmt->close();
        }
    }
}

// Fetch single order for editing
$edit_order = null;
if (isset($_GET['edit'])) {
    $order_id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM tailoring_orders WHERE order_id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
        $edit_order = $res->fetch_assoc();
    }
    $stmt->close();
}

// Customers list for dropdown
$customers_list = $conn->query("SELECT customer_id, customer_name, phone FROM customers ORDER BY customer_name ASC");

// Search and filter
$search = trim($_GET['search'] ?? "");
$status_filter = trim($_GET['status'] ?? "");

$sql = "
    SELECT t.*, c.customer_name, c.phone
    FROM tailoring_orders t
    JOIN customers c ON t.customer_id = c.customer_id
    WHERE 1=1
";

$params = [];
$types = "";

if ($search !== "") {
    $sql .= " AND (c.customer_name LIKE ? OR t.service_type LIKE ? OR t.description LIKE ?)";
    $like = "%" . $search . "%";
    $params[] = $like; $params[] = $like; $params[] = $like;
    $types .= "sss";
}

if ($status_filter !== "") {
    $sql .= " AND t.status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

$sql .= " ORDER BY t.order_id DESC";

if (!empty($params)) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $orders = $stmt->get_result();
} else {
    $orders = $conn->query($sql);
}

include __DIR__ . '/includes/header.php';
?>

<?php if ($message !== ""): ?>
    <div class="msg <?= $message_type === "error" ? "error" : "" ?>">
        <i class="fa-solid <?= $message_type === "error" ? "fa-circle-exclamation" : "fa-circle-check" ?>"></i>
        <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>

<!-- Order Form -->
<div class="panel">
    <h2>
        <i class="fa-solid <?= $edit_order ? 'fa-pen-to-square' : 'fa-scissors' ?>" style="color: #8b5cf6;"></i>
        <?= $edit_order ? "Edit Tailoring Order #TO-" . str_pad($edit_order['order_id'], 4, '0', STR_PAD_LEFT) : "Create New Tailoring Order" ?>
    </h2>

    <form method="POST" action="orders.php">
        <input type="hidden" name="order_id" value="<?= $edit_order ? $edit_order['order_id'] : 0 ?>">

        <div class="form-grid">
            <div class="form-group">
                <label>Customer *</label>
                <select name="customer_id" required>
                    <option value="">-- Select Customer --</option>
                    <?php if ($customers_list): ?>
                        <?php while ($c = $customers_list->fetch_assoc()): ?>
                            <option value="<?= $c['customer_id'] ?>" <?= ($edit_order && $edit_order['customer_id'] == $c['customer_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['customer_name']) ?> (<?= htmlspecialchars($c['phone'] ?: 'No Phone') ?>)
                            </option>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Service / Garment Type *</label>
                <input type="text" name="service_type" required value="<?= $edit_order ? htmlspecialchars($edit_order['service_type']) : '' ?>" placeholder="e.g. Evening Gown, 3-Piece Suit, Barong, Alteration">
            </div>

            <div class="form-group">
                <label>Total Amount (₱) *</label>
                <input type="number" step="0.01" min="0" name="total_amount" required value="<?= $edit_order ? htmlspecialchars($edit_order['total_amount']) : '0.00' ?>">
            </div>

            <div class="form-group">
                <label>Due / Target Date</label>
                <input type="date" name="due_date" value="<?= $edit_order ? htmlspecialchars($edit_order['due_date'] ?? '') : '' ?>">
            </div>

            <div class="form-group">
                <label>Status</label>
                <select name="status">
                    <?php
                    $statuses = ['Pending', 'In Progress', 'Ready', 'Completed', 'Cancelled'];
                    foreach ($statuses as $st) {
                        $sel = ($edit_order && $edit_order['status'] === $st) ? 'selected' : '';
                        echo "<option value='{$st}' {$sel}>{$st}</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="form-group full">
                <label>Garment Description & Measurements</label>
                <textarea name="description" placeholder="Notes, fabric details, special embroidery or measurement specs..."><?= $edit_order ? htmlspecialchars($edit_order['description'] ?? '') : '' ?></textarea>
            </div>
        </div>

        <div class="actions">
            <button type="submit" class="btn btn-success">
                <i class="fa-solid fa-floppy-disk"></i>
                <?= $edit_order ? "Update Order" : "Save Order" ?>
            </button>
            <?php if ($edit_order): ?>
                <a href="orders.php" class="btn btn-secondary"><i class="fa-solid fa-xmark"></i> Cancel</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Orders Table -->
<div class="panel">
    <div class="panel-header">
        <div>
            <i class="fa-solid fa-list-check" style="color: #8b5cf6;"></i>
            <h2>Tailoring Orders Log</h2>
        </div>

        <div class="filter-bar" style="margin-bottom:0;">
            <form method="GET" action="orders.php" class="search-form">
                <select name="status" onchange="this.form.submit()" style="padding: 8px 12px; border-radius: 8px; border: 1.5px solid #e5e7eb; font-size: 12px;">
                    <option value="">All Statuses</option>
                    <?php foreach ($statuses as $st): ?>
                        <option value="<?= $st ?>" <?= ($status_filter === $st) ? 'selected' : '' ?>><?= $st ?></option>
                    <?php endforeach; ?>
                </select>

                <input type="text" name="search" placeholder="Search customer or service..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
                <?php if ($search !== "" || $status_filter !== ""): ?>
                    <a href="orders.php" class="btn btn-secondary btn-sm"><i class="fa-solid fa-xmark"></i> Clear</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ORDER ID</th>
                    <th>CUSTOMER</th>
                    <th>SERVICE / ITEM</th>
                    <th>DUE DATE</th>
                    <th>AMOUNT</th>
                    <th>STATUS</th>
                    <th>CREATED</th>
                    <th style="text-align: right;">ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($orders && $orders->num_rows > 0): ?>
                    <?php while ($o = $orders->fetch_assoc()): ?>
                        <?php 
                        $st = strtolower($o['status']);
                        $badge = 'badge-pending';
                        if ($st === 'in progress') $badge = 'badge-progress';
                        elseif ($st === 'ready') $badge = 'badge-ready';
                        elseif ($st === 'completed') $badge = 'badge-completed';
                        elseif ($st === 'cancelled') $badge = 'badge-cancelled';
                        ?>
                        <tr>
                            <td><strong>#TO-<?= str_pad($o['order_id'], 4, '0', STR_PAD_LEFT) ?></strong></td>
                            <td>
                                <strong><?= htmlspecialchars($o['customer_name']) ?></strong><br>
                                <small style="color: #6b7280;"><?= htmlspecialchars($o['phone'] ?: 'No Phone') ?></small>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($o['service_type']) ?></strong>
                                <?php if (!empty($o['description'])): ?>
                                    <div style="font-size: 11px; color: #6b7280; max-width: 250px; white-space: normal;"><?= htmlspecialchars($o['description']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td><?= $o['due_date'] ? date('M d, Y', strtotime($o['due_date'])) : '—' ?></td>
                            <td><strong>₱<?= number_format($o['total_amount'], 2) ?></strong></td>
                            <td><span class="badge <?= $badge ?>"><?= htmlspecialchars($o['status']) ?></span></td>
                            <td><?= date('M d, Y', strtotime($o['created_at'])) ?></td>
                            <td style="text-align: right;">
                                <a href="orders.php?edit=<?= $o['order_id'] ?>" class="btn btn-sm btn-secondary" title="Edit">
                                    <i class="fa-solid fa-pen"></i> Edit
                                </a>
                                <a href="orders.php?delete=<?= $o['order_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete order #TO-<?= $o['order_id'] ?>?');" title="Delete">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align:center; padding: 24px; color: #9ca3af;">
                            No tailoring orders found. Use the form above to add an order.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
