<?php
// edit_order.php

// Boot up DB connection + login authentication guard
require_once 'bootstrap.php';
require_once 'auth_guard.php';

// Get OID from URL for display/edit mode
$OID = isset($_GET['OID']) ? $_GET['OID'] : '';
$order = null;

if ($OID) {
    $stmt = $conn->prepare("
        SELECT o.OID, o.user_id, o.order_status_id, o.item_id, o.colour_id, o.size_id, 
               o.payment_id, o.address_id, o.item_qty, o.order_time, o.order_hash, o.order_price,
               u.username, i.name as item_name, os.order_status, 
               c.name as colour_name, s.size as size_name,
               a.street_name, a.city, a.country
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
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();
    $stmt->close();
}

// If order not found, redirect back
if (!$order) {
    $_SESSION['action_status'] = [
        'type' => 'error',
        'message' => 'Order not found.'
    ];
    header("Location: dashboard_orders.php");
    exit();
}

// Fetch order statuses for dropdown
$statuses = $conn->query("SELECT OSID, order_status FROM order_stat ORDER BY OSID ASC");

// Fetch items for dropdown
$items = $conn->query("SELECT IID, name FROM item ORDER BY name ASC");

// Fetch colours for dropdown
$colours = $conn->query("SELECT CID, name FROM colour ORDER BY name ASC");

// Fetch sizes for dropdown
$sizes = $conn->query("SELECT SID, size FROM size ORDER BY SID ASC");

// Fetch users for dropdown
$users = $conn->query("SELECT UID, username FROM user ORDER BY username ASC");

// Fetch payments for dropdown
$payments = $conn->query("SELECT PID, token FROM payment ORDER BY PID ASC");

// Fetch addresses for dropdown
$addresses = $conn->query("SELECT AID, CONCAT(street_name, ', ', city, ', ', country) as address_display FROM address ORDER BY AID ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Order - Store Dashboard</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/add_edit_item.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <main class="main-content">
        <header class="topbar">
            <div class="topbar-left">
                <button class="back-btn" onclick="window.location.href='dashboard_orders.php'">
                    ← Back
                </button>
                <h1>Edit Order #<?php echo htmlspecialchars($order['OID']); ?></h1>
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
            <div class="form-container">
                <div class="form-card">
                    <div class="form-header">
                        <h2>Edit Order Information</h2>
                        <p>Update the details below to modify this order</p>
                    </div>

                    <form method="POST" action="process_files/process_edit_order.php" class="edit-item-form">
                        <input type="hidden" name="OID" value="<?php echo htmlspecialchars($order['OID']); ?>">

                        <!-- User and Order Status Row -->
                        <div class="form-row">
                            <div class="form-group">
                                <label for="user_id">Customer <span class="required">*</span></label>
                                <select id="user_id" name="user_id" required>
                                    <option value="" disabled>Select customer</option>
                                    <?php
                                    if ($users && $users->num_rows > 0) {
                                        while ($user = $users->fetch_assoc()) {
                                            $selected = ($order['user_id'] == $user['UID']) ? 'selected' : '';
                                            echo "<option value='" . htmlspecialchars($user['UID']) . "' $selected>" 
                                                 . htmlspecialchars($user['username']) 
                                                 . "</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="order_status_id">Order Status <span class="required">*</span></label>
                                <select id="order_status_id" name="order_status_id" required>
                                    <option value="" disabled>Select status</option>
                                    <?php
                                    if ($statuses && $statuses->num_rows > 0) {
                                        while ($status = $statuses->fetch_assoc()) {
                                            $selected = ($order['order_status_id'] == $status['OSID']) ? 'selected' : '';
                                            echo "<option value='" . htmlspecialchars($status['OSID']) . "' $selected>" 
                                                 . htmlspecialchars(ucfirst($status['order_status'])) 
                                                 . "</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <!-- Item and Quantity Row -->
                        <div class="form-row">
                            <div class="form-group">
                                <label for="item_id">Product <span class="required">*</span></label>
                                <select id="item_id" name="item_id" required>
                                    <option value="" disabled>Select product</option>
                                    <?php
                                    if ($items && $items->num_rows > 0) {
                                        while ($item = $items->fetch_assoc()) {
                                            $selected = ($order['item_id'] == $item['IID']) ? 'selected' : '';
                                            echo "<option value='" . htmlspecialchars($item['IID']) . "' $selected>" 
                                                 . htmlspecialchars($item['name']) 
                                                 . "</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="item_qty">Quantity <span class="required">*</span></label>
                                <input 
                                    type="number" 
                                    id="item_qty" 
                                    name="item_qty" 
                                    value="<?php echo htmlspecialchars($order['item_qty']); ?>"
                                    min="1"
                                    placeholder="Enter quantity"
                                    required
                                >
                            </div>
                        </div>

                        <!-- Colour and Size Row -->
                        <div class="form-row">
                            <div class="form-group">
                                <label for="colour_id">Colour <span class="required">*</span></label>
                                <select id="colour_id" name="colour_id" required>
                                    <option value="" disabled>Select colour</option>
                                    <?php
                                    if ($colours && $colours->num_rows > 0) {
                                        while ($colour = $colours->fetch_assoc()) {
                                            $selected = ($order['colour_id'] == $colour['CID']) ? 'selected' : '';
                                            echo "<option value='" . htmlspecialchars($colour['CID']) . "' $selected>" 
                                                 . htmlspecialchars($colour['name']) 
                                                 . "</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="size_id">Size <span class="required">*</span></label>
                                <select id="size_id" name="size_id" required>
                                    <option value="" disabled>Select size</option>
                                    <?php
                                    if ($sizes && $sizes->num_rows > 0) {
                                        while ($size = $sizes->fetch_assoc()) {
                                            $selected = ($order['size_id'] == $size['SID']) ? 'selected' : '';
                                            echo "<option value='" . htmlspecialchars($size['SID']) . "' $selected>" 
                                                 . htmlspecialchars($size['size']) 
                                                 . "</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <!-- Payment and Address Row -->
                        <div class="form-row">
                            <div class="form-group">
                                <label for="payment_id">Payment Method <span class="required">*</span></label>
                                <select id="payment_id" name="payment_id" required>
                                    <option value="" disabled>Select payment</option>
                                    <?php
                                    if ($payments && $payments->num_rows > 0) {
                                        while ($payment = $payments->fetch_assoc()) {
                                            $selected = ($order['payment_id'] == $payment['PID']) ? 'selected' : '';
                                            echo "<option value='" . htmlspecialchars($payment['PID']) . "' $selected>Payment #" 
                                                 . htmlspecialchars($payment['PID']) 
                                                 . " (Token: " . htmlspecialchars($payment['token']) . ")</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="address_id">Shipping Address <span class="required">*</span></label>
                                <select id="address_id" name="address_id" required>
                                    <option value="" disabled>Select address</option>
                                    <?php
                                    if ($addresses && $addresses->num_rows > 0) {
                                        while ($address = $addresses->fetch_assoc()) {
                                            $selected = ($order['address_id'] == $address['AID']) ? 'selected' : '';
                                            echo "<option value='" . htmlspecialchars($address['AID']) . "' $selected>" 
                                                 . htmlspecialchars($address['address_display']) 
                                                 . "</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <!-- Order Price Row -->
                        <div class="form-row">
                            <div class="form-group full-width">
                                <label for="order_price">Order Price (USD) <span class="required">*</span></label>
                                <div class="input-with-icon">
                                    <span class="input-icon">$</span>
                                    <input 
                                        type="text"
                                        inputmode="decimal"
                                        pattern="[0-9]*\.?[0-9]*"
                                        id="order_price" 
                                        name="order_price" 
                                        value="<?php echo htmlspecialchars($order['order_price']); ?>"
                                        placeholder="0.00"
                                        required
                                    >
                                </div>
                                <span class="helper-text">Total price for this order</span>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="button" class="btn-cancel" onclick="window.location.href='dashboard_orders.php'">
                                Cancel
                            </button>
                            <button type="submit" class="btn-submit">
                                <span class="btn-icon">✓</span>
                                Update Order
                            </button>
                        </div>
                    </form>
                </div>

                <div class="info-card">
                    <div class="info-header">
                        <span class="info-icon">ℹ️</span>
                        <h3>Current Order Details</h3>
                    </div>
                    <div class="item-details">
                        <div class="detail-row">
                            <span class="detail-label">Order ID:</span>
                            <span class="detail-value">#<?php echo htmlspecialchars($order['OID']); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Order Hash:</span>
                            <span class="detail-value"><code><?php echo htmlspecialchars($order['order_hash']); ?></code></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Customer:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($order['username']); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Product:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($order['item_name']); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Quantity:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($order['item_qty']); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Total Price:</span>
                            <span class="detail-value">$<?php echo number_format($order['order_price'], 2); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Status:</span>
                            <span class="detail-value">
                                <?php 
                                $statusClass = '';
                                switch($order['order_status_id']) {
                                    case 1: $statusClass = 'pending'; break;
                                    case 2: 
                                    case 3: $statusClass = 'processing'; break;
                                    case 4: $statusClass = 'completed'; break;
                                    case 5: $statusClass = 'cancelled'; break;
                                }
                                ?>
                                <span class="status-badge <?php echo $statusClass; ?>">
                                    <?php echo htmlspecialchars(ucfirst($order['order_status'])); ?>
                                </span>
                            </span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Order Time:</span>
                            <span class="detail-value"><?php echo date('M d, Y H:i', strtotime($order['order_time'])); ?></span>
                        </div>
                    </div>

                    <div class="section-separator-minor"></div>

                    <div class="info-header">
                        <span class="info-icon">📦</span>
                        <h3>Order Status Guide</h3>
                    </div>
                    <div class="role-descriptions">
                        <div class="role-desc">
                            <strong>Pending:</strong> Order received, awaiting processing
                        </div>
                        <div class="role-desc">
                            <strong>Manufacturing:</strong> Product is being manufactured
                        </div>
                        <div class="role-desc">
                            <strong>Shipping:</strong> Order has been shipped to customer
                        </div>
                        <div class="role-desc">
                            <strong>Completed:</strong> Order successfully delivered
                        </div>
                        <div class="role-desc">
                            <strong>Cancelled:</strong> Order has been cancelled
                        </div>
                    </div>

                    <div class="warning-box">
                        <div class="warning-header">
                            <span>⚠️</span>
                            <span>Important</span>
                        </div>
                        <p>Changing order details may affect inventory and customer expectations. Ensure all changes are communicated to the customer.</p>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>