<?php
session_start();
require_once 'includes/functions.php';
require_once 'includes/Posting.php';

// Get recent postings
$postingModel = new Posting();
$recentPostings = $postingModel->getRecent(6);

echo "Debug Output:\n";
echo "Number of recent postings: " . count($recentPostings) . "\n";
echo "<br><br>Recent Postings:\n";
foreach ($recentPostings as $index => $posting) {
    echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0; background: #f9f9f9;'>";
    echo "<h3>Posting " . ($index + 1) . ": " . $posting['title'] . "</h3>";
    echo "<p><strong>Category:</strong> " . $posting['category'] . "</p>";
    echo "<p><strong>Location:</strong> " . $posting['location'] . "</p>";
    echo "<p><strong>Price:</strong> " . $posting['price'] . "</p>";
    echo "<p><strong>Images:</strong> " . $posting['images'] . "</p>";
    echo "<p><strong>Description:</strong> " . substr($posting['description'], 0, 100) . "...</p>";
    echo "</div>";
}

?>