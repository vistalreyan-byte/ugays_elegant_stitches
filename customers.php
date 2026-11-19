<?php

require_once "config/database.php";

$message = "";
$message_type = "";

/* =========================
   DELETE CUSTOMER
========================= */

if (isset($_GET['delete'])) {

    $customer_id = (int) $_GET['delete'];

    $stmt = $conn->prepare(
        "DELETE FROM customers WHERE customer_id = ?"
    );

    $stmt->bind_param("i", $customer_id);

    if ($stmt->execute()) {
        $message = "Customer deleted successfully.";
        $message_type = "success";
    } else {
        $message = "Unable to delete customer.";
        $message_type = "error";
    }

    $stmt->close();
}


/* =========================
   ADD / UPDATE CUSTOMER
========================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $customer_id = isset($_POST["customer_id"])
        ? (int) $_POST["customer_id"]
        : 0;

    $first_name = trim($_POST["first_name"]);
    $last_name = trim($_POST["last_name"]);
    $contact_number = trim($_POST["contact_number"]);
    $email = trim($_POST["email"]);
    $address = trim($_POST["address"]);


    /* ---------- UPDATE ---------- */

    if ($customer_id > 0) {

        $stmt = $conn->prepare(
            "UPDATE customers
             SET first_name = ?,
                 last_name = ?,
                 contact_number = ?,
                 email = ?,
                 address = ?
             WHERE customer_id = ?"
        );

        $stmt->bind_param(
            "sssssi",
            $first_name,
            $last_name,
            $contact_number,
            $email,
            $address,
            $customer_id
        );

        if ($stmt->execute()) {
            $message = "Customer updated successfully.";
            $message_type = "success";
        } else {
            $message = "Unable to update customer.";
            $message_type = "error";
        }

        $stmt->close();

    }

    /* ---------- ADD ---------- */

    else {

        $stmt = $conn->prepare(
            "INSERT INTO customers
            (first_name, last_name, contact_number, email, address)
            VALUES (?, ?, ?, ?, ?)"
        );

        $stmt->bind_param(
            "sssss",
            $first_name,
            $last_name,
            $contact_number,
            $email,
            $address
        );

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


/* =========================
   GET CUSTOMER FOR EDIT
========================= */

$edit_customer = null;

if (isset($_GET['edit'])) {

    $customer_id = (int) $_GET['edit'];

    $stmt = $conn->prepare(
        "SELECT *
         FROM customers
         WHERE customer_id = ?"
    );

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

$search = "";

if (isset($_GET['search'])) {
    $search = trim($_GET['search']);
}

if ($search !== "") {

    $search_value = "%" . $search . "%";

    $stmt = $conn->prepare(
        "SELECT *
         FROM customers
         WHERE first_name LIKE ?
            OR last_name LIKE ?
            OR contact_number LIKE ?
            OR email LIKE ?
         ORDER BY customer_id DESC"
    );

    $stmt->bind_param(
        "ssss",
        $search_value,
        $search_value,
        $search_value,
        $search_value
    );

    $stmt->execute();

    $customers = $stmt->get_result();

} else {

    $customers = $conn->query(
        "SELECT *
         FROM customers
         ORDER BY customer_id DESC"
    );
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Customers | Ugay's Elegant Stitches</title>

    <link rel="stylesheet"
          href="assets/css/style.css">

</head>

<body>

<div class="sidebar">

    <div class="brand">
        Ugay's Elegant Stitches
    </div>

    <nav class="nav">

        <a href="dashboard.php">
            🏠 Dashboard
        </a>

        <a href="customers.php" class="active">
            👥 Customers
        </a>

        <a href="orders.php">
            ✂️ Tailoring Orders
        </a>

        <a href="rentals.php">
            👗 Rentals
        </a>

        <a href="inventory.php">
            📦 Inventory
        </a>

        <a href="payments.php">
            💰 Payments
        </a>

        <a href="appointments.php">
            📅 Appointments
        </a>

        <a href="reports.php">
            📊 Reports
        </a>

    </nav>

</div>


<div class="main">

    <div class="topbar">

        <strong>Customers</strong>

        <span class="muted">
            Customer Management
        </span>

    </div>


    <div class="content">


        <!-- =========================
             MESSAGE
        ========================== -->

        <?php if ($message !== ""): ?>

            <div class="msg
                <?= $message_type === "error"
                    ? "error"
                    : "" ?>">

                <?= htmlspecialchars($message) ?>

            </div>

        <?php endif; ?>


        <!-- =========================
             CUSTOMER FORM
        ========================== -->

        <div class="panel">

            <h2>

                <?= $edit_customer
                    ? "Edit Customer"
                    : "Add New Customer" ?>

            </h2>


            <form method="POST"
                  action="customers.php">


                <input type="hidden"
                       name="customer_id"
                       value="<?= $edit_customer
                           ? $edit_customer['customer_id']
                           : 0 ?>">


                <div class="form-grid">


                    <!-- FIRST NAME -->

                    <div class="form-group">

                        <label>
                            First Name
                        </label>

                        <input
                            type="text"
                            name="first_name"
                            required
                            value="<?= $edit_customer
                                ? htmlspecialchars(
                                    $edit_customer['first_name']
                                )
                                : '' ?>"
                        >

                    </div>


                    <!-- LAST NAME -->

                    <div class="form-group">

                        <label>
                            Last Name
                        </label>

                        <input
                            type="text"
                            name="last_name"
                            required
                            value="<?= $edit_customer
                                ? htmlspecialchars(
                                    $edit_customer['last_name']
                                )
                                : '' ?>"
                        >

                    </div>


                    <!-- CONTACT -->

                    <div class="form-group">

                        <label>
                            Contact Number
                        </label>

                        <input
                            type="text"
                            name="contact_number"
                            value="<?= $edit_customer
                                ? htmlspecialchars(
                                    $edit_customer['contact_number']
                                )
                                : '' ?>"
                        >

                    </div>


                    <!-- EMAIL -->

                    <div class="form-group">

                        <label>
                            Email
                        </label>

                        <input
                            type="email"
                            name="email"
                            value="<?= $edit_customer
                                ? htmlspecialchars(
                                    $edit_customer['email']
                                )
                                : '' ?>"
                        >

                    </div>


                    <!-- ADDRESS -->

                    <div class="form-group full">

                        <label>
                            Address
                        </label>

                        <textarea
                            name="address"
                        ><?= $edit_customer
                            ? htmlspecialchars(
                                $edit_customer['address']
                            )
                            : '' ?></textarea>

                    </div>

                </div>


                <div class="actions">

                    <button
                        type="submit"
                        class="btn btn-success">

                        <?= $edit_customer
                            ? "Update Customer"
                            : "Add Customer" ?>

                    </button>


                    <?php if ($edit_customer): ?>

                        <a
                            href="customers.php"
                            class="btn btn-secondary">

                            Cancel

                        </a>

                    <?php endif; ?>

                </div>

            </form>

        </div>


        <!-- =========================
             CUSTOMER LIST
        ========================== -->

        <div class="panel">

            <h2>
                Customer List
            </h2>


            <!-- SEARCH -->

            <form method="GET"
                  action="customers.php">

                <input
                    type="text"
                    name="search"
                    class="search"
                    placeholder="Search customer..."
                    value="<?= htmlspecialchars($search) ?>"
                >

                <button
                    type="submit"
                    class="btn btn-primary">

                    Search

                </button>

                <?php if ($search !== ""): ?>

                    <a
                        href="customers.php"
                        class="btn btn-secondary">

                        Clear

                    </a>

                <?php endif; ?>

            </form>


            <br>


            <table>

                <thead>

                    <tr>

                        <th>ID</th>

                        <th>First Name</th>

                        <th>Last Name</th>

                        <th>Contact</th>

                        <th>Email</th>

                        <th>Address</th>

                        <th>Created</th>

                        <th>Actions</th>

                    </tr>

                </thead>


                <tbody>

                <?php if ($customers->num_rows > 0): ?>


                    <?php while (
                        $customer =
                        $customers->fetch_assoc()
                    ): ?>

                        <tr>

                            <td>
                                <?= $customer['customer_id'] ?>
                            </td>

                            <td>
                                <?= htmlspecialchars(
                                    $customer['first_name']
                                ) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars(
                                    $customer['last_name']
                                ) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars(
                                    $customer['contact_number']
                                ) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars(
                                    $customer['email']
                                ) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars(
                                    $customer['address']
                                ) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars(
                                    $customer['created_at']
                                ) ?>
                            </td>

                            <td>

                                <a
                                    href="customers.php?edit=<?= $customer['customer_id'] ?>"
                                    class="btn btn-secondary">

                                    Edit

                                </a>


                                <a
                                    href="customers.php?delete=<?= $customer['customer_id'] ?>"
                                    class="btn btn-danger"
                                    onclick="return confirm('Are you sure you want to delete this customer?');">

                                    Delete

                                </a>

                            </td>

                        </tr>

                    <?php endwhile; ?>


                <?php else: ?>

                    <tr>

                        <td
                            colspan="8"
                            style="text-align:center;">

                            No customers found.

                        </td>

                    </tr>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>


</body>

</html>