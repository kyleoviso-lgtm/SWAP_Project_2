<?php
// process_delete_user.php

// ---------------------------
// 1. Database Connection
// ---------------------------
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mydb";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Database connection failed: " . htmlspecialchars($conn->connect_error));
}

// ---------------------------
// 2. Validate and Fetch UID
// ---------------------------
$UID = $_GET['UID'] ?? '';

if (empty($UID)) {
    header("Location: dashboard_users.php?status=error_missing_id");
    exit();
}

// Optional: Confirm valid UUID format (defense-in-depth)
if (!preg_match('/^[a-f0-9-]{36}$/i', $UID)) {
    header("Location: dashboard_users.php?status=error_invalid_id");
    exit();
}

// ---------------------------
// 3. Delete User Securely
// ---------------------------
$stmt = $conn->prepare("DELETE FROM user WHERE UID = ?");
if (!$stmt) {
    header("Location: dashboard_users.php?status=error_stmt_failed");
    exit();
}

$stmt->bind_param("s", $UID);

if ($stmt->execute()) {
    // ✅ Success
    header("Location: dashboard_users.php?status=success_delete");
    exit();
} else {
    // ❌ Failure
    header("Location: dashboard_users.php?status=error_delete_failed");
    exit();
}

$stmt->close();
$conn->close();
?>
