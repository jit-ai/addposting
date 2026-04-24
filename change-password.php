<?php
require_once 'includes/functions.php';
require_once 'includes/User.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

$userModel = new User();

// Get user data
$user = $userModel->findById($_SESSION['user_id']);

// Handle form submission
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate input
    $currentPassword = sanitize($_POST['current_password']);
    $newPassword = sanitize($_POST['new_password']);
    $confirmPassword = sanitize($_POST['confirm_password']);

    if (empty($currentPassword)) {
        $errors[] = 'Current password is required';
    }

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

    // Verify current password
    if (empty($errors)) {
        if (!verifyPassword($currentPassword, $user['password'])) {
            $errors[] = 'Current password is incorrect';
        }
    }

    // If no errors, update password
    if (empty($errors)) {
        if ($userModel->updatePassword($user['email'], $newPassword)) {
            $success = 'Password updated successfully!';
            // Clear form
            $_POST = [];
        } else {
            $errors[] = 'Failed to update password. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .change-password {
            padding: 2rem 0;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .page-header h1 {
            font-size: 2.5rem;
            color: #2d3748;
            margin: 0;
        }

        .form-container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin: 0 auto;
        }

        .form-container h2 {
            color: #2d3748;
            margin-bottom: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
            color: #2d3748;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e2e8f0;
            border-radius: 5px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .password-strength {
            margin-top: 0.5rem;
            height: 5px;
            border-radius: 3px;
            background: #e2e8f0;
            display: none;
        }

        .password-strength-bar {
            height: 100%;
            border-radius: 3px;
            transition: all 0.3s ease;
        }

        .password-strength-weak {
            width: 33%;
            background: #dc3545;
        }

        .password-strength-medium {
            width: 66%;
            background: #ffc107;
        }

        .password-strength-strong {
            width: 100%;
            background: #28a745;
        }

        .password-strength-text {
            font-size: 0.875rem;
            margin-top: 0.25rem;
            display: none;
        }

        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
        }
    </style>
</head>
<body>
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
            <li><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
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

    <!-- Change Password -->
    <section class="change-password">
        <div class="container">
            <div class="page-header">
                <h1>Change Password</h1>
                <a href="profile.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Profile
                </a>
            </div>

            <div class="form-container">
                <h2>Update Your Password</h2>
                
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

                <form method="POST" action="change-password.php">
                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <input type="password" id="current_password" name="current_password" required placeholder="Enter your current password">
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password" required placeholder="Enter your new password">
                        <div class="password-strength">
                            <div class="password-strength-bar"></div>
                        </div>
                        <div class="password-strength-text"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required placeholder="Confirm your new password">
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary btn-block">Update Password</button>
                    </div>
                </form>
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
    <script>
        // Password strength checker
        document.addEventListener('DOMContentLoaded', function() {
            const newPasswordInput = document.getElementById('new_password');
            const passwordStrength = document.querySelector('.password-strength');
            const passwordStrengthBar = document.querySelector('.password-strength-bar');
            const passwordStrengthText = document.querySelector('.password-strength-text');

            newPasswordInput.addEventListener('input', function() {
                const password = this.value;
                const strength = checkPasswordStrength(password);
                
                passwordStrength.style.display = 'block';
                passwordStrengthText.style.display = 'block';
                
                // Remove existing classes
                passwordStrengthBar.classList.remove('password-strength-weak', 'password-strength-medium', 'password-strength-strong');
                
                // Add strength class
                switch (strength) {
                    case 'weak':
                        passwordStrengthBar.classList.add('password-strength-weak');
                        passwordStrengthText.textContent = 'Weak';
                        passwordStrengthText.style.color = '#dc3545';
                        break;
                    case 'medium':
                        passwordStrengthBar.classList.add('password-strength-medium');
                        passwordStrengthText.textContent = 'Medium';
                        passwordStrengthText.style.color = '#ffc107';
                        break;
                    case 'strong':
                        passwordStrengthBar.classList.add('password-strength-strong');
                        passwordStrengthText.textContent = 'Strong';
                        passwordStrengthText.style.color = '#28a745';
                        break;
                    default:
                        passwordStrength.style.display = 'none';
                        passwordStrengthText.style.display = 'none';
                        break;
                }
            });

            function checkPasswordStrength(password) {
                if (password.length < 6) {
                    return '';
                }
                
                let strength = 'weak';
                
                // Check for numbers
                if (/\d/.test(password)) {
                    strength = 'medium';
                }
                
                // Check for special characters
                if (/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)) {
                    strength = 'strong';
                }
                
                // Check for both uppercase and lowercase
                if (/[a-z]/.test(password) && /[A-Z]/.test(password)) {
                    strength = strength === 'medium' ? 'strong' : strength;
                }
                
                // Check length
                if (password.length >= 10) {
                    strength = strength === 'medium' ? 'strong' : strength;
                }
                
                return strength;
            }
        });
    </script>
</body>
</html>