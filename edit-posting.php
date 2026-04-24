<?php
require_once 'includes/functions.php';
require_once 'includes/Posting.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

// Check if posting ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    redirect('my-postings.php');
}

$postingModel = new Posting();

// Get posting details
$posting = $postingModel->findById($_GET['id']);

if (!$posting || $posting['user_id'] != $_SESSION['user_id']) {
    redirect('404.php');
}

// Handle form submission
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate input
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $category = sanitize($_POST['category']);
    $price = (float)$_POST['price'];
    $location = sanitize($_POST['location']);
    $contact = sanitize($_POST['contact']);
    $status = sanitize($_POST['status']);
    
    // Logic to handle existing images vs removed images
    $images = [];
    if (!empty($posting['images'])) {
        $existingImages = explode(',', $posting['images']);
        $removedIndices = isset($_POST['remove_images']) ? $_POST['remove_images'] : [];
        
        foreach ($existingImages as $index => $imgName) {
            if (!in_array($index, $removedIndices)) {
                $images[] = $imgName;
            }
        }
    }

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

    // Price is optional
    if (!empty($price) && $price < 0) {
        $errors[] = 'Price cannot be negative';
    }

    if (empty($location)) {
        $errors[] = 'Location is required';
    }

    if (empty($contact)) {
        $errors[] = 'Contact information is required';
    }

    // Handle new image uploads
    if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
        foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
            if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
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
    }

    // If no errors, update posting
    if (empty($errors)) {
        $updateData = [
            'title' => $title,
            'description' => $description,
            'category' => $category,
            'price' => $price,
            'location' => $location,
            'contact' => $contact,
            'status' => $status,
            'images' => implode(',', $images),
            'scheduled_at' => $posting['scheduled_at'] // Preserve scheduling if exists
        ];

        if ($postingModel->update($_GET['id'], $updateData)) {
            $success = 'Posting updated successfully!';
            // Refresh posting data
            $posting = $postingModel->findById($_GET['id']);
        } else {
            $errors[] = 'Failed to update posting. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Posting - <?php echo APP_NAME; ?></title>
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
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .sidebar-panel {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .tip-card {
            background: linear-gradient(135deg, #1f2937 0%, #374151 100%);
            color: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .form-section {
            margin-bottom: 32px;
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
        .form-section textarea,
        .form-section select {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.2s;
        }

        .form-section textarea {
            min-height: 200px;
            resize: vertical;
            font-family: inherit;
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
        }

        .featured-image {
            border: 2px dashed #d1d5db;
            border-radius: 8px;
            padding: 40px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
            margin-top: 10px;
        }

        .editor-toolbar {
            display: flex;
            gap: 4px;
            padding: 8px 16px;
            background: #f9fafb;
            border: 2px solid #e5e7eb;
            border-bottom: none;
            border-radius: 8px 8px 0 0;
        }

        .editor-toolbar button {
            background: none;
            border: none;
            padding: 6px 8px;
            cursor: pointer;
            border-radius: 4px;
        }

        /* Existing Image Styles */
        .existing-images {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .existing-image-item {
            position: relative;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            aspect-ratio: 1;
        }
        
        .existing-image-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .remove-btn {
            position: absolute;
            top: 5px;
            right: 5px;
            background: rgba(220, 53, 69, 0.9);
            color: white;
            border: none;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background 0.2s;
        }
        
        .remove-btn:hover {
            background: #c82333;
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
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container">
            <div class="logo">
                <i class="fas fa-store"></i>
                <h1><?php echo APP_NAME; ?></h1>
            </div>
            <button class="hamburger" aria-label="Toggle menu" aria-expanded="false">
                <i class="fas fa-bars"></i>
            </button>
            <nav>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="addposting.php">Add Posting</a></li>
                    <?php if (isLoggedIn()): ?>
                        <li class="dropdown">
                            <a href="#"><i class="fas fa-user"></i> My Account</a>
                            <div class="dropdown-content">
                                <a href="dashboard.php">Dashboard</a>
                                <a href="my-postings.php">My Postings</a>
                                <a href="profile.php">Profile</a>
                                <a href="logout.php">Logout</a>
                            </div>
                        </li>
                    <?php else: ?>
                        <li><a href="login.php">Login</a></li>
                        <li><a href="register.php">Register</a></li>
                    <?php endif; ?>
                    <?php if (isAdmin()): ?>
                        <li><a href="admin/dashboard.php" class="btn btn-danger">Admin Dashboard</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>
    
    <!-- Mobile Navigation -->
    <div class="mobile-nav-overlay"></div>
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
        <?php if (isLoggedIn()): ?>
        <div class="mobile-nav-user">
            <i class="fas fa-user-circle"></i> My Account
        </div>
        <?php endif; ?>
        <ul>
            <li><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
            <li><a href="addposting.php"><i class="fas fa-plus-circle"></i> Add Posting</a></li>
            <?php if (isLoggedIn()): ?>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle"><i class="fas fa-user"></i> My Account</a>
                    <div class="dropdown-content">
                        <a href="dashboard.php">Dashboard</a>
                        <a href="my-postings.php">My Postings</a>
                        <a href="profile.php">Profile</a>
                        <a href="logout.php">Logout</a>
                    </div>
                </li>
            <?php else: ?>
                <li><a href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a></li>
                <li><a href="register.php"><i class="fas fa-user-plus"></i> Register</a></li>
            <?php endif; ?>
            <?php if (isAdmin()): ?>
                <li><a href="admin/dashboard.php"><i class="fas fa-cog"></i> Admin Dashboard</a></li>
            <?php endif; ?>
        </ul>
    </div>

    <!-- Edit Posting -->
    <div class="post-create-container">
        <div class="editor-panel">
            <h1 style="font-size: 28px; font-weight: 700; margin-bottom: 12px;">Edit Posting</h1>
            <p style="color: #6b7280; margin-bottom: 32px;">Update your listing details below.</p>

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
                        <p><a href="my-postings.php" class="btn btn-primary" style="margin-top: 10px; display: inline-block;">View My Postings</a></p>
                    </div>
                <?php endif; ?>

                <form method="POST" action="edit-posting.php?id=<?php echo $_GET['id']; ?>" enctype="multipart/form-data">
                    <div class="form-section">
                        <label for="title">Title</label>
                        <input type="text" id="title" name="title" required placeholder="Enter item title" value="<?php echo htmlspecialchars(isset($_POST['title']) ? $_POST['title'] : $posting['title']); ?>">
                    </div>
                    
                    <div class="form-section">
                        <label>Existing Images</label>
                        <?php if (!empty($posting['images'])): ?>
                            <div class="existing-images">
                                <?php foreach (explode(',', $posting['images']) as $key => $image): ?>
                                    <div class="existing-image-item">
                                        <img src="uploads/postings/<?php echo $image; ?>" alt="Image <?php echo $key + 1; ?>">
                                        <button type="button" class="remove-btn" data-index="<?php echo $key; ?>" title="Remove Image">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p style="color: #6b7280; font-style: italic;">No existing images</p>
                        <?php endif; ?>
                    </div>

                    <div class="form-section">
                        <label for="images">Add New Images</label>
                        <div class="featured-image">
                            <input type="file" id="images" name="images[]" multiple accept="image/*" style="display: none;">
                            <label for="images" style="cursor: pointer;">
                                <i class="fas fa-cloud-upload-alt" style="font-size: 48px; color: #9ca3af; margin-bottom: 12px;"></i>
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
                        <textarea id="description" name="description" required placeholder="Enter item description"><?php echo htmlspecialchars(isset($_POST['description']) ? $_POST['description'] : $posting['description']); ?></textarea>
                    </div>

                    <div class="form-section">
                        <label for="category">Category</label>
                        <select id="category" name="category" required>
                            <option value="">Select a category</option>
                            <?php
                            $cats = ['Electronics', 'Furniture', 'Vehicles', 'Real Estate', 'Jobs', 'Services', 'Clothing', 'Books', 'Sports', 'Other'];
                            $currentCat = isset($_POST['category']) ? $_POST['category'] : $posting['category'];
                            foreach($cats as $cat) {
                                $selected = $currentCat === $cat ? 'selected' : '';
                                echo "<option value=\"$cat\" $selected>$cat</option>";
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div class="form-section">
                        <label for="price">Price (₹)</label>
                        <input type="number" id="price" name="price" step="0.01" min="0" placeholder="Enter price" value="<?php echo isset($_POST['price']) ? $_POST['price'] : $posting['price']; ?>">
                    </div>
                    
                    <div class="form-section">
                        <label for="location">Location</label>
                        <input type="text" id="location" name="location" required placeholder="Enter location" value="<?php echo htmlspecialchars(isset($_POST['location']) ? $_POST['location'] : $posting['location']); ?>">
                    </div>
                    
                    <div class="form-section">
                        <label for="contact">Contact Information</label>
                        <input type="text" id="contact" name="contact" required placeholder="Enter phone number or email" value="<?php echo htmlspecialchars(isset($_POST['contact']) ? $_POST['contact'] : $posting['contact']); ?>">
                    </div>
                    
                    <div class="form-section">
                        <label for="status">Status</label>
                        <select id="status" name="status" required>
                            <option value="active" <?php echo (isset($_POST['status']) ? $_POST['status'] : $posting['status']) === 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo (isset($_POST['status']) ? $_POST['status'] : $posting['status']) === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="publish-btn">
                        <i class="fas fa-paper-plane"></i>
                        Update Post
                    </button>
                </form>
        </div>

        <!-- Sidebar -->
        <div class="sidebar-panel">
            <div class="tip-card">
                <h3><i class="fas fa-lightbulb"></i> Pro Tip</h3>
                <p>Keeping your posting status 'Active' ensures it appears in search results. Deactivate it if the item is no longer available.</p>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p>&copy; 2024 <?php echo APP_NAME; ?>. All rights reserved.</p>
        </div>
    </footer>

    <script src="assets/js/main.js?v=<?php echo time(); ?>"></script>
    <script>
        // Image preview and removal
        document.addEventListener('DOMContentLoaded', function() {
            const removeBtns = document.querySelectorAll('.remove-btn');
            
            removeBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    if(!confirm('Are you sure you want to remove this image? It will be deleted upon saving.')) return;
                    const index = this.dataset.index;
                    const imagePreviewItem = this.closest('.existing-image-item');
                    
                    // Add hidden input to indicate image should be removed
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'remove_images[]';
                    input.value = index;
                    document.querySelector('form').appendChild(input);
                    
                    // Remove preview item
                    imagePreviewItem.remove();
                });
            });
        });
    </script>
</body>
</html>