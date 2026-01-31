<?php
// process_files/process_delete_user.php

session_start();

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../auth_guard.php';

// --------------------
// Helper function for redirect
// --------------------
function redirectWithStatus($status) {
    header("Location: ../dashboard_users.php?status={$status}");
    exit();
}

// --------------------
// 1. Only allow POST requests
// --------------------
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectWithStatus('error_invalid_request');
}

// --------------------
// 2. CSRF TOKEN VALIDATION
// --------------------
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    redirectWithStatus('error_csrf_invalid');
}

// --------------------
// 3. Validate UID
// --------------------
$UID = trim($_POST['UID'] ?? '');
if (empty($UID)) {
    redirectWithStatus('error_missing_id');
}

// UUID format check
if (!preg_match('/^[a-f0-9-]{36}$/i', $UID)) {
    redirectWithStatus('error_invalid_id');
}

// --------------------
// 4. Check if user exists
// --------------------
$stmtCheck = $conn->prepare("SELECT UID FROM user WHERE UID = ?");
$stmtCheck->bind_param("s", $UID);
$stmtCheck->execute();
$result = $stmtCheck->get_result();

if ($result->num_rows === 0) {
    $stmtCheck->close();
    redirectWithStatus('warning_user_not_found');
}
$stmtCheck->close();

// --------------------
// 5. Attempt deletion
// --------------------
$stmt = $conn->prepare("DELETE FROM user WHERE UID = ?");
if (!$stmt) {
    redirectWithStatus('error_stmt_failed');
}
$stmt->bind_param("s", $UID);

if ($stmt->execute()) {
    redirectWithStatus('success_delete');
} else {
    $errorMsg = $stmt->error;

    // Detect foreign key constraint error
    if (stripos($errorMsg, 'foreign key') !== false) {
        $errorMsg = "Cannot delete user $UID because it is referenced by other records (orders, payments, etc.).";
    }

    $_SESSION['action_status'] = [
        'type' => 'error',
        'message' => htmlspecialchars($errorMsg)
    ];
    header("Location: ../dashboard_users.php");
    exit();
}

$stmt->close();
$conn->close();
