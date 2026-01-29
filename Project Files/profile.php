<?php
session_start();
//Boot up DB connection + login authentication guard
require_once 'bootstrap.php';
require_once 'auth_guard.php';

$userId = $_SESSION['user_id'];

/* =======================
   FETCH USER INFO
======================= */
$userSql = "
    SELECT 
        u.username,
        u.email,
        r.RoleName,
        us.status_name
    FROM user u
    JOIN roles r ON u.role_ID = r.RID
    JOIN user_stat us ON u.status_ID = us.USID
    WHERE u.UID = ?
";
$stmt = mysqli_prepare($conn, $userSql);
mysqli_stmt_bind_param($stmt, "s", $userId);
mysqli_stmt_execute($stmt);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

/* =======================
   FETCH USER ORDERS
======================= */
$orderSql = "
    SELECT
        o.OID,
        o.order_hash,
        o.order_time,
        o.item_qty,
        o.order_price,
        os.order_status,
        i.name AS item_name,
        c.name AS colour_name,
        s.size
    FROM order_table o
    JOIN order_stat os ON o.order_status_id = os.OSID
    JOIN item i ON o.item_id = i.IID
    JOIN colour c ON o.colour_id = c.CID
    JOIN size s ON o.size_id = s.SID
    WHERE o.user_id = ?
    ORDER BY o.order_time DESC
";
$stmt = mysqli_prepare($conn, $orderSql);
mysqli_stmt_bind_param($stmt, "s", $userId);
mysqli_stmt_execute($stmt);
$orders = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);

/* =======================
   STATS
======================= */
$totalOrders = count($orders);
$totalSpent = 0;
$activeOrders = 0;

foreach ($orders as $o) {
    $totalSpent += $o['order_price'];
    if ($o['order_status'] !== 'Completed' && $o['order_status'] !== 'Cancelled') {
        $activeOrders++;
    }
}

/* =======================
   STATUS BADGE CLASS
======================= */
function statusClass($status) {
    return match(strtolower($status)) {
        'completed' => 'completed',
        'pending' => 'pending',
        'shipping', 'manufacturing' => 'shipped',
        default => 'pending'
    };
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Profile</title>
    <link rel="stylesheet" href="css/profile-styles.css">
</head>
<body>

<div class="main-content">
    <div class="dashboard-content">

        <!-- USER PROFILE -->
        <div class="subsection-container">
            <h3>User Profile</h3>
            <div class="section-separator-minor"></div>

            <p><strong>Username:</strong> <?= htmlspecialchars($user['username']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
            <p><strong>Role:</strong> <?= htmlspecialchars($user['RoleName']) ?></p>
            <p><strong>Status:</strong> <?= htmlspecialchars($user['status_name']) ?></p>
        </div>

        <div class="section-separator"></div>

        <!-- STATS -->
        <div class="stats-container">
            <div class="stat-card">
                <h4>Total Orders</h4>
                <p><?= $totalOrders ?></p>
            </div>
            <div class="stat-card">
                <h4>Total Spent</h4>
                <p>$<?= number_format($totalSpent, 2) ?></p>
            </div>
            <div class="stat-card">
                <h4>Active Orders</h4>
                <p><?= $activeOrders ?></p>
            </div>
        </div>

        <div class="section-separator"></div>

        <!-- ORDER HISTORY -->
        <div class="table-card">
            <div class="header-left">
                <h3>Purchase History</h3>
                <span class="order-count"><?= $totalOrders ?> orders</span>
            </div>

            <div class="data-table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Item</th>
                            <th>Colour</th>
                            <th>Size</th>
                            <th>Qty</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($orders)): ?>
                        <tr>
                            <td colspan="8">No orders found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($orders as $o): ?>
                        <tr>
                            <td><?= htmlspecialchars($o['order_hash']) ?></td>
                            <td><?= htmlspecialchars($o['item_name']) ?></td>
                            <td><?= htmlspecialchars($o['colour_name']) ?></td>
                            <td><?= htmlspecialchars($o['size']) ?></td>
                            <td><?= $o['item_qty'] ?></td>
                            <td>$<?= number_format($o['order_price'], 2) ?></td>
                            <td>
                                <span class="status-badge <?= statusClass($o['order_status']) ?>">
                                    <?= htmlspecialchars($o['order_status']) ?>
                                </span>
                            </td>
                            <td><?= date("d M Y", strtotime($o['order_time'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Logout button -->
        <a href="process_files/process_logout.php">
            <div class="logout-btn">
                <h3 class="logout-btn-text">logout</h3>
            </div>
        </a> 
    </div>
</div>

</body>
</html>
