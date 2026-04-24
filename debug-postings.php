<?php
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
    <title>Debug Postings</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background: #f8fafc;
        }
        
        .debug-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            max-width: 1200px;
            margin: 0 auto;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .debug-header {
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        
        .debug-info {
            background: #f0f0f0;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        
        .debug-info h3 {
            margin-top: 0;
            margin-bottom: 10px;
        }
        
        .debug-pre {
            background: #1e293b;
            color: #f1f5f9;
            padding: 15px;
            border-radius: 6px;
            font-family: monospace;
            white-space: pre-wrap;
            word-wrap: break-word;
            max-height: 500px;
            overflow-y: auto;
        }
        
        .posting-preview {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: white;
        }
        
        .posting-preview img {
            width: 300px;
            height: 200px;
            object-fit: cover;
            border-radius: 6px;
        }
        
        .posting-preview-content {
            flex: 1;
        }
        
        .posting-preview-title {
            font-size: 1.25rem;
            font-weight: bold;
            margin-bottom: 10px;
            color: #1e293b;
        }
        
        .posting-preview-description {
            color: #64748b;
            margin-bottom: 10px;
            line-height: 1.6;
        }
        
        .posting-preview-meta {
            font-size: 0.875rem;
            color: #64748b;
            margin-bottom: 10px;
        }
        
        .posting-preview-price {
            font-size: 1.5rem;
            font-weight: bold;
            color: #10b981;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="debug-container">
        <div class="debug-header">
            <h2>Debug: Recent Postings</h2>
            <p>Checking what's being returned from the getRecent() method</p>
        </div>
        
        <div class="debug-info">
            <h3>Number of Postings:</h3>
            <p style="font-size: 1.5rem; font-weight: bold; color: #1e293b;"><?php echo count($recentPostings); ?> postings returned</p>
        </div>
        
        <div class="debug-info">
            <h3>Raw Data:</h3>
            <div class="debug-pre">
<?php
print_r($recentPostings);
?>
            </div>
        </div>
        
        <div class="debug-info">
            <h3>Posting Previews:</h3>
            <?php foreach ($recentPostings as $index => $posting): ?>
            <div class="posting-preview">
                <img src="uploads/postings/<?php echo explode(',', $posting['images'])[0]; ?>" alt="<?php echo $posting['title']; ?>">
                <div class="posting-preview-content">
                    <div class="posting-preview-title"><?php echo $posting['title']; ?></div>
                    <div class="posting-preview-description"><?php echo substr($posting['description'], 0, 150); ?>...</div>
                    <div class="posting-preview-meta">
                        Category: <?php echo $posting['category']; ?><br>
                        Location: <?php echo $posting['location']; ?><br>
                        Price: $<?php echo number_format($posting['price'], 2); ?><br>
                        Contact: <?php echo $posting['contact']; ?><br>
                        Created: <?php echo date('M d, Y', strtotime($posting['created_at'])); ?>
                    </div>
                    <div class="posting-preview-price">$<?php echo number_format($posting['price'], 2); ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>