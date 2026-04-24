<?php
session_start();
require_once 'includes/functions.php';
require_once 'includes/Posting.php';

// Get recent postings
$postingModel = new Posting();
$recentPostings = $postingModel->getRecent(6);

// Get similar postings - for the first posting, find similar in same category
$similarPostings = [];
if (!empty($recentPostings)) {
    $firstPosting = $recentPostings[0];
    echo "<h3>First Posting:</h3>";
    echo "<pre>" . print_r($firstPosting, true) . "</pre><br>";
    
    $similarPostings = $postingModel->getSimilar($firstPosting['category'], $firstPosting['id'], 4);
    echo "<h3>Similar Postings in '" . $firstPosting['category'] . "' category:</h3>";
    echo "<pre>" . print_r($similarPostings, true) . "</pre>";
} else {
    echo "No recent postings found";
}
?>