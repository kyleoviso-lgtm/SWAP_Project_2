<?php
// edit_item.php

// Boot up DB connection + login authentication guard
require_once 'bootstrap.php';
require_once 'auth_guard.php';

// Get IID from URL for display/edit mode
$IID = isset($_GET['IID']) ? $_GET['IID'] : '';
$item = null;

if ($IID) {
    $stmt = $conn->prepare("SELECT IID, name, price, description, availability, role_id FROM item WHERE IID = ?");
    $stmt->bind_param("s", $IID);
    $stmt->execute();
    $result = $stmt->get_result();
    $item = $result->fetch_assoc();
    $stmt->close();
}

// If item not found, redirect back
if (!$item) {
    $_SESSION['action_status'] = [
        'type' => 'error',
        'message' => 'Item not found.'
    ];
    header("Location: dashboard_product_management.php");
    exit();
}

// Fetch roles for dropdown
$roles = $conn->query("SELECT RID, RoleName FROM roles ORDER BY RID ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Item - Store Dashboard</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/add_edit_item.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <main class="main-content">
        <header class="topbar">
            <div class="topbar-left">
                <button class="back-btn" onclick="window.location.href='dashboard_product_management.php'">
                    ← Back
                </button>
                <h1>Edit Item</h1>
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
                        <h2>Edit Product Information</h2>
                        <p>Update the details below to modify this product</p>
                    </div>

                    <form method="POST" action="process_files/process_edit_item.php" class="edit-item-form">
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
                                <label for="role_id">Target Customer Type <span class="required">*</span></label>
                                <select id="role_id" name="role_id" required>
                                    <option value="" disabled>Select customer type</option>
                                    <?php
                                    if ($roles && $roles->num_rows > 0) {
                                        while ($role = $roles->fetch_assoc()) {
                                            $selected = ($item['role_id'] == $role['RID']) ? 'selected' : '';
                                            echo "<option value='" . htmlspecialchars($role['RID']) . "' $selected>" 
                                                 . htmlspecialchars(ucfirst($role['RoleName'])) 
                                                 . "</option>";
                                        }
                                    }
                                    ?>
                                </select>
                                <span class="helper-text">This product will be visible only to the selected customer type</span>
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
                                <span class="btn-icon">✓</span>
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

                    <div class="section-separator-minor"></div>

                    <div class="info-header">
                        <span class="info-icon">👥</span>
                        <h3>Customer Visibility</h3>
                    </div>
                    <div class="role-descriptions">
                        <div class="role-desc">
                            <strong>Admin:</strong> Products visible only to administrators for testing or internal use
                        </div>
                        <div class="role-desc">
                            <strong>Individual:</strong> Products available to individual customers
                        </div>
                        <div class="role-desc">
                            <strong>Enterprise:</strong> Products available only to enterprise/business customers
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