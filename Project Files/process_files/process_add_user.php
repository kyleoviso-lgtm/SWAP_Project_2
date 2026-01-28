<?php
// process_add_user.php

// --------------------
// GLOBAL PARENT DIRECTORY
// --------------------
// Go up two levels from this script (process_files) to Project Files
$PARENT_DIR = dirname(dirname($_SERVER['PHP_SELF']));

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
    header("Location: {$PARENT_DIR}/index.php?status=error_invalid_request");
    exit();
}

// --------------------
// 3. Identify source safely
// --------------------
// This controls where the user came from (admin panel, signup page, etc.)
$source = $_POST['source'] ?? 'signup'; // default public signup

// Map source to file names
$redirects = [
    'admin' => 'dashboard_users.php',
    'signup' => 'login.php',
];

// Base redirect file for this request
$redirect_file = $redirects[$source] ?? 'index.php';

// Helper function to redirect anywhere in parent folder
function redirect($file, $status) {
    global $PARENT_DIR;
    header("Location: {$PARENT_DIR}/{$file}?status={$status}");
    exit();
}

// --------------------
// 4. Collect and validate inputs
// --------------------
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
    redirect($redirect_file, "error_missing_fields");
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirect($redirect_file, "error_invalid_email");
}

if ($password !== $confirm_password) {
    redirect($redirect_file, "error_password_mismatch");
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
    redirect($redirect_file, "error_duplicate_user");
}

// --------------------
// 6. Generate secure UUID
// --------------------
$result = $conn->query("SELECT UUID() AS uuid");
$uid = $result->fetch_assoc()['uuid'] ?? null;
if (!$uid) {
    redirect($redirect_file, "error_uuid_failed");
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

// Validate IDs are numeric
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
    if ($source === 'admin') {
        redirect("dashboard_users.php", "success_add");
    } else {
        redirect("login.php", "success_signup");
    }
} else {
    redirect($redirect_file, "error_db");
}

$stmt->close();
$conn->close();
?>
