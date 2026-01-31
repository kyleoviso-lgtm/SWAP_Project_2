<?php
require_once 'db.php';
session_start();

$user_id = $_SESSION['user_id'] ?? 'LOCAL_USER';

$cart = [
    [
        'iid' => 1,
        'size' => 1,
        'colour' => 1,
        'qty' => 2
    ]
];

// 1️ADDRESS (dummy)
$stmt = $connection->prepare("
    INSERT INTO address (address_line_1, address_line_2, city, country, ZIP_code)
    VALUES ('123 Test St', '', 'Test City', 'AU', '0000')
");
$stmt->execute();
$address_id = $stmt->insert_id;
$stmt->close();

// PAYMENT (dummy)
$fake_token = 'LOCAL_PAYMENT_' . uniqid();
$stmt = $connection->prepare("INSERT INTO payment (token) VALUES (?)");
$stmt->bind_param('s', $fake_token);
$stmt->execute();
$payment_id = $stmt->insert_id;
$stmt->close();

// 3️ORDER HASH
$order_hash = hash('sha256', uniqid('local_', true));
$cs = 'LOCAL_SIMULATION';

// 4INSERT ITEMS
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

    $stmtPrice = $connection->prepare("
        SELECT i.price, s.size_price_multi
        FROM item i
        JOIN size s ON s.SID = ?
        WHERE i.IID = ?
        LIMIT 1
    ");
    $stmtPrice->bind_param('ii', $item['size'], $item['iid']);
    $stmtPrice->execute();
    $priceRow = $stmtPrice->get_result()->fetch_assoc();
    $stmtPrice->close();

    if (!$priceRow) continue;

    $price = (float)$priceRow['price'] * (float)$priceRow['size_price_multi'];

    $stmt->bind_param(
        'siiiidssss',
        $user_id,
        $item['iid'],
        $item['size'],
        $item['colour'],
        $item['qty'],
        $price,
        $order_hash,
        $cs,
        $address_id,
        $payment_id
    );
    $stmt->execute();
}

$stmt->close();

