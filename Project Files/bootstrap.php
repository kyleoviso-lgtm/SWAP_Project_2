<?php
// --------------------
// SESSION START
// --------------------
if (session_status() === PHP_SESSION_NONE) {
    session_start();
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
    die("Database connection failed: " . $conn->connect_error);
}
