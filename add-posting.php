<?php
session_start();
require_once 'includes/functions.php';
require_once 'includes/Posting.php';
require_once 'includes/database.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

$postingModel = new Posting();
$errors = [];
$success = '';

// Get active categories from database
$db = new Database();
$conn = $db->getConnection();
$activeCategories = $conn->query("SELECT name FROM categories WHERE status = 'active' ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);

// Initialize variables with default values
$title = '';
$description = '';
$category = '';
$price = '';
$state = '';
$city = '';
$contact = '';
$publish = 'immediately';
$schedule_date = '';
$schedule_time = '';
$isEditing = false;
$postId = null;
$existingImages = '';
$isPatching = false;
$patchSource = null;

// Check if editing an existing post
if (isset($_GET['id'])) {
    $postId = (int)$_GET['id'];
    $postData = $postingModel->findById($postId);

    if ($postData && ($postData['user_id'] == $_SESSION['user_id'] || isAdmin())) {
        $isEditing = true;
        $title = $postData['title'];
        $description = $postData['description'];
        $category = $postData['category'];
        $price = $postData['price'];
        $state = $postData['state'];
        $city = $postData['city'];
        $contact = $postData['contact'];
        $publish = isset($postData['publish']) ? $postData['publish'] : 'immediately';
        $schedule_date = isset($postData['schedule_date']) ? $postData['schedule_date'] : '';
        $schedule_time = isset($postData['schedule_time']) ? $postData['schedule_time'] : '';
        $existingImages = $postData['images'];
    }
}
// Check if patching from an existing post (to create new post with similar details)
elseif (isset($_GET['patch_id'])) {
    $patchId = (int)$_GET['patch_id'];
    $patchData = $postingModel->findById($patchId);

    if ($patchData && $patchData['status'] == 'active') {
        $isPatching = true;
        $patchSource = $patchId;
        $title = $patchData['title'];
        $description = $patchData['description'];
        $category = $patchData['category'];
        $price = $patchData['price'];
        $state = $patchData['state'];
        $city = $patchData['city'];
        $contact = $patchData['contact'];
        $publish = isset($patchData['publish']) ? $patchData['publish'] : 'immediately';
        $schedule_date = isset($patchData['schedule_date']) ? $patchData['schedule_date'] : '';
        $schedule_time = isset($patchData['schedule_time']) ? $patchData['schedule_time'] : '';
        // Don't copy images for new post
        $existingImages = '';
    }
}
// If not editing or patching, but user is logged in, pre-fill with their latest post
elseif (isLoggedIn()) {
    $userPosts = $postingModel->findByUserId($_SESSION['user_id']);
    if (!empty($userPosts)) {
        $latestPost = $userPosts[0]; // Most recent post
        $title = $latestPost['title'];
        $description = $latestPost['description'];
        $category = $latestPost['category'];
        $price = $latestPost['price'];
        $state = $latestPost['state'];
        $city = $latestPost['city'];
        $contact = $latestPost['contact'];
        $publish = isset($latestPost['publish']) ? $latestPost['publish'] : 'immediately';
        $schedule_date = isset($latestPost['schedule_date']) ? $latestPost['schedule_date'] : '';
        $schedule_time = isset($latestPost['schedule_time']) ? $latestPost['schedule_time'] : '';
        // Don't copy images for new post
        $existingImages = '';
    }
}

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Validate input
        $title = sanitize($_POST['title']);
        $description = sanitize($_POST['description']);
        $category = sanitize($_POST['category']);
        // Fix for "Column 'price' cannot be null"
        $price = (isset($_POST['price']) && $_POST['price'] !== '') ? (float)$_POST['price'] : 0.0;
        $state = sanitize($_POST['state']);
        $city = sanitize($_POST['city']);
        $contact = preg_replace('/[^0-9]/', '', sanitize($_POST['contact']));
        $publish = sanitize($_POST['publish']);
        $schedule_date = isset($_POST['schedule_date']) ? sanitize($_POST['schedule_date']) : '';
        $schedule_time = isset($_POST['schedule_time']) ? sanitize($_POST['schedule_time']) : '';
        $images = [];

    if (empty($title)) {
        $errors[] = 'Title is required';
    } elseif (strlen($title) > 255) {
        $errors[] = 'Title must be less than 255 characters';
    }

    if (empty($description)) {
        $errors[] = 'Description is required';
    }

    if (empty($category)) {
        $errors[] = 'Category is required';
    }

    if (empty($state)) {
        $errors[] = 'State is required';
    }

    if (empty($city)) {
        $errors[] = 'City is required';
    }

    if (empty($contact)) {
        $errors[] = 'Contact information is required';
    }

    // Validate schedule fields if scheduling
    if ($publish === 'schedule') {
        if (empty($schedule_date)) {
            $errors[] = 'Schedule date is required';
        } elseif ($schedule_date < date('Y-m-d')) {
            $errors[] = 'Schedule date must be in the future';
        }
        
        if (empty($schedule_time)) {
            $errors[] = 'Schedule time is required';
        } else {
            // Check if scheduled time is in the future
            $current_datetime = date('Y-m-d H:i');
            $scheduled_datetime = $schedule_date . ' ' . $schedule_time;
            if ($scheduled_datetime <= $current_datetime) {
                $errors[] = 'Scheduled time must be in the future';
            }
        }
    }

    // Handle image uploads
    if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
        foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
            $file = [
                'name' => $_FILES['images']['name'][$key],
                'type' => $_FILES['images']['type'][$key],
                'tmp_name' => $tmpName,
                'error' => $_FILES['images']['error'][$key],
                'size' => $_FILES['images']['size'][$key]
            ];
            
            $uploadResult = uploadFile($file, 'postings/');
            
            if (isset($uploadResult['error'])) {
                $errors[] = $uploadResult['error'];
            } else {
                $images[] = $uploadResult['success'];
            }
        }
    }

    // If no errors, create or update posting
        if (empty($errors)) {
            $data = [
                'user_id' => $_SESSION['user_id'],
                'title' => $title,
                'description' => $description,
                'category' => $category,
                'price' => $price,
                'state' => $state,
                'city' => $city,
                'contact' => $contact,
                'publish' => $publish,
                'schedule_date' => $schedule_date,
                'schedule_time' => $schedule_time
            ];

            // Handle Images logic
            if ($isEditing) {
                // If no new images uploaded, keep existing ones
                if (empty($images) && !empty($existingImages)) {
                    $data['images'] = $existingImages;
                } else {
                    $data['images'] = implode(',', $images);
                }
                
                if ($postingModel->update($postId, $data)) {
                    $success = 'Posting updated successfully!';
                    // Don't clear form on edit so user sees changes
                } else {
                    $errors[] = 'Failed to update posting.';
                }
            } else {
                // Creating new
                $data['images'] = implode(',', $images);
                if ($postingModel->create($data)) {
                    $success = 'Posting created successfully!';
                    $_POST = []; // Clear form
                } else {
                    $errors[] = 'Failed to create posting.';
                }
            }

        if (!empty($success) && !$isEditing) {
            // Clear form
            $title = ''; $description = ''; $category = ''; $price = ''; 
            $state = ''; $city = ''; $contact = ''; $schedule_date = ''; $schedule_time = '';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isEditing ? 'Edit Post' : ($isPatching ? 'Create Similar Post' : 'Create New Post'); ?> - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .post-create-container {
            display: grid;
            grid-template-columns: 1fr 380px;
            gap: 30px;
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .editor-panel {
            background: #1e1e1e;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border: 1px solid #333;
        }

        .sidebar-panel {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .publish-card,
        .settings-card,
        .tip-card {
            background: #1e1e1e;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .publish-card h3,
        .settings-card h3 {
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6b7280;
            margin-bottom: 16px;
        }

        .publish-btn {
            width: 100%;
            padding: 12px 24px;
            background: #1f2937;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: background 0.2s;
            margin-bottom: 12px;
        }

        .publish-btn:hover {
            background: #374151;
        }

        .draft-btn {
            width: 100%;
            padding: 12px 24px;
            background: #f3f4f6;
            color: #374151;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: background 0.2s;
            margin-bottom: 16px;
        }

        .draft-btn:hover {
            background: #e5e7eb;
        }

        .trash-link {
            color: #ef4444;
            text-decoration: none;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 8px 0;
            transition: color 0.2s;
        }

        .trash-link:hover {
            color: #dc2626;
            text-decoration: underline;
        }

        .last-saved {
            font-size: 12px;
            color: #9ca3af;
            margin-top: 12px;
        }

        .form-section {
            margin-bottom: 32px;
        }

        .form-row {
            display: flex;
            gap: 20px;
            margin-bottom: 32px;
        }

        .form-row .form-section {
            flex: 1;
            margin-bottom: 0;
        }

        .form-section label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #111827;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-section input[type="text"],
        .form-section input[type="number"],
        .form-section input[type="date"],
        .form-section input[type="time"],
        .form-section textarea,
        .form-section select {
                width: 100%;
                padding: 0.75rem;
                border: 1px solid #444;
                border-radius: 8px;
                font-size: 1rem;
                background: #2a2a2a;
                color: #f0f0f0;
        }

        .form-section input[type="text"]:focus,
        .form-section input[type="number"]:focus,
        .form-section input[type="date"]:focus,
        .form-section input[type="time"]:focus,
        .form-section textarea:focus,
        .form-section select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-section textarea {
            min-height: 200px;
            resize: vertical;
            font-family: inherit;
        }

        .title-input {
            font-size: 24px;
            font-weight: 600;
            color: #111827;
            padding: 16px;
        }

        .featured-image {
            border: 2px dashed #d1d5db;
            border-radius: 8px;
            padding: 40px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
        }

        .featured-image:hover {
            border-color: #667eea;
            background: #2a2a2a;
        }

        .featured-image i {
            font-size: 48px;
            color: #9ca3af;
            margin-bottom: 12px;
        }

        .featured-image p {
            color: #6b7280;
            font-size: 14px;
        }

        .featured-image input[type="file"] {
            display: none;
        }

        .editor-toolbar {
            display: flex;
            gap: 4px;
            padding: 8px 16px;
            background: #2a2a2a;
            border: 2px solid #2a2a2a;
            border-bottom: none;
            border-radius: 8px 8px 0 0;
        }

        .editor-toolbar button {
            background: none;
            border: none;
            padding: 6px 8px;
            cursor: pointer;
            border-radius: 4px;
            transition: background 0.2s;
            color: #fff;
        }

        .editor-toolbar button:hover {
            background: #2a2a2a;
        }

        .settings-card .form-group {
            margin-bottom: 20px;
        }

        .settings-card label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            color: #374151;
            margin-bottom: 8px;
        }

        .settings-card select,
        .settings-card input[type="text"] {
            width: 100%;
            padding: 10px 12px;
            border: 2px solid #e5e7eb;
            border-radius: 6px;
            font-size: 14px;
        }

        .tags-input {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            padding: 8px;
            border: 2px solid #e5e7eb;
            border-radius: 6px;
            min-height: 44px;
        }

        .tag {
            background: #2a2a2a;
            color: #374151;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .tag-remove {
            cursor: pointer;
            color: #6b7280;
            font-size: 14px;
        }

        .tag-remove:hover {
            color: #dc2626;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 0;
        }

        .checkbox-group label {
            margin-bottom: 0;
            cursor: pointer;
        }

        .checkbox-group input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }

        .tip-card {
            background: linear-gradient(135deg, #1f2937 0%, #374151 100%);
            color: white;
        }

        .tip-card h3 {
            color: white;
            margin-bottom: 12px;
        }

        .tip-card p {
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 12px;
        }

        .tip-card .learn-more {
            color: #60a5fa;
            text-decoration: none;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .tip-card .learn-more:hover {
            text-decoration: underline;
        }

        @media (max-width: 1024px) {
            .post-create-container {
                grid-template-columns: 1fr;
            }
        }

        .alert {
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 24px;
        }

        .alert-danger {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }

        .alert-success {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #166534;
        }

        .alert p {
            margin: 0;
            font-size: 14px;
            line-height: 1.5;
        }
    </style>
</head>
<body class="auth-body">
    <?php include 'includes/header.php'; ?>

    <!-- Editor Panel -->
    <div class="mobile-nav">
        <div class="mobile-nav-header">
            <div class="logo">
                <i class="fas fa-store"></i>
                <span><?php echo APP_NAME; ?></span>
            </div>
            <button class="mobile-nav-close" aria-label="Close menu">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>

    <!-- Post Create Section -->
    <div class="post-create-container">
        <!-- Editor Panel -->
        <div class="editor-panel">
<h1 style="font-size: 28px; font-weight: 700; margin-bottom: 12px; color: #dc3545;"><?php echo $isEditing ? 'Edit Post' : ($isPatching ? 'Create Similar Post' : 'Create New Post'); ?></h1>
                        <p style="color: #a0aec0; margin-bottom: 32px;"><?php echo $isEditing ? 'Update your listing details below.' : ($isPatching ? 'Create a new post based on the selected listing.' : 'Draft your next story and share it with the community.'); ?></p>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo $error; ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="alert alert-success">
                    <p><?php echo $success; ?></p>
                    <p><a href="my-postings.php" class="btn btn-primary">View My Postings</a></p>
                </div>
            <?php endif; ?>
            
            <?php if (empty($success)): ?>
                <form method="POST" action="add-posting.php<?php echo $isEditing ? '?id=' . $postId : ($isPatching ? '?patch_id=' . $patchSource : ''); ?>" enctype="multipart/form-data">
                    <div class="form-section">
                        <label for="title">Post Title</label>
                        <input type="text" id="title" name="title" class="title-input" required placeholder="Enter a catchy title..." value="<?php echo htmlspecialchars($title); ?>">
                    </div>

                    <div class="form-section">
                        <label for="images">Featured Image</label>
                        <div class="featured-image">
                            <input type="file" id="images" name="images[]" multiple accept="image/*">
                            <label for="images">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <p>Click to upload or drag and drop</p>
                                <p style="font-size: 12px; color: #9ca3af; margin-top: 4px;">PNG, JPG, GIF up to 5MB</p>
                            </label>
                        </div>
                    </div>

                    <div class="form-section">
                        <label for="description">Content</label>
                        <div class="editor-toolbar">
                            <button type="button"><i class="fas fa-bold"></i></button>
                            <button type="button"><i class="fas fa-italic"></i></button>
                            <button type="button"><i class="fas fa-list-ul"></i></button>
                            <button type="button"><i class="fas fa-list-ol"></i></button>
                            <button type="button"><i class="fas fa-link"></i></button>
                            <button type="button"><i class="fas fa-image"></i></button>
                            <button type="button"><i class="fas fa-code"></i></button>
                        </div>
                        <textarea id="description" name="description" required placeholder="Write your story here..."><?php echo htmlspecialchars($description); ?></textarea>
                    </div>

                    <div class="form-section">
                        <label for="category">Category</label>
                        <select id="category" name="category" required>
                            <option value="">Select a category</option>
                            <?php foreach ($activeCategories as $cat): ?>
                            <option value="<?php echo $cat['name']; ?>" <?php echo $category === $cat['name'] ? 'selected' : ''; ?>><?php echo $cat['name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-section">
                        <label for="price">Price (₹)</label>
                        <input type="number" id="price" name="price" step="0.01" min="0" placeholder="Enter price" value="<?php echo $price !== '' ? htmlspecialchars($price) : ''; ?>">
                    </div>
                    

                    
                    <div class="form-section">
                        <label for="state">State</label>
                        <select id="state" name="state" required onchange="loadCities(this.value)" style="width: 100%; padding: 0.75rem; border: 1px solid #444; border-radius: 8px; font-size: 1rem; background: #2a2a2a; color: #f0f0f0;">
                            <option value="">Select a state</option>
                            <option value="Andhra Pradesh" <?php echo $state === 'Andhra Pradesh' ? 'selected' : ''; ?>>Andhra Pradesh</option>
                            <option value="Arunachal Pradesh" <?php echo $state === 'Arunachal Pradesh' ? 'selected' : ''; ?>>Arunachal Pradesh</option>
                            <option value="Assam" <?php echo $state === 'Assam' ? 'selected' : ''; ?>>Assam</option>
                            <option value="Bihar" <?php echo $state === 'Bihar' ? 'selected' : ''; ?>>Bihar</option>
                            <option value="Chhattisgarh" <?php echo $state === 'Chhattisgarh' ? 'selected' : ''; ?>>Chhattisgarh</option>
                            <option value="Goa" <?php echo $state === 'Goa' ? 'selected' : ''; ?>>Goa</option>
                            <option value="Gujarat" <?php echo $state === 'Gujarat' ? 'selected' : ''; ?>>Gujarat</option>
                            <option value="Haryana" <?php echo $state === 'Haryana' ? 'selected' : ''; ?>>Haryana</option>
                            <option value="Himachal Pradesh" <?php echo $state === 'Himachal Pradesh' ? 'selected' : ''; ?>>Himachal Pradesh</option>
                            <option value="Jharkhand" <?php echo $state === 'Jharkhand' ? 'selected' : ''; ?>>Jharkhand</option>
                            <option value="Karnataka" <?php echo $state === 'Karnataka' ? 'selected' : ''; ?>>Karnataka</option>
                            <option value="Kerala" <?php echo $state === 'Kerala' ? 'selected' : ''; ?>>Kerala</option>
                            <option value="Madhya Pradesh" <?php echo $state === 'Madhya Pradesh' ? 'selected' : ''; ?>>Madhya Pradesh</option>
                            <option value="Maharashtra" <?php echo $state === 'Maharashtra' ? 'selected' : ''; ?>>Maharashtra</option>
                            <option value="Manipur" <?php echo $state === 'Manipur' ? 'selected' : ''; ?>>Manipur</option>
                            <option value="Meghalaya" <?php echo $state === 'Meghalaya' ? 'selected' : ''; ?>>Meghalaya</option>
                            <option value="Mizoram" <?php echo $state === 'Mizoram' ? 'selected' : ''; ?>>Mizoram</option>
                            <option value="Nagaland" <?php echo $state === 'Nagaland' ? 'selected' : ''; ?>>Nagaland</option>
                            <option value="Odisha" <?php echo $state === 'Odisha' ? 'selected' : ''; ?>>Odisha</option>
                            <option value="Punjab" <?php echo $state === 'Punjab' ? 'selected' : ''; ?>>Punjab</option>
                            <option value="Rajasthan" <?php echo $state === 'Rajasthan' ? 'selected' : ''; ?>>Rajasthan</option>
                            <option value="Sikkim" <?php echo $state === 'Sikkim' ? 'selected' : ''; ?>>Sikkim</option>
                            <option value="Tamil Nadu" <?php echo $state === 'Tamil Nadu' ? 'selected' : ''; ?>>Tamil Nadu</option>
                            <option value="Telangana" <?php echo $state === 'Telangana' ? 'selected' : ''; ?>>Telangana</option>
                            <option value="Tripura" <?php echo $state === 'Tripura' ? 'selected' : ''; ?>>Tripura</option>
                            <option value="Uttar Pradesh" <?php echo $state === 'Uttar Pradesh' ? 'selected' : ''; ?>>Uttar Pradesh</option>
                            <option value="Uttarakhand" <?php echo $state === 'Uttarakhand' ? 'selected' : ''; ?>>Uttarakhand</option>
                            <option value="West Bengal" <?php echo $state === 'West Bengal' ? 'selected' : ''; ?>>West Bengal</option>
                            <option value="Delhi" <?php echo $state === 'Delhi' ? 'selected' : ''; ?>>Delhi</option>
                        </select>
                    </div>

                    <div class="form-section">
                        <label for="city">City</label>
                        <select id="city" name="city" required style="width: 100%; padding: 0.75rem; border: 1px solid #444; border-radius: 8px; font-size: 1rem; background: #2a2a2a; color: #f0f0f0;">
                            <option value="">Select a city</option>
                            <?php if (!empty($state)): ?>
                                <?php
                                $citiesByState = [
                                    'Andhra Pradesh' => ['Visakhapatnam', 'Vijayawada', 'Guntur', 'Tirupati', 'Nellore', 'Kakinada', 'Rajahmundry', 'Kadapa', 'Kurnool', 'Anantapur'],
                                    'Arunachal Pradesh' => ['Itanagar', 'Naharlagun', 'Pasighat', 'Tezpur', 'Dibang Valley', 'Roing', 'Ziro', 'Bomdila'],
                                    'Assam' => ['Guwahati', 'Silchar', 'Dibrugarh', 'Jorhat', 'Nagaon', 'Tinsukia', 'Tezpur', 'Bongaigaon'],
                                    'Bihar' => ['Patna', 'Gaya', 'Bhagalpur', 'Muzaffarpur', 'Darbhanga', 'Arrah', 'Begusarai', 'Katihar', 'Munger', 'Purnia'],
                                    'Chhattisgarh' => ['Raipur', 'Bhilai', 'Bilaspur', 'Durg', 'Rajnandgaon', 'Korba', 'Raigarh', 'Mahasamund'],
                                    'Goa' => ['Panaji', 'Margao', 'Vasco da Gama', 'Mapusa', 'Ponda', 'Curchorem', 'Benaulim'],
                                    'Gujarat' => ['Ahmedabad', 'Surat', 'Vadodara', 'Rajkot', 'Bhavnagar', 'Jamnagar', 'Junagadh', 'Gandhidham', 'Anand', 'Bharuch'],
                                    'Haryana' => ['Faridabad', 'Gurgaon', 'Panipat', 'Karnal', 'Rohtak', 'Hisar', 'Sonipat', 'Ambala', 'Yamunanagar', 'Kurukshetra'],
                                    'Himachal Pradesh' => ['Shimla', 'Mandi', 'Solan', 'Kullu', 'Manali', 'Dharamshala', 'Kangra', 'Chamba', 'Bilaspur'],
                                    'Jharkhand' => ['Ranchi', 'Jamshedpur', 'Dhanbad', 'Bokaro Steel City', 'Hazaribagh', 'Deoghar', 'Ramgarh', 'Giridih'],
                                    'Karnataka' => ['Bengaluru', 'Mysore', 'Mangalore', 'Hubli-Dharwad', 'Belgaum', 'Gulbarga', 'Dharwad', 'Udupi', 'Hassan', 'Bellary'],
                                    'Kerala' => ['Thiruvananthapuram', 'Kochi', 'Kozhikode', 'Thrissur', 'Kollam', 'Palakkad', 'Alappuzha', 'Kannur', 'Kottayam', 'Ernakulam'],
                                    'Madhya Pradesh' => ['Bhopal', 'Indore', 'Gwalior', 'Jabalpur', 'Ujjain', 'Sagar', 'Ratlam', 'Satna', 'Burhanpur', 'Khandwa'],
                                    'Maharashtra' => ['Mumbai', 'Pune', 'Nagpur', 'Thane', 'Nashik', 'Aurangabad', 'Solapur', 'Kolhapur', 'Navi Mumbai', 'Sangli'],
                                    'Manipur' => ['Imphal', 'Thoubal', 'Bishnupur', 'Churachandpur', 'Ukhrul', 'Tamenglong', 'Senapati'],
                                    'Meghalaya' => ['Shillong', 'Tura', 'Nongstoin', 'Jowai', 'Baghmara', 'Williamnagar', 'Mawkyrwat'],
                                    'Mizoram' => ['Aizawl', 'Lunglei', 'Champhai', 'Serchhip', 'Kolasib', 'Mamit', 'Saitual'],
                                    'Nagaland' => ['Kohima', 'Dimapur', 'Mokokchung', 'Wokha', 'Tuensang', 'Phek', 'Zunheboto'],
                                    'Odisha' => ['Bhubaneswar', 'Cuttack', 'Rourkela', 'Berhampur', 'Sambalpur', 'Balasore', 'Bhadrak', 'Angul', 'Jharsuguda', 'Puri'],
                                    'Punjab' => ['Ludhiana', 'Amritsar', 'Jalandhar', 'Patiala', 'Bathinda', 'Mohali', 'Hoshiarpur', 'Kapurthala', 'Ferozepur', 'Moga'],
                                    'Rajasthan' => ['Jaipur', 'Jodhpur', 'Udaipur', 'Kota', 'Bikaner', 'Ajmer', 'Pilani', 'Bhilwara', 'Alwar', 'Bharatpur'],
                                    'Sikkim' => ['Gangtok', 'Namchi', 'Gyalshing', 'Rabong', 'Soreng', 'Jorethang', 'Mangan'],
                                    'Tamil Nadu' => ['Chennai', 'Coimbatore', 'Madurai', 'Tiruchirappalli', 'Salem', 'Tiruppur', 'Vellore', 'Erode', 'Tirunelveli', 'Thanjavur'],
                                    'Telangana' => ['Hyderabad', 'Warangal', 'Karimnagar', 'Khammam', 'Secunderabad', 'Nizamabad', 'Ramagundam', 'Suryapet', 'Miryalguda', 'Kothakota'],
                                    'Tripura' => ['Agartala', 'Udaipur', 'Dharmanagar', 'Kailasahar', 'Belonia', 'Khowai', 'Sabroom'],
                                    'Uttar Pradesh' => ['Lucknow', 'Kanpur', 'Ghaziabad', 'Agra', 'Varanasi', 'Allahabad', 'Meerut', 'Aligarh', 'Moradabad', 'Saharanpur'],
                                    'Uttarakhand' => ['Dehradun', 'Haridwar', 'Roorkee', 'Haldwani', 'Kashipur', 'Rishikesh', 'Rudrapur', 'Kotdwar', 'Ramnagar'],
                                    'West Bengal' => ['Kolkata', 'Howrah', 'Asansol', 'Siliguri', 'Durgapur', 'Bardhaman', 'Malda', 'Kharagpur', 'Berhampore', 'Baharampur'],
                                    'Delhi' => ['New Delhi', 'North Delhi', 'South Delhi', 'East Delhi', 'West Delhi', 'Central Delhi', 'Old Delhi']
                                ];
                                $cities = $citiesByState[$state] ?? [];
                                foreach ($cities as $c): ?>
                                <option value="<?php echo $c; ?>" <?php echo $city === $c ? 'selected' : ''; ?>><?php echo $c; ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    
                    <div class="form-section">
                        <label for="contact">Contact Information</label>
                        <input type="tel" id="contact" name="contact" required placeholder="Enter phone number" value="<?php echo htmlspecialchars($contact); ?>" pattern="[0-9]+" oninput="this.value = this.value.replace(/[^0-9]/g, '')" style="width: 100%; padding: 0.75rem; border: 1px solid #444; border-radius: 8px; font-size: 1rem; background: #2a2a2a; color: #f0f0f0; transition: border-color 0.2s;">
                    </div>

                    <div class="form-section">
                        <label for="publish">Publish</label>
                        <select id="publish" name="publish">
                            <option value="immediately" <?php echo $publish === 'immediately' ? 'selected' : ''; ?>>Immediately</option>
                            <option value="schedule" <?php echo $publish === 'schedule' ? 'selected' : ''; ?>>Schedule</option>
                        </select>
                    </div>

                    <div class="form-row" id="scheduleFields" style="display: none;">
                        <div class="form-section">
                            <label for="schedule_date">Schedule Date</label>
                            <input type="date" id="schedule_date" name="schedule_date" min="<?php echo date('Y-m-d'); ?>" value="<?php echo htmlspecialchars($schedule_date); ?>">
                        </div>
                        <div class="form-section" id="scheduleTimeFields">
                            <label for="schedule_time">Schedule Time</label>
                            <input type="time" id="schedule_time" name="schedule_time" value="<?php echo htmlspecialchars($schedule_time); ?>">
                        </div>
                    </div>

                    <button type="submit" class="publish-btn">
                        <i class="fas fa-paper-plane"></i>
                        <?php echo $isEditing ? 'Update Post' : ($isPatching ? 'Create Similar Post' : 'Publish Post'); ?>
                    </button>
</form>
<?php endif; ?>
        </div> 
            <div class="sidebar-panel">
                <div class="publish-card">
                    <h3>Publishing Options</h3>
                    <p>Choose when to publish your post. You can publish immediately or schedule it for a later date and time.</p>
                </div>

                <div class="settings-card">
                    <h3>Post Settings</h3>
                    <p>Set the category, price, location, and contact information for your post to reach the right audience.</p>
                </div>

                <div class="tip-card">
                    <h3>Pro Tip</h3>
                    <?php if ($isPatching): ?>
                    <p>When creating a similar post, review all details to ensure they match your new listing. Update the title, price, and contact information as needed.</p>
                    <?php else: ?>
                    <p>Use clear and descriptive titles to attract more viewers. High-quality images can also increase engagement!</p>
                    <a href="#" class="learn-more">Learn More <i class="fas fa-arrow-right"></i></a>
                    <?php endif; ?>
                </div>
            </div>                 
    </div>

    <!-- Sidebar Panel -->
<?php include 'includes/footer.php'; ?>
    <script src="assets/js/main.js?v=<?php echo time(); ?>"></script>
    <<script>
        const citiesByState = {
            'Andhra Pradesh': ['Visakhapatnam', 'Vijayawada', 'Guntur', 'Tirupati', 'Nellore', 'Kakinada', 'Rajahmundry', 'Kadapa', 'Kurnool', 'Anantapur'],
            'Arunachal Pradesh': ['Itanagar', 'Naharlagun', 'Pasighat', 'Tezpur', 'Dibang Valley', 'Roing', 'Ziro', 'Bomdila'],
            'Assam': ['Guwahati', 'Silchar', 'Dibrugarh', 'Jorhat', 'Nagaon', 'Tinsukia', 'Tezpur', 'Bongaigaon'],
            'Bihar': ['Patna', 'Gaya', 'Bhagalpur', 'Muzaffarpur', 'Darbhanga', 'Arrah', 'Begusarai', 'Katihar', 'Munger', 'Purnia'],
            'Chhattisgarh': ['Raipur', 'Bhilai', 'Bilaspur', 'Durg', 'Rajnandgaon', 'Korba', 'Raigarh', 'Mahasamund'],
            'Goa': ['Panaji', 'Margao', 'Vasco da Gama', 'Mapusa', 'Ponda', 'Curchorem', 'Benaulim'],
            'Gujarat': ['Ahmedabad', 'Surat', 'Vadodara', 'Rajkot', 'Bhavnagar', 'Jamnagar', 'Junagadh', 'Gandhidham', 'Anand', 'Bharuch'],
            'Haryana': ['Faridabad', 'Gurgaon', 'Panipat', 'Karnal', 'Rohtak', 'Hisar', 'Sonipat', 'Ambala', 'Yamunanagar', 'Kurukshetra'],
            'Himachal Pradesh': ['Shimla', 'Mandi', 'Solan', 'Kullu', 'Manali', 'Dharamshala', 'Kangra', 'Chamba', 'Bilaspur'],
            'Jharkhand': ['Ranchi', 'Jamshedpur', 'Dhanbad', 'Bokaro Steel City', 'Hazaribagh', 'Deoghar', 'Ramgarh', 'Giridih'],
            'Karnataka': ['Bengaluru', 'Mysore', 'Mangalore', 'Hubli-Dharwad', 'Belgaum', 'Gulbarga', 'Dharwad', 'Udupi', 'Hassan', 'Bellary'],
            'Kerala': ['Thiruvananthapuram', 'Kochi', 'Kozhikode', 'Thrissur', 'Kollam', 'Palakkad', 'Alappuzha', 'Kannur', 'Kottayam', 'Ernakulam'],
            'Madhya Pradesh': ['Bhopal', 'Indore', 'Gwalior', 'Jabalpur', 'Ujjain', 'Sagar', 'Ratlam', 'Satna', 'Burhanpur', 'Khandwa'],
            'Maharashtra': ['Mumbai', 'Pune', 'Nagpur', 'Thane', 'Nashik', 'Aurangabad', 'Solapur', 'Kolhapur', 'Navi Mumbai', 'Sangli'],
            'Manipur': ['Imphal', 'Thoubal', 'Bishnupur', 'Churachandpur', 'Ukhrul', 'Tamenglong', 'Senapati'],
            'Meghalaya': ['Shillong', 'Tura', 'Nongstoin', 'Jowai', 'Baghmara', 'Williamnagar', 'Mawkyrwat'],
            'Mizoram': ['Aizawl', 'Lunglei', 'Champhai', 'Serchhip', 'Kolasib', 'Mamit', 'Saitual'],
            'Nagaland': ['Kohima', 'Dimapur', 'Mokokchung', 'Wokha', 'Tuensang', 'Phek', 'Zunheboto'],
            'Odisha': ['Bhubaneswar', 'Cuttack', 'Rourkela', 'Berhampur', 'Sambalpur', 'Balasore', 'Bhadrak', 'Angul', 'Jharsuguda', 'Puri'],
            'Punjab': ['Ludhiana', 'Amritsar', 'Jalandhar', 'Patiala', 'Bathinda', 'Mohali', 'Hoshiarpur', 'Kapurthala', 'Ferozepur', 'Moga'],
            'Rajasthan': ['Jaipur', 'Jodhpur', 'Udaipur', 'Kota', 'Bikaner', 'Ajmer', 'Pilani', 'Bhilwara', 'Alwar', 'Bharatpur'],
            'Sikkim': ['Gangtok', 'Namchi', 'Gyalshing', 'Rabong', 'Soreng', 'Jorethang', 'Mangan'],
            'Tamil Nadu': ['Chennai', 'Coimbatore', 'Madurai', 'Tiruchirappalli', 'Salem', 'Tiruppur', 'Vellore', 'Erode', 'Tirunelveli', 'Thanjavur'],
            'Telangana': ['Hyderabad', 'Warangal', 'Karimnagar', 'Khammam', 'Secunderabad', 'Nizamabad', 'Ramagundam', 'Suryapet', 'Miryalguda', 'Kothakota'],
            'Tripura': ['Agartala', 'Udaipur', 'Dharmanagar', 'Kailasahar', 'Belonia', 'Khowai', 'Sabroom'],
            'Uttar Pradesh': ['Lucknow', 'Kanpur', 'Ghaziabad', 'Agra', 'Varanasi', 'Allahabad', 'Meerut', 'Aligarh', 'Moradabad', 'Saharanpur'],
            'Uttarakhand': ['Dehradun', 'Haridwar', 'Roorkee', 'Haldwani', 'Kashipur', 'Rishikesh', 'Rudrapur', 'Kotdwar', 'Ramnagar'],
            'West Bengal': ['Kolkata', 'Howrah', 'Asansol', 'Siliguri', 'Durgapur', 'Bardhaman', 'Malda', 'Kharagpur', 'Berhampore', 'Baharampur'],
            'Delhi': ['New Delhi', 'North Delhi', 'South Delhi', 'East Delhi', 'West Delhi', 'Central Delhi', 'Old Delhi']
        };

        function loadCities(state) {
            const citySelect = document.getElementById('city');
            citySelect.innerHTML = '<option value="">Select a city</option>';
            
            if (state && citiesByState[state]) {
                citiesByState[state].forEach(city => {
                    const option = document.createElement('option');
                    option.value = city;
                    option.textContent = city;
                    citySelect.appendChild(option);
                });
            }
        }

        // Schedule fields toggle
        const publishSelect = document.getElementById('publish');
        const scheduleDateFields = document.getElementById('scheduleFields');

        // Initialize schedule fields visibility based on loaded value
        if (publishSelect.value === 'schedule') {
            scheduleDateFields.style.display = 'flex';
        } else {
            scheduleDateFields.style.display = 'none';
        }

        publishSelect.addEventListener('change', function() {
            if (this.value === 'schedule') {
                scheduleDateFields.style.display = 'flex';
            } else {
                scheduleDateFields.style.display = 'none';
            }
        });

        // Simple toolbar functionality
        const toolbarButtons = document.querySelectorAll('.editor-toolbar button');
        const editor = document.getElementById('description');

        toolbarButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                const icon = e.target.closest('button').querySelector('i');
                const iconClass = icon.className;

                if (iconClass.includes('fa-bold')) {
                    wrapText('**', '**');
                } else if (iconClass.includes('fa-italic')) {
                    wrapText('*', '*');
                } else if (iconClass.includes('fa-list-ul')) {
                    addList('unordered');
                } else if (iconClass.includes('fa-list-ol')) {
                    addList('ordered');
                }
            });
        });

        function wrapText(startTag, endTag) {
            const selectionStart = editor.selectionStart;
            const selectionEnd = editor.selectionEnd;
            const text = editor.value;
            
            const before = text.substring(0, selectionStart);
            const selected = text.substring(selectionStart, selectionEnd);
            const after = text.substring(selectionEnd);
            
            editor.value = before + startTag + selected + endTag + after;
            editor.focus();
            editor.setSelectionRange(
                selectionStart + startTag.length,
                selectionEnd + startTag.length
            );
        }

        function addList(type) {
            const selectionStart = editor.selectionStart;
            const selectionEnd = editor.selectionEnd;
            const text = editor.value;
            
            const before = text.substring(0, selectionStart);
            const selected = text.substring(selectionStart, selectionEnd);
            const after = text.substring(selectionEnd);
            
            const lines = selected.split('\n');
            const listLines = lines.map(line => {
                if (type === 'unordered') {
                    return `- ${line}`;
                } else {
                    return `${lines.indexOf(line) + 1}. ${line}`;
                }
            });
            
            editor.value = before + listLines.join('\n') + after;
            editor.focus();
        }
    </script>
</body>
</html>