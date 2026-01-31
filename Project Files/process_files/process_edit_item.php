<?php
// process_files/process_edit_item.php

session_start();

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../auth_guard.php';

// --------------------
// 1. Allow only POST requests
// --------------------
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['action_status'] = [
        'type' => 'warning',
        'message' => "Invalid request method. Please submit the form properly."
    ];
    header("Location: ../dashboard_product_management.php");
    exit;
}

// --------------------
// 2. CSRF TOKEN VALIDATION
// --------------------
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    $_SESSION['action_status'] = [
        'type' => 'error',
        'message' => "Invalid security token. Please try again."
    ];
    header("Location: ../edit_item.php?IID=" . urlencode($_POST['original_IID'] ?? ''));
    exit;
}

// --------------------
// 3. Validate required fields
// --------------------
$requiredFields = ['original_IID', 'IID', 'name', 'price', 'description', 'role_id'];
foreach ($requiredFields as $field) {
    if (empty($_POST[$field])) {
        $_SESSION['action_status'] = [
            'type' => 'error',
            'message' => "The field '$field' is required."
        ];
        header("Location: ../edit_item.php?IID=" . urlencode($_POST['original_IID'] ?? ''));
        exit;
    }
}

// --------------------
// 4. Sanitize & assign
// --------------------
$original_IID = trim($_POST['original_IID']);
$IID          = trim($_POST['IID']);
$name         = trim($_POST['name']);
$price        = trim($_POST['price']);
$description  = trim($_POST['description']);
$availability = isset($_POST['availability']) ? (int)$_POST['availability'] : 0;
$role_id      = (int)$_POST['role_id'];

// --------------------
// 5. Validate numeric fields
// --------------------
if (!is_numeric($price) || $price < 0) {
    $_SESSION['action_status'] = [
        'type' => 'error',
        'message' => "Price must be a valid positive number."
    ];
    header("Location: ../edit_item.php?IID=" . urlencode($original_IID));
    exit;
}

// Optional: Ensure IID format if using UUIDs or specific patterns
// if (!preg_match('/^[a-zA-Z0-9_-]+$/', $IID)) {
//     $_SESSION['action_status'] = [
//         'type' => 'error',
//         'message' => "Invalid Item ID format."
//     ];
//     header("Location: ../edit_item.php?IID=" . urlencode($original_IID));
//     exit;
// }

// --------------------
// 6. Prepare UPDATE statement
// --------------------
$stmt = $conn->prepare("
    UPDATE item
    SET IID = ?, name = ?, price = ?, description = ?, availability = ?, role_id = ?
    WHERE IID = ?
");

if (!$stmt) {
    $_SESSION['action_status'] = [
        'type' => 'error',
        'message' => "Database preparation error: " . $conn->error
    ];
    header("Location: ../edit_item.php?IID=" . urlencode($original_IID));
    exit;
}

$stmt->bind_param("ssdsiis", $IID, $name, $price, $description, $availability, $role_id, $original_IID);

// --------------------
// 7. Execute and redirect
// --------------------
if ($stmt->execute()) {
    $_SESSION['action_status'] = [
        'type' => 'success',
        'message' => "Item updated successfully!"
    ];
    header("Location: ../dashboard_product_management.php");
    exit;
} else {
    $errorMsg = $stmt->error;

    // Handle foreign key constraints or duplicate IID
    if (stripos($errorMsg, 'foreign key') !== false) {
        $errorMsg = "Cannot change Item ID because it is referenced by existing orders.";
    } elseif (stripos($errorMsg, 'Duplicate') !== false) {
        $errorMsg = "The new Item ID already exists. Choose a unique ID.";
    }

    $_SESSION['action_status'] = [
        'type' => 'error',
        'message' => "Update failed: $errorMsg"
    ];
    header("Location: ../edit_item.php?IID=" . urlencode($original_IID));
    exit;
}

$stmt->close();
$conn->close();
