<?php
// process_files/process_add_user.php

session_start();

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../auth_guard.php';

// --------------------
// Helper function for redirect with session messages
// --------------------
function redirect($file, $status, $type = 'error') {
    $_SESSION['action_status'] = [
        'type' => $type,
        'message' => $status
    ];
    header("Location: $file");
    exit();
}

// --------------------
// 1. Only POST requests
// --------------------
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    redirect('../index.php', "Invalid request method. Please submit the form properly.", 'warning');
}

// --------------------
// 2. CSRF Token Validation
// --------------------
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    redirect('../index.php', "Invalid security token. Please try again.");
}

// --------------------
// 3. Identify source safely
// --------------------
$source = $_POST['source'] ?? 'signup';
$redirect_file = match($source) {
    'admin' => '../dashboard_users.php',
    'signup' => '../signup.php',
    default => '../index.php'
};

// --------------------
// 4. Collect & sanitize inputs
// --------------------
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
    redirect($redirect_file, "Please fill in all required fields.");
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirect($redirect_file, "Invalid email address.");
}

if ($password !== $confirm_password) {
    redirect($redirect_file, "Passwords do not match.");
}

// --------------------
// 5. Check duplicates
// --------------------
$check_stmt = $conn->prepare("SELECT COUNT(*) FROM user WHERE username = ? OR email = ?");
$check_stmt->bind_param("ss", $username, $email);
$check_stmt->execute();
$check_stmt->bind_result($exists);
$check_stmt->fetch();
$check_stmt->close();

if ($exists > 0) {
    redirect($redirect_file, "A user with that username or email already exists.");
}

// --------------------
// 6. Generate secure UUID for UID
// --------------------
$uid_result = $conn->query("SELECT UUID() AS uuid");
$uid = $uid_result->fetch_assoc()['uuid'] ?? null;
if (!$uid) {
    redirect($redirect_file, "Failed to generate unique user ID.");
}

// --------------------
// 7. Assign role, status, and optional FKs
// --------------------
if ($source === 'admin') {
    $role_ID = $_POST['role_ID'] ?? null;
    $status_ID = $_POST['status_ID'] ?? null;
    $payment_ID = !empty($_POST['payment_ID']) ? $_POST['payment_ID'] : null;
    $address_ID = !empty($_POST['address_ID']) ? $_POST['address_ID'] : null;
} else {
    $role_ID = 3;      // Individual
    $status_ID = 3;    // Pending Activation
    $payment_ID = null;
    $address_ID = null;
}

// Ensure IDs are integers or null
foreach (['role_ID', 'status_ID', 'payment_ID', 'address_ID'] as $var) {
    if (isset($$var) && !is_null($$var) && !ctype_digit((string) $$var)) {
        $$var = null;
    } else {
        $$var = is_null($$var) ? null : (int) $$var;
    }
}

// --------------------
// 8. Hash password securely
// --------------------
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// --------------------
// 9. Insert user using prepared statement
// --------------------
$stmt = $conn->prepare("
    INSERT INTO user (UID, username, email, password_hash, role_ID, status_ID, payment_ID, address_ID)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
");

$stmt->bind_param(
    "ssssiiii",
    $uid,
    $username,
    $email,
    $password_hash,
    $role_ID,
    $status_ID,
    $payment_ID,
    $address_ID
);

// --------------------
// 10. Execute and redirect
// --------------------
if ($stmt->execute()) {
    redirect($redirect_file, $source === 'admin' ? "User added successfully!" : "Signup successful!", 'success');
} else {
    redirect($redirect_file, "Database error: " . htmlspecialchars($stmt->error));
}

$stmt->close();
$conn->close();
