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
    die("Connection failed: " . $conn->connect_error);
}

// --------------------
// 2. Handle only POST requests
// --------------------
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit();
}

// --------------------
// 3. Collect and validate inputs
// --------------------
$source = $_POST['source'] ?? 'public'; // identify where this came from
$return_url = $_POST['return_url'] ?? 'dashboard_users.php'; // default fallback

// --- sanitize URL
if (strpos($return_url, 'http') === 0 || strpos($return_url, '//') === 0) {
    $return_url = 'dashboard_users.php';
}

// Required fields
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
    die("Error: All required fields must be filled.");
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("Error: Invalid email format.");
}

if ($password !== $confirm_password) {
    die("Error: Passwords do not match.");
}

// --------------------
// 4. Check for duplicates
// --------------------
$check_stmt = $conn->prepare("SELECT COUNT(*) FROM user WHERE username = ? OR email = ?");
$check_stmt->bind_param("ss", $username, $email);
$check_stmt->execute();
$check_stmt->bind_result($exists);
$check_stmt->fetch();
$check_stmt->close();

if ($exists > 0) {
    header("Location: " . $return_url . "?error=duplicate_user");
    exit();
}

// --------------------
// 5. Hash password & create UUID
// --------------------
$password_hash = password_hash($password, PASSWORD_DEFAULT);
$uuid_result = $conn->query("SELECT UUID() AS uuid");
$uid = $uuid_result->fetch_assoc()['uuid'];

// --------------------
// 6. Assign role/status depending on source
// --------------------
if ($source === 'admin') {
    $role_ID = $_POST['role_ID'] ?? null;
    $status_ID = $_POST['status_ID'] ?? null;
    $payment_ID = !empty($_POST['payment_ID']) ? $_POST['payment_ID'] : null;
    $address_ID = !empty($_POST['address_ID']) ? $_POST['address_ID'] : null;
} else {
    // Default for public sign-up
    $role_ID = 2;      // 2 = Individual
    $status_ID = 3;    // 3 = pending_activation
    $payment_ID = null;
    $address_ID = null;
}

// --------------------
// 7. Insert user into database
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

if ($stmt->execute()) {
    // Redirect back
    header("Location: " . $return_url . "?success=1");
    exit();
} else {
    echo "Database error: " . htmlspecialchars($stmt->error);
}

$stmt->close();
$conn->close();
?>
