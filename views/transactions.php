<?php
require_once '../includes/config.php';

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$connection = mysqli_connect($db_host, $db_user, $db_pass, $db_name);
if (!$connection) {
    die("Database connection failed: " . mysqli_connect_error());
}
mysqli_set_charset($connection, 'utf8');

$message = '';
$message_type = '';

// Handle form submissions
if (isset($_POST['create_transaction'])) {
    $borrower_id = mysqli_real_escape_string($connection, $_POST['borrower_id']);
    $activity_purpose = mysqli_real_escape_string($connection, $_POST['activity_purpose']);
    $place_of_activity = mysqli_real_escape_string($connection, $_POST['place_of_activity']);
    $date_requested = mysqli_real_escape_string($connection, $_POST['date_requested']);
    $date_needed = mysqli_real_escape_string($connection, $_POST['date_needed']);
    $date_of_return = mysqli_real_escape_string($connection, $_POST['date_of_return']);

    // Check availability for each item based on dates
    $insufficient_items = array();
    if (isset($_POST['items']) && is_array($_POST['items'])) {
        foreach ($_POST['items'] as $item_id => $quantity) {
            $item_id_clean = mysqli_real_escape_string($connection, $item_id);
            $quantity = intval($quantity);

            // Get total quantity
            $total_sql = "SELECT total_quantity FROM items WHERE item_id = '$item_id_clean'";
            $total_result = mysqli_query($connection, $total_sql);
            $total_row = mysqli_fetch_assoc($total_result);
            $total_quantity = $total_row ? $total_row['total_quantity'] : 0;

            // Calculate committed quantity from overlapping transactions
            $committed_sql = "SELECT COALESCE(SUM(CASE WHEN t.status = 'issued' THEN (bi.quantity_issued - bi.quantity_returned) ELSE bi.quantity_required END), 0) as committed " .
                "FROM borrowing_transactions t JOIN borrowed_items bi ON t.transaction_id = bi.transaction_id " .
                "WHERE bi.item_id = '$item_id_clean' " .
                "AND t.status IN ('approved', 'issued') " .
                "AND t.date_needed <= '$date_of_return' " .
                "AND t.date_of_return >= '$date_needed'";
            $committed_result = mysqli_query($connection, $committed_sql);
            $committed_row = mysqli_fetch_assoc($committed_result);
            $committed = $committed_row ? $committed_row['committed'] : 0;

            $available = $total_quantity - $committed;
            if ($available < $quantity) {
                $insufficient_items[] = "Item ID $item_id_clean: Requested $quantity, Available $available";
            }
        }
    }

    if (!empty($insufficient_items)) {
        $message = 'Insufficient stock for the following items during the selected dates: ' . implode(', ', $insufficient_items);
        $message_type = 'error';
    } else {
        $insert_sql = "INSERT INTO borrowing_transactions (borrower_id, activity_purpose, place_of_activity, date_requested, date_needed, date_of_return, status, created_at) VALUES ('$borrower_id', '$activity_purpose', '$place_of_activity', '$date_requested', '$date_needed', '$date_of_return', 'pending', NOW())";

        if (mysqli_query($connection, $insert_sql)) {
            $transaction_id = mysqli_insert_id($connection);

            // Add items to transaction
            if (isset($_POST['items']) && is_array($_POST['items'])) {
                foreach ($_POST['items'] as $item_id => $quantity) {
                    $item_id = mysqli_real_escape_string($connection, $item_id);
                    $quantity = intval($quantity);
                    if ($quantity > 0) {
                        $item_insert_sql = "INSERT INTO borrowed_items (transaction_id, item_id, quantity_required) VALUES ('$transaction_id', '$item_id', $quantity)";
                        if (!mysqli_query($connection, $item_insert_sql)) {
                            $message = "Error adding item ID $item_id to transaction.";
                            $message_type = 'error';
                            break;
                        }
                    }
                }
            }

            if (empty($message)) {
                $message = 'Transaction created successfully!';
                $message_type = 'success';
            }
        } else {
            $message = 'Error creating transaction.';
            $message_type = 'error';
        }
    }
}

if (isset($_POST['approve_transaction'])) {
    $transaction_id = mysqli_real_escape_string($connection, $_POST['transaction_id']);
    $admin_id = mysqli_real_escape_string($connection, $_SESSION['admin_id']);

    $update_sql = "UPDATE borrowing_transactions SET status='approved' WHERE transaction_id='$transaction_id'";

    if (mysqli_query($connection, $update_sql)) {
        $approval_sql = "INSERT INTO approvals (transaction_id, approval_type, admin_id) VALUES ('$transaction_id', 'noted_by', '$admin_id')";
        mysqli_query($connection, $approval_sql);
        $message = 'Transaction approved successfully!';
        $message_type = 'success';
    } else {
        $message = 'Error approving transaction.';
        $message_type = 'error';
    }
}

if (isset($_POST['issue_items'])) {
    $transaction_id = mysqli_real_escape_string($connection, $_POST['transaction_id']);
    $admin_id = mysqli_real_escape_string($connection, $_SESSION['admin_id']);

    // Update transaction
    $update_trans_sql = "UPDATE borrowing_transactions SET status='issued' WHERE transaction_id='$transaction_id'";
    mysqli_query($connection, $update_trans_sql);
    $approval_sql = "INSERT INTO approvals (transaction_id, approval_type, admin_id) VALUES ('$transaction_id', 'issued_by', '$admin_id')";
    mysqli_query($connection, $approval_sql);

    // Get items for transaction
    $items_sql = "SELECT ti.* FROM borrowed_items ti WHERE ti.transaction_id = '$transaction_id'";
    $items_result = mysqli_query($connection, $items_sql);

    // Update borrowed_items and items
    while ($item_row = mysqli_fetch_assoc($items_result)) {
        $item_id = $item_row['item_id'];
        $quantity = $item_row['quantity_required'];
        $update_ti_sql = "UPDATE borrowed_items SET quantity_issued=$quantity WHERE borrowed_item_id='{$item_row['borrowed_item_id']}'";
        mysqli_query($connection, $update_ti_sql);
        $update_item_sql = "UPDATE items SET available_quantity = available_quantity - $quantity WHERE item_id='$item_id'";
        mysqli_query($connection, $update_item_sql);
    }

    $message = 'Items issued successfully!';
    $message_type = 'success';
}

if (isset($_POST['return_items'])) {
    $transaction_id = mysqli_real_escape_string($connection, $_POST['transaction_id']);
    $admin_id = mysqli_real_escape_string($connection, $_SESSION['admin_id']);

    $quantities_returned = isset($_POST['quantities_returned']) ? $_POST['quantities_returned'] : [];

    // Update transaction
    $update_trans_sql = "UPDATE borrowing_transactions SET status='returned' WHERE transaction_id='$transaction_id'";
    mysqli_query($connection, $update_trans_sql);
    $approval_sql = "INSERT INTO approvals (transaction_id, approval_type, admin_id) VALUES ('$transaction_id', 'assessed_received_by', '$admin_id')";
    mysqli_query($connection, $approval_sql);

    // Update borrowed_items and items
    foreach ($quantities_returned as $borrowed_item_id => $quantity_returned) {
        $borrowed_item_id = mysqli_real_escape_string($connection, $borrowed_item_id);
        $quantity_returned = intval($quantity_returned);

        $update_ti_sql = "UPDATE borrowed_items SET quantity_returned = quantity_returned + $quantity_returned WHERE borrowed_item_id='$borrowed_item_id'";
        mysqli_query($connection, $update_ti_sql);

        // Get item_id for updating available_quantity
        $item_sql = "SELECT item_id FROM borrowed_items WHERE borrowed_item_id='$borrowed_item_id'";
        $item_result = mysqli_query($connection, $item_sql);
        $item_row = mysqli_fetch_assoc($item_result);
        $item_id = $item_row['item_id'];

        $update_item_sql = "UPDATE items SET available_quantity = available_quantity + $quantity_returned WHERE item_id='$item_id'";
        mysqli_query($connection, $update_item_sql);
    }

    $message = 'Items returned successfully!';
    $message_type = 'success';
}

// Get all transactions
$where_clauses = array("1=1");
if (isset($_GET['search']) && $_GET['search']) {
    $search = mysqli_real_escape_string($connection, $_GET['search']);
    $where_clauses[] = "(t.activity_purpose LIKE '%$search%' OR b.full_name LIKE '%$search%' OR b.id_number LIKE '%$search%')";
}
if (isset($_GET['status']) && $_GET['status']) {
    $status = mysqli_real_escape_string($connection, $_GET['status']);
    $where_clauses[] = "t.status = '$status'";
}
if (isset($_GET['department']) && $_GET['department']) {
    $department = mysqli_real_escape_string($connection, $_GET['department']);
    $where_clauses[] = "b.department_course_office LIKE '%$department%'";
}
if (isset($_GET['date_from']) && isset($_GET['date_to']) && $_GET['date_from'] && $_GET['date_to']) {
    $date_from = mysqli_real_escape_string($connection, $_GET['date_from']);
    $date_to = mysqli_real_escape_string($connection, $_GET['date_to']);
    $where_clauses[] = "t.date_needed BETWEEN '$date_from' AND '$date_to'";
}

$where_sql = implode(' AND ', $where_clauses);
$transactions_sql = "SELECT t.*, b.full_name, b.id_number, b.department_course_office FROM borrowing_transactions t JOIN borrowers b ON t.borrower_id = b.borrower_id WHERE $where_sql ORDER BY t.created_at DESC";
$transactions_result = mysqli_query($connection, $transactions_sql);
$transactions_list = array();
while ($row = mysqli_fetch_assoc($transactions_result)) {
    $transactions_list[] = $row;
}

// Get borrowers list
$borrowers_sql = "SELECT * FROM borrowers";
$borrowers_result = mysqli_query($connection, $borrowers_sql);
$borrowers_list = array();
while ($row = mysqli_fetch_assoc($borrowers_result)) {
    $borrowers_list[] = $row;
}

// Get items list
$items_sql = "SELECT * FROM items";
$items_result = mysqli_query($connection, $items_sql);
$items_list = array();
while ($row = mysqli_fetch_assoc($items_result)) {
    $items_list[] = $row;
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $system_name; ?> - Transaction Management</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <?php include '../includes/navigation.php'; ?>

    <div class="dashboard-container">

        <?php if ($message): ?>
        <div class="alert alert-<?php echo $message_type == 'success' ? 'success' : 'error'; ?>">
            <?php echo $message; ?>
        </div>
        <?php endif; ?>

        <!-- Quick Actions -->
        <div class="form-container">
            <h3>Quick Actions</h3>
            <div class="form-row">
                <button type="button" class="btn btn-primary" onclick="showCreateForm()">Create New Transaction</button>
                <button type="button" class="btn btn-secondary" onclick="showFilterForm()">Filter Transactions</button>
            </div>
        </div>

        <!-- Create Transaction Form (Hidden by default) -->
        <div id="createForm" class="form-container" style="display: none;">
            <h3>Create New Transaction</h3>
            <form method="POST" action="">
                <div class="form-row">
                    <div class="form-group">
                        <label for="borrower_id">Borrower</label>
                        <select id="borrower_id" name="borrower_id" required>
                            <option value="">Select Borrower</option>
                            <?php foreach ($borrowers_list as $borrower_data): ?>
                            <option value="<?php echo $borrower_data['borrower_id']; ?>">
                                <?php echo $borrower_data['full_name'] . ' (' . $borrower_data['id_number'] . ')'; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="activity_purpose">Activity Purpose</label>
                        <input type="text" id="activity_purpose" name="activity_purpose" required>
                    </div>
                    <div class="form-group">
                        <label for="place_of_activity">Place of Activity</label>
                        <input type="text" id="place_of_activity" name="place_of_activity" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="date_requested">Date Requested</label>
                        <input type="date" id="date_requested" name="date_requested" required>
                    </div>
                    <div class="form-group">
                        <label for="date_needed">Date Needed</label>
                        <input type="date" id="date_needed" name="date_needed" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="date_of_return">Date of Return</label>
                        <input type="date" id="date_of_return" name="date_of_return" required>
                    </div>
                </div>

                <!-- Items Selection -->
                <h4>Select Items to Borrow</h4>
                <div class="form-row">
                    <div class="form-group">
                        <label for="item_select">Item</label>
                        <select id="item_select" onchange="addItemToList()">
                            <option value="">Select Item</option>
                            <?php foreach ($items_list as $item_data): ?>
                            <option value="<?php echo $item_data['item_id']; ?>"
                                data-name="<?php echo $item_data['item_name']; ?>"
                                data-available="<?php echo $item_data['available_quantity']; ?>">
                                <?php echo $item_data['item_name'] . ' (' . $item_data['available_quantity'] . ' available)'; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div id="selectedItems">
                    <!-- Selected items will be added here -->
                </div>

                <button type="submit" name="create_transaction" class="btn btn-primary">Create Transaction</button>
                <button type="button" class="btn btn-secondary" onclick="hideCreateForm()">Cancel</button>
            </form>
        </div>

        <!-- Filter Form (Hidden by default) -->
        <div id="filterForm" class="form-container" style="display: none;">
            <h3>Filter Transactions</h3>
            <form method="GET" action="">
                <div class="form-row">
                    <div class="form-group">
                        <label for="search">Search</label>
                        <input type="text" id="search" name="search" placeholder="Search transactions...">
                    </div>
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <option value="">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="issued">Issued</option>
                            <option value="returned">Returned</option>
                            <option value="overdue">Overdue</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="department">Department</label>
                        <input type="text" id="department" name="department" placeholder="Filter by department...">
                    </div>
                    <div class="form-group">
                        <label for="date_from">Date From</label>
                        <input type="date" id="date_from" name="date_from">
                    </div>
                    <div class="form-group">
                        <label for="date_to">Date To</label>
                        <input type="date" id="date_to" name="date_to">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Apply Filters</button>
                <button type="button" class="btn btn-secondary" onclick="hideFilterForm()">Clear Filters</button>
            </form>
        </div>

        <!-- Transactions List -->
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Transaction ID</th>
                        <th>Borrower</th>
                        <th>Activity</th>
                        <th>Place of Activity</th>
                        <th>Date Needed</th>
                        <th>Return Date</th>
                        <th>Status</th>
                        <th>Items Borrowed</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions_list as $trans): ?>
                    <?php
                        $trans_id = mysqli_real_escape_string($connection, $trans['transaction_id']);
                        $items_sql = "SELECT ti.borrowed_item_id, ti.quantity_required, ti.quantity_issued, ti.quantity_returned, i.item_id, i.item_name " .
                            "FROM borrowed_items ti JOIN items i ON ti.item_id = i.item_id WHERE ti.transaction_id = '$trans_id'";
                        $items_result = mysqli_query($connection, $items_sql);
                        $items = array();
                        while ($item_row = mysqli_fetch_assoc($items_result)) {
                            $items[] = $item_row;
                        }
                        ?>
                    <tr>
                        <td><?php echo $trans['transaction_id']; ?></td>
                        <td>
                            <strong><?php echo $trans['full_name']; ?></strong><br>
                            <small><?php echo $trans['id_number']; ?></small>
                        </td>
                        <td><?php echo $trans['activity_purpose']; ?></td>
                        <td><?php echo $trans['place_of_activity']; ?></td>
                        <td><?php echo date('M d, Y', strtotime($trans['date_needed'])); ?></td>
                        <td><?php echo date('M d, Y', strtotime($trans['date_of_return'])); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo $trans['status']; ?>">
                                <?php echo ucfirst($trans['status']); ?>
                            </span>
                        </td>
                        <td data-items="<?php echo htmlspecialchars(json_encode($items)); ?>">
                            <?php
                                if (!empty($items)) {
                                    foreach ($items as $item) {
                                        $quantity = ($trans['status'] == 'pending' || $trans['status'] == 'approved') ? $item['quantity_required'] : $item['quantity_issued'];
                                        echo htmlspecialchars($item['item_name']) . ' (' . intval($quantity) . ')<br>';
                                    }
                                } else {
                                    echo 'No items borrowed';
                                }
                                ?>
                        </td>
                        <td>
                            <?php if ($trans['status'] == 'pending'): ?>
                            <form method="POST" action="" style="display: inline;">
                                <input type="hidden" name="transaction_id"
                                    value="<?php echo $trans['transaction_id']; ?>">
                                <button type="submit" name="approve_transaction"
                                    class="btn btn-sm btn-success">Approve</button>
                            </form>
                            <?php endif; ?>

                            <?php if ($trans['status'] == 'approved'): ?>
                            <form method="POST" action="" style="display: inline;">
                                <input type="hidden" name="transaction_id"
                                    value="<?php echo $trans['transaction_id']; ?>">
                                <button type="submit" name="issue_items" class="btn btn-sm btn-warning">Issue
                                    Items</button>
                            </form>
                            <?php endif; ?>

                            <?php if ($trans['status'] == 'issued'): ?>
                            <button type="button" class="btn btn-sm btn-info"
                                onclick="showReturnForm(<?php echo $trans['transaction_id']; ?>)">Return Items</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Return Items Modal -->
    <div id="returnModal"
        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
        <div
            style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 20px; border-radius: 10px; max-width: 500px; width: 90%;">
            <h3>Return Items</h3>
            <form id="returnForm" method="POST" action="">
                <input type="hidden" name="transaction_id" id="returnTransactionId">
                <div id="returnItemsList"></div>
                <div style="margin-top: 20px;">
                    <button type="submit" name="return_items" class="btn btn-primary">Submit Return</button>
                    <button type="button" class="btn btn-secondary" onclick="hideReturnForm()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script src="transactions.js"></script>
    <script src="../assets/js/script.js"></script>
</body>

</html>
?>