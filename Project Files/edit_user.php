<!DOCTYPE html>
<html lang="en">

<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mydb";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get UID from URL
$UID = isset($_GET['UID']) ? $_GET['UID'] : '';

// Fetch user data
$user = null;
if ($UID) {
    $stmt = $conn->prepare("SELECT UID, username, email, role_ID, status_ID, payment_ID, address_ID FROM user WHERE UID = ?");
    $stmt->bind_param("s", $UID);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
}

// If user not found, redirect back
if (!$user) {
    header("Location: dashboard_users.php");
    exit();
}

// Fetch roles for dropdown
$roles = $conn->query("SELECT RID, RoleName FROM roles ORDER BY RID ASC");

// Fetch statuses for dropdown
$statuses = $conn->query("SELECT USID, status_name FROM user_stat ORDER BY USID ASC");

// Fetch payments for dropdown (optional)
$payments = $conn->query("SELECT PID, token FROM payment ORDER BY PID ASC");

// Fetch addresses for dropdown (optional)
$addresses = $conn->query("SELECT AID, CONCAT(street_name, ', ', city) as address_display FROM address ORDER BY AID ASC");
?>

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
        <div class="back-btn-container">
            <button class="back-btn" onclick="window.location.href='dashboard_users.php'">
                ← Back
            </button>
        </div>

        <div class="add-user-form-content">
            <div class="form-container">
                <div class="form-card">
                    <div class="form-header">
                        <h2>Edit User Information</h2>
                        <p>Update the details below to modify this user account</p>
                    </div>

                    <form method="POST" action="process_edit_user.php" class="add-user-form">
                        <input type="hidden" name="UID" value="<?php echo htmlspecialchars($user['UID']); ?>">
                        
                        <!-- Username and Email Row -->
                        <div class="form-row">
                            <div class="form-group">
                                <label for="username">Username <span class="required">*</span></label>
                                <input 
                                    type="text" 
                                    id="username" 
                                    name="username" 
                                    value="<?php echo htmlspecialchars($user['username']); ?>"
                                    placeholder="Enter username"
                                    maxlength="45"
                                    required
                                >
                                <span class="helper-text">Username must be unique (max 45 characters)</span>
                            </div>

                            <div class="form-group">
                                <label for="email">Email Address <span class="required">*</span></label>
                                <input 
                                    type="email" 
                                    id="email" 
                                    name="email" 
                                    value="<?php echo htmlspecialchars($user['email']); ?>"
                                    placeholder="user@example.com"
                                    maxlength="100"
                                    required
                                >
                            </div>
                        </div>

                        <!-- Password Row (Optional for editing) -->
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
                                <span class="helper-text">Only fill if changing password (min 8 characters)</span>
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

                        <!-- Role and Status Row -->
                        <div class="form-row">
                            <div class="form-group">
                                <label for="role_ID">Role <span class="required">*</span></label>
                                <select id="role_ID" name="role_ID" required>
                                    <option value="" disabled>Select role</option>
                                    <?php
                                    if ($roles->num_rows > 0) {
                                        while ($role = $roles->fetch_assoc()) {
                                            $selected = ($user['role_ID'] == $role['RID']) ? 'selected' : '';
                                            echo "<option value='" . htmlspecialchars($role['RID']) . "' $selected>" . htmlspecialchars(ucfirst($role['RoleName'])) . "</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="status_ID">Account Status <span class="required">*</span></label>
                                <select id="status_ID" name="status_ID" required>
                                    <option value="" disabled>Select status</option>
                                    <?php
                                    if ($statuses->num_rows > 0) {
                                        while ($status = $statuses->fetch_assoc()) {
                                            $selected = ($user['status_ID'] == $status['USID']) ? 'selected' : '';
                                            echo "<option value='" . htmlspecialchars($status['USID']) . "' $selected>" . htmlspecialchars(ucfirst(str_replace('_', ' ', $status['status_name']))) . "</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="section-separator-minor"></div>

                        <!-- Optional Foreign Key References -->
                        <div class="form-section-header">
                            <h3>Optional References</h3>
                            <span class="optional-label">(Optional)</span>
                        </div>

                        <!-- Payment and Address Row -->
                        <div class="form-row">
                            <div class="form-group">
                                <label for="payment_ID">Payment Method</label>
                                <select id="payment_ID" name="payment_ID">
                                    <option value="">None</option>
                                    <?php
                                    if ($payments->num_rows > 0) {
                                        while ($payment = $payments->fetch_assoc()) {
                                            $selected = ($user['payment_ID'] == $payment['PID']) ? 'selected' : '';
                                            echo "<option value='" . htmlspecialchars($payment['PID']) . "' $selected>Payment #" . htmlspecialchars($payment['PID']) . " (Token: " . htmlspecialchars($payment['token']) . ")</option>";
                                        }
                                    }
                                    ?>
                                </select>
                                <span class="helper-text">Link to existing payment method</span>
                            </div>

                            <div class="form-group">
                                <label for="address_ID">Address</label>
                                <select id="address_ID" name="address_ID">
                                    <option value="">None</option>
                                    <?php
                                    if ($addresses->num_rows > 0) {
                                        while ($address = $addresses->fetch_assoc()) {
                                            $selected = ($user['address_ID'] == $address['AID']) ? 'selected' : '';
                                            echo "<option value='" . htmlspecialchars($address['AID']) . "' $selected>" . htmlspecialchars($address['address_display']) . "</option>";
                                        }
                                    }
                                    ?>
                                </select>
                                <span class="helper-text">Link to existing address</span>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="button" class="btn-cancel" onclick="window.location.href='dashboard_users.php'">
                                Cancel
                            </button>
                            <button type="submit" class="btn-submit">
                                <span class="btn-icon">✓</span>
                                Update User
                            </button>
                        </div>
                    </form>
                </div>

                <div class="info-card">
                    <div class="info-header">
                        <span class="info-icon">ℹ️</span>
                        <h3>Current User Details</h3>
                    </div>
                    <div class="item-details">
                        <div class="detail-row">
                            <span class="detail-label">User ID:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($user['UID']); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Username:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($user['username']); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Email:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($user['email']); ?></span>
                        </div>
                    </div>

                    <div class="section-separator-minor"></div>

                    <div class="info-header">
                        <span class="info-icon">💡</span>
                        <h3>Edit Tips</h3>
                    </div>
                    <ul class="info-list">
                        <li>Username and email must remain unique</li>
                        <li>Leave password fields blank to keep current password</li>
                        <li>Changing role will affect user permissions immediately</li>
                        <li>Status changes take effect on next login</li>
                        <li>Payment and Address links are optional</li>
                    </ul>

                    <div class="section-separator-minor"></div>

                    <div class="info-header">
                        <span class="info-icon">🔐</span>
                        <h3>Role Permissions</h3>
                    </div>
                    <div class="role-descriptions">
                        <div class="role-desc">
                            <strong>Admin:</strong> Full system access and management
                        </div>
                        <div class="role-desc">
                            <strong>Individual:</strong> Standard customer account
                        </div>
                        <div class="role-desc">
                            <strong>Enterprise:</strong> Business customer with bulk ordering
                        </div>
                    </div>

                    <div class="warning-box">
                        <div class="warning-header">
                            <span>⚠️</span>
                            <span>Warning</span>
                        </div>
                        <p>Changes to user roles and status affect system access. Verify changes before submitting.</p>
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
            // Only validate if password is being changed
            if (password.value || confirmPassword.value) {
                if (password.value !== confirmPassword.value) {
                    e.preventDefault();
                    alert('Passwords do not match!');
                    confirmPassword.focus();
                    return false;
                }
                
                if (password.value.length < 8) {
                    e.preventDefault();
                    alert('Password must be at least 8 characters!');
                    password.focus();
                    return false;
                }
            }
        });
    </script>
</body>
</html>