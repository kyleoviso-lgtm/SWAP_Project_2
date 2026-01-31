<?php
// process_files/process_edit_user.php

// --------------------
// BOOTSTRAP & SESSION
// --------------------
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../auth_guard.php';

// --------------------
// PARENT DIRECTORY
// --------------------
$PARENT_DIR = dirname(dirname($_SERVER['PHP_SELF']));

// --------------------
// Helper function for redirect
// --------------------
function redirect($file, $params = []) {
    global $PARENT_DIR;
    $query = http_build_query($params);
    $url = $PARENT_DIR . "/" . $file;
    if ($query) {
        $url .= "?" . $query;
    }
    header("Location: " . $url);
    exit();
}

// --------------------
// 1. Only POST requests
// --------------------
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    redirect("dashboard_users.php", ['status' => 'error_invalid_request']);
}

// --------------------
// 2. CSRF TOKEN VALIDATION
// --------------------
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    redirect("dashboard_users.php", ['status' => 'error_csrf_invalid']);
}

// --------------------
// 3. Get POST data safely
// --------------------
$UID           = $_POST['UID'] ?? '';
$username      = trim($_POST['username'] ?? '');
$email         = trim($_POST['email'] ?? '');
$role_ID       = $_POST['role_ID'] ?? null;
$status_ID     = $_POST['status_ID'] ?? null;
$payment_ID    = isset($_POST['payment_ID']) && $_POST['payment_ID'] !== '' ? (int) $_POST['payment_ID'] : null;
$address_ID    = isset($_POST['address_ID']) && $_POST['address_ID'] !== '' ? (int) $_POST['address_ID'] : null;
$password      = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// --------------------
// 4. Basic validations
// --------------------
if (!$UID || !$username || !$email || !$role_ID || !$status_ID) {
    redirect("edit_user.php", ['UID' => $UID, 'status' => 'error_missing']);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirect("edit_user.php", ['UID' => $UID, 'status' => 'error_invalid_email']);
}

if ($password) {
    if (strlen($password) < 8) {
        redirect("edit_user.php", ['UID' => $UID, 'status' => 'error_password_length']);
    }
    if ($password !== $confirm_password) {
        redirect("edit_user.php", ['UID' => $UID, 'status' => 'error_password_mismatch']);
    }
}

// --------------------
// 5. Check for duplicate username/email (excluding self)
// --------------------
$dup_stmt = $conn->prepare("SELECT COUNT(*) FROM user WHERE (username = ? OR email = ?) AND UID != ?");
$dup_stmt->bind_param("sss", $username, $email, $UID);
$dup_stmt->execute();
$dup_stmt->bind_result($dup_count);
$dup_stmt->fetch();
$dup_stmt->close();

if ($dup_count > 0) {
    redirect("edit_user.php", ['UID' => $UID, 'status' => 'error_duplicate']);
}

// --------------------
// 6. Build dynamic SQL
// --------------------
$sql = "UPDATE user SET username = ?, email = ?, role_ID = ?, status_ID = ?";
$params = [$username, $email, (int)$role_ID, (int)$status_ID];
$types = "ssii";

// Add password if provided
if ($password) {
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $sql .= ", password_hash = ?";
    $types .= "s";
    $params[] = $hashedPassword;
}

// Optional foreign keys
if ($payment_ID !== null) {
    $sql .= ", payment_ID = ?";
    $types .= "i";
    $params[] = $payment_ID;
} else {
    $sql .= ", payment_ID = NULL";
}

if ($address_ID !== null) {
    $sql .= ", address_ID = ?";
    $types .= "i";
    $params[] = $address_ID;
} else {
    $sql .= ", address_ID = NULL";
}

// WHERE clause
$sql .= " WHERE UID = ?";
$types .= "s";
$params[] = $UID;

// --------------------
// 7. Prepare and execute
// --------------------
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    $_SESSION['action_status'] = [
        'type' => 'error',
        'message' => "Database preparation error: " . $conn->error
    ];
    redirect("edit_user.php", ['UID' => $UID]);
}

$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    $_SESSION['action_status'] = [
        'type' => 'success',
        'message' => "User updated successfully!"
    ];
    $stmt->close();
    $conn->close();
    redirect("dashboard_users.php");
} else {
    $errorMsg = $stmt->error;
    $_SESSION['action_status'] = [
        'type' => 'error',
        'message' => "Update failed: $errorMsg"
    ];
    $stmt->close();
    $conn->close();
    redirect("edit_user.php", ['UID' => $UID]);
}
