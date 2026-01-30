<?php
session_start();

/**
 * ===============================
 * CART & CHECKOUT FLOW CONTROL
 * ===============================
 */


/**
 * Ensure user is allowed to access checkout or payment pages
 * (prevents skipping directly to checkout or pay.php)
 */
function require_checkout_ready() {

    if (!isset($_SESSION['can_checkout']) || $_SESSION['can_checkout'] !== true) {
        // User tried to skip cart
        header('Location: cart.php');
        exit;
    }
}

/**
 * Mark checkout as allowed (after user reviews cart)
 * Call this from cart.php when user clicks "Proceed to Checkout"
 */
function enable_checkout() {
    if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0) {
        $_SESSION['can_checkout'] = true;
    }
}

/**
 * Clear checkout permission (after payment is completed)
 */
function disable_checkout() {
    unset($_SESSION['can_checkout']);
}

?>