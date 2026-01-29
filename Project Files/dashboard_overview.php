<?php
//Boot up DB connection + login authentication guard
require_once 'bootstrap.php';
require_once 'auth_guard.php';
?>

<!DOCTYPE html>
<html lang="en">
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
                        <select class="chart-select">
                            <option>Last 7 days</option>
                            <option>Last 30 days</option>
                            <option>Last 90 days</option>
                        </select>
                    </div>
                    <div class="chart-placeholder">
                        <svg viewBox="0 0 400 200" class="line-chart">
                            <polyline
                                fill="none"
                                stroke="#5865f2"
                                stroke-width="3"
                                points="0,150 50,120 100,140 150,80 200,100 250,60 300,90 350,40 400,70"
                            />
                            <polyline
                                fill="url(#gradient)"
                                stroke="none"
                                points="0,150 50,120 100,140 150,80 200,100 250,60 300,90 350,40 400,70 400,200 0,200"
                            />
                            <defs>
                                <linearGradient id="gradient" x1="0%" y1="0%" x2="0%" y2="100%">
                                    <stop offset="0%" style="stop-color:#5865f2;stop-opacity:0.3" />
                                    <stop offset="100%" style="stop-color:#5865f2;stop-opacity:0" />
                                </linearGradient>
                            </defs>
                        </svg>
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
                    <button class="btn-secondary">View All</button>
                </div>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Product</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>#ORD-2847</td>
                                <td>Sarah Johnson</td>
                                <td>Wireless Headphones</td>
                                <td>$129.99</td>
                                <td><span class="status-badge completed">Completed</span></td>
                                <td>Jan 12, 2026</td>
                            </tr>
                            <tr>
                                <td>#ORD-2846</td>
                                <td>Mike Wilson</td>
                                <td>Gaming Mouse</td>
                                <td>$79.99</td>
                                <td><span class="status-badge processing">Processing</span></td>
                                <td>Jan 12, 2026</td>
                            </tr>
                            <tr>
                                <td>#ORD-2845</td>
                                <td>Emily Davis</td>
                                <td>Laptop Stand</td>
                                <td>$45.50</td>
                                <td><span class="status-badge completed">Completed</span></td>
                                <td>Jan 11, 2026</td>
                            </tr>
                            <tr>
                                <td>#ORD-2844</td>
                                <td>Alex Brown</td>
                                <td>USB-C Cable</td>
                                <td>$19.99</td>
                                <td><span class="status-badge pending">Pending</span></td>
                                <td>Jan 11, 2026</td>
                            </tr>
                            <tr>
                                <td>#ORD-2843</td>
                                <td>Lisa Anderson</td>
                                <td>Mechanical Keyboard</td>
                                <td>$159.99</td>
                                <td><span class="status-badge completed">Completed</span></td>
                                <td>Jan 10, 2026</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</body>
</html>

