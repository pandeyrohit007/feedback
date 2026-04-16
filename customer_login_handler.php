<?php
session_start();
require_once 'includes/db_connect.php'; // Connect to DB

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    if (!empty($email) && !empty($password)) {
        try {
            $query = "SELECT * FROM customers WHERE email = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows === 1) {
                $user = $result->fetch_assoc();

                if (password_verify($password, $user['password'])) {
                    // Store session and redirect
                    $_SESSION['customer_id'] = $user['id'];
                    $_SESSION['customer_name'] = $user['name'];
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_type'] = 'customer';
                    $_SESSION['user_email'] = $user['email'];
                    header("Location: customer_dashboard.php");
                    exit;
                } else {
                    $error = "Invalid password.";
                }
            } else {
                $error = "No account found with that email.";
            }
        } catch (Exception $e) {
            $error = "Database error.";
        }
    } else {
        $error = "Please fill in all fields.";
    }

    // If login fails
    $_SESSION['login_error'] = $error;
    header("Location: customer_login.php");
    exit;
}
?>
