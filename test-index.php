<?php
session_start();
require_once 'includes/functions.php';
require_once 'includes/Posting.php';

// Get recent postings
$postingModel = new Posting();
$recentPostings = $postingModel->getRecent(6);

echo "Number of postings: " . count($recentPostings) . "<br><br>";

// Output raw HTML
echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Test Index - " . APP_NAME . "</title>
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css'>
    <style>
        .posting-cards {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .posting-card {
            display: flex;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .posting-image {
            width: 300px;
            flex-shrink: 0;
            background: #f8f9fa;
        }
        
        .posting-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .no-image {
            width: 100%;
            height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
        }
        
        .posting-content {
            padding: 1rem;
            flex: 1;
        }
        
        .posting-title {
            font-size: 1.25rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        
        .posting-description {
            color: #6c757d;
            margin-bottom: 1rem;
        }
        
        .posting-meta {
            font-size: 0.875rem;
            color: #6c757d;
            margin-bottom: 1rem;
        }
        
        .posting-price {
            font-size: 1.5rem;
            font-weight: bold;
            color: #28a745;
            margin-bottom: 1rem;
        }
        
        .posting-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .vip-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: bold;
            color: white;
            margin-bottom: 0.5rem;
        }
        
        .vip-gold {
            background: #ffc107;
        }
        
        .vip-silver {
            background: #6c757d;
        }
        
        .vip-platinum {
            background: #e91e63;
        }
    </style>
</head>
<body>
    <h1 style='text-align: center; margin-top: 2rem;'>Test Index Page</h1>
    
    <div class='posting-cards'>";

if (!empty($recentPostings)) {
    foreach ($recentPostings as $index => $posting) {
        echo "
        <div class='posting-card'>
            <div class='posting-image'>";
                if (!empty($posting['images'])) {
                    echo "<img src='uploads/postings/" . explode(',', $posting['images'])[0] . "' alt='" . $posting['title'] . "'>";
                } else {
                    echo "<div class='no-image'><i class='fas fa-image fa-3x'></i></div>";
                }
                echo "
                <span class='vip-badge ";
                if ($index == 0) echo "vip-gold'>VIP GOLD";
                elseif ($index == 1) echo "vip-silver'>VIP SILVER";
                elseif ($index == 2) echo "vip-platinum'>VIP PLATINUM";
                else echo "'>VIP";
                echo "</span>
            </div>
            <div class='posting-content'>
                <h3 class='posting-title'>" . $posting['title'] . "</h3>
                <p class='posting-description'>" . substr($posting['description'], 0, 150) . "...</p>
                <div class='posting-meta'>
                    <i class='fas fa-calendar'></i> " . date('M d, Y', strtotime($posting['created_at'])) . "
                    <i class='fas fa-map-marker-alt' style='margin-left: 1rem;'></i> " . $posting['location'] . "
                    <i class='fas fa-check-circle' style='margin-left: 1rem;'></i> Verified
                </div>
                <div class='posting-price'>";
                if (!empty($posting['price']) && $posting['price'] > 0) {
                    echo "$" . number_format($posting['price'], 2);
                }
                echo "</div>
                <div class='posting-actions'>
                    <button style='padding: 0.5rem 1rem; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer;'>
                        <i class='fab fa-whatsapp'></i> WhatsApp
                    </button>
                    <button style='padding: 0.5rem 1rem; background: #17a2b8; color: white; border: none; border-radius: 4px; cursor: pointer;'>
                        <i class='fas fa-phone'></i> Call Now
                    </button>
                    <button style='padding: 0.5rem; background: #f8f9fa; color: #6c757d; border: none; border-radius: 4px; cursor: pointer;'>
                        <i class='fas fa-heart'></i>
                    </button>
                </div>
            </div>
        </div>";
    }
} else {
    echo "<div style='text-align: center; padding: 4rem; color: #6c757d;'>";
    echo "<i class='fas fa-inbox fa-3x mb-3'></i>";
    echo "<h3>No postings found</h3>";
    echo "<p>There are currently no VIP featured listings.</p>";
    echo "</div>";
}

echo "
    </div>
</body>
</html>";
?>