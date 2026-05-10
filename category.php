<?php
session_start();
require_once 'includes/functions.php';
require_once 'includes/Posting.php';
require_once 'includes/database.php';

// Get database connection
$db = new Database();

// Get category from URL parameter
$category = isset($_GET['category']) ? sanitize($_GET['category']) : null;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10;

// Validate category exists and is active
$categoryDetails = null;
if ($category) {
    $sql = "SELECT * FROM categories WHERE name = ? AND status = 'active'";
    $stmt = $db->getConnection()->prepare($sql);
    $stmt->bind_param('s', $category);
    $stmt->execute();
    $result = $stmt->get_result();
    $categoryDetails = $result->fetch_assoc();
}

// If category not found, redirect to home
if (!$category || !$categoryDetails) {
    header('HTTP/1.0 404 Not Found');
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

// Get filters (keep other filters working on category page)
$state = isset($_GET['state']) ? sanitize($_GET['state']) : null;
$city = isset($_GET['city']) ? sanitize($_GET['city']) : null;

// Get filtered postings
$filteredPostings = [];
$totalPostings = 0;
$postingModel = new Posting();

$offset = ($page - 1) * $perPage;
$filteredPostings = $postingModel->getAll($category, null, $state, $city, $perPage, $offset);
$totalPostings = $postingModel->getTotalCount($category, null, $state, $city);

// Get posting count for this category (without other filters)
$countSql = "SELECT COUNT(*) as count FROM postings WHERE category = ? AND status = 'active'";
$countStmt = $db->getConnection()->prepare($countSql);
$countStmt->bind_param('s', $category);
$countStmt->execute();
$countResult = $countStmt->get_result();
$postingCount = $countResult->fetch_assoc()['count'];

// Build page title and description
$pageTitle = ucfirst($category) . ' Listings & Services - ' . APP_NAME;
$pageDescription = 'Find top ' . strtolower($category) . ' services and listings across India. Browse verified ' . strtolower($category) . ' providers with contact details, prices, and reviews.';
if ($state) {
    $pageTitle .= ' in ' . $state;
    $pageDescription = 'Best ' . strtolower($category) . ' services in ' . $state;
}
if ($city) {
    $pageTitle .= ' in ' . $city;
    $pageDescription = 'Top ' . strtolower($category) . ' listings in ' . $city . ', ' . $state;
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
    <meta name="keywords" content="<?php echo $category; ?>, services, listings, <?php echo $city ? $city : ''; ?>, <?php echo $state ? $state : ''; ?>, India, classifieds">
    <meta property="og:title" content="<?php echo $pageTitle; ?>">
    <meta property="og:description" content="<?php echo $pageDescription; ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo 'https://' . $_SERVER['HTTP_HOST'] . '/addposting/category/' . urlencode($category); ?>">
    <link rel="canonical" href="<?php echo 'https://' . $_SERVER['HTTP_HOST'] . '/addposting/category/' . urlencode($category); ?>">
    
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "CollectionPage",
        "name": "<?php echo addslashes($pageTitle); ?>",
        "description": "<?php echo addslashes($pageDescription); ?>",
        "url": "<?php echo 'https://' . $_SERVER['HTTP_HOST'] . '/addposting/category/' . urlencode($category); ?>",
        "mainEntity": {
            "@type": "ItemList",
            "numberOfItems": <?php echo count($filteredPostings); ?>,
            "itemListElement": [
                <?php foreach (array_slice($filteredPostings, 0, 5) as $index => $posting): ?>
                {
                    "@type": "ListItem",
                    "position": <?php echo $index + 1; ?>,
                        "item": {
                            "@type": "Service",
                            "name": "<?php echo addslashes($posting['title']); ?>",
                            "url": "<?php echo 'https://' . $_SERVER['HTTP_HOST'] . '/addposting/posting/' . strtolower(str_replace(' ', '-', preg_replace('/[^a-zA-Z0-9 ]/', '', $posting['title']))) . '-' . $posting['id']; ?>"
                        }
                }<?php echo $index < min(4, count($filteredPostings) - 1) ? ',' : ''; ?>
                <?php endforeach; ?>
            ]
        },
        "breadcrumb": {
            "@type": "BreadcrumbList",
            "itemListElement": [
                {
                    "@type": "ListItem",
                    "position": 1,
                    "name": "Home",
                    "item": "<?php echo 'https://' . $_SERVER['HTTP_HOST'] . '/addposting/'; ?>"
                },
                {
                    "@type": "ListItem",
                    "position": 2,
                    "name": "<?php echo ucfirst($category); ?>",
                    "item": "<?php echo 'https://' . $_SERVER['HTTP_HOST'] . '/addposting/category/' . urlencode($category); ?>"
                }
            ]
        }
    }
    </script>
    
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time() + 1000; ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .category-hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 4rem 0;
            color: white;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .category-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.4);
            z-index: 1;
        }
        .category-hero-content {
            position: relative;
            z-index: 2;
            max-width: 800px;
            margin: 0 auto;
        }
        .category-hero h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
            text-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }
        .category-hero-image {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            margin: 0 auto 1.5rem;
            border: 5px solid rgba(255,255,255,0.3);
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        .category-hero-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .category-hero-image .icon-placeholder {
            width: 100%;
            height: 100%;
            background: rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
        }
        .category-stats {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin: 2rem 0;
            flex-wrap: wrap;
        }
        .stat-item {
            text-align: center;
        }
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            display: block;
        }
        .category-breadcrumb {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            opacity: 0.9;
            margin-bottom: 1rem;
        }
        .category-breadcrumb a {
            color: rgba(255,255,255,0.9);
            text-decoration: none;
        }
        .category-breadcrumb a:hover {
            color: white;
        }
        @media (max-width: 768px) {
            .category-hero h1 { font-size: 2rem; }
            .category-stats { gap: 1rem; }
            .stat-number { font-size: 2rem; }
        }
        .search-filters-bar .category-active {
            background: #667eea;
            border-color: #667eea;
            color: white;
        }
    </style>
</head>
<body class="index-body">
    <?php include 'includes/header.php'; ?>



    <!-- Search Form (pre-select category) -->
    <section class="index-hero" style="padding: 2rem 0;">
        <div class="container">
            <div class="search-container">
                <form method="GET" action="category.php?category=<?php echo urlencode($category); ?>" id="searchForm">
                    <input type="hidden" name="category" value="<?php echo htmlspecialchars($category); ?>">
                    <div class="search-field">
                        <label for="state">State</label>
                        <select id="state" name="state" onchange="loadCities(this.value)">
                            <option value="">All India</option>
                            <option value="Andhra Pradesh" <?php echo $state === 'Andhra Pradesh' ? 'selected' : ''; ?>>Andhra Pradesh</option>
                            <!-- ... all states as in search.php ... -->
                            <option value="Delhi" <?php echo $state === 'Delhi' ? 'selected' : ''; ?>>Delhi</option>
                        </select>
                    </div>
                    <div class="search-field">
                        <label for="city">City</label>
                        <select id="city" name="city">
                            <option value="">All Cities</option>
                            <?php if ($state && isset($citiesByState[$state])): ?>
                                <?php foreach ($citiesByState[$state] as $c): ?>
                                <option value="<?php echo $c; ?>" <?php echo $city === $c ? 'selected' : ''; ?>><?php echo $c; ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <button type="submit">
                        <i class="fas fa-search"></i> Filter
                    </button>
                    <?php if ($state || $city): ?>
                        <a href="category.php?category=<?php echo urlencode($category); ?>" class="btn btn-secondary" style="padding: 0.75rem 1.25rem; border-radius: 8px; text-decoration: none; color: white;">
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
                            <a href="posting/<?php echo strtolower(str_replace(' ', '-', preg_replace('/[^a-zA-Z0-9 ]/', '', $posting['title']))) . '-' . $posting['id']; ?>">
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
                                <a href="posting/<?php echo strtolower(str_replace(' ', '-', preg_replace('/[^a-zA-Z0-9 ]/', '', $posting['title']))) . '-' . $posting['id']; ?>">
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
                                <span class="category"><?php echo ucfirst($posting['category']); ?></span>
                                <span><i class="fas fa-map-marker-alt"></i> <?php echo $posting['city'] . ', ' . $posting['state']; ?></span>
                            </div>
                            <div class="posting-actions">
                                <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $posting['contact']); ?>" class="whatsapp-btn" target="_blank">
                                    <i class="fab fa-whatsapp"></i> WhatsApp
                                </a>
                                <a href="tel:<?php echo preg_replace('/[^0-9]/', '', $posting['contact']); ?>" class="call-btn">
                                    <i class="fas fa-phone"></i> Call
                                </a>
                                <a href="https://t.me/+<?php echo preg_replace('/[^0-9]/', '', $posting['contact']); ?>" class="telegram-btn" target="_blank">
                                    <i class="fab fa-telegram"></i> Telegram
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    </div>

                    <?php if ($totalPostings > $perPage): ?>
                        <?php
                        $queryParams = ['category' => $category];
                        echo generatePagination($totalPostings, $perPage, $page, 'category.php', $queryParams);
                        ?>
                    <?php endif; ?>
                <?php else: ?>
                <div class="no-postings" style="text-align: center; padding: 4rem 2rem;">
                    <i class="fas fa-tags" style="font-size: 4rem; color: #64748b; margin-bottom: 1rem;"></i>
                    <h3>No <?php echo strtolower($category); ?> Listings Yet</h3>
                    <p>Be the first to post a <?php echo strtolower($category); ?> service in this category.</p>
                    <a href="addposting.php?category=<?php echo urlencode($category); ?>" class="btn btn-primary" style="font-size: 1.1rem; padding: 1rem 2rem;">
                        <i class="fas fa-plus"></i> Add <?php echo ucfirst($category); ?> Listing
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <script>
    // Cities by state data (same as search.php)
    const citiesByState = {
        'Andhra Pradesh': ['Visakhapatnam', 'Vijayawada', 'Guntur', 'Tirupati', 'Nellore'],
        // ... full list as in other files
        'Delhi': ['New Delhi', 'North Delhi', 'South Delhi']
    };

    function loadCities(state) {
        const citySelect = document.getElementById('city');
        citySelect.innerHTML = '<option value="">All Cities</option>';
        if (state && citiesByState[state]) {
            citiesByState[state].forEach(city => {
                const option = document.createElement('option');
                option.value = city;
                option.textContent = city;
                citySelect.appendChild(option);
            });
        }
    }
    </script>
</body>
</html>
