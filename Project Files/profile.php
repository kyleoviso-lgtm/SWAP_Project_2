<?php
// Database connection (configure your own credentials)
// $conn = mysqli_connect("localhost", "username", "password", "database");

// Mock user data (replace with database query)
$user = [
    'name' => 'John Doe',
    'email' => 'john.doe@example.com',
    'phone' => '+1 (555) 123-4567',
    'joinDate' => 'January 2024',
    'avatar' => 'JD',
    'role' => 'Premium Member'
];

// Mock order history (replace with database query)
$orders = [
    [
        'id' => 'ORD-2024-001',
        'date' => '2024-01-15',
        'status' => 'Delivered',
        'total' => '$249.99',
        'items' => [
            ['name' => 'Custom Widget Pro', 'quantity' => 1, 'price' => '$149.99', 'customization' => 'Blue, Size L'],
            ['name' => 'Premium Cable Set', 'quantity' => 2, 'price' => '$50.00', 'customization' => 'Black, 2m']
        ],
        'shippingAddress' => '123 Main St, Apt 4B, New York, NY 10001',
        'trackingNumber' => 'TRK123456789'
    ],
    [
        'id' => 'ORD-2024-002',
        'date' => '2024-01-20',
        'status' => 'In Transit',
        'total' => '$189.50',
        'items' => [
            ['name' => 'Smart Device', 'quantity' => 1, 'price' => '$189.50', 'customization' => 'Silver, Standard']
        ],
        'shippingAddress' => '123 Main St, Apt 4B, New York, NY 10001',
        'trackingNumber' => 'TRK987654321'
    ],
    [
        'id' => 'ORD-2024-003',
        'date' => '2024-01-22',
        'status' => 'Processing',
        'total' => '$459.99',
        'items' => [
            ['name' => 'Custom Controller', 'quantity' => 1, 'price' => '$299.99', 'customization' => 'Red, Gaming Edition'],
            ['name' => 'Protective Case', 'quantity' => 1, 'price' => '$79.99', 'customization' => 'Black'],
            ['name' => 'Cleaning Kit', 'quantity' => 2, 'price' => '$40.00', 'customization' => 'Standard']
        ],
        'shippingAddress' => '123 Main St, Apt 4B, New York, NY 10001',
        'trackingNumber' => 'Pending'
    ],
    [
        'id' => 'ORD-2024-004',
        'date' => '2024-01-10',
        'status' => 'Delivered',
        'total' => '$99.99',
        'items' => [
            ['name' => 'Accessory Pack', 'quantity' => 1, 'price' => '$99.99', 'customization' => 'Multi-color']
        ],
        'shippingAddress' => '123 Main St, Apt 4B, New York, NY 10001',
        'trackingNumber' => 'TRK555666777'
    ]
];

// Get active tab from URL parameter or default to 'orders'
$activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'orders';

// Function to get status color class
function getStatusColor($status) {
    switch ($status) {
        case 'Delivered':
            return 'status-delivered';
        case 'In Transit':
            return 'status-transit';
        case 'Processing':
            return 'status-processing';
        default:
            return 'status-default';
    }
}

// Calculate stats
$totalOrders = count($orders);
$totalSpent = 999.47;
$activeOrders = 2;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="css/profile-styles.css">
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="logo">
                <span class="icon"></span>
                <span>User Profile</span>
            </div>
        </div>

        <nav class="sidebar-nav">
            <a href="?tab=orders" class="nav-item <?php echo $activeTab === 'orders' ? 'active' : ''; ?>">
                <span class="icon">📦</span>
                <span>Order History</span>
                <?php if ($activeOrders > 0): ?>
                    <span class="badge"><?php echo $activeOrders; ?></span>
                <?php endif; ?>
            </a>
            <a href="?tab=profile" class="nav-item <?php echo $activeTab === 'profile' ? 'active' : ''; ?>">
                <span class="icon">👤</span>
                <span>Profile Info</span>
            </a>
            <a href="?tab=addresses" class="nav-item <?php echo $activeTab === 'addresses' ? 'active' : ''; ?>">
                <span class="icon">📍</span>
                <span>Addresses</span>
            </a>
            <a href="?tab=payment" class="nav-item <?php echo $activeTab === 'payment' ? 'active' : ''; ?>">
                <span class="icon">💳</span>
                <span>Payment Methods</span>
            </a>
            <a href="?tab=settings" class="nav-item <?php echo $activeTab === 'settings' ? 'active' : ''; ?>">
                <span class="icon">⚙️</span>
                <span>Settings</span>
            </a>
        </nav>

        <div class="sidebar-footer">
            <div class="user-profile">
                <div class="avatar"><?php echo htmlspecialchars($user['avatar']); ?></div>
                <div class="user-info">
                    <div class="user-name"><?php echo htmlspecialchars($user['name']); ?></div>
                    <div class="user-role"><?php echo htmlspecialchars($user['role']); ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Topbar -->
        <div class="topbar">
            <div class="search-bar">
                <span class="search-icon">🔍</span>
                <input type="text" placeholder="Search orders, products...">
            </div>
            <div class="topbar-right">
                <button class="icon-btn">
                    <span>🔔</span>
                    <span class="badge-small">3</span>
                </button>
                <button class="icon-btn">
                    <span>💬</span>
                </button>
                <button class="icon-btn">
                    <span>⚙️</span>
                </button>
            </div>
        </div>

        <!-- Store Content -->
        <div class="store-content">
            <?php if ($activeTab === 'orders'): ?>
                <!-- Orders Tab -->
                <div class="products-section">
                    <div class="products-header">
                        <div class="results-info">
                            <h2>Order History</h2>
                            <p class="results-count">Manage and track your purchases</p>
                        </div>
                    </div>

                    <!-- Stats Cards -->
                    <div class="stats-container">
                        <div class="stat-card">
                            <div class="stat-icon">📦</div>
                            <div class="stat-info">
                                <div class="stat-value"><?php echo $totalOrders; ?></div>
                                <div class="stat-label">Total Orders</div>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">💰</div>
                            <div class="stat-info">
                                <div class="stat-value">$<?php echo number_format($totalSpent, 2); ?></div>
                                <div class="stat-label">Total Spent</div>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">🚚</div>
                            <div class="stat-info">
                                <div class="stat-value"><?php echo $activeOrders; ?></div>
                                <div class="stat-label">Active Orders</div>
                            </div>
                        </div>
                    </div>

                    <!-- Orders List -->
                    <div class="orders-list">
                        <?php foreach ($orders as $order): ?>
                            <div class="order-card">
                                <!-- Order Header -->
                                <div class="order-header">
                                    <div class="order-primary-info">
                                        <div class="order-id">
                                            <span class="label">Order ID</span>
                                            <span class="value"><?php echo htmlspecialchars($order['id']); ?></span>
                                        </div>
                                        <div class="order-date">
                                            <span class="label">Date</span>
                                            <span class="value"><?php echo htmlspecialchars($order['date']); ?></span>
                                        </div>
                                        <div class="order-total">
                                            <span class="label">Total</span>
                                            <span class="value price"><?php echo htmlspecialchars($order['total']); ?></span>
                                        </div>
                                    </div>
                                    <div class="order-status">
                                        <span class="status-badge <?php echo getStatusColor($order['status']); ?>">
                                            <?php echo htmlspecialchars($order['status']); ?>
                                        </span>
                                    </div>
                                </div>

                                <!-- Order Body -->
                                <div class="order-body">
                                    <!-- Items -->
                                    <div class="order-items">
                                        <?php foreach ($order['items'] as $index => $item): ?>
                                            <div class="order-item">
                                                <div class="item-details">
                                                    <div class="item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                                                    <div class="item-custom">
                                                        <span class="custom-label">Customization:</span>
                                                        <?php echo htmlspecialchars($item['customization']); ?>
                                                    </div>
                                                </div>
                                                <div class="item-price-info">
                                                    <div class="item-qty">Qty: <?php echo htmlspecialchars($item['quantity']); ?></div>
                                                    <div class="item-price"><?php echo htmlspecialchars($item['price']); ?></div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>

                                    <!-- Shipping -->
                                    <div class="shipping-info">
                                        <span class="shipping-icon">📍</span>
                                        <div class="shipping-text">
                                            <div class="shipping-label">Shipping Address</div>
                                            <div class="shipping-address"><?php echo htmlspecialchars($order['shippingAddress']); ?></div>
                                        </div>
                                    </div>

                                    <!-- Tracking -->
                                    <?php if ($order['trackingNumber'] !== 'Pending'): ?>
                                        <div class="tracking-info">
                                            <div class="tracking-details">
                                                <div class="tracking-label">Tracking Number</div>
                                                <div class="tracking-number"><?php echo htmlspecialchars($order['trackingNumber']); ?></div>
                                            </div>
                                            <button class="btn-primary">Track Package</button>
                                        </div>
                                    <?php else: ?>
                                        <div class="pending-notice">
                                            ⏳ Tracking information will be available once your order ships.
                                        </div>
                                    <?php endif; ?>

                                    <!-- Actions -->
                                    <div class="order-actions">
                                        <button class="btn-secondary">View Details</button>
                                        <button class="btn-secondary">Download Invoice</button>
                                        <button class="btn-secondary">Contact Support</button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

            <?php elseif ($activeTab === 'profile'): ?>
                <!-- Profile Tab -->
                <div class="products-section">
                    <div class="products-header">
                        <div class="results-info">
                            <h2>Profile Information</h2>
                            <p class="results-count">Manage your personal information</p>
                        </div>
                    </div>

                    <div class="profile-container">
                        <div class="profile-card">
                            <div class="profile-avatar-section">
                                <div class="avatar-large"><?php echo htmlspecialchars($user['avatar']); ?></div>
                                <div class="profile-main-info">
                                    <h3><?php echo htmlspecialchars($user['name']); ?></h3>
                                    <p><?php echo htmlspecialchars($user['role']); ?></p>
                                    <p class="member-since">Member since <?php echo htmlspecialchars($user['joinDate']); ?></p>
                                </div>
                            </div>

                            <form class="profile-form">
                                <div class="form-row">
                                    <div class="form-group">
                                        <label class="form-label">Full Name</label>
                                        <input type="text" class="form-input" value="<?php echo htmlspecialchars($user['name']); ?>" readonly>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label class="form-label">Email Address</label>
                                        <input type="email" class="form-input" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label class="form-label">Phone Number</label>
                                        <input type="tel" class="form-input" value="<?php echo htmlspecialchars($user['phone']); ?>" readonly>
                                    </div>
                                </div>

                                <div class="form-actions">
                                    <button type="button" class="btn-primary">Edit Profile</button>
                                    <button type="button" class="btn-secondary">Change Password</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            <?php else: ?>
                <!-- Other Tabs Placeholder -->
                <div class="products-section">
                    <div class="products-header">
                        <div class="results-info">
                            <h2>
                                <?php 
                                    if ($activeTab === 'addresses') echo 'Saved Addresses';
                                    elseif ($activeTab === 'payment') echo 'Payment Methods';
                                    elseif ($activeTab === 'settings') echo 'Account Settings';
                                ?>
                            </h2>
                            <p class="results-count">This section is under construction</p>
                        </div>
                    </div>

                    <div class="placeholder-container">
                        <div class="placeholder-icon">🚧</div>
                        <h3>Coming Soon</h3>
                        <p>This feature is currently being developed and will be available shortly.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
