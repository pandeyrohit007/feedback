<?php
session_start();
require_once 'includes/db_connect.php';

header('Content-Type: application/json');

// Enable debugging (remove in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Step 1: Ensure user is logged in
if (!isset($_SESSION['user_id'], $_SESSION['user_email'])) {
    echo json_encode(["success" => false, "message" => "Session error: User not logged in."]);
    exit();
}

// Step 2: Get and validate input JSON
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['feedback']) || empty(trim($data['feedback']))) {
    echo json_encode(["success" => false, "message" => "Feedback is required."]);
    exit();
}

$feedback = trim($data['feedback']);
$analysis = isset($data['analysis']) ? trim($data['analysis']) : '';
$customer_id = $_SESSION['user_id'];
$customer_email = $_SESSION['user_email'];

// Step 3: Insert into the database
$stmt = $conn->prepare("INSERT INTO feedback (customer_id, customer_email, feedback_text, analysis, submitted_at) VALUES (?, ?, ?, ?, NOW())");

if (!$stmt) {
    echo json_encode(["success" => false, "message" => "DB Prepare failed: " . $conn->error]);
    exit();
}

$stmt->bind_param("isss", $customer_id, $customer_email, $feedback, $analysis);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "message" => "Execution failed: " . $stmt->error]);
}

$stmt->close();
$conn->close();
