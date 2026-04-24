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

// Get all categories with images for categories section
$allCategories = [];
$sql = "SELECT * FROM categories WHERE status = 'active' ORDER BY name";
$result = $db->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $allCategories[] = $row;
    }
}
$db->close();

// Get filters from URL parameters
$category = isset($_GET['category']) ? sanitize($_GET['category']) : null;
$state = isset($_GET['state']) ? sanitize($_GET['state']) : null;
$city = isset($_GET['city']) ? sanitize($_GET['city']) : null;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10;

// Get postings based on filters or show VIP listings if no filters
$filteredPostings = [];
$totalPostings = 0;
$postingModel = new Posting();

if ($category || $state || $city) {
    // Get filtered postings with pagination
    $offset = ($page - 1) * $perPage;
    $filteredPostings = $postingModel->getAll($category, null, $state, $city, $perPage, $offset);
    $totalPostings = $postingModel->getTotalCount($category, null, $state, $city);
} else {
    // Get VIP featured listings (recent 6 postings) - no pagination for home page
    $filteredPostings = $postingModel->getRecent(6);
}

// Get similar postings only if we have postings and no filters
$similarPostings = [];
if (!empty($filteredPostings) && !$category && !$state && !$city) {
    $firstPosting = $filteredPostings[0];
    $similarPostings = $postingModel->getSimilar($firstPosting['category'], $firstPosting['id'], 4);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="/addposting/">
    <meta name="description" content="<?php echo APP_NAME; ?> - Buy and sell everything in India. Find listings for <?php echo !empty($categories) ? implode(', ', array_slice($categories, 0, 5)) : 'various categories'; ?>. Post your ads for free.">
    <meta name="google-site-verification" content="yb5NctbbrRbjl4vT9_E2ffWAyPnNdHO-esGbdmlfGcI" />
    <meta name="keywords" content="classified ads, buy and sell, <?php echo !empty($categories) ? implode(', ', $categories) : 'postings'; ?>, India">
    <meta property="og:title" content="<?php echo APP_NAME; ?> - Buy and Sell Everything">
    <meta property="og:description" content="Buy and sell everything in India. Find the best deals near you.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo APP_NAME; ?> - Buy and Sell Everything">
    <meta name="twitter:description" content="Buy and sell everything in India. Find the best deals near you.">
    <link rel="canonical" href="<?php echo 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
    <meta property="og:image" content="<?php echo 'https://' . $_SERVER['HTTP_HOST'] . '/addposting/assets/images/logo.png'; ?>">
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebSite",
        "name": "<?php echo addslashes(APP_NAME); ?>",
        "url": "<?php echo 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>",
        "description": "Buy and sell everything in India. Find the best deals near you.",
        "potentialAction": {
            "@type": "SearchAction",
            "target": "<?php echo 'https://' . $_SERVER['HTTP_HOST']; ?>/addposting/search.php?category={search_term_string}",
            "query-input": "required name=search_term_string"
        }
    }
    </script>
    <title><?php echo APP_NAME; ?> - Buy and Sell Everything</title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time() + 1000; ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .similar-postings .posting-cards {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }
        .similar-postings .posting-card {
            flex-direction: column;
            height: 100%;
        }
        .similar-postings .posting-image {
            width: 100%;
            height: 200px;
        }
        .similar-postings .posting-price {
            margin-top: 10px;
            text-align: left;
        }
        .similar-postings .posting-meta {
            flex-wrap: wrap;
        }
        .similar-postings .posting-title {
            font-size: 1.1rem;
        }
        
        .categories-section {
            padding: 3rem 0;
            background: #0f172a;
        }
        .categories-section .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 1rem;
        }
        .categories-section h2 {
            color: #f1f5f9;
            font-size: 1.75rem;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .categories-section h2 i {
            color: #fbbf24;
        }
        .category-cards {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 1.5rem;
        }
        .category-card {
            background: #1e293b;
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s ease;
            cursor: pointer;
            border: 1px solid #334155;
        }
        .category-card:hover {
            transform: translateY(-5px);
            border-color: #fbbf24;
            box-shadow: 0 10px 30px rgba(251, 191, 36, 0.15);
        }
        .category-card-image {
            width: 100%;
            height: 140px;
            background: #334155;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .category-card-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .category-card-image i {
            font-size: 2.5rem;
            color: #64748b;
        }
        .category-card-content {
            padding: 1rem;
        }
        .category-card-name {
            color: #f1f5f9;
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        .category-card-desc {
            color: #94a3b8;
            font-size: 0.8rem;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        @media (max-width: 1200px) {
            .category-cards {
                grid-template-columns: repeat(4, 1fr);
            }
        }
        @media (max-width: 900px) {
            .category-cards {
                grid-template-columns: repeat(3, 1fr);
            }
        }
        @media (max-width: 600px) {
            .category-cards {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media (max-width: 400px) {
            .category-cards {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 1200px) {
            .similar-postings .posting-cards {
                grid-template-columns: repeat(3, 1fr);
            }
        }
        @media (max-width: 900px) {
            .similar-postings .posting-cards {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media (max-width: 600px) {
            .similar-postings .posting-cards {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body class="index-body">
    <!-- Age Verification Modal -->
    <div class="modal fade" id="ageVerificationModal" tabindex="-1" aria-labelledby="ageVerificationModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content age-verification-modal">
                <div class="modal-header age-modal-header">
                    <h5 class="modal-title age-modal-title" id="ageVerificationModalLabel">
                        <i class="fas fa-exclamation-triangle"></i>
                        Age Verification Required
                    </h5>
                </div>
                <div class="modal-body age-modal-body">
                    <div class="age-verification-content">
                        <i class="fas fa-user-shield age-icon"></i>
                        <h4 class="age-question">Are you 18 years or older?</h4>
                        <p class="age-description">This website contains content intended for adults only. By proceeding, you confirm that you are 18 years of age or older and agree to view adult content.</p>
                        <div class="age-buttons">
                            <button type="button" class="btn btn-primary age-btn-confirm" id="confirmAge">
                                <i class="fas fa-check"></i> Yes, I am 18+
                            </button>
                            <button type="button" class="btn btn-secondary age-btn-deny" id="denyAge">
                                <i class="fas fa-times"></i> No, I am under 18
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
    .age-verification-modal {
        border: none;
        border-radius: 15px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        overflow: hidden;
    }

    .age-modal-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-bottom: none;
        padding: 2rem 2rem 1.5rem;
        text-align: center;
    }

    .age-modal-title {
        font-size: 1.5rem;
        font-weight: 600;
        margin: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }

    .age-modal-title i {
        color: #ffd700;
        font-size: 1.2rem;
    }

    .age-modal-body {
        padding: 2.5rem 2rem;
        background: white;
    }

    .age-verification-content {
        text-align: center;
    }

    .age-icon {
        font-size: 4rem;
        color: #667eea;
        margin-bottom: 1.5rem;
        opacity: 0.8;
    }

    .age-question {
        font-size: 1.8rem;
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 1rem;
    }

    .age-description {
        font-size: 1rem;
        color: #718096;
        line-height: 1.6;
        margin-bottom: 2rem;
        max-width: 400px;
        margin-left: auto;
        margin-right: auto;
    }

    .age-buttons {
        display: flex;
        gap: 1rem;
        justify-content: center;
        flex-wrap: wrap;
    }

    .age-btn-confirm {
        background-color: #667eea;
        border-color: #667eea;
        padding: 0.75rem 2rem;
        font-weight: 600;
        border-radius: 8px;
        transition: all 0.3s ease;
        min-width: 140px;
    }

    .age-btn-confirm:hover {
        background-color: #5a6fd8;
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
    }

    .age-btn-deny {
        background-color: #6c757d;
        border-color: #6c757d;
        padding: 0.75rem 2rem;
        font-weight: 600;
        border-radius: 8px;
        transition: all 0.3s ease;
        min-width: 140px;
    }

    .age-btn-deny:hover {
        background-color: #5a6268;
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(108, 117, 125, 0.3);
    }

    @media (max-width: 576px) {
        .age-modal-header {
            padding: 1.5rem 1rem 1rem;
        }

        .age-modal-title {
            font-size: 1.3rem;
        }

        .age-modal-body {
            padding: 2rem 1rem;
        }

        .age-question {
            font-size: 1.5rem;
        }

        .age-description {
            font-size: 0.95rem;
        }

        .age-buttons {
            flex-direction: column;
            align-items: center;
        }

        .age-btn-confirm,
        .age-btn-deny {
            width: 100%;
            max-width: 250px;
        }
    }
    </style>

    <?php include 'includes/header.php'; ?>

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

    <section class="index-hero">
        <div class="container">
            <h1>Find Featured Services & Ads</h1>
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
                            <option value="Andhra Pradesh" <?php echo $state === 'Andhra Pradesh' ? 'selected' : ''; ?>>
Andhra Pradesh</option>
                            <option value="Arunachal Pradesh" <?php echo $state === 'Arunachal Pradesh' ? 'selected' : ''; ?>>
Arunachal Pradesh</option>
                            <option value="Assam" <?php echo $state === 'Assam' ? 'selected' : ''; ?>>
Assam</option>
                            <option value="Bihar" <?php echo $state === 'Bihar' ? 'selected' : ''; ?>>
Bihar</option>
                            <option value="Chhattisgarh" <?php echo $state === 'Chhattisgarh' ? 'selected' : ''; ?>>
Chhattisgarh</option>
                            <option value="Goa" <?php echo $state === 'Goa' ? 'selected' : ''; ?>>
Goa</option>
                            <option value="Gujarat" <?php echo $state === 'Gujarat' ? 'selected' : ''; ?>>
Gujarat</option>
                            <option value="Haryana" <?php echo $state === 'Haryana' ? 'selected' : ''; ?>>
Haryana</option>
                            <option value="Himachal Pradesh" <?php echo $state === 'Himachal Pradesh' ? 'selected' : ''; ?>>
Himachal Pradesh</option>
                            <option value="Jharkhand" <?php echo $state === 'Jharkhand' ? 'selected' : ''; ?>>
Jharkhand</option>
                            <option value="Karnataka" <?php echo $state === 'Karnataka' ? 'selected' : ''; ?>>
Karnataka</option>
                            <option value="Kerala" <?php echo $state === 'Kerala' ? 'selected' : ''; ?>>
Kerala</option>
                            <option value="Madhya Pradesh" <?php echo $state === 'Madhya Pradesh' ? 'selected' : ''; ?>>
Madhya Pradesh</option>
                            <option value="Maharashtra" <?php echo $state === 'Maharashtra' ? 'selected' : ''; ?>>
Maharashtra</option>
                            <option value="Manipur" <?php echo $state === 'Manipur' ? 'selected' : ''; ?>>
Manipur</option>
                            <option value="Meghalaya" <?php echo $state === 'Meghalaya' ? 'selected' : ''; ?>>
Meghalaya</option>
                            <option value="Mizoram" <?php echo $state === 'Mizoram' ? 'selected' : ''; ?>>
Mizoram</option>
                            <option value="Nagaland" <?php echo $state === 'Nagaland' ? 'selected' : ''; ?>>
Nagaland</option>
                            <option value="Odisha" <?php echo $state === 'Odisha' ? 'selected' : ''; ?>>
Odisha</option>
                            <option value="Punjab" <?php echo $state === 'Punjab' ? 'selected' : ''; ?>>
Punjab</option>
                            <option value="Rajasthan" <?php echo $state === 'Rajasthan' ? 'selected' : ''; ?>>
Rajasthan</option>
                            <option value="Sikkim" <?php echo $state === 'Sikkim' ? 'selected' : ''; ?>>
Sikkim</option>
                            <option value="Tamil Nadu" <?php echo $state === 'Tamil Nadu' ? 'selected' : ''; ?>>
Tamil Nadu</option>
                            <option value="Telangana" <?php echo $state === 'Telangana' ? 'selected' : ''; ?>>
Telangana</option>
                            <option value="Tripura" <?php echo $state === 'Tripura' ? 'selected' : ''; ?>>
Tripura</option>
                            <option value="Uttar Pradesh" <?php echo $state === 'Uttar Pradesh' ? 'selected' : ''; ?>>
Uttar Pradesh</option>
                            <option value="Uttarakhand" <?php echo $state === 'Uttarakhand' ? 'selected' : ''; ?>>
Uttarakhand</option>
                            <option value="West Bengal" <?php echo $state === 'West Bengal' ? 'selected' : ''; ?>>
West Bengal</option>
                            <option value="Delhi" <?php echo $state === 'Delhi' ? 'selected' : ''; ?>>
Delhi</option>
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
                        <i class="fas fa-search"></i> Search Now
                    </button>
                    <?php if ($category || $state || $city): ?>
                        <a href="index.php" class="btn btn-secondary" style="padding: 0.75rem 1.25rem; border-radius: 8px; text-decoration: none; color: white;margin-top:18px">
                            <i class="fas fa-times"></i> Clear
                        </a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </section>

    <?php if (!empty($allCategories)): ?>
    <section class="categories-section">
        <div class="container">
            <h2>
                <i class="fas fa-th-large"></i>
                Browse Categories
            </h2>

            <div class="category-cards">
                <?php foreach ($allCategories as $cat): ?>
                <a href="category/<?php echo urlencode($cat['name']); ?>" class="category-card">
                    <div class="category-card-image">
                        <?php if (!empty($cat['image'])): ?>
                            <img src="uploads/categories/<?php echo $cat['image']; ?>" alt="<?php echo $cat['name']; ?>">
                        <?php else: ?>
                            <i class="fas fa-<?php 
                                $iconMap = [
                                    'Electronics' => 'laptop',
                                    'Furniture' => 'couch',
                                    'Vehicles' => 'car',
                                    'Real Estate' => 'home',
                                    'Jobs' => 'briefcase',
                                    'Services' => 'hand-holding-heart',
                                    'Clothing' => 'tshirt',
                                    'Books' => 'book-open',
                                    'Sports' => 'football-ball',
                                    'Other' => 'box'
                                ];
                                echo $iconMap[$cat['name']] ?? 'tag';
                            ?>"></i>
                        <?php endif; ?>
                    </div>
                    <div class="category-card-content">
                        <div class="category-card-name"><?php echo $cat['name']; ?></div>
                        <div class="category-card-desc"><?php echo $cat['description'] ?? 'Browse ' . $cat['name'] . ' listings'; ?></div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Popular States Section -->
    <!-- <section class="categories-section" style="background: #1a202c;">
        <div class="container">
            <h2>
                <i class="fas fa-map"></i>
                Popular States
            </h2>
            <div class="category-cards">
                <a href="state/Maharashtra" class="category-card">
                    <div class="category-card-image">
                        <i class="fas fa-landmark"></i>
                    </div>
                    <div class="category-card-content">
                        <div class="category-card-name">Maharashtra</div>
                        <div class="category-card-desc">Mumbai, Pune & more</div>
                    </div>
                </a>
                <a href="state/Delhi" class="category-card">
                    <div class="category-card-image">
                        <i class="fas fa-monument"></i>
                    </div>
                    <div class="category-card-content">
                        <div class="category-card-name">Delhi</div>
                        <div class="category-card-desc">New Delhi NCR</div>
                    </div>
                </a>
                <a href="state/Karnataka" class="category-card">
                    <div class="category-card-image">
                        <i class="fas fa-building"></i>
                    </div>
                    <div class="category-card-content">
                        <div class="category-card-name">Karnataka</div>
                        <div class="category-card-desc">Bengaluru & more</div>
                    </div>
                </a>
                <a href="state/Tamil+Nadu" class="category-card">
                    <div class="category-card-image">
                        <i class="fas fa-university"></i>
                    </div>
                    <div class="category-card-content">
                        <div class="category-card-name">Tamil Nadu</div>
                        <div class="category-card-desc">Chennai & more</div>
                    </div>
                </a>
                <a href="state/Uttar+Pradesh" class="category-card">
                    <div class="category-card-image">
                        <i class="fas fa-mosque"></i>
                    </div>
                    <div class="category-card-content">
                        <div class="category-card-name">Uttar Pradesh</div>
                        <div class="category-card-desc">Lucknow & more</div>
                    </div>
                </a>
            </div>
        </div>
    </section> -->

    <!-- Popular Cities Section -->
    <!-- <section class="categories-section">
        <div class="container">
            <h2>
                <i class="fas fa-city"></i>
                Popular Cities
            </h2>
            <div class="category-cards">
                <a href="city/Mumbai" class="category-card">
                    <div class="category-card-image">
                        <i class="fas fa-landmark"></i>
                    </div>
                    <div class="category-card-content">
                        <div class="category-card-name">Mumbai</div>
                        <div class="category-card-desc">Maharashtra</div>
                    </div>
                </a>
                <a href="city/Pune" class="category-card">
                    <div class="category-card-image">
                        <i class="fas fa-university"></i>
                    </div>
                    <div class="category-card-content">
                        <div class="category-card-name">Pune</div>
                        <div class="category-card-desc">Maharashtra</div>
                    </div>
                </a>
                <a href="city/Bengaluru" class="category-card">
                    <div class="category-card-image">
                        <i class="fas fa-building"></i>
                    </div>
                    <div class="category-card-content">
                        <div class="category-card-name">Bengaluru</div>
                        <div class="category-card-desc">Karnataka</div>
                    </div>
                </a>
                <a href="city/Hyderabad" class="category-card">
                    <div class="category-card-image">
                        <i class="fas fa-mosque"></i>
                    </div>
                    <div class="category-card-content">
                        <div class="category-card-name">Hyderabad</div>
                        <div class="category-card-desc">Telangana</div>
                    </div>
                </a>
                <a href="city/Chennai" class="category-card">
                    <div class="category-card-image">
                        <i class="fas fa-landmark"></i>
                    </div>
                    <div class="category-card-content">
                        <div class="category-card-name">Chennai</div>
                        <div class="category-card-desc">Tamil Nadu</div>
                    </div>
                </a>
            </div>
        </div>
    </section> -->

    <!-- Madhya Pradesh Cities Section -->
    <section class="categories-section" style="background: #1a202c;">
        <div class="container">
            <h2>
                <i class="fas fa-city"></i>
                Madhya Pradesh Cities
            </h2>
            <div class="category-cards">
                <a href="city/Bhopal" class="category-card">
                    <div class="category-card-image">
                        <i class="fas fa-university"></i>
                    </div>
                    <div class="category-card-content">
                        <div class="category-card-name">Bhopal</div>
                        <div class="category-card-desc">Capital of MP</div>
                    </div>
                </a>
                <a href="city/Indore" class="category-card">
                    <div class="category-card-image">
                        <i class="fas fa-building"></i>
                    </div>
                    <div class="category-card-content">
                        <div class="category-card-name">Indore</div>
                        <div class="category-card-desc">Commercial Capital</div>
                    </div>
                </a>
                <a href="city/Jabalpur" class="category-card">
                    <div class="category-card-image">
                        <i class="fas fa-mountain"></i>
                    </div>
                    <div class="category-card-content">
                        <div class="category-card-name">Jabalpur</div>
                        <div class="category-card-desc">Maharishi City</div>
                    </div>
                </a>
                <a href="city/Gwalior" class="category-card">
                    <div class="category-card-image">
                        <i class="fas fa-landmark"></i>
                    </div>
                    <div class="category-card-content">
                        <div class="category-card-name">Gwalior</div>
                        <div class="category-card-desc">Fort City</div>
                    </div>
                </a>
                <a href="city/Ujjain" class="category-card">
                    <div class="category-card-image">
                        <i class="fas fa-mosque"></i>
                    </div>
                    <div class="category-card-content">
                        <div class="category-card-name">Ujjain</div>
                        <div class="category-card-desc">Temple City</div>
                    </div>
                </a>
                <a href="city/Sagar" class="category-card">
                    <div class="category-card-image">
                        <i class="fas fa-mountain"></i>
                    </div>
                    <div class="category-card-content">
                        <div class="category-card-name">Sagar</div>
                        <div class="category-card-desc">Educational Hub</div>
                    </div>
                </a>
                <a href="city/Satna" class="category-card">
                    <div class="category-card-image">
                        <i class="fas fa-university"></i>
                    </div>
                    <div class="category-card-content">
                        <div class="category-card-name">Satna</div>
                        <div class="category-card-desc">Cement City</div>
                    </div>
                </a>
                <a href="city/Rewa" class="category-card">
                    <div class="category-card-image">
                        <i class="fas fa-landmark"></i>
                    </div>
                    <div class="category-card-content">
                        <div class="category-card-name">Rewa</div>
                        <div class="category-card-desc">Baghelkhand</div>
                    </div>
                </a>
                <a href="city/Datia" class="category-card">
                    <div class="category-card-image">
                        <i class="fas fa-mosque"></i>
                    </div>
                    <div class="category-card-content">
                        <div class="category-card-name">Datia</div>
                        <div class="category-card-desc">Peetambra City</div>
                    </div>
                </a>
                <a href="city/Damoh" class="category-card">
                    <div class="category-card-image">
                        <i class="fas fa-mountain"></i>
                    </div>
                    <div class="category-card-content">
                        <div class="category-card-name">Damoh</div>
                        <div class="category-card-desc">Rice City</div>
                    </div>
                </a>
                <a href="city/Guna" class="category-card">
                    <div class="category-card-image">
                        <i class="fas fa-university"></i>
                    </div>
                    <div class="category-card-content">
                        <div class="category-card-name">Guna</div>
                        <div class="category-card-desc">Education Hub</div>
                    </div>
                </a>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

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

        // Age verification logic
        function checkAgeVerification() {
            const ageVerified = localStorage.getItem('ageVerified');
            if (!ageVerified) {
                const modal = new bootstrap.Modal(document.getElementById('ageVerificationModal'), {
                    backdrop: 'static',
                    keyboard: false
                });
                modal.show();
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Check age verification on page load
            checkAgeVerification();

            // Handle age confirmation
            document.getElementById('confirmAge').addEventListener('click', function() {
                localStorage.setItem('ageVerified', 'true');
                const modal = bootstrap.Modal.getInstance(document.getElementById('ageVerificationModal'));
                modal.hide();
            });

            // Handle age denial
            document.getElementById('denyAge').addEventListener('click', function() {
                window.location.href = 'https://www.google.com'; // Redirect to safe site
            });

            const searchForm = document.getElementById('searchForm');
            if (searchForm) {
                searchForm.addEventListener('submit', function(e) {
                    const category = document.getElementById('category').value;
                    const state = document.getElementById('state').value;
                    const city = document.getElementById('city').value;

                    if (category) {
                        let url = 'search.php?category=' + encodeURIComponent(category);
                        if (state) url += '&state=' + encodeURIComponent(state);
                        if (city) url += '&city=' + encodeURIComponent(city);
                        window.location.href = url;
                        e.preventDefault();
                    } else if (state) {
                        window.location.href = 'search.php?state=' + encodeURIComponent(state);
                        e.preventDefault();
                    }
                });
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
