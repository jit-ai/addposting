<?php
require_once 'includes/functions.php';
require_once 'includes/User.php';

$userModel = new User();
$errors = [];
$success = '';

// Check if email and token are provided
if (!isset($_GET['email']) || !isset($_GET['token'])) {
    $errors[] = 'Invalid reset link';
} else {
    $email = sanitize($_GET['email']);
    $token = sanitize($_GET['token']);
    
    // Check if email exists
    $user = $userModel->findByEmail($email);
    if (!$user) {
        $errors[] = 'Invalid email address';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($errors)) {
    // Validate input
    $newPassword = sanitize($_POST['new_password']);
    $confirmPassword = sanitize($_POST['confirm_password']);

    if (empty($newPassword)) {
        $errors[] = 'New password is required';
    } elseif (strlen($newPassword) < 6) {
        $errors[] = 'Password must be at least 6 characters';
    }

    if (empty($confirmPassword)) {
        $errors[] = 'Confirm password is required';
    } elseif ($newPassword !== $confirmPassword) {
        $errors[] = 'Passwords do not match';
    }

    // If no errors, update password
    if (empty($errors)) {
        if ($userModel->updatePassword($email, $newPassword)) {
            $success = 'Your password has been successfully reset. You can now login with your new password.';
        } else {
            $errors[] = 'Failed to reset password. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time() + 1000; ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="auth-body">
    <header>
        <div class="container">
            <div class="logo">
                <a href="index.php" style="display: flex; align-items: center; text-decoration: none; color: inherit;">
                    <i class="fas fa-store"></i>
                    <h1><?php echo APP_NAME; ?></h1>
                </a>
            </div>
            <button class="hamburger" aria-label="Toggle menu" aria-expanded="false">
                <i class="fas fa-bars"></i>
            </button>
            <nav>
                <ul>
                    <li><a href="index.php" class="active">Home</a></li>
                    <li><a href="addposting.php" class="btn btn-primary">Add Posting</a></li>
                    <?php if (isLoggedIn()): ?>
                        <li class="dropdown">
                            <a href="#"><i class="fas fa-user"></i> My Account</a>
                            <div class="dropdown-content">
                                <a href="dashboard.php">Dashboard</a>
                                <a href="my-postings.php">My Postings</a>
                                <a href="profile.php">Profile</a>
                                <a href="logout.php">Logout</a>
                            </div>
                        </li>
                    <?php else: ?>
                        <li><a href="login.php">Login</a></li>
                        <li><a href="register.php">Register</a></li>
                    <?php endif; ?>
                    <?php if (isAdmin()): ?>
                        <li><a href="admin/dashboard.php" class="btn btn-danger">Admin Dashboard</a></li>
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
        <?php if (isLoggedIn()): ?>
        <div class="mobile-nav-user">
            <i class="fas fa-user-circle"></i> My Account
        </div>
        <?php endif; ?>
        <ul>
            <li><a href="index.php" class="active"><i class="fas fa-home"></i> Home</a></li>
            <li><a href="addposting.php"><i class="fas fa-plus-circle"></i> Add Posting</a></li>
            <?php if (isLoggedIn()): ?>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle"><i class="fas fa-user"></i> My Account</a>
                    <div class="dropdown-content">
                        <a href="dashboard.php">Dashboard</a>
                        <a href="my-postings.php">My Postings</a>
                        <a href="profile.php">Profile</a>
                        <a href="logout.php">Logout</a>
                    </div>
                </li>
            <?php else: ?>
                <li><a href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a></li>
                <li><a href="register.php"><i class="fas fa-user-plus"></i> Register</a></li>
            <?php endif; ?>
            <?php if (isAdmin()): ?>
                <li><a href="admin/dashboard.php"><i class="fas fa-cog"></i> Admin Dashboard</a></li>
            <?php endif; ?>
        </ul>
    </div>

    <!-- Reset Password Form -->
    <section class="auth-form">
        <div class="container">
            <div class="form-container">
                <h2>Reset Your Password</h2>
                
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
                        <p><a href="login.php" class="btn btn-primary">Login Now</a></p>
                    </div>
                <?php elseif (empty($errors)): ?>
                    <form method="POST" action="reset-password.php?email=<?php echo urlencode($email); ?>&token=<?php echo urlencode($token); ?>">
                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <input type="password" id="new_password" name="new_password" required placeholder="Enter your new password">
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Confirm Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" required placeholder="Confirm your new password">
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-block">Reset Password</button>
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