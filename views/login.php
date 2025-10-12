<?php
require_once '../includes/config.php';

$error = '';

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        $connection = mysqli_connect($db_host, $db_user, $db_pass, $db_name);
        if (!$connection) {
            die("Database connection failed: " . mysqli_connect_error());
        }
        mysqli_set_charset($connection, 'utf8');

        $username_escaped = mysqli_real_escape_string($connection, $username);

        $sql = "SELECT admin_id, username, password_hash, full_name, email
                FROM admins
                WHERE username = '$username_escaped'";

        $result = mysqli_query($connection, $sql);

        if (mysqli_num_rows($result) == 1) {
            $admin = mysqli_fetch_assoc($result);

            if (password_verify($password, $admin['password_hash'])) {
                $admin_id_escaped = mysqli_real_escape_string($connection, $admin['admin_id']);
                $update_sql = "UPDATE admins
                               SET updated_at = CURRENT_TIMESTAMP
                               WHERE admin_id = $admin_id_escaped";
                mysqli_query($connection, $update_sql);

                $_SESSION['admin_id'] = $admin['admin_id'];
                $_SESSION['admin_name'] = $admin['full_name'];
                $_SESSION['login_time'] = time();

                mysqli_close($connection);
                header('Location: dashboard.php');
                exit;
            }
        }

        mysqli_close($connection);
        $error = 'Invalid username or password.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $system_name; ?> - Login</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <div class="login-container">
        <div class="login-box">
            <h2><?php echo $system_name; ?></h2>
            <p class="login-subtitle">Please sign in to continue</p>

            <?php if ($error): ?>
            <div class="error-message">
                <?php echo $error; ?>
            </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit" name="login" class="btn btn-primary">
                    Sign In
                </button>
            </form>


        </div>
    </div>
</body>

</html>