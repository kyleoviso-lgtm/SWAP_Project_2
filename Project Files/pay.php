<?php

require_once 'payment_flow.php';

// Prevent users from skipping checkout
require_checkout_ready(); 

// Require login
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login_page.php');
    exit;
}

// At this point: user is logged in, has cart items, and has checkout permission
// Clear cart and disable further checkout
unset($_SESSION['cart']);
disable_checkout(); // prevent re-accessing payment page directly
?>



<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Payment Success</title>
<link rel="stylesheet" href="css/pay.css">
</head>
<body>
<!-- Centered container for success message -->
<div class="success-container">
  
    <div class="success-icon">✓</div>

    <h1>Thank you for your purchase!</h1>
    <p>Your payment was successful. Your cart has been cleared.</p>
    
    <!-- Styled continue button -->
    <a href="store_page.php" class="btn-continue">Continue Shopping</a>
</div>
</body>
</html>
