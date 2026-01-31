<?php
// process_files/process_edit_item.php

session_start();

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../auth_guard.php';

// --------------------
// 1. Handle only POST requests
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
    if (!isset($_POST[$field]) || $_POST[$field] === '') {
        $_SESSION['action_status'] = [
            'type' => 'error',
            'message' => "The field '$field' is required."
        ];
        header("Location: ../edit_item.php?IID=" . urlencode($_POST['original_IID'] ?? ''));
        exit;
    }
}

// --------------------
// 4. Sanitize and assign
// --------------------
$original_IID = $_POST['original_IID'];
$IID = $_POST['IID'];
$name = $_POST['name'];
$price = $_POST['price'];
$description = $_POST['description'];
$availability = isset($_POST['availability']) ? (int)$_POST['availability'] : 0;
$role_id = (int)$_POST['role_id'];

// --------------------
// 5. Validate numeric price
// --------------------
if (!is_numeric($price) || $price < 0) {
    $_SESSION['action_status'] = [
        'type' => 'error',
        'message' => "Price must be a valid positive number."
    ];
    header("Location: ../edit_item.php?IID=" . urlencode($original_IID));
    exit;
}

// --------------------
// 6. Prepare update statement
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

    // Handle foreign key constraint for changing IID
    if (strpos($errorMsg, 'foreign key') !== false) {
        $errorMsg = "Cannot change Item ID because it is referenced by existing orders.";
    }

    $_SESSION['action_status'] = [
        'type' => 'error',
        'message' => "Update failed: $errorMsg"
    ];
    header("Location: ../edit_item.php?IID=" . urlencode($original_IID));
    exit;
}

$stmt->close();