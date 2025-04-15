<?php
// Include DB connection
require_once 'includes/db_connect.php';

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize input
    $name      = trim($_POST['name']);
    $email     = trim($_POST['email']);
    $password  = $_POST['password'];
    $user_type = $_POST['user_type'];  // Can be 'customer' or 'admin'

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Choose table based on user type
    if ($user_type === 'customer') {
        $check_sql = "SELECT id FROM customers WHERE email = ?";
        $insert_sql = "INSERT INTO customers (name, email, password) VALUES (?, ?, ?)";
    } elseif ($user_type === 'admin') {
        $check_sql = "SELECT id FROM admins WHERE email = ?";
        $insert_sql = "INSERT INTO admins (name, email, password, created_at) VALUES (?, ?, ?, NOW())";
    } else {
        die("Invalid user type.");
    }

    // Check if email already exists
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows > 0) {
        echo "<script>alert('Account already exists with this email.'); window.location.href='register.php';</script>";
        $check_stmt->close();
        $conn->close();
        exit();
    }
    $check_stmt->close();

    // Now insert the account
    $insert_stmt = $conn->prepare($insert_sql);

    if ($user_type === 'customer') {
        $insert_stmt->bind_param("sss", $name, $email, $hashed_password);
    } elseif ($user_type === 'admin') {
        $insert_stmt->bind_param("sss", $name, $email, $hashed_password);
    }

    if ($insert_stmt->execute()) {
        if ($user_type === 'customer') {
            echo "<script>alert('Customer account created successfully!'); window.location.href='customer_login.php';</script>";
        } elseif ($user_type === 'admin') {
            echo "<script>alert('Admin account created successfully!'); window.location.href='admin_login.php';</script>";
        }
    } else {
        echo "Error: " . $insert_stmt->error;
    }

    $insert_stmt->close();
    $conn->close();
} else {
    header("Location: index.html");
}
?>
