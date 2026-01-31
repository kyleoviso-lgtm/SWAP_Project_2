<?php
session_start();

// --------------------
// Include dependencies
// --------------------
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../auth_guard.php';

// --------------------
// Debug flag
// --------------------
$debug = false; // Turn off for production

function debug_log($message) {
    global $debug;
    if ($debug) {
        echo "<p style='color: red; font-family: monospace;'>DEBUG: $message</p>";
    }
}

// --------------------
// Helper redirect function
// --------------------
function redirect($file, $status, $type = 'error') {
    global $debug;

    // Root-relative base path for your localhost project
    $basePath = '/SWAP_Part_2/SWAP_Project_2/Project%20Files/';

    // Clean up file path
    $file = preg_replace('#^(\.\./|\.\/)+#', '', $file);

    // Construct full redirect URL
    $url = $basePath . ltrim($file, '/');

    if ($debug) {
        echo "<p style='color: orange;'>Redirect triggered → $url ($status)</p>";
        exit;
    } else {
        $_SESSION['action_status'] = [
            'type' => $type,
            'message' => $status
        ];
        header("Location: $url");
        exit();
    }
}


// --------------------
// 1️⃣ Request method check
// --------------------
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    debug_log("Invalid method: " . $_SERVER["REQUEST_METHOD"]);
    redirect('../login_page.php', "Invalid request method. Please submit the form properly.", 'warning');
}

// --------------------
// 2️⃣ CSRF token validation
// --------------------
if (!isset($_POST['csrf_token'])) {
    debug_log("CSRF token missing in POST.");
    redirect('../login_page.php', "Missing CSRF token.");
}

if (!isset($_SESSION['csrf_token'])) {
    debug_log("CSRF token missing in SESSION.");
    redirect('../login_page.php', "Missing session CSRF token.");
}

if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    debug_log("CSRF mismatch. POST token: " . ($_POST['csrf_token'] ?? 'null') . " | SESSION token: " . ($_SESSION['csrf_token'] ?? 'null'));
    redirect('../login_page.php', "Invalid security token. Please try again.");
}

// --------------------
// 3️⃣ Identify source safely
// --------------------
$source = $_POST['source'] ?? 'signup';
$redirect_file = $_POST['return_url'] ?? match($source) {
    'admin' => '../dashboard_users.php',
    'signup' => '../signup.php',
    default => '../login_page.php'
};
debug_log("Source detected: $source → redirect_file: $redirect_file");

// --------------------
// 4️⃣ Collect inputs
// --------------------
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
    debug_log("Missing field(s): username='$username', email='$email'");
    redirect($redirect_file, "Please fill in all required fields.");
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    debug_log("Invalid email: $email");
    redirect($redirect_file, "Invalid email address.");
}

if ($password !== $confirm_password) {
    debug_log("Password mismatch.");
    redirect($redirect_file, "Passwords do not match.");
}

// --------------------
// 5️⃣ Check duplicates
// --------------------
$check_stmt = $conn->prepare("SELECT COUNT(*) FROM user WHERE username = ? OR email = ?");
$check_stmt->bind_param("ss", $username, $email);
$check_stmt->execute();
$check_stmt->bind_result($exists);
$check_stmt->fetch();
$check_stmt->close();
debug_log("Duplicate check result: exists=$exists");

if ($exists > 0) {
    redirect($redirect_file, "A user with that username or email already exists.");
}

// --------------------
// 6️⃣ Generate secure UUID
// --------------------
$uid_result = $conn->query("SELECT UUID() AS uuid");
$uid = $uid_result->fetch_assoc()['uuid'] ?? null;
debug_log("Generated UID: " . ($uid ?: "NULL"));

if (!$uid) {
    redirect($redirect_file, "Failed to generate unique user ID.");
}

// --------------------
// 7️⃣ Assign role, status, FKs
// --------------------
if ($source === 'admin') {
    $role_ID = $_POST['role_ID'] ?? null;
    $status_ID = $_POST['status_ID'] ?? null;
    $payment_ID = !empty($_POST['payment_ID']) ? $_POST['payment_ID'] : null;
    $address_ID = !empty($_POST['address_ID']) ? $_POST['address_ID'] : null;
} else {
    $role_ID = 3;
    $status_ID = 3;
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
debug_log("Role=$role_ID, Status=$status_ID, Payment=$payment_ID, Address=$address_ID");

// --------------------
// 8️⃣ Hash password securely
// --------------------
$password_hash = password_hash($password, PASSWORD_DEFAULT);
debug_log("Password hashed successfully.");

// --------------------
// 9️⃣ Insert user
// --------------------
$stmt = $conn->prepare("
    INSERT INTO user (UID, username, email, password_hash, role_ID, status_ID, payment_ID, address_ID)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
");

if (!$stmt) {
    debug_log("Prepare failed: " . $conn->error);
    redirect($redirect_file, "Database prepare error: " . htmlspecialchars($conn->error));
}

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
    debug_log("User inserted successfully → redirecting to $redirect_file");
    redirect($redirect_file, $source === 'admin' ? "User added successfully!" : "Signup successful!", 'success');
} else {
    debug_log("Database error: " . $stmt->error);
    redirect($redirect_file, "Database error: " . htmlspecialchars($stmt->error));
}

$stmt->close();
$conn->close();
?>
