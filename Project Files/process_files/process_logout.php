<?php
// process_files/process_logout.php

// --------------------
// PARENT DIRECTORY
// --------------------
$PARENT_DIR = dirname(dirname($_SERVER['PHP_SELF']));

// --------------------
// 1. Start session safely
// --------------------
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --------------------
// 2. Clear all session data
// --------------------
$_SESSION = [];

// --------------------
// 3. Delete session cookie
// --------------------
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// --------------------
// 4. Destroy the session completely
// --------------------
session_destroy();

// --------------------
// 5. Redirect to login page with status
// --------------------
header("Location: {$PARENT_DIR}/login_page.php?status=logged_out");
exit();
