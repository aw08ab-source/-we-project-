<?php
require_once '../includes/database.php';
require_once '../includes/admin_auth.php';
requireAdmin();

// Get statistics for reports
$courseStats = $pdo->query("
    SELECT c.code, c.title, c.instructor, c.seats, 
           COUNT(sr.id) as enrolled,
           ROUND(COUNT(sr.id) * 100.0 / c.seats, 1) as occupancy_rate,
           AVG(CASE WHEN sr.grade = 'A' THEN 4.0
                    WHEN sr.grade = 'A-' THEN 3.7
                    WHEN sr.grade = 'B+' THEN 3.3
                    WHEN sr.grade = 'B' THEN 3.0
                    WHEN sr.grade = 'B-' THEN 2.7
                    WHEN sr.grade = 'C+' THEN 2.3
                    WHEN sr.grade = 'C' THEN 2.0
                    WHEN sr.grade = 'C-' THEN 1.7
                    WHEN sr.grade = 'D' THEN 1.0
                    WHEN sr.grade = 'F' THEN 0.0
                    ELSE NULL END) as avg_gpa
    FROM courses c
    LEFT JOIN student_records sr ON c.code = sr.course_code
    GROUP BY c.code
    ORDER BY enrolled DESC
")->fetchAll(PDO::FETCH_ASSOC);

$userGrowth = $pdo->query("
    SELECT DATE(created_at) as date, COUNT(*) as count
    FROM users
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY DATE(created_at)
    ORDER BY date
")->fetchAll();

$gradeDistribution = $pdo->query("
    SELECT grade, COUNT(*) as count
    FROM student_records
    WHERE grade IS NOT NULL
    GROUP BY grade
    ORDER BY FIELD(grade, 'A', 'A-', 'B+', 'B', 'B-', 'C+', 'C', 'C-', 'D', 'F')
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Admin</title>
    <link rel="stylesheet" href="../style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php include_once '../includes/navbar.php'; ?>
    
    <div class="container mt-4">
        <h1 class="mb-4"><i class="bi bi-graph-up"></i> Reports & Analytics</h1>
        
        <!-- Export Options -->
        <div class="card shadow mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-download"></i> Export Data</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <a href="export.php?type=users" class="btn btn-outline-primary w-100">
                            <i class="bi bi-people"></i> Export Users
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="export.php?type=courses" class="btn btn-outline-primary w-100">
                            <i class="bi bi-book"></i> Export Courses
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="export.php?type=enrollments" class="btn btn-outline-primary w-100">
                            <i class="bi bi-clipboard-data"></i> Export Enrollments
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="export.php?type=all" class="btn btn-primary w-100">
                            <i class="bi bi-database"></i> Export All
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Charts Row -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header">
                        <h5 class="mb-0">Course Occupancy Rates</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="occupancyChart" height="250"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header">
                        <h5 class="mb-0">Grade Distribution</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="gradeChart" height="250"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Course Statistics Table -->
        <div class="card shadow mb-4">
            <div class="card-header">
                <h5 class="mb-0">Course Performance Report</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Course</th>
                                <th>Instructor</th>
                                <th>Seats</th>
                                <th>Enrolled</th>
                                <th>Occupancy</th>
                                <th>Avg GPA</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($courseStats as $course): ?>
                            <tr>
                                <td>
                                    <strong><?php echo $course['code']; ?></strong><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($course['title']); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($course['instructor']); ?></td>
                                <td><?php echo $course['seats']; ?></td>
                                <td><?php echo $course['enrolled']; ?></td>
                                <td>
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar bg-<?php 
                                            echo $course['occupancy_rate'] > 90 ? 'danger' : 
                                                 ($course['occupancy_rate'] > 70 ? 'warning' : 'success');
                                        ?>" 
                                             style="width: <?php echo min($course['occupancy_rate'], 100); ?>%"
                                             role="progressbar">
                                            <?php echo $course['occupancy_rate']; ?>%
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($course['avg_gpa']): ?>
                                        <span class="badge bg-<?php 
                                            echo $course['avg_gpa'] >= 3.5 ? 'success' : 
                                                 ($course['avg_gpa'] >= 2.5 ? 'info' : 
                                                 ($course['avg_gpa'] >= 1.5 ? 'warning' : 'danger'));
                                        ?>">
                                            <?php echo number_format($course['avg_gpa'], 2); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($course['occupancy_rate'] >= 100): ?>
                                        <span class="badge bg-danger">Full</span>
                                    <?php elseif ($course['occupancy_rate'] >= 80): ?>
                                        <span class="badge bg-warning">Almost Full</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Available</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- System Health -->
        <div class="row">
            <div class="col-md-4">
                <div class="card shadow">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0"><i class="bi bi-check-circle"></i> System Health</h6>
                    </div>
                    <div class="card-body">
                        <?php
                        $tables = ['users', 'courses', 'student_records', 'course_enrollments'];
                        $status = [];
                        
                        foreach($tables as $table) {
                            try {
                                $count = $pdo->query("SELECT COUNT(*) as count FROM $table")->fetch()['count'];
                                $status[$table] = ['count' => $count, 'status' => 'healthy'];
                            } catch(Exception $e) {
                                $status[$table] = ['count' => 0, 'status' => 'error'];
                            }
                        }
                        ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach($status as $table => $info): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <?php echo ucfirst(str_replace('_', ' ', $table)); ?>
                                <span class="badge bg-<?php echo $info['status'] == 'healthy' ? 'success' : 'danger'; ?> rounded-pill">
                                    <?php echo $info['count']; ?>
                                </span>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0"><i class="bi bi-calendar"></i> Recent Activity (30 days)</h6>
                    </div>
                    <div class="card-body">
                        <canvas id="activityChart" height="150"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Occupancy Chart
        const occupancyCtx = document.getElementById('occupancyChart').getContext('2d');
        const occupancyChart = new Chart(occupancyCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($courseStats, 'code')); ?>,
                datasets: [{
                    label: 'Occupancy Rate (%)',
                    data: <?php echo json_encode(array_column($courseStats, 'occupancy_rate')); ?>,
                    backgroundColor: <?php echo json_encode(array_map(function($rate) {
                        return $rate > 90 ? '#dc3545' : ($rate > 70 ? '#ffc107' : '#28a745');
                    }, array_column($courseStats, 'occupancy_rate'))); ?>,
                    borderColor: 'rgba(0,0,0,0.1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100
                    }
                }
            }
        });
        
        // Grade Distribution Chart
        const gradeCtx = document.getElementById('gradeChart').getContext('2d');
        const gradeChart = new Chart(gradeCtx, {
            type: 'pie',
            data: {
                labels: <?php echo json_encode(array_column($gradeDistribution, 'grade')); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($gradeDistribution, 'count')); ?>,
                    backgroundColor: [
                        '#28a745', '#20c997', '#17a2b8', '#007bff', '#6f42c1',
                        '#e83e8c', '#fd7e14', '#dc3545', '#6c757d', '#343a40'
                    ]
                }]
            },
            options: {
                responsive: true
            }
        });
        
        // Activity Chart
        const activityCtx = document.getElementById('activityChart').getContext('2d');
        const activityChart = new Chart(activityCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($userGrowth, 'date')); ?>,
                datasets: [{
                    label: 'New Users',
                    data: <?php echo json_encode(array_column($userGrowth, 'count')); ?>,
                    borderColor: '#4e73df',
                    backgroundColor: 'rgba(78, 115, 223, 0.05)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>