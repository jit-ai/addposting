<header>
    <div class="container">
        <div class="logo">
            <a href="/index.php" style="display: flex; align-items: center; text-decoration: none; color: inherit;">
                <img src="https://admypost.org/assets/logonewadd.png" style="width:100px;padding:3px"/>
            </a>
        </div>
        <button class="hamburger" aria-label="Toggle menu" aria-expanded="false">
            <i class="fas fa-bars"></i>
        </button>
        <nav>
            <ul>
                <li><a href="/index.php" class="active">Home</a></li>
                <li><a href="/statecitylist.php">Browse by Location</a></li>
                <li><a href="/add-posting.php" class="btn btn-primary">Add Posting</a></li>
                <?php if (isLoggedIn()): ?>
                    <li class="dropdown">
                        <a href="#"><i class="fas fa-user"></i> My Account</a>
                        <div class="dropdown-content">
                            <a href="/dashboard.php">Dashboard</a>
                            <a href="/my-postings.php">My Postings</a>
                            <a href="/profile.php">Profile</a>
                            <a href="/logout.php">Logout</a>
                        </div>
                    </li>
                <?php else: ?>
                    <li><a href="/login.php">Login</a></li>
                    <li><a href="/register.php">Register</a></li>
                <?php endif; ?>
                <?php if (isAdmin()): ?>
                    <li><a href="/admin/dashboard.php" class="btn btn-danger">Admin Dashboard</a></li>
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
            <img src="https://admypost.org/assets/logonewadd.png" style="width:100px;padding:3px"/>
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
        <li><a href="/index.php" class="active"><i class="fas fa-home"></i> Home</a></li>
        <li><a href="/statecitylist.php"><i class="fas fa-map-marked-alt"></i> Browse by Location</a></li>
        <li><a href="/add-posting.php"><i class="fas fa-plus-circle"></i> Add Posting</a></li>
        <?php if (isLoggedIn()): ?>
            <li class="dropdown">
                <a href="#" class="dropdown-toggle"><i class="fas fa-user"></i> My Account</a>
                <div class="dropdown-content">
                    <a href="/dashboard.php">Dashboard</a>
                    <a href="/my-postings.php">My Postings</a>
                    <a href="/profile.php">Profile</a>
                    <a href="/logout.php">Logout</a>
                </div>
            </li>
        <?php else: ?>
            <li><a href="/login.php"><i class="fas fa-sign-in-alt"></i> Login</a></li>
            <li><a href="/register.php"><i class="fas fa-user-plus"></i> Register</a></li>
        <?php endif; ?>
        <?php if (isAdmin()): ?>
            <li><a href="/admin/dashboard.php"><i class="fas fa-cog"></i> Admin Dashboard</a></li>
        <?php endif; ?>
    </ul>
</div>