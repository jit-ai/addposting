<?php
session_start();
require_once 'includes/functions.php';
require_once 'includes/Posting.php';

$postingModel = new Posting();

// Cities by state data
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

$pageTitle = "Browse by Location - " . APP_NAME;
$pageDescription = "Find listings by state and city across India. Browse verified postings in your preferred location.";

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="/addposting/">
    <title><?php echo $pageTitle; ?></title>
    <meta name="description" content="<?php echo $pageDescription; ?>">
    <meta name="keywords" content="states, cities, locations, India, listings, browse by location">
    <meta property="og:title" content="<?php echo $pageTitle; ?>">
    <meta property="og:description" content="<?php echo $pageDescription; ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo 'https://' . $_SERVER['HTTP_HOST'] . '/addposting/statecitylist.php'; ?>">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo $pageTitle; ?>">
    <meta name="twitter:description" content="<?php echo $pageDescription; ?>">
    <link rel="canonical" href="<?php echo 'https://' . $_SERVER['HTTP_HOST'] . '/addposting/statecitylist.php'; ?>">

    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time() + 1000; ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .states-section {
            padding: 3rem 0;
            background: #0f172a;
        }
        .states-section .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 1rem;
        }
        .states-section h1 {
            color: #f1f5f9;
            font-size: 2.5rem;
            margin-bottom: 1rem;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
        }
        .states-section h1 i {
            color: #fbbf24;
        }
        .states-section .subtitle {
            color: #94a3b8;
            text-align: center;
            font-size: 1.1rem;
            margin-bottom: 3rem;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .state-group {
            margin-bottom: 4rem;
        }
        .state-name {
            color: #fbbf24;
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .state-name i {
            color: #64748b;
        }
        .cities-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1rem;
        }
        .city-card {
            background: #1e293b;
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s ease;
            cursor: pointer;
            border: 1px solid #334155;
            text-decoration: none;
            color: inherit;
        }
        .city-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0,0,0,0.3);
            border-color: #667eea;
        }
        .city-card-content {
            padding: 1.5rem;
        }
        .city-name {
            font-size: 1.25rem;
            font-weight: 600;
            color: #f1f5f9;
            margin-bottom: 0.5rem;
        }
        .city-meta {
            color: #94a3b8;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .city-meta i {
            color: #667eea;
        }

        .state-stats {
            background: #334155;
            padding: 1rem 1.5rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .stat-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #cbd5e1;
            font-size: 0.9rem;
        }
        .stat-item i {
            color: #fbbf24;
        }

        @media (max-width: 768px) {
            .states-section h1 {
                font-size: 2rem;
            }
            .cities-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }
            .city-card-content {
                padding: 1rem;
            }
            .city-name {
                font-size: 1.1rem;
            }
        }

        @media (max-width: 480px) {
            .cities-grid {
                grid-template-columns: 1fr;
            }
            .state-name {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <section class="states-section">
        <div class="container">
            <h1>
                <i class="fas fa-map-marked-alt"></i>
                Browse by Location
            </h1>
            <p class="subtitle">Discover listings across all states and cities in India. Find what you're looking for in your preferred location.</p>

            <?php foreach ($citiesByState as $stateName => $cities): ?>
            <div class="state-group">
                <div class="state-name">
                    <i class="fas fa-map-marker-alt"></i>
                    <?php echo $stateName; ?>
                </div>

                <div class="state-stats">
                    <div class="stat-item">
                        <i class="fas fa-city"></i>
                        <?php echo count($cities); ?> Cities
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-list"></i>
                        Browse Listings
                    </div>
                </div>

                <div class="cities-grid">
                    <?php foreach ($cities as $cityName): ?>
                    <a href="city/<?php echo urlencode($cityName); ?>" class="city-card">
                        <div class="city-card-content">
                            <div class="city-name"><?php echo $cityName; ?></div>
                            <div class="city-meta">
                                <span><i class="fas fa-building"></i> <?php echo $stateName; ?></span>
                                <span><i class="fas fa-arrow-right"></i> View Listings</span>
                            </div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
    <script src="assets/js/main.js?v=<?php echo time(); ?>"></script>
</body>
</html></content>
<parameter name="filePath">C:\xampp\htdocs\addposting\statecitylist.php