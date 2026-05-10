<?php
session_start();
require_once 'includes/functions.php';
require_once 'includes/Posting.php';
require_once 'includes/database.php';

// Get database connection
$db = new Database();

// Get active categories for dropdown
$categories = [];
$sql = "SELECT name FROM categories WHERE status = 'active' ORDER BY name";
$result = $db->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row['name'];
    }
}

// Get filters from URL parameters
$category = isset($_GET['category']) ? sanitize($_GET['category']) : null;
$state = isset($_GET['state']) ? sanitize($_GET['state']) : null;
$city = isset($_GET['city']) ? sanitize($_GET['city']) : null;

// Get active categories for dropdown (for case-sensitive matching)
$dbCategories = [];
$sql = "SELECT name FROM categories WHERE status = 'active' ORDER BY name";
$result = $db->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $dbCategories[$row['name']] = $row['name'];
    }
}

// Close database connection after all queries are done
$db->close();

// Try to match category case-insensitively
if ($category) {
    $categoryLower = strtolower($category);
    foreach ($dbCategories as $dbCat) {
        if (strtolower($dbCat) === $categoryLower) {
            $category = $dbCat;
            break;
        }
    }
}

// State mapping for case-insensitive matching
$stateMapping = [
    'andhra pradesh' => 'Andhra Pradesh',
    'arunachal pradesh' => 'Arunachal Pradesh',
    'assam' => 'Assam',
    'bihar' => 'Bihar',
    'chhattisgarh' => 'Chhattisgarh',
    'goa' => 'Goa',
    'gujarat' => 'Gujarat',
    'haryana' => 'Haryana',
    'himachal pradesh' => 'Himachal Pradesh',
    'jharkhand' => 'Jharkhand',
    'karnataka' => 'Karnataka',
    'kerala' => 'Kerala',
    'madhya pradesh' => 'Madhya Pradesh',
    'maharashtra' => 'Maharashtra',
    'manipur' => 'Manipur',
    'meghalaya' => 'Meghalaya',
    'mizoram' => 'Mizoram',
    'nagaland' => 'Nagaland',
    'odisha' => 'Odisha',
    'punjab' => 'Punjab',
    'rajasthan' => 'Rajasthan',
    'sikkim' => 'Sikkim',
    'tamil nadu' => 'Tamil Nadu',
    'telangana' => 'Telangana',
    'tripura' => 'Tripura',
    'uttar pradesh' => 'Uttar Pradesh',
    'uttarakhand' => 'Uttarakhand',
    'west bengal' => 'West Bengal',
    'delhi' => 'Delhi'
];

if ($state) {
    $stateLower = strtolower($state);
    if (isset($stateMapping[$stateLower])) {
        $state = $stateMapping[$stateLower];
    }
}

// Get postings based on filters
$filteredPostings = [];
$postingModel = new Posting();

if ($category || $state || $city) {
    // Get filtered postings
    $filteredPostings = $postingModel->getAll($category, null, $state, $city);
}

// Build page title
$pageTitle = 'Search Results';
if ($category && $city) {
    $pageTitle = ucfirst($category) . ' in ' . $city;
} elseif ($category && $state) {
    $pageTitle = ucfirst($category) . ' in ' . $state;
} elseif ($category) {
    $pageTitle = ucfirst($category) . ' Listings';
} elseif ($state) {
    $pageTitle = 'Postings in ' . $state;
} elseif ($city) {
    $pageTitle = 'Postings in ' . $city;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="/">
    <meta name="description" content="Search <?php echo $category ? ucfirst($category) : ''; ?> listings in <?php echo $city ? $city : ($state ? $state : 'India'); ?>. Find the best <?php echo $category ? strtolower($category) : 'services'; ?> near you.">
    <meta name="keywords" content="<?php echo $category ? $category : 'postings'; ?>, <?php echo $city ? $city : ''; ?>, <?php echo $state ? $state : ''; ?>, classified ads, listings">
    <meta property="og:title" content="<?php echo $pageTitle; ?> - <?php echo APP_NAME; ?>">
    <meta property="og:description" content="Search <?php echo $category ? ucfirst($category) : ''; ?> listings in <?php echo $city ? $city : ($state ? $state : 'India'); ?>.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="<?php echo $pageTitle; ?> - <?php echo APP_NAME; ?>">
    <meta name="twitter:description" content="Search <?php echo $category ? ucfirst($category) : ''; ?> listings in <?php echo $city ? $city : ($state ? $state : 'India'); ?>.">
    <link rel="canonical" href="<?php echo 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "CollectionPage",
        "name": "<?php echo addslashes($pageTitle); ?>",
        "description": "Search <?php echo $category ? ucfirst($category) : ''; ?> listings in <?php echo $city ? $city : ($state ? $state : 'India'); ?>.",
        "url": "<?php echo 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>",
        "mainEntity": {
            "@type": "ItemList",
            "itemListElement": [
                <?php foreach (array_slice($filteredPostings, 0, 5) as $index => $posting): ?>
                {
                    "@type": "ListItem",
                    "position": <?php echo $index + 1; ?>,
                    "url": "<?php echo 'https://' . $_SERVER['HTTP_HOST'] . '/add-posting/posting/' . strtolower(str_replace(' ', '-', preg_replace('/[^a-zA-Z0-9 ]/', '', $posting['title']))); ?>"
                }<?php echo $index < min(4, count($filteredPostings) - 1) ? ',' : ''; ?>
                <?php endforeach; ?>
            ]
        }
    }
    </script>
    <title><?php echo $pageTitle; ?> - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time() + 1000; ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .search-page-header {
            background: linear-gradient(135deg, #010101 0%, #08030c 100%);  
            padding: 3rem 0;
            text-align: center;
            color: white;
        }
        .search-page-header h1 {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }
        .search-page-header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        .search-filters-bar {
            background: #282b2e;
            padding: 1rem 0;
            border-bottom: 1px solid #282b2e;
        }
        .search-filters-bar .container {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .filter-tag {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: #282b2e;
            border: 1px solid #282b2e;
            border-radius: 50px;
            font-size: 0.9rem;
            color: #7a899d;
        }
        .filter-tag i {
            color: #667eea;
        }
        .filter-tag .remove {
            margin-left: 0.5rem;
            color: #94a3b8;
            cursor: pointer;
        }
        .filter-tag .remove:hover {
            color: #ef4444;
        }
        .results-count {
            margin-left: auto;
            color: #64748b;
            font-weight: 500;
        }
    </style>
</head>
<body class="index-body">
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
                    <li><a href="index.php">Home</a></li>
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

    <!-- Search Page Header -->
    <section class="search-page-header">
        <div class="container">
            <h1><?php echo $pageTitle; ?></h1>
            <p>Browse all <?php echo $category ? ucfirst($category) : ''; ?> services in <?php echo $city ? $city : ($state ? $state : 'all India'); ?></p>
        </div>
    </section>

  <section class="search-filters-bar">
    <div class="container">

        <!-- Home -->
        <a href="search.php" class="filter-tag">
            <i class="fas fa-home"></i> Home
        </a>

        <!-- Category -->
        <?php if ($category): ?>
            <a href="search.php?category=<?php echo urlencode($category); ?>" class="filter-tag">
                <i class="fas fa-tag"></i> <?php echo ucfirst($category); ?>
            </a>
        <?php endif; ?>

        <!-- State -->
        <?php if ($state): ?>
            <a href="search.php?<?php 
                echo $category ? 'category=' . urlencode($category) . '&' : ''; 
                echo 'state=' . urlencode($state); 
            ?>" class="filter-tag">
                <i class="fas fa-map-marker-alt"></i> <?php echo $state; ?>
            </a>
        <?php endif; ?>

        <!-- City -->
        <?php if ($city): ?>
            <a href="search.php?<?php 
                echo $category ? 'category=' . urlencode($category) . '&' : ''; 
                echo $state ? 'state=' . urlencode($state) . '&' : ''; 
                echo 'city=' . urlencode($city); 
            ?>" class="filter-tag">
                <i class="fas fa-map-marker"></i> <?php echo $city; ?>
            </a>
        <?php endif; ?>

        <!-- Results Count -->
        <span class="results-count">
            Found <?php echo count($filteredPostings); ?> posting<?php echo count($filteredPostings) != 1 ? 's' : ''; ?>
        </span>

    </div>
</section>

    <!-- Search Form -->
    <section class="index-hero" style="padding: 2rem 0;" >
        <div class="container">
            <div class="search-container">
                <form method="GET" action="search.php" id="searchForm">
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
                        <label for="state">State</label>
                        <select id="state" name="state" onchange="loadCities(this.value)" style="width: 100%; padding: 0.75rem; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 1rem; background: white;">
                            <option value="">Select a state</option>
                            <option value="Andhra Pradesh" <?php echo $state === 'Andhra Pradesh' ? 'selected' : ''; ?>>Andhra Pradesh</option>
                            <option value="Arunachal Pradesh" <?php echo $state === 'Arunachal Pradesh' ? 'selected' : ''; ?>>Arunachal Pradesh</option>
                            <option value="Assam" <?php echo $state === 'Assam' ? 'selected' : ''; ?>>Assam</option>
                            <option value="Bihar" <?php echo $state === 'Bihar' ? 'selected' : ''; ?>>Bihar</option>
                            <option value="Chhattisgarh" <?php echo $state === 'Chhattisgarh' ? 'selected' : ''; ?>>Chhattisgarh</option>
                            <option value="Goa" <?php echo $state === 'Goa' ? 'selected' : ''; ?>>Goa</option>
                            <option value="Gujarat" <?php echo $state === 'Gujarat' ? 'selected' : ''; ?>>Gujarat</option>
                            <option value="Haryana" <?php echo $state === 'Haryana' ? 'selected' : ''; ?>>Haryana</option>
                            <option value="Himachal Pradesh" <?php echo $state === 'Himachal Pradesh' ? 'selected' : ''; ?>>Himachal Pradesh</option>
                            <option value="Jharkhand" <?php echo $state === 'Jharkhand' ? 'selected' : ''; ?>>Jharkhand</option>
                            <option value="Karnataka" <?php echo $state === 'Karnataka' ? 'selected' : ''; ?>>Karnataka</option>
                            <option value="Kerala" <?php echo $state === 'Kerala' ? 'selected' : ''; ?>>Kerala</option>
                            <option value="Madhya Pradesh" <?php echo $state === 'Madhya Pradesh' ? 'selected' : ''; ?>>Madhya Pradesh</option>
                            <option value="Maharashtra" <?php echo $state === 'Maharashtra' ? 'selected' : ''; ?>>Maharashtra</option>
                            <option value="Manipur" <?php echo $state === 'Manipur' ? 'selected' : ''; ?>>Manipur</option>
                            <option value="Meghalaya" <?php echo $state === 'Meghalaya' ? 'selected' : ''; ?>>Meghalaya</option>
                            <option value="Mizoram" <?php echo $state === 'Mizoram' ? 'selected' : ''; ?>>Mizoram</option>
                            <option value="Nagaland" <?php echo $state === 'Nagaland' ? 'selected' : ''; ?>>Nagaland</option>
                            <option value="Odisha" <?php echo $state === 'Odisha' ? 'selected' : ''; ?>>Odisha</option>
                            <option value="Punjab" <?php echo $state === 'Punjab' ? 'selected' : ''; ?>>Punjab</option>
                            <option value="Rajasthan" <?php echo $state === 'Rajasthan' ? 'selected' : ''; ?>>Rajasthan</option>
                            <option value="Sikkim" <?php echo $state === 'Sikkim' ? 'selected' : ''; ?>>Sikkim</option>
                            <option value="Tamil Nadu" <?php echo $state === 'Tamil Nadu' ? 'selected' : ''; ?>>Tamil Nadu</option>
                            <option value="Telangana" <?php echo $state === 'Telangana' ? 'selected' : ''; ?>>Telangana</option>
                            <option value="Tripura" <?php echo $state === 'Tripura' ? 'selected' : ''; ?>>Tripura</option>
                            <option value="Uttar Pradesh" <?php echo $state === 'Uttar Pradesh' ? 'selected' : ''; ?>>Uttar Pradesh</option>
                            <option value="Uttarakhand" <?php echo $state === 'Uttarakhand' ? 'selected' : ''; ?>>Uttarakhand</option>
                            <option value="West Bengal" <?php echo $state === 'West Bengal' ? 'selected' : ''; ?>>West Bengal</option>
                            <option value="Delhi" <?php echo $state === 'Delhi' ? 'selected' : ''; ?>>Delhi</option>
                        </select>
                    </div>
                    <div class="search-field">
                        <label for="city">City</label>
                        <select id="city" name="city" style="width: 100%; padding: 0.75rem; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 1rem; background: white;">
                            <option value="">Select a city</option>
                            <?php if (!empty($state)): ?>
                                <?php
                                $citiesByState = [
                                    'Andhra Pradesh' => ['Visakhapatnam', 'Vijayawada', 'Guntur', 'Tirupati', 'Nellore', 'Kakinada', 'Rajahmundry', 'Kadapa', 'Kurnool', 'Anantapur'],
                                    'Arunachal Pradesh' => ['Itanagar', 'Naharlagun', 'Pasighat', 'Tezpur', 'Dibang Valley', 'Roing', 'Ziro', 'Bomdila'],
                                    'Assam' => ['Guwahati', 'Silchar', 'Dibrugarh', 'Jorhat', 'Nagaon', 'Tinsukia', 'Tezpur', 'Bongaigaon'],
                                    'Bihar' => ['Patna', 'Gaya', 'Bhagalpur', 'Muzaffarpur', 'Darbhanga', 'Arrah', 'Begusarai', 'Katihar', 'Munger', 'Purnia'],
                                    'Chhattisgarh' => ['Raipur', 'Bhilai', 'Bilaspur', 'Durg', 'Rajnandgaon', 'Korba', 'Raigarh', 'Mahasamund'],
                                    'Goa' => ['Panaji', 'Margao', 'Vasco da Gama', 'Mapusa', 'Ponda', 'Curchorem', 'Benaulim'],
                                    'Gujarat' => ['Ahmedabad', 'Surat', 'Vadodara', 'Rajkot', 'Bhavnagar', 'Jamnagar', 'Junagadh', 'Gandhidham', 'Anand', 'Bharuch'],
                                    'Haryana' => ['Faridabad', 'Gurgaon', 'Panipat', 'Karnal', 'Rohtak', 'Hisar', 'Sonipat', 'Ambala', 'Yamunanagar', 'Kurukshetra'],
                                    'Himachal Pradesh' => ['Shimla', 'Mandi', 'Solan', 'Kullu', 'Manali', 'Dharamshala', 'Kangra', 'Chamba', 'Bilaspur'],
                                    'Jharkhand' => ['Ranchi', 'Jamshedpur', 'Dhanbad', 'Bokaro Steel City', 'Hazaribagh', 'Deoghar', 'Ramgarh', 'Giridih'],
                                    'Karnataka' => ['Bengaluru', 'Mysore', 'Mangalore', 'Hubli-Dharwad', 'Belgaum', 'Gulbarga', 'Dharwad', 'Udupi', 'Hassan', 'Bellary'],
                                    'Kerala' => ['Thiruvananthapuram', 'Kochi', 'Kozhikode', 'Thrissur', 'Kollam', 'Palakkad', 'Alappuzha', 'Kannur', 'Kottayam', 'Ernakulam'],
                                    'Madhya Pradesh' => ['Bhopal', 'Indore', 'Gwalior', 'Jabalpur', 'Ujjain', 'Sagar', 'Ratlam', 'Satna', 'Burhanpur', 'Khandwa'],
                                    'Maharashtra' => ['Mumbai', 'Pune', 'Nagpur', 'Thane', 'Nashik', 'Aurangabad', 'Solapur', 'Kolhapur', 'Navi Mumbai', 'Sangli'],
                                    'Manipur' => ['Imphal', 'Thoubal', 'Bishnupur', 'Churachandpur', 'Ukhrul', 'Tamenglong', 'Senapati'],
                                    'Meghalaya' => ['Shillong', 'Tura', 'Nongstoin', 'Jowai', 'Baghmara', 'Williamnagar', 'Mawkyrwat'],
                                    'Mizoram' => ['Aizawl', 'Lunglei', 'Champhai', 'Serchhip', 'Kolasib', 'Mamit', 'Saitual'],
                                    'Nagaland' => ['Kohima', 'Dimapur', 'Mokokchung', 'Wokha', 'Tuensang', 'Phek', 'Zunheboto'],
                                    'Odisha' => ['Bhubaneswar', 'Cuttack', 'Rourkela', 'Berhampur', 'Sambalpur', 'Balasore', 'Bhadrak', 'Angul', 'Jharsuguda', 'Puri'],
                                    'Punjab' => ['Ludhiana', 'Amritsar', 'Jalandhar', 'Patiala', 'Bathinda', 'Mohali', 'Hoshiarpur', 'Kapurthala', 'Ferozepur', 'Moga'],
                                    'Rajasthan' => ['Jaipur', 'Jodhpur', 'Udaipur', 'Kota', 'Bikaner', 'Ajmer', 'Pilani', 'Bhilwara', 'Alwar', 'Bharatpur'],
                                    'Sikkim' => ['Gangtok', 'Namchi', 'Gyalshing', 'Rabong', 'Soreng', 'Jorethang', 'Mangan'],
                                    'Tamil Nadu' => ['Chennai', 'Coimbatore', 'Madurai', 'Tiruchirappalli', 'Salem', 'Tiruppur', 'Vellore', 'Erode', 'Tirunelveli', 'Thanjavur'],
                                    'Telangana' => ['Hyderabad', 'Warangal', 'Karimnagar', 'Khammam', 'Secunderabad', 'Nizamabad', 'Ramagundam', 'Suryapet', 'Miryalguda', 'Kothakota'],
                                    'Tripura' => ['Agartala', 'Udaipur', 'Dharmanagar', 'Kailasahar', 'Belonia', 'Khowai', 'Sabroom'],
                                    'Uttar Pradesh' => ['Lucknow', 'Kanpur', 'Ghaziabad', 'Agra', 'Varanasi', 'Allahabad', 'Meerut', 'Aligarh', 'Moradabad', 'Saharanpur'],
                                    'Uttarakhand' => ['Dehradun', 'Haridwar', 'Roorkee', 'Haldwani', 'Kashipur', 'Rishikesh', 'Rudrapur', 'Kotdwar', 'Ramnagar'],
                                    'West Bengal' => ['Kolkata', 'Howrah', 'Asansol', 'Siliguri', 'Durgapur', 'Bardhaman', 'Malda', 'Kharagpur', 'Berhampore', 'Baharampur'],
                                    'Delhi' => ['New Delhi', 'North Delhi', 'South Delhi', 'East Delhi', 'West Delhi', 'Central Delhi', 'Old Delhi']
                                ];
                                $cities = $citiesByState[$state] ?? [];
                                foreach ($cities as $c): ?>
                                <option value="<?php echo $c; ?>" <?php echo $city === $c ? 'selected' : ''; ?>><?php echo $c; ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <button type="submit">
                        <i class="fas fa-search"></i> Search
                    </button>
                    <?php if ($category || $state || $city): ?>
                        <a href="search.php" class="btn btn-secondary" style="padding: 0.75rem 1.25rem; border-radius: 8px; text-decoration: none; color: white;margin-top:18px">
                            <i class="fas fa-times"></i> Clear
                        </a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </section>

    <!-- Search Results -->
    <section class="featured-listings">
        <div class="container">
            <div class="listings-header">
                <h2>
                    <i class="fas fa-list"></i>
                    Search Results
                </h2>
                <a href="index.php">Back to Home</a>
            </div>

            <div class="posting-cards">
                <?php if (!empty($filteredPostings)): ?>
                    <?php foreach ($filteredPostings as $index => $posting): ?>
                    <div class="posting-card">
                            <div class="posting-image">
<a href="posting/<?php echo strtolower(str_replace(' ', '-', preg_replace('/[^a-zA-Z0-9 ]/', '', $posting['title']))); ?>" style="display: block; width: 100%; height: 100%;">
                                <?php if (!empty($posting['images'])): ?>
                                    <img src="uploads/postings/<?php echo explode(',', $posting['images'])[0]; ?>" alt="<?php echo $posting['title']; ?>">
                                <?php else: ?>
                                    <div style="width: 100%; height: 100%; background: #f1f5f9; display: flex; align-items: center; justify-content: center; color: #64748b;">
                                        <i class="fas fa-image" style="font-size: 3rem;"></i>
                                    </div>
                                <?php endif; ?>
                                </a>
                            </div>
                            <div class="posting-content">
                                <div class="posting-header">
                                    <a href="posting/<?php echo strtolower(str_replace(' ', '-', preg_replace('/[^a-zA-Z0-9 ]/', '', $posting['title']))); ?>" style="display: block; width: 100%; height: 100%;">
                                         <h3 class="posting-title">
                                        <?php echo htmlspecialchars(mb_strimwidth($posting['title'], 0, 80, '...')); ?>
                                    </h3>
                                        </a>
                                    <div class="posting-price">
                                        <?php if (!empty($posting['price']) && $posting['price'] > 0): ?>
                                            ₹<?php echo number_format($posting['price'], 2); ?>/hr
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <p class="posting-description"><?php echo substr($posting['description'], 0, 100); ?>...</p>
                                <div class="posting-meta">
                                    <span><i class="fas fa-calendar"></i> <?php echo rand(2, 25); ?> Years</span>
                                    <span><i class="fas fa-map-marker-alt"></i> <?php echo $posting['city'] . ', ' . $posting['state']; ?></span>
                                    <span><i class="fas fa-check-circle"></i> Verified</span>
                                </div>
                                <div class="posting-actions">
                                    <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $posting['contact']); ?>" class="whatsapp-btn" target="_blank">
                                        <i class="fab fa-whatsapp"></i>
                                    </a>
                                    <a href="tel:<?php echo preg_replace('/[^0-9]/', '', $posting['contact']); ?>" class="call-btn" target="_blank">
                                        <i class="fas fa-phone"></i>
                                    </a>
                                </div>
                            </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-postings">
                        <i class="fas fa-search"></i>
                        <h3>No Postings Found</h3>
                        <p>
                            No postings match your selected filters. Try different category or state.
                        </p>
                        <?php if (isLoggedIn()): ?>
                            <a href="add-posting.php" class="btn btn-primary">Add Posting</a>
                        <?php else: ?>
                            <a href="register.php" class="btn btn-primary">Register to Post</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <footer>
        <div class="container">
            <p>&copy; 2024 <?php echo APP_NAME; ?>. All rights reserved.</p>
        </div>
    </footer>

    <script src="assets/js/main.js?v=<?php echo time(); ?>"></script>
    <script>
        const citiesByState = {
            'Andhra Pradesh': ['Visakhapatnam', 'Vijayawada', 'Guntur', 'Tirupati', 'Nellore', 'Kakinada', 'Rajahmundry', 'Kadapa', 'Kurnool', 'Anantapur'],
            'Arunachal Pradesh': ['Itanagar', 'Naharlagun', 'Pasighat', 'Tezpur', 'Dibang Valley', 'Roing', 'Ziro', 'Bomdila'],
            'Assam': ['Guwahati', 'Silchar', 'Dibrugarh', 'Jorhat', 'Nagaon', 'Tinsukia', 'Tezpur', 'Bongaigaon'],
            'Bihar': ['Patna', 'Gaya', 'Bhagalpur', 'Muzaffarpur', 'Darbhanga', 'Arrah', 'Begusarai', 'Katihar', 'Munger', 'Purnia'],
            'Chhattisgarh': ['Raipur', 'Bhilai', 'Bilaspur', 'Durg', 'Rajnandgaon', 'Korba', 'Raigarh', 'Mahasamund'],
            'Goa': ['Panaji', 'Margao', 'Vasco da Gama', 'Mapusa', 'Ponda', 'Curchorem', 'Benaulim'],
            'Gujarat': ['Ahmedabad', 'Surat', 'Vadodara', 'Rajkot', 'Bhavnagar', 'Jamnagar', 'Junagadh', 'Gandhidham', 'Anand', 'Bharuch'],
            'Haryana': ['Faridabad', 'Gurgaon', 'Panipat', 'Karnal', 'Rohtak', 'Hisar', 'Sonipat', 'Ambala', 'Yamunanagar', 'Kurukshetra'],
            'Himachal Pradesh': ['Shimla', 'Mandi', 'Solan', 'Kullu', 'Manali', 'Dharamshala', 'Kangra', 'Chamba', 'Bilaspur'],
            'Jharkhand': ['Ranchi', 'Jamshedpur', 'Dhanbad', 'Bokaro Steel City', 'Hazaribagh', 'Deoghar', 'Ramgarh', 'Giridih'],
            'Karnataka': ['Bengaluru', 'Mysore', 'Mangalore', 'Hubli-Dharwad', 'Belgaum', 'Gulbarga', 'Dharwad', 'Udupi', 'Hassan', 'Bellary'],
            'Kerala': ['Thiruvananthapuram', 'Kochi', 'Kozhikode', 'Thrissur', 'Kollam', 'Palakkad', 'Alappuzha', 'Kannur', 'Kottayam', 'Ernakulam'],
            'Madhya Pradesh': ['Bhopal', 'Indore', 'Gwalior', 'Jabalpur', 'Ujjain', 'Sagar', 'Ratlam', 'Satna', 'Burhanpur', 'Khandwa'],
            'Maharashtra': ['Mumbai', 'Pune', 'Nagpur', 'Thane', 'Nashik', 'Aurangabad', 'Solapur', 'Kolhapur', 'Navi Mumbai', 'Sangli'],
            'Manipur': ['Imphal', 'Thoubal', 'Bishnupur', 'Churachandpur', 'Ukhrul', 'Tamenglong', 'Senapati'],
            'Meghalaya': ['Shillong', 'Tura', 'Nongstoin', 'Jowai', 'Baghmara', 'Williamnagar', 'Mawkyrwat'],
            'Mizoram': ['Aizawl', 'Lunglei', 'Champhai', 'Serchhip', 'Kolasib', 'Mamit', 'Saitual'],
            'Nagaland': ['Kohima', 'Dimapur', 'Mokokchung', 'Wokha', 'Tuensang', 'Phek', 'Zunheboto'],
            'Odisha': ['Bhubaneswar', 'Cuttack', 'Rourkela', 'Berhampur', 'Sambalpur', 'Balasore', 'Bhadrak', 'Angul', 'Jharsuguda', 'Puri'],
            'Punjab': ['Ludhiana', 'Amritsar', 'Jalandhar', 'Patiala', 'Bathinda', 'Mohali', 'Hoshiarpur', 'Kapurthala', 'Ferozepur', 'Moga'],
            'Rajasthan': ['Jaipur', 'Jodhpur', 'Udaipur', 'Kota', 'Bikaner', 'Ajmer', 'Pilani', 'Bhilwara', 'Alwar', 'Bharatpur'],
            'Sikkim': ['Gangtok', 'Namchi', 'Gyalshing', 'Rabong', 'Soreng', 'Jorethang', 'Mangan'],
            'Tamil Nadu': ['Chennai', 'Coimbatore', 'Madurai', 'Tiruchirappalli', 'Salem', 'Tiruppur', 'Vellore', 'Erode', 'Tirunelveli', 'Thanjavur'],
            'Telangana': ['Hyderabad', 'Warangal', 'Karimnagar', 'Khammam', 'Secunderabad', 'Nizamabad', 'Ramagundam', 'Suryapet', 'Miryalguda', 'Kothakota'],
            'Tripura': ['Agartala', 'Udaipur', 'Dharmanagar', 'Kailasahar', 'Belonia', 'Khowai', 'Sabroom'],
            'Uttar Pradesh': ['Lucknow', 'Kanpur', 'Ghaziabad', 'Agra', 'Varanasi', 'Allahabad', 'Meerut', 'Aligarh', 'Moradabad', 'Saharanpur'],
            'Uttarakhand': ['Dehradun', 'Haridwar', 'Roorkee', 'Haldwani', 'Kashipur', 'Rishikesh', 'Rudrapur', 'Kotdwar', 'Ramnagar'],
            'West Bengal': ['Kolkata', 'Howrah', 'Asansol', 'Siliguri', 'Durgapur', 'Bardhaman', 'Malda', 'Kharagpur', 'Berhampore', 'Baharampur'],
            'Delhi': ['New Delhi', 'North Delhi', 'South Delhi', 'East Delhi', 'West Delhi', 'Central Delhi', 'Old Delhi']
        };

        function loadCities(state) {
            const citySelect = document.getElementById('city');
            citySelect.innerHTML = '<option value="">Select a city</option>';
            
            if (state && citiesByState[state]) {
                citiesByState[state].forEach(city => {
                    const option = document.createElement('option');
                    option.value = city;
                    option.textContent = city;
                    citySelect.appendChild(option);
                });
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const searchForm = document.getElementById('searchForm');
            if (searchForm) {
                searchForm.addEventListener('submit', function(e) {
                    const category = document.getElementById('category').value;
                    const state = document.getElementById('state').value;
                    const city = document.getElementById('city').value;
                    
                    let url = 'search.php?';
                    const params = [];
                    
                    if (category) {
                        params.push('category=' + encodeURIComponent(category));
                    }
                    if (state) {
                        params.push('state=' + encodeURIComponent(state));
                    }
                    if (city) {
                        params.push('city=' + encodeURIComponent(city));
                    }
                    
                    if (params.length > 0) {
                        window.location.href = url + params.join('&');
                        e.preventDefault();
                    }
                });
            }
        });
    </script>
</body>
</html>
