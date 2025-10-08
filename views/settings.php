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

if (isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $message = 'All password fields are required.';
        $message_type = 'error';
    } elseif ($new_password !== $confirm_password) {
        $message = 'New password and confirm password do not match.';
        $message_type = 'error';
    } elseif (strlen($new_password) < 8) {
        $message = 'New password must be at least 8 characters long.';
        $message_type = 'error';
    } else {
        // Verify current password
        $username_escaped = mysqli_real_escape_string($connection, $_SESSION['admin_name']);
        $sql = "SELECT password_hash FROM admins WHERE username = '$username_escaped'";
        $result = mysqli_query($connection, $sql);
        if (mysqli_num_rows($result) == 1) {
            $admin = mysqli_fetch_assoc($result);
            if (password_verify($current_password, $admin['password_hash'])) {
                $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
                $admin_id_escaped = mysqli_real_escape_string($connection, $_SESSION['admin_id']);
                $update_sql = "UPDATE admins SET password_hash='$new_hash', updated_at=NOW() WHERE admin_id='$admin_id_escaped'";
                if (mysqli_query($connection, $update_sql)) {
                    $message = 'Password changed successfully!';
                    $message_type = 'success';
                } else {
                    $message = 'Error changing password.';
                    $message_type = 'error';
                }
            } else {
                $message = 'Current password is incorrect.';
                $message_type = 'error';
            }
        } else {
            $message = 'Admin not found.';
            $message_type = 'error';
        }
    }
}

if (isset($_POST['update_settings'])) {
    $settings = [
        'max_items_per_transaction' => $_POST['max_items_per_transaction'],
        'max_borrowing_days' => $_POST['max_borrowing_days'],
        'overdue_notice_days' => $_POST['overdue_notice_days']
    ];

    foreach ($settings as $key => $value) {
        $key_escaped = mysqli_real_escape_string($connection, $key);
        $value_escaped = mysqli_real_escape_string($connection, $value);
        $update_sql = "INSERT INTO system_settings (setting_key, setting_value, updated_at) VALUES ('$key_escaped', '$value_escaped', NOW()) ON DUPLICATE KEY UPDATE setting_value='$value_escaped', updated_at=NOW()";
        mysqli_query($connection, $update_sql);
    }

    $message = 'System settings updated successfully!';
    $message_type = 'success';
}

if (isset($_POST['add_category'])) {
    $category_name = trim($_POST['new_category_name']);
    if (!empty($category_name)) {
        $category_name_escaped = mysqli_real_escape_string($connection, $category_name);
        $insert_sql = "INSERT INTO categories (category_name) VALUES ('$category_name_escaped')";
        if (mysqli_query($connection, $insert_sql)) {
            $message = 'Category added successfully!';
            $message_type = 'success';
        } else {
            $message = 'Error adding category.';
            $message_type = 'error';
        }
    } else {
        $message = 'Category name cannot be empty.';
        $message_type = 'error';
    }
}

if (isset($_POST['edit_category'])) {
    $category_id = mysqli_real_escape_string($connection, $_POST['category_id']);
    $category_name = trim($_POST['edit_category_name']);
    if (!empty($category_name)) {
        $category_name_escaped = mysqli_real_escape_string($connection, $category_name);
        $update_sql = "UPDATE categories SET category_name='$category_name_escaped' WHERE category_id='$category_id'";
        if (mysqli_query($connection, $update_sql)) {
            $message = 'Category updated successfully!';
            $message_type = 'success';
        } else {
            $message = 'Error updating category.';
            $message_type = 'error';
        }
    } else {
        $message = 'Category name cannot be empty.';
        $message_type = 'error';
    }
}

if (isset($_POST['delete_category'])) {
    $category_id = mysqli_real_escape_string($connection, $_POST['category_id']);
    $delete_sql = "DELETE FROM categories WHERE category_id='$category_id'";
    if (mysqli_query($connection, $delete_sql)) {
        $message = 'Category deleted successfully!';
        $message_type = 'success';
    } else {
        $message = 'Error deleting category.';
        $message_type = 'error';
    }
}

// Get current settings
$system_settings = array();
$settings_sql = "SELECT * FROM system_settings";
$settings_result = mysqli_query($connection, $settings_sql);
while ($row = mysqli_fetch_assoc($settings_result)) {
    $system_settings[$row['setting_key']] = array(
        'value' => $row['setting_value'],
        'description' => $row['description'] ?? '',
        'updated_at' => $row['updated_at']
    );
}

// Get categories
$categories = array();
$categories_sql = "SELECT * FROM categories";
$categories_result = mysqli_query($connection, $categories_sql);
while ($row = mysqli_fetch_assoc($categories_result)) {
    $categories[] = $row;
}
?>
<?php include '../includes/navigation.php'; ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $system_name; ?> - System Settings</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <div class="dashboard-container">

        <?php if ($message): ?>
        <div class="alert alert-<?php echo $message_type == 'success' ? 'success' : 'error'; ?>">
            <?php echo $message; ?>
        </div>
        <?php endif; ?>

        <!-- Settings Tabs -->

        <div class="tab-content">
            <!-- Profile Settings Tab -->
            <div class="tab-pane fade show active" id="profile">
                <div class="form-container">
                    <h3>Change Password</h3>
                    <form method="POST" action="">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="current_password">Current Password</label>
                                <input type="password" id="current_password" name="current_password" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="new_password">New Password</label>
                                <input type="password" id="new_password" name="new_password" minlength="8" required>
                                <small style="color: #666;">Password must be at least 8 characters long</small>
                            </div>
                            <div class="form-group">
                                <label for="confirm_password">Confirm New Password</label>
                                <input type="password" id="confirm_password" name="confirm_password" required>
                            </div>
                        </div>
                        <button type="submit" name="change_password" class="btn btn-primary">Change Password</button>
                    </form>
                </div>
            </div>

            <!-- System Settings Tab -->
            <div class="tab-pane fade" id="system">
                <div class="form-container">
                    <h3>System Configuration</h3>
                    <form method="POST" action="">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="max_items_per_transaction">Max Items Per Transaction</label>
                                <input type="number" id="max_items_per_transaction" name="max_items_per_transaction"
                                    value="<?php echo $system_settings['max_items_per_transaction']['value'] ?? 5; ?>"
                                    min="1" max="20" required>
                                <small style="color: #666;">Maximum number of items a borrower can request per
                                    transaction</small>
                            </div>
                            <div class="form-group">
                                <label for="max_borrowing_days">Max Borrowing Days</label>
                                <input type="number" id="max_borrowing_days" name="max_borrowing_days"
                                    value="<?php echo $system_settings['max_borrowing_days']['value'] ?? 7; ?>" min="1"
                                    max="30" required>
                                <small style="color: #666;">Maximum number of days items can be borrowed</small>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="overdue_notice_days">Overdue Notice Days</label>
                                <input type="number" id="overdue_notice_days" name="overdue_notice_days"
                                    value="<?php echo $system_settings['overdue_notice_days']['value'] ?? 3; ?>" min="1"
                                    max="10" required>
                                <small style="color: #666;">Number of days before due date to send notification</small>
                            </div>
                        </div>
                        <button type="submit" name="update_settings" class="btn btn-primary">Update Settings</button>
                    </form>
                </div>

                <div class="form-container">
                    <h3>System Information</h3>
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Setting</th>
                                    <th>Value</th>
                                    <th>Description</th>
                                    <th>Last Updated</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($system_settings as $key => $setting): ?>
                                <tr>
                                    <td><?php echo ucwords(str_replace('_', ' ', $key)); ?></td>
                                    <td><strong><?php echo $setting['value']; ?></strong></td>
                                    <td><?php echo $setting['description']; ?></td>
                                    <td><?php echo date('M d, Y H:i', strtotime($setting['updated_at'] ?? 'now')); ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>



            <!-- Category Management Tab -->
            <div class="tab-pane fade" id="categories">
                <div class="form-container">
                    <h3>Manage Categories</h3>
                    <form method="POST" action="">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="new_category_name">Add New Category</label>
                                <input type="text" id="new_category_name" name="new_category_name"
                                    placeholder="Category Name" required>
                            </div>
                            <div class="form-group">
                                <button type="submit" name="add_category" class="btn btn-primary">Add Category</button>
                            </div>
                        </div>
                    </form>

                    <h4>Existing Categories</h4>
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Category Name</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($categories as $cat): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($cat['category_name']); ?></td>
                                    <td>
                                        <form method="POST" action="" style="display:inline;">
                                            <input type="hidden" name="category_id"
                                                value="<?php echo $cat['category_id']; ?>">
                                            <input type="text" name="edit_category_name"
                                                value="<?php echo htmlspecialchars($cat['category_name']); ?>" required>
                                            <button type="submit" name="edit_category"
                                                class="btn btn-sm btn-primary">Update</button>
                                        </form>
                                        <form method="POST" action="" style="display:inline;">
                                            <input type="hidden" name="category_id"
                                                value="<?php echo $cat['category_id']; ?>">
                                            <button type="submit" name="delete_category" class="btn btn-sm btn-danger"
                                                onclick="return confirm('Are you sure you want to delete this category?')">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <script>
        // Tab functionality
        const tabLinks = document.querySelectorAll('.nav-tabs .nav-link');

        tabLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();

                // Remove active class from all tabs
                document.querySelectorAll('.nav-tabs .nav-link').forEach(tab => {
                    tab.classList.remove('active');
                });

                // Add active class to clicked tab
                this.classList.add('active');

                // Hide all tab panes
                document.querySelectorAll('.tab-pane').forEach(pane => {
                    pane.classList.remove('show', 'active');
                });

                // Show target tab pane
                const targetId = this.getAttribute('href');
                const targetPane = document.querySelector(targetId);
                if (targetPane) {
                    targetPane.classList.add('show', 'active');
                }
            });
        });
        </script>
        <script src="../assets/js/script.js"></script>

</body>

</html>