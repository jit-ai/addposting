<?php
session_start();
require_once 'includes/functions.php';
require_once 'includes/Posting.php';
require_once 'includes/User.php';

// Connect to database
require_once 'includes/database.php';
$db = new Database();
$conn = $db->getConnection();

// Check if test user exists
$testUserEmail = 'test@example.com';
$userModel = new User();
$testUser = $userModel->findByEmail($testUserEmail);

if (!$testUser) {
    $testUser = [
        'name' => 'Test User',
        'email' => $testUserEmail,
        'password' => 'password123',
        'role' => 'user'
    ];
    
    $userId = $userModel->create($testUser);
    echo "Test user created with ID: " . $userId . "<br>";
} else {
    $userId = $testUser['id'];
    echo "Test user already exists (ID: " . $userId . ")<br>";
}

// Create test postings
$postingModel = new Posting();

$testPostings = [
    [
        'title' => 'Exclusive Premium Services in London',
        'description' => 'Experience high-end professional services tailored to your needs. Available 24/7 in Central London. Multilingual and discreet service guaranteed.',
        'category' => 'Services',
        'price' => 150.00,
        'location' => 'London, UK',
        'contact' => 'contact@londonpremium.com',
        'images' => '1773588513_banner-oakwood.png'
    ],
    [
        'title' => 'Professional Wellness & Relaxation',
        'description' => 'Therapeutic wellness sessions in a private, luxurious setting. Focus on stress relief and holistic rejuvenation. Certified practitioners only.',
        'category' => 'Services',
        'price' => 120.00,
        'location' => 'Berlin, DE',
        'contact' => 'wellness@berlinspa.com',
        'images' => '1773590853_gjjhhjhkg.png'
    ],
    [
        'title' => 'Elite Event Hosting & Entertainment',
        'description' => 'Premium hosting services for corporate events and private parties. Experience in high-profile social management and hospitality.',
        'category' => 'Services',
        'price' => 200.00,
        'location' => 'Paris, FR',
        'contact' => 'events@pariselite.com',
        'images' => ''
    ]
];

foreach ($testPostings as $postingData) {
    $data = [
        'user_id' => $userId,
        'title' => $postingData['title'],
        'description' => $postingData['description'],
        'category' => $postingData['category'],
        'price' => $postingData['price'],
        'location' => $postingData['location'],
        'contact' => $postingData['contact'],
        'images' => $postingData['images']
    ];
    
    $postingId = $postingModel->create($data);
    if ($postingId) {
        echo "Test posting created: " . $postingData['title'] . " (ID: " . $postingId . ")<br>";
    } else {
        echo "Failed to create posting: " . $postingData['title'] . "<br>";
    }
}

echo "<br>Test data added successfully!<br>";
echo "<a href='index.php'>Go to home page</a>";
?>