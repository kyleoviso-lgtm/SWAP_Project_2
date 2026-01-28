<?php
// process_edit_user.php

// --------------------
// GLOBAL PARENT DIRECTORY
// --------------------
$PARENT_DIR = dirname(dirname($_SERVER['PHP_SELF']));

// --------------------
// Database connection
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
// Get POST data safely
// --------------------
$UID = $_POST['UID'] ?? '';
$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$role_ID = $_POST['role_ID'] ?? null;
$status_ID = $_POST['status_ID'] ?? null;
$payment_ID = (isset($_POST['payment_ID']) && $_POST['payment_ID'] !== '') ? $_POST['payment_ID'] : null;
$address_ID = (isset($_POST['address_ID']) && $_POST['address_ID'] !== '') ? $_POST['address_ID'] : null;
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// --------------------
// Basic validations
// --------------------
if (!$UID || !$username || !$email || !$role_ID || !$status_ID) {
    redirect("edit_user.php", ['UID' => $UID, 'status' => 'error_missing']);
}

if ($password && strlen($password) < 8) {
    redirect("edit_user.php", ['UID' => $UID, 'status' => 'error_password_length']);
}

if ($password && $password !== $confirm_password) {
    redirect("edit_user.php", ['UID' => $UID, 'status' => 'error_password_mismatch']);
}

// --------------------
// Start building SQL dynamically
// --------------------
$sql = "UPDATE user SET username = ?, email = ?, role_ID = ?, status_ID = ?";
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

// WHERE clause
$sql .= " WHERE UID = ?";
$types .= "s";
$params[] = $UID;

// --------------------
// Prepare and execute
// --------------------
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Prepare failed: " . $conn->error);
}

// Bind dynamically
$stmt->bind_param($types, ...$params);

// --------------------
// Execute and redirect
// --------------------
if ($stmt->execute()) {
    // Success redirect
    $stmt->close();
    $conn->close();
    redirect("dashboard_users.php", ['status' => 'success_edit']);
} else {
    // Failure redirect
    $stmt->close();
    $conn->close();
    redirect("dashboard_users.php", ['status' => 'error_db']);
}
?>
