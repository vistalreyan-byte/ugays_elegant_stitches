<?php
require_once "config/auth.php";
require_once "config/database.php";

$page_title = "Appointments Calendar";
$message = "";
$message_type = "";

// Quick status change
if (isset($_GET['status_change']) && isset($_GET['id'])) {
    $appointment_id = (int)$_GET['id'];
    $new_status = $_GET['status_change'];
    $stmt = $conn->prepare("UPDATE appointments SET status = ? WHERE appointment_id = ?");
    $stmt->bind_param("si", $new_status, $appointment_id);
    if ($stmt->execute()) {
        $message = "Appointment status changed to " . htmlspecialchars($new_status) . ".";
        $message_type = "success";
    }
    $stmt->close();
}

// Delete Appointment
if (isset($_GET['delete'])) {
    $appointment_id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM appointments WHERE appointment_id = ?");
    $stmt->bind_param("i", $appointment_id);
    if ($stmt->execute()) {
        $message = "Appointment deleted successfully.";
        $message_type = "success";
    } else {
        $message = "Unable to delete appointment.";
        $message_type = "error";
    }
    $stmt->close();
}

// Add or Update Appointment
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $appointment_id = isset($_POST["appointment_id"]) ? (int)$_POST["appointment_id"] : 0;
    $customer_id = (int)($_POST["customer_id"] ?? 0);
    $appointment_date = !empty($_POST["appointment_date"]) ? $_POST["appointment_date"] : date('Y-m-d');
    $appointment_time = !empty($_POST["appointment_time"]) ? $_POST["appointment_time"] : '10:00:00';
    $service_type = trim($_POST["service_type"] ?? "Fitting");
    $notes = trim($_POST["notes"] ?? "");
    $status = $_POST["status"] ?? "Scheduled";

    if ($customer_id <= 0 || empty($service_type)) {
        $message = "Please select a customer and choose an appointment service type.";
        $message_type = "error";
    } else {
        if ($appointment_id > 0) {
            $stmt = $conn->prepare(
                "UPDATE appointments
                 SET customer_id = ?, appointment_date = ?, appointment_time = ?, service_type = ?, notes = ?, status = ?
                 WHERE appointment_id = ?"
            );
            $stmt->bind_param("isssssi", $customer_id, $appointment_date, $appointment_time, $service_type, $notes, $status, $appointment_id);
            if ($stmt->execute()) {
                $message = "Appointment updated successfully.";
                $message_type = "success";
            } else {
                $message = "Failed to update appointment: " . $conn->error;
                $message_type = "error";
            }
            $stmt->close();
        } else {
            $stmt = $conn->prepare(
                "INSERT INTO appointments (customer_id, appointment_date, appointment_time, service_type, notes, status)
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
            $stmt->bind_param("isssss", $customer_id, $appointment_date, $appointment_time, $service_type, $notes, $status);
            if ($stmt->execute()) {
                $message = "Appointment scheduled successfully.";
                $message_type = "success";
            } else {
                $message = "Failed to schedule appointment: " . $conn->error;
                $message_type = "error";
            }
            $stmt->close();
        }
    }
}

// Fetch for Edit
$edit_app = null;
if (isset($_GET['edit'])) {
    $appointment_id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM appointments WHERE appointment_id = ?");
    $stmt->bind_param("i", $appointment_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
        $edit_app = $res->fetch_assoc();
    }
    $stmt->close();
}

// Customers dropdown
$customers_list = $conn->query("SELECT customer_id, customer_name, phone FROM customers ORDER BY customer_name ASC");

// Filter and search
$search = trim($_GET['search'] ?? "");
$status_filter = trim($_GET['status'] ?? "");

$sql = "
    SELECT a.*, c.customer_name, c.phone
    FROM appointments a
    JOIN customers c ON a.customer_id = c.customer_id
    WHERE 1=1
";
$params = [];
$types = "";

if ($search !== "") {
    $sql .= " AND (c.customer_name LIKE ? OR a.service_type LIKE ? OR a.notes LIKE ?)";
    $like = "%" . $search . "%";
    $params[] = $like; $params[] = $like; $params[] = $like;
    $types .= "sss";
}

if ($status_filter !== "") {
    $sql .= " AND a.status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

$sql .= " ORDER BY a.appointment_date ASC, a.appointment_time ASC";

if (!empty($params)) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $appointments = $stmt->get_result();
} else {
    $appointments = $conn->query($sql);
}

$app_types = ['Fitting', 'Measurement', 'Pickup', 'Return', 'Consultation', 'Alteration Check'];
$app_statuses = ['Scheduled', 'Confirmed', 'Completed', 'Cancelled'];

include __DIR__ . '/includes/header.php';
?>

<?php if ($message !== ""): ?>
    <div class="msg <?= $message_type === "error" ? "error" : "" ?>">
        <i class="fa-solid <?= $message_type === "error" ? "fa-circle-exclamation" : "fa-circle-check" ?>"></i>
        <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>

<!-- Appointment Form -->
<div class="panel">
    <h2>
        <i class="fa-regular <?= $edit_app ? 'fa-calendar-check' : 'fa-calendar-plus' ?>" style="color: #f59e0b;"></i>
        <?= $edit_app ? "Edit Appointment #" . $edit_app['appointment_id'] : "Schedule New Appointment" ?>
    </h2>

    <form method="POST" action="appointment.php">
        <input type="hidden" name="appointment_id" value="<?= $edit_app ? $edit_app['appointment_id'] : 0 ?>">

        <div class="form-grid">
            <div class="form-group">
                <label>Customer *</label>
                <select name="customer_id" required>
                    <option value="">-- Select Customer --</option>
                    <?php if ($customers_list): ?>
                        <?php while ($c = $customers_list->fetch_assoc()): ?>
                            <option value="<?= $c['customer_id'] ?>" <?= ($edit_app && $edit_app['customer_id'] == $c['customer_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['customer_name']) ?> (<?= htmlspecialchars($c['phone'] ?: 'No Phone') ?>)
                            </option>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Service Type *</label>
                <select name="service_type">
                    <?php foreach ($app_types as $type): ?>
                        <option value="<?= $type ?>" <?= ($edit_app && $edit_app['service_type'] === $type) ? 'selected' : '' ?>><?= $type ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Appointment Date *</label>
                <input type="date" name="appointment_date" required value="<?= $edit_app ? htmlspecialchars($edit_app['appointment_date']) : date('Y-m-d') ?>">
            </div>

            <div class="form-group">
                <label>Appointment Time *</label>
                <input type="time" name="appointment_time" required value="<?= $edit_app ? htmlspecialchars(substr($edit_app['appointment_time'], 0, 5)) : '10:00' ?>">
            </div>

            <div class="form-group">
                <label>Status</label>
                <select name="status">
                    <?php foreach ($app_statuses as $st): ?>
                        <option value="<?= $st ?>" <?= ($edit_app && $edit_app['status'] === $st) ? 'selected' : '' ?>><?= $st ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group full">
                <label>Notes & Preparation Details</label>
                <textarea name="notes" placeholder="e.g. Bring shoes for fitting, test sleeve length, etc..."><?= $edit_app ? htmlspecialchars($edit_app['notes'] ?? '') : '' ?></textarea>
            </div>
        </div>

        <div class="actions">
            <button type="submit" class="btn btn-success">
                <i class="fa-solid fa-floppy-disk"></i>
                <?= $edit_app ? "Update Appointment" : "Book Appointment" ?>
            </button>
            <?php if ($edit_app): ?>
                <a href="appointment.php" class="btn btn-secondary"><i class="fa-solid fa-xmark"></i> Cancel</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Appointments List -->
<div class="panel">
    <div class="panel-header">
        <div>
            <i class="fa-regular fa-calendar-days" style="color: #f59e0b;"></i>
            <h2>Upcoming & Past Appointments</h2>
        </div>

        <div class="filter-bar" style="margin-bottom:0;">
            <form method="GET" action="appointment.php" class="search-form">
                <select name="status" onchange="this.form.submit()" style="padding: 8px 12px; border-radius: 8px; border: 1.5px solid #e5e7eb; font-size: 12px;">
                    <option value="">All Statuses</option>
                    <?php foreach ($app_statuses as $st): ?>
                        <option value="<?= $st ?>" <?= ($status_filter === $st) ? 'selected' : '' ?>><?= $st ?></option>
                    <?php endforeach; ?>
                </select>

                <input type="text" name="search" placeholder="Search customer or service..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
                <?php if ($search !== "" || $status_filter !== ""): ?>
                    <a href="appointment.php" class="btn btn-secondary btn-sm"><i class="fa-solid fa-xmark"></i> Clear</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>DATE & TIME</th>
                    <th>CUSTOMER</th>
                    <th>SERVICE TYPE</th>
                    <th>NOTES</th>
                    <th>STATUS</th>
                    <th style="text-align: right;">ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($appointments && $appointments->num_rows > 0): ?>
                    <?php while ($a = $appointments->fetch_assoc()): ?>
                        <?php 
                        $st = strtolower($a['status']);
                        $badge = 'badge-reserved';
                        if ($st === 'confirmed') $badge = 'badge-ready';
                        elseif ($st === 'completed') $badge = 'badge-completed';
                        elseif ($st === 'cancelled') $badge = 'badge-cancelled';
                        ?>
                        <tr>
                            <td>
                                <strong><?= date('M d, Y', strtotime($a['appointment_date'])) ?></strong><br>
                                <small style="color: #6b7280; font-weight:600;"><i class="fa-regular fa-clock"></i> <?= date('h:i A', strtotime($a['appointment_time'])) ?></small>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($a['customer_name']) ?></strong><br>
                                <small style="color: #6b7280;"><?= htmlspecialchars($a['phone'] ?: 'No Phone') ?></small>
                            </td>
                            <td><strong><?= htmlspecialchars($a['service_type']) ?></strong></td>
                            <td><?= htmlspecialchars($a['notes'] ?: '—') ?></td>
                            <td><span class="badge <?= $badge ?>"><?= htmlspecialchars($a['status']) ?></span></td>
                            <td style="text-align: right;">
                                <?php if ($a['status'] === 'Scheduled'): ?>
                                    <a href="appointment.php?status_change=Confirmed&id=<?= $a['appointment_id'] ?>" class="btn btn-sm btn-primary" title="Confirm">
                                        <i class="fa-solid fa-thumbs-up"></i> Confirm
                                    </a>
                                <?php elseif ($a['status'] === 'Confirmed'): ?>
                                    <a href="appointment.php?status_change=Completed&id=<?= $a['appointment_id'] ?>" class="btn btn-sm btn-success" title="Mark Completed">
                                        <i class="fa-solid fa-check-double"></i> Complete
                                    </a>
                                <?php endif; ?>
                                <a href="appointment.php?edit=<?= $a['appointment_id'] ?>" class="btn btn-sm btn-secondary" title="Edit">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                                <a href="appointment.php?delete=<?= $a['appointment_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Cancel and delete this appointment?');" title="Delete">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align:center; padding: 24px; color: #9ca3af;">
                            No appointments found. Use the form above to book an appointment.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
