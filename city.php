<?php
session_start();
require_once 'includes/functions.php';
require_once 'includes/Posting.php';
require_once 'includes/database.php';

// Get database connection
$db = new Database();

// Get city from URL parameter
$city = isset($_GET['city']) ? sanitize($_GET['city']) : null;
$state = isset($_GET['state']) ? sanitize($_GET['state']) : null;

// If city not provided, redirect to home
if (!$city) {
    header('Location: index.php');
    exit();
}

// Get state for this city (try to detect)
$cityState = null;
if ($state) {
    $cityState = $state;
} else {
    // Simple city-to-state mapping (extend as needed)
    $cityStates = [
        'Mumbai' => 'Maharashtra',
        'Delhi' => 'Delhi',
        'Bengaluru' => 'Karnataka',
        'Hyderabad' => 'Telangana',
        'Chennai' => 'Tamil Nadu',
        // Add more mappings
    ];
    $cityState = $cityStates[$city] ?? null;
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

// Get additional filters
$category = isset($_GET['category']) ? sanitize($_GET['category']) : null;

// Get filtered postings
$filteredPostings = [];
$postingModel = new Posting();
$filteredPostings = $postingModel->getAll($category, null, $cityState, $city);

// Get posting count for this city
$countSql = "SELECT COUNT(*) as count FROM postings WHERE city = ? AND status = 'active'";
$countStmt = $db->getConnection()->prepare($countSql);
$countStmt->bind_param('s', $city);
$countStmt->execute();
$countResult = $countStmt->get_result();
$postingCount = $countResult->fetch_assoc()['count'];

// Build page title and description
$pageTitle = $city . ' Listings & Services - ' . APP_NAME;
$pageDescription = 'Find top services and listings in ' . $city . ', ' . ($cityState ?? 'India') . '. Browse verified local providers with contact details and prices.';
if ($category) {
    $pageTitle = ucfirst($category) . ' in ' . $city . ' - ' . APP_NAME;
    $pageDescription = 'Best ' . strtolower($category) . ' services in ' . $city;
}
$db->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="/add-posting/">
    <title><?php echo $pageTitle; ?></title>
    <meta name="description" content="<?php echo $pageDescription; ?>">
    <meta name="keywords" content="<?php echo $city; ?>, <?php echo $category ?? ''; ?> services, listings, <?php echo $cityState ?? ''; ?>, India">
    <meta property="og:title" content="<?php echo $pageTitle; ?>">
    <meta property="og:description" content="<?php echo $pageDescription; ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo 'https://' . $_SERVER['HTTP_HOST'] . '/add-posting/city/' . urlencode($city); ?>">
    <link rel="canonical" href="<?php echo 'https://' . $_SERVER['HTTP_HOST'] . '/add-posting/city/' . urlencode($city); ?>">
    
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "CollectionPage",
        "name": "<?php echo addslashes($pageTitle); ?>",
        "description": "<?php echo addslashes($pageDescription); ?>",
        "url": "<?php echo 'https://' . $_SERVER['HTTP_HOST'] . '/add-posting/city/' . urlencode($city); ?>",
        "mainEntity": {
            "@type": "ItemList",
            "numberOfItems": <?php echo count($filteredPostings); ?>
        },
        "breadcrumb": {
            "@type": "BreadcrumbList",
            "itemListElement": [
                {
                    "@type": "ListItem",
                    "position": 1,
                    "name": "Home",
                    "item": "<?php echo 'https://' . $_SERVER['HTTP_HOST'] . '/add-posting/'; ?>"
                },
                {
                    "@type": "ListItem",
                    "position": 2,
                    "name": "<?php echo $city; ?>",
                    "item": "<?php echo 'https://' . $_SERVER['HTTP_HOST'] . '/add-posting/city/' . urlencode($city); ?>"
                }
            ]
        }
    }
    </script>
    
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time() + 1000; ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .city-hero {
            background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%);
            padding: 4rem 0;
            color: white;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .city-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.4);
            z-index: 1;
        }
        .city-hero-content {
            position: relative;
            z-index: 2;
            max-width: 800px;
            margin: 0 auto;
        }
        .city-hero h1 {
            font-size: 3.5rem;
            margin-bottom: 1rem;
            text-shadow: 0 2px 10px rgba(0,0,0,0.5);
        }
        .city-hero-location {
            font-size: 1.3rem;
            opacity: 0.95;
            margin-bottom: 2rem;
        }
        .city-stats {
            display: flex;
            justify-content: center;
            gap: 2.5rem;
            margin: 2rem 0;
            flex-wrap: wrap;
        }
        .stat-item {
            text-align: center;
        }
        .stat-number {
            font-size: 3rem;
            font-weight: 800;
            display: block;
            text-shadow: 0 1px 3px rgba(0,0,0,0.3);
        }
        .city-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.9;
        }
        .city-breadcrumb {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1rem;
            opacity: 0.9;
            margin-bottom: 1.5rem;
        }
        .city-breadcrumb a {
            color: rgba(255,255,255,0.9);
            text-decoration: none;
        }
        .city-breadcrumb a:hover {
            color: white;
        }
        @media (max-width: 768px) {
            .city-hero h1 { font-size: 2.5rem; }
            .city-stats { gap: 1.5rem; }
            .stat-number { font-size: 2.2rem; }
            .city-icon { font-size: 3rem; }
        }
    </style>
</head>
<body class="index-body">
    <?php include 'includes/header.php'; ?>


    <!-- Category Filter -->
    <section class="index-hero" style="padding: 2rem 0;">
        <div class="container">
            <div class="search-container">
                <form method="GET" action="city.php?city=<?php echo urlencode($city); ?>" id="searchForm">
                    <input type="hidden" name="city" value="<?php echo htmlspecialchars($city); ?>">
                    <div class="search-field">
                        <label for="category">Category</label>
                        <select name="category" id="category">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $category === $cat ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit">
                        <i class="fas fa-search"></i> Filter by Category
                    </button>
                    <?php if ($category): ?>
                        <a href="city.php?city=<?php echo urlencode($city); ?>" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Clear
                        </a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </section>

    <!-- Postings Grid -->
    <section class="featured-listings">
        <div class="container">
            <?php if (!empty($filteredPostings)): ?>
                <div class="posting-cards">
                    <?php foreach ($filteredPostings as $posting): ?>
                    <div class="posting-card">
                        <div class="posting-image">
                            <a href="posting/<?php echo strtolower(str_replace(' ', '-', preg_replace('/[^a-zA-Z0-9 ]/', '', $posting['title']))); ?>">
                                <?php if (!empty($posting['images'])): ?>
                                    <img src="uploads/postings/<?php echo explode(',', $posting['images'])[0]; ?>" alt="<?php echo htmlspecialchars($posting['title']); ?>">
                                <?php else: ?>
                                    <div style="width: 100%; height: 100%; background: #f1f5f9; display: flex; align-items: center; justify-content: center; color: #64748b;">
                                        <i class="fas fa-image" style="font-size: 3rem;"></i>
                                    </div>
                                <?php endif; ?>
                            </a>
                        </div>
                        <div class="posting-content">
                            <div class="posting-header">
                                <a href="posting/<?php echo strtolower(str_replace(' ', '-', preg_replace('/[^a-zA-Z0-9 ]/', '', $posting['title']))); ?>">
                                    <h3 class="posting-title"><?php echo htmlspecialchars($posting['title']); ?></h3>
                                </a>
                                <div class="posting-price">
                                    <?php if (!empty($posting['price']) && $posting['price'] > 0): ?>
                                        ₹<?php echo number_format($posting['price'], 2); ?>/hr
                                    <?php endif; ?>
                                </div>
                            </div>
                            <p class="posting-description"><?php echo substr($posting['description'], 0, 150); ?>...</p>
                            <div class="posting-meta">
                                <span><i class="fas fa-tag"></i> <?php echo ucfirst($posting['category']); ?></span>
                                <span><i class="fas fa-map-marker-alt"></i> <?php echo $posting['city']; ?></span>
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
                    <h3>No Listings Found in <?php echo $city; ?></h3>
                    <p>Be the first to list services in <?php echo $city; ?>.</p>
                    <a href="add-posting.php?city=<?php echo urlencode($city); ?>" class="btn btn-primary" style="font-size: 1.1rem; padding: 1rem 2rem;">
                        <i class="fas fa-plus"></i> Add Listing in <?php echo $city; ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <script src="assets/js/main.js?v=<?php echo time(); ?>"></script>
</body>
</html>
