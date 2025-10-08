<?php
require_once '../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$connection = mysqli_connect($db_host, $db_user, $db_pass, $db_name);
if (!$connection) {
    die("Database connection failed: " . mysqli_connect_error());
}
mysqli_set_charset($connection, 'utf8');

$type = isset($_POST['type']) ? $_POST['type'] : (isset($_GET['type']) ? $_GET['type'] : '');
if ($type === '') {
    http_response_code(400);
    echo 'Missing export type';
    exit;
}

$filename = 'export_' . $type . '_' . date('Ymd_His') . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);

$output = fopen('php://output', 'w');

switch ($type) {
    case 'transactions':
        $rows_sql = "SELECT t.*, b.full_name, b.id_number FROM borrowing_transactions t JOIN borrowers b ON t.borrower_id = b.borrower_id ORDER BY t.created_at DESC";
        $rows_result = mysqli_query($connection, $rows_sql);
        $rows = array();
        while ($row = mysqli_fetch_assoc($rows_result)) {
            $rows[] = $row;
        }
        fputcsv($output, ['Transaction ID', 'Borrower Name', 'ID Number', 'Activity', 'Date Requested', 'Date Needed', 'Return Date', 'Status']);
        foreach ($rows as $row) {
            fputcsv($output, [
                $row['transaction_id'],
                $row['full_name'],
                $row['id_number'],
                $row['activity_purpose'],
                $row['date_requested'],
                $row['date_needed'],
                $row['date_of_return'],
                $row['status']
            ]);
        }
        break;

    case 'items':
        $rows_sql = "SELECT i.*, c.category_name as category FROM items i LEFT JOIN categories c ON i.category_id = c.category_id";
        $rows_result = mysqli_query($connection, $rows_sql);
        $rows = array();
        while ($row = mysqli_fetch_assoc($rows_result)) {
            $rows[] = $row;
        }
        fputcsv($output, ['Item Code', 'Item Name', 'Category', 'Available', 'Total', 'Unit']);
        foreach ($rows as $row) {
            fputcsv($output, [
                $row['item_code'],
                $row['item_name'],
                $row['category'],
                $row['available_quantity'],
                $row['total_quantity'],
                $row['unit']
            ]);
        }
        break;

    case 'borrowers':
        $rows_sql = "SELECT * FROM borrowers";
        $rows_result = mysqli_query($connection, $rows_sql);
        $rows = array();
        while ($row = mysqli_fetch_assoc($rows_result)) {
            $rows[] = $row;
        }
        fputcsv($output, ['ID Number', 'Full Name', 'Department/Office', 'Contact Number', 'Email Address']);
        foreach ($rows as $row) {
            fputcsv($output, [
                $row['id_number'],
                $row['full_name'],
                $row['department_course_office'],
                $row['contact_number'],
                $row['email_address']
            ]);
        }
        break;

    default:
        http_response_code(400);
        echo 'Invalid export type';
}

fclose($output);
exit;
?>