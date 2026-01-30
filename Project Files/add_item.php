<?php
// add_item.php

require_once 'bootstrap.php';   // includes DB connection + session start
require_once 'auth_guard.php';  // ensures user is logged in

// Fetch customer roles for the dropdown
$roles = $conn->query("SELECT RID, RoleName FROM roles ORDER BY RoleName ASC");

if (!$roles) {
    die("Database query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Item - Store Dashboard</title>
    <link rel="stylesheet" href="css/add_edit_item.css">
</head>
<body>

    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>

    <main class="main-content">
        <header class="topbar">
            <div class="topbar-left">
                <button class="back-btn" onclick="window.location.href='dashboard_product_management.php'">
                    ← Back
                </button>
                <h1>Add New Item</h1>
            </div>
        </header>

        <div class="dashboard-content">
            <div class="form-container">
                <div class="form-card">
                    <div class="form-header">
                        <h2>Product Information</h2>
                        <p>Fill in the details below to add a new product to your inventory</p>
                    </div>

                    <?php if (isset($_SESSION['action_status'])): ?>
                        <div class="<?= $_SESSION['action_status']['type'] === 'success' ? 'success-banner' : 'error-banner' ?>">
                            <?= htmlspecialchars($_SESSION['action_status']['message']) ?>
                        </div>
                        <?php unset($_SESSION['action_status']); ?>
                    <?php endif; ?>

                    <form method="POST" action="process_files/process_add_item.php" class="add-item-form">
                        <div class="form-row">
                            <div class="form-group full-width">
                                <label for="name">Product Name <span class="required">*</span></label>
                                <input type="text" id="name" name="name" placeholder="Enter product name" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="price">Price (SGD) <span class="required">*</span></label>
                                <div class="input-with-icon-section">
                                    <div class="payment-input-container">
                                        <span class="input-icon">$</span>
                                        <input type="text" id="price" name="price" placeholder="0.00" required>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="availability">Availability <span class="required">*</span></label>
                                <select id="availability" name="availability" required>
                                    <option value="" disabled selected>Select availability</option>
                                    <option value="1">Available</option>
                                    <option value="0">Unavailable</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group full-width">
                                <label for="role_id">Target Customer Type <span class="required">*</span></label>
                                <select id="role_id" name="role_id" required>
                                    <option value="" disabled selected>Select customer type</option>
                                    <?php
                                    if ($roles && $roles->num_rows > 0) {
                                        while ($role = $roles->fetch_assoc()) {
                                            echo "<option value='" . htmlspecialchars($role['RID']) . "'>" 
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
                                <textarea id="description" name="description" rows="5" placeholder="Enter product description" required></textarea>
                                <span class="helper-text">Provide a detailed description of the product</span>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="button" class="btn-cancel" onclick="window.location.href='dashboard_product_management.php'">Cancel</button>
                            <button type="submit" class="btn-submit">Add Product</button>
                        </div>
                    </form>
                </div>

                 <div class="info-card">
                    <div class="info-header">
                        <span class="info-icon">💡</span>
                        <h3>Tips for Adding Products</h3>
                    </div>
                    <ul class="info-list">
                        <li>Use clear and descriptive product names</li>
                        <li>Set competitive prices based on market research</li>
                        <li>Write detailed descriptions to help customers</li>
                        <li>Mark items as unavailable if out of stock</li>
                        <li>Review all information before submitting</li>
                    </ul>
                </div>
                
            </div>
        </div>
    </main>

</body>
</html>