<?php
session_start();
require_once 'includes/functions.php';
require_once 'includes/Posting.php';

$postingModel = new Posting();
$posting = null;

// Check if title is provided (SEO-friendly URL)
if (isset($_GET['title']) && !empty($_GET['title'])) {
    $title = $_GET['title'];
    $posting = $postingModel->findByTitle($title);
    
    if (!$posting) {
        redirect('index.php');
    }
    
    // Redirect to clean URL if old format with id is used
    if (isset($_GET['id'])) {
        $newTitle = strtolower(str_replace(' ', '-', preg_replace('/[^a-zA-Z0-9 ]/', '', $posting['title'])));
        redirect('posting/' . $newTitle);
    }
} 
// Fallback: Check if posting ID is provided (legacy format)
elseif (isset($_GET['id']) && !empty($_GET['id'])) {
    $posting = $postingModel->findById($_GET['id']);
    
    if (!$posting) {
        redirect('404.php');
    }
    
    // Redirect to SEO-friendly URL
    $newTitle = strtolower(str_replace(' ', '-', preg_replace('/[^a-zA-Z0-9 ]/', '', $posting['title'])));
    redirect('posting/' . $newTitle);
} 
else {
    redirect('index.php');
}

// Get title from URL for display
$urlTitle = isset($_GET['title']) ? $_GET['title'] : '';

// Get user data for posting author
require_once 'includes/User.php';
$userModel = new User();
$author = $userModel->findById($posting['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $posting['title']; ?> - <?php echo APP_NAME; ?></title>
    <meta name="description" content="<?php echo substr(strip_tags($posting['description']), 0, 160); ?>">
    <meta name="keywords" content="<?php echo $posting['title']; ?>, <?php echo $posting['category']; ?>, <?php echo $posting['city']; ?>, <?php echo $posting['state']; ?>">
    <meta property="og:title" content="<?php echo $posting['title']; ?> - <?php echo APP_NAME; ?>">
    <meta property="og:description" content="<?php echo substr(strip_tags($posting['description']), 0, 160); ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
    <meta property="og:site_name" content="<?php echo APP_NAME; ?>">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo $posting['title']; ?> - <?php echo APP_NAME; ?>">
    <meta name="twitter:description" content="<?php echo substr(strip_tags($posting['description']), 0, 160); ?>">
    <link rel="canonical" href="<?php echo 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
    <link rel="alternate" hreflang="en" href="<?php echo 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
    <?php if (!empty($posting['images'])): ?>
    <meta property="og:image" content="<?php echo 'https://' . $_SERVER['HTTP_HOST'] . '/addposting/uploads/postings/' . explode(',', $posting['images'])[0]; ?>">
    <?php endif; ?>
    <script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "Product",
    "name": "<?php echo addslashes($posting['title']); ?>",
    "description": "<?php echo addslashes(substr(strip_tags($posting['description']), 0, 500)); ?>",
    "category": "<?php echo addslashes($posting['category']); ?>",
    <?php if (!empty($posting['images'])): ?>
    "image": "<?php echo 'https://' . $_SERVER['HTTP_HOST'] . '/addposting/uploads/postings/' . explode(',', $posting['images'])[0]; ?>",
    <?php endif; ?>
    <?php if (!empty($posting['price']) && $posting['price'] > 0): ?>
    "offers": {
        "@type": "Offer",
        "price": "<?php echo $posting['price']; ?>",
        "priceCurrency": "INR",
        "availability": "https://schema.org/InStock"
    },
    <?php endif; ?>
    "address": {
        "@type": "PostalAddress",
        "addressLocality": "<?php echo addslashes($posting['city']); ?>",
        "addressRegion": "<?php echo addslashes($posting['state']); ?>",
        "addressCountry": "IN"
    },
    "seller": {
        "@type": "Person",
        "name": "<?php echo addslashes($author['name']); ?>"
    }
}
</script>
    <link rel="stylesheet" href="/addposting/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .posting-detail {
            padding: 2rem 0;
        }

        .posting-main {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .posting-gallery {
            background: #1e1e1e;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
            border: 1px solid #333;
        }

        .main-image {
            width: 100%;
            height: 400px;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 1rem;
            background: #2a2a2a;
        }

        .main-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .image-thumbnails {
            display: flex;
            gap: 10px;
            overflow-x: auto;
        }

        .image-thumbnails img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 5px;
            cursor: pointer;
            border: 2px solid transparent;
            transition: all 0.3s ease;
        }

        .image-thumbnails img:hover,
        .image-thumbnails img.active {
            border-color: #667eea;
        }

        .posting-info {
            background: #1e1e1e;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
            border: 1px solid #333;
        }

        .posting-category {
            display: inline-block;
            background: #dc3545;
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
            margin-bottom: 1rem;
        }

        .posting-title {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: #dc3545;
        }

        .posting-price {
            font-size: 2.5rem;
            font-weight: bold;
            color: #48c78e;
            margin-bottom: 1rem;
        }

        .posting-location {
            display: flex;
            align-items: center;
            gap: 5px;
            color: #a0aec0;
            margin-bottom: 1rem;
        }

        .posting-contact {
            display: flex;
            align-items: center;
            gap: 5px;
            color: #a0aec0;
            margin-bottom: 2rem;
        }

        .posting-description {
         background: #1e1e1e;
        padding: 1.5rem;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        border: 1px solid #333;
        }

        .posting-description h3 {
            color: #dc3545;
            margin-bottom: 1rem;
        }

        .posting-description p {
            line-height: 1.8;
            color: #a0aec0;
        }

        .posting-actions {
            display: flex;
            gap: 10px;
            margin-top: 2rem;
        }

        .author-info {
            background: #1e1e1e;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
            border: 1px solid #333;
        }
        

        .author-info h3 {
            color: #dc3545;
            margin-bottom: 1rem;
        }

        .author-details {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .author-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: #667eea;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.5rem;
        }

        .author-name {
            font-size: 1.2rem;
            font-weight: bold;
            color: #f0f0f0;
        }

        .author-posts {
            color: #a0aec0;
            font-size: 0.9rem;
        }

         .similar-postings {
            margin-top: 3rem;
            background: #0a0a0a;
            padding: 2rem 0;
            border-radius: 16px;
        }

        .similar-postings h2 {
            font-size: 1.75rem;
            font-weight: 700;
            color: #dc3545;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .similar-postings h3 {
            font-size: 1rem;
            color: #dc3545;
            margin-bottom: 0rem;
        }

        /* Posting Cards */
        .posting-cards {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }

        .posting-card {
            background: #1e1e1e;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
            transition: transform 0.3s, box-shadow 0.3s;
            display: flex;
            flex-direction: column;
            height: 100%;
            border: 1px solid #333;
        }

        .posting-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
        }

        .posting-card a {
            text-decoration: none;
            color: inherit;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .posting-image {
            position: relative;
            width: 100%;
            height: 200px;
            flex-shrink: 0;
            background: #2a2a2a;
        }

        .posting-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
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
            color: #dc3545;
            margin-bottom: 0.5rem;
        }

        .posting-description {
            color: #a0aec0;
            margin-bottom: 1rem;
            line-height: 1.6;
            font-size: 0.95rem;
        }

        .posting-meta {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
            font-size: 0.875rem;
            color: #a0aec0;
            flex-wrap: wrap;
        }

        .posting-meta span {
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .posting-price {
            font-size: 1.5rem;
            font-weight: 700;
            color: #48c78e;
            margin-bottom: 1rem;
            text-align: left;
        }

        .posting-actions {
            display: flex;
            gap: 0.5rem;
            width: 100%;
        }

        .posting-actions a {
            flex: 1;
            padding: 0.75rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            color: white;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            white-space: nowrap;
            font-size: 0.8rem;
        }

        .whatsapp-btn {
            background: #25d366;
            color: white;
        }

        .whatsapp-btn:hover {
            background: #20ba5a;
        }

        .call-btn {
            background: #1e293b;
            color: white;
        }

        .call-btn:hover {
            background: #334155;
        }

        .create-similar-btn {
            background: #667eea;
            color: white;
        }

        .create-similar-btn:hover {
            background: #5a6fd8;
        }

        .telegram-btn {
            background: #0088cc;
            color: white;
        }

        .telegram-btn:hover {
            background: #006699;
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

        @media (max-width: 1200px) {
            .posting-cards {
                grid-template-columns: repeat(3, 1fr);
            }
        }
        
        @media (max-width: 900px) {
            .posting-cards {
                grid-template-columns: repeat(2, 1fr);
            }
        }

         @media (max-width: 768px) {
            .posting-main {
                grid-template-columns: 1fr;
            }

            .main-image {
                height: 300px;
            }

            .posting-title {
                font-size: 1.5rem;
            }

            .posting-price {
                font-size: 2rem;
            }
            
            .posting-cards {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body class="auth-body">
    <?php include 'includes/header.php'; ?>

                    <?php if (isAdmin()): ?>
                        <li><a href="admin/dashboard.php" class="btn btn-danger">Admin Dashboard</a></li>
                    <?php endif; ?>
<!-- Posting Detail -->
    <section class="posting-detail">
        <div class="container">
            <div class="posting-main">
                <div class="posting-gallery">
                    <div class="main-image">
                        <?php if (!empty($posting['images'])): ?>
                            <img src="uploads/postings/<?php echo explode(',', $posting['images'])[0]; ?>" alt="<?php echo $posting['title']; ?>">
                        <?php else: ?>
                            <div class="no-image">
                                <i class="fas fa-image"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="image-thumbnails">
                        <?php if (!empty($posting['images'])): ?>
                            <?php foreach (explode(',', $posting['images']) as $key => $image): ?>
                                <img src="uploads/postings/<?php echo $image; ?>" alt="Image <?php echo $key + 1; ?>" class="<?php echo $key === 0 ? 'active' : ''; ?>" data-index="<?php echo $key; ?>">
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="posting-info">
                    <div class="posting-category"><?php echo $posting['category']; ?></div>
                    <h1 class="posting-title"><?php echo $posting['title']; ?></h1>
                    <?php if (!empty($posting['price']) && $posting['price'] > 0): ?>
                    <div class="posting-price">₹<?php echo number_format($posting['price'], 2); ?></div>
                    <?php endif; ?>
                    <div class="posting-location">
                        <i class="fas fa-map-marker-alt"></i> <?php echo $posting['city'] . ', ' . $posting['state']; ?>
                    </div>
                    <div class="posting-contact">
                        <i class="fas fa-phone"></i> <?php echo $posting['contact']; ?>
                    </div>

                               <div class="posting-actions">
                                <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $posting['contact']); ?>" class="whatsapp-btn" target="_blank">
                                    <i class="fab fa-whatsapp"></i> WhatsApp
                                </a>
                                <a href="tel:<?php echo preg_replace('/[^0-9]/', '', $posting['contact']); ?>" class="call-btn" target="_blank">
                                    <i class="fas fa-phone"></i> Call Now
                                </a>
                                <a href="https://t.me/+<?php echo preg_replace('/[^0-9]/', '', $posting['contact']); ?>" class="telegram-btn" target="_blank">
                                    <i class="fab fa-telegram"></i> Telegram
                                </a>
                            </div>

                    <div class="author-info">
                        <h3>Seller Information</h3>
                        <div class="author-details">
                            <div class="author-avatar"><?php echo strtoupper(substr($author['name'], 0, 1)); ?></div>
                            <div>
                                <div class="author-name"><?php echo $author['name']; ?></div>
                                <div class="author-posts"><?php echo count($postingModel->findByUserId($author['id'])); ?> postings</div>
                            </div>
                        </div>
                        <button class="btn btn-primary btn-block" style="margin-top: 1rem;">
                            <i class="fas fa-envelope"></i> Contact Seller
                        </button>
                    </div>
                </div>
            </div>

            <div class="posting-description">
                <h3>Description</h3>
                <p><?php echo nl2br($posting['description']); ?></p>
            </div>

<!-- Similar Postings -->
             <div class="similar-postings">
                 <h2>
                     <i class="fas fa-th-large"></i>
                     Similar Postings
                 </h2>
                 <div class="posting-cards">
                     <?php
                     // Get similar postings
                     require_once 'includes/database.php';
                     $db = new Database();
                     $conn = $db->getConnection();
                     $similarPostings = $conn->query("SELECT p.*, u.name as author_name FROM postings p 
                             JOIN users u ON p.user_id = u.id 
                             WHERE p.category = '" . $posting['category'] . "' 
                             AND p.id != " . $posting['id'] . " 
                             AND p.status = 'active' 
                             ORDER BY p.created_at DESC LIMIT 4")->fetch_all(MYSQLI_ASSOC);

                     foreach ($similarPostings as $similarPosting):
                     ?>
                     <div class="posting-card">
                         <div class="posting-image">
                             <a href="posting/<?php echo strtolower(str_replace(' ', '-', preg_replace('/[^a-zA-Z0-9 ]/', '', $similarPosting['title']))); ?>" style="display: block; width: 100%; height: 100%;">
                             <?php if (!empty($similarPosting['images'])): ?>
                                 <img src="uploads/postings/<?php echo explode(',', $similarPosting['images'])[0]; ?>" alt="<?php echo $similarPosting['title']; ?>">
                             <?php else: ?>
                                 <div style="width: 100%; height: 100%; background: #2a2a2a; display: flex; align-items: center; justify-content: center; color: #4a5568;">
                                     <i class="fas fa-image" style="font-size: 3rem;"></i>
                                 </div>
                             <?php endif; ?>
                             </a>
                         </div>
                         <div class="posting-content">
                             <h3 class="posting-title"><?php echo $similarPosting['title']; ?></h3>
                             <p class="posting-description"><?php echo substr($similarPosting['description'], 0, 150); ?>...</p>
                             <div class="posting-meta">
                                 <span><i class="fas fa-calendar"></i> <?php echo rand(2, 25); ?> Years</span>
                                 <span><i class="fas fa-map-marker-alt"></i> <?php echo $similarPosting['city'] . ', ' . $similarPosting['state']; ?></span>
                                 <span><i class="fas fa-check-circle"></i> Verified</span>
                             </div>
                         </div>
                     </div>
                     <?php endforeach; ?>
                 </div>
             </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <script src="assets/js/main.js?v=<?php echo time(); ?>"></script>
    <script>
        // Image gallery functionality
        document.addEventListener('DOMContentLoaded', function() {
            const mainImage = document.querySelector('.main-image img');
            const thumbnails = document.querySelectorAll('.image-thumbnails img');

            thumbnails.forEach(thumb => {
                thumb.addEventListener('click', function() {
                    // Update main image
                    mainImage.src = this.src;
                    
                    // Update active thumbnail
                    thumbnails.forEach(t => t.classList.remove('active'));
                    this.classList.add('active');
                });
            });
        });
    </script>
</body>
</html>