<?php
//Boot up DB connection + login authentication guard
require_once 'bootstrap.php';
require_once 'auth_guard.php';

// Fetch recent orders (latest 5)
$recent_sql = "
SELECT 
    o.OID,
    o.user_id,
    o.item_id,
    o.item_qty,
    o.order_price,
    o.order_status_id,
    o.order_time,
    i.name AS item_name
FROM order_table o
JOIN item i ON o.item_id = i.IID
ORDER BY o.order_time DESC
LIMIT 5
";

$recent_result = $conn->query($recent_sql);

function getOrderStatus($status_id) {
    switch ($status_id) {
        case 1: return ['class' => 'pending', 'text' => 'Pending'];
        case 2: return ['class' => 'processing', 'text' => 'Manufacturing'];
        case 3: return ['class' => 'processing', 'text' => 'Shipping'];
        case 4: return ['class' => 'completed', 'text' => 'Completed'];
        case 5: return ['class' => 'cancelled', 'text' => 'Cancelled'];
        default: return ['class' => 'pending', 'text' => 'Unknown'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Store Dashboard</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/visualization.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>
<body>
    <!-- Sidebar Navigation -->
    <?php include 'sidebar.php'; ?>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Top Bar -->
        <header class="topbar">
            <div class="topbar-left">
                <h1>Dashboard Overview</h1>
            </div>
            <div class="topbar-right">
                <button class="icon-btn">
                    <span>🔔</span>
                    <span class="badge">3</span>
                </button>
                <button class="icon-btn">
                    <span>⚙️</span>
                </button>
            </div>
        </header>

        <!-- Dashboard Content -->
        <div class="dashboard-content">
            
            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-icon revenue">💵</span>
                        <span class="stat-trend positive">+12.5%</span>
                    </div>
                    <div class="stat-value">$24,563</div>
                    <div class="stat-label">Total Revenue</div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-icon orders">📦</span>
                        <span class="stat-trend positive">+8.2%</span>
                    </div>
                    <div class="stat-value">342</div>
                    <div class="stat-label">Orders</div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-icon customers">👥</span>
                        <span class="stat-trend positive">+18.4%</span>
                    </div>
                    <div class="stat-value">1,429</div>
                    <div class="stat-label">Customers</div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-icon products">📊</span>
                        <span class="stat-trend negative">-2.1%</span>
                    </div>
                    <div class="stat-value">89</div>
                    <div class="stat-label">Products</div>
                </div>
            </div>

            <div class="section-separator"></div>

            <!-- Charts Section -->
            <div class="charts-row">
                <div class="chart-card">
                    <div class="card-header">
                        <h3>Sales Overview</h3>
                    </div>
                    <div class="chart-placeholder">
                        <?php include 'visualization_element.php'; ?>
                    </div>
                </div>

                <div class="chart-card small">
                    <div class="card-header">
                        <h3>Top Categories</h3>
                    </div>
                    <div class="category-list">
                        <div class="category-item">
                            <div class="category-info">
                                <span class="category-name">Electronics</span>
                                <span class="category-value">$8,240</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 75%"></div>
                            </div>
                        </div>
                        <div class="category-item">
                            <div class="category-info">
                                <span class="category-name">Clothing</span>
                                <span class="category-value">$6,120</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 55%"></div>
                            </div>
                        </div>
                        <div class="category-item">
                            <div class="category-info">
                                <span class="category-name">Home & Garden</span>
                                <span class="category-value">$4,890</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 45%"></div>
                            </div>
                        </div>
                        <div class="category-item">
                            <div class="category-info">
                                <span class="category-name">Sports</span>
                                <span class="category-value">$3,210</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 30%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="section-separator"></div>

            <!-- Recent Orders -->
            <div class="table-card">
                <div class="card-header">
                    <h3>Recent Orders</h3>
                    <a href="dashboard_orders.php" class="btn-secondary">View All</a>
                </div>

                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Product</th>
                                <th>Qty</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($recent_result->num_rows > 0) {
                                while ($row = $recent_result->fetch_assoc()) {
                                    $status = getOrderStatus($row['order_status_id']);

                                    echo "<tr>";
                                    echo "<td><strong>#ORD-" . htmlspecialchars($row['OID']) . "</strong></td>";
                                    echo "<td>User " . htmlspecialchars($row['user_id']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['item_name']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['item_qty']) . "</td>";
                                    echo "<td>$" . number_format($row['order_price'], 2) . "</td>";
                                    echo "<td>
                                            <span class='status-badge {$status['class']}'>
                                                {$status['text']}
                                            </span>
                                        </td>";
                                    echo "<td>" . date('M d, Y', strtotime($row['order_time'])) . "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr>
                                        <td colspan='7' class='no-data'>
                                            No recent orders found
                                        </td>
                                    </tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>
</body>
</html>

