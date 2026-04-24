<?php
require_once 'includes/functions.php';
require_once 'includes/User.php';

$userModel = new User();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate input
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $password = sanitize($_POST['password']);
    $confirmPassword = sanitize($_POST['confirm_password']);

    if (empty($name)) {
        $errors[] = 'Name is required';
    }

    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    } elseif ($userModel->findByEmail($email)) {
        $errors[] = 'Email already exists';
    }

    if (empty($password)) {
        $errors[] = 'Password is required';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters';
    }

    if (empty($confirmPassword)) {
        $errors[] = 'Confirm password is required';
    } elseif ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match';
    }

    // If no errors, create user
    if (empty($errors)) {
        $userId = $userModel->create([
            'name' => $name,
            'email' => $email,
            'password' => $password
        ]);

        if ($userId) {
            // Start session and log user in
            session_start();
            $_SESSION['user_id'] = $userId;
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;
            $_SESSION['role'] = 'user';
            
            redirect('dashboard.php');
        } else {
            $errors[] = 'Failed to create account. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - <?php echo APP_NAME; ?></title>
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

    <!-- Register Form -->
    <section class="auth-form">
        <div class="container">
            <div class="form-container">
                <h2>Create an Account</h2>
                <p>Join our community to buy and sell everything</p>
                
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <?php foreach ($errors as $error): ?>
                            <p><?php echo $error; ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="register.php">
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" required placeholder="Enter your full name">
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required placeholder="Enter your email">
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required placeholder="Enter your password">
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required placeholder="Confirm your password">
                    </div>
                    
                    <div class="form-group terms-group">
                        <input type="checkbox" id="terms" name="terms" required>
                        <label for="terms">I agree to the <a href="terms.php" target="_blank">Terms and Conditions</a> and <a href="privacy.php" target="_blank">Privacy Policy</a></label>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary btn-block">Register</button>
                    </div>
                </form>
                
                <div class="auth-links">
                    <p>Already have an account? <a href="login.php">Login here</a></p>
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