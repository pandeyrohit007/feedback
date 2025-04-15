<?php
session_start();
require_once 'includes/db_connect.php';

// Ensure only admins can access
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: admin_login.php");
    exit();
}

// Set headers to download file as CSV
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="feedback_export.csv"');

// Open output stream
$output = fopen('php://output', 'w');

// Output column headings
fputcsv($output, ['Feedback ID', 'Customer ID', 'User Name', 'Email', 'Feedback', 'Analysis', 'Date']);

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

// Output each row to CSV
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row['id'],
            $row['customer_id'],
            $row['user_name'],
            $row['user_email'],
            $row['feedback_text'],
            $row['analysis'],
            $row['submitted_at']
        ]);
    }
}

fclose($output);
exit();
?>
