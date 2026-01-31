<?php
session_start();
require_once 'payment_flow.php';

// db purely for checking for local mode
require_once 'db.php'; 


require_checkout_ready(); // prevent skipping cart

// require login
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login_page.php');
    exit;
}

// get login
$user_id   = $_SESSION['user_id'] ?? null;
$user_role = $_SESSION['role'] ?? null;
$username  = $_SESSION['username'] ?? null;

if (!$user_id) {
    die('User ID missing. Cannot proceed to checkout.');
}

// validate cart
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    die('Your cart is empty.');
}

// stripe init
require_once 'stripe-php-19.3.0/init.php';
require_once 'key.php';

\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

// format for stripe meta 
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

// strip fields
$line_items = [];
foreach ($metadata_cart as $item) {
    $line_items[] = [
        'price_data' => [
            'currency' => 'sgd',
            'product_data' => [
                'name' => $item['name'] .
                    (!empty($item['size_label']) ? " - Size: {$item['size_label']}" : '') .
                    (!empty($item['colour_label']) ? " - Color: {$item['colour_label']}" : ''),
            ],
            'unit_amount' => intval($item['price'] * 100),
        ],
        'quantity' => $item['qty'],
    ];
}

// check for local mode before creating session
if (defined('LOCAL_MODE') && LOCAL_MODE === true) {

    // generate simulated CS
    $cs = 'LOCAL_SIMULATION_' . uniqid();

    // store for downstream use
    $_SESSION['checkout_session_id'] = $cs;

    $success_url = "http://localhost/SWAP_Project_2/Project%20Files/pay.php?cs=$cs";




// remote ver
} else {
    $success_url = 'http://localhost/SWAP_Project_2/Project%20Files/pay.php?cs={CHECKOUT_SESSION_ID}';
}

// stripe session
$session = \Stripe\Checkout\Session::create([
    'payment_method_types' => ['card'],
    'line_items'           => $line_items,
    'mode'                 => 'payment',
    'success_url'          => $success_url,
    'cancel_url'           => 'http://localhost/SWAP_Project_2/Project%20Files/cart.php?checkout_cancelled=1',
    'shipping_address_collection' => [
        'allowed_countries' => ['SG'],
    ],
    'shipping_options' => [
        [
            'shipping_rate_data' => [
                'type'         => 'fixed_amount',
                'fixed_amount' => ['amount' => 0, 'currency' => 'sgd'],
                'display_name' => 'Standard Shipping',
                'delivery_estimate' => [
                    'minimum' => ['unit' => 'business_day', 'value' => 3],
                    'maximum' => ['unit' => 'business_day', 'value' => 7],
                ],
            ],
        ],
    ],

    'metadata' => [
        'user_id' => $user_id,
        'cart'    => json_encode($metadata_cart),
    ],
]);

// redirect to stripe
header("Location: " . $session->url);
exit;
?>
