<?php
// Start session for CSRF token
require_once 'bootstrap.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Store Login</title>
    <link rel="stylesheet" href="css/login_page.css">
</head>
<body>
    <div class="container">
        <div class="branding-section">
            <div class="branding-content">
                <div class="logo">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none">
                        <path d="M12 2L2 7V17L12 22L22 17V7L12 2Z" fill="#5865f2"/>
                    </svg>
                </div>
                <h1>Welcome Back!</h1>
                <p>Sign in to access your store account and continue shopping.</p>
                <div class="features">
                    <div class="feature-item">
                        <span class="check-icon">✓</span>
                        <span>Track your orders</span>
                    </div>
                    <div class="feature-item">
                        <span class="check-icon">✓</span>
                        <span>Purchase manufacturing orders</span>
                    </div>
                    <div class="feature-item">
                        <span class="check-icon">✓</span>
                        <span>Exclusive member deals</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-section">
            <div class="form-container">

                <!-- ========================= -->
                <!-- STATUS BANNERS -->
                <!-- ========================= -->
                <?php if (isset($_GET['status'])): ?>
                    <?php
                        $status = $_GET['status'];
                        $bannerClass = str_starts_with($status, 'success')
                            ? 'success-banner'
                            : 'error-banner';

                        $message = '';

                        switch ($status) {
                            // --- Success Messages ---
                            case 'success_signup':
                                $message = '✅ Account created successfully! Please log in.';
                                break;
                            
                            case 'logged_out':
                                $message = '✅ You have been logged out successfully.';
                                break;

                            case 'password_reset_success':
                                $message = '✅ Password reset successful! Please log in with your new password.';
                                break;

                            // --- Login Errors ---
                            case 'error_invalid_credentials':
                                $message = '⚠️ Invalid email or password. Please try again.';
                                break;

                            case 'error_account_inactive':
                                $message = '⚠️ Your account is not active. Please check your email for activation instructions.';
                                break;

                            case 'error_missing_fields':
                                $message = '⚠️ Please fill in all required fields.';
                                break;

                            case 'error_csrf_invalid':
                                $message = '⚠️ Invalid security token. Please try again.';
                                break;

                            case 'error_unknown_role':
                                $message = '⚠️ Your account role is not recognized. Please contact support.';
                                break;

                            // --- General Errors ---
                            case 'error_invalid_request':
                                $message = '⚠️ Invalid request method.';
                                break;

                            case 'error_db':
                                $message = '⚠️ A database error occurred. Please try again later.';
                                break;

                            // --- Session/Auth Errors ---
                            case 'error_not_logged_in':
                                $message = '⚠️ Please log in to continue.';
                                break;

                            case 'error_session_expired':
                                $message = '⚠️ Your session has expired. Please log in again.';
                                break;

                            default:
                                $message = '⚠️ An unexpected error occurred.';
                                break;
                        }
                    ?>
                    <div class="<?php echo $bannerClass; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <!-- ========================= -->
                <!-- FORM HEADER -->
                <!-- ========================= -->
                <div class="form-header">
                    <h2>Sign In</h2>
                    <p>Enter your credentials to continue</p>
                </div>

                <!-- ========================= -->
                <!-- LOGIN FORM -->
                <!-- ========================= -->
                <form class="login-form" action="process_files/process_login.php" method="POST">
                    <!-- CSRF Token -->
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            placeholder="you@example.com"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            placeholder="Enter your password"
                            required
                        >
                    </div>

                    <div class="form-options">
                        <label class="checkbox-container">
                            <input type="checkbox" name="remember">
                            <span class="checkbox-label">Remember me</span>
                        </label>
                        <a href="forgot_password.php" class="forgot-link">Forgot password?</a>
                    </div>

                    <button type="submit" class="btn-primary">Sign In</button>

                    <div class="divider">
                        <span>OR</span>
                    </div>

                    <button type="button" class="btn-secondary">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                        </svg>
                        Continue with Google
                    </button>
                </form>

                <!-- ========================= -->
                <!-- FOOTER -->
                <!-- ========================= -->
                <div class="form-footer">
                    <p>Don't have an account? <a href="signup_page.php">Sign up</a></p>
                </div>

            </div>
        </div>
    </div>
</body>
</html>