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

// Get dashboard statistics
$today_sql = "SELECT COUNT(*) as today_borrowed FROM borrowing_transactions WHERE DATE(created_at) = CURDATE()";
$today_result = mysqli_query($connection, $today_sql);
$today_row = mysqli_fetch_assoc($today_result);
$today_borrowed = $today_row['today_borrowed'];

$week_sql = "SELECT COUNT(*) as week_borrowed FROM borrowing_transactions WHERE YEARWEEK(created_at) = YEARWEEK(CURDATE())";
$week_result = mysqli_query($connection, $week_sql);
$week_row = mysqli_fetch_assoc($week_result);
$week_borrowed = $week_row['week_borrowed'];

$month_sql = "SELECT COUNT(*) as month_borrowed FROM borrowing_transactions WHERE YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE())";
$month_result = mysqli_query($connection, $month_sql);
$month_row = mysqli_fetch_assoc($month_result);
$month_borrowed = $month_row['month_borrowed'];

$overdue_sql = "SELECT COUNT(*) as overdue FROM borrowing_transactions WHERE status = 'issued' AND date_of_return < CURDATE()";
$overdue_result = mysqli_query($connection, $overdue_sql);
$overdue_row = mysqli_fetch_assoc($overdue_result);
$overdue = $overdue_row['overdue'];

$due_soon_sql = "SELECT COUNT(*) as due_soon FROM borrowing_transactions WHERE status = 'issued' AND date_of_return BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 3 DAY)";
$due_soon_result = mysqli_query($connection, $due_soon_sql);
$due_soon_row = mysqli_fetch_assoc($due_soon_result);
$due_soon = $due_soon_row['due_soon'];

$stats = array(
    'today_borrowed' => $today_borrowed,
    'week_borrowed' => $week_borrowed,
    'month_borrowed' => $month_borrowed,
    'overdue' => $overdue,
    'due_soon' => $due_soon
);

// Get most borrowed items
$most_borrowed_sql = "SELECT i.item_name, i.item_code, COUNT(ti.borrowed_item_id) as borrow_count
                      FROM borrowed_items ti
                      JOIN items i ON ti.item_id = i.item_id
                      GROUP BY ti.item_id
                      ORDER BY borrow_count DESC
                      LIMIT 5";
$most_borrowed_result = mysqli_query($connection, $most_borrowed_sql);
$most_borrowed = array();
while ($row = mysqli_fetch_assoc($most_borrowed_result)) {
    $most_borrowed[] = $row;
}

// Get low stock items
$low_stock_sql = "SELECT * FROM items WHERE available_quantity <= 5";
$low_stock_result = mysqli_query($connection, $low_stock_sql);
$low_stock = array();
while ($row = mysqli_fetch_assoc($low_stock_result)) {
    $low_stock[] = $row;
}

// Get recent transactions
$recent_sql = "SELECT t.*, b.full_name, t.activity_purpose, t.status, t.created_at
               FROM borrowing_transactions t
               JOIN borrowers b ON t.borrower_id = b.borrower_id
               ORDER BY t.created_at DESC
               LIMIT 5";
$recent_result = mysqli_query($connection, $recent_sql);
$recent_transactions = array();
while ($row = mysqli_fetch_assoc($recent_result)) {
    $recent_transactions[] = $row;
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    mysqli_close($connection);
    header('Location: login.php');
    exit;
}
?>
<?php include '../includes/navigation.php'; ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $system_name; ?> - Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <div class="dashboard-container fade-in">
        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <h3 class="stat-title">Total Transactions</h3>
                    <div class="stat-icon"><i class="fas fa-list"></i></div>
                </div>
                <div class="stat-number">
                    <?php $count_sql = "SELECT COUNT(*) as count FROM borrowing_transactions"; $count_result = mysqli_query($connection, $count_sql); $count_row = mysqli_fetch_assoc($count_result); echo $count_row['count']; ?>
                </div>
                <div class="stat-label">All transactions</div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <h3 class="stat-title">Total Items</h3>
                    <div class="stat-icon"><i class="fas fa-boxes"></i></div>
                </div>
                <div class="stat-number">
                    <?php $item_count_sql = "SELECT COUNT(*) as count FROM items"; $item_count_result = mysqli_query($connection, $item_count_sql); $item_count_row = mysqli_fetch_assoc($item_count_result); echo $item_count_row['count']; ?>
                </div>
                <div class="stat-label">Inventory items</div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <h3 class="stat-title">Total Borrowers</h3>
                    <div class="stat-icon"><i class="fas fa-users"></i></div>
                </div>
                <div class="stat-number">
                    <?php $borrower_count_sql = "SELECT COUNT(*) as count FROM borrowers"; $borrower_count_result = mysqli_query($connection, $borrower_count_sql); $borrower_count_row = mysqli_fetch_assoc($borrower_count_result); echo $borrower_count_row['count']; ?>
                </div>
                <div class="stat-label">Registered borrowers</div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <h3 class="stat-title">Overdue Items</h3>
                    <div class="stat-icon"><i class="fas fa-exclamation-triangle"></i></div>
                </div>
                <div class="stat-number" style="color: var(--danger-color);"><?php echo $stats['overdue']; ?></div>
                <div class="stat-label">Items past due date</div>
            </div>
        </div>
    </div>
    <script src="../assets/js/script.js"></script>
</body>

</html>
<?php mysqli_close($connection); ?>