<?php
require_once 'includes/config.php';

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

$type = $_POST['type'] ?? '';
$date = $_POST['date'] ?? date('Y-m-d');

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $type . '_report_' . $date . '.csv"');

$output = fopen('php://output', 'w');

switch ($type) {
    case 'transactions':
        // Export all transactions
        $transactions_sql = "SELECT t.*, b.full_name, b.id_number, b.department_course_office FROM borrowing_transactions t JOIN borrowers b ON t.borrower_id = b.borrower_id ORDER BY t.created_at DESC";
        $transactions_result = mysqli_query($connection, $transactions_sql);
        $transactions = array();
        while ($row = mysqli_fetch_assoc($transactions_result)) {
            $transactions[] = $row;
        }

        // CSV headers
        fputcsv($output, ['Transaction ID', 'Borrower Name', 'ID Number', 'Department', 'Activity Purpose', 'Place of Activity', 'Date Requested', 'Date Needed', 'Date of Return', 'Status', 'Items']);

        foreach ($transactions as $trans) {
            $trans_id = mysqli_real_escape_string($connection, $trans['transaction_id']);
            $items_sql = "SELECT ti.quantity_issued, i.item_name FROM borrowed_items ti JOIN items i ON ti.item_id = i.item_id WHERE ti.transaction_id = '$trans_id'";
            $items_result = mysqli_query($connection, $items_sql);
            $item_list = [];
            while ($item_row = mysqli_fetch_assoc($items_result)) {
                $item_list[] = $item_row['item_name'] . ' (' . $item_row['quantity_issued'] . ')';
            }

            fputcsv($output, [
                $trans['transaction_id'],
                $trans['full_name'],
                $trans['id_number'],
                $trans['department_course_office'],
                $trans['activity_purpose'],
                $trans['place_of_activity'],
                $trans['date_requested'],
                $trans['date_needed'],
                $trans['date_of_return'],
                $trans['status'],
                implode('; ', $item_list)
            ]);
        }
        break;

    case 'items':
        // Export all items
        $items_sql = "SELECT *, total_quantity as available_quantity FROM items";
        $items_result = mysqli_query($connection, $items_sql);
        $items_list = array();
        while ($row = mysqli_fetch_assoc($items_result)) {
            $items_list[] = $row;
        }

        // CSV headers
        fputcsv($output, ['Item Code', 'Item Name', 'Category', 'Description', 'Total Quantity', 'Available Quantity', 'Status']);

        foreach ($items_list as $item_data) {
            fputcsv($output, [
                $item_data['item_code'],
                $item_data['item_name'],
                $item_data['category'],
                $item_data['description'],
                $item_data['total_quantity'],
                $item_data['available_quantity'],
                $item_data['status']
            ]);
        }
        break;

    case 'borrowers':
        // Export all borrowers
        $borrowers_sql = "SELECT * FROM borrowers";
        $borrowers_result = mysqli_query($connection, $borrowers_sql);
        $borrowers_list = array();
        while ($row = mysqli_fetch_assoc($borrowers_result)) {
            $borrowers_list[] = $row;
        }

        // CSV headers
        fputcsv($output, ['ID Number', 'Full Name', 'Department/Course/Office', 'Contact Number', 'Email', 'Status']);

        foreach ($borrowers_list as $borrower_data) {
            fputcsv($output, [
                $borrower_data['id_number'],
                $borrower_data['full_name'],
                $borrower_data['department_course_office'],
                $borrower_data['contact_number'],
                $borrower_data['email'],
                $borrower_data['status']
            ]);
        }
        break;

    default:
        // Default to transactions
        header('Location: views/reports.php');
        exit;
}

fclose($output);
exit;