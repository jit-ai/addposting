<?php
require_once '../includes/functions.php';
require_once '../includes/User.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

$userModel = new User();

// Handle form submission
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if it's an edit request
    if (isset($_POST['edit_user'])) {
        $id = $_POST['id'];
        $name = sanitize($_POST['name']);
        $email = sanitize($_POST['email']);
        $role = sanitize($_POST['role']);
        $status = sanitize($_POST['status']);

        if (empty($name)) {
            $errors[] = 'Name is required';
        }

        if (empty($email)) {
            $errors[] = 'Email is required';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format';
        }

        if (empty($role)) {
            $errors[] = 'Role is required';
        }

        if (empty($status)) {
            $errors[] = 'Status is required';
        }

        if (empty($errors)) {
            if ($userModel->update($id, [
                'name' => $name,
                'email' => $email,
                'role' => $role,
                'status' => $status
            ])) {
                $success = 'User updated successfully!';
            } else {
                $errors[] = 'Failed to update user. Please try again.';
            }
        }
    }
}

// Get all users
$users = $userModel->getAllUsers();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users - <?php echo APP_NAME; ?></title>
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

        .role-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
        }

        .role-admin {
            background: #dc3545;
            color: white;
        }

        .role-user {
            background: #28a745;
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
            z-index: 2000;
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
                <li><a href="users.php" class="active"><i class="fas fa-users"></i> Users</a></li>
                <li><a href="postings.php"><i class="fas fa-list"></i> Postings</a></li>
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
            <h2>Users Management</h2>
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
                <h1>Users</h1>
                <a href="add-user.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New User
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
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th>Updated At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo $user['name']; ?></td>
                                <td><?php echo $user['email']; ?></td>
                                <td>
                                    <span class="role-badge role-<?php echo $user['role']; ?>">
                                        <?php echo ucfirst($user['role']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo $user['status']; ?>">
                                        <?php echo ucfirst($user['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                <td><?php echo date('M d, Y', strtotime($user['updated_at'])); ?></td>
                                <td>
                                    <div class="admin-actions">
                                        <button class="btn btn-sm btn-primary edit-user" data-user='<?php echo json_encode($user); ?>'>
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <a href="delete-user.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this user?');">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
                                        <?php if ($user['status'] === 'active'): ?>
                                            <a href="toggle-user.php?id=<?php echo $user['id']; ?>&status=inactive" class="btn btn-sm btn-warning">
                                                <i class="fas fa-times"></i> Deactivate
                                            </a>
                                        <?php else: ?>
                                            <a href="toggle-user.php?id=<?php echo $user['id']; ?>&status=active" class="btn btn-sm btn-success">
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

    <!-- Edit User Modal -->
    <div class="modal" id="editUserModal">
        <div class="modal-content">
            <button class="modal-close" id="closeModal">
                <i class="fas fa-times"></i>
            </button>
            <h3>Edit User</h3>
            <form method="POST" action="users.php">
                <input type="hidden" name="id" id="editUserId">
                <input type="hidden" name="edit_user" value="1">
                
                <div class="form-group">
                    <label for="editUserName">Name</label>
                    <input type="text" id="editUserName" name="name" required placeholder="Enter user name">
                </div>
                
                <div class="form-group">
                    <label for="editUserEmail">Email</label>
                    <input type="email" id="editUserEmail" name="email" required placeholder="Enter user email">
                </div>
                
                <div class="form-group">
                    <label for="editUserRole">Role</label>
                    <select id="editUserRole" name="role" required>
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="editUserStatus">Status</label>
                    <select id="editUserStatus" name="status" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-block">Update User</button>
                </div>
            </form>
        </div>
    <script src="../assets/js/main.js"></script>
    <script>
        // Sidebar Toggle Functionality
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
        // Edit user modal
        document.addEventListener('DOMContentLoaded', function() {
            const editUserButtons = document.querySelectorAll('.edit-user');
            const editUserModal = document.getElementById('editUserModal');
            const closeModalButton = document.getElementById('closeModal');
            const editUserId = document.getElementById('editUserId');
            const editUserName = document.getElementById('editUserName');
            const editUserEmail = document.getElementById('editUserEmail');
            const editUserRole = document.getElementById('editUserRole');
            const editUserStatus = document.getElementById('editUserStatus');

            editUserButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const user = JSON.parse(this.dataset.user);
                    editUserId.value = user.id;
                    editUserName.value = user.name;
                    editUserEmail.value = user.email;
                    editUserRole.value = user.role;
                    editUserStatus.value = user.status;
                    editUserModal.classList.add('active');
                });
            });

            closeModalButton.addEventListener('click', function() {
                editUserModal.classList.remove('active');
            });

            editUserModal.addEventListener('click', function(e) {
                if (e.target === editUserModal) {
                    editUserModal.classList.remove('active');
                }
            });
        });
    </script>
</body>
</html>