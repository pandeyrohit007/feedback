<?php
session_start();
require_once 'includes/db_connect.php'; // Connect to DB

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    if (!empty($email) && !empty($password)) {
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
                header("Location: customer_dashboard.html");
                exit;
            } else {
                $error = "Invalid password.";
            }
        } else {
            $error = "No account found with that email.";
        }
    } else {
        $error = "Please fill in all fields.";
    }

    // If login fails
    $_SESSION['login_error'] = $error;
    header("Location: customer_login.html");
    exit;
}
?>
