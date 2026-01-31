<?php
// edit_order.php

require_once 'bootstrap.php';
require_once 'auth_guard.php';

// Ensure CSRF token exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Get OID from URL
$OID = isset($_GET['OID']) ? (int)$_GET['OID'] : 0;
$order = null;

if ($OID > 0) {
    $stmt = $conn->prepare("
        SELECT 
            o.OID, o.user_id, o.order_status_id, o.item_id, o.colour_id, o.size_id,
            o.payment_id, o.address_id, o.item_qty, o.order_time, o.order_hash, o.order_price,
            u.username, i.name AS item_name, os.order_status,
            c.name AS colour_name, s.size AS size_name,
            a.address_line_1, a.address_line_2, a.city, a.country
        FROM order_table o
        JOIN user u ON o.user_id = u.UID
        JOIN item i ON o.item_id = i.IID
        JOIN order_stat os ON o.order_status_id = os.OSID
        JOIN colour c ON o.colour_id = c.CID
        JOIN size s ON o.size_id = s.SID
        JOIN address a ON o.address_id = a.AID
        WHERE o.OID = ?
    ");
    $stmt->bind_param("i", $OID);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// Redirect if not found
if (!$order) {
    $_SESSION['action_status'] = [
        'type' => 'error',
        'message' => 'Order not found.'
    ];
    header("Location: dashboard_orders.php");
    exit();
}

// Dropdown data
$statuses  = $conn->query("SELECT OSID, order_status FROM order_stat ORDER BY OSID ASC");
$items     = $conn->query("SELECT IID, name FROM item ORDER BY name ASC");
$colours   = $conn->query("SELECT CID, name FROM colour ORDER BY name ASC");
$sizes     = $conn->query("SELECT SID, size FROM size ORDER BY SID ASC");
$users     = $conn->query("SELECT UID, username FROM user ORDER BY username ASC");
$payments  = $conn->query("SELECT PID, token FROM payment ORDER BY PID ASC");

// Build readable address for dropdown
$addresses = $conn->query("
    SELECT 
        AID, 
        CONCAT(address_line_1, ', ', address_line_2, ', ', city, ', ', country) AS address_display
    FROM address
    ORDER BY AID ASC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Order - Store Dashboard</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/add_edit_item.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <main class="main-content">
        <header class="topbar">
            <div class="topbar-left">
                <button class="back-btn" onclick="window.location.href='dashboard_orders.php'">← Back</button>
                <h1>Edit Order #<?= htmlspecialchars($order['OID']); ?></h1>
            </div>
        </header>

        <div class="dashboard-content">
            <div class="form-container">
                <div class="form-card">
                    <div class="form-header">
                        <h2>Edit Order Information</h2>
                        <p>Update the details below to modify this order.</p>
                    </div>

                    <form method="POST" action="process_files/process_edit_order.php" class="edit-item-form">
                        <!-- CSRF Protection -->
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="OID" value="<?= htmlspecialchars($order['OID']); ?>">

                        <!-- User + Order Status -->
                        <div class="form-row">
                            <div class="form-group">
                                <label>Customer <span class="required">*</span></label>
                                <select name="user_id" required>
                                    <option value="">Select customer</option>
                                    <?php while ($user = $users->fetch_assoc()): ?>
                                        <option value="<?= $user['UID']; ?>" <?= ($order['user_id'] == $user['UID']) ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars($user['username']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Order Status <span class="required">*</span></label>
                                <select name="order_status_id" required>
                                    <?php while ($status = $statuses->fetch_assoc()): ?>
                                        <option value="<?= $status['OSID']; ?>" <?= ($order['order_status_id'] == $status['OSID']) ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars(ucfirst($status['order_status'])); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Item + Quantity -->
                        <div class="form-row">
                            <div class="form-group">
                                <label>Product <span class="required">*</span></label>
                                <select name="item_id" required>
                                    <?php while ($item = $items->fetch_assoc()): ?>
                                        <option value="<?= $item['IID']; ?>" <?= ($order['item_id'] == $item['IID']) ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars($item['name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Quantity <span class="required">*</span></label>
                                <input type="number" name="item_qty" value="<?= htmlspecialchars($order['item_qty']); ?>" min="1" required>
                            </div>
                        </div>

                        <!-- Colour + Size -->
                        <div class="form-row">
                            <div class="form-group">
                                <label>Colour <span class="required">*</span></label>
                                <select name="colour_id" required>
                                    <?php while ($colour = $colours->fetch_assoc()): ?>
                                        <option value="<?= $colour['CID']; ?>" <?= ($order['colour_id'] == $colour['CID']) ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars($colour['name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Size <span class="required">*</span></label>
                                <select name="size_id" required>
                                    <?php while ($size = $sizes->fetch_assoc()): ?>
                                        <option value="<?= $size['SID']; ?>" <?= ($order['size_id'] == $size['SID']) ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars($size['size']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Payment + Address -->
                        <div class="form-row">
                            <div class="form-group">
                                <label>Payment <span class="required">*</span></label>
                                <select name="payment_id" required>
                                    <?php while ($payment = $payments->fetch_assoc()): ?>
                                        <option value="<?= $payment['PID']; ?>" <?= ($order['payment_id'] == $payment['PID']) ? 'selected' : ''; ?>>
                                            Payment #<?= $payment['PID']; ?> (<?= htmlspecialchars($payment['token']); ?>)
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Shipping Address <span class="required">*</span></label>
                                <select name="address_id" required>
                                    <?php while ($address = $addresses->fetch_assoc()): ?>
                                        <option value="<?= $address['AID']; ?>" <?= ($order['address_id'] == $address['AID']) ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars($address['address_display']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Price -->
                        <div class="form-row">
                            <div class="form-group full-width">
                                <label>Order Price (USD) <span class="required">*</span></label>
                                <div class=
