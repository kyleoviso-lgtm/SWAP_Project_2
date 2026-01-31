<?php
// process_files/process_add_item.php

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
    header("Location: ../add_item.php");
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
    header("Location: ../add_item.php");
    exit;
}

// --------------------
// 3. Validate required fields
// --------------------
$requiredFields = ['name', 'price', 'description', 'availability', 'role_id'];
foreach ($requiredFields as $field) {
    if (!isset($_POST[$field]) || $_POST[$field] === '') {
        $_SESSION['action_status'] = [
            'type' => 'error',
            'message' => "The field '$field' is required."
        ];
        header("Location: ../add_item.php");
        exit;
    }
}

// --------------------
// 4. Sanitize & assign
// --------------------
$name = $_POST['name'];
$price = (float) $_POST['price'];
$description = $_POST['description'];
$availability = (int)$_POST['availability'];
$role_id = (int)$_POST['role_id'];

// --------------------
// 5. Validate numeric price
// --------------------
if (!is_numeric($price) || $price < 0) {
    $_SESSION['action_status'] = [
        'type' => 'error',
        'message' => "Price must be a valid positive number."
    ];
    header("Location: ../add_item.php");
    exit;
}

// --------------------
// 6. Prepare INSERT statement
// --------------------
$stmt = $conn->prepare("
    INSERT INTO item (name, price, description, availability, role_id)
    VALUES (?, ?, ?, ?, ?)
");

if (!$stmt) {
    $_SESSION['action_status'] = [
        'type' => 'error',
        'message' => "Database preparation error: " . $conn->error
    ];
    header("Location: ../add_item.php");
    exit;
}

$stmt->bind_param("sdsii", $name, $price, $description, $availability, $role_id);

// --------------------
// 7. Execute and redirect
// --------------------
if ($stmt->execute()) {
    $_SESSION['action_status'] = [
        'type' => 'success',
        'message' => "Item added successfully!"
    ];
    header("Location: ../dashboard_product_management.php");
    exit;
} else {
    $_SESSION['action_status'] = [
        'type' => 'error',
        'message' => "Insert failed: " . $stmt->error
    ];
    header("Location: ../add_item.php");
    exit;
}

$stmt->close();