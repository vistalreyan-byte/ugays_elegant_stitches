<?php
require_once "config/auth.php";
require_once "config/database.php";

$page_title = "Customer Management";

$message = "";
$message_type = "";

/* =========================
   DELETE CUSTOMER
========================= */
if (isset($_GET['delete'])) {
    $customer_id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM customers WHERE customer_id = ?");
    $stmt->bind_param("i", $customer_id);

    if ($stmt->execute()) {
        $message = "Customer deleted successfully.";
        $message_type = "success";
    } else {
        $message = "Unable to delete customer. Ensure no dependent orders or rentals exist.";
        $message_type = "error";
    }
    $stmt->close();
}

/* =========================
   ADD / UPDATE CUSTOMER
========================= */
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $customer_id = isset($_POST["customer_id"]) ? (int)$_POST["customer_id"] : 0;
    $customer_name = trim($_POST["customer_name"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $address = trim($_POST["address"] ?? "");

    if (empty($customer_name)) {
        $message = "Customer full name is required.";
        $message_type = "error";
    } else {
        if ($customer_id > 0) {
            $stmt = $conn->prepare(
                "UPDATE customers
                 SET customer_name = ?, phone = ?, email = ?, address = ?
                 WHERE customer_id = ?"
            );
            $stmt->bind_param("ssssi", $customer_name, $phone, $email, $address, $customer_id);

            if ($stmt->execute()) {
                $message = "Customer updated successfully.";
                $message_type = "success";
            } else {
                $message = "Unable to update customer.";
                $message_type = "error";
            }
            $stmt->close();
        } else {
            $stmt = $conn->prepare(
                "INSERT INTO customers (customer_name, phone, email, address)
                 VALUES (?, ?, ?, ?)"
            );
            $stmt->bind_param("ssss", $customer_name, $phone, $email, $address);

            if ($stmt->execute()) {
                $message = "Customer added successfully.";
                $message_type = "success";
            } else {
                $message = "Unable to add customer.";
                $message_type = "error";
            }
            $stmt->close();
        }
    }
}

/* =========================
   GET CUSTOMER FOR EDIT
========================= */
$edit_customer = null;
if (isset($_GET['edit'])) {
    $customer_id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM customers WHERE customer_id = ?");
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $edit_customer = $result->fetch_assoc();
    }
    $stmt->close();
}

/* =========================
   SEARCH CUSTOMERS
========================= */
$search = trim($_GET['search'] ?? "");
if ($search !== "") {
    $search_value = "%" . $search . "%";
    $stmt = $conn->prepare(
        "SELECT * FROM customers
         WHERE customer_name LIKE ? OR phone LIKE ? OR email LIKE ? OR address LIKE ?
         ORDER BY customer_id DESC"
    );
    $stmt->bind_param("ssss", $search_value, $search_value, $search_value, $search_value);
    $stmt->execute();
    $customers = $stmt->get_result();
} else {
    $customers = $conn->query("SELECT * FROM customers ORDER BY customer_id DESC");
}

include __DIR__ . '/includes/header.php';
?>

<?php if ($message !== ""): ?>
    <div class="msg <?= $message_type === "error" ? "error" : "" ?>">
        <i class="fa-solid <?= $message_type === "error" ? "fa-circle-exclamation" : "fa-circle-check" ?>"></i>
        <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>

<!-- Customer Form -->
<div class="panel">
    <h2>
        <i class="fa-solid <?= $edit_customer ? 'fa-user-pen' : 'fa-user-plus' ?>" style="color: #8b5cf6;"></i>
        <?= $edit_customer ? "Edit Customer" : "Add New Customer" ?>
    </h2>

    <form method="POST" action="customers.php">
        <input type="hidden" name="customer_id" value="<?= $edit_customer ? $edit_customer['customer_id'] : 0 ?>">

        <div class="form-grid">
            <div class="form-group">
                <label>Customer Full Name *</label>
                <input type="text" name="customer_name" required value="<?= $edit_customer ? htmlspecialchars($edit_customer['customer_name']) : '' ?>" placeholder="e.g. Maria Santos">
            </div>

            <div class="form-group">
                <label>Contact / Phone Number</label>
                <input type="text" name="phone" value="<?= $edit_customer ? htmlspecialchars($edit_customer['phone']) : '' ?>" placeholder="e.g. 09171234567">
            </div>

            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" value="<?= $edit_customer ? htmlspecialchars($edit_customer['email']) : '' ?>" placeholder="e.g. maria@example.com">
            </div>

            <div class="form-group full">
                <label>Address</label>
                <textarea name="address" placeholder="Complete address..."><?= $edit_customer ? htmlspecialchars($edit_customer['address'] ?? '') : '' ?></textarea>
            </div>
        </div>

        <div class="actions">
            <button type="submit" class="btn btn-success">
                <i class="fa-solid fa-floppy-disk"></i>
                <?= $edit_customer ? "Update Customer" : "Save Customer" ?>
            </button>
            <?php if ($edit_customer): ?>
                <a href="customers.php" class="btn btn-secondary">
                    <i class="fa-solid fa-xmark"></i> Cancel
                </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Customer Directory -->
<div class="panel">
    <div class="panel-header">
        <div>
            <i class="fa-solid fa-users" style="color: #e83e8c;"></i>
            <h2>Customer Directory</h2>
        </div>

        <div class="filter-bar" style="margin-bottom:0;">
            <form method="GET" action="customers.php" class="search-form">
                <input type="text" name="search" placeholder="Search by name, phone, or email..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
                <?php if ($search !== ""): ?>
                    <a href="customers.php" class="btn btn-secondary btn-sm"><i class="fa-solid fa-xmark"></i> Clear</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>CUSTOMER NAME</th>
                    <th>PHONE</th>
                    <th>EMAIL</th>
                    <th>ADDRESS</th>
                    <th>REGISTERED</th>
                    <th style="text-align: right;">ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($customers && $customers->num_rows > 0): ?>
                    <?php while ($c = $customers->fetch_assoc()): ?>
                        <tr>
                            <td>#C-<?= str_pad($c['customer_id'], 4, '0', STR_PAD_LEFT) ?></td>
                            <td><strong><?= htmlspecialchars($c['customer_name']) ?></strong></td>
                            <td><?= htmlspecialchars($c['phone'] ?: '—') ?></td>
                            <td><?= htmlspecialchars($c['email'] ?: '—') ?></td>
                            <td><?= htmlspecialchars($c['address'] ?: '—') ?></td>
                            <td><?= date('M d, Y', strtotime($c['created_at'])) ?></td>
                            <td style="text-align: right;">
                                <a href="customers.php?edit=<?= $c['customer_id'] ?>" class="btn btn-sm btn-secondary" title="Edit">
                                    <i class="fa-solid fa-pen"></i> Edit
                                </a>
                                <a href="customers.php?delete=<?= $c['customer_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this customer?');" title="Delete">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align:center; padding: 24px; color: #9ca3af;">
                            No customers found. Fill in the form above to register your first customer.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
