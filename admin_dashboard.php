<?php
session_start();
require_once 'includes/db_connect.php'; 

// Check if the user is logged in as an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: admin_login.php");
    exit();
}

// Fetch feedback with customer details
$query = "SELECT 
            f.id, 
            f.customer_id, 
            f.feedback_text, 
            f.analysis, 
            f.submitted_at, 
            c.name AS user_name, 
            c.email AS user_email 
          FROM feedback f 
          JOIN customers c ON f.customer_id = c.id 
          ORDER BY f.submitted_at ASC";

$result = $conn->query($query);

if (!$result) {
    die("Query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard | Feedback Analyzer</title>
  <link rel="stylesheet" href="assets/css/admin_dashboard.css">
  <style>
    .top-bar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
    }
    .logout-btn {
      padding: 8px 16px;
      background-color: #e74c3c;
      color: white;
      border: none;
      border-radius: 4px;
      cursor: pointer;
    }
    .logout-btn:hover {
      background-color: #c0392b;
    }
  </style>
</head>
<body>
  <div class="dashboard-container">
    <div class="top-bar">
      <h2>Admin Dashboard</h2>
      <form action="logout.php" method="post" style="margin: 0;">
        <button type="submit" class="logout-btn">Logout</button>
      </form>
    </div>

    <form method="POST" action="export_csv.php" style="margin-bottom: 20px;">
      <button type="submit" class="export-btn">📤 Export to CSV</button>
    </form>


    <div class="feedback-table">
      <table>
        <thead>
          <tr>
            <th>Feedback ID</th>
            <th>Customer ID</th>
            <th>User Name</th>
            <th>Email</th>
            <th>Feedback</th>
            <th>Analysis</th>
            <th>Date</th>
          </tr>
        </thead>
        <tbody>
          <?php
          if ($result->num_rows > 0) {
              while ($row = $result->fetch_assoc()) {
                  echo "<tr>";
                  echo "<td>" . $row['id'] . "</td>";
                  echo "<td>" . $row['customer_id'] . "</td>";
                  echo "<td>" . htmlspecialchars($row['user_name']) . "</td>";
                  echo "<td>" . htmlspecialchars($row['user_email']) . "</td>";
                  echo "<td>" . htmlspecialchars($row['feedback_text']) . "</td>";
                  echo "<td><a href='#' class='view-analysis-link' data-analysis=\"" . htmlspecialchars($row['analysis']) . "\">View Analysis</a></td>";
                  echo "<td>" . $row['submitted_at'] . "</td>";
                  echo "</tr>";
              }
          } else {
              echo "<tr><td colspan='7'>No feedback records found.</td></tr>";
          }
          ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Modal for AI Analysis -->
  <div id="analysisModal" class="modal">
    <div class="modal-content">
      <span class="close">&times;</span>
      <h3>AI Analysis</h3>
      <p id="analysisText"></p>
    </div>
  </div>

  <script>
    document.querySelectorAll('.view-analysis-link').forEach(link => {
      link.addEventListener('click', function(e) {
        e.preventDefault();
        const analysisText = this.getAttribute('data-analysis');
        document.getElementById('analysisText').innerText = analysisText;
        document.getElementById('analysisModal').style.display = 'block';
      });
    });

    document.querySelector('.close').addEventListener('click', function() {
      document.getElementById('analysisModal').style.display = 'none';
    });

    window.onclick = function(event) {
      if (event.target == document.getElementById('analysisModal')) {
        document.getElementById('analysisModal').style.display = 'none';
      }
    };
  </script>
</body>
</html>
