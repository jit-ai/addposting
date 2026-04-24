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
    <title>Account Settings - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .account-settings {
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

        .notification-settings {
            display: grid;
            gap: 1rem;
        }

        .notification-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .notification-item:hover {
            border-color: #667eea;
            background: #f8f9fa;
        }

        .notification-item input[type="checkbox"] {
            width: auto;
            transform: scale(1.2);
        }

        .notification-item label {
            margin: 0;
            font-weight: normal;
            color: #4a5568;
        }

        .notification-item .description {
            font-size: 0.875rem;
            color: #718096;
            margin-top: 0.25rem;
        }

        .theme-options {
            display: grid;
            gap: 1rem;
        }

        .theme-option {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 5px;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .theme-option:hover {
            border-color: #667eea;
            background: #f8f9fa;
        }

        .theme-option input[type="radio"] {
            width: auto;
            transform: scale(1.2);
        }

        .theme-option label {
            margin: 0;
            font-weight: normal;
            color: #4a5568;
        }

        .theme-preview {
            display: flex;
            gap: 10px;
            margin-top: 0.5rem;
        }

        .theme-preview-item {
            width: 30px;
            height: 30px;
            border-radius: 50%;
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
                    <li><a href="add-posting.php">Add Posting</a></li>
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

    <!-- Account Settings -->
    <section class="account-settings">
        <div class="container">
            <div class="page-header">
                <h1>Account Settings</h1>
                <a href="profile.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Profile
                </a>
            </div>

            <div class="settings-container">
                <!-- Notification Settings -->
                <div class="settings-section">
                    <h2>Notification Settings</h2>
                    <form method="POST" action="account-settings.php">
                        <input type="hidden" name="notification_settings" value="1">
                        
                        <div class="notification-settings">
                            <div class="notification-item">
                                <input type="checkbox" id="notifyEmail" name="notify_email" checked>
                                <div>
                                    <label for="notifyEmail">Email Notifications</label>
                                    <div class="description">Receive emails about new postings, messages, and updates</div>
                                </div>
                            </div>
                            
                            <div class="notification-item">
                                <input type="checkbox" id="notifySMS" name="notify_sms">
                                <div>
                                    <label for="notifySMS">SMS Notifications</label>
                                    <div class="description">Receive SMS messages about important updates</div>
                                </div>
                            </div>
                            
                            <div class="notification-item">
                                <input type="checkbox" id="notifyPush" name="notify_push" checked>
                                <div>
                                    <label for="notifyPush">Push Notifications</label>
                                    <div class="description">Receive push notifications on your mobile device</div>
                                </div>
                            </div>
                            
                            <div class="notification-item">
                                <input type="checkbox" id="notifyComments" name="notify_comments" checked>
                                <div>
                                    <label for="notifyComments">Comment Notifications</label>
                                    <div class="description">Receive notifications when someone comments on your postings</div>
                                </div>
                            </div>
                            
                            <div class="notification-item">
                                <input type="checkbox" id="notifyLikes" name="notify_likes" checked>
                                <div>
                                    <label for="notifyLikes">Like Notifications</label>
                                    <div class="description">Receive notifications when someone likes your postings</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-block">Update Notification Settings</button>
                        </div>
                    </form>
                </div>

                <!-- Theme Settings -->
                <div class="settings-section">
                    <h2>Theme Settings</h2>
                    <form method="POST" action="account-settings.php">
                        <input type="hidden" name="theme_settings" value="1">
                        
                        <div class="form-group">
                            <label for="theme">Choose your theme</label>
                            <div class="theme-options">
                                <div class="theme-option">
                                    <input type="radio" id="themeLight" name="theme" value="light" checked>
                                    <div>
                                        <label for="themeLight">Light Theme</label>
                                        <div class="theme-preview">
                                            <div class="theme-preview-item" style="background: #ffffff;"></div>
                                            <div class="theme-preview-item" style="background: #f8f9fa;"></div>
                                            <div class="theme-preview-item" style="background: #e2e8f0;"></div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="theme-option">
                                    <input type="radio" id="themeDark" name="theme" value="dark">
                                    <div>
                                        <label for="themeDark">Dark Theme</label>
                                        <div class="theme-preview">
                                            <div class="theme-preview-item" style="background: #1a202c;"></div>
                                            <div class="theme-preview-item" style="background: #2d3748;"></div>
                                            <div class="theme-preview-item" style="background: #4a5568;"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-block">Update Theme Settings</button>
                        </div>
                    </form>
                </div>

                <!-- Language Settings -->
                <div class="settings-section">
                    <h2>Language Settings</h2>
                    <form method="POST" action="account-settings.php">
                        <input type="hidden" name="language_settings" value="1">
                        
                        <div class="form-group">
                            <label for="language">Language</label>
                            <select id="language" name="language" required>
                                <option value="en" selected>English</option>
                                <option value="es">Spanish</option>
                                <option value="fr">French</option>
                                <option value="de">German</option>
                                <option value="it">Italian</option>
                                <option value="pt">Portuguese</option>
                                <option value="ru">Russian</option>
                                <option value="zh">Chinese</option>
                                <option value="ja">Japanese</option>
                                <option value="ko">Korean</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-block">Update Language Settings</button>
                        </div>
                    </form>
                </div>

                <!-- Timezone Settings -->
                <div class="settings-section">
                    <h2>Timezone Settings</h2>
                    <form method="POST" action="account-settings.php">
                        <input type="hidden" name="timezone_settings" value="1">
                        
                        <div class="form-group">
                            <label for="timezone">Timezone</label>
                            <select id="timezone" name="timezone" required>
                                <option value="America/New_York">America/New_York (EST)</option>
                                <option value="America/Chicago">America/Chicago (CST)</option>
                                <option value="America/Denver">America/Denver (MST)</option>
                                <option value="America/Los_Angeles">America/Los_Angeles (PST)</option>
                                <option value="Europe/London">Europe/London (GMT)</option>
                                <option value="Europe/Paris">Europe/Paris (CET)</option>
                                <option value="Europe/Berlin">Europe/Berlin (CET)</option>
                                <option value="Asia/Calcutta">Asia/Calcutta (IST)</option>
                                <option value="Asia/Tokyo">Asia/Tokyo (JST)</option>
                                <option value="Asia/Singapore">Asia/Singapore (SGT)</option>
                                <option value="Australia/Sydney">Australia/Sydney (AEDT)</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-block">Update Timezone Settings</button>
                        </div>
                    </form>
                </div>

                <!-- Security Settings -->
                <div class="settings-section">
                    <h2>Security Settings</h2>
                    <form method="POST" action="account-settings.php">
                        <input type="hidden" name="security_settings" value="1">
                        
                        <div class="form-group form-check">
                            <input type="checkbox" id="twoFactorAuth" name="two_factor_auth">
                            <label for="twoFactorAuth">Enable Two-Factor Authentication</label>
                        </div>
                        
                        <div class="form-group form-check">
                            <input type="checkbox" id="loginAlerts" name="login_alerts" checked>
                            <label for="loginAlerts">Receive alerts for new logins</label>
                        </div>
                        
                        <div class="form-group">
                            <label for="sessionTimeout">Session Timeout (minutes)</label>
                            <input type="number" id="sessionTimeout" name="session_timeout" value="30" min="5" max="120">
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-block">Update Security Settings</button>
                        </div>
                    </form>
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
    <script>
        // Theme preview functionality
        document.addEventListener('DOMContentLoaded', function() {
            const themeOptions = document.querySelectorAll('.theme-option');
            
            themeOptions.forEach(option => {
                option.addEventListener('click', function() {
                    const radio = this.querySelector('input[type="radio"]');
                    radio.checked = true;
                });
            });
        });
    </script>
</body>
</html>