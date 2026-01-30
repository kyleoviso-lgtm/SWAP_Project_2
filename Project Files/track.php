<?php
session_start();
require_once 'db.php';

// Require login
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login_page.php');
    exit;
}

$user_id = $_SESSION['user_id'] ?? 0;

// Initialize empty orders array
$orders = [];
$order_hash = trim($_GET['order_hash'] ?? '');

if ($order_hash !== '') {
    // Secure query to fetch order by hash for this user
    $sql = "
    SELECT 
        o.order_hash,
        o.order_time,
        o.item_qty,
        o.order_price,
        os.order_status,
        i.name AS item_name,
        i.description AS item_desc,
        c.name AS color_name,
        s.size AS size_name
    FROM order_table o
    LEFT JOIN item i ON o.item_id = i.IID
    LEFT JOIN colour c ON o.colour_id = c.CID
    LEFT JOIN size s ON o.size_id = s.SID
    LEFT JOIN order_stat os ON o.order_status_id = os.OSID
    WHERE o.user_id = ? AND o.order_hash = ?
    ";

    $stmt = $connection->prepare($sql);
    $stmt->bind_param("is", $user_id, $order_hash);
    $stmt->execute();
    $result = $stmt->get_result();
    $orders = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Track Orders</title>
<link rel="stylesheet" href="css/track.css">
</head>
<body>
<div class="track-container">
    <h1>Track Your Orders</h1>

    <form method="get" class="search-form">
        <input type="text" name="order_hash" placeholder="Enter Order Hash" value="<?= htmlspecialchars($order_hash) ?>">
        <button type="submit">Search</button>
    </form>

    <?php if ($order_hash !== ''): ?>
        <?php if (empty($orders)) : ?>
            <p class="no-orders">No orders found for this hash.</p>
        <?php else : ?>
            <div class="orders-grid">
                <?php foreach ($orders as $order) : 
                    $price_per_item = $order['item_qty'] > 0 ? $order['order_price'] / $order['item_qty'] : $order['order_price'];
                ?>
                <div class="order-card">
                    <div class="order-header">
                        <span class="order-hash">#<?= htmlspecialchars($order['order_hash']) ?></span>
                        <span class="order-time"><?= date("d M Y H:i", strtotime($order['order_time'])) ?></span>
                    </div>
                    <div class="order-info">
                        <p><strong>Item:</strong> <?= htmlspecialchars($order['item_name']) ?></p>
                        <p><strong>Description:</strong> <?= htmlspecialchars($order['item_desc']) ?></p>
                        <p><strong>Color:</strong> <?= htmlspecialchars($order['color_name']) ?></p>
                        <p><strong>Size:</strong> <?= htmlspecialchars($order['size_name']) ?></p>
                        <p><strong>Qty:</strong> <?= $order['item_qty'] ?></p>
                        <p><strong>Price per item:</strong> $<?= number_format($price_per_item, 2) ?></p>
                        <p><strong>Total Price:</strong> $<?= number_format($order['order_price'], 2) ?></p>
                        <p><strong>Status:</strong> <?= htmlspecialchars($order['order_status']) ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
</body>
</html>
