<?php
require_once '../includes/database.php';
require_once '../includes/admin_auth.php';
requireAdmin();

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;
$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['create_user'])) {
        // Create new user
        $name = $_POST['name'];
        $user_id = $_POST['user_id'];
        $email = $_POST['email'];
        $role = $_POST['role'];
        $password = $_POST['password'];
        
        try {
            $sql = "INSERT INTO users (id, name, password, email, role) VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt->execute([$user_id, $name, $hashed_password, $email, $role]);
            $message = "User created successfully!";
        } catch(PDOException $e) {
            $error = "Error creating user: " . $e->getMessage();
        }
    } elseif (isset($_POST['update_user'])) {
        // Update user
        $name = $_POST['name'];
        $email = $_POST['email'];
        $role = $_POST['role'];
        
        $sql = "UPDATE users SET name = ?, email = ?, role = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$name, $email, $role, $id]);
        $message = "User updated successfully!";
    } elseif (isset($_POST['delete_user'])) {
        // Delete user
        $sql = "DELETE FROM users WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $message = "User deleted successfully!";
        header("Location: users.php");
        exit();
    }
}

// Fetch user for editing
$user = null;
if ($id && ($action == 'edit' || $action == 'delete')) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users Management - Admin</title>
    <link rel="stylesheet" href="../style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .action-buttons .btn {
            margin-right: 5px;
        }
    </style>
</head>
<body>
    <?php include_once '../includes/navbar.php'; ?>
    
    <div class="container mt-4">
        <!-- Messages -->
        <?php if ($message): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>
                <i class="bi bi-people"></i> Users Management
                <?php if ($action == 'add'): ?>
                    <span class="h4 text-muted"> / Add New User</span>
                <?php elseif ($action == 'edit'): ?>
                    <span class="h4 text-muted"> / Edit User</span>
                <?php endif; ?>
            </h1>
            <a href="index.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Dashboard
            </a>
        </div>
        
        <?php if ($action == 'add' || $action == 'edit'): ?>
            <!-- ADD/EDIT FORM -->
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="mb-0">
                        <?php echo $action == 'add' ? 'Add New User' : 'Edit User: ' . htmlspecialchars($user['name']); ?>
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Full Name *</label>
                                <input type="text" class="form-control" name="name" 
                                       value="<?php echo $action == 'edit' ? htmlspecialchars($user['name']) : ''; ?>" 
                                       required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">User ID *</label>
                                <input type="text" class="form-control" name="user_id" 
                                       value="<?php echo $action == 'edit' ? htmlspecialchars($user['id']) : ''; ?>" 
                                       <?php echo $action == 'edit' ? 'readonly' : 'required'; ?>>
                                <small class="form-text text-muted">Unique identifier (cannot be changed)</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email Address *</label>
                                <input type="email" class="form-control" name="email" 
                                       value="<?php echo $action == 'edit' ? htmlspecialchars($user['email']) : ''; ?>" 
                                       required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Role *</label>
                                <select class="form-select" name="role" required>
                                    <option value="student" <?php echo ($action == 'edit' && $user['role'] == 'student') ? 'selected' : ''; ?>>Student</option>
                                    <option value="teacher" <?php echo ($action == 'edit' && $user['role'] == 'teacher') ? 'selected' : ''; ?>>Teacher</option>
                                    <option value="admin" <?php echo ($action == 'edit' && $user['role'] == 'admin') ? 'selected' : ''; ?>>Administrator</option>
                                </select>
                            </div>
                            <?php if ($action == 'add'): ?>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Password *</label>
                                    <input type="password" class="form-control" name="password" required minlength="6">
                                    <small class="form-text text-muted">Minimum 6 characters</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Confirm Password *</label>
                                    <input type="password" class="form-control" name="confirm_password" required minlength="6">
                                </div>
                            <?php else: ?>
                                <div class="col-12 mb-3">
                                    <div class="alert alert-info">
                                        <i class="bi bi-info-circle"></i> To change password, use the "Reset Password" feature.
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" name="<?php echo $action == 'add' ? 'create_user' : 'update_user'; ?>" 
                                    class="btn btn-primary">
                                <i class="bi bi-save"></i> 
                                <?php echo $action == 'add' ? 'Create User' : 'Update User'; ?>
                            </button>
                            <a href="users.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
            
        <?php elseif ($action == 'delete'): ?>
            <!-- DELETE CONFIRMATION -->
            <div class="card shadow border-danger">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Confirm Deletion</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <h5>Warning: This action cannot be undone!</h5>
                        <p>You are about to delete user: <strong><?php echo htmlspecialchars($user['name']); ?></strong> (ID: <?php echo $user['id']; ?>)</p>
                        <p>This will also delete all associated records (enrollments, grades, etc.).</p>
                    </div>
                    
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label">Type "DELETE" to confirm:</label>
                            <input type="text" class="form-control" name="confirm_text" placeholder="DELETE" required pattern="DELETE">
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" name="delete_user" class="btn btn-danger">
                                <i class="bi bi-trash"></i> Delete User Permanently
                            </button>
                            <a href="users.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
            
        <?php else: ?>
            <!-- USER LIST -->
            <div class="card shadow">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">All Users</h5>
                    <a href="?action=add" class="btn btn-success">
                        <i class="bi bi-person-plus"></i> Add New User
                    </a>
                </div>
                <div class="card-body">
                    <!-- Filters -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <input type="text" class="form-control" id="searchInput" placeholder="Search users...">
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="roleFilter">
                                <option value="">All Roles</option>
                                <option value="student">Students</option>
                                <option value="teacher">Teachers</option>
                                <option value="admin">Admins</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Users Table -->
                    <div class="table-responsive">
                        <table class="table table-hover" id="usersTable">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Joined</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "SELECT * FROM users ORDER BY created_at DESC";
                                $stmt = $pdo->query($sql);
                                $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                
                                foreach($users as $u):
                                ?>
                                <tr>
                                    <td><code><?php echo $u['id']; ?></code></td>
                                    <td><?php echo htmlspecialchars($u['name']); ?></td>
                                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo $u['role'] == 'admin' ? 'danger' : 
                                                 ($u['role'] == 'teacher' ? 'warning' : 'info');
                                        ?>">
                                            <?php echo $u['role']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($u['created_at'])); ?></td>
                                    <td class="action-buttons">
                                        <a href="?action=edit&id=<?php echo $u['id']; ?>" 
                                           class="btn btn-sm btn-outline-primary" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="?action=delete&id=<?php echo $u['id']; ?>" 
                                           class="btn btn-sm btn-outline-danger" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                        <button class="btn btn-sm btn-outline-secondary" 
                                                onclick="resetPassword('<?php echo $u['id']; ?>')" 
                                                title="Reset Password">
                                            <i class="bi bi-key"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Statistics -->
                    <div class="row mt-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h5><?php echo count(array_filter($users, fn($u) => $u['role'] == 'student')); ?></h5>
                                    <p class="mb-0">Students</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-dark">
                                <div class="card-body text-center">
                                    <h5><?php echo count(array_filter($users, fn($u) => $u['role'] == 'teacher')); ?></h5>
                                    <p class="mb-0">Teachers</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-danger text-white">
                                <div class="card-body text-center">
                                    <h5><?php echo count(array_filter($users, fn($u) => $u['role'] == 'admin')); ?></h5>
                                    <p class="mb-0">Admins</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <h5><?php echo count($users); ?></h5>
                                    <p class="mb-0">Total Users</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Filter and search functionality
        document.getElementById('searchInput').addEventListener('input', function() {
            const filter = this.value.toLowerCase();
            const rows = document.querySelectorAll('#usersTable tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        });
        
        document.getElementById('roleFilter').addEventListener('change', function() {
            const role = this.value;
            const rows = document.querySelectorAll('#usersTable tbody tr');
            
            rows.forEach(row => {
                if (!role) {
                    row.style.display = '';
                    return;
                }
                
                const roleCell = row.querySelector('td:nth-child(4)').textContent.toLowerCase();
                row.style.display = roleCell.includes(role) ? '' : 'none';
            });
        });
        
        // Reset password function
        function resetPassword(userId) {
            if (confirm('Reset password for user ' + userId + ' to "password123"? This will send an email notification.')) {
                // In a real app, this would be an AJAX call
                alert('Password reset initiated for ' + userId);
                // window.location.href = 'reset_password.php?id=' + userId;
            }
        }
    </script>
</body>
</html>