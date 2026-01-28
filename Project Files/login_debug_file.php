<?php
session_start();

// Check if user is logged in
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    echo "<h2>Logged In User Info</h2>";
    echo "User ID: " . htmlspecialchars($_SESSION['user_id']) . "<br>";
    echo "Username: " . htmlspecialchars($_SESSION['username']) . "<br>";
    echo "Role: " . htmlspecialchars($_SESSION['role']) . "<br>";
} else {
    echo "No user is logged in.";
}

// Optional: dump the full session array
echo "<pre>";
print_r($_SESSION);
echo "</pre>";
?>
