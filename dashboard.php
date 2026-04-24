<?php
require_once 'includes/functions.php';
require_once 'includes/User.php';
require_once 'includes/Posting.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

$userModel = new User();
$postingModel = new Posting();

// Get user data
$user = $userModel->findById($_SESSION['user_id']);

// Get user's postings
$userPostings = $postingModel->findByUserId($_SESSION['user_id']);

// Get total count of user's postings
$totalPostings = count($userPostings);

// Calculate active and inactive postings
$activePostings = array_filter($userPostings, function($posting) {
    return $posting['status'] === 'active';
});
$activeCount = count($activePostings);

$inactivePostings = array_filter($userPostings, function($posting) {
    return $posting['status'] === 'inactive';
});
$inactiveCount = count($inactivePostings);

// Calculate total revenue (if applicable)
$totalRevenue = 0;
foreach ($userPostings as $posting) {
    $totalRevenue += $posting['price'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo APP_NAME; ?></title>
     <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time() + 1000; ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<style>
    .stats-grid {
    grid-template-columns: repeat(4, 1fr);
    gap: 10px;
}
</style>
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
                    <li><a href="add-posting.php" class="btn btn-primary">Add Posting</a></li>
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
            <li><a href="add-posting.php"><i class="fas fa-plus-circle"></i> Add Posting</a></li>
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

    <!-- Dashboard -->
    <section class="dashboard">
        <div class="container">
            <div class="dashboard-header">
                <h2>Welcome Back, <?php echo $user['name']; ?>!</h2>
                <p>Here's what's happening with your account</p>
            </div>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <i class="fas fa-list"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $totalPostings; ?></h3>
                        <p>Total Postings</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $activeCount; ?></h3>
                        <p>Active Postings</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $inactiveCount; ?></h3>
                        <p>Inactive Postings</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-content">
                        <h3>$<?php echo number_format($totalRevenue, 2); ?></h3>
                        <p>Total Revenue</p>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <h3>Quick Actions</h3>
                <div class="actions-grid">
                    <a href="add-posting.php" class="action-card">
                        <i class="fas fa-plus-circle"></i>
                        <h4>Add New Posting</h4>
                        <p>Create a new item listing</p>
                    </a>
                    <a href="my-postings.php" class="action-card">
                        <i class="fas fa-edit"></i>
                        <h4>Manage Postings</h4>
                        <p>Edit or delete your listings</p>
                    </a>
                    <a href="profile.php" class="action-card">
                        <i class="fas fa-user-edit"></i>
                        <h4>Edit Profile</h4>
                        <p>Update your personal information</p>
                    </a>
                    <a href="index.php" class="action-card">
                        <i class="fas fa-search"></i>
                        <h4>Browse Items</h4>
                        <p>Explore available postings</p>
                    </a>
                </div>
            </div>

            <!-- Recent Postings -->
            <div class="recent-postings">
                <div class="section-header">
                    <h3>Recent Postings</h3>
                    <a href="my-postings.php" class="btn btn-secondary">View All</a>
                </div>
                <div class="postings-list">
                    <?php foreach (array_slice($userPostings, 0, 5) as $posting): ?>
                        <div class="posting-item">
                            <div class="posting-info">
                                <div class="posting-title"><?php echo $posting['title']; ?></div>
                                <div class="posting-meta">
                                    <span class="category"><?php echo $posting['category']; ?></span>
                                    <span class="price">$<?php echo number_format($posting['price'], 2); ?></span>
                                    <span class="status status-<?php echo $posting['status']; ?>">
                                        <?php echo ucfirst($posting['status']); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="posting-actions">
                                <a href="edit-posting.php?id=<?php echo $posting['id']; ?>" class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="delete-posting.php?id=<?php echo $posting['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this posting?');">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <?php if (empty($userPostings)): ?>
                        <div class="no-postings">
                            <i class="fas fa-inbox"></i>
                            <h4>No Postings Yet</h4>
                            <p>Create your first posting to start selling</p>
                            <a href="add-posting.php" class="btn btn-primary">Create Posting</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Account Settings -->
            <div class="account-settings">
                <h3>Account Settings</h3>
                <div class="settings-grid">
                    <div class="setting-item">
                        <div class="setting-info">
                            <h4>Email Notification</h4>
                            <p>Receive email updates about your postings</p>
                        </div>
                        <div class="setting-toggle">
                            <label class="switch">
                                <input type="checkbox" checked>
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>
                    <div class="setting-item">
                        <div class="setting-info">
                            <h4>SMS Notification</h4>
                            <p>Receive SMS updates about your postings</p>
                        </div>
                        <div class="setting-toggle">
                            <label class="switch">
                                <input type="checkbox">
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>
                    <div class="setting-item">
                        <div class="setting-info">
                            <h4>Privacy Settings</h4>
                            <p>Control who can see your postings</p>
                        </div>
                        <div class="setting-action">
                            <a href="privacy-settings.php" class="btn btn-secondary">Manage</a>
                        </div>
                    </div>
                    <div class="setting-item">
                        <div class="setting-info">
                            <h4>Change Password</h4>
                            <p>Update your account password</p>
                        </div>
                        <div class="setting-action">
                            <a href="change-password.php" class="btn btn-secondary">Change</a>
                        </div>
                    </div>
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