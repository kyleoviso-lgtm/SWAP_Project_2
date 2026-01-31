<?php
//Boot up DB connection + login authentication guard
require_once 'bootstrap.php';
require_once 'auth_guard.php';

// Now you can run your table queries safely
$sql = "SELECT 
            u.UID, 
            u.username, 
            u.email, 
            u.role_ID, 
            r.RoleName AS role, 
            s.status_name AS status
        FROM user u
        INNER JOIN roles r ON u.role_ID = r.RID
        INNER JOIN user_stat s ON u.status_ID = s.USID
        ORDER BY u.username ASC";
$result = $conn->query($sql);

// Count users by role
$admin_count = $conn->query("
    SELECT COUNT(*) as count 
    FROM user u 
    INNER JOIN roles r ON u.role_ID = r.RID 
    WHERE r.RoleName IN ('admin', 'staff')
")->fetch_assoc()['count'];

$customer_count = $conn->query("
    SELECT COUNT(*) as count 
    FROM user u 
    INNER JOIN roles r ON u.role_ID = r.RID 
    WHERE r.RoleName = 'individual'
")->fetch_assoc()['count'];

$enterprise_count = $conn->query("
    SELECT COUNT(*) as count 
    FROM user u 
    INNER JOIN roles r ON u.role_ID = r.RID 
    WHERE r.RoleName = 'enterprise'
")->fetch_assoc()['count'];

$total_count = $result->num_rows;
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
        
        <?php if (isset($_GET['status'])): ?>
            <?php
            $status = $_GET['status'];
            $bannerClass = str_starts_with($status, 'success') ? 'success-banner' : 'error-banner';
            $message = '';

            switch ($status) {
                // --- Success Messages ---
                case 'success_add':
                    $message = "User added successfully.";
                    break;
                case 'success_edit':
                    $message = "User updated successfully.";
                    break;
                case 'success_delete':
                    $message = "User deleted successfully.";
                    break;

                // --- Error Messages ---
                case 'error_missing':
                    $message = "Missing or invalid user ID.";
                    break;
                case 'error_db':
                    $message = "Database error. Please try again.";
                    break;
                case 'error_permission':
                    $message = "You don't have permission to perform this action.";
                    break;
                case 'error_duplicate_user':
                    $message = "Duplicate user exists";
                case 'error_unknown':
                default:
                    $message = "⚠️ An unexpected error occurred.";
                    break;
            }
            ?>
            <div class="<?php echo $bannerClass; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <header class="topbar">
            <div class="topbar-left">
                <h1>User Management</h1>
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
            
            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-icon users">👥</span>
                    </div>
                    <div class="stat-value"><?php echo $result->num_rows; ?></div>
                    <div class="stat-label">Total Users</div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-icon admin">🔑</span>
                    </div>
                    <div class="stat-value"><?php echo $admin_count; ?></div>
                    <div class="stat-label">Admin/Staff</div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-icon individual">🛍️</span>
                    </div>
                    <div class="stat-value"><?php echo $customer_count; ?></div>
                    <div class="stat-label">Individual Customers</div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-icon enterprise">🏢</span>
                    </div>
                    <div class="stat-value"><?php echo $enterprise_count; ?></div>
                    <div class="stat-label">Enterprise Customers</div>
                </div>
            </div>

            <div class="section-separator"></div>

            <!-- User Management Section -->
            <div class="subsection-container-2">

                <!-- User Management Table -->
                <div class="user-management-section">
                    <div class="user-management-section-header">
                        <div class="header-left">
                            <h2>All Users</h2>
                        </div>
                        <div class="user-management-header-actions">
                            <input type="text" class="search-bar" placeholder="Search by username or email" id="userSearchBar">
                            <!-- Role filter function -->
                            <?php
                            $roles = $conn->query("SELECT RoleName FROM roles ORDER BY RID ASC");
                            echo '<select class="filter-select" id="roleFilter">';
                            echo '<option value="all">All Roles</option>';
                            while ($r = $roles->fetch_assoc()) {
                                $roleName = htmlspecialchars($r['RoleName']);
                                echo "<option value='{$roleName}'>{$roleName}</option>";
                            }
                            echo '</select>';
                            ?>

                            <!-- Status badge filter function -->
                            <?php
                            $statusOptions = $conn->query("SELECT status_name FROM user_stat ORDER BY USID ASC");
                            echo '<select class="filter-select" id="statusFilter">';
                            echo '<option value="all">All Status</option>';
                            while ($status = $statusOptions->fetch_assoc()) {
                                echo '<option value="'.htmlspecialchars($status['status_name']).'">'.ucfirst(htmlspecialchars($status['status_name'])).'</option>';
                            }
                            echo '</select>';
                            ?>

                            <button class="btn-add" onclick="location.href='add_user.php'">
                                <span>+</span>
                                Add User
                            </button>
                        </div>
                    </div>

                    <div class="data-table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th style="width:10%;">User ID</th>
                                    <th style="width:25%;">Username</th>
                                    <th style="width:25%;">Email</th>
                                    <th style="width:15%;">Role</th>
                                    <th style="width:15%;">Status</th>
                                    <th style="width:10%;">Actions</th>

                                </tr>
                            </thead>
                            <tbody id="userTableBody">
                                <?php
                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        // Prepare role and status classes
                                        $known_roles = ['admin', 'staff', 'individual', 'enterprise'];
                                        $role_name = strtolower(str_replace(' ', '-', $row['role']));
                                        $role_class = in_array($role_name, $known_roles) ? $role_name : 'other';

                                        $status = strtolower($row['status']);
                                        switch($status) {
                                            case 'active':
                                                $status_class = 'normal';
                                                break;
                                            case 'inactive':
                                            case 'locked':
                                            case 'suspended':
                                                $status_class = 'critical';
                                                break;
                                            case 'pending_activation':
                                            case 'password_expired':
                                                $status_class = 'warning';
                                                break;
                                            default:
                                                $status_class = 'other';
                                        }

                                        $uid_js = json_encode($row['UID']);
                                        $role_html = htmlspecialchars($row['role']);
                                        $status_html = htmlspecialchars(ucfirst($row['status']));

                                        echo "<tr data-role='" . htmlspecialchars($row['role']) . "' data-status='" . htmlspecialchars($row['status']) . "'>";
                                        echo "<td><strong>#" . htmlspecialchars($row['UID']) . "</strong></td>";
                                        echo "<td><span>" . htmlspecialchars($row['username']) . "</span></td>";
                                        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                                        echo "<td><span class='role-badge {$role_class}'>{$role_html}</span></td>";
                                        echo "<td><span class='user-status-badge {$status_class}'>{$status_html}</span></td>";

                                        echo "<td class='action-btn-cell'>
                                                <button class='user-view-btn' onclick='viewUser({$uid_js})'>View</button>
                                                <button class='edit-btn' onclick='editUser({$uid_js})'>Edit</button>
                                                <button class='delete-btn' onclick='deleteUser({$uid_js})'>Delete</button>
                                            </td>";

                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='9' class='no-data'>No users found</td></tr>";
                                }
                                ?>

                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="info-card">
                    <div class="info-header">
                        <span class="info-icon">ℹ️</span>
                        <h3>Status tips</h3>
                    </div>
                    <div class="item-details">
                        <div class="detail-row">
                            <span class="detail-label">Green</span>
                            <span class="detail-value">Accounts with a green status needs no actions and is running as expected</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Yellow</span>
                            <span class="detail-value">Accounts with a yellow status require actions form an admin or user</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Red</span>
                            <span class="detail-value">Accounts with a red status are critical and needs attention</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">White</span>
                            <span class="detail-value">Accounts with a white status are accounts that have extra statuses that may need attention</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">More info</span>
                            <span class="detail-value"><a href="https://howto.caspio.com/manage-users-and-groups/directory-users/user-status-overview/" target="_blank">Click here for more info about account statuses</a></span>
                        </div>
                    </div>
                    <div class="warning-box">
                        <div class="warning-header">
                            <span>⚠️</span>
                            <span>Warning</span>
                        </div>
                        <p>Seek a local administrator's approval before finalizing the change of an account's status</p>
                    </div>
                </div>
            </div>
            

        </div>
    </main>

    <script>
        const searchBar = document.getElementById('userSearchBar');
        const roleFilter = document.getElementById('roleFilter');
        const statusFilter = document.getElementById('statusFilter');
        const tbody = document.getElementById('userTableBody');

        function filterTable() {
            const searchTerm = searchBar.value.toLowerCase();
            const selectedRole = roleFilter.value;
            const selectedStatus = statusFilter.value;
            const rows = tbody.getElementsByTagName('tr');

            for (let row of rows) {
                if (row.classList.contains('no-data')) continue;

                const username = row.cells[1].textContent.toLowerCase();
                const email = row.cells[2].textContent.toLowerCase();
                const role = row.getAttribute('data-role');
                const status = row.getAttribute('data-status');

                const matchesSearch = username.includes(searchTerm) || email.includes(searchTerm);
                const matchesRole = selectedRole === 'all' || role === selectedRole;
                const matchesStatus = selectedStatus === 'all' || status === selectedStatus;

                if (matchesSearch && matchesRole && matchesStatus) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
        }

        searchBar.addEventListener('input', filterTable);
        roleFilter.addEventListener('change', filterTable);
        statusFilter.addEventListener('change', filterTable);

        function viewUser(userId) {
            window.location.href = 'profile.php?UID=' + encodeURIComponent(userId);
        }

        function editUser(userId) {
            window.location.href = 'edit_user.php?UID=' + userId;
        }

        function deleteUser(userId) {
            if (confirm('Are you sure you want to delete this user?')) {
                window.location.href = 'process_files/process_delete_user.php?UID=' + userId;
            }
        }
    </script>
</body>
</html>