<?php

require_once 'db.php';


$user_id = $_SESSION['user_id'] ?? null;
$cart    = $_SESSION['cart'] ?? null;

if (!$user_id || !is_array($cart) || empty($cart)) {
    die('Missing user or cart');
}

// simulated addr data
$line1   = '123 Local Test St';
$line2   = '';
$city    = 'Test City';
$country = 'AU';
$zip     = '0000';

$stmt = $connection->prepare("
    INSERT INTO address (address_line_1, address_line_2, city, country, ZIP_code)
    VALUES (?, ?, ?, ?, ?)
");
$stmt->bind_param('sssss', $line1, $line2, $city, $country, $zip);
$stmt->execute();

$address_id = $stmt->insert_id;
$stmt->close();

// simulated payment data
$fake_token = 'LOCAL_PAYMENT_' . uniqid();
$stmt = $connection->prepare("INSERT INTO payment (token) VALUES (?)");
$stmt->bind_param('s', $fake_token);
$stmt->execute();
$payment_id = $stmt->insert_id;
$stmt->close();


$order_hash = hash(
    'sha256',
    $user_id . json_encode($cart) . microtime(true)
);

$CS = 'LOCAL_SIMULATION_' . uniqid();


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
    $size_id   = (int)($item['size'] ?? 0);
    $colour_id = (int)($item['colour'] ?? 0);
    $qty       = max(1, (int)($item['qty'] ?? 1));

    // fetch trusted price
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
        continue;
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
        $CS,            
        $address_id,
        $payment_id
    );


    $stmt->execute();
}

$stmt->close();
