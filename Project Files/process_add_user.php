<?php
// process_add_user.php

// --------------------
// 1. Database connection
// --------------------
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mydb";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . htmlspecialchars($conn->connect_error));
}

// --------------------
// 2. Handle only POST requests
// --------------------
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php?status=error_invalid_request");
    exit();
}

// --------------------
// 3. Identify source safely
// --------------------
// This controls where the user came from (admin panel, signup page, etc.)
// and defines safe redirect destinations.
$source = $_POST['source'] ?? 'signup'; // default public signup
$redirects = [
    'admin' => 'dashboard_users.php',
    'signup' => 'login.php',
];
$return_url = $redirects[$source] ?? 'index.php';

// --------------------
// 4. Collect and validate inputs
// --------------------
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
    header("Location: {$return_url}?status=error_missing_fields");
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: {$return_url}?status=error_invalid_email");
    exit();
}

if ($password !== $confirm_password) {
    header("Location: {$return_url}?status=error_password_mismatch");
    exit();
}

// --------------------
// 5. Check for duplicates
// --------------------
$check_stmt = $conn->prepare("SELECT COUNT(*) FROM user WHERE username = ? OR email = ?");
$check_stmt->bind_param("ss", $username, $email);
$check_stmt->execute();
$check_stmt->bind_result($exists);
$check_stmt->fetch();
$check_stmt->close();

if ($exists > 0) {
    header("Location: {$return_url}?status=error_duplicate_user");
    exit();
}

// --------------------
// 6. Generate secure UUID
// --------------------
$result = $conn->query("SELECT UUID() AS uuid");
$uid = $result->fetch_assoc()['uuid'] ?? null;
if (!$uid) {
    header("Location: {$return_url}?status=error_uuid_failed");
    exit();
}

// --------------------
// 7. Determine roles, statuses, and optional FKs
// --------------------
if ($source === 'admin') {
    $role_ID = $_POST['role_ID'] ?? null;
    $status_ID = $_POST['status_ID'] ?? null;
    $payment_ID = !empty($_POST['payment_ID']) ? $_POST['payment_ID'] : null;
    $address_ID = !empty($_POST['address_ID']) ? $_POST['address_ID'] : null;
} else {
    // Default for public signup
    $role_ID = 2;      // Individual
    $status_ID = 3;    // Pending activation
    $payment_ID = null;
    $address_ID = null;
}

// Validate IDs are numeric (defense-in-depth)
foreach (['role_ID', 'status_ID', 'payment_ID', 'address_ID'] as $var) {
    if (isset($$var) && !is_null($$var) && !ctype_digit((string) $$var)) {
        $$var = null; // Reset invalid input
    }
}

// --------------------
// 8. Hash password
// --------------------
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// --------------------
// 9. Insert user securely
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
    // SUCCESS: redirect to the right place with banner support
    if ($source === 'admin') {
        header("Location: dashboard_users.php?status=success_add");
    } else {
        header("Location: login.php?status=success_signup");
    }
    exit();
} else {
    // ERROR: redirect back with a database error banner
    header("Location: {$return_url}?status=error_db");
    exit();
}

$stmt->close();
$conn->close();
?>
