<?php
session_start();
require_once 'includes/functions.php';
require_once 'includes/Posting.php';
require_once 'includes/database.php';

// Get database connection
$db = new Database();

// Get state from URL parameter
$state = isset($_GET['state']) ? sanitize($_GET['state']) : null;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10;

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
    'Andhra Pradesh' => ['West Godavari', 'Vizianagaram', 'Visakhapatnam', 'Srikakulam', 'Sri Satya Sai', 'Sri Balaji', 'Prakasam', 'Palnadu', 'Nellore', 'Nandyal', 'N T Rama Rao', 'Manyam', 'Kurnool', 'Krishna', 'Konaseema', 'Kakinada', 'Kadapa', 'Guntur', 'Eluru', 'Bapatla', 'Annamaya', 'Anakapalli', 'Alluri Sitarama Raju', 'East Godavari', 'Chittoor', 'Anantapur'],
    'Arunachal Pradesh' => ['Tawang', 'West Kameng', 'Bichom', 'East Kameng', 'Pakke-Kessang', 'Kurung Kumey', 'Papum Pare', 'Itanagar', 'Kra Daadi', 'Lower Subansiri', 'Kamle', 'Keyi Panyor', 'Upper Subansiri', 'Shi-Yomi', 'West Siang', 'Siang', 'Lower Siang', 'Lepa-Rada', 'Upper Siang', 'East Siang', 'Dibang Valley', 'Lower Dibang Valley', 'Lohit', 'Anjaw', 'Namsai', 'Changlang', 'Tirap', 'Longding'],
    'Assam' => ['Baksa', 'Barpeta', 'Biswanath', 'Bongaigaon', 'Cachar', 'Charaideo', 'Chirang', 'Darrang', 'Dhemaji', 'Dhubri', 'Dibrugarh', 'Goalpara', 'Golaghat', 'Hailakandi', 'Hojai', 'Jorhat', 'Kamrup', 'Kamrup Metropolitan', 'Karbi Anglong', 'Karimganj', 'Kokrajhar', 'Lakhimpur', 'Majuli', 'Morigaon', 'Nagaon', 'Nalbari', 'Sivasagar', 'Sonitpur', 'South Salmara-Mankachar', 'Tamulpur', 'Tinsukia', 'Udalguri', 'West Karbi Anglong'],
    'Bihar' => ['Araria', 'Arwal', 'Aurangabad', 'Banka', 'Begusarai', 'Bhagalpur', 'Bhojpur', 'Buxar', 'Darbhanga', 'East Champaran', 'Gaya', 'Gopalganj', 'Jamui', 'Jehanabad', 'Kaimur', 'Katihar', 'Khagaria', 'Kishanganj', 'Lakhisarai', 'Madhepura', 'Madhubani', 'Munger', 'Muzaffarpur', 'Nalanda', 'Nawada', 'Patna', 'Purnia', 'Rohtas', 'Saharsa', 'Samastipur', 'Saran', 'Sheikhpura', 'Sheohar', 'Sitamarhi', 'Siwan', 'Supaul', 'Vaishali', 'West Champaran'],
    'Chhattisgarh' => ['Balod', 'Baloda Bazar', 'Balrampur-Ramanujganj', 'Bastar', 'Bemetara', 'Bijapur', 'Bilaspur', 'Dantewada', 'Dhamtari', 'Durg', 'Gariaband', 'Gaurela-Pendra-Marwahi', 'Janjgir-Champa', 'Jashpur', 'Kabirdham', 'Kanker', 'Khairagarh-Chhuikhadan-Gandai', 'Kondagaon', 'Korba', 'Korea', 'Mahasamund', 'Manendragarh-Chirmiri-Bharatpur', 'Mohla-Manpur-Ambagarh Chowki', 'Mungeli', 'Narayanpur', 'Raigarh', 'Raipur', 'Rajnandgaon', 'Sarangarh-Bilaigarh', 'Shakti', 'Sukma', 'Surajpur', 'Surguja'],
    'Goa' => ['North Goa', 'South Goa'],
    'Gujarat' => ['Ahmedabad', 'Amreli', 'Anand', 'Aravalli', 'Banaskantha', 'Bharuch', 'Bhavnagar', 'Botad', 'Chhota Udaipur', 'Dahod', 'Dang', 'Devbhumi Dwarka', 'Gandhinagar', 'Gir Somnath', 'Jamnagar', 'Junagadh', 'Kheda', 'Kutch', 'Mahisagar', 'Mehsana', 'Morbi', 'Narmada', 'Navsari', 'Panchmahal', 'Patan', 'Porbandar', 'Rajkot', 'Sabarkantha', 'Surat', 'Surendranagar', 'Tapi', 'Vadodara', 'Valsad'],
    'Haryana' => ['Ambala', 'Bhiwani', 'Charkhi Dadri', 'Faridabad', 'Fatehabad', 'Gurugram', 'Hisar', 'Jhajjar', 'Jind', 'Kaithal', 'Karnal', 'Kurukshetra', 'Mahendragarh', 'Nuh', 'Palwal', 'Panchkula', 'Panipat', 'Rewari', 'Rohtak', 'Sirsa', 'Sonipat', 'Yamunanagar'],
    'Himachal Pradesh' => ['Bilaspur', 'Chamba', 'Hamirpur', 'Kangra', 'Kinnaur', 'Kullu', 'Lahaul and Spiti', 'Mandi', 'Shimla', 'Sirmaur', 'Solan', 'Una'],
    'Jharkhand' => ['Bokaro', 'Chatra', 'Deoghar', 'Dhanbad', 'Dumka', 'East Singhbhum', 'Garhwa', 'Giridih', 'Godda', 'Gumla', 'Hazaribag', 'Jamtara', 'Khunti', 'Koderma', 'Latehar', 'Lohardaga', 'Pakur', 'Palamu', 'Ramgarh', 'Ranchi', 'Sahibganj', 'Saraikela Kharsawan', 'Simdega', 'West Singhbhum'],
    'Karnataka' => ['Bagalakote', 'Ballari', 'Belagavi', 'Bengaluru Rural', 'Bengaluru Urban', 'Bidar', 'Chamarajanagara', 'Chikkaballapura', 'Chikkamagaluru', 'Chitradurga', 'Dakshina Kannada', 'Davanagere', 'Dharwad', 'Gadag', 'Kalaburagi', 'Hassan', 'Haveri', 'Kodagu', 'Kolar', 'Koppala', 'Mandya', 'Mysuru', 'Raichur', 'Ramanagara', 'Shivamogga', 'Tumakuru', 'Udupi', 'Uttara Kannada', 'Vijayanagara', 'Vijayapura', 'Yadgir'],
    'Kerala' => ['Alappuzha', 'Ernakulam', 'Idukki', 'Kannur', 'Kasaragod', 'Kollam', 'Kottayam', 'Kozhikode', 'Malappuram', 'Palakkad', 'Pathanamthitta', 'Thiruvananthapuram', 'Thrissur', 'Wayanad'],
    'Madhya Pradesh' => ['Agar Malwa', 'Alirajpur', 'Anuppur', 'Ashoknagar', 'Balaghat', 'Barwani', 'Betul', 'Bhind', 'Bhopal', 'Burhanpur', 'Chhatarpur', 'Chhindwara', 'Damoh', 'Datia', 'Dewas', 'Dhar', 'Dindori', 'Guna', 'Gwalior', 'Harda', 'Hoshangabad', 'Indore', 'Jabalpur', 'Jhabua', 'Katni', 'Khandwa', 'Khargone', 'Mandla', 'Mandsaur', 'Morena', 'Narsinghpur', 'Neemuch', 'Panna', 'Raisen', 'Rajgarh', 'Ratlam', 'Rewa', 'Sagar', 'Satna', 'Sehore', 'Seoni', 'Shahdol', 'Shajapur', 'Sheopur', 'Shivpuri', 'Sidhi', 'Singrauli', 'Tikamgarh', 'Ujjain', 'Umaria', 'Vidisha'],
    'Maharashtra' => ['Ahmednagar', 'Akola', 'Amravati', 'Aurangabad', 'Beed', 'Bhandara', 'Buldhana', 'Chandrapur', 'Dhule', 'Gadchiroli', 'Gondia', 'Hingoli', 'Jalgaon', 'Jalna', 'Kolhapur', 'Latur', 'Mumbai City', 'Mumbai Suburban', 'Nagpur', 'Nanded', 'Nandurbar', 'Nashik', 'Osmanabad', 'Palghar', 'Parbhani', 'Pune', 'Raigad', 'Ratnagiri', 'Sangli', 'Satara', 'Sindhudurg', 'Solapur', 'Thane', 'Wardha', 'Washim', 'Yavatmal'],
    'Manipur' => ['Bishnupur', 'Chandel', 'Churachandpur', 'Imphal East', 'Imphal West', 'Jiribam', 'Kakching', 'Kamjong', 'Kangpokpi', 'Noney', 'Pherzawl', 'Senapati', 'Tamenglong', 'Tengnoupal', 'Thoubal', 'Ukhrul'],
    'Meghalaya' => ['East Garo Hills', 'East Jaintia Hills', 'East Khasi Hills', 'North Garo Hills', 'Ribhoi', 'South Garo Hills', 'South West Garo Hills', 'South West Khasi Hills', 'West Garo Hills', 'West Jaintia Hills', 'West Khasi Hills'],
    'Mizoram' => ['Aizawl East', 'Aizawl West', 'Champhai', 'Mamit', 'Kolasib', 'Lawngtlai', 'Lunglei', 'Saiha', 'Serchhip'],
    'Nagaland' => ['Dimapur', 'Kiphire', 'Kohima', 'Longleng', 'Mokokchung', 'Mon', 'Peren', 'Phek', 'Tuensang', 'Wokha', 'Zunheboto'],
    'Odisha' => ['Angul', 'Balangir', 'Balasore', 'Bargarh', 'Bhadrak', 'Boudh', 'Cuttack', 'Deogarh', 'Dhenkanal', 'Gajapati', 'Ganjam', 'Jagatsinghpur', 'Jajpur', 'Jharsuguda', 'Kalahandi', 'Kandhamal', 'Kendrapara', 'Kendujhar', 'Khordha', 'Koraput', 'Malkangiri', 'Mayurbhanj', 'Nabarangpur', 'Nayagarh', 'Nuapada', 'Puri', 'Rayagada', 'Sambalpur', 'Sonepur', 'Sundargarh'],
    'Punjab' => ['Amritsar', 'Barnala', 'Bathinda', 'Faridkot', 'Fatehgarh Sahib', 'Fazilka', 'Firozpur', 'Gurdaspur', 'Hoshiarpur', 'Jalandhar', 'Kapurthala', 'Ludhiana', 'Mansa', 'Moga', 'Muktsar', 'Pathankot', 'Patiala', 'Rupnagar', 'Sahibzada Ajit Singh Nagar', 'Sangrur', 'Shahid Bhagat Singh Nagar', 'Sri Muktsar Sahib', 'Tarn Taran'],
    'Rajasthan' => ['Ajmer', 'Alwar', 'Banswara', 'Baran', 'Barmer', 'Bharatpur', 'Bhilwara', 'Bikaner', 'Bundi', 'Chittorgarh', 'Churu', 'Dausa', 'Dholpur', 'Dungarpur', 'Hanumangarh', 'Jaipur', 'Jaisalmer', 'Jalore', 'Jhalawar', 'Jhunjhunu', 'Jodhpur', 'Karauli', 'Kota', 'Nagaur', 'Pali', 'Pratapgarh', 'Rajsamand', 'Sawai Madhopur', 'Sikar', 'Sirohi', 'Sri Ganganagar', 'Tonk', 'Udaipur'],
    'Sikkim' => ['East Sikkim', 'North Sikkim', 'South Sikkim', 'West Sikkim'],
    'Tamil Nadu' => ['Ariyalur', 'Chennai', 'Coimbatore', 'Cuddalore', 'Dharmapuri', 'Dindigul', 'Erode', 'Kallakurichi', 'Kancheepuram', 'Kanyakumari', 'Karur', 'Krishnagiri', 'Madurai', 'Nagapattinam', 'Namakkal', 'Nilgiris', 'Perambalur', 'Pudukkottai', 'Ramanathapuram', 'Ranipet', 'Salem', 'Sivaganga', 'Tenkasi', 'Thanjavur', 'Theni', 'Thiruvallur', 'Thiruvannamalai', 'Thiruvarur', 'Tiruchirappalli', 'Tirunelveli', 'Tirupattur', 'Tiruppur', 'Tiruvannamalai', 'Vellore', 'Viluppuram', 'Virudhunagar'],
    'Telangana' => ['Adilabad', 'Bhadradri Kothagudem', 'Hyderabad', 'Jagtial', 'Jangaon', 'Jayashankar Bhupalapally', 'Jogulamba Gadwal', 'Kamareddy', 'Karimnagar', 'Khammam', 'Kumuram Bheem Asifabad', 'Mahabubabad', 'Mahabubnagar', 'Mancherial', 'Medak', 'Medchal-Malkajgiri', 'Mulugu', 'Nagarkurnool', 'Nalgonda', 'Narayanpet', 'Nirmal', 'Nizamabad', 'Peddapalli', 'Rajanna Sircilla', 'Rangareddy', 'Sangareddy', 'Siddipet', 'Suryapet', 'Vikarabad', 'Wanaparthy', 'Warangal Rural', 'Warangal Urban', 'Yadadri Bhuvanagiri'],
    'Tripura' => ['Dhalai', 'Gomati', 'Khowai', 'North Tripura', 'Sepahijala', 'South Tripura', 'Unakoti', 'West Tripura'],
    'Uttar Pradesh' => ['Agra', 'Aligarh', 'Allahabad', 'Ambedkar Nagar', 'Amethi', 'Amroha', 'Auraiya', 'Azamgarh', 'Baghpat', 'Bahraich', 'Ballia', 'Balrampur', 'Banda', 'Barabanki', 'Bareilly', 'Basti', 'Bhadohi', 'Bijnor', 'Budaun', 'Bulandshahr', 'Chandauli', 'Chitrakoot', 'Deoria', 'Etah', 'Etawah', 'Faizabad', 'Farrukhabad', 'Fatehpur', 'Firozabad', 'Gautam Buddh Nagar', 'Ghaziabad', 'Ghazipur', 'Gonda', 'Gorakhpur', 'Hamirpur', 'Hapur', 'Hardoi', 'Hathras', 'Jalaun', 'Jaunpur', 'Jhansi', 'Kannauj', 'Kanpur Dehat', 'Kanpur Nagar', 'Kanshiram Nagar', 'Kaushambi', 'Kushinagar', 'Lakhimpur Kheri', 'Lalitpur', 'Lucknow', 'Maharajganj', 'Mahoba', 'Mainpuri', 'Mathura', 'Mau', 'Meerut', 'Mirzapur', 'Moradabad', 'Muzaffarnagar', 'Pilibhit', 'Pratapgarh', 'Rae Bareli', 'Rampur', 'Saharanpur', 'Sambhal', 'Sant Kabir Nagar', 'Shahjahanpur', 'Shamli', 'Shravasti', 'Siddharthnagar', 'Sitapur', 'Sonbhadra', 'Sultanpur', 'Unnao', 'Varanasi'],
    'Uttarakhand' => ['Almora', 'Bageshwar', 'Chamoli', 'Champawat', 'Dehradun', 'Haridwar', 'Nainital', 'Pauri Garhwal', 'Pithoragarh', 'Rudraprayag', 'Tehri Garhwal', 'Udham Singh Nagar', 'Uttarkashi'],
    'West Bengal' => ['Alipurduar', 'Bankura', 'Birbhum', 'Cooch Behar', 'Dakshin Dinajpur', 'Darjeeling', 'Hooghly', 'Howrah', 'Jalpaiguri', 'Jhargram', 'Kalimpong', 'Kolkata', 'Malda', 'Murshidabad', 'Nadia', 'North 24 Parganas', 'Paschim Bardhaman', 'Paschim Medinipur', 'Purba Bardhaman', 'Purba Medinipur', 'Purulia', 'South 24 Parganas', 'Uttar Dinajpur'],
    'Delhi' => ['Central Delhi', 'East Delhi', 'New Delhi', 'North Delhi', 'North East Delhi', 'North West Delhi', 'Shahdara', 'South Delhi', 'South East Delhi', 'South West Delhi', 'West Delhi'],
    'default' => []
];
$citiesInState = $citiesByState[$state] ?? $citiesByState['default'];

// Get additional filters
$category = isset($_GET['category']) ? sanitize($_GET['category']) : null;
$city = isset($_GET['city']) ? sanitize($_GET['city']) : null;

// Get filtered postings
$filteredPostings = [];
$totalPostings = 0;
$postingModel = new Posting();

$offset = ($page - 1) * $perPage;
$filteredPostings = $postingModel->getAll($category, null, $state, $city, $perPage, $offset);
$totalPostings = $postingModel->getTotalCount($category, null, $state, $city);

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
    <title><?php echo $pageTitle; ?> Male Escorts & Call Boy, Play Boy Job And Gay Escort Adult Meeting</title>
    <meta name="description" content="<?php echo $pageDescription; ?>Find on MALE ESCORTS and gay escorts category +1200 call boys ads Play Boy available. Amateur and professional ads on Admypost, find yours now and enjoy!">
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
                            <a href="posting/<?php echo strtolower(str_replace(' ', '-', preg_replace('/[^a-zA-Z0-9 ]/', '', $posting['title']))) . '-' . $posting['id']; ?>">
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
                               <a href="posting/<?php echo strtolower(str_replace(' ', '-', preg_replace('/[^a-zA-Z0-9 ]/', '', $posting['title']))) . '-' . $posting['id']; ?>">  <h3 class="posting-title">
                                        <?php echo htmlspecialchars(mb_strimwidth($posting['title'], 0, 80, '...')); ?>
                                    </h3></a>
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
                                <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $posting['contact']); ?>?text=<?php echo urlencode('Hi, I saw your ad on Admypost'); ?>" 
                                   class="whatsapp-btn" target="_blank">
                                   <i class="fab fa-whatsapp"></i>
                                </a>
                                <a href="tel:<?php echo preg_replace('/[^0-9]/', '', $posting['contact']); ?>" class="call-btn">
                                    <i class="fas fa-phone"></i>
                                </a>
                                <a href="https://t.me/+<?php echo preg_replace('/[^0-9]/', '', $posting['contact']); ?>" class="telegram-btn" target="_blank">
                                    <i class="fab fa-telegram"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    </div>

                    <?php if ($totalPostings > $perPage): ?>
                        <?php
                        $queryParams = ['state' => $state];
                        if ($category) $queryParams['category'] = $category;
                        echo generatePagination($totalPostings, $perPage, $page, 'state.php', $queryParams);
                        ?>
                    <?php endif; ?>
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
