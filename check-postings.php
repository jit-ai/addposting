<?php
session_start();
require_once 'includes/functions.php';
require_once 'includes/Posting.php';

echo "<h2>Checking Postings:</h2>";

$postingModel = new Posting();

// Get all active postings
$allPostings = $postingModel->getAll();
echo "<h3>All Active Postings (" . count($allPostings) . "):</h3>";
foreach ($allPostings as $posting) {
    echo "<p><strong>" . $posting['title'] . "</strong> - " . $posting['category'] . "</p>";
}

// Check if we have at least one posting to get similar postings
if (!empty($allPostings)) {
    $firstPosting = $allPostings[0];
    echo "<h3>Similar Postings for '" . $firstPosting['title'] . "' (" . $firstPosting['category'] . "):</h3>";
    
    $similarPostings = $postingModel->getSimilar($firstPosting['category'], $firstPosting['id']);
    
    if (!empty($similarPostings)) {
        foreach ($similarPostings as $posting) {
            echo "<p><strong>" . $posting['title'] . "</strong> - " . $posting['category'] . "</p>";
        }
    } else {
        echo "<p>No similar postings found in the same category.</p>";
    }
} else {
    echo "<p>No active postings found in the database.</p>";
}
?>