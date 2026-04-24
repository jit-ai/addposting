<?php
session_start();
require_once 'includes/functions.php';
require_once 'includes/Posting.php';

// Get recent postings
$postingModel = new Posting();
$recentPostings = $postingModel->getRecent(6);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test VIP Layout</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: #f8fafc;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        /* Header */
        header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.2rem !important;
            font-weight: bold;
        }
        
        /* Hero Section */
        .hero {
            background: #f8fafc;
            padding: 4rem 0;
            text-align: center;
        }
        
        .hero h1 {
            font-size: 2.5rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 1rem;
        }
        
        /* Featured Listings */
        .featured-listings {
            padding: 4rem 0;
            background: white;
        }
        
        .listings-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .listings-header h2 {
            font-size: 2rem;
            font-weight: 700;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        /* Posting Cards */
        .posting-cards {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        
        .posting-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            display: flex;
            flex-direction: row;
        }
        
        .posting-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }
        
        .posting-image {
            position: relative;
            width: 300px;
            height: 200px;
            flex-shrink: 0;
        }
        
        .posting-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .vip-badge {
            position: absolute;
            top: 1rem;
            left: 1rem;
            background: #1e293b;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
        }
        
        .vip-gold {
            background: #f59e0b;
        }
        
        .vip-silver {
            background: #94a3b8;
        }
        
        .vip-platinum {
            background: #8b5cf6;
        }
        
        .posting-content {
            padding: 1.5rem;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        
        .posting-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }
        
        .posting-description {
            color: #64748b;
            margin-bottom: 1rem;
            line-height: 1.6;
            font-size: 0.95rem;
        }
        
        .posting-meta {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
            font-size: 0.875rem;
            color: #64748b;
        }
        
        .posting-meta span {
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }
        
        .posting-price {
            font-size: 1.5rem;
            font-weight: 700;
            color: #10b981;
            margin-bottom: 1rem;
            text-align: right;
        }
        
        .posting-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .posting-actions button {
            flex: 1;
            padding: 0.75rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .whatsapp-btn {
            background: #10b981;
            color: white;
        }
        
        .whatsapp-btn:hover {
            background: #059669;
        }
        
        .call-btn {
            background: #1e293b;
            color: white;
        }
        
        .call-btn:hover {
            background: #334155;
        }
        
        .save-btn {
            background: #f1f5f9;
            color: #64748b;
            padding: 0.75rem;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .save-btn:hover {
            background: #e2e8f0;
            color: #1e293b;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2rem;
            }
            
            .listings-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
            
            .posting-card {
                flex-direction: column;
            }
            
            .posting-image {
                width: 100%;
                height: 200px;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="logo">
                <i class="fas fa-store"></i>
                <span>Add Posting</span>
            </div>
        </div>
    </header>
    
    <section class="hero">
        <div class="container">
            <h1>Find Featured Services & Ads</h1>
        </div>
    </section>
    
    <section class="featured-listings">
        <div class="container">
            <div class="listings-header">
                <h2>
                    <i class="fas fa-star"></i>
                    VIP Featured Listings
                </h2>
                <a href="#">View All VIPs</a>
            </div>
            
            <div class="posting-cards">
                <?php foreach ($recentPostings as $index => $posting): ?>
                <div class="posting-card">
                    <div class="posting-image">
                        <?php if (!empty($posting['images'])): ?>
                            <img src="uploads/postings/<?php echo explode(',', $posting['images'])[0]; ?>" alt="<?php echo $posting['title']; ?>">
                        <?php else: ?>
                            <div style="width: 100%; height: 100%; background: #f1f5f9; display: flex; align-items: center; justify-content: center; color: #64748b;">
                                <i class="fas fa-image" style="font-size: 3rem;"></i>
                            </div>
                        <?php endif; ?>
                        <span class="vip-badge <?php 
                            if ($index == 0) echo 'vip-gold';
                            elseif ($index == 1) echo 'vip-silver';
                            elseif ($index == 2) echo 'vip-platinum';
                        ?>">
                            <?php 
                                if ($index == 0) echo 'VIP GOLD';
                                elseif ($index == 1) echo 'VIP SILVER';
                                elseif ($index == 2) echo 'VIP PLATINUM';
                            ?>
                        </span>
                    </div>
                    <div class="posting-content">
                        <h3 class="posting-title"><?php echo $posting['title']; ?></h3>
                        <p class="posting-description"><?php echo substr($posting['description'], 0, 150); ?>...</p>
                        <div class="posting-meta">
                            <span><i class="fas fa-calendar"></i> <?php echo date('M d, Y', strtotime($posting['created_at'])); ?></span>
                            <span><i class="fas fa-map-marker-alt"></i> <?php echo $posting['location']; ?></span>
                            <span><i class="fas fa-check-circle"></i> Verified</span>
                        </div>
                        <div class="posting-price">
                            <?php if (!empty($posting['price']) && $posting['price'] > 0): ?>
                                $<?php echo number_format($posting['price'], 2); ?>
                            <?php endif; ?>
                        </div>
                        <div style="display: flex; gap: 0.5rem;">
                            <div class="posting-actions">
                                <button class="whatsapp-btn">
                                    <i class="fab fa-whatsapp"></i> WhatsApp
                                </button>
                                <button class="call-btn">
                                    <i class="fas fa-phone"></i> Call Now
                                </button>
                            </div>
                            <button class="save-btn">
                                <i class="fas fa-heart"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    
    <footer style="background: #2d3748; color: white; padding: 2rem 0; text-align: center;">
        <div class="container">
            <p>&copy; 2024 Add Posting. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>