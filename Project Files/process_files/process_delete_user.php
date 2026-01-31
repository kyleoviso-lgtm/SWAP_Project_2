<?php
// process_files/process_delete_user.php

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
// 1. Validate request method
// --------------------
// Note: Delete operations via GET should ideally be POST, but keeping original structure
// If this uses GET, CSRF should be in URL or consider converting to POST with a form

if (!isset($_GET['UID']) || empty($_GET['UID'])) {
    header("Location: {$PARENT_DIR}/dashboard_users.php?status=error_missing_id");
    exit();
}

// --------------------
// 2. CSRF TOKEN VALIDATION (for GET-based delete)
// --------------------
// IMPORTANT: For security, delete operations should use POST with CSRF in form
// If using GET, CSRF token should be in URL parameter
if (!isset($_GET['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_GET['csrf_token'])) {
    header("Location: {$PARENT_DIR}/dashboard_users.php?status=error_csrf_invalid");
    exit();
}

// --------------------
// 3. Validate UID format
// --------------------
$UID = $_GET['UID'];

if (!preg_match('/^[a-f0-9-]{36}$/i', $UID)) {
    header("Location: {$PARENT_DIR}/dashboard_users.php?status=error_invalid_id");
    exit();
}

// --------------------
// 4. Delete user securely
// --------------------
$stmt = $conn->prepare("DELETE FROM user WHERE UID = ?");
if (!$stmt) {
    header("Location: {$PARENT_DIR}/dashboard_users.php?status=error_stmt_failed");
    exit();
}

$stmt->bind_param("s", $UID);

if ($stmt->execute()) {
    header("Location: {$PARENT_DIR}/dashboard_users.php?status=success_delete");
    exit();
} else {
    header("Location: {$PARENT_DIR}/dashboard_users.php?status=error_delete_failed");
    exit();
}

$stmt->close();
$conn->close();
?>