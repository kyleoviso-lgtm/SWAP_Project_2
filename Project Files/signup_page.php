<!DOCTYPE html>
<html lang="en">
<?php
// Start session for CSRF token
require_once 'bootstrap.php';
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Store</title>
    <link rel="stylesheet" href="css/signup_page.css">
</head>
<body>
    <div class="signup-container">
        <div class="logo-section">
            <div class="logo">S</div>
            <h1>Create Account</h1>
            <p>Join us today and start shopping!</p>
        </div>

        <?php if (isset($_GET['status'])): ?>
            <?php if ($_GET['status'] === 'success_signup'): ?>
                <div class="success-banner">
                    ✓ Account created successfully! Please check your email to activate your account.
                </div>
            <?php elseif ($_GET['status'] === 'error_missing_fields'): ?>
                <div class="error-banner">
                    ⚠ Please fill in all required fields.
                </div>
            <?php elseif ($_GET['status'] === 'error_invalid_email'): ?>
                <div class="error-banner">
                    ⚠ Please enter a valid email address.
                </div>
            <?php elseif ($_GET['status'] === 'error_password_mismatch'): ?>
                <div class="error-banner">
                    ⚠ Passwords do not match. Please try again.
                </div>
            <?php elseif ($_GET['status'] === 'error_duplicate_user'): ?>
                <div class="error-banner">
                    ⚠ An account with this username or email already exists.
                </div>
            <?php else: ?>
                <div class="error-banner">
                    ⚠ An error occurred. Please try again.
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <form method="POST" action="process_files/process_add_user.php">
            <input type="hidden" name="source" value="signup">
            
            <div class="form-group">
                <label for="username">Username <span class="required">*</span></label>
                <input 
                    type="text" 
                    id="username" 
                    name="username" 
                    placeholder="Enter your username"
                    maxlength="45"
                    required
                    value="<?php echo isset($_GET['username']) ? htmlspecialchars($_GET['username']) : ''; ?>"
                >
                <span class="helper-text">This will be your display name (max 45 characters)</span>
            </div>

            <div class="form-group">
                <label for="email">Email Address <span class="required">*</span></label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    placeholder="your.email@example.com"
                    maxlength="100"
                    required
                    value="<?php echo isset($_GET['email']) ? htmlspecialchars($_GET['email']) : ''; ?>"
                >
                <span class="helper-text">We'll send a confirmation to this email</span>
            </div>

            <div class="form-group">
                <label for="password">Password <span class="required">*</span></label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    placeholder="Enter a strong password"
                    minlength="8"
                    required
                >
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password <span class="required">*</span></label>
                <input 
                    type="password" 
                    id="confirm_password" 
                    name="confirm_password" 
                    placeholder="Re-enter your password"
                    required
                >
            </div>

            <div class="password-requirements">
                <h4>Password Requirements</h4>
                <ul>
                    <li>At least 8 characters long</li>
                    <li>Use a mix of letters and numbers</li>
                    <li>Avoid common passwords</li>
                </ul>
            </div>

            <button type="submit" class="btn-submit">Create Account</button>
        </form>

        <div class="divider">
            <span>Already have an account?</span>
        </div>

        <div class="login-link">
            <a href="login_page.php">Log in instead</a>
        </div>

        <div class="terms-text">
            By creating an account, you agree to our <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>
        </div>
    </div>

    <script>
        // Client-side password validation
        const form = document.querySelector('form');
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');

        form.addEventListener('submit', function(e) {
            if (password.value !== confirmPassword.value) {
                e.preventDefault();
                alert('Passwords do not match!');
                confirmPassword.focus();
            }
        });

        // Optional: Real-time password match indicator
        confirmPassword.addEventListener('input', function() {
            if (this.value && password.value !== this.value) {
                this.style.borderColor = '#ed4245';
            } else if (this.value && password.value === this.value) {
                this.style.borderColor = '#43b581';
            } else {
                this.style.borderColor = '#1a1d20';
            }
        });
    </script>
</body>
</html>