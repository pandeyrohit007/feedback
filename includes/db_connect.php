<?php
$servername = "localhost";
$username = "root";
$password = ""; // or your MySQL password
$database = "feedback_analyzer";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
