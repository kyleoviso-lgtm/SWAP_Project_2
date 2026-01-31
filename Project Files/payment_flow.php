<?php
session_start();

// this file validates users action flow, ensuring correct operation order
// call function fm other files

// starting a session is no longer req on other files since it will be done here

// prevents skipping to checkout or pay.php
function require_checkout_ready() {

    if (!isset($_SESSION['can_checkout']) || $_SESSION['can_checkout'] !== true) {
        // User tried to skip cart
        header('Location: cart.php');
        exit;
    }
}

// allows checkout after validation
function enable_checkout() {
    if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0) {
        $_SESSION['can_checkout'] = true;
    }
}

// clear checkout permission (after payment is completed)
 
function disable_checkout() {
    unset($_SESSION['can_checkout']);
}

?>