<?php
// process_files/process_delete_item.php

session_start();

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../auth_guard.php';

// --------------------
// Helper function for redirect with message
// --------------------
function redirectWithMessage($type, $message) {
    $_SESSION['action_status'] = [
        'type' => $type,
        'message' => $message
    ];
    header("Location: ../dashboard_product_management.php");
    exit();
}

// --------------------
// 1. Only allow POST requests
// --------------------
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectWithMessage('warning', "Invalid request method. Please submit the deletion form properly.");
}

// --------------------
// 2. CSRF Token Validation
// --------------------
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    redirectWithMessage('error', "Invalid security token. Please try again.");
}

// --------------------
// 3. Validate IID
// --------------------
$IID = trim($_POST['IID'] ?? '');
if (empty($IID)) {
    redirectWithMessage('error', "No Item ID provided for deletion.");
}

// --------------------
// 4. Check if item exists
// --------------------
$stmtCheck = $conn->prepare("SELECT IID FROM item WHERE IID = ?");
$stmtCheck->bind_param("s", $IID);
$stmtCheck->execute();
$result = $stmtCheck->get_result();

if ($result->num_rows === 0) {
    $stmtCheck->close();
    redirectWithMessage('warning', "Item with ID $IID does not exist.");
}
$stmtCheck->close();

// --------------------
// 5. Attempt deletion
// --------------------
$stmt = $conn->prepare("DELETE FROM item WHERE IID = ?");
if (!$stmt) {
    redirectWithMessage('error', "Database preparation error: " . htmlspecialchars($conn->error));
}

$stmt->bind_param("s", $IID);

if ($stmt->execute()) {
    redirectWithMessage('success', "Item with ID $IID deleted successfully.");
} else {
    $errorMsg = $stmt->error;

    // Detect foreign key constraint errors
    if (stripos($errorMsg, 'foreign key') !== false) {
        $errorMsg = "Cannot delete item $IID because it is referenced by existing orders.";
    }

    redirectWithMessage('error', "Deletion failed: " . htmlspecialchars($errorMsg));
}

$stmt->close();
$conn->close();
