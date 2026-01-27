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

// Fetch roles for dropdown
$roles = $conn->query("SELECT RID, RoleName FROM roles ORDER BY RID ASC");

// Fetch statuses for dropdown
$statuses = $conn->query("SELECT USID, status_name FROM user_stat ORDER BY USID ASC");

// Fetch payments for dropdown (optional)
$payments = $conn->query("SELECT PID, token FROM payment ORDER BY PID ASC");

// Fetch addresses for dropdown (optional)
$addresses = $conn->query("SELECT AID, CONCAT(street_name, ', ', city) as address_display FROM address ORDER BY AID ASC");
?>

<?php if (isset($_GET['error'])): ?>
    <div class="error-banner">
        <?php
        switch ($_GET['error']) {
            case 'duplicate_user':
                echo "A user with that username or email already exists.";
                break;
            case 'missing_fields':
                echo "Please fill in all required fields.";
                break;
            case 'invalid_email':
                echo "Please enter a valid email address.";
                break;
            case 'password_mismatch':
                echo "Passwords do not match.";
                break;
            default:
                echo "An unexpected error occurred. Please try again.";
        }
        ?>
    </div>
<?php endif; ?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add User - Store Dashboard</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/add_user.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <main class="main-content">
        <header class="topbar">
            <div class="topbar-left">
                <h1>Add New User</h1>
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
                        <h2>User Information</h2>
                        <p>Fill in the details below to create a new user account</p>
                    </div>

                    <form method="POST" action="process_add_user.php" class="add-user-form">
                        
                        <!-- Username and Email Row -->
                        <div class="form-row">
                            <div class="form-group">
                                <label for="username">Username <span class="required">*</span></label>
                                <input 
                                    type="text" 
                                    id="username" 
                                    name="username" 
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
                                    placeholder="user@example.com"
                                    maxlength="100"
                                    required
                                >
                            </div>
                        </div>

                        <!-- Password Row -->
                        <div class="form-row">
                            <div class="form-group">
                                <label for="password">Password <span class="required">*</span></label>
                                <input 
                                    type="password" 
                                    id="password" 
                                    name="password" 
                                    placeholder="Enter password"
                                    required
                                    minlength="8"
                                >
                                <span class="helper-text">Minimum 8 characters</span>
                            </div>

                            <div class="form-group">
                                <label for="confirm_password">Confirm Password <span class="required">*</span></label>
                                <input 
                                    type="password" 
                                    id="confirm_password" 
                                    name="confirm_password" 
                                    placeholder="Re-enter password"
                                    required
                                >
                            </div>
                        </div>

                        <!-- Role and Status Row -->
                        <div class="form-row">
                            <div class="form-group">
                                <label for="role_ID">Role <span class="required">*</span></label>
                                <select id="role_ID" name="role_ID" required>
                                    <option value="" disabled selected>Select role</option>
                                    <?php
                                    if ($roles->num_rows > 0) {
                                        while ($role = $roles->fetch_assoc()) {
                                            echo "<option value='" . htmlspecialchars($role['RID']) . "'>" . htmlspecialchars(ucfirst($role['RoleName'])) . "</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="status_ID">Account Status <span class="required">*</span></label>
                                <select id="status_ID" name="status_ID" required>
                                    <option value="" disabled selected>Select status</option>
                                    <?php
                                    if ($statuses->num_rows > 0) {
                                        while ($status = $statuses->fetch_assoc()) {
                                            echo "<option value='" . htmlspecialchars($status['USID']) . "'>" . htmlspecialchars(ucfirst(str_replace('_', ' ', $status['status_name']))) . "</option>";
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
                                            echo "<option value='" . htmlspecialchars($payment['PID']) . "'>Payment #" . htmlspecialchars($payment['PID']) . " (Token: " . htmlspecialchars($payment['token']) . ")</option>";
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
                                            echo "<option value='" . htmlspecialchars($address['AID']) . "'>" . htmlspecialchars($address['address_display']) . "</option>";
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
                                Create User
                            </button>
                        </div>
                    </form>
                </div>

                <div class="info-card">
                    <div class="info-header">
                        <span class="info-icon">💡</span>
                        <h3>User Creation Tips</h3>
                    </div>
                    <ul class="info-list">
                        <li>Username must be unique in the system</li>
                        <li>Password will be securely hashed before storage</li>
                        <li>User ID (UID) will be auto-generated as UUID</li>
                        <li>Payment and Address are optional references</li>
                        <li>Set status to "Active" for immediate access</li>
                    </ul>

                    <div class="section-separator-minor"></div>

                    <div class="info-header">
                        <span class="info-icon">🔐</span>
                        <h3>Role Permissions</h3>
                    </div>
                    <div class="role-descriptions">
                        <div class="role-desc">
                            <strong>Admin:</strong> Full system access and management capabilities
                        </div>
                        <div class="role-desc">
                            <strong>Individual:</strong> Standard customer with personal account
                        </div>
                        <div class="role-desc">
                            <strong>Enterprise:</strong> Business customer with bulk ordering
                        </div>
                    </div>

                    <div class="section-separator-minor"></div>

                    <div class="info-header">
                        <span class="info-icon">📋</span>
                        <h3>Account Statuses</h3>
                    </div>
                    <div class="role-descriptions">
                        <div class="role-desc">
                            <strong>Active:</strong> User can access the system normally
                        </div>
                        <div class="role-desc">
                            <strong>Inactive:</strong> Account is disabled
                        </div>
                        <div class="role-desc">
                            <strong>Pending Activation:</strong> Awaiting email confirmation
                        </div>
                        <div class="role-desc">
                            <strong>Locked:</strong> Account locked due to security
                        </div>
                        <div class="role-desc">
                            <strong>Suspended:</strong> Temporarily suspended
                        </div>
                        <div class="role-desc">
                            <strong>Password Expired:</strong> Requires password reset
                        </div>
                    </div>

                    <div class="warning-box">
                        <div class="warning-header">
                            <span>⚠️</span>
                            <span>Important</span>
                        </div>
                        <p>Admin accounts have full system access. Only create admin accounts for trusted personnel.</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Password confirmation validation
        const form = document.querySelector('.add-user-form');
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');

        form.addEventListener('submit', function(e) {
            if (password.value !== confirmPassword.value) {
                e.preventDefault();
                alert('Passwords do not match!');
                confirmPassword.focus();
            }
        });

        // Show password strength indicator
        password.addEventListener('input', function() {
            const strength = getPasswordStrength(this.value);
            // You can add visual feedback here if needed
        });

        function getPasswordStrength(password) {
            let strength = 0;
            if (password.length >= 8) strength++;
            if (password.length >= 12) strength++;
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
            if (/\d/.test(password)) strength++;
            if (/[^a-zA-Z\d]/.test(password)) strength++;
            return strength;
        }
    </script>
</body>
</html>