<?php
// order_details.php

require_once 'bootstrap.php';
require_once 'auth_guard.php';

$OID = $_GET['OID'] ?? null;

if (!$OID) {
    $_SESSION['action_status'] = [
        'type' => 'error',
        'message' => 'Invalid order ID.'
    ];
    header("Location: dashboard_orders.php");
    exit;
}

$stmt = $conn->prepare("
    SELECT
        o.OID,
        o.order_time,
        o.order_hash,
        o.item_qty,
        o.order_price,

        os.order_status,

        i.IID,
        i.name AS item_name,

        c.name AS colour_name,
        s.size AS size_name,

        u.username,
        u.email,

        a.street_name,
        a.unit_number,
        a.city,
        a.country,
        a.ZIP_code

    FROM order_table o
    JOIN order_stat os ON o.order_status_id = os.OSID
    JOIN item i ON o.item_id = i.IID
    JOIN colour c ON o.colour_id = c.CID
    JOIN size s ON o.size_id = s.SID
    JOIN user u ON o.user_id = u.UID
    JOIN address a ON o.address_id = a.AID
    WHERE o.OID = ?
");

$stmt->bind_param("i", $OID);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    $_SESSION['action_status'] = [
        'type' => 'error',
        'message' => 'Order not found.'
    ];
    header("Location: dashboard_orders.php");
    exit;
}

// Status badge class mapping
$statusClass = match (strtolower($order['order_status'])) {
    'completed' => 'completed',
    'cancelled' => 'cancelled',
    'shipping', 'manufacturing' => 'pending',
    default => 'pending'
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order #<?php echo htmlspecialchars($order['OID']); ?></title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/add_edit_item.css">
</head>
<body>

<?php include 'sidebar.php'; ?>

<main class="main-content">

<header class="topbar">
    <div class="topbar-left">
        <button class="back-btn" onclick="window.location.href='dashboard_orders.php'">← Back</button>
        <h1>Order #<?php echo htmlspecialchars($order['OID']); ?></h1>
    </div>
</header>

<div class="dashboard-content">
<div class="form-container">

<!-- Order Overview -->
<div class="form-card">
    <div class="form-header">
        <h2>Order Overview</h2>
    </div>

    <div class="item-details">
        <div class="detail-row">
            <span class="detail-label">Status:</span>
            <span class="detail-value">
                <span class="status-badge <?php echo $statusClass; ?>">
                    <?php echo htmlspecialchars($order['order_status']); ?>
                </span>
            </span>
        </div>

        <div class="detail-row">
            <span class="detail-label">Order Date:</span>
            <span class="detail-value"><?php echo htmlspecialchars($order['order_time']); ?></span>
        </div>

        <div class="detail-row">
            <span class="detail-label">Order Hash:</span>
            <span class="detail-value"><?php echo htmlspecialchars($order['order_hash']); ?></span>
        </div>

        <div class="detail-row">
            <span class="detail-label">Total Price:</span>
            <span class="detail-value">$<?php echo number_format($order['order_price'], 2); ?></span>
        </div>
    </div>
</div>

<!-- Item Details -->
<div class="form-card">
    <div class="form-header">
        <h2>Item Details</h2>
    </div>

    <div class="item-details">
        <div class="detail-row">
            <span class="detail-label">Item:</span>
            <span class="detail-value"><?php echo htmlspecialchars($order['item_name']); ?></span>
        </div>

        <div class="detail-row">
            <span class="detail-label">Quantity:</span>
            <span class="detail-value"><?php echo $order['item_qty']; ?></span>
        </div>

        <div class="detail-row">
            <span class="detail-label">Colour:</span>
            <span class="detail-value"><?php echo htmlspecialchars($order['colour_name']); ?></span>
        </div>

        <div class="detail-row">
            <span class="detail-label">Size:</span>
            <span class="detail-value"><?php echo htmlspecialchars($order['size_name']); ?></span>
        </div>
    </div>
</div>

<!-- Customer & Address -->
<div class="form-card">
    <div class="form-header">
        <h2>Customer Information</h2>
    </div>

    <div class="item-details">
        <div class="detail-row">
            <span class="detail-label">Customer:</span>
            <span class="detail-value"><?php echo htmlspecialchars($order['username']); ?></span>
        </div>

        <div class="detail-row">
            <span class="detail-label">Email:</span>
            <span class="detail-value"><?php echo htmlspecialchars($order['email']); ?></span>
        </div>

        <div class="detail-row">
            <span class="detail-label">Address:</span>
            <span class="detail-value">
                <?php
                echo htmlspecialchars(
                    "{$order['street_name']} {$order['unit_number']}, 
                     {$order['city']}, {$order['country']} {$order['ZIP_code']}"
                );
                ?>
            </span>
        </div>
    </div>
</div>

</div>
</div>

</main>
</body>
</html>
