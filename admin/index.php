<?php
require_once '../includes/database.php';
require_once '../includes/admin_auth.php';
requireAdmin();

$stats = getAdminStats($pdo);
$recentUsers = $pdo->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
$recentCourses = $pdo->query("SELECT * FROM courses ORDER BY created_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - UniTrack</title>
    <link rel="stylesheet" href="../style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
    /* Fixed Sidebar Styles */
    .sidebar {
        position: fixed;
        top: 56px; /* Height of navbar */
        left: 0;
        bottom: 0;
        z-index: 1000;
        padding: 20px 0 0;
        width: 240px;
        background: #f8f9fa;
        border-right: 1px solid #dee2e6;
        overflow-y: auto;
    }
    
    .main-content {
        margin-left: 240px;
        padding: 20px;
        min-height: calc(100vh - 56px);
    }
    
    /* Fix for mobile */
    @media (max-width: 768px) {
        .sidebar {
            position: static;
            width: 100%;
            height: auto;
            border-right: none;
            border-bottom: 1px solid #dee2e6;
        }
        
        .main-content {
            margin-left: 0;
        }
    }
    
    /* Stat Cards Fix */
    .stat-card {
        border: 1px solid #e3e6f0;
        border-radius: 0.35rem;
        transition: all 0.15s ease;
    }
    
    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    }
    
    /* Fix navbar spacing */
    .navbar {
        padding: 0.5rem 1rem;
    }
    
    /* Fix table display */
    .table-responsive {
        overflow-x: auto;
    }
    
    /* Better badge colors */
    .badge-admin { background-color: #dc3545; }
    .badge-teacher { background-color: #ffc107; color: #212529; }
    .badge-student { background-color: #17a2b8; }
    
    /* Remove any conflicting styles */
    body { 
        padding-top: 0 !important; 
        background-color: #f8f9fc !important;
    }
    </style>
</head>
<body>
    <!-- Top Navbar -->
    <nav class="navbar navbar-dark bg-dark sticky-top">
        <div class="container-fluid">
            <button class="navbar-toggler d-md-none" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu">
                <span class="navbar-toggler-icon"></span>
            </button>
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-speedometer2"></i> Admin Dashboard
            </a>
            <div class="dropdown">
                <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['user_name']); ?>&background=4e73df&color=fff" 
                         alt="Admin" width="32" height="32" class="rounded-circle me-2">
                    <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="../dashboard.php"><i class="bi bi-person-circle"></i> User Dashboard</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="../logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="sidebar-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="index.php">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="users.php">
                                <i class="bi bi-people"></i> Users Management
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="courses.php">
                                <i class="bi bi-book"></i> Courses Management
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="enrollments.php">
                                <i class="bi bi-clipboard-check"></i> Enrollments
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="reports.php">
                                <i class="bi bi-graph-up"></i> Reports
                            </a>
                        </li>
                        <li class="nav-item mt-4">
                            <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                                <span>Quick Actions</span>
                            </h6>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="users.php?action=add">
                                <i class="bi bi-person-plus"></i> Add New User
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="courses.php?action=add">
                                <i class="bi bi-plus-circle"></i> Add New Course
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-md-4 main-content">
                <!-- Stats Cards -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Dashboard Overview</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary">Export</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary">Print</button>
                        </div>
                    </div>
                </div>

                <!-- Stats Row -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stat-card h-100">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Total Users</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php 
                                            $total = 0;
                                            foreach($stats['users_by_role'] as $role) {
                                                $total += $role['role_count'];
                                            }
                                            echo $total;
                                            ?>
                                        </div>
                                        <div class="mt-2 mb-0 text-muted text-xs">
                                            <?php foreach($stats['users_by_role'] as $role): ?>
                                                <span class="badge bg-secondary me-1"><?php echo $role['role'] . ': ' . $role['role_count']; ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-people fa-2x text-primary"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stat-card h-100">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Total Courses</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['total_courses']; ?></div>
                                        <div class="mt-2 mb-0 text-muted text-xs">
                                            <span class="text-success mr-2"><i class="bi bi-arrow-up"></i> Active</span>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-book fa-2x text-success"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stat-card h-100">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                            Total Enrollments</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['total_enrollments']; ?></div>
                                        <div class="mt-2 mb-0 text-muted text-xs">
                                            <span>Active registrations</span>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-clipboard-check fa-2x text-warning"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stat-card h-100">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                            Recent Users (7d)</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['recent_users']; ?></div>
                                        <div class="mt-2 mb-0 text-muted text-xs">
                                            <span class="text-success mr-2"><i class="bi bi-person-plus"></i> New</span>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-person-plus fa-2x text-info"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="row">
                    <div class="col-lg-6 mb-4">
                        <div class="card shadow">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Recent Users</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Name</th>
                                                <th>Role</th>
                                                <th>Joined</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($recentUsers as $user): ?>
                                            <tr>
                                                <td><code><?php echo $user['id']; ?></code></td>
                                                <td><?php echo htmlspecialchars($user['name']); ?></td>
                                                <td><span class="badge bg-<?php echo $user['role'] == 'admin' ? 'danger' : ($user['role'] == 'teacher' ? 'warning' : 'info'); ?>"><?php echo $user['role']; ?></span></td>
                                                <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <a href="users.php" class="btn btn-sm btn-primary">View All Users</a>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6 mb-4">
                        <div class="card shadow">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Recent Courses</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Code</th>
                                                <th>Title</th>
                                                <th>Instructor</th>
                                                <th>Seats</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($recentCourses as $course): ?>
                                            <tr>
                                                <td><strong><?php echo $course['code']; ?></strong></td>
                                                <td><?php echo htmlspecialchars($course['title']); ?></td>
                                                <td><?php echo htmlspecialchars($course['instructor']); ?></td>
                                                <td><?php echo $course['seats']; ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <a href="courses.php" class="btn btn-sm btn-primary">View All Courses</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="row">
                    <div class="col-12">
                        <div class="card shadow">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                            </div>
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-md-3 mb-3">
                                        <a href="users.php?action=add" class="btn btn-success btn-block">
                                            <i class="bi bi-person-plus fa-2x mb-2"></i><br>
                                            Add User
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <a href="courses.php?action=add" class="btn btn-primary btn-block">
                                            <i class="bi bi-plus-circle fa-2x mb-2"></i><br>
                                            Add Course
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <a href="enrollments.php" class="btn btn-warning btn-block">
                                            <i class="bi bi-clipboard-check fa-2x mb-2"></i><br>
                                            Manage Enrollments
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <a href="reports.php" class="btn btn-info btn-block">
                                            <i class="bi bi-graph-up fa-2x mb-2"></i><br>
                                            View Reports
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Enable tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })
    </script>
</body>
</html>