<?php
require_once '../includes/functions.php';
require_once '../includes/Posting.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

// Check if posting ID and status are provided
if (!isset($_GET['id']) || empty($_GET['id']) || !isset($_GET['status']) || empty($_GET['status'])) {
    redirect('postings.php');
}

$postingModel = new Posting();

// Get posting details
$posting = $postingModel->findById($_GET['id']);

if (!$posting) {
    redirect('404.php');
}

// Validate status
$status = $_GET['status'] === 'active' ? 'active' : 'inactive';

// Update posting status
if ($postingModel->update($_GET['id'], [
    'status' => $status
])) {
    // Set success message
    session_start();
    $_SESSION['success'] = 'Posting ' . ($status === 'active' ? 'activated' : 'deactivated') . ' successfully!';
} else {
    // Set error message
    session_start();
    $_SESSION['error'] = 'Failed to update posting status. Please try again.';
}

// Redirect to postings page
redirect('postings.php');
?>