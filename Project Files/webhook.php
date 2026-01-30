<?php
require_once 'db.php';
require_once 'stripe-php-19.3.0/init.php';

\Stripe\Stripe::setApiKey('sk_test_51SsJVhD22va0abhHFZLNVHNvDjFXt2roZqcP0RAdvvszNkAfLp6FfWmgyM04uR8kumj4zDAQx93RYXg8TBh68eM0005th24rsC');

$endpoint_secret = 'whsec_ds9NLrXrXWoGpxNuDjMOC3ceRe7gSfZd';

// Read payload
$payload = file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

try {
    $event = \Stripe\Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
} catch (\Throwable $e) {
    http_response_code(400);
    exit;
}

// Only handle completed checkouts
if ($event->type !== 'checkout.session.completed') {
    http_response_code(200);
    exit;
}

$session = $event->data->object;

// Expand payment_intent
$session_full = \Stripe\Checkout\Session::retrieve(
    $session->id,
    ['expand' => ['payment_intent']]
);

// Get metadata
$user_id = (int)($session_full->metadata->user_id ?? 0);
$cart_json = $session_full->metadata->cart ?? '';

if ($user_id <= 0 || empty($cart_json)) {
    http_response_code(200);
    exit;
}

$cart = json_decode($cart_json, true);
if (!is_array($cart)) {
    http_response_code(200);
    exit;
}

/* -------------------------------
   1. Insert payment record
--------------------------------*/
$payment_token = $session_full->payment_intent; // string ID from Stripe
$payment_id = null;

$stmt = $connection->prepare("INSERT INTO payment (token) VALUES (?)");
$stmt->bind_param('s', $payment_token);
if ($stmt->execute()) {
    $payment_id = $stmt->insert_id;
}
$stmt->close();

/* -------------------------------
   2. Insert shipping address
--------------------------------*/
$address_id = null;
$shipping = $session_full->shipping ?? $session_full->customer_details ?? null;

$line1 = $line2 = $city = $country = $zip_code = '';

if ($shipping && isset($shipping->address)) {
    $line1    = $shipping->address->line1 ?? '';
    $line2    = $shipping->address->line2 ?? '';
    $city     = $shipping->address->city ?? '';
    $country  = $shipping->address->country ?? '';
    $zip_code = $shipping->address->postal_code ?? '';
}

if ($line1 && $country) {
    $stmt = $connection->prepare("
        INSERT INTO address (address_line_1, address_line_2, city, country, ZIP_code)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param('sssss', $line1, $line2, $city, $country, $zip_code);
    if ($stmt->execute()) {
        $address_id = $stmt->insert_id;
    }
    $stmt->close();
}

/* -------------------------------
   3. Update user with address & payment
--------------------------------*/
if ($address_id || $payment_id) {
    $fields = [];
    $types = '';
    $values = [];

    if ($address_id) {
        $fields[] = 'address_ID = ?';
        $types .= 'i';
        $values[] = $address_id;
    }

    if ($payment_id) {
        $fields[] = 'payment_ID = ?';
        $types .= 'i';
        $values[] = $payment_id;
    }

    $types .= 'i';
    $values[] = $user_id;

    // Make sure your user table has column UID as primary key
    $sql = "UPDATE user SET " . implode(',', $fields) . " WHERE UID = ?";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param($types, ...$values);
    $stmt->execute();
    $stmt->close();
}

/* -------------------------------
   4. Generate single order hash for entire cart
--------------------------------*/
$order_hash = hash('sha256', $user_id . microtime(true) . bin2hex(random_bytes(8)));

/* -------------------------------
   5. Insert each cart item
--------------------------------*/
foreach ($cart as $item) {
    $item_id   = (int)$item['iid'];
    $size_id   = (int)$item['size'];
    $colour_id = (int)$item['colour'];
    $qty       = (int)$item['qty'];
    $price     = (float)$item['price'];

    $stmt = $connection->prepare("
        INSERT INTO order_table
        (user_id, item_id, size_id, colour_id, item_qty, order_price, order_status_id, order_hash, address_id, payment_id)
        VALUES (?, ?, ?, ?, ?, ?, 1, ?, ?, ?)
    ");
    $stmt->bind_param(
        'iiiiidiii',
        $user_id,
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

http_response_code(200);
?>
