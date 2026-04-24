<?php
require_once '../includes/functions.php';
require_once '../includes/User.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

// Check if user ID and status are provided
if (!isset($_GET['id']) || empty($_GET['id']) || !isset($_GET['status']) || empty($_GET['status'])) {
    redirect('users.php');
}

$userModel = new User();

// Get user details
$user = $userModel->findById($_GET['id']);

if (!$user) {
    redirect('404.php');
}

// Validate status
$status = $_GET['status'] === 'active' ? 'active' : 'inactive';

// Update user status
if ($userModel->update($_GET['id'], [
    'status' => $status
])) {
    // Set success message
    session_start();
    $_SESSION['success'] = 'User ' . ($status === 'active' ? 'activated' : 'deactivated') . ' successfully!';
} else {
    // Set error message
    session_start();
    $_SESSION['error'] = 'Failed to update user status. Please try again.';
}

// Redirect to users page
redirect('users.php');
?>