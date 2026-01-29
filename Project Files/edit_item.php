<?php
// edit_item.php

// Database connection
//Boot up DB connection + login authentication guard
require_once 'bootstrap.php';
require_once 'auth_guard.php';

// Handle update submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $original_IID = $_POST['original_IID'];
    $IID = $_POST['IID'];
    $name = $_POST['name'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    $availability = isset($_POST['availability']) ? (int)$_POST['availability'] : 0;

    // Validate fields
    if (empty($IID) || empty($name) || empty($description)) {
        $error = "Please fill in all required fields.";
    } else {
        // Prepare and execute update query
        $stmt = $conn->prepare("
            UPDATE item 
            SET IID = ?, name = ?, price = ?, description = ?, availability = ? 
            WHERE IID = ?
        ");
        $stmt->bind_param("ssdsis", $IID, $name, $price, $description, $availability, $original_IID);


        if ($stmt->execute()) {
            // Redirect back to dashboard after success
            header("Location: dashboard_product_management.php");
            exit;
        } else {
            $error = "Error updating record: " . $stmt->error;
        }

        $stmt->close();
    }
}

// Get IID from URL for display/edit mode
$IID = isset($_GET['IID']) ? $_GET['IID'] : '';
$item = null;

if ($IID) {
    $stmt = $conn->prepare("SELECT IID, name, price, description, availability FROM item WHERE IID = ?");
    $stmt->bind_param("s", $IID);
    $stmt->execute();
    $result = $stmt->get_result();
    $item = $result->fetch_assoc();
    $stmt->close();
}

// If item not found, redirect back
if (!$item) {
    header("Location: dashboard_product_management.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Item - Store Dashboard</title>
    <link rel="stylesheet" href="css/add_edit_item.css">
</head>
<body>
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="logo">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none">
                    <path d="M12 2L2 7V17L12 22L22 17V7L12 2Z" fill="#5865f2"/>
                </svg>
                <span>Store Dashboard</span>
            </div>
        </div>

        <nav class="sidebar-nav">
            <a href="dashboard.php" class="nav-item">
                <span class="icon">📊</span>
                <span>Overview</span>
            </a>
            <a href="dashboard_product_management.php" class="nav-item active">
                <span class="icon">📦</span>
                <span>Product Management</span>
            </a>
            <a href="#" class="nav-item"><span class="icon">🛒</span><span>Orders</span></a>
            <a href="#" class="nav-item"><span class="icon">👥</span><span>Customers</span></a>
            <a href="#" class="nav-item"><span class="icon">💰</span><span>Revenue</span></a>
            <a href="#" class="nav-item"><span class="icon">⚙️</span><span>Settings</span></a>
        </nav>

        <div class="sidebar-footer">
            <div class="user-profile">
                <div class="avatar">JD</div>
                <div class="user-info">
                    <div class="user-name">John Doe</div>
                    <div class="user-role">Admin</div>
                </div>
            </div>
        </div>
    </aside>

    <main class="main-content">
        <header class="topbar">
            <div class="topbar-left">
                <button class="back-btn" onclick="window.location.href='dashboard_product_management.php'">
                    ← Back
                </button>
                <h1>Edit Item</h1>
            </div>
        </header>

        <div class="dashboard-content">
            <div class="form-container">
                <div class="form-card">
                    <div class="form-header">
                        <h2>Edit Product Information</h2>
                        <p>Update the details below to modify this product</p>
                    </div>

                    <?php if (isset($error)) : ?>
                        <p style="color:#ed4245; margin-bottom:16px;"><?php echo htmlspecialchars($error); ?></p>
                    <?php endif; ?>

                    <form method="POST" action="" class="edit-item-form">
                        <input type="hidden" name="original_IID" value="<?php echo htmlspecialchars($item['IID']); ?>">

                        <div class="form-row">
                            <div class="form-group full-width">
                                <label for="IID">Item ID <span class="required">*</span></label>
                                <input 
                                    type="text" 
                                    id="IID" 
                                    name="IID" 
                                    value="<?php echo htmlspecialchars($item['IID']); ?>"
                                    placeholder="Enter item ID"
                                    required
                                >
                                <span class="helper-text">Changing the Item ID will update it in the database</span>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group full-width">
                                <label for="name">Product Name <span class="required">*</span></label>
                                <input 
                                    type="text" 
                                    id="name" 
                                    name="name" 
                                    value="<?php echo htmlspecialchars($item['name']); ?>"
                                    placeholder="Enter product name"
                                    required
                                >
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="price">Price (USD) <span class="required">*</span></label>
                                <div class="input-with-icon">
                                    <span class="input-icon">$</span>
                                    <input 
                                        type="text"
                                        inputmode="decimal"
                                        pattern="[0-9]*\.?[0-9]*"
                                        id="price" 
                                        name="price" 
                                        value="<?php echo htmlspecialchars($item['price']); ?>"
                                        placeholder="0.00"
                                        required
                                    >
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="availability">Availability <span class="required">*</span></label>
                                <select id="availability" name="availability" required>
                                    <option value="1" <?php echo ($item['availability'] == 1) ? 'selected' : ''; ?>>Available</option>
                                    <option value="0" <?php echo ($item['availability'] == 0) ? 'selected' : ''; ?>>Unavailable</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group full-width">
                                <label for="description">Description <span class="required">*</span></label>
                                <textarea 
                                    id="description" 
                                    name="description" 
                                    rows="5"
                                    placeholder="Enter product description"
                                    required
                                ><?php echo htmlspecialchars($item['description']); ?></textarea>
                                <span class="helper-text">Provide a detailed description of the product</span>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="button" class="btn-cancel" onclick="window.location.href='dashboard_product_management.php'">
                                Cancel
                            </button>
                            <button type="submit" class="btn-submit">
                                Update Product
                            </button>
                        </div>
                    </form>
                </div>

                <div class="info-card">
                    <div class="info-header">
                        <span class="info-icon">ℹ️</span>
                        <h3>Current Item Details</h3>
                    </div>
                    <div class="item-details">
                        <div class="detail-row">
                            <span class="detail-label">Item ID:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($item['IID']); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Name:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($item['name']); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Price:</span>
                            <span class="detail-value">$<?php echo number_format($item['price'], 2); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Status:</span>
                            <span class="detail-value">
                                <?php if ($item['availability'] == 1): ?>
                                    <span class="status-badge completed">Available</span>
                                <?php else: ?>
                                    <span class="status-badge pending">Unavailable</span>
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>

                    <div class="warning-box">
                        <div class="warning-header">
                            <span>⚠️</span>
                            <span>Warning</span>
                        </div>
                        <p>Changing the Item ID will update the primary key. Make sure no orders reference this item before changing it.</p>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
