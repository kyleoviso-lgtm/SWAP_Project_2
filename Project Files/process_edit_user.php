<?php
// process_edit_user.php

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mydb";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get POST data safely
$UID = isset($_POST['UID']) ? $_POST['UID'] : '';
$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$role_ID = isset($_POST['role_ID']) ? $_POST['role_ID'] : null;
$status_ID = isset($_POST['status_ID']) ? $_POST['status_ID'] : null;
$payment_ID = isset($_POST['payment_ID']) && $_POST['payment_ID'] !== '' ? $_POST['payment_ID'] : null;
$address_ID = isset($_POST['address_ID']) && $_POST['address_ID'] !== '' ? $_POST['address_ID'] : null;
$password = isset($_POST['password']) ? $_POST['password'] : '';
$confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

// Basic validations
if (!$UID || !$username || !$email || !$role_ID || !$status_ID) {
    header("Location: edit_user.php?UID={$UID}&status=error_missing");
    exit();
}

if ($password && strlen($password) < 8) {
    header("Location: edit_user.php?UID={$UID}&status=error_password_length");
    exit();
}

if ($password && $password !== $confirm_password) {
    header("Location: edit_user.php?UID={$UID}&status=error_password_mismatch");
    exit();
}


// Start building SQL dynamically
$sql = "UPDATE user SET username = ?, email = ?, role_ID = ?, status_ID = ?";

// Prepare array for bind_param
$params = [$username, $email, $role_ID, $status_ID];
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

// WHERE clause for UID
$sql .= " WHERE UID = ?";
$types .= "s";
$params[] = $UID;

// Prepare and execute
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Prepare failed: " . $conn->error);
}

// Bind dynamically
$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    $stmt->close();
    $conn->close();
    // Redirect back to dashboard with success banner
    header("Location: dashboard_users.php?status=success_edit");
    exit();
} else {
    // On failure, redirect back with error message
    header("Location: dashboard_users.php?status=error_db");
    exit();
}

?>
