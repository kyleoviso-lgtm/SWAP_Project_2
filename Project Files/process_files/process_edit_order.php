<?php
// process_files/process_edit_order.php

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
    header("Location: ../dashboard_orders.php");
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
    header("Location: ../edit_order.php?OID=" . urlencode($_POST['OID'] ?? ''));
    exit;
}

// --------------------
// 3. Validate required fields
// --------------------
$requiredFields = ['OID', 'user_id', 'order_status_id', 'item_id', 'colour_id', 
                   'size_id', 'payment_id', 'address_id', 'item_qty', 'order_price'];

foreach ($requiredFields as $field) {
    if (!isset($_POST[$field]) || $_POST[$field] === '') {
        $_SESSION['action_status'] = [
            'type' => 'error',
            'message' => "The field '$field' is required."
        ];
        header("Location: ../edit_order.php?OID=" . urlencode($_POST['OID'] ?? ''));
        exit;
    }
}

// --------------------
// 4. Sanitize and assign
// --------------------
$OID = (int)$_POST['OID'];
$user_id = $_POST['user_id'];
$order_status_id = (int)$_POST['order_status_id'];
$item_id = (int)$_POST['item_id'];
$colour_id = (int)$_POST['colour_id'];
$size_id = (int)$_POST['size_id'];
$payment_id = (int)$_POST['payment_id'];
$address_id = (int)$_POST['address_id'];
$item_qty = (int)$_POST['item_qty'];
$order_price = $_POST['order_price'];

// --------------------
// 5. Validate numeric price
// --------------------
if (!is_numeric($order_price) || $order_price < 0) {
    $_SESSION['action_status'] = [
        'type' => 'error',
        'message' => "Order price must be a valid positive number."
    ];
    header("Location: ../edit_order.php?OID=" . urlencode($OID));
    exit;
}

// --------------------
// 6. Validate quantity
// --------------------
if ($item_qty < 1) {
    $_SESSION['action_status'] = [
        'type' => 'error',
        'message' => "Quantity must be at least 1."
    ];
    header("Location: ../edit_order.php?OID=" . urlencode($OID));
    exit;
}

// --------------------
// 7. Verify order exists
// --------------------
$check_stmt = $conn->prepare("SELECT OID FROM order_table WHERE OID = ?");
$check_stmt->bind_param("i", $OID);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows === 0) {
    $_SESSION['action_status'] = [
        'type' => 'error',
        'message' => "Order not found."
    ];
    $check_stmt->close();
    header("Location: ../dashboard_orders.php");
    exit;
}
$check_stmt->close();

// --------------------
// 8. Verify foreign key references
// --------------------
$foreign_checks = [
    ['table' => 'user', 'column' => 'UID', 'value' => $user_id, 'name' => 'User'],
    ['table' => 'order_stat', 'column' => 'OSID', 'value' => $order_status_id, 'name' => 'Order Status'],
    ['table' => 'item', 'column' => 'IID', 'value' => $item_id, 'name' => 'Item'],
    ['table' => 'colour', 'column' => 'CID', 'value' => $colour_id, 'name' => 'Colour'],
    ['table' => 'size', 'column' => 'SID', 'value' => $size_id, 'name' => 'Size'],
    ['table' => 'payment', 'column' => 'PID', 'value' => $payment_id, 'name' => 'Payment'],
    ['table' => 'address', 'column' => 'AID', 'value' => $address_id, 'name' => 'Address']
];

foreach ($foreign_checks as $check) {
    $fk_stmt = $conn->prepare("SELECT {$check['column']} FROM {$check['table']} WHERE {$check['column']} = ?");
    
    if ($check['column'] === 'UID') {
        $fk_stmt->bind_param("s", $check['value']);
    } else {
        $fk_stmt->bind_param("i", $check['value']);
    }
    
    $fk_stmt->execute();
    $fk_result = $fk_stmt->get_result();
    
    if ($fk_result->num_rows === 0) {
        $_SESSION['action_status'] = [
            'type' => 'error',
            'message' => "{$check['name']} does not exist in the database."
        ];
        $fk_stmt->close();
        header("Location: ../edit_order.php?OID=" . urlencode($OID));
        exit;
    }
    $fk_stmt->close();
}

// --------------------
// 9. Prepare update statement
// --------------------
$stmt = $conn->prepare("
    UPDATE order_table
    SET user_id = ?, order_status_id = ?, item_id = ?, colour_id = ?, 
        size_id = ?, payment_id = ?, address_id = ?, item_qty = ?, order_price = ?
    WHERE OID = ?
");

if (!$stmt) {
    $_SESSION['action_status'] = [
        'type' => 'error',
        'message' => "Database preparation error: " . $conn->error
    ];
    header("Location: ../edit_order.php?OID=" . urlencode($OID));
    exit;
}

$stmt->bind_param(
    "siiiiiiidi",
    $user_id,
    $order_status_id,
    $item_id,
    $colour_id,
    $size_id,
    $payment_id,
    $address_id,
    $item_qty,
    $order_price,
    $OID
);

// --------------------
// 10. Execute and redirect
// --------------------
if ($stmt->execute()) {
    $_SESSION['action_status'] = [
        'type' => 'success',
        'message' => "Order #$OID updated successfully!"
    ];
    header("Location: ../dashboard_orders.php");
    exit;
} else {
    $errorMsg = $stmt->error;

    // Handle foreign key constraint errors
    if (strpos($errorMsg, 'foreign key') !== false || strpos($errorMsg, 'Cannot add or update') !== false) {
        $errorMsg = "Invalid reference: One or more selected values do not exist in the database.";
    }

    $_SESSION['action_status'] = [
        'type' => 'error',
        'message' => "Update failed: $errorMsg"
    ];
    header("Location: ../edit_order.php?OID=" . urlencode($OID));
    exit;
}

$stmt->close();