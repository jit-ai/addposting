<?php
require_once '../includes/functions.php';
require_once '../includes/database.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

// Check if category ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    redirect('categories.php');
}

$db = new Database();
$conn = $db->getConnection();

// Get category details
$category = $conn->query("SELECT * FROM categories WHERE id = " . $_GET['id'])->fetch_assoc();

if (!$category) {
    redirect('404.php');
}

// Check if category is used in any postings
$usedInPostings = $conn->query("SELECT COUNT(*) as count FROM postings WHERE category = '" . $category['name'] . "'")->fetch_assoc()['count'];

if ($usedInPostings > 0) {
    // Set error message
    session_start();
    $_SESSION['error'] = 'Cannot delete category. It is used in ' . $usedInPostings . ' posting(s).';
    redirect('categories.php');
}

// Delete category
if ($conn->query("DELETE FROM categories WHERE id = " . $_GET['id'])) {
    // Set success message
    session_start();
    $_SESSION['success'] = 'Category deleted successfully!';
} else {
    // Set error message
    session_start();
    $_SESSION['error'] = 'Failed to delete category. Please try again.';
}

// Redirect to categories page
redirect('categories.php');
?>