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

// Get report data
$stats_sql = "SELECT COUNT(*) as total_transactions FROM borrowing_transactions";
$stats_result = mysqli_query($connection, $stats_sql);
$stats_row = mysqli_fetch_assoc($stats_result);
$total_transactions = $stats_row['total_transactions'];

$overdue_sql = "SELECT COUNT(*) as overdue FROM borrowing_transactions WHERE date_of_return < CURDATE() AND status != 'returned'";
$overdue_result = mysqli_query($connection, $overdue_sql);
$overdue_row = mysqli_fetch_assoc($overdue_result);
$overdue = $overdue_row['overdue'];

$stats = array('overdue' => $overdue);

$most_borrowed_items_sql = "SELECT i.item_code, i.item_name, c.category_name as category, i.available_quantity, i.total_quantity, COUNT(ti.borrowed_item_id) as borrow_count FROM items i LEFT JOIN borrowed_items ti ON i.item_id = ti.item_id LEFT JOIN categories c ON i.category_id = c.category_id GROUP BY i.item_id ORDER BY borrow_count DESC LIMIT 10";
$most_borrowed_result = mysqli_query($connection, $most_borrowed_items_sql);
$most_borrowed_items = array();
while ($row = mysqli_fetch_assoc($most_borrowed_result)) {
    $most_borrowed_items[] = $row;
}

$monthly_report_sql = "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as total_transactions, SUM(CASE WHEN status = 'returned' THEN 1 ELSE 0 END) as items_returned, SUM(CASE WHEN status != 'returned' THEN 1 ELSE 0 END) as items_borrowed, SUM(CASE WHEN date_of_return < CURDATE() AND status != 'returned' THEN 1 ELSE 0 END) as overdue FROM borrowing_transactions GROUP BY month ORDER BY month DESC";
$monthly_result = mysqli_query($connection, $monthly_report_sql);
$monthly_report = array();
while ($row = mysqli_fetch_assoc($monthly_result)) {
    $monthly_report[] = $row;
}

$overdue_items_sql = "SELECT t.transaction_id, b.full_name as borrower_name, i.item_name, t.created_at as date_issued, t.date_of_return, DATEDIFF(CURDATE(), t.date_of_return) as days_overdue, b.contact_number FROM borrowing_transactions t JOIN borrowers b ON t.borrower_id = b.borrower_id JOIN borrowed_items ti ON t.transaction_id = ti.transaction_id JOIN items i ON ti.item_id = i.item_id WHERE t.date_of_return < CURDATE() AND t.status != 'returned'";
$overdue_items_result = mysqli_query($connection, $overdue_items_sql);
$overdue_items = array();
while ($row = mysqli_fetch_assoc($overdue_items_result)) {
    $overdue_items[] = $row;
}

$all_transactions_sql = "SELECT t.*, b.full_name, b.id_number, b.department_course_office FROM borrowing_transactions t JOIN borrowers b ON t.borrower_id = b.borrower_id ORDER BY t.created_at DESC";
$all_transactions_result = mysqli_query($connection, $all_transactions_sql);
$all_transactions = array();
while ($row = mysqli_fetch_assoc($all_transactions_result)) {
    $all_transactions[] = $row;
}

$item_count_sql = "SELECT COUNT(*) as count FROM items";
$item_count_result = mysqli_query($connection, $item_count_sql);
$item_count = mysqli_fetch_assoc($item_count_result)['count'];

$borrower_count_sql = "SELECT COUNT(*) as count FROM borrowers";
$borrower_count_result = mysqli_query($connection, $borrower_count_sql);
$borrower_count = mysqli_fetch_assoc($borrower_count_result)['count'];
?>
<?php include '../includes/navigation.php'; ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $system_name; ?> - Reports</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <div class="dashboard-container">

        <!-- Export Options -->
        <div class="form-container">
            <h3>Export Options</h3>
            <div class="form-row">
                <button class="btn btn-primary" onclick="exportReport('transactions')">Export Transactions</button>
                <button class="btn btn-primary" onclick="exportReport('items')">Export Items</button>
                <button class="btn btn-primary" onclick="exportReport('borrowers')">Export Borrowers</button>
                <button class="btn btn-secondary" onclick="printReport()">Print Report</button>
            </div>
        </div>

        <!-- Summary Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Transactions</h3>
                <div class="stat-number"><?php echo $total_transactions; ?></div>
                <div class="stat-label">All time transactions</div>
            </div>

            <div class="stat-card">
                <h3>Active Items</h3>
                <div class="stat-number"><?php echo $item_count; ?></div>
                <div class="stat-label">Items in inventory</div>
            </div>

            <div class="stat-card">
                <h3>Total Borrowers</h3>
                <div class="stat-number"><?php echo $borrower_count; ?></div>
                <div class="stat-label">Registered borrowers</div>
            </div>

            <div class="stat-card">
                <h3>Overdue Items</h3>
                <div class="stat-number" style="color: #dc3545;"><?php echo $stats['overdue']; ?></div>
                <div class="stat-label">Items past due date</div>
            </div>

            <div class="stat-card clickable" data-type="transaction_history">
                <h3>Transaction History</h3>
                <div class="stat-number"><?php echo $total_transactions; ?></div>
                <div class="stat-label">View all transactions</div>
            </div>
        </div>

        <!-- Report Sections -->
        <div class="form-container">
            <h3>Monthly Activity Report</h3>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th>Total Transactions</th>
                            <th>Items Borrowed</th>
                            <th>Items Returned</th>
                            <th>Overdue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($monthly_report as $month): ?>
                        <tr>
                            <td><?php echo date('F Y', strtotime($month['month'] . '-01')); ?></td>
                            <td><?php echo $month['total_transactions']; ?></td>
                            <td><?php echo $month['items_borrowed']; ?></td>
                            <td><?php echo $month['items_returned']; ?></td>
                            <td>
                                <span style="color: <?php echo $month['overdue'] > 0 ? '#dc3545' : '#28a745'; ?>;">
                                    <?php echo $month['overdue']; ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="form-container">
            <h3>Most Borrowed Items</h3>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Item Code</th>
                            <th>Item Name</th>
                            <th>Category</th>
                            <th>Borrow Count</th>
                            <th>Available Stock</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $rank = 1;
                        foreach ($most_borrowed_items as $item_data): ?>
                        <tr>
                            <td><?php echo $rank++; ?></td>
                            <td><?php echo $item_data['item_code']; ?></td>
                            <td><?php echo $item_data['item_name']; ?></td>
                            <td><?php echo $item_data['category']; ?></td>
                            <td><strong><?php echo $item_data['borrow_count']; ?></strong></td>
                            <td><?php echo $item_data['available_quantity'] . '/' . $item_data['total_quantity']; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="form-container">
            <h3>Items by Category</h3>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Total Items</th>
                            <th>Available Items</th>
                            <th>Borrowed Items</th>
                            <th>Utilization Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $categories = [];
                        foreach ($most_borrowed_items as $item) {
                            $cat = $item['category'];
                            if (!isset($categories[$cat])) {
                                $categories[$cat] = ['total' => 0, 'available' => 0, 'borrowed' => 0];
                            }
                            $categories[$cat]['total'] += $item['total_quantity'];
                            $categories[$cat]['available'] += $item['available_quantity'];
                            $categories[$cat]['borrowed'] += ($item['total_quantity'] - $item['available_quantity']);
                        }

                        foreach ($categories as $category => $data):
                            $utilization = $data['total'] > 0 ? round((($data['total'] - $data['available']) / $data['total']) * 100, 1) : 0;
                        ?>
                        <tr>
                            <td><?php echo $category; ?></td>
                            <td><?php echo $data['total']; ?></td>
                            <td><?php echo $data['available']; ?></td>
                            <td><?php echo $data['borrowed']; ?></td>
                            <td>
                                <div style="display: flex; align-items: center;">
                                    <div
                                        style="flex: 1; background: #eee; height: 10px; margin-right: 10px; border-radius: 5px;">
                                        <div
                                            style="width: <?php echo $utilization; ?>%; background: #667eea; height: 100%; border-radius: 5px;">
                                        </div>
                                    </div>
                                    <?php echo $utilization; ?>%
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="form-container">
            <h3>Overdue Items Report</h3>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Transaction ID</th>
                            <th>Borrower</th>
                            <th>Item</th>
                            <th>Date Borrowed</th>
                            <th>Due Date</th>
                            <th>Days Overdue</th>
                            <th>Contact</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($overdue_items as $item): ?>
                        <tr>
                            <td><?php echo $item['transaction_id']; ?></td>
                            <td><?php echo $item['borrower_name']; ?></td>
                            <td><?php echo $item['item_name']; ?></td>
                            <td><?php echo date('M d, Y', strtotime($item['date_issued'])); ?></td>
                            <td><?php echo date('M d, Y', strtotime($item['date_of_return'])); ?></td>
                            <td>
                                <span style="color: #dc3545; font-weight: bold;">
                                    <?php echo $item['days_overdue']; ?> days
                                </span>
                            </td>
                            <td><?php echo $item['contact_number']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
    function exportReport(type) {
        const data = {
            type: type,
            date: new Date().toISOString().split('T')[0]
        };

        // Create a form to submit the export request
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'export.php';

        Object.keys(data).forEach(key => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = data[key];
            form.appendChild(input);
        });

        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    }

    function printReport() {
        window.print();
    }
    </script>
    <script src="../assets/js/script.js"></script>

</body>

</html>