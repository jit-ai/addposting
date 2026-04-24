<?php
require_once '../includes/functions.php';
require_once '../includes/User.php';
require_once '../includes/Posting.php';
require_once '../includes/database.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

$userModel = new User();
$postingModel = new Posting();
$db = new Database();
$conn = $db->getConnection();

// Get statistics
$totalUsers = $userModel->count();
$totalPostings = $postingModel->count();
$totalRevenue = $conn->query("SELECT SUM(price) as total FROM postings WHERE status = 'active'")->fetch_assoc()['total'];
$totalCategories = $conn->query("SELECT COUNT(DISTINCT category) as count FROM postings")->fetch_assoc()['count'];

// Get recent users
$recentUsers = $conn->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);

// Get recent postings
$recentPostings = $postingModel->getRecent(5);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .admin-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 3rem;
        }

        .admin-stat-card {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .admin-stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }

        .admin-stat-card .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            margin-bottom: 1rem;
        }

        .admin-stat-card h3 {
            font-size: 2rem;
            color: #2d3748;
            margin-bottom: 0.25rem;
        }

        .admin-stat-card p {
            color: #718096;
            font-size: 0.9rem;
        }

        .admin-section {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .admin-section h3 {
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            color: #2d3748;
        }

        .admin-table {
            width: 100%;
            border-collapse: collapse;
        }

        .admin-table th,
        .admin-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        .admin-table th {
            background: #f8f9fa;
            font-weight: bold;
            color: #2d3748;
        }

        .admin-table tbody tr:hover {
            background: #f8f9fa;
        }

        .admin-actions {
            display: flex;
            gap: 10px;
        }

        .btn-small {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }

        .status-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
        }

        .status-active {
            background: #d4edda;
            color: #155724;
        }

        .status-inactive {
            background: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="admin-sidebar">
        <div class="logo">
            <i class="fas fa-crown"></i>
            <h1>Admin Panel</h1>
        </div>
        <nav>
            <ul>
                <li><a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="users.php"><i class="fas fa-users"></i> Users</a></li>
                <li><a href="postings.php"><i class="fas fa-list"></i> Postings</a></li>
                <li><a href="categories.php"><i class="fas fa-tags"></i> Categories</a></li>
                <li><a href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </nav>
    </div>

    <!-- Header -->
    <div class="admin-header">
        <div style="display: flex; align-items: center;">
            <button class="sidebar-toggle" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
            <h2>Admin Dashboard</h2>
        </div>
        <div class="user-info">
            <div class="avatar"><?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?></div>
            <span><?php echo $_SESSION['user_name']; ?></span>
        </div>
    </div>

    <!-- Main Content -->
    <div class="admin-main">
        <div class="main-content">
            <!-- Stats Cards -->
            <div class="admin-stats">
                <div class="admin-stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3><?php echo $totalUsers; ?></h3>
                    <p>Total Users</p>
                </div>

                <div class="admin-stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                        <i class="fas fa-list"></i>
                    </div>
                    <h3><?php echo $totalPostings; ?></h3>
                    <p>Total Postings</p>
                </div>

                <div class="admin-stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                        <i class="fas fa-tag"></i>
                    </div>
                    <h3><?php echo $totalCategories; ?></h3>
                    <p>Total Categories</p>
                </div>

                <div class="admin-stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                        <i class="fas fa-indian-rupe-sign"></i>
                    </div>
                    <h3>₹<?php echo number_format($totalRevenue, 2); ?></h3>
                    <p>Total Revenue</p>
                </div>
            </div>

            <!-- Recent Users -->
            <div class="admin-section">
                <h3>Recent Users</h3>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentUsers as $user): ?>
                            <tr>
                                <td><?php echo $user['name']; ?></td>
                                <td><?php echo $user['email']; ?></td>
                                <td><?php echo ucfirst($user['role']); ?></td>
                                <td><span class="status-badge status-<?php echo $user['status']; ?>"><?php echo ucfirst($user['status']); ?></span></td>
                                <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <div class="admin-actions">
                                        <a href="edit-user.php?id=<?php echo $user['id']; ?>" class="btn btn-small btn-primary">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href="delete-user.php?id=<?php echo $user['id']; ?>" class="btn btn-small btn-danger" onclick="return confirm('Are you sure you want to delete this user?');">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Recent Postings -->
            <div class="admin-section">
                <h3>Recent Postings</h3>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentPostings as $posting): ?>
                            <tr>
                                <td><?php echo $posting['title']; ?></td>
                                <td><?php echo $posting['category']; ?></td>
                                <td>₹<?php echo number_format($posting['price'], 2); ?></td>
                                <td><?php echo $posting['location']; ?></td>
                                <td><span class="status-badge status-<?php echo $posting['status']; ?>"><?php echo ucfirst($posting['status']); ?></span></td>
                                <td><?php echo date('M d, Y', strtotime($posting['created_at'])); ?></td>
                                <td>
                                    <div class="admin-actions">
                                        <a href="edit-posting.php?id=<?php echo $posting['id']; ?>" class="btn btn-small btn-primary">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href="delete-posting.php?id=<?php echo $posting['id']; ?>" class="btn btn-small btn-danger" onclick="return confirm('Are you sure you want to delete this posting?');">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
    <script>
        // Sidebar Toggle Functionality
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.getElementById('sidebarToggle');
            const adminSidebar = document.querySelector('.admin-sidebar');
            
            if (sidebarToggle && adminSidebar) {
                sidebarToggle.addEventListener('click', function() {
                    // Toggle collapsed class on sidebar
                    adminSidebar.classList.toggle('collapsed');
                    
                    // Toggle body class for main content adjustment
                    document.body.classList.toggle('sidebar-collapsed');
                    
                    // For mobile - toggle mobile-open class
                    if (window.innerWidth <= 768) {
                        adminSidebar.classList.toggle('mobile-open');
                    }
                    
                    // Update icon
                    const icon = sidebarToggle.querySelector('i');
                    if (adminSidebar.classList.contains('collapsed')) {
                        icon.classList.remove('fa-bars');
                        icon.classList.add('fa-chevron-left');
                    } else {
                        icon.classList.remove('fa-chevron-left');
                        icon.classList.add('fa-bars');
                    }
                });
            }
            
            // Close mobile sidebar when clicking outside
            document.addEventListener('click', function(e) {
                const adminSidebar = document.querySelector('.admin-sidebar');
                if (window.innerWidth <= 768 && 
                    adminSidebar && 
                    adminSidebar.classList.contains('mobile-open') &&
                    !adminSidebar.contains(e.target) &&
                    !sidebarToggle.contains(e.target)) {
                    adminSidebar.classList.remove('mobile-open');
                }
            });
        });
    </script>
</body>
</html>