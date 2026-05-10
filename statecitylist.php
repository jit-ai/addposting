<?php
session_start();
require_once 'includes/functions.php';
require_once 'includes/Posting.php';

$postingModel = new Posting();

// Cities by state data
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
    'Delhi' => ['Central Delhi', 'East Delhi', 'New Delhi', 'North Delhi', 'North East Delhi', 'North West Delhi', 'Shahdara', 'South Delhi', 'South East Delhi', 'South West Delhi', 'West Delhi']
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