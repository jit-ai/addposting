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
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);

    if (empty($name)) {
        $errors[] = 'Name is required';
    }

    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    } elseif ($email != $user['email'] && $userModel->findByEmail($email)) {
        $errors[] = 'Email already exists';
    }

    // If no errors, update user
    if (empty($errors)) {
        if ($userModel->update($_SESSION['user_id'], [
            'name' => $name,
            'email' => $email
        ])) {
            $success = 'Profile updated successfully!';
            // Refresh user data
            $user = $userModel->findById($_SESSION['user_id']);
            // Update session data
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
        } else {
            $errors[] = 'Failed to update profile. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .profile {
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
            color: #dc3545;
            margin: 0;
        }

        .profile-container {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 2rem;
        }

        .profile-sidebar {
            background: #1e1e1e;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
            height: fit-content;
            border: 1px solid #333;
        }

        .profile-avatar {
            text-align: center;
            margin-bottom: 2rem;
        }

        .avatar-circle {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 3rem;
            color: white;
            font-weight: bold;
        }

        .profile-name {
            font-size: 1.5rem;
            font-weight: bold;
            color: #f0f0f0;
            margin-bottom: 0.5rem;
        }

        .profile-email {
            color: #a0aec0;
            font-size: 0.9rem;
        }

        .profile-menu {
            border-top: 1px solid #333;
            padding-top: 1rem;
        }

        .profile-menu h3 {
            color: #f0f0f0;
            margin-bottom: 1rem;
        }

        .profile-menu ul {
            list-style: none;
        }

        .profile-menu ul li {
            margin-bottom: 0.5rem;
        }

        .profile-menu ul li a {
            color: #a0aec0;
            text-decoration: none;
            padding: 0.75rem 1rem;
            display: block;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .profile-menu ul li a:hover,
        .profile-menu ul li a.active {
            color: white;
            background-color: #dc3545;
        }

        .profile-content {
            background: #1e1e1e;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
            border: 1px solid #333;
        }

        .profile-content h2 {
            color: #dc3545;
            margin-bottom: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
            color: #f0f0f0;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #444;
            border-radius: 5px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #2a2a2a;
            color: #f0f0f0;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #dc3545;
            box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.2);
        }

        .profile-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 2rem;
        }

        .stat-item {
            text-align: center;
            padding: 1.5rem;
            background: #2a2a2a;
            border-radius: 10px;
            border: 1px solid #333;
        }

        .stat-item i {
            font-size: 2rem;
            color: #dc3545;
            margin-bottom: 0.5rem;
        }

        .stat-item .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #f0f0f0;
            margin-bottom: 0.25rem;
        }

        .stat-item .stat-label {
            color: #a0aec0;
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .profile-container {
                grid-template-columns: 1fr;
            }

            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .avatar-circle {
                width: 100px;
                height: 100px;
                font-size: 2rem;
            }

            .profile-stats {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            }
        }
    </style>
</head>
<body class="auth-body">
    <?php include 'includes/header.php'; ?>

    <!-- Profile -->
    <section class="profile">
        <div class="container">
            <div class="page-header">
                <h1>Profile Settings</h1>
                <a href="dashboard.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>

            <div class="profile-container">
                <div class="profile-sidebar">
                    <div class="profile-avatar">
                        <div class="avatar-circle">
                            <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                        </div>
                        <div class="profile-name"><?php echo $user['name']; ?></div>
                        <div class="profile-email"><?php echo $user['email']; ?></div>
                    </div>

                    <div class="profile-menu">
                        <h3>Menu</h3>
                        <ul>
                            <li><a href="profile.php" class="active">
                                <i class="fas fa-user"></i> Personal Information
                            </a></li>
                            <li><a href="change-password.php">
                                <i class="fas fa-key"></i> Change Password
                            </a></li>
                            <li><a href="privacy-settings.php">
                                <i class="fas fa-lock"></i> Privacy Settings
                            </a></li>
                            <li><a href="account-settings.php">
                                <i class="fas fa-cog"></i> Account Settings
                            </a></li>
                        </ul>
                    </div>

                    <div class="profile-stats">
                        <div class="stat-item">
                            <i class="fas fa-list"></i>
                            <div class="stat-number">
                                <?php 
                                require_once 'includes/Posting.php';
                                $postingModel = new Posting();
                                echo count($postingModel->findByUserId($user['id']));
                                ?>
                            </div>
                            <div class="stat-label">Postings</div>
                        </div>
                        <div class="stat-item">
                            <i class="fas fa-clock"></i>
                            <div class="stat-number">
                                <?php echo date('M d, Y', strtotime($user['created_at'])); ?>
                            </div>
                            <div class="stat-label">Member Since</div>
                        </div>
                    </div>
                </div>

                <div class="profile-content">
                    <h2>Personal Information</h2>
                    
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

                    <form method="POST" action="profile.php">
                        <div class="form-group">
                            <label for="name">Full Name</label>
                            <input type="text" id="name" name="name" required placeholder="Enter your full name" value="<?php echo isset($_POST['name']) ? $_POST['name'] : $user['name']; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" required placeholder="Enter your email" value="<?php echo isset($_POST['email']) ? $_POST['email'] : $user['email']; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="role">Role</label>
                            <input type="text" id="role" value="<?php echo ucfirst($user['role']); ?>" disabled>
                        </div>
                        
                        <div class="form-group">
                            <label for="status">Status</label>
                            <input type="text" id="status" value="<?php echo ucfirst($user['status']); ?>" disabled>
                        </div>
                        
                        <div class="form-group">
                            <label for="created_at">Created At</label>
                            <input type="text" id="created_at" value="<?php echo date('F d, Y H:i:s', strtotime($user['created_at'])); ?>" disabled>
                        </div>
                        
                        <div class="form-group">
                            <label for="updated_at">Updated At</label>
                            <input type="text" id="updated_at" value="<?php echo date('F d, Y H:i:s', strtotime($user['updated_at'])); ?>" disabled>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Update Profile</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <script src="assets/js/main.js?v=<?php echo time(); ?>"></script>
</body>
</html>