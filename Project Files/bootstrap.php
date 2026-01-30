<?php
// --------------------
// SESSION START
// --------------------
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --------------------
// CSRF TOKEN (generate once per session)
// --------------------
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// --------------------
// DATABASE CONNECTION
// --------------------
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "mydb";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Database connection failed");
}
