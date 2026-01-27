<!DOCTYPE html>
<html lang="en">

<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mydb";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch active orders (order_status_id != completed/cancelled, adjust based on your status IDs)
$active_sql = "SELECT OID, user_id, order_status_id, item_id, colour_id, size_id, payment_id, 
               address_id, item_qty, order_time, order_hash, order_price 
               FROM order_table 
               WHERE order_status_id IN (1, 2, 3)
               ORDER BY order_time DESC";
$active_result = $conn->query($active_sql);

// Fetch order history (completed/cancelled orders)
$history_sql = "SELECT OID, user_id, order_status_id, item_id, colour_id, size_id, payment_id, 
                address_id, item_qty, order_time, order_hash, order_price 
                FROM order_table
                WHERE order_status_id IN (4, 5)
                ORDER BY order_time DESC";
$history_result = $conn->query($history_sql);

// Function to get status badge class and text
function getOrderStatus($status_id) {
    switch($status_id) {
        case 1: return ['class' => 'pending', 'text' => 'Pending'];
        case 2: return ['class' => 'processing', 'text' => 'Manufacturing'];
        case 3: return ['class' => 'processing', 'text' => 'Shipping'];
        case 4: return ['class' => 'completed', 'text' => 'Completed'];
        case 5: return ['class' => 'cancelled', 'text' => 'Cancelled'];
        default: return ['class' => 'pending', 'text' => 'Unknown'];
    }
}
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Store Dashboard</title>
    <link rel="stylesheet" href="css/dashboard.css">
</head>

<body>
    <!-- Sidebar Navigation -->
    <?php include 'sidebar.php'; ?>

    <!-- Main Content -->
    <main class="main-content">
        <header class="topbar">
            <div class="topbar-left">
                <h1>Order Tracking</h1>
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

        <div class="dashboard-content">
            
            <!-- Active Orders Section -->
            <div class="orders-section">
                <div class="subsection-container">
                    <div class="section-header">
                        <div class="header-left">
                            <h2>Active Orders</h2>
                            <span class="order-count"><?php echo $active_result->num_rows; ?> orders</span>
                        </div>
                        <div class="active-order-header-actions">
                            <input type="text" class="order-tracking-search-bar" placeholder="Search by Order ID or Hash" id="activeSearchBar">
                            <button class="btn-refresh" onclick="location.reload()">🔄 Refresh</button>
                        </div>
                    </div>

                    <div class="data-table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Order Hash</th>
                                    <th>User ID</th>
                                    <th>Item ID</th>
                                    <th>Quantity</th>
                                    <th>Colour</th>
                                    <th>Size</th>
                                    <th>Price</th>
                                    <th>Status</th>
                                    <th>Order Time</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="activeOrdersBody">
                                <?php
                                if ($active_result->num_rows > 0) {
                                    while ($row = $active_result->fetch_assoc()) {
                                        $status = getOrderStatus($row['order_status_id']);
                                        echo "<tr>";
                                        echo "<td><strong>#" . htmlspecialchars($row['OID']) . "</strong></td>";
                                        echo "<td><code class='order-hash'>" . htmlspecialchars(substr($row['order_hash'], 0, 12)) . "...</code></td>";
                                        echo "<td>" . htmlspecialchars($row['user_id']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['item_id']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['item_qty']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['colour_id']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['size_id']) . "</td>";
                                        echo "<td>$" . number_format($row['order_price'], 2) . "</td>";
                                        echo "<td><span class='status-badge " . $status['class'] . "'>" . $status['text'] . "</span></td>";
                                        echo "<td>" . date('M d, Y H:i', strtotime($row['order_time'])) . "</td>";
                                        echo "<td class='action-btn-cell'>
                                                <button class='view-btn' onclick='viewOrder(" . $row['OID'] . ")'>View</button>
                                            </td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='11' class='no-data'>No active orders</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="section-separator"></div>

            <!-- Order History Section -->
            <div class="subsection-container">
                <div class="orders-section">
                    <div class="section-header">
                        <div class="header-left">
                            <h2>Order History</h2>
                            <span class="order-count"><?php echo $history_result->num_rows; ?> orders</span>
                        </div>
                        <div class="order-history-header-actions">
                            <input type="text" class="order-tracking-search-bar" placeholder="Search by Order ID or Hash" id="historySearchBar">
                            <select class="filter-select">
                                <option value="all">All Status</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>

                    <div class="data-table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Order Hash</th>
                                    <th>User ID</th>
                                    <th>Item ID</th>
                                    <th>Quantity</th>
                                    <th>Colour</th>
                                    <th>Size</th>
                                    <th>Price</th>
                                    <th>Status</th>
                                    <th>Order Time</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="historyOrdersBody">
                                <?php
                                if ($history_result->num_rows > 0) {
                                    while ($row = $history_result->fetch_assoc()) {
                                        $status = getOrderStatus($row['order_status_id']);
                                        echo "<tr>";
                                        echo "<td><strong>#" . htmlspecialchars($row['OID']) . "</strong></td>";
                                        echo "<td><code class='order-hash'>" . htmlspecialchars(substr($row['order_hash'], 0, 12)) . "...</code></td>";
                                        echo "<td>" . htmlspecialchars($row['user_id']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['item_id']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['item_qty']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['colour_id']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['size_id']) . "</td>";
                                        echo "<td>$" . number_format($row['order_price'], 2) . "</td>";
                                        echo "<td><span class='status-badge " . $status['class'] . "'>" . $status['text'] . "</span></td>";
                                        echo "<td>" . date('M d, Y H:i', strtotime($row['order_time'])) . "</td>";
                                        echo "<td class='action-btn-cell'>
                                                <button class='view-btn' onclick='viewOrder(" . $row['OID'] . ")'>View</button>
                                            </td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='11' class='no-data'>No order history</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    
    <script>
        // Search functionality for active orders
        document.getElementById('activeSearchBar').addEventListener('input', function() {
            const filter = this.value.toLowerCase();
            const tbody = document.getElementById('activeOrdersBody');
            const rows = tbody.getElementsByTagName('tr');

            for (let row of rows) {
                if (row.classList.contains('no-data')) continue;
                
                const orderId = row.cells[0].textContent.toLowerCase();
                const orderHash = row.cells[1].textContent.toLowerCase();
                
                if (orderId.includes(filter) || orderHash.includes(filter)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
        });

        // Search functionality for order history
        document.getElementById('historySearchBar').addEventListener('input', function() {
            const filter = this.value.toLowerCase();
            const tbody = document.getElementById('historyOrdersBody');
            const rows = tbody.getElementsByTagName('tr');

            for (let row of rows) {
                if (row.classList.contains('no-data')) continue;
                
                const orderId = row.cells[0].textContent.toLowerCase();
                const orderHash = row.cells[1].textContent.toLowerCase();
                
                if (orderId.includes(filter) || orderHash.includes(filter)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
        });

        // View order details function
        function viewOrder(orderId) {
            window.location.href = 'order_details.php?OID=' + orderId;
        }
    </script>

    </main>

</body>
</html>