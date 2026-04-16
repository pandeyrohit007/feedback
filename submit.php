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
$customer_id = $_SESSION['user_id'];
$customer_email = $_SESSION['user_email'];

// Step 3: Get AI analysis from Gemini
$analysis = getGeminiAnalysis($feedback);

// Step 4: Insert into the database
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

function getGeminiAnalysis($feedback) {
    $api_key = getenv('GEMINI_API_KEY') ?: "AIzaSyD7roQlayvnjQRp88Ej-BsQYGMnk_Ja9xw"; // Use environment variable in production
    
    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=" . $api_key;
    
    $postData = [
        "contents" => [
            [
                "parts" => [
                    [
                        "text" => 'Please analyze the following customer feedback: "' . $feedback . '". 

Give a very short and concise analysis, and also provide one or two specific suggestions for how a business can make smarter decisions based on this feedback.'
                    ]
                ]
            ]
        ]
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode == 200) {
        $data = json_decode($response, true);
        return $data['candidates'][0]['content']['parts'][0]['text'] ?? "No analysis available.";
    } else {
        return "Error getting AI analysis.";
    }
}
?>
