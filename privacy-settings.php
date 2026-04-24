<?php
require_once 'includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Settings - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .privacy-settings {
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

        .privacy-options {
            display: grid;
            gap: 1rem;
        }

        .privacy-option {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .privacy-option:hover {
            border-color: #667eea;
            background: #f8f9fa;
        }

        .privacy-option input[type="radio"] {
            width: auto;
            transform: scale(1.2);
        }

        .privacy-option label {
            margin: 0;
            font-weight: normal;
            color: #4a5568;
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

    <!-- Privacy Settings -->
    <section class="privacy-settings">
        <div class="container">
            <div class="page-header">
                <h1>Privacy Settings</h1>
                <a href="profile.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Profile
                </a>
            </div>

            <div class="settings-container">
                <!-- Profile Visibility -->
                <div class="settings-section">
                    <h2>Profile Visibility</h2>
                    <form method="POST" action="privacy-settings.php">
                        <input type="hidden" name="profile_visibility" value="1">
                        
                        <div class="form-group">
                            <label for="profileVisibility">Who can see your profile</label>
                            <div class="privacy-options">
                                <div class="privacy-option">
                                    <input type="radio" id="visibilityPublic" name="visibility" value="public" checked>
                                    <label for="visibilityPublic">
                                        <strong>Public</strong> - Everyone can see your profile
                                    </label>
                                </div>
                                <div class="privacy-option">
                                    <input type="radio" id="visibilityMembers" name="visibility" value="members">
                                    <label for="visibilityMembers">
                                        <strong>Members Only</strong> - Only logged-in users can see your profile
                                    </label>
                                </div>
                                <div class="privacy-option">
                                    <input type="radio" id="visibilityPrivate" name="visibility" value="private">
                                    <label for="visibilityPrivate">
                                        <strong>Private</strong> - Only you can see your profile
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-block">Update Profile Visibility</button>
                        </div>
                    </form>
                </div>

                <!-- Posting Visibility -->
                <div class="settings-section">
                    <h2>Posting Visibility</h2>
                    <form method="POST" action="privacy-settings.php">
                        <input type="hidden" name="posting_visibility" value="1">
                        
                        <div class="form-group">
                            <label for="postingVisibility">Default visibility for new postings</label>
                            <div class="privacy-options">
                                <div class="privacy-option">
                                    <input type="radio" id="postingPublic" name="posting_visibility" value="public" checked>
                                    <label for="postingPublic">
                                        <strong>Public</strong> - Everyone can see your postings
                                    </label>
                                </div>
                                <div class="privacy-option">
                                    <input type="radio" id="postingMembers" name="posting_visibility" value="members">
                                    <label for="postingMembers">
                                        <strong>Members Only</strong> - Only logged-in users can see your postings
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-block">Update Posting Visibility</button>
                        </div>
                    </form>
                </div>

                <!-- Contact Information -->
                <div class="settings-section">
                    <h2>Contact Information</h2>
                    <form method="POST" action="privacy-settings.php">
                        <input type="hidden" name="contact_settings" value="1">
                        
                        <div class="form-group form-check">
                            <input type="checkbox" id="showEmail" name="show_email" checked>
                            <label for="showEmail">Show email address on my profile</label>
                        </div>
                        
                        <div class="form-group form-check">
                            <input type="checkbox" id="showPhone" name="show_phone">
                            <label for="showPhone">Show phone number on my profile</label>
                        </div>
                        
                        <div class="form-group form-check">
                            <input type="checkbox" id="allowMessages" name="allow_messages" checked>
                            <label for="allowMessages">Allow users to send me messages</label>
                        </div>
                        
                        <div class="form-group form-check">
                            <input type="checkbox" id="allowNotifications" name="allow_notifications" checked>
                            <label for="allowNotifications">Allow notifications about my postings</label>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-block">Update Contact Settings</button>
                        </div>
                    </form>
                </div>

                <!-- Data Privacy -->
                <div class="settings-section">
                    <h2>Data Privacy</h2>
                    <div class="form-group">
                        <label for="dataRetention">Data Retention Policy</label>
                        <p class="help-text">Your data will be retained as long as your account is active. If you delete your account, all your data will be permanently removed within 30 days.</p>
                    </div>
                    
                    <div class="form-group">
                        <label for="dataExport">Export Your Data</label>
                        <p class="help-text">You can request a copy of all your personal data in a machine-readable format.</p>
                        <button type="button" class="btn btn-secondary">Request Data Export</button>
                    </div>
                    
                    <div class="form-group">
                        <label for="dataDeletion">Delete Your Account</label>
                        <p class="help-text">Deleting your account is permanent and cannot be undone. All your postings, messages, and personal data will be deleted.</p>
                        <button type="button" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete your account? This cannot be undone.')">Delete Account</button>
                    </div>
                </div>

                <!-- Privacy Policy -->
                <div class="settings-section">
                    <h2>Privacy Policy</h2>
                    <p class="help-text">Please review our <a href="privacy-policy.php" style="color: #667eea; text-decoration: none; font-weight: bold;">Privacy Policy</a> to understand how we collect, use, and protect your personal information.</p>
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