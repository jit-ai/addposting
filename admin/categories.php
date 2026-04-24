<?php
require_once '../includes/functions.php';
require_once '../includes/database.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

$db = new Database();
$conn = $db->getConnection();

// Handle form submission
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if it's an add request
    if (isset($_POST['add_category'])) {
        $name = sanitize($_POST['name']);
        $description = sanitize($_POST['description']);
        $status = sanitize($_POST['status']);

        $image = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../uploads/categories/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $fileName = time() . '_' . basename($_FILES['image']['name']);
            $targetPath = $uploadDir . $fileName;
            
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $fileType = $_FILES['image']['type'];
            
            if (in_array($fileType, $allowedTypes)) {
                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                    $image = 'uploads/categories/' . $fileName;
                } else {
                    $errors[] = 'Failed to upload image.';
                }
            } else {
                $errors[] = 'Invalid image type. Allowed: JPEG, PNG, GIF, WebP';
            }
        }

        if (empty($name)) {
            $errors[] = 'Category name is required';
        }

        if (empty($status)) {
            $status = 'active';
        }

        if (empty($errors)) {
            $sql = "INSERT INTO categories (name, description, image, status, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $name, $description, $image, $status);
            
            if ($stmt->execute()) {
                $success = 'Category added successfully!';
                $_POST = []; // Clear form
            } else {
                $errors[] = 'Failed to add category. Please try again.';
            }
        }
    }
    
    // Check if it's an edit request
    if (isset($_POST['edit_category'])) {
        $id = $_POST['id'];
        $name = sanitize($_POST['name']);
        $description = sanitize($_POST['description']);
        $status = sanitize($_POST['status']);

        // Get current image
        $currentCategory = $conn->query("SELECT image FROM categories WHERE id = $id")->fetch_assoc();
        $image = $currentCategory['image'];

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../uploads/categories/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $fileName = time() . '_' . basename($_FILES['image']['name']);
            $targetPath = $uploadDir . $fileName;
            
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $fileType = $_FILES['image']['type'];
            
            if (in_array($fileType, $allowedTypes)) {
                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                    // Delete old image if exists
                    if ($image && file_exists('../' . $image)) {
                        unlink('../' . $image);
                    }
                    $image = 'uploads/categories/' . $fileName;
                } else {
                    $errors[] = 'Failed to upload image.';
                }
            } else {
                $errors[] = 'Invalid image type. Allowed: JPEG, PNG, GIF, WebP';
            }
        }

        if (empty($name)) {
            $errors[] = 'Category name is required';
        }

        if (empty($status)) {
            $status = 'active';
        }

        if (empty($errors)) {
            $sql = "UPDATE categories SET name = ?, description = ?, image = ?, status = ?, updated_at = NOW() WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssi", $name, $description, $image, $status, $id);
            
            if ($stmt->execute()) {
                $success = 'Category updated successfully!';
            } else {
                $errors[] = 'Failed to update category. Please try again.';
            }
        }
    }
}

// Get all categories
$categories = $conn->query("SELECT * FROM categories ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .page-header h1 {
            font-size: 2rem;
            color: #2d3748;
            margin: 0;
        }

        .admin-content {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .admin-table {
            width: 100%;
            border-collapse: collapse;
        }

        .admin-table th,
        .admin-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        .admin-table th {
            background: #f8f9fa;
            font-weight: bold;
            color: #2d3748;
        }

        .admin-table tbody tr:hover {
            background: #f8f9fa;
        }

        .admin-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            max-width: 600px;
            width: 90%;
            position: relative;
        }

        .modal-content h3 {
            color: #2d3748;
            margin-bottom: 1.5rem;
        }

        .modal-close {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #718096;
        }

        .modal-close:hover {
            color: #2d3748;
        }

        .add-form {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .add-form h3 {
            color: #2d3748;
            margin-bottom: 1.5rem;
        }

        @media (max-width: 768px) {
            .admin-table {
                font-size: 0.875rem;
            }

            .admin-table th,
            .admin-table td {
                padding: 0.75rem;
            }

            .admin-actions {
                flex-direction: column;
            }

            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="admin-sidebar">
        <div class="logo">
            <i class="fas fa-crown"></i>
            <h1>Admin Panel</h1>
        </div>
        <nav>
            <ul>
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="users.php"><i class="fas fa-users"></i> Users</a></li>
                <li><a href="postings.php"><i class="fas fa-list"></i> Postings</a></li>
                <li><a href="categories.php" class="active"><i class="fas fa-tags"></i> Categories</a></li>
                <li><a href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </nav>
    </div>

    <!-- Header -->
    <div class="admin-header">
        <div style="display: flex; align-items: center;">
            <button class="sidebar-toggle" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
            <h2>Categories Management</h2>
        </div>
        <div class="user-info">
            <div class="avatar"><?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?></div>
            <span><?php echo $_SESSION['user_name']; ?></span>
        </div>
    </div>

    <!-- Main Content -->
    <div class="admin-main">
        <div class="main-content">
            <div class="page-header">
                <h1>Categories</h1>
                <button class="btn btn-primary" id="addCategoryBtn">
                    <i class="fas fa-plus"></i> Add New Category
                </button>
            </div>

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
                </div>
            <?php endif; ?>

            <!-- Add Category Form -->
            <div class="add-form" id="addCategoryForm">
                <h3>Add New Category</h3>
                <form method="POST" action="categories.php" enctype="multipart/form-data">
                    <input type="hidden" name="add_category" value="1">
                    
                    <div class="form-group">
                        <label for="categoryName">Category Name</label>
                        <input type="text" id="categoryName" name="name" required placeholder="Enter category name" value="<?php echo isset($_POST['name']) ? $_POST['name'] : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="categoryDescription">Description</label>
                        <textarea id="categoryDescription" name="description" rows="4" placeholder="Enter category description"><?php echo isset($_POST['description']) ? $_POST['description'] : ''; ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="categoryImage">Category Image</label>
                        <input type="file" id="categoryImage" name="image" accept="image/*">
                        <small>Allowed: JPEG, PNG, GIF, WebP</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="categoryStatus">Status</label>
                        <select id="categoryStatus" name="status" required>
                            <option value="active" selected>Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Add Category</button>
                        <button type="button" class="btn btn-secondary" id="cancelAddBtn">Cancel</button>
                    </div>
                </form>
            </div>

            <!-- Categories List -->
            <div class="admin-content">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th>Updated At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $category): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($category['image'])): ?>
                                        <img src="../<?php echo $category['image']; ?>" alt="<?php echo $category['name']; ?>" style="width: 60px; height: 60px; object-fit: cover; border-radius: 5px;">
                                    <?php else: ?>
                                        <span style="color: #718096;">No Image</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $category['name']; ?></td>
                                <td><?php echo $category['description']; ?></td>
                                <td><span class="status-badge status-<?php echo $category['status']; ?>"><?php echo ucfirst($category['status']); ?></span></td>
                                <td><?php echo date('M d, Y', strtotime($category['created_at'])); ?></td>
                                <td><?php echo date('M d, Y', strtotime($category['updated_at'])); ?></td>
                                <td>
                                    <div class="admin-actions">
                                        <button class="btn btn-sm btn-primary edit-category" data-category='<?php echo json_encode($category); ?>'>
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <a href="delete-category.php?id=<?php echo $category['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this category?');">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Edit Category Modal -->
    <div class="modal" id="editCategoryModal">
        <div class="modal-content">
            <button class="modal-close" id="closeModal">
                <i class="fas fa-times"></i>
            </button>
            <h3>Edit Category</h3>
            <form method="POST" action="categories.php" enctype="multipart/form-data">
                <input type="hidden" name="id" id="editCategoryId">
                <input type="hidden" name="edit_category" value="1">
                
                <div class="form-group">
                    <label for="editCategoryName">Category Name</label>
                    <input type="text" id="editCategoryName" name="name" required placeholder="Enter category name">
                </div>
                
                <div class="form-group">
                    <label for="editCategoryDescription">Description</label>
                    <textarea id="editCategoryDescription" name="description" rows="4" placeholder="Enter category description"></textarea>
                </div>

                <div class="form-group">
                    <label for="editCategoryImage">Category Image</label>
                    <input type="file" id="editCategoryImage" name="image" accept="image/*">
                    <small>Allowed: JPEG, PNG, GIF, WebP</small>
                    <div id="currentImageContainer" style="margin-top: 10px;">
                        <img id="currentImage" src="" alt="Current Image" style="width: 100px; height: 100px; object-fit: cover; border-radius: 5px; display: none;">
                        <p id="noImageText" style="color: #718096; display: none;">No image</p>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="editCategoryStatus">Status</label>
                    <select id="editCategoryStatus" name="status" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Update Category</button>
                    <button type="button" class="btn btn-secondary" id="cancelEditBtn">Cancel</button>
                </div>
            </form>
        </div>
    <script src="../assets/js/main.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.getElementById('sidebarToggle');
            const adminSidebar = document.querySelector('.admin-sidebar');
            
            if (sidebarToggle && adminSidebar) {
                sidebarToggle.addEventListener('click', function() {
                    adminSidebar.classList.toggle('collapsed');
                    document.body.classList.toggle('sidebar-collapsed');
                    if (window.innerWidth <= 768) {
                        adminSidebar.classList.toggle('mobile-open');
                    }
                    // Update icon
                    const icon = sidebarToggle.querySelector('i');
                    if (adminSidebar.classList.contains('collapsed')) {
                        icon.classList.remove('fa-bars');
                        icon.classList.add('fa-chevron-left');
                    } else {
                        icon.classList.remove('fa-chevron-left');
                        icon.classList.add('fa-bars');
                    }
                });
            }
            
            document.addEventListener('click', function(e) {
                const adminSidebar = document.querySelector('.admin-sidebar');
                if (window.innerWidth <= 768 && adminSidebar && adminSidebar.classList.contains('mobile-open') && !adminSidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
                    adminSidebar.classList.remove('mobile-open');
                }
            });
        });
    </script>
    <script>
        // Category management functionality
        document.addEventListener('DOMContentLoaded', function() {
            const addCategoryBtn = document.getElementById('addCategoryBtn');
            const addCategoryForm = document.getElementById('addCategoryForm');
            const cancelAddBtn = document.getElementById('cancelAddBtn');
            const editCategoryButtons = document.querySelectorAll('.edit-category');
            const editCategoryModal = document.getElementById('editCategoryModal');
            const closeModalButton = document.getElementById('closeModal');
            const cancelEditBtn = document.getElementById('cancelEditBtn');
            const editCategoryId = document.getElementById('editCategoryId');
            const editCategoryName = document.getElementById('editCategoryName');
            const editCategoryDescription = document.getElementById('editCategoryDescription');
            const editCategoryStatus = document.getElementById('editCategoryStatus');
            const currentImage = document.getElementById('currentImage');
            const currentImageContainer = document.getElementById('currentImageContainer');
            const noImageText = document.getElementById('noImageText');

            // Hide add form by default
            addCategoryForm.style.display = 'none';

            // Show add form
            addCategoryBtn.addEventListener('click', function() {
                addCategoryForm.style.display = 'block';
            });

            // Cancel add form
            cancelAddBtn.addEventListener('click', function() {
                addCategoryForm.style.display = 'none';
            });

            // Edit category
            editCategoryButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const category = JSON.parse(this.dataset.category);
                    editCategoryId.value = category.id;
                    editCategoryName.value = category.name;
                    editCategoryDescription.value = category.description || '';
                    editCategoryStatus.value = category.status || 'active';
                    
                    // Show current image
                    if (category.image) {
                        currentImage.src = '../' + category.image;
                        currentImage.style.display = 'block';
                        noImageText.style.display = 'none';
                    } else {
                        currentImage.style.display = 'none';
                        noImageText.style.display = 'block';
                    }
                    
                    editCategoryModal.classList.add('active');
                });
            });

            // Close modal
            closeModalButton.addEventListener('click', function() {
                editCategoryModal.classList.remove('active');
            });

            cancelEditBtn.addEventListener('click', function() {
                editCategoryModal.classList.remove('active');
            });

            editCategoryModal.addEventListener('click', function(e) {
                if (e.target === editCategoryModal) {
                    editCategoryModal.classList.remove('active');
                }
            });
        });
    </script>
</body>
</html>