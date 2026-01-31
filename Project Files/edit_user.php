<?php
// edit_user.php

require_once 'bootstrap.php'; // includes DB + session
require_once 'auth_guard.php'; // requires login

// Ensure CSRF token exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Get UID
$UID = isset($_GET['UID']) ? (int)$_GET['UID'] : 0;

// Fetch user data
$user = null;
if ($UID > 0) {
    $stmt = $conn->prepare("SELECT UID, username, email, role_ID, status_ID, payment_ID, address_ID FROM user WHERE UID = ?");
    $stmt->bind_param("i", $UID);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// If not found, redirect
if (!$user) {
    $_SESSION['action_status'] = [
        'type' => 'error',
        'message' => 'User not found.'
    ];
    header("Location: dashboard_users.php");
    exit();
}

// Dropdowns
$roles = $conn->query("SELECT RID, RoleName FROM roles ORDER BY RID ASC");
$statuses = $conn->query("SELECT USID, status_name FROM user_stat ORDER BY USID ASC");
$payments = $conn->query("SELECT PID, token FROM payment ORDER BY PID ASC");
$addresses = $conn->query("
    SELECT 
        AID, 
        CONCAT(address_line_1, ', ', address_line_2, ', ', city, ', ', country) AS address_display 
    FROM address 
    ORDER BY AID ASC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - Store Dashboard</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/add_user.css">
</head>
<body>

<?php include 'sidebar.php'; ?>

<main class="main-content">
    <header class="topbar">
        <div class="topbar-left">
            <h1>Edit User</h1>
        </div>
    </header>

    <div class="back-btn-container">
        <button class="back-btn" onclick="window.location.href='dashboard_users.php'">← Back</button>
    </div>

    <div class="add-user-form-content">
        <div class="form-container">
            <div class="form-card">
                <div class="form-header">
                    <h2>Edit User Information</h2>
                    <p>Update the details below to modify this user account</p>
                </div>

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

                <form method="POST" action="process_files/process_edit_user.php" class="add-user-form">
                    <!-- CSRF -->
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                    <input type="hidden" name="UID" value="<?= htmlspecialchars($user['UID']); ?>">

                    <!-- Username + Email -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="username">Username <span class="required">*</span></label>
                            <input 
                                type="text" 
                                id="username" 
                                name="username" 
                                value="<?= htmlspecialchars($user['username']); ?>"
                                maxlength="45"
                                required
                            >
                            <span class="helper-text">Must be unique (max 45 characters)</span>
                        </div>

                        <div class="form-group">
                            <label for="email">Email Address <span class="required">*</span></label>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                value="<?= htmlspecialchars($user['email']); ?>"
                                maxlength="100"
                                required
                            >
                        </div>
                    </div>

                    <!-- Password (optional) -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="password">New Password</label>
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                placeholder="Leave blank to keep current password"
                                minlength="8"
                            >
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password</label>
                            <input 
                                type="password" 
                                id="confirm_password" 
                                name="confirm_password" 
                                placeholder="Re-enter new password"
                            >
                        </div>
                    </div>

                    <!-- Role + Status -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="role_ID">Role <span class="required">*</span></label>
                            <select id="role_ID" name="role_ID" required>
                                <option value="">Select role</option>
                                <?php while ($role = $roles->fetch_assoc()): ?>
                                    <option 
                                        value="<?= htmlspecialchars($role['RID']); ?>"
                                        <?= ($user['role_ID'] == $role['RID']) ? 'selected' : ''; ?>
                                    >
                                        <?= htmlspecialchars(ucfirst($role['RoleName'])); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="status_ID">Account Status <span class="required">*</span></label>
                            <select id="status_ID" name="status_ID" required>
                                <option value="">Select status</option>
                                <?php while ($status = $statuses->fetch_assoc()): ?>
                                    <option 
                                        value="<?= htmlspecialchars($status['USID']); ?>"
                                        <?= ($user['status_ID'] == $status['USID']) ? 'selected' : ''; ?>
                                    >
                                        <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $status['status_name']))); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>

                    <div class="section-separator-minor"></div>

                    <!-- Optional references -->
                    <div class="form-section-header">
                        <h3>Optional References</h3>
                        <span class="optional-label">(Optional)</span>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="payment_ID">Payment Method</label>
                            <select id="payment_ID" name="payment_ID">
                                <option value="">None</option>
                                <?php while ($payment = $payments->fetch_assoc()): ?>
                                    <option 
                                        value="<?= htmlspecialchars($payment['PID']); ?>"
                                        <?= ($user['payment_ID'] == $payment['PID']) ? 'selected' : ''; ?>
                                    >
                                        Payment #<?= htmlspecialchars($payment['PID']); ?> (Token: <?= htmlspecialchars($payment['token']); ?>)
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="address_ID">Address</label>
                            <select id="address_ID" name="address_ID">
                                <option value="">None</option>
                                <?php while ($address = $addresses->fetch_assoc()): ?>
                                    <option 
                                        value="<?= htmlspecialchars($address['AID']); ?>"
                                        <?= ($user['address_ID'] == $address['AID']) ? 'selected' : ''; ?>
                                    >
                                        <?= htmlspecialchars($address['address_display']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn-cancel" onclick="window.location.href='dashboard_users.php'">
                            Cancel
                        </button>
                        <button type="submit" class="btn-submit">
                            <span class="btn-icon">✓</span> Update User
                        </button>
                    </div>
                </form>
            </div>

            <!-- Info Card -->
            <div class="info-card">
                <div class="info-header">
                    <span class="info-icon">ℹ️</span>
                    <h3>Current User Details</h3>
                </div>
                <div class="item-details">
                    <div class="detail-row"><span class="detail-label">User ID:</span><span class="detail-value"><?= htmlspecialchars($user['UID']); ?></span></div>
                    <div class="detail-row"><span class="detail-label">Username:</span><span class="detail-value"><?= htmlspecialchars($user['username']); ?></span></div>
                    <div class="detail-row"><span class="detail-label">Email:</span><span class="detail-value"><?= htmlspecialchars($user['email']); ?></span></div>
                </div>

                <div class="section-separator-minor"></div>

                <div class="info-header"><span class="info-icon">💡</span><h3>Edit Tips</h3></div>
                <ul class="info-list">
                    <li>Leave password blank to keep it unchanged.</li>
                    <li>Changing roles will immediately affect user permissions.</li>
                    <li>Status changes take effect on next login.</li>
                    <li>Payment and address are optional links.</li>
                </ul>

                <div class="section-separator-minor"></div>

                <div class="info-header"><span class="info-icon">🔐</span><h3>Role Permissions</h3></div>
                <div class="role-descriptions">
                    <div class="role-desc"><strong>Admin:</strong> Full access to all dashboards.</div>
                    <div class="role-desc"><strong>Individual:</strong> Standard customer access.</div>
                    <div class="role-desc"><strong>Enterprise:</strong> Business account with bulk order options.</div>
                </div>

                <div class="warning-box">
                    <div class="warning-header"><span>⚠️</span><span>Warning</span></div>
                    <p>Changes to roles or statuses affect access rights. Review before saving.</p>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
    const form = document.querySelector('.add-user-form');
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');

    form.addEventListener('submit', function(e) {
        if (password.value || confirmPassword.value) {
            if (password.value !== confirmPassword.value) {
                e.preventDefault();
                alert('Passwords do not match!');
                confirmPassword.focus();
            } else if (password.value.length < 8) {
                e.preventDefault();
                alert('Password must be at least 8 characters!');
                password.focus();
            }
        }
    });
</script>

</body>
</html>
