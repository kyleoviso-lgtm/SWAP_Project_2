<?php
session_start();
require_once 'db.php';

// Require login
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login_page.php');
    exit;
}

$user_id = $_SESSION['user_id'] ?? null;
$order_hash = trim($_GET['order_hash'] ?? '');
$orders = [];

if ($user_id && $order_hash !== '') {
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
        JOIN item i ON o.item_id = i.IID
        JOIN colour c ON o.colour_id = c.CID
        JOIN size s ON o.size_id = s.SID
        JOIN order_stat os ON o.order_status_id = os.OSID
        WHERE o.user_id = ?
          AND o.order_hash = ?
        ORDER BY o.OID ASC
    ";

    $stmt = $connection->prepare($sql);
    $stmt->bind_param('ss', $user_id, $order_hash);
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
<title>Track Order</title>
<link rel="stylesheet" href="css/track.css">
</head>
<body>

<div class="track-container">
    <h1>Track Your Order</h1>

    <form method="get" class="search-form">
        <input
            type="text"
            name="order_hash"
            placeholder="Enter Order Hash"
            value="<?= htmlspecialchars($order_hash) ?>"
            required
        >
        <button type="submit">Track</button>
    </form>

<?php if ($order_hash !== ''): ?>

    <?php if (!$orders): ?>
        <p class="no-orders">No order found for this reference.</p>
    <?php else: ?>

        <?php
            // Header data (same for all rows)
            $orderTime = $orders[0]['order_time'];
            $status = $orders[0]['order_status'];
            $total = 0;
            foreach ($orders as $o) {
                $total += $o['order_price'] * $o['item_qty'];
            }

        ?>

        <!-- ORDER SUMMARY -->
        <div class="order-summary">
            <p><strong>Order Hash:</strong> <?= htmlspecialchars($order_hash) ?></p>
            <p><strong>Date:</strong> <?= date("d M Y H:i", strtotime($orderTime)) ?></p>
            <p><strong>Status:</strong> <?= htmlspecialchars($status) ?></p>
            <p><strong>Total:</strong> $<?= number_format($total, 2) ?></p>
        </div>

        <!-- LINE ITEMS -->
        <div class="orders-grid">
            <?php foreach ($orders as $order): ?>
                <div class="order-card">
                    <p><strong>Item:</strong> <?= htmlspecialchars($order['item_name']) ?></p>
                    <p><?= htmlspecialchars($order['item_desc']) ?></p>
                    <p><strong>Color:</strong> <?= htmlspecialchars($order['color_name']) ?></p>
                    <p><strong>Size:</strong> <?= htmlspecialchars($order['size_name']) ?></p>
                    <p><strong>Qty:</strong> <?= (int)$order['item_qty'] ?></p>
                    <p><strong>Line Total:</strong>
                    $<?= number_format($order['order_price'] * $order['item_qty'], 2) ?>
                </p>

                </div>
            <?php endforeach; ?>
        </div>

    <?php endif; ?>

<?php endif; ?>

</div>
</body>
</html>
