<?php
require_once '../includes/functions.php';
require_once '../includes/User.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

// Check if user ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    redirect('users.php');
}

$userModel = new User();

// Get user details
$user = $userModel->findById($_GET['id']);

if (!$user) {
    redirect('404.php');
}

// Delete user and associated data
if ($userModel->delete($_GET['id'])) {
    // In a real application, you would also delete all postings associated with this user
    // and any other related data
    
    // Set success message
    session_start();
    $_SESSION['success'] = 'User deleted successfully!';
} else {
    // Set error message
    session_start();
    $_SESSION['error'] = 'Failed to delete user. Please try again.';
}

// Redirect to users page
redirect('users.php');
?>