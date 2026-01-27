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
?>

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
                                    required
                                >
                                <span class="helper-text">Username must be unique</span>
                            </div>

                            <div class="form-group">
                                <label for="email">Email Address <span class="required">*</span></label>
                                <input 
                                    type="email" 
                                    id="email" 
                                    name="email" 
                                    placeholder="user@example.com"
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
                                            echo "<option value='" . htmlspecialchars($role['RID']) . "'>" . htmlspecialchars($role['RoleName']) . "</option>";
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
                                            echo "<option value='" . htmlspecialchars($status['USID']) . "'>" . htmlspecialchars(ucfirst($status['status_name'])) . "</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="section-separator-minor"></div>

                        <!-- Optional Information -->
                        <div class="form-section-header">
                            <h3>Additional Information</h3>
                            <span class="optional-label">(Optional)</span>
                        </div>

                        <!-- First Name and Last Name Row -->
                        <div class="form-row">
                            <div class="form-group">
                                <label for="first_name">First Name</label>
                                <input 
                                    type="text" 
                                    id="first_name" 
                                    name="first_name" 
                                    placeholder="Enter first name"
                                >
                            </div>

                            <div class="form-group">
                                <label for="last_name">Last Name</label>
                                <input 
                                    type="text" 
                                    id="last_name" 
                                    name="last_name" 
                                    placeholder="Enter last name"
                                >
                            </div>
                        </div>

                        <!-- Phone Number Row -->
                        <div class="form-row">
                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input 
                                    type="tel" 
                                    id="phone" 
                                    name="phone" 
                                    placeholder="+65 1234 5678"
                                >
                            </div>

                            <div class="form-group">
                                <label for="date_of_birth">Date of Birth</label>
                                <input 
                                    type="date" 
                                    id="date_of_birth" 
                                    name="date_of_birth"
                                >
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="form-row">
                            <div class="form-group full-width">
                                <label for="notes">Admin Notes</label>
                                <textarea 
                                    id="notes" 
                                    name="notes" 
                                    rows="4"
                                    placeholder="Add any internal notes about this user account"
                                ></textarea>
                                <span class="helper-text">These notes are only visible to administrators</span>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="button" class="btn-cancel" onclick="window.location.href='user_management.php'">
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
                        <li>Choose a descriptive username that's easy to remember</li>
                        <li>Use a strong password with mixed characters</li>
                        <li>Assign the appropriate role based on user permissions</li>
                        <li>Set account status to "Active" for immediate access</li>
                        <li>Double-check email address for account notifications</li>
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

