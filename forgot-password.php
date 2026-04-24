<?php
require_once 'includes/functions.php';
require_once 'includes/User.php';

$userModel = new User();
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate email
    $email = sanitize($_POST['email']);

    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    } else {
        $user = $userModel->findByEmail($email);
        
        if (!$user) {
            $errors[] = 'No account found with that email';
        }
    }

    // If no errors, send password reset email
    if (empty($errors)) {
        // Generate reset token
        $resetToken = generateToken();
        $resetExpiry = time() + 3600; // 1 hour
        
        // In a real application, you would store these in a password_resets table
        // For this example, we'll just show the token (not recommended for production)
        $resetLink = APP_URL . '/reset-password.php?email=' . urlencode($email) . '&token=' . $resetToken;
        
        // Send email
        $subject = 'Password Reset Request - ' . APP_NAME;
        $message = "
            <html>
            <body>
                <h1>Password Reset Request</h1>
                <p>You requested a password reset for your account at " . APP_NAME . ".</p>
                <p>Click the link below to reset your password:</p>
                <p><a href=\"" . $resetLink . "\">Reset Password</a></p>
                <p>This link will expire in 1 hour.</p>
                <p>If you didn't request this, you can safely ignore this email.</p>
            </body>
            </html>
        ";
        
        if (sendEmail($email, $subject, $message)) {
            $success = 'A password reset link has been sent to your email address. Please check your inbox.';
        } else {
            $errors[] = 'Failed to send reset email. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="auth-body">
    <!-- Header -->
    <header>
        <div class="container">
            <div class="logo">
                <i class="fas fa-store"></i>
                <h1><?php echo APP_NAME; ?></h1>
            </div>
            <button class="hamburger" aria-label="Toggle menu" aria-expanded="false">
                <i class="fas fa-bars"></i>
            </button>
            <nav>
                <ul>
                    <li><a href="index.php">Home</a></li>            
                    <li><a href="addposting.php">Add Posting</a></li>
                    <?php if (isLoggedIn()): ?>
                        <li><a href="dashboard.php">Dashboard</a></li>
                        <li><a href="logout.php">Logout</a></li>
                    <?php else: ?>
                        <li><a href="login.php">Login</a></li>
                        <li><a href="register.php">Register</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>
    
    <!-- Mobile Navigation -->
    <div class="mobile-nav-overlay"></div>
    <div class="mobile-nav">
        <div class="mobile-nav-header">
            <div class="logo">
                <i class="fas fa-store"></i>
                <span><?php echo APP_NAME; ?></span>
            </div>
            <button class="mobile-nav-close" aria-label="Close menu">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <ul>
            <li><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
            <li><a href="addposting.php"><i class="fas fa-plus-circle"></i> Add Posting</a></li>
            <?php if (isLoggedIn()): ?>
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            <?php else: ?>
                <li><a href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a></li>
                <li><a href="register.php"><i class="fas fa-user-plus"></i> Register</a></li>
            <?php endif; ?>
        </ul>
    </div>

    <!-- Forgot Password Form -->
    <section class="auth-form">
        <div class="container">
            <div class="form-container">
                <h2>Reset Your Password</h2>
                <p>Enter your email address and we'll send you a password reset link</p>
                
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <?php foreach ($errors as $error): ?>
                            <p><?php echo $error; ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success">
                        <p><?php echo $success; ?></p>
                    </div>
                <?php endif; ?>
                
                <?php if (empty($success)): ?>
                    <form method="POST" action="forgot-password.php">
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" required placeholder="Enter your email">
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-block">Send Reset Link</button>
                        </div>
                    </form>
                <?php endif; ?>
                
                <div class="auth-links">
                    <p>Remember your password? <a href="login.php">Login here</a></p>
                    <p>Don't have an account? <a href="register.php">Register here</a></p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p>&copy; 2024 <?php echo APP_NAME; ?>. All rights reserved.</p>
        </div>
    </footer>

    <script src="assets/js/main.js?v=<?php echo time(); ?>"></script>
</body>
</html>