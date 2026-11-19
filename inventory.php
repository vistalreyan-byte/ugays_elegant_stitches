<?php
require_once "config/auth.php";
require_once "config/database.php";

$page_title = "Rental Inventory";
$message = "";
$message_type = "";

// Delete Inventory Item
if (isset($_GET['delete'])) {
    $inventory_id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM inventory WHERE inventory_id = ?");
    $stmt->bind_param("i", $inventory_id);
    if ($stmt->execute()) {
        $message = "Inventory item removed successfully.";
        $message_type = "success";
    } else {
        $message = "Unable to delete inventory item. It may be linked to active rentals.";
        $message_type = "error";
    }
    $stmt->close();
}

// Add or Update Item
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $inventory_id = isset($_POST["inventory_id"]) ? (int)$_POST["inventory_id"] : 0;
    $item_name = trim($_POST["item_name"] ?? "");
    $category = trim($_POST["category"] ?? "Gown");
    $quantity = (int)($_POST["quantity"] ?? 1);
    $rental_price = (float)($_POST["rental_price"] ?? 0.00);
    $status = $_POST["status"] ?? "Available";

    // Auto update status based on quantity if not manually overridden
    if ($quantity <= 0) {
        $status = "Out of Stock";
    } elseif ($quantity <= 2 && $status !== "Out of Stock") {
        $status = "Low Stock";
    }

    if (empty($item_name)) {
        $message = "Item name is required.";
        $message_type = "error";
    } else {
        if ($inventory_id > 0) {
            $stmt = $conn->prepare(
                "UPDATE inventory
                 SET item_name = ?, category = ?, quantity = ?, rental_price = ?, status = ?
                 WHERE inventory_id = ?"
            );
            $stmt->bind_param("ssid si", $item_name, $category, $quantity, $rental_price, $status, $inventory_id);
            if ($stmt->execute()) {
                $message = "Inventory item updated successfully.";
                $message_type = "success";
            } else {
                $message = "Failed to update item: " . $conn->error;
                $message_type = "error";
            }
            $stmt->close();
        } else {
            $stmt = $conn->prepare(
                "INSERT INTO inventory (item_name, category, quantity, rental_price, status)
                 VALUES (?, ?, ?, ?, ?)"
            );
            $stmt->bind_param("ssids", $item_name, $category, $quantity, $rental_price, $status);
            if ($stmt->execute()) {
                $message = "New inventory item added successfully.";
                $message_type = "success";
            } else {
                $message = "Failed to add item: " . $conn->error;
                $message_type = "error";
            }
            $stmt->close();
        }
    }
}

// Fetch item for edit
$edit_item = null;
if (isset($_GET['edit'])) {
    $inventory_id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM inventory WHERE inventory_id = ?");
    $stmt->bind_param("i", $inventory_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
        $edit_item = $res->fetch_assoc();
    }
    $stmt->close();
}

// Search and filter
$search = trim($_GET['search'] ?? "");
$cat_filter = trim($_GET['category'] ?? "");

$sql = "SELECT * FROM inventory WHERE 1=1";
$params = [];
$types = "";

if ($search !== "") {
    $sql .= " AND item_name LIKE ?";
    $params[] = "%" . $search . "%";
    $types .= "s";
}

if ($cat_filter !== "") {
    $sql .= " AND category = ?";
    $params[] = $cat_filter;
    $types .= "s";
}

$sql .= " ORDER BY inventory_id DESC";

if (!empty($params)) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $inventory = $stmt->get_result();
} else {
    $inventory = $conn->query($sql);
}

$categories = ['Gown', 'Suit', 'Barong', 'Dress', 'Accessories', 'Traditional', 'Other'];

include __DIR__ . '/includes/header.php';
?>

<?php if ($message !== ""): ?>
    <div class="msg <?= $message_type === "error" ? "error" : "" ?>">
        <i class="fa-solid <?= $message_type === "error" ? "fa-circle-exclamation" : "fa-circle-check" ?>"></i>
        <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>

<!-- Inventory Form -->
<div class="panel">
    <h2>
        <i class="fa-solid <?= $edit_item ? 'fa-pen-to-square' : 'fa-box-open' ?>" style="color: #14b8a6;"></i>
        <?= $edit_item ? "Edit Item #" . $edit_item['inventory_id'] : "Add New Rental Inventory Item" ?>
    </h2>

    <form method="POST" action="inventory.php">
        <input type="hidden" name="inventory_id" value="<?= $edit_item ? $edit_item['inventory_id'] : 0 ?>">

        <div class="form-grid">
            <div class="form-group">
                <label>Item Name *</label>
                <input type="text" name="item_name" required value="<?= $edit_item ? htmlspecialchars($edit_item['item_name']) : '' ?>" placeholder="e.g. Royal Blue Debutante Gown, Slim-fit Navy Suit">
            </div>

            <div class="form-group">
                <label>Category *</label>
                <select name="category">
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat ?>" <?= ($edit_item && $edit_item['category'] === $cat) ? 'selected' : '' ?>><?= $cat ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Rental Price (₱ per rental) *</label>
                <input type="number" step="0.01" min="0" name="rental_price" required value="<?= $edit_item ? htmlspecialchars($edit_item['rental_price']) : '500.00' ?>">
            </div>

            <div class="form-group">
                <label>Quantity in Stock *</label>
                <input type="number" min="0" name="quantity" required value="<?= $edit_item ? htmlspecialchars($edit_item['quantity']) : '1' ?>">
            </div>

            <div class="form-group">
                <label>Status</label>
                <select name="status">
                    <?php
                    $inv_statuses = ['Available', 'Low Stock', 'Out of Stock'];
                    foreach ($inv_statuses as $st) {
                        $sel = ($edit_item && $edit_item['status'] === $st) ? 'selected' : '';
                        echo "<option value='{$st}' {$sel}>{$st}</option>";
                    }
                    ?>
                </select>
            </div>
        </div>

        <div class="actions">
            <button type="submit" class="btn btn-success">
                <i class="fa-solid fa-floppy-disk"></i>
                <?= $edit_item ? "Update Item" : "Add Item" ?>
            </button>
            <?php if ($edit_item): ?>
                <a href="inventory.php" class="btn btn-secondary"><i class="fa-solid fa-xmark"></i> Cancel</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Inventory List -->
<div class="panel">
    <div class="panel-header">
        <div>
            <i class="fa-solid fa-boxes-stacked" style="color: #14b8a6;"></i>
            <h2>Inventory Stock List</h2>
        </div>

        <div class="filter-bar" style="margin-bottom:0;">
            <form method="GET" action="inventory.php" class="search-form">
                <select name="category" onchange="this.form.submit()" style="padding: 8px 12px; border-radius: 8px; border: 1.5px solid #e5e7eb; font-size: 12px;">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat ?>" <?= ($cat_filter === $cat) ? 'selected' : '' ?>><?= $cat ?></option>
                    <?php endforeach; ?>
                </select>

                <input type="text" name="search" placeholder="Search item name..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
                <?php if ($search !== "" || $cat_filter !== ""): ?>
                    <a href="inventory.php" class="btn btn-secondary btn-sm"><i class="fa-solid fa-xmark"></i> Clear</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ITEM ID</th>
                    <th>ITEM NAME</th>
                    <th>CATEGORY</th>
                    <th>RENTAL FEE</th>
                    <th>STOCK QTY</th>
                    <th>STATUS</th>
                    <th style="text-align: right;">ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($inventory && $inventory->num_rows > 0): ?>
                    <?php while ($it = $inventory->fetch_assoc()): ?>
                        <?php 
                        $st = strtolower($it['status']);
                        $badge = 'badge-available';
                        if ($st === 'low stock') $badge = 'badge-lowstock';
                        elseif ($st === 'out of stock') $badge = 'badge-outstock';
                        ?>
                        <tr>
                            <td>#INV-<?= str_pad($it['inventory_id'], 4, '0', STR_PAD_LEFT) ?></td>
                            <td><strong><?= htmlspecialchars($it['item_name']) ?></strong></td>
                            <td><?= htmlspecialchars($it['category']) ?></td>
                            <td><strong>₱<?= number_format($it['rental_price'], 2) ?></strong></td>
                            <td><?= (int)$it['quantity'] ?> pcs</td>
                            <td><span class="badge <?= $badge ?>"><?= htmlspecialchars($it['status']) ?></span></td>
                            <td style="text-align: right;">
                                <a href="inventory.php?edit=<?= $it['inventory_id'] ?>" class="btn btn-sm btn-secondary" title="Edit">
                                    <i class="fa-solid fa-pen"></i> Edit
                                </a>
                                <a href="rentals.php?item_id=<?= $it['inventory_id'] ?>" class="btn btn-sm btn-primary" title="Rent Out">
                                    <i class="fa-solid fa-person-dress"></i> Rent
                                </a>
                                <a href="inventory.php?delete=<?= $it['inventory_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete <?= htmlspecialchars(addslashes($it['item_name'])) ?>?');" title="Delete">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align:center; padding: 24px; color: #9ca3af;">
                            No items found in inventory. Use the form above to add an item.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
