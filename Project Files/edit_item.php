<?php
// edit_item.php

require_once 'bootstrap.php'; // DB + session start
require_once 'auth_guard.php'; // requires login

// Ensure CSRF token exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Get item ID from query string
$IID = isset($_GET['IID']) ? (int)$_GET['IID'] : 0;
$item = null;

if ($IID > 0) {
    $stmt = $conn->prepare("SELECT IID, name, price, description, availability, role_id FROM item WHERE IID = ?");
    $stmt->bind_param("i", $IID);
    $stmt->execute();
    $item = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// Redirect if not found
if (!$item) {
    $_SESSION['action_status'] = [
        'type' => 'error',
        'message' => 'Item not found.'
    ];
    header("Location: dashboard_product_management.php");
    exit();
}

// Fetch roles for dropdown
$roles = $conn->query("SELECT RID, RoleName FROM roles ORDER BY RoleName ASC");
if (!$roles) {
    die("Database error: " . htmlspecialchars($conn->error));
}
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
    <?php if (isset($_SESSION['action_status'])): ?>
        <?php
        $type = $_SESSION['action_status']['type'];
        $bannerClass = ($type === 'success') ? 'success-banner' : 'error-banner';
        ?>
        <div class="<?= $bannerClass; ?>">
            <?= htmlspecialchars($_SESSION['action_status']['message']); ?>
        </div>
        <?php unset($_SESSION['action_status']); ?>
    <?php endif; ?>

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
                    <p>Update the details below to modify this product.</p>
                </div>

                <form method="POST" action="process_files/process_edit_item.php" class="edit-item-form">
                    <!-- CSRF Token -->
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                    <input type="hidden" name="original_IID" value="<?= htmlspecialchars($item['IID']); ?>">

                    <!-- Item ID -->
                    <div class="form-row">
                        <div class="form-group full-width">
                            <label for="IID">Item ID <span class="required">*</span></label>
                            <input 
                                type="number" 
                                id="IID" 
                                name="IID" 
                                value="<?= htmlspecialchars($item['IID']); ?>" 
                                min="1"
                                required
                            >
                            <span class="helper-text">Changing the Item ID will update it in the database.</span>
                        </div>
                    </div>

                    <!-- Product Name -->
                    <div class="form-row">
                        <div class="form-group full-width">
                            <label for="name">Product Name <span class="required">*</span></label>
                            <input 
                                type="text" 
                                id="name" 
                                name="name" 
                                value="<?= htmlspecialchars($item['name']); ?>"
                                maxlength="45"
                                required
                            >
                        </div>
                    </div>

                    <!-- Price and Availability -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="price">Price (SGD) <span class="required">*</span></label>
                            <div class="input-with-icon">
                                <span class="input-icon">$</span>
                                <input 
                                    type="number"
                                    id="price"
                                    name="price"
                                    step="0.01"
                                    min="0"
                                    value="<?= htmlspecialchars($item['price']); ?>"
                                    required
                                >
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="availability">Availability <span class="required">*</span></label>
                            <select id="availability" name="availability" required>
                                <option value="1" <?= ($item['availability'] == 1) ? 'selected' : ''; ?>>Available</option>
                                <option value="0" <?= ($item['availability'] == 0) ? 'selected' : ''; ?>>Unavailable</option>
                            </select>
                        </div>
                    </div>

                    <!-- Role -->
                    <div class="form-row">
                        <div class="form-group full-width">
                            <label for="role_id">Target Customer Type <span class="required">*</span></label>
                            <select id="role_id" name="role_id" required>
                                <option value="" disabled>Select customer type</option>
                                <?php while ($role = $roles->fetch_assoc()): ?>
                                    <option 
                                        value="<?= htmlspecialchars($role['RID']); ?>" 
                                        <?= ($item['role_id'] == $role['RID']) ? 'selected' : ''; ?>
                                    >
                                        <?= htmlspecialchars(ucfirst($role['RoleName'])); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                            <span class="helper-text">This product will be visible only to the selected customer type.</span>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="form-row">
                        <div class="form-group full-width">
                            <label for="description">Description <span class="required">*</span></label>
                            <textarea 
                                id="description" 
                                name="description" 
                                rows="5"
                                maxlength="100"
                                required
                            ><?= htmlspecialchars($item['description']); ?></textarea>
                            <span class="helper-text">Provide a detailed description of the product.</span>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="form-actions">
                        <button type="button" class="btn-cancel" onclick="window.location.href='dashboard_product_management.php'">
                            Cancel
                        </button>
                        <button type="submit" class="btn-submit">
                            <span class="btn-icon">✓</span> Update Product
                        </button>
                    </div>
                </form>
            </div>

            <!-- Info Card -->
            <div class="info-card">
                <div class="info-header">
                    <span class="info-icon">ℹ️</span>
                    <h3>Current Item Details</h3>
                </div>
                <div class="item-details">
                    <div class="detail-row"><span class="detail-label">Item ID:</span><span class="detail-value"><?= htmlspecialchars($item['IID']); ?></span></div>
                    <div class="detail-row"><span class="detail-label">Name:</span><span class="detail-value"><?= htmlspecialchars($item['name']); ?></span></div>
                    <div class="detail-row"><span class="detail-label">Price:</span><span class="detail-value">$<?= number_format($item['price'], 2); ?></span></div>
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
                    <div class="role-desc"><strong>Admin:</strong> Internal testing or hidden items.</div>
                    <div class="role-desc"><strong>Individual:</strong> Visible to personal customers.</div>
                    <div class="role-desc"><strong>Enterprise:</strong> Available to business clients.</div>
                </div>

                <div class="warning-box">
                    <div class="warning-header">
                        <span>⚠️</span>
                        <span>Important</span>
                    </div>
                    <p>Changing the Item ID updates the primary key. Ensure no existing orders depend on this item before modifying it.</p>
                </div>
            </div>
        </div>
    </div>
</main>
</body>
</html>
