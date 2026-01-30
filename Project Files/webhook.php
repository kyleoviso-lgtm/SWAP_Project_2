<?php
// webhook.php
// Stripe webhook to process checkout.session.completed
session_start();
require_once 'db.php';
require_once 'stripe-php-19.3.0/init.php';

// Stripe sandbox key
\Stripe\Stripe::setApiKey('sk_test_51SsJVhD22va0abhHFZLNVHNvDjFXt2roZqcP0RAdvvszNkAfLp6FfWmgyM04uR8kumj4zDAQx93RYXg8TBh68eM0005th24rsC');
$endpoint_secret = 'whsec_ds9NLrXrXWoGpxNuDjMOC3ceRe7gSfZd';

// Read payload and signature
$payload = file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

try {
    $event = \Stripe\Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
} catch (\Throwable $e) {
    http_response_code(400);
    exit('Webhook signature verification failed');
}

// Only handle checkout.session.completed
if ($event->type !== 'checkout.session.completed') {
    http_response_code(200);
    exit;
}

$session = $event->data->object;

// Retrieve full session with PaymentIntent expanded
$session_full = \Stripe\Checkout\Session::retrieve(
    $session->id,
    ['expand' => ['payment_intent']]
);

// --- Get user ID from Stripe metadata ---
$user_id = $session_full->metadata->user_id ?? null;
if (!$user_id) {
    http_response_code(400);
    exit('User ID missing in metadata');
}

// --- Get cart from metadata ---
$cart = json_decode($session_full->metadata->cart ?? '[]', true);
if (!is_array($cart) || empty($cart)) {
    http_response_code(200);
    exit('Cart empty');
}

// --- Insert address ---
$shipping = $session_full->customer_details->address ?? null;
$address_id = null;

if ($shipping && !empty($shipping->line1) && !empty($shipping->country)) {
    $stmt = $connection->prepare("
        INSERT INTO address (address_line_1, address_line_2, city, country, ZIP_code)
        VALUES (?, ?, ?, ?, ?)
    ");
    $line1 = $shipping->line1;
    $line2 = $shipping->line2 ?? '';
    $city  = $shipping->city ?? '';
    $country = $shipping->country;
    $zip_code = $shipping->postal_code ?? '';

    $stmt->bind_param('sssss', $line1, $line2, $city, $country, $zip_code);
    $stmt->execute();
    $address_id = $stmt->insert_id;
    $stmt->close();

    // Link address to user
    if ($address_id) {
        $stmt = $connection->prepare("UPDATE user SET address_ID = ? WHERE UID = ?");
        $stmt->bind_param('ss', $address_id, $user_id);
        $stmt->execute();
        $stmt->close();
    }
}

// --- Insert payment ---
$payment_token = $session_full->payment_intent; // Stripe PaymentIntent ID
$payment_id = null;

$stmt = $connection->prepare("INSERT INTO payment (token) VALUES (?)");
$stmt->bind_param('s', $payment_token);
$stmt->execute();
$payment_id = $stmt->insert_id;
$stmt->close();

// Link payment to user
if ($payment_id) {
    $stmt = $connection->prepare("UPDATE user SET payment_ID = ? WHERE UID = ?");
    $stmt->bind_param('ss', $payment_id, $user_id);
    $stmt->execute();
    $stmt->close();
}

// --- Generate a single order hash for this checkout ---
$order_hash = hash('sha256', $user_id . json_encode($cart) . time() . bin2hex(random_bytes(8)));

// --- Insert each item as an order ---
foreach ($cart as $item) {
    $stmt = $connection->prepare("
        INSERT INTO order_table
        (user_id, item_id, size_id, colour_id, item_qty, order_price, order_status_id, order_hash, address_id, payment_id)
        VALUES (?, ?, ?, ?, ?, ?, 1, ?, ?, ?)
    ");

    $item_id = (int)$item['iid'];
    $size_id = (int)$item['size'];
    $colour_id = (int)$item['colour'];
    $qty     = (int)$item['qty'];
    $price   = (float)$item['price'];

    $stmt->bind_param(
        'ssssidsss',
        $user_id,    // string UUID
        $item_id,
        $size_id,
        $colour_id,
        $qty,
        $price,
        $order_hash,
        $address_id,
        $payment_id
    );

    $stmt->execute();
    $stmt->close();
}

// Respond 200 to Stripe
http_response_code(200);
?>
