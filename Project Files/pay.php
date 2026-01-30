<?php
session_start();

// Optional: require user to be logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login_page.php');
    exit;
}

// Clear only the cart in session
unset($_SESSION['cart']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Payment Success</title>
</head>
<body>
<h1>Thank you for your purchase!</h1>
<p>Your payment was successful. Your cart has been cleared.</p>
<a href="store_page.php">Continue Shopping</a>
</body>
</html>
