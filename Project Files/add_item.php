<?php
// add_item.php

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mydb";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    $availability = isset($_POST['availability']) ? (int)$_POST['availability'] : 0;

    $stmt = $conn->prepare("INSERT INTO item (name, price, description, availability) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sdsi", $name, $price, $description, $availability);

    if ($stmt->execute()) {
        // Redirect back to product management dashboard after successful insert
        header("Location: dashboard_product_management.php");
        exit;
    } else {
        $error = "Error: " . $stmt->error;
    }

    $stmt->close();
}
$conn->close();
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
    <aside class="sidebar">
        <!-- Sidebar -->
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
            <a href="#" class="nav-item">
                <span class="icon">🛒</span>
                <span>Orders</span>
            </a>
            <a href="#" class="nav-item">
                <span class="icon">👥</span>
                <span>Customers</span>
            </a>
            <a href="#" class="nav-item">
                <span class="icon">💰</span>
                <span>Revenue</span>
            </a>
            <a href="#" class="nav-item">
                <span class="icon">⚙️</span>
                <span>Settings</span>
            </a>
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

                    <?php if (isset($error)) : ?>
                        <p style="color:#ed4245; margin-bottom:16px;"><?= htmlspecialchars($error) ?></p>
                    <?php endif; ?>

                    <form method="POST" action="" class="add-item-form">
                        <div class="form-row">
                            <div class="form-group full-width">
                                <label for="name">Product Name <span class="required">*</span></label>
                                <input 
                                    type="text" 
                                    id="name" 
                                    name="name" 
                                    placeholder="Enter product name"
                                    required
                                >
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="price">Price (SGD) <span class="required">*</span></label>
                                <div class="input-with-icon-section">
                                    <div class="payment-input-container">
                                        <span class="input-icon">$</span>
                                        <input 
                                            type="text" 
                                            id="price" 
                                            name="price"
                                            placeholder="0.00"
                                            min="0"
                                            inputmode="decimal"
                                            pattern="[0-9]*\.?[0-9]*"
                                            required
                                        >

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
                                <label for="description">Description <span class="required">*</span></label>
                                <textarea 
                                    id="description" 
                                    name="description" 
                                    rows="5"
                                    placeholder="Enter product description"
                                    required
                                ></textarea>
                                <span class="helper-text">Provide a detailed description of the product</span>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="button" class="btn-cancel" onclick="window.location.href='dashboard_product_management.php'">
                                Cancel
                            </button>
                            <button type="submit" class="btn-submit">
                                Add Product
                            </button>
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