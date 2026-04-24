<?php
session_start();
define('SECURE_ACCESS', true);
require_once 'config/database.php';
require_once 'includes/functions.php';

$page_title = 'Terms and Conditions';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Add Posting</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .legal-container {
            max-width: 800px;
            margin: 100px auto 2rem;
            padding: 2rem;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .legal-container h1 {
            color: #2d3748;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #e2e8f0;
        }
        .legal-container h2 {
            color: #4a5568;
            margin: 1.5rem 0 0.75rem;
        }
        .legal-container p, .legal-container li {
            color: #4a5568;
            line-height: 1.7;
        }
        .legal-container ul {
            padding-left: 1.5rem;
        }
        .legal-container li {
            margin-bottom: 0.5rem;
        }
        .back-link {
            display: inline-block;
            margin-bottom: 1rem;
            color: #667eea;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
        .effective-date {
            color: #718096;
            font-style: italic;
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>
    <a href="register.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Registration</a>
    
    <div class="legal-container">
        <h1>Terms and Conditions</h1>
        <p class="effective-date">Effective Date: <?php echo date('F j, Y'); ?></p>
        
        <h2>1. Acceptance of Terms</h2>
        <p>By accessing and using this website, you accept and agree to be bound by the terms and provision of this agreement.</p>
        
        <h2>2. Use License</h2>
        <p>Permission is granted to temporarily use this website for personal, non-commercial use only. This is the grant of a license, not a transfer of title.</p>
        
        <h2>3. User Responsibilities</h2>
        <p>As a user of this website, you agree to:</p>
        <ul>
            <li>Provide accurate and complete information when posting content</li>
            <li>Not post any illegal, harmful, or inappropriate content</li>
            <li>Not impersonate any person or entity</li>
            <li>Not engage in any activity that could harm or damage the website</li>
            <li>Not use the website for any unlawful purpose</li>
        </ul>
        
        <h2>4. Posting Guidelines</h2>
        <p>All postings are subject to review. We reserve the right to:</p>
        <ul>
            <li>Reject or remove any posting at our sole discretion</li>
            <li>Modify or edit any content posted</li>
            <li>Block access to users who violate these terms</li>
        </ul>
        
        <h2>5. Disclaimer</h2>
        <p>The information on this website is provided on an "as is" basis. We make no representations or warranties of any kind, express or implied, about the completeness, accuracy, reliability, suitability, or availability of the information contained on the website.</p>
        
        <h2>6. Limitation of Liability</h2>
        <p>In no event shall we be liable for any loss or damage including without limitation, indirect or consequential loss or damage, arising out of or in connection with the use of this website.</p>
        
        <h2>7. Privacy</h2>
        <p>Your privacy is important to us. Please review our Privacy Policy to understand how we collect, use, and protect your information.</p>
        
        <h2>8. Contact Information</h2>
        <p>If you have any questions about these Terms and Conditions, please contact us through the website.</p>
    </div>
</body>
</html>