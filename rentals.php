<?php
require_once "config/auth.php";
require_once "config/database.php";

$page_title = "Rental Management";
$message = "";
$message_type = "";

// Mark as Returned quick action
if (isset($_GET['mark_returned'])) {
    $rental_id = (int)$_GET['mark_returned'];
    $today = date('Y-m-d');
    $stmt = $conn->prepare("UPDATE rentals SET status = 'Returned', return_date = ? WHERE rental_id = ?");
    $stmt->bind_param("si", $today, $rental_id);
    if ($stmt->execute()) {
        $message = "Rental #RO-" . str_pad($rental_id, 4, '0', STR_PAD_LEFT) . " marked as Returned.";
        $message_type = "success";
    }
    $stmt->close();
}

// Delete Rental
if (isset($_GET['delete'])) {
    $rental_id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM rentals WHERE rental_id = ?");
    $stmt->bind_param("i", $rental_id);
    if ($stmt->execute()) {
        $message = "Rental record deleted successfully.";
        $message_type = "success";
    } else {
        $message = "Unable to delete rental record.";
        $message_type = "error";
    }
    $stmt->close();
}

// Add or Update Rental
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $rental_id = isset($_POST["rental_id"]) ? (int)$_POST["rental_id"] : 0;
    $customer_id = (int)($_POST["customer_id"] ?? 0);
    $inventory_id = (int)($_POST["inventory_id"] ?? 0);
    $rental_date = !empty($_POST["rental_date"]) ? $_POST["rental_date"] : date('Y-m-d');
    $due_date = !empty($_POST["due_date"]) ? $_POST["due_date"] : date('Y-m-d', strtotime('+3 days'));
    $return_date = !empty($_POST["return_date"]) ? $_POST["return_date"] : null;
    $rental_fee = (float)($_POST["rental_fee"] ?? 0.00);
    $status = $_POST["status"] ?? "Reserved";

    if ($customer_id <= 0 || $inventory_id <= 0) {
        $message = "Please select both a customer and an inventory item.";
        $message_type = "error";
    } else {
        if ($rental_id > 0) {
            $stmt = $conn->prepare(
                "UPDATE rentals
                 SET customer_id = ?, inventory_id = ?, rental_date = ?, due_date = ?, return_date = ?, rental_fee = ?, status = ?
                 WHERE rental_id = ?"
            );
            $stmt->bind_param("iisssdsi", $customer_id, $inventory_id, $rental_date, $due_date, $return_date, $rental_fee, $status, $rental_id);
            if ($stmt->execute()) {
                $message = "Rental record updated successfully.";
                $message_type = "success";
            } else {
                $message = "Failed to update rental: " . $conn->error;
                $message_type = "error";
            }
            $stmt->close();
        } else {
            $stmt = $conn->prepare(
                "INSERT INTO rentals (customer_id, inventory_id, rental_date, due_date, return_date, rental_fee, status)
                 VALUES (?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->bind_param("iisssds", $customer_id, $inventory_id, $rental_date, $due_date, $return_date, $rental_fee, $status);
            if ($stmt->execute()) {
                $message = "New rental transaction registered successfully.";
                $message_type = "success";
            } else {
                $message = "Failed to register rental: " . $conn->error;
                $message_type = "error";
            }
            $stmt->close();
        }
    }
}

// Fetch for Edit
$edit_rental = null;
if (isset($_GET['edit'])) {
    $rental_id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM rentals WHERE rental_id = ?");
    $stmt->bind_param("i", $rental_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
        $edit_rental = $res->fetch_assoc();
    }
    $stmt->close();
}

$preselected_item = (int)($_GET['item_id'] ?? 0);

// Dropdown lists
$customers_list = $conn->query("SELECT customer_id, customer_name, phone FROM customers ORDER BY customer_name ASC");
$inventory_list = $conn->query("SELECT inventory_id, item_name, category, rental_price, quantity, status FROM inventory ORDER BY item_name ASC");

// Filter and search
$search = trim($_GET['search'] ?? "");
$status_filter = trim($_GET['status'] ?? "");

$sql = "
    SELECT r.*, c.customer_name, c.phone, i.item_name, i.category
    FROM rentals r
    JOIN customers c ON r.customer_id = c.customer_id
    JOIN inventory i ON r.inventory_id = i.inventory_id
    WHERE 1=1
";
$params = [];
$types = "";

if ($search !== "") {
    $sql .= " AND (c.customer_name LIKE ? OR i.item_name LIKE ?)";
    $like = "%" . $search . "%";
    $params[] = $like; $params[] = $like;
    $types .= "ss";
}

if ($status_filter !== "") {
    $sql .= " AND r.status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

$sql .= " ORDER BY r.rental_id DESC";

if (!empty($params)) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $rentals = $stmt->get_result();
} else {
    $rentals = $conn->query($sql);
}

$rental_statuses = ['Reserved', 'Rented', 'Returned', 'Overdue', 'Cancelled'];

include __DIR__ . '/includes/header.php';
?>

<?php if ($message !== ""): ?>
    <div class="msg <?= $message_type === "error" ? "error" : "" ?>">
        <i class="fa-solid <?= $message_type === "error" ? "fa-circle-exclamation" : "fa-circle-check" ?>"></i>
        <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>

<!-- Rental Form -->
<div class="panel">
    <h2>
        <i class="fa-solid <?= $edit_rental ? 'fa-pen-to-square' : 'fa-person-dress' ?>" style="color: #14b8a6;"></i>
        <?= $edit_rental ? "Edit Rental #RO-" . str_pad($edit_rental['rental_id'], 4, '0', STR_PAD_LEFT) : "Record New Rental Transaction" ?>
    </h2>

    <form method="POST" action="rentals.php">
        <input type="hidden" name="rental_id" value="<?= $edit_rental ? $edit_rental['rental_id'] : 0 ?>">

        <div class="form-grid">
            <div class="form-group">
                <label>Customer *</label>
                <select name="customer_id" required>
                    <option value="">-- Select Customer --</option>
                    <?php if ($customers_list): ?>
                        <?php while ($c = $customers_list->fetch_assoc()): ?>
                            <option value="<?= $c['customer_id'] ?>" <?= ($edit_rental && $edit_rental['customer_id'] == $c['customer_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['customer_name']) ?> (<?= htmlspecialchars($c['phone'] ?: 'No Phone') ?>)
                            </option>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Rental Item (from Inventory) *</label>
                <select name="inventory_id" required id="inventorySelect" onchange="updatePrice(this)">
                    <option value="">-- Select Rental Item --</option>
                    <?php if ($inventory_list): ?>
                        <?php while ($it = $inventory_list->fetch_assoc()): ?>
                            <?php 
                            $selected = false;
                            if ($edit_rental && $edit_rental['inventory_id'] == $it['inventory_id']) $selected = true;
                            elseif (!$edit_rental && $preselected_item == $it['inventory_id']) $selected = true;
                            ?>
                            <option value="<?= $it['inventory_id'] ?>" data-price="<?= $it['rental_price'] ?>" <?= $selected ? 'selected' : '' ?>>
                                <?= htmlspecialchars($it['item_name']) ?> — ₱<?= number_format($it['rental_price'], 2) ?> (<?= $it['quantity'] ?> in stock)
                            </option>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Rental Date *</label>
                <input type="date" name="rental_date" required value="<?= $edit_rental ? htmlspecialchars($edit_rental['rental_date']) : date('Y-m-d') ?>">
            </div>

            <div class="form-group">
                <label>Due Date *</label>
                <input type="date" name="due_date" required value="<?= $edit_rental ? htmlspecialchars($edit_rental['due_date']) : date('Y-m-d', strtotime('+3 days')) ?>">
            </div>

            <div class="form-group">
                <label>Rental Fee (₱) *</label>
                <input type="number" step="0.01" min="0" name="rental_fee" id="rentalFeeInput" required value="<?= $edit_rental ? htmlspecialchars($edit_rental['rental_fee']) : '500.00' ?>">
            </div>

            <div class="form-group">
                <label>Status</label>
                <select name="status">
                    <?php foreach ($rental_statuses as $st): ?>
                        <option value="<?= $st ?>" <?= ($edit_rental && $edit_rental['status'] === $st) ? 'selected' : '' ?>><?= $st ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Return Date (if already returned)</label>
                <input type="date" name="return_date" value="<?= $edit_rental ? htmlspecialchars($edit_rental['return_date'] ?? '') : '' ?>">
            </div>
        </div>

        <div class="actions">
            <button type="submit" class="btn btn-success">
                <i class="fa-solid fa-floppy-disk"></i>
                <?= $edit_rental ? "Update Rental" : "Save Rental" ?>
            </button>
            <?php if ($edit_rental): ?>
                <a href="rentals.php" class="btn btn-secondary"><i class="fa-solid fa-xmark"></i> Cancel</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Rentals List -->
<div class="panel">
    <div class="panel-header">
        <div>
            <i class="fa-solid fa-list" style="color: #14b8a6;"></i>
            <h2>Rentals Log</h2>
        </div>

        <div class="filter-bar" style="margin-bottom:0;">
            <form method="GET" action="rentals.php" class="search-form">
                <select name="status" onchange="this.form.submit()" style="padding: 8px 12px; border-radius: 8px; border: 1.5px solid #e5e7eb; font-size: 12px;">
                    <option value="">All Statuses</option>
                    <?php foreach ($rental_statuses as $st): ?>
                        <option value="<?= $st ?>" <?= ($status_filter === $st) ? 'selected' : '' ?>><?= $st ?></option>
                    <?php endforeach; ?>
                </select>

                <input type="text" name="search" placeholder="Search customer or item..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
                <?php if ($search !== "" || $status_filter !== ""): ?>
                    <a href="rentals.php" class="btn btn-secondary btn-sm"><i class="fa-solid fa-xmark"></i> Clear</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>RENTAL ID</th>
                    <th>CUSTOMER</th>
                    <th>ITEM</th>
                    <th>RENT DATE</th>
                    <th>DUE DATE</th>
                    <th>RETURNED</th>
                    <th>FEE</th>
                    <th>STATUS</th>
                    <th style="text-align: right;">ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($rentals && $rentals->num_rows > 0): ?>
                    <?php while ($r = $rentals->fetch_assoc()): ?>
                        <?php 
                        $st = strtolower($r['status']);
                        $badge = 'badge-reserved';
                        if ($st === 'rented') $badge = 'badge-rented';
                        elseif ($st === 'returned') $badge = 'badge-returned';
                        elseif ($st === 'overdue') $badge = 'badge-overdue';
                        elseif ($st === 'cancelled') $badge = 'badge-cancelled';
                        ?>
                        <tr>
                            <td><strong>#RO-<?= str_pad($r['rental_id'], 4, '0', STR_PAD_LEFT) ?></strong></td>
                            <td>
                                <strong><?= htmlspecialchars($r['customer_name']) ?></strong><br>
                                <small style="color: #6b7280;"><?= htmlspecialchars($r['phone'] ?: 'No Phone') ?></small>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($r['item_name']) ?></strong><br>
                                <small style="color: #6b7280;"><?= htmlspecialchars($r['category']) ?></small>
                            </td>
                            <td><?= date('M d, Y', strtotime($r['rental_date'])) ?></td>
                            <td>
                                <strong><?= date('M d, Y', strtotime($r['due_date'])) ?></strong>
                                <?php if ($r['status'] === 'Rented' && strtotime($r['due_date']) < time()): ?>
                                    <span style="color:#ef4444; font-size:10px; font-weight:700;">(Overdue)</span>
                                <?php endif; ?>
                            </td>
                            <td><?= $r['return_date'] ? date('M d, Y', strtotime($r['return_date'])) : '—' ?></td>
                            <td><strong>₱<?= number_format($r['rental_fee'], 2) ?></strong></td>
                            <td><span class="badge <?= $badge ?>"><?= htmlspecialchars($r['status']) ?></span></td>
                            <td style="text-align: right;">
                                <?php if ($r['status'] !== 'Returned'): ?>
                                    <a href="rentals.php?mark_returned=<?= $r['rental_id'] ?>" class="btn btn-sm btn-success" title="Mark Returned">
                                        <i class="fa-solid fa-check"></i> Return
                                    </a>
                                <?php endif; ?>
                                <a href="rentals.php?edit=<?= $r['rental_id'] ?>" class="btn btn-sm btn-secondary" title="Edit">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                                <a href="rentals.php?delete=<?= $r['rental_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete rental record #RO-<?= $r['rental_id'] ?>?');" title="Delete">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" style="text-align:center; padding: 24px; color: #9ca3af;">
                            No rental records found. Use the form above to record a rental.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function updatePrice(select) {
    const selectedOption = select.options[select.selectedIndex];
    const price = selectedOption.getAttribute('data-price');
    if (price) {
        document.getElementById('rentalFeeInput').value = parseFloat(price).toFixed(2);
    }
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
