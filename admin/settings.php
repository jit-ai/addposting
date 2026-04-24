<?php
require_once '../includes/functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

// Handle form submission
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if it's a general settings update
    if (isset($_POST['general_settings'])) {
        // In a real application, these settings would be stored in the database
        $siteName = sanitize($_POST['site_name']);
        $siteDescription = sanitize($_POST['site_description']);
        $contactEmail = sanitize($_POST['contact_email']);
        $contactPhone = sanitize($_POST['contact_phone']);

        if (empty($siteName)) {
            $errors[] = 'Site name is required';
        }

        if (empty($siteDescription)) {
            $errors[] = 'Site description is required';
        }

        if (empty($contactEmail)) {
            $errors[] = 'Contact email is required';
        } elseif (!filter_var($contactEmail, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format';
        }

        if (empty($contactPhone)) {
            $errors[] = 'Contact phone is required';
        }

        if (empty($errors)) {
            $success = 'General settings updated successfully!';
        }
    }
}

// In a real application, these settings would be retrieved from the database
$settings = [
    'site_name' => 'Add Posting',
    'site_description' => 'A platform to share and discover postings',
    'contact_email' => 'contact@addposting.com',
    'contact_phone' => '+1 (555) 123-4567',
    'max_file_size' => 5, // MB
    'max_images' => 5,
    'default_category' => 'Other',
    'new_user_status' => 'active',
    'new_posting_status' => 'pending',
    'email_notifications' => true,
    'sms_notifications' => false,
    'require_email_verification' => false
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .page-header h1 {
            font-size: 2rem;
            color: #2d3748;
            margin: 0;
        }

        .settings-container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .settings-section {
            margin-bottom: 2rem;
            padding-bottom: 2rem;
            border-bottom: 1px solid #e2e8f0;
        }

        .settings-section:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }

        .settings-section h2 {
            color: #2d3748;
            margin-bottom: 1.5rem;
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

        .form-check {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-check input[type="checkbox"] {
            width: auto;
            transform: scale(1.2);
        }

        .btn-block {
            width: 100%;
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
    <!-- Sidebar -->
    <div class="admin-sidebar">
        <div class="logo">
            <i class="fas fa-crown"></i>
            <h1>Admin Panel</h1>
        </div>
        <nav>
            <ul>
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="users.php"><i class="fas fa-users"></i> Users</a></li>
                <li><a href="postings.php"><i class="fas fa-list"></i> Postings</a></li>
                <li><a href="categories.php"><i class="fas fa-tags"></i> Categories</a></li>
                <li><a href="settings.php" class="active"><i class="fas fa-cog"></i> Settings</a></li>
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
            <h2>Settings</h2>
        </div>
        <div class="user-info">
            <div class="avatar"><?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?></div>
            <span><?php echo $_SESSION['user_name']; ?></span>
        </div>
    </div>

    <!-- Main Content -->
    <div class="admin-main">
        <div class="main-content">
            <div class="page-header">
                <h1>Settings</h1>
            </div>

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

            <div class="settings-container">
                <!-- General Settings -->
                <div class="settings-section">
                    <h2>General Settings</h2>
                    <form method="POST" action="settings.php">
                        <input type="hidden" name="general_settings" value="1">
                        
                        <div class="form-group">
                            <label for="siteName">Site Name</label>
                            <input type="text" id="siteName" name="site_name" required placeholder="Enter site name" value="<?php echo $settings['site_name']; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="siteDescription">Site Description</label>
                            <textarea id="siteDescription" name="site_description" rows="3" required placeholder="Enter site description"><?php echo $settings['site_description']; ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="contactEmail">Contact Email</label>
                            <input type="email" id="contactEmail" name="contact_email" required placeholder="Enter contact email" value="<?php echo $settings['contact_email']; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="contactPhone">Contact Phone</label>
                            <input type="text" id="contactPhone" name="contact_phone" required placeholder="Enter contact phone" value="<?php echo $settings['contact_phone']; ?>">
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-block">Update General Settings</button>
                        </div>
                    </form>
                </div>

                <!-- Posting Settings -->
                <div class="settings-section">
                    <h2>Posting Settings</h2>
                    <form method="POST" action="settings.php">
                        <input type="hidden" name="posting_settings" value="1">
                        
                        <div class="form-group">
                            <label for="maxFileSize">Maximum File Size (MB)</label>
                            <input type="number" id="maxFileSize" name="max_file_size" required placeholder="Enter maximum file size" value="<?php echo $settings['max_file_size']; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="maxImages">Maximum Images per Posting</label>
                            <input type="number" id="maxImages" name="max_images" required placeholder="Enter maximum images" value="<?php echo $settings['max_images']; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="defaultCategory">Default Category</label>
                            <select id="defaultCategory" name="default_category" required>
                                <option value="Electronics" <?php echo $settings['default_category'] === 'Electronics' ? 'selected' : ''; ?>>Electronics</option>
                                <option value="Furniture" <?php echo $settings['default_category'] === 'Furniture' ? 'selected' : ''; ?>>Furniture</option>
                                <option value="Vehicles" <?php echo $settings['default_category'] === 'Vehicles' ? 'selected' : ''; ?>>Vehicles</option>
                                <option value="Real Estate" <?php echo $settings['default_category'] === 'Real Estate' ? 'selected' : ''; ?>>Real Estate</option>
                                <option value="Jobs" <?php echo $settings['default_category'] === 'Jobs' ? 'selected' : ''; ?>>Jobs</option>
                                <option value="Services" <?php echo $settings['default_category'] === 'Services' ? 'selected' : ''; ?>>Services</option>
                                <option value="Clothing" <?php echo $settings['default_category'] === 'Clothing' ? 'selected' : ''; ?>>Clothing</option>
                                <option value="Books" <?php echo $settings['default_category'] === 'Books' ? 'selected' : ''; ?>>Books</option>
                                <option value="Sports" <?php echo $settings['default_category'] === 'Sports' ? 'selected' : ''; ?>>Sports</option>
                                <option value="Other" <?php echo $settings['default_category'] === 'Other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="newPostingStatus">New Posting Status</label>
                            <select id="newPostingStatus" name="new_posting_status" required>
                                <option value="active" <?php echo $settings['new_posting_status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="pending" <?php echo $settings['new_posting_status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="inactive" <?php echo $settings['new_posting_status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-block">Update Posting Settings</button>
                        </div>
                    </form>
                </div>

                <!-- User Settings -->
                <div class="settings-section">
                    <h2>User Settings</h2>
                    <form method="POST" action="settings.php">
                        <input type="hidden" name="user_settings" value="1">
                        
                        <div class="form-group">
                            <label for="newUserStatus">New User Status</label>
                            <select id="newUserStatus" name="new_user_status" required>
                                <option value="active" <?php echo $settings['new_user_status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo $settings['new_user_status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                        
                        <div class="form-group form-check">
                            <input type="checkbox" id="requireEmailVerification" name="require_email_verification" <?php echo $settings['require_email_verification'] ? 'checked' : ''; ?>>
                            <label for="requireEmailVerification">Require Email Verification</label>
                        </div>
                        
                        <div class="form-group form-check">
                            <input type="checkbox" id="emailNotifications" name="email_notifications" <?php echo $settings['email_notifications'] ? 'checked' : ''; ?>>
                            <label for="emailNotifications">Enable Email Notifications</label>
                        </div>
                        
                        <div class="form-group form-check">
                            <input type="checkbox" id="smsNotifications" name="sms_notifications" <?php echo $settings['sms_notifications'] ? 'checked' : ''; ?>>
                            <label for="smsNotifications">Enable SMS Notifications</label>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-block">Update User Settings</button>
                        </div>
                    </form>
                </div>

                <!-- System Settings -->
                <div class="settings-section">
                    <h2>System Settings</h2>
                    <form method="POST" action="settings.php">
                        <input type="hidden" name="system_settings" value="1">
                        
                        <div class="form-group">
                            <label for="systemVersion">System Version</label>
                            <input type="text" id="systemVersion" value="1.0.0" disabled>
                        </div>
                        
                        <div class="form-group">
                            <label for="lastUpdate">Last Update</label>
                            <input type="text" id="lastUpdate" value="<?php echo date('F d, Y H:i:s'); ?>" disabled>
                        </div>
                        
                        <div class="form-group form-check">
                            <input type="checkbox" id="maintenanceMode" name="maintenance_mode">
                            <label for="maintenanceMode">Maintenance Mode</label>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-block">Update System Settings</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <script src="../assets/js/main.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.getElementById('sidebarToggle');
            const adminSidebar = document.querySelector('.admin-sidebar');
            
            if (sidebarToggle && adminSidebar) {
                sidebarToggle.addEventListener('click', function() {
                    adminSidebar.classList.toggle('collapsed');
                    document.body.classList.toggle('sidebar-collapsed');
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
            
            document.addEventListener('click', function(e) {
                const adminSidebar = document.querySelector('.admin-sidebar');
                if (window.innerWidth <= 768 && adminSidebar && adminSidebar.classList.contains('mobile-open') && !adminSidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
                    adminSidebar.classList.remove('mobile-open');
                }
            });
        });
    </script>
</body>
</html>