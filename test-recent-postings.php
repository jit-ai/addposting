<?php
session_start();
require_once 'includes/functions.php';
require_once 'includes/Posting.php';

// Connect to database
require_once 'includes/database.php';
$db = new Database();
$conn = $db->getConnection();

// Test the database connection
echo "Database connection established<br>";

// Get recent postings
$postingModel = new Posting();
$recentPostings = $postingModel->getRecent(3);

echo "Number of recent postings: " . count($recentPostings) . "<br><br>";

if (!empty($recentPostings)) {
    foreach ($recentPostings as $index => $posting) {
        echo "<h3>Posting " . ($index + 1) . ": " . $posting['title'] . "</h3>";
        echo "<pre>" . print_r($posting, true) . "</pre>";
        echo "<br>";
    }
} else {
    echo "No recent postings found. Let's check the database directly:<br>";
    
    // Check database directly
    $result = $conn->query("SELECT * FROM postings WHERE status = 'active' ORDER BY created_at DESC LIMIT 3");
    if ($result->num_rows > 0) {
        echo "Direct database query found " . $result->num_rows . " postings:<br>";
        while ($row = $result->fetch_assoc()) {
            echo "<pre>" . print_r($row, true) . "</pre>";
            echo "<br>";
        }
    } else {
        echo "No postings in database. Let's check users:<br>";
        $result = $conn->query("SELECT * FROM users");
        echo "Users in database: " . $result->num_rows . "<br>";
        while ($row = $result->fetch_assoc()) {
            echo "<pre>" . print_r($row, true) . "</pre>";
            echo "<br>";
        }
    }
}
?>