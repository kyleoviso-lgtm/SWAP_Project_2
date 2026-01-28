<?php
session_start();

// --------------------
// GLOBAL PARENT DIRECTORY
// --------------------
$PARENT_DIR = dirname(dirname($_SERVER['PHP_SELF']));

// --------------------
// Database connection
// --------------------
$conn = new mysqli("localhost", "root", "", "mydb");
if ($conn->connect_error) {
    die("Connection failed: " . htmlspecialchars($conn->connect_error));
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
// Only allow POST
// --------------------
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    redirect("login.php", ['status' => 'error_invalid_request']);
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// --------------------
// Basic validation
// --------------------
if (empty($email) || empty($password)) {
    redirect("login.php", ['status' => 'error_missing_fields']);
}

// --------------------
// Fetch user by email
// --------------------
$stmt = $conn->prepare("
    SELECT u.UID, u.username, u.email, u.password_hash, r.RoleName, s.status_name
    FROM user u
    INNER JOIN roles r ON u.role_ID = r.RID
    INNER JOIN user_stat s ON u.status_ID = s.USID
    WHERE u.email = ?
    LIMIT 1
");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    redirect("login.php", ['status' => 'error_invalid_credentials']);
}

$user = $result->fetch_assoc();

// --------------------
// Check password
// --------------------
if (!password_verify($password, $user['password_hash'])) {
    redirect("login.php", ['status' => 'error_invalid_credentials']);
}

// --------------------
// Check if account is active
// --------------------
if (strtolower($user['status_name']) !== 'active') {
    redirect("login.php", ['status' => 'error_account_inactive']);
}

// --------------------
// Login successful — create session
// --------------------
$_SESSION['user_id'] = $user['UID'];
$_SESSION['username'] = $user['username'];
$_SESSION['role'] = strtolower($user['RoleName']);
$_SESSION['logged_in'] = true;

// --------------------
// Redirect based on role
// --------------------
switch ($_SESSION['role']) {
    case 'admin':
    case 'staff':
        redirect("dashboard_overview.php", ['status' => 'successful_login']);
        break;
    case 'individual':
    case 'enterprise':
        redirect("store_page.php", ['status' => 'success_login']);
        break;
    default:
        redirect("login.php", ['status' => 'error_unknown_role']);
}
?>
