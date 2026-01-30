<?php
session_start();
require_once 'payment_flow.php';

require_checkout_ready(); // prevent skipping cart

// Require login
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login_page.php');
    exit;
}

// Check cart
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    die('Your cart is empty.');
}

require_once 'stripe-php-19.3.0/init.php';
\Stripe\Stripe::setApiKey('sk_test_51SsJVhD22va0abhHFZLNVHNvDjFXt2roZqcP0RAdvvszNkAfLp6FfWmgyM04uR8kumj4zDAQx93RYXg8TBh68eM0005th24rsC');

// --- Normalize cart for metadata ---
$metadata_cart = [];
foreach ($_SESSION['cart'] as $item) {
    $metadata_cart[] = [
        'iid'          => (int)$item['iid'],
        'size'         => (int)($item['size'] ?? 0),
        'colour'       => (int)($item['colour'] ?? 0),
        'price'        => (float)($item['price'] ?? 0.0),
        'qty'          => max(1, (int)($item['qty'] ?? 1)),
        'size_label'   => $item['size_label'] ?? $item['size_text'] ?? '',
        'colour_label' => $item['colour_label'] ?? $item['colour_text'] ?? '',
        'name'         => $item['name'] ?? '',
    ];
}

// --- Build line items for Stripe ---
$line_items = [];
foreach ($metadata_cart as $item) {
    $line_items[] = [
        'price_data' => [
            'currency' => 'usd',
            'product_data' => [
                'name' => $item['name'] .
                    (!empty($item['size_label']) ? " - Size: {$item['size_label']}" : '') .
                    (!empty($item['colour_label']) ? " - Color: {$item['colour_label']}" : ''),
            ],
            'unit_amount' => intval($item['price'] * 100), // Stripe expects cents
        ],
        'quantity' => $item['qty'],
    ];
}

// --- Create Stripe Checkout Session ---
$session = \Stripe\Checkout\Session::create([
    'payment_method_types' => ['card'],
    'line_items'           => $line_items,
    'mode'                 => 'payment',
    'success_url'          => 'http://localhost/SWAP_Project_2/Project%20Files/pay.php',
    'cancel_url'           => 'http://localhost/SWAP_Project_2/Project%20Files/cart.php',
    'shipping_address_collection' => [
        'allowed_countries' => ['SG','US','CA'],
    ],
    // Optional: prefill shipping fields if you have a default
    // 'customer_details' => [
    //     'email' => $_SESSION['user_email'] ?? '',
    // ],
    'metadata' => [
        'user_id' => (int)$_SESSION['user_id'],
        'cart'    => json_encode($metadata_cart),
    ],
]);

// Redirect to Stripe Checkout
header("Location: " . $session->url);
exit;
?>
