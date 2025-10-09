<?php
require_once '../includes/config.php';

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$connection = mysqli_connect($db_host, $db_user, $db_pass, $db_name);
if (!$connection) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}
mysqli_set_charset($connection, 'utf8');

$date_needed = isset($_GET['date_needed']) ? mysqli_real_escape_string($connection, $_GET['date_needed']) : '';
$date_of_return = isset($_GET['date_of_return']) ? mysqli_real_escape_string($connection, $_GET['date_of_return']) : '';

if (empty($date_needed) || empty($date_of_return)) {
    http_response_code(400);
    echo json_encode(['error' => 'Date needed and date of return are required']);
    exit;
}

// Get all items
$items_sql = "SELECT item_id, item_name, total_quantity FROM items";
$items_result = mysqli_query($connection, $items_sql);

$availability = array();
while ($item = mysqli_fetch_assoc($items_result)) {
    $item_id = $item['item_id'];
    $total_quantity = $item['total_quantity'];

    // Calculate committed quantity from overlapping transactions
    $committed_sql = "SELECT COALESCE(SUM(CASE WHEN t.status = 'issued' THEN (bi.quantity_issued - bi.quantity_returned) ELSE bi.quantity_required END), 0) as committed " .
        "FROM borrowing_transactions t JOIN borrowed_items bi ON t.transaction_id = bi.transaction_id " .
        "WHERE bi.item_id = '$item_id' " .
        "AND t.status IN ('approved', 'issued') " .
        "AND t.date_needed <= '$date_of_return' " .
        "AND t.date_of_return >= '$date_needed'";
    $committed_result = mysqli_query($connection, $committed_sql);
    $committed_row = mysqli_fetch_assoc($committed_result);
    $committed = $committed_row ? $committed_row['committed'] : 0;

    $available = $total_quantity - $committed;
    $availability[$item_id] = max(0, $available); // Ensure not negative
}

echo json_encode($availability);
?>