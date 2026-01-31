<?php
// process_files/process_login.php

// --------------------
// BOOTSTRAP & SESSION
// --------------------
require_once __DIR__ . '/../bootstrap.php';

// --------------------
// PARENT DIRECTORY
// --------------------
$PARENT_DIR = dirname(dirname($_SERVER['PHP_SELF']));

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
// 1. Only allow POST requests
// --------------------
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    redirect("login_page.php", ['status' => 'error_invalid_request']);
}

// --------------------
// 2. CSRF TOKEN VALIDATION
// --------------------
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
    redirect("login_page.php", ['status' => 'error_csrf_invalid']);
}

// --------------------
// 3. Get input safely
// --------------------
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// --------------------
// 4. Basic validation
// --------------------
if (empty($email) || empty($password)) {
    redirect("login_page.php", ['status' => 'error_missing_fields']);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirect("login_page.php", ['status' => 'error_invalid_email']);
}

// --------------------
// 5. Fetch user by email
// --------------------
$stmt = $conn->prepare("
    SELECT u.UID, u.username, u.email, u.password_hash, r.RoleName, s.status_name
    FROM user u
    INNER JOIN roles r ON u.role_ID = r.RID
    INNER JOIN user_stat s ON u.status_ID = s.USID
    WHERE u.email = ?
    LIMIT 1
");

if (!$stmt) {
    redirect("login_page.php", ['status' => 'error_db']);
}

$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    redirect("login_page.php", ['status' => 'error_invalid_credentials']);
}

$user = $result->fetch_assoc();
$stmt->close();

// --------------------
// 6. Check password
// --------------------
if (!password_verify($password, $user['password_hash'])) {
    redirect("login_page.php", ['status' => 'error_invalid_credentials']);
}

// --------------------
// 7. Check if account is active
// --------------------
if (strtolower($user['status_name']) !== 'active') {
    redirect("login_page.php", ['status' => 'error_account_inactive']);
}

// --------------------
// 8. Login successful - initialize session
// --------------------
session_regenerate_id(true); // prevent session fixation

$_SESSION['user_id'] = $user['UID'];
$_SESSION['username'] = $user['username'];
$_SESSION['role'] = strtolower($user['RoleName']);
$_SESSION['logged_in'] = true;

// Generate a fresh CSRF token after login
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// Optional: track login timestamp
$_SESSION['last_login'] = time();

// --------------------
// 9. Redirect based on role
// --------------------
switch ($_SESSION['role']) {
    case 'admin':
    case 'staff':
        redirect("dashboard_overview.php", ['status' => 'success_login']);
        break;
    case 'individual':
    case 'enterprise':
        redirect("store_page.php", ['status' => 'success_login']);
        break;
    default:
        // Unknown role: log out user immediately
        session_unset();
        session_destroy();
        redirect("login_page.php", ['status' => 'error_unknown_role']);
}
