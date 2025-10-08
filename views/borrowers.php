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

if (isset($_POST['add_borrower'])) {
    $id_number = mysqli_real_escape_string($connection, $_POST['id_number']);
    $full_name = mysqli_real_escape_string($connection, $_POST['full_name']);
    $department_course_office = mysqli_real_escape_string($connection, $_POST['department_course_office']);
    $contact_number = mysqli_real_escape_string($connection, $_POST['contact_number']);
    $email_address = mysqli_real_escape_string($connection, $_POST['email_address']);

    $insert_sql = "INSERT INTO borrowers (id_number, full_name, department_course_office, contact_number, email_address) VALUES ('$id_number', '$full_name', '$department_course_office', '$contact_number', '$email_address')";

    if (mysqli_query($connection, $insert_sql)) {
        $message = 'Borrower added successfully!';
        $message_type = 'success';
    } else {
        $message = 'Error adding borrower.';
        $message_type = 'error';
    }
}

if (isset($_POST['edit_borrower'])) {
    $borrower_id = mysqli_real_escape_string($connection, $_POST['borrower_id']);
    $id_number = mysqli_real_escape_string($connection, $_POST['id_number']);
    $full_name = mysqli_real_escape_string($connection, $_POST['full_name']);
    $department_course_office = mysqli_real_escape_string($connection, $_POST['department_course_office']);
    $contact_number = mysqli_real_escape_string($connection, $_POST['contact_number']);
    $email_address = mysqli_real_escape_string($connection, $_POST['email_address']);

    $update_sql = "UPDATE borrowers SET id_number='$id_number', full_name='$full_name', department_course_office='$department_course_office', contact_number='$contact_number', email_address='$email_address' WHERE borrower_id='$borrower_id'";

    if (mysqli_query($connection, $update_sql)) {
        $message = 'Borrower updated successfully!';
        $message_type = 'success';
    } else {
        $message = 'Error updating borrower.';
        $message_type = 'error';
    }
}

if (isset($_POST['delete_borrower'])) {
    $borrower_id = mysqli_real_escape_string($connection, $_POST['borrower_id']);

    $delete_sql = "DELETE FROM borrowers WHERE borrower_id='$borrower_id'";

    if (mysqli_query($connection, $delete_sql)) {
        $message = 'Borrower deleted successfully!';
        $message_type = 'success';
    } else {
        $message = 'Error deleting borrower.';
        $message_type = 'error';
    }
}

// Get all borrowers
$search = isset($_GET['search']) ? mysqli_real_escape_string($connection, $_GET['search']) : '';
$where_clause = '';
if ($search) {
    $where_clause = "WHERE full_name LIKE '%$search%' OR id_number LIKE '%$search%' OR department_course_office LIKE '%$search%'";
}
$select_sql = "SELECT * FROM borrowers $where_clause";
$result = mysqli_query($connection, $select_sql);
$borrowers_list = array();
while ($row = mysqli_fetch_assoc($result)) {
    $borrowers_list[] = $row;
}
?>
<?php include '../includes/navigation.php'; ?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $system_name; ?> - Borrowers</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <div class="dashboard-container fade-in">
        <?php if ($message): ?>
        <div class="alert alert-<?php echo $message_type == 'success' ? 'success' : 'error'; ?>">
            <i class="fas fa-info-circle"></i> <?php echo $message; ?>
        </div>
        <?php endif; ?>

        <!-- Add New Borrower Form -->
        <div class="form-container">
            <div class="form-header">
                <h3 class="form-title"><i class="fas fa-user-plus"></i> Add New Borrower</h3>
            </div>
            <form method="POST" action="">
                <div class="form-row">
                    <div class="form-group">
                        <label for="id_number" class="form-label"><i class="fas fa-id-badge"></i> ID Number</label>
                        <input type="text" id="id_number" name="id_number" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label for="full_name" class="form-label"><i class="fas fa-user"></i> Full Name</label>
                        <input type="text" id="full_name" name="full_name" class="form-input" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="department_course_office" class="form-label"><i class="fas fa-building"></i>
                            Department/Course/Office</label>
                        <input type="text" id="department_course_office" name="department_course_office"
                            class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label for="contact_number" class="form-label"><i class="fas fa-phone"></i> Contact
                            Number</label>
                        <input type="text" id="contact_number" name="contact_number" class="form-input" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="email_address" class="form-label"><i class="fas fa-envelope"></i> Email
                            Address</label>
                        <input type="email" id="email_address" name="email_address" class="form-input" required>
                    </div>
                </div>
                <button type="submit" name="add_borrower" class="btn btn-primary"><i class="fas fa-plus"></i> Add
                    Borrower</button>
            </form>
        </div>

        <!-- Search -->
        <div class="form-container">
            <form method="GET" action="">
                <div class="form-row">
                    <div class="form-group">
                        <label for="search" class="form-label"><i class="fas fa-search"></i> Search Borrowers</label>
                        <input type="text" id="search" name="search" class="form-input"
                            placeholder="Search by name, ID, or department..." value="<?php echo $search; ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Search</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Borrowers List -->
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID Number</th>
                        <th>Full Name</th>
                        <th>Department/Office</th>
                        <th>Contact Number</th>
                        <th>Email Address</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($borrowers_list as $borrower_data): ?>
                    <tr>
                        <td><?php echo $borrower_data['id_number']; ?></td>
                        <td><?php echo $borrower_data['full_name']; ?></td>
                        <td><?php echo $borrower_data['department_course_office']; ?></td>
                        <td><?php echo $borrower_data['contact_number']; ?></td>
                        <td><?php echo $borrower_data['email_address']; ?></td>
                        <td>
                            <button class="btn btn-sm btn-primary"
                                onclick="editBorrower(<?php echo $borrower_data['borrower_id']; ?>)"><i
                                    class="fas fa-edit"></i></button>

                            <form method="POST" action="" style="display: inline;">
                                <input type="hidden" name="borrower_id"
                                    value="<?php echo $borrower_data['borrower_id']; ?>">
                                <button type="submit" name="delete_borrower" class="btn btn-sm btn-danger"
                                    title="Delete Borrower"
                                    onclick="return confirm('Are you sure you want to delete this borrower?')"><i
                                        class="fas fa-trash-alt"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    </main>
    </div>

    <script src="../assets/js/script.js"></script>
    <script>
    window.borrowersList = <?php echo json_encode($borrowers_list); ?>;
    </script>
</body>

</html>