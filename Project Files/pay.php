<?php


require_once 'db.php';
require_once 'payment_flow.php';

/**
 * SECURITY CHECKS
 */

// Prevent users from skipping checkout
require_checkout_ready();

// Require login
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login_page.php');
    exit;
}

// Validate Stripe checkout session reference (from redirect)
$checkout_session_id = $_GET['cs'] ?? null;

if (
    !$checkout_session_id ||
    !preg_match('/^cs_(test|live)_[A-Za-z0-9]+$/', $checkout_session_id)
) {
    http_response_code(400);
    exit('Invalid checkout session');
}

if (
    isset($_SESSION['success_seen_cs']) &&
    $_SESSION['success_seen_cs'] === $checkout_session_id
) {
    header('Location: store_page.php');
    exit;
}



// Fetch the latest order hash for THIS user only
$order_hash = null;

$stmt = $connection->prepare("
    SELECT order_hash
    FROM order_table
    WHERE user_id = ?
      AND CS = ?
    LIMIT 1
");
$stmt->bind_param('ss', $_SESSION['user_id'], $checkout_session_id);


$stmt->execute();
$stmt->bind_result($order_hash);
$stmt->fetch();
$stmt->close();

// If no order found, something went wrong
if (!$order_hash) {
    http_response_code(500);
    
    exit('Order not found');
}

// Clear cart and disable further checkout
unset($_SESSION['cart']);
disable_checkout();

// Prevent refresh / replay
$_SESSION['success_seen_cs'] = $checkout_session_id;

?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Payment Success</title>
<link rel="stylesheet" href="css/pay.css">
</head>
<body>

<div class="success-container">

    <div class="success-icon">✓</div>

    <h1>Thank you for your purchase!</h1>

    <p>Your payment was successful.</p>

    <p class="order-hash">
        <strong>Order Reference:</strong><br>
        <?= htmlspecialchars($order_hash, ENT_QUOTES, 'UTF-8') ?>
    </p>

    <p class="order-note">
        Please keep this reference for your records.
    </p>

    <a href="store_page.php" class="btn-continue">
        Continue Shopping
    </a>

</div>

</body>
</html>
