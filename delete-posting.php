<?php
require_once 'includes/functions.php';
require_once 'includes/Posting.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

// Check if posting ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    redirect('my-postings.php');
}

$postingModel = new Posting();

// Get posting details
$posting = $postingModel->findById($_GET['id']);

if (!$posting || $posting['user_id'] != $_SESSION['user_id']) {
    redirect('404.php');
}

// Delete posting and associated images
if ($postingModel->delete($_GET['id'])) {
    // Delete images from server
    if (!empty($posting['images'])) {
        foreach (explode(',', $posting['images']) as $image) {
            $imagePath = 'uploads/postings/' . $image;
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
    }
    
    // Set success message
    session_start();
    $_SESSION['success'] = 'Posting deleted successfully!';
} else {
    // Set error message
    session_start();
    $_SESSION['error'] = 'Failed to delete posting. Please try again.';
}

// Redirect to my postings page
redirect('my-postings.php');
?>