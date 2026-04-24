<?php
// General Functions
// Determine the correct path to config/database.php based on the current file's location
$currentDir = dirname(__FILE__);
$configFile = $currentDir . '/../config/database.php';

// Check if the file exists at the determined path
if (file_exists($configFile)) {
    require_once $configFile;
} else {
    // Try to find config file in the same directory (for root level files)
    $configFile = $currentDir . '/config/database.php';
    if (file_exists($configFile)) {
        require_once $configFile;
    } else {
        die('Config file not found');
    }
}

// Sanitize input
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Hash password
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Verify password
function verifyPassword($password, $hashedPassword) {
    return password_verify($password, $hashedPassword);
}

// Generate random token
function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

// Redirect
function redirect($url) {
    header("Location: " . $url);
    exit();
}

// Check if user is logged in
function isLoggedIn() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return isset($_SESSION['user_id']);
}

// Check if user is admin
function isAdmin() {
    if (!isLoggedIn()) {
        return false;
    }
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Get user data
function getUserById($userId) {
    $db = new Database();
    $conn = $db->getConnection();
    
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}

// Upload file
function uploadFile($file, $uploadPath) {
    $targetDir = UPLOAD_PATH . $uploadPath;
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }
    
    $fileName = time() . '_' . basename($file['name']);
    $targetFile = $targetDir . $fileName;
    $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
    
    // Check file size
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['error' => 'File is too large. Maximum size is 5MB.'];
    }
    
    // Allow certain file formats
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
    if (!in_array($fileType, $allowedTypes)) {
        return ['error' => 'Only JPG, JPEG, PNG, GIF, and PDF files are allowed.'];
    }
    
    if (move_uploaded_file($file['tmp_name'], $targetFile)) {
        return ['success' => $fileName];
    } else {
        return ['error' => 'Failed to upload file.'];
    }
}

// Send email
function sendEmail($to, $subject, $message) {
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: " . APP_NAME . " <no-reply@" . $_SERVER['HTTP_HOST'] . ">" . "\r\n";
    
    return mail($to, $subject, $message, $headers);
}
?>