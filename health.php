<?php
// Health check script for Feedback Analyzer
header('Content-Type: application/json');

$checks = [
    'php_version' => PHP_VERSION,
    'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
    'database' => 'Checking...',
    'environment' => 'Checking...',
    'status' => 'OK'
];

try {
    // Check database connection
    require_once 'includes/db_connect.php';
    $stmt = $conn->prepare("SELECT 1");
    $stmt->execute();
    $checks['database'] = 'Connected';
    $conn->close();
} catch (Exception $e) {
    $checks['database'] = 'Failed: ' . $e->getMessage();
    $checks['status'] = 'ERROR';
}

// Check environment variables
$required_env = ['DB_HOST', 'DB_USER', 'DB_PASS', 'DB_NAME'];
$missing_env = [];

foreach ($required_env as $env) {
    if (!getenv($env)) {
        $missing_env[] = $env;
    }
}

if (empty($missing_env)) {
    $checks['environment'] = 'All required variables set';
} else {
    $checks['environment'] = 'Missing: ' . implode(', ', $missing_env);
    $checks['status'] = 'WARNING';
}

// Check if Gemini API key is set
if (!getenv('GEMINI_API_KEY')) {
    $checks['gemini_api'] = 'Not configured';
    if ($checks['status'] === 'OK') {
        $checks['status'] = 'WARNING';
    }
} else {
    $checks['gemini_api'] = 'Configured';
}

echo json_encode($checks, JSON_PRETTY_PRINT);
?>