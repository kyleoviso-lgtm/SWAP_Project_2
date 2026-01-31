<?php
// process_files/process_logout.php

// --------------------
// PARENT DIRECTORY
// --------------------
$PARENT_DIR = dirname(dirname($_SERVER['PHP_SELF']));

// --------------------
// 1. Start session
// --------------------
session_start();

// --------------------
// 2. Destroy session
// --------------------
$_SESSION = [];
session_destroy();

// Optional: delete the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// --------------------
// 3. Redirect to login page
// --------------------
header("Location: {$PARENT_DIR}/login_page.php?status=logged_out");
exit();
?>