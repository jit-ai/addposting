<?php
require_once 'includes/functions.php';
require_once 'includes/Posting.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

$postingModel = new Posting();

// Get user's postings
$userPostings = $postingModel->findByUserId($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Postings - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time() + 1000; ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
     <style>
        .my-postings {
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

        /* Posting Cards */
        .posting-cards {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .posting-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            display: flex;
            flex-direction: row;
        }

        .posting-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .posting-image {
            position: relative;
            width: 300px;
            height: 200px;
            flex-shrink: 0;
        }

        .posting-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .posting-content {
            padding: 1.5rem;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .posting-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }

        .posting-description {
            color: #64748b;
            margin-bottom: 1rem;
            line-height: 1.6;
            font-size: 0.95rem;
        }

        .posting-meta {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
            font-size: 0.875rem;
            color: #64748b;
        }

        .posting-meta span {
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .posting-price {
            font-size: 1.5rem;
            font-weight: 700;
            color: #10b981;
            margin-bottom: 1rem;
            text-align: right;
        }

        .posting-item-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
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
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .no-postings {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .no-postings i {
            font-size: 4rem;
            color: #cbd5e0;
            margin-bottom: 1rem;
        }

        .no-postings h3 {
            font-size: 1.5rem;
            color: #2d3748;
            margin-bottom: 0.5rem;
        }

        .no-postings p {
            color: #718096;
            margin-bottom: 1rem;
        }

        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .posting-card {
                flex-direction: column;
                text-align: center;
            }

            .posting-image {
                width: 100%;
                height: 200px;
            }

            .posting-item-actions {
                justify-content: center;
            }
        }
    </style>
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

    <!-- My Postings -->
    <section class="my-postings">
        <div class="container">
            <div class="page-header">
                <h1>My Postings</h1>
                <a href="add-posting.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Posting
                </a>
            </div>

            <?php if (!empty($userPostings)): ?>
                <div class="posting-cards">
                <?php foreach ($userPostings as $posting): ?>
                    <div class="posting-card">
                        <div class="posting-image">
                            <?php if (!empty($posting['images'])): ?>
                                <img src="uploads/postings/<?php echo explode(',', $posting['images'])[0]; ?>" alt="<?php echo $posting['title']; ?>">
                            <?php else: ?>
                                <div style="width: 100%; height: 100%; background: #f1f5f9; display: flex; align-items: center; justify-content: center; color: #64748b;">
                                    <i class="fas fa-image" style="font-size: 3rem;"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="posting-content">
                            <h3 class="posting-title"><?php echo $posting['title']; ?></h3>
                            <p class="posting-description"><?php echo substr($posting['description'], 0, 150); ?>...</p>
                             <div class="posting-meta">
                                 <span><i class="fas fa-calendar"></i> <?php echo date('M d, Y', strtotime($posting['created_at'])); ?></span>
                                 <span><i class="fas fa-map-marker-alt"></i> <?php echo ($posting['city'] ?? '') . ', ' . ($posting['state'] ?? ''); ?></span>
                                 <span class="status-badge status-<?php echo $posting['status']; ?>"><?php echo ucfirst($posting['status']); ?></span>
                                 <?php if ($posting['status'] === 'pending' && !empty($posting['scheduled_at'])): ?>
                                     <span><i class="fas fa-clock"></i> Scheduled: <?php echo date('M d, Y H:i', strtotime($posting['scheduled_at'])); ?></span>
                                 <?php endif; ?>
                             </div>
                            <div class="posting-price">
                                <?php if (!empty($posting['price']) && $posting['price'] > 0): ?>
                                    ₹<?php echo number_format($posting['price'], 2); ?>
                                <?php endif; ?>
                            </div>
                            <div class="posting-item-actions">
                                <a href="posting/<?php echo strtolower(str_replace(' ', '-', preg_replace('/[^a-zA-Z0-9 ]/', '', $posting['title']))); ?>" class="btn btn-sm btn-secondary">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <a href="edit-posting.php?id=<?php echo $posting['id']; ?>" class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="delete-posting.php?id=<?php echo $posting['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this posting?');">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                                <?php if ($posting['status'] === 'active'): ?>
                                    <a href="toggle-posting.php?id=<?php echo $posting['id']; ?>&status=inactive" class="btn btn-sm btn-warning">
                                        <i class="fas fa-times"></i> Deactivate
                                    </a>
                                <?php else: ?>
                                    <a href="toggle-posting.php?id=<?php echo $posting['id']; ?>&status=active" class="btn btn-sm btn-success">
                                        <i class="fas fa-check"></i> Activate
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-postings">
                    <i class="fas fa-inbox"></i>
                    <h3>No Postings Yet</h3>
                    <p>You haven't created any postings yet. Start by creating your first posting!</p>
                    <a href="add-posting.php" class="btn btn-primary">Create Posting</a>
                </div>
            <?php endif; ?>
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