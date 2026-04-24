<?php
session_start();
define('SECURE_ACCESS', true);
require_once 'config/database.php';
require_once 'includes/functions.php';

$page_title = 'Privacy Policy';
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
        <h1>Privacy Policy</h1>
        <p class="effective-date">Effective Date: <?php echo date('F j, Y'); ?></p>
        
        <h2>1. Introduction</h2>
        <p>We respect your privacy and are committed to protecting your personal data. This privacy policy will inform you as to how we look after your personal data when you visit our website.</p>
        
        <h2>2. Data We Collect</h2>
        <p>We may collect, use, store, and transfer different kinds of personal data about you which we have grouped together follows:</p>
        <ul>
            <li><strong>Identity Data</strong> - includes first name, last name, username or similar identifier</li>
            <li><strong>Contact Data</strong> - includes email address and telephone numbers</li>
            <li><strong>Technical Data</strong> - includes internet protocol (IP) address, browser type and version, time zone setting and location</li>
            <li><strong>Usage Data</strong> - includes information about how you use our website</li>
        </ul>
        
        <h2>3. How We Use Your Data</h2>
        <p>We will only use your personal data when the law allows us to. Most commonly, we will use your personal data in the following circumstances:</p>
        <ul>
            <li>Where we need to perform the contract we are about to enter into or have entered into with you</li>
            <li>Where it is necessary for our legitimate interests</li>
            <li>Where we need to comply with a legal obligation</li>
        </ul>
        
        <h2>4. Data Security</h2>
        <p>We have put in place appropriate security measures to prevent your personal data from being accidentally lost, used, or accessed inappropriately, altered, or disclosed.</p>
        
        <h2>5. Data Retention</h2>
        <p>We will only retain your personal data for as long as reasonably necessary to fulfill the purposes we collected it for, including for the purposes of satisfying any legal, accounting, or reporting requirements.</p>
        
        <h2>6. Your Legal Rights</h2>
        <p>Under certain circumstances, you have rights under data protection laws in relation to your personal data, including the right to:</p>
        <ul>
            <li>Request access to your personal data</li>
            <li>Request correction of your personal data</li>
            <li>Request erasure of your personal data</li>
            <li>Object to processing of your personal data</li>
            <li>Request restriction of processing your personal data</li>
            <li>Request transfer of your personal data</li>
            <li>Right to withdraw consent</li>
        </ul>
        
        <h2>7. Third-Party Links</h2>
        <p>This website may include links to third-party websites, plug-ins, and applications. Clicking on those links or enabling those connections may allow third parties to collect or share data about you.</p>
        
        <h2>8. Contact Information</h2>
        <p>If you have any questions about this privacy policy or our privacy practices, please contact us through the website.</p>
    </div>
</body>
</html>