<?php
// local_order_insert.php
// Fallback: insert order locally when webhook DB ≠ website DB

if (!isset($_SESSION)) {
    session_start();
}

// dupe prevention
if (!empty($_SESSION['orders_inserted'])) {
    return;
}
$_SESSION['orders_inserted'] = true;

// session data
$user_id = $_SESSION['user_id'] ?? null;
$cart    = $_SESSION['cart'] ?? [];
$cs      = $_GET['cs'] ?? null;

if (!$user_id || !$cart || !$cs) {
    return;
}

// pay
$stmt = $connection->prepare("INSERT INTO payment (token) VALUES (?)");
$stmt->bind_param('s', $cs);
$stmt->execute();
$payment_id = $stmt->insert_id;
$stmt->close();

// addr
$address_id = null;
if (!empty($_SESSION['shipping_address'])) {
    $a = $_SESSION['shipping_address'];

    $stmt = $connection->prepare("
        INSERT INTO address (address_line_1, address_line_2, city, country, ZIP_code)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param(
        'sssss',
        $a['line1'],
        $a['line2'],
        $a['city'],
        $a['country'],
        $a['zip']
    );
    $stmt->execute();
    $address_id = $stmt->insert_id;
    $stmt->close();
}

// hash
$order_hash = hash(
    'sha256',
    $user_id . json_encode($cart) . microtime(true)
);

// insert
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
    $price     = (float)$item['price'];

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
