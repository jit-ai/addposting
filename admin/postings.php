<?php
require_once '../includes/functions.php';
require_once '../includes/Posting.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

$postingModel = new Posting();

// Handle form submission
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if it's an edit request
    if (isset($_POST['edit_posting'])) {
        $id = $_POST['id'];
        $title = sanitize($_POST['title']);
        $description = sanitize($_POST['description']);
        $category = sanitize($_POST['category']);
        $price = (float)$_POST['price'];
        $location = sanitize($_POST['location']);
        $contact = sanitize($_POST['contact']);
        $status = sanitize($_POST['status']);

        if (empty($title)) {
            $errors[] = 'Title is required';
        }

        if (empty($description)) {
            $errors[] = 'Description is required';
        }

        if (empty($category)) {
            $errors[] = 'Category is required';
        }

        if (empty($price) || $price <= 0) {
            $errors[] = 'Price must be greater than 0';
        }

        if (empty($location)) {
            $errors[] = 'Location is required';
        }

        if (empty($contact)) {
            $errors[] = 'Contact information is required';
        }

        if (empty($status)) {
            $errors[] = 'Status is required';
        }

        if (empty($errors)) {
            if ($postingModel->update($id, [
                'title' => $title,
                'description' => $description,
                'category' => $category,
                'price' => $price,
                'location' => $location,
                'contact' => $contact,
                'status' => $status
            ])) {
                $success = 'Posting updated successfully!';
            } else {
                $errors[] = 'Failed to update posting. Please try again.';
            }
        }
    }
}

// Get all postings
$postings = $postingModel->getAll();

// Get user data for each posting
require_once '../includes/User.php';
$userModel = new User();
foreach ($postings as &$posting) {
    $posting['user'] = $userModel->findById($posting['user_id']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Postings - <?php echo APP_NAME; ?></title>
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

        .status-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
        }

        .status-active {
            background: #d4edda;
            color: #155724;
        }

        .status-inactive {
            background: #f8d7da;
            color: #721c24;
        }

        .category-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
            background: #667eea;
            color: white;
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
            max-height: 90vh;
            overflow-y: auto;
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

        .image-preview {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 1rem;
        }

        .image-preview-item {
            width: 80px;
            height: 80px;
            border-radius: 5px;
            overflow: hidden;
            border: 2px solid #e2e8f0;
        }

        .image-preview-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
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
                <li><a href="postings.php" class="active"><i class="fas fa-list"></i> Postings</a></li>
                <li><a href="categories.php"><i class="fas fa-tags"></i> Categories</a></li>
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
            <h2>Postings Management</h2>
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
                <h1>Postings</h1>
                <a href="../addposting.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Posting
                </a>
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

            <div class="admin-content">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Location</th>
                            <th>Seller</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th>Updated At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($postings as $posting): ?>
                            <tr>
                                <td><?php echo $posting['title']; ?></td>
                                <td>
                                    <span class="category-badge"><?php echo $posting['category']; ?></span>
                                </td>
                                <td>₹<?php echo number_format($posting['price'], 2); ?></td>
                                <td><?php echo $posting['location']; ?></td>
                                <td><?php echo $posting['user']['name']; ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $posting['status']; ?>">
                                        <?php echo ucfirst($posting['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($posting['created_at'])); ?></td>
                                <td><?php echo date('M d, Y', strtotime($posting['updated_at'])); ?></td>
                                <td>
                                    <div class="admin-actions">
                                        <button class="btn btn-sm btn-primary edit-posting" data-posting='<?php echo json_encode($posting); ?>'>
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <a href="delete-posting.php?id=<?php echo $posting['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this posting?');">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
                                        <?php if ($posting['status'] === 'active'): ?>
                                            <a href="toggle-posting.php?id=<?php echo $posting['id']; ?>&status=inactive" class="btn btn-sm btn-warning">
                                                <i class="fas fa-times"></i> Deactivate
                                            </a>
                                        <?php else: ?>
                                            <a href="toggle-posting.php?id=<?php echo $posting['id']; ?>&status=active" class="btn btn-sm btn-success">
                                                <i class="fas fa-check"></i> Activate
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
        </div>
    </div>

    <!-- Edit Posting Modal -->
    <div class="modal" id="editPostingModal">
        <div class="modal-content">
            <button class="modal-close" id="closeModal">
                <i class="fas fa-times"></i>
            </button>
            <h3>Edit Posting</h3>
            <form method="POST" action="postings.php">
                <input type="hidden" name="id" id="editPostingId">
                <input type="hidden" name="edit_posting" value="1">
                
                <div class="form-group">
                    <label for="editPostingTitle">Title</label>
                    <input type="text" id="editPostingTitle" name="title" required placeholder="Enter posting title">
                </div>
                
                <div class="form-group">
                    <label for="editPostingCategory">Category</label>
                    <select id="editPostingCategory" name="category" required>
                        <option value="">Select a category</option>
                        <option value="Electronics">Electronics</option>
                        <option value="Furniture">Furniture</option>
                        <option value="Vehicles">Vehicles</option>
                        <option value="Real Estate">Real Estate</option>
                        <option value="Jobs">Jobs</option>
                        <option value="Services">Services</option>
                        <option value="Clothing">Clothing</option>
                        <option value="Books">Books</option>
                        <option value="Sports">Sports</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="editPostingPrice">Price</label>
                    <div class="price-input">
                        <span>$</span>
                        <input type="number" id="editPostingPrice" name="price" step="0.01" required placeholder="0.00">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="editPostingLocation">Location</label>
                    <input type="text" id="editPostingLocation" name="location" required placeholder="Enter location">
                </div>
                
                <div class="form-group">
                    <label for="editPostingContact">Contact Information</label>
                    <input type="text" id="editPostingContact" name="contact" required placeholder="Enter phone number or email">
                </div>
                
                <div class="form-group">
                    <label for="editPostingDescription">Description</label>
                    <textarea id="editPostingDescription" name="description" rows="6" required placeholder="Enter item description"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="editPostingStatus">Status</label>
                    <select id="editPostingStatus" name="status" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-block">Update Posting</button>
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
        // Edit posting modal
        document.addEventListener('DOMContentLoaded', function() {
            const editPostingButtons = document.querySelectorAll('.edit-posting');
            const editPostingModal = document.getElementById('editPostingModal');
            const closeModalButton = document.getElementById('closeModal');
            const editPostingId = document.getElementById('editPostingId');
            const editPostingTitle = document.getElementById('editPostingTitle');
            const editPostingCategory = document.getElementById('editPostingCategory');
            const editPostingPrice = document.getElementById('editPostingPrice');
            const editPostingLocation = document.getElementById('editPostingLocation');
            const editPostingContact = document.getElementById('editPostingContact');
            const editPostingDescription = document.getElementById('editPostingDescription');
            const editPostingStatus = document.getElementById('editPostingStatus');

            editPostingButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const posting = JSON.parse(this.dataset.posting);
                    editPostingId.value = posting.id;
                    editPostingTitle.value = posting.title;
                    editPostingCategory.value = posting.category;
                    editPostingPrice.value = posting.price;
                    editPostingLocation.value = posting.location;
                    editPostingContact.value = posting.contact;
                    editPostingDescription.value = posting.description;
                    editPostingStatus.value = posting.status;
                    editPostingModal.classList.add('active');
                });
            });

            closeModalButton.addEventListener('click', function() {
                editPostingModal.classList.remove('active');
            });

            editPostingModal.addEventListener('click', function(e) {
                if (e.target === editPostingModal) {
                    editPostingModal.classList.remove('active');
                }
            });
        });
    </script>
</body>
</html>