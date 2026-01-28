<?php
// --------------------
// DEFAULT VALUES
// --------------------
$userName = "Guest";
$userRole = "Visitor";
$userInitials = "G";

// --------------------
// IF USER LOGGED IN, FETCH REAL INFO
// --------------------
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];

    $sql = "
        SELECT u.username, r.RoleName
        FROM user u
        JOIN roles r ON u.role_ID = r.RID
        WHERE u.UID = ?
        LIMIT 1
    ";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("s", $userId);
        $stmt->execute();
        $stmt->bind_result($username, $roleName);

        if ($stmt->fetch()) {
            $userName = $username;
            $userRole = $roleName;

            $nameParts = explode(" ", $userName);
            $userInitials = strtoupper(substr($nameParts[0], 0, 1) . (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : ""));
        }

        $stmt->close();
    }
}
?>

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
        <a href="dashboard_overview.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'dashboard_overview.php' ? 'active' : ''; ?>">
            <span class="icon">📊</span>
            <span>Overview</span>
        </a>
        <a href="dashboard_product_management.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'dashboard_product_management.php' ? 'active' : ''; ?>">
            <span class="icon">📦</span>
            <span>Product Management</span>
        </a>
        <a href="dashboard_orders.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'dashboard_orders.php' ? 'active' : ''; ?>">
            <span class="icon">🛒</span>
            <span>Orders</span>
        </a>
        <a href="dashboard_users.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'dashboard_users.php' ? 'active' : ''; ?>">
            <span class="icon">👥</span>
            <span>Users</span>
        </a>
    </nav>

    <div class="sidebar-footer">
        <a href="profile.php">
            <div class="user-profile">
                <div class="avatar"><?= htmlspecialchars($userInitials) ?></div>
                <div class="user-info">
                    <div class="user-name"><?= htmlspecialchars($userName) ?></div>
                    <div class="user-role"><?= htmlspecialchars($userRole) ?></div>
                </div>
            </div>
        </a>
    </div>
</aside>
