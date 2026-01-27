<?php
// process_add_user.php

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mydb";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ensure POST method
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Detect form source (admin or public)
    $source = $_POST['source'] ?? 'public'; // default = public

    // Common inputs
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Universal validation
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        die("Error: All required fields must be filled.");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Error: Invalid email format.");
    }

    if ($password !== $confirm_password) {
        die("Error: Passwords do not match.");
    }

    // Check if username/email already exists
    $check_stmt = $conn->prepare("SELECT COUNT(*) FROM user WHERE username = ? OR email = ?");
    $check_stmt->bind_param("ss", $username, $email);
    $check_stmt->execute();
    $check_stmt->bind_result($exists);
    $check_stmt->fetch();
    $check_stmt->close();

    if ($exists > 0) {
        die("Error: Username or email already exists.");
    }

    // Hash password securely
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Generate UUID
    $uuid_result = $conn->query("SELECT UUID() AS uuid");
    $uid = $uuid_result->fetch_assoc()['uuid'];

    // Assign role and status depending on source
    if ($source === 'admin') {
        // Admin-supplied inputs
        $role_ID = $_POST['role_ID'];
        $status_ID = $_POST['status_ID'];
        $payment_ID = !empty($_POST['payment_ID']) ? $_POST['payment_ID'] : NULL;
        $address_ID = !empty($_POST['address_ID']) ? $_POST['address_ID'] : NULL;
    } else {
        // Public sign-up defaults
        $role_ID = 2; // 2 = Individual
        $status_ID = 3; // 3 = pending_activation
        $payment_ID = NULL;
        $address_ID = NULL;
    }

    // Insert new user
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
        if ($source === 'admin') {
            header("Location: dashboard_users.php?success=1");
        } else {
            header("Location: signup_success.php");
        }
        exit();
    } else {
        echo "Database error: " . htmlspecialchars($stmt->error);
    }

    $stmt->close();
} else {
    // Prevent direct access
    header("Location: index.php");
    exit();
}

$conn->close();
?>