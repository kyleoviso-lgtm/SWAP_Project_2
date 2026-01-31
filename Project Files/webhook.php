<?php

session_start();
require_once 'db.php';
require_once 'stripe-php-19.3.0/init.php';
require_once 'key.php';

// get keys (hide fm client side code)
\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

$endpoint_secret = STRIPE_WEBHOOK_SECRET;

// verify webhook
$payload = file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

try {
    $event = \Stripe\Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
} catch (\Throwable $e) {
    http_response_code(400);
    exit('Invalid signature');
}

if ($event->type !== 'checkout.session.completed') {
    http_response_code(200);
    exit;
}

// session
$session = $event->data->object;
$session_full = \Stripe\Checkout\Session::retrieve(
    $session->id,
    ['expand' => ['payment_intent']]
);

// user
$user_id = $session_full->metadata->user_id ?? null;
if (!$user_id) {
    http_response_code(400);
    exit('Missing user');
}

//  cart
$cart = json_decode($session_full->metadata->cart ?? '[]', true);
if (!is_array($cart) || empty($cart)) {
    http_response_code(200);
    exit;
}

// shipping addr
$address_id = null;
$addr = $session_full->customer_details->address ?? null;

if ($addr && !empty($addr->line1) && !empty($addr->country)) {
    $line1   = $addr->line1;
    $line2   = $addr->line2 ?? '';
    $city    = $addr->city ?? '';
    $country = $addr->country;
    $zip     = $addr->postal_code ?? '';

    $stmt = $connection->prepare("
        INSERT INTO address (address_line_1, address_line_2, city, country, ZIP_code)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param('sssss', $line1, $line2, $city, $country, $zip);
    $stmt->execute();
    $address_id = $stmt->insert_id;
    $stmt->close();
}

// payment
$payment_id = null;
$payment_token = $session_full->payment_intent;

$stmt = $connection->prepare("INSERT INTO payment (token) VALUES (?)");
$stmt->bind_param('s', $payment_token);
$stmt->execute();
$payment_id = $stmt->insert_id;
$stmt->close();

// order hash
$order_hash = hash(
    'sha256',
    $user_id . json_encode($cart) . microtime(true) . bin2hex(random_bytes(8))
);

$cs = $session_full->id; // 1 checkout session id for all rows

// insert items
$stmt = $connection->prepare("
    INSERT INTO order_table
    (
        user_id,
        item_id,
        size_id,
        colour_id,
        item_qty,
        order_price,
        order_status_id,
        order_hash,
        CS,
        address_id,
        payment_id
    )
    VALUES (?, ?, ?, ?, ?, ?, 1, ?, ?, ?, ?)
");

foreach ($cart as $item) {
    $item_id   = (int)$item['iid'];
    $size_id   = (int)$item['size'];
    $colour_id = (int)$item['colour'];
    $qty       = (int)$item['qty'];
        
    // fetch real price from db
    $stmtPrice = $connection->prepare("
        SELECT i.price, s.size_price_multi
        FROM item i
        JOIN size s ON s.SID = ?
        WHERE i.IID = ?
        LIMIT 1
    ");
    $stmtPrice->bind_param('ii', $size_id, $item_id);
    $stmtPrice->execute();
    $priceRow = $stmtPrice->get_result()->fetch_assoc();
    $stmtPrice->close();

    if (!$priceRow) {
        continue; // skip invalid item
    }

    $price = (float)$priceRow['price'] * (float)$priceRow['size_price_multi'];



    $stmt->bind_param(
        'siiiidssss',
        $user_id,
        $item_id,
        $size_id,
        $colour_id,
        $qty,
        $price,
        $order_hash,
        $cs,
        $address_id,
        $payment_id
    );

    $stmt->execute();
}

$stmt->close();
http_response_code(200);
