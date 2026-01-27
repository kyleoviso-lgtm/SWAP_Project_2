<!-- sidebar.php -->
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
        <a href="dashboard_overview.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard_overview.php' ? 'active' : ''; ?>">
            <span class="icon">📊</span>
            <span>Overview</span>
        </a>
        <a href="dashboard_product_management.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard_product_management.php' ? 'active' : ''; ?>">
            <span class="icon">📦</span>
            <span>Product Management</span>
        </a>
        <a href="dashboard_orders.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard_orders.php' ? 'active' : ''; ?>">
            <span class="icon">🛒</span>
            <span>Orders</span>
        </a>
        <a href="dashboard_users.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard_users.php' ? 'active' : ''; ?>">
            <span class="icon">👥</span>
            <span>Users</span>
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