<?php
session_start();
require_once 'includes/db_connect.php'; // Make sure this connects to your DB

// If form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Query the admins table
    $stmt = $conn->prepare("SELECT id, name, password FROM admins WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    // If admin exists
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $name, $hashed_password);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
          // Correct login
          $_SESSION['user_id'] = $id;             // Store the admin ID in the session
          $_SESSION['user_name'] = $name;         // Store the admin name in the session
          $_SESSION['user_type'] = 'admin';       // Store user type as 'admin'
          $_SESSION['user_email'] = $email;      // ✅ Needed for further use

          // Redirect to admin dashboard
          header("Location: admin_dashboard.php");
          exit();
      } else {
          $error = "Invalid password.";
      }
    } else {
        $error = "Admin not found.";
    }

    $stmt->close();
}
?>

<!-- Admin Login Form UI -->
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Login | Feedback Analyzer</title>
  <link rel="stylesheet" href="assets/css/admin.css"> <!-- Your custom styles -->
</head>
<body>
  <div class="login-container">
    <h2>Admin Login</h2>

    <?php if (isset($error)): ?>
      <div class="error-message"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" action="admin_login.php" autocomplete="off">
      <input type="email" name="email" placeholder="Email" required autocomplete="off">
      <input type="password" name="password" placeholder="Password" id="password" required autocomplete="new-password">
      <div class="show-password">
        <input type="checkbox" id="togglePassword" />
        <label for="togglePassword">Show Password</label>
      </div>
      <button type="submit">Login</button>
    </form>

    <p><a href="index.html">Back to Home</a></p>
  </div>

  <script>
  const togglePassword = document.getElementById("togglePassword");
  const passwordField = document.getElementById("password");

  togglePassword.addEventListener("change", function () {
    if (this.checked) {
      passwordField.type = "text";
    } else {
      passwordField.type = "password";
    }
  });
  </script>

</body>
</html>
