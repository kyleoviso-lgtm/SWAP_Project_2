<?php
// delete_item.php

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mydb";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if IID is provided
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['IID'])) {
    $IID = $_POST['IID'];

    // Delete item using prepared statement
    $stmt = $conn->prepare("DELETE FROM item WHERE IID = ?");
    $stmt->bind_param("s", $IID);

    if ($stmt->execute()) {
        // Redirect back with success flag
        header("Location: dashboard_product_management.php?deleted=1");
        exit;
    } else {
        // Redirect back with error
        header("Location: dashboard_product_management.php?deleted=0");
        exit;
    }
} else {
    // If no IID or wrong method
    header("Location: dashboard_product_management.php");
    exit;
}
?>
