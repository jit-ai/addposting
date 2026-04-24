<?php
session_start();
require_once 'includes/functions.php';
require_once 'includes/Posting.php';
require_once 'includes/database.php';

// Get database connection
$db = new Database();

// Get state from URL parameter
$state = isset($_GET['state']) ? sanitize($_GET['state']) : null;

// If state not provided, redirect to home
if (!$state) {
    header('Location: index.php');
    exit();
}

// Get active categories for dropdown
$categories = [];
$sql = "SELECT name FROM categories WHERE status = 'active' ORDER BY name";
$result = $db->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row['name'];
    }
}

// Get cities in this state
$citiesInState = [];
$citiesByState = [
    'Maharashtra' => ['Mumbai', 'Pune', 'Nagpur', 'Nashik', 'Aurangabad'],
    'Delhi' => ['New Delhi', 'North Delhi', 'South Delhi'],
    'Karnataka' => ['Bengaluru', 'Mysore', 'Mangalore'],
    'Telangana' => ['Hyderabad', 'Warangal'],
    'Tamil Nadu' => ['Chennai', 'Coimbatore', 'Madurai'],
    // Add more states...
    'default' => []
];
$citiesInState = $citiesByState[$state] ?? $citiesByState['default'];

// Get additional filters
$category = isset($_GET['category']) ? sanitize($_GET['category']) : null;
$city = isset($_GET['city']) ? sanitize($_GET['city']) : null;

// Get filtered postings
$filteredPostings = [];
$postingModel = new Posting();
$filteredPostings = $postingModel->getAll($category, null, $state, $city);

// Get posting count for this state
$countSql = "SELECT COUNT(*) as count FROM postings WHERE state = ? AND status = 'active'";
$countStmt = $db->getConnection()->prepare($countSql);
$countStmt->bind_param('s', $state);
$countStmt->execute();
$countResult = $countStmt->get_result();
$postingCount = $countResult->fetch_assoc()['count'];

// Build page title and description
$pageTitle = $state . ' Listings & Services - ' . APP_NAME;
$pageDescription = 'Find top services across ' . $state . ', India. Browse verified providers in major cities like ' . implode(', ', array_slice($citiesInState, 0, 3)) . '.';
if ($category) {
    $pageTitle = ucfirst($category) . ' Services in ' . $state . ' - ' . APP_NAME;
    $pageDescription = strtolower($category) . ' services across ' . $state;
}
$db->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="/addposting/">
    <title><?php echo $pageTitle; ?></title>
    <meta name="description" content="<?php echo $pageDescription; ?>">
    <meta name="keywords" content="<?php echo $state; ?>, services <?php echo $state; ?>, listings <?php echo $state; ?>, <?php echo implode(', ', $citiesInState); ?>">
    <meta property="og:title" content="<?php echo $pageTitle; ?>">
    <meta property="og:description" content="<?php echo $pageDescription; ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo 'https://' . $_SERVER['HTTP_HOST'] . '/addposting/state/' . urlencode($state); ?>">
    <link rel="canonical" href="<?php echo 'https://' . $_SERVER['HTTP_HOST'] . '/addposting/state/' . urlencode($state); ?>">
    
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "CollectionPage",
        "name": "<?php echo addslashes($pageTitle); ?>",
        "description": "<?php echo addslashes($pageDescription); ?>",
        "url": "<?php echo 'https://' . $_SERVER['HTTP_HOST'] . '/addposting/state/' . urlencode($state); ?>",
        "breadcrumb": {
            "@type": "BreadcrumbList",
            "itemListElement": [
                {"@type": "ListItem", "position": 1, "name": "Home", "item": "<?php echo 'https://' . $_SERVER['HTTP_HOST'] . '/addposting/'; ?>"},
                {"@type": "ListItem", "position": 2, "name": "<?php echo $state; ?>", "item": "<?php echo 'https://' . $_SERVER['HTTP_HOST'] . '/addposting/state/' . urlencode($state); ?>"}
            ]
        }
    }
    </script>
    
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time() + 1000; ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .state-hero {
            background: linear-gradient(135deg, #ed8936 0%, #dd6b20 100%);
            padding: 4rem 0;
            color: white;
            text-align: center;
        }
        .state-hero h1 {
            font-size: 3.5rem;
            margin-bottom: 1rem;
        }
        .state-cities-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin: 3rem 0;
        }
        .city-card {
            background: rgba(255,255,255,0.15);
            padding: 2rem;
            border-radius: 16px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
            transition: all 0.3s ease;
        }
        .city-card:hover {
            transform: translateY(-8px);
            background: rgba(255,255,255,0.25);
        }
        .city-card h3 {
            font-size: 1.3rem;
            margin-bottom: 0.5rem;
        }
    </style>
</head>
<body class="index-body">
    <?php include 'includes/header.php'; ?>

    <!-- Filters -->
    <section class="index-hero" style="padding: 2rem 0;">
        <div class="container">
            <div class="search-container">
                <form method="GET" action="state.php?state=<?php echo urlencode($state); ?>" id="searchForm">
                    <input type="hidden" name="state" value="<?php echo htmlspecialchars($state); ?>">
                    <div class="search-field">
                        <label for="category">Category</label>
                        <select name="category" id="category">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $category === $cat ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="search-field">
                        <label for="city">Popular Cities</label>
                        <select name="city" id="city">
                            <option value="">All Cities</option>
                            <?php foreach ($citiesInState as $cityName): ?>
                            <option value="<?php echo htmlspecialchars($cityName); ?>" <?php echo $city === $cityName ? 'selected' : ''; ?>><?php echo $cityName; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit">
                        <i class="fas fa-search"></i> Filter
                    </button>
                    <?php if ($category || $city): ?>
                        <a href="state.php?state=<?php echo urlencode($state); ?>" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Clear
                        </a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </section>

    <!-- Postings -->
    <section class="featured-listings">
        <div class="container">
            <?php if (!empty($filteredPostings)): ?>
                <div class="posting-cards">
                    <?php foreach ($filteredPostings as $posting): ?>
                    <div class="posting-card">
                        <!-- Same posting card structure as category/city -->
                        <div class="posting-image">
                            <a href="posting/<?php echo strtolower(str_replace(' ', '-', preg_replace('/[^a-zA-Z0-9 ]/', '', $posting['title']))); ?>">
                                <?php if (!empty($posting['images'])): ?>
                                    <img src="uploads/postings/<?php echo explode(',', $posting['images'])[0]; ?>" alt="<?php echo htmlspecialchars($posting['title']); ?>">
                                <?php else: ?>
                                    <div style="background: #f1f5f9; display: flex; align-items: center; justify-content: center; color: #64748b;">
                                        <i class="fas fa-image" style="font-size: 3rem;"></i>
                                    </div>
                                <?php endif; ?>
                            </a>
                        </div>
                        <div class="posting-content">
                            <div class="posting-header">
                                <h3 class="posting-title"><?php echo htmlspecialchars($posting['title']); ?></h3>
                                <div class="posting-price">
                                    <?php if (!empty($posting['price']) && $posting['price'] > 0): ?>
                                        ₹<?php echo number_format($posting['price'], 2); ?>/hr
                                    <?php endif; ?>
                                </div>
                            </div>
                            <p class="posting-description"><?php echo substr($posting['description'], 0, 150); ?>...</p>
                            <div class="posting-meta">
                                <span><?php echo ucfirst($posting['category']); ?></span>
                                <span><?php echo $posting['city']; ?></span>
                            </div>
                            <div class="posting-actions">
                                <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $posting['contact']); ?>" class="whatsapp-btn" target="_blank">
                                    <i class="fab fa-whatsapp"></i> WhatsApp
                                </a>
                                <a href="tel:<?php echo preg_replace('/[^0-9]/', '', $posting['contact']); ?>" class="call-btn">
                                    <i class="fas fa-phone"></i> Call
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-postings" style="text-align: center; padding: 4rem 2rem;">
                    <i class="fas fa-map" style="font-size: 4rem; color: #64748b;"></i>
                    <h3>No Services in <?php echo $state; ?> Yet</h3>
                    <p>Be the first service provider in <?php echo $state; ?>.</p>
                    <a href="addposting.php?state=<?php echo urlencode($state); ?>" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Service in <?php echo $state; ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
