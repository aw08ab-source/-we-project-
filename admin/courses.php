<?php
require_once '../includes/database.php';
require_once '../includes/admin_auth.php';
requireAdmin();

$action = $_GET['action'] ?? 'list';
$code = $_GET['code'] ?? null;
$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['create_course'])) {
        $code = $_POST['code'];
        $title = $_POST['title'];
        $instructor = $_POST['instructor'];
        $credits = $_POST['credits'];
        $seats = $_POST['seats'];
        
        try {
            $sql = "INSERT INTO courses (code, title, instructor, credits, seats) VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$code, $title, $instructor, $credits, $seats]);
            $message = "Course created successfully!";
        } catch(PDOException $e) {
            $error = "Error creating course: " . $e->getMessage();
        }
    } elseif (isset($_POST['update_course'])) {
        $title = $_POST['title'];
        $instructor = $_POST['instructor'];
        $credits = $_POST['credits'];
        $seats = $_POST['seats'];
        
        $sql = "UPDATE courses SET title = ?, instructor = ?, credits = ?, seats = ? WHERE code = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$title, $instructor, $credits, $seats, $code]);
        $message = "Course updated successfully!";
    } elseif (isset($_POST['delete_course'])) {
        $sql = "DELETE FROM courses WHERE code = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$code]);
        $message = "Course deleted successfully!";
        header("Location: courses.php");
        exit();
    }
}

// Fetch course for editing
$course = null;
if ($code && ($action == 'edit' || $action == 'delete')) {
    $stmt = $pdo->prepare("SELECT * FROM courses WHERE code = ?");
    $stmt->execute([$code]);
    $course = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Get instructors from existing courses
$instructors = $pdo->query("SELECT DISTINCT instructor FROM courses ORDER BY instructor")->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courses Management - Admin</title>
    <link rel="stylesheet" href="../style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
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
            <h1><i class="bi bi-book"></i> Courses Management</h1>
            <a href="index.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Dashboard
            </a>
        </div>
        
        <?php if ($action == 'add' || $action == 'edit'): ?>
            <!-- ADD/EDIT FORM -->
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card shadow">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <?php echo $action == 'add' ? 'Add New Course' : 'Edit Course: ' . htmlspecialchars($course['code']); ?>
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Course Code *</label>
                                        <input type="text" class="form-control" name="code" 
                                               value="<?php echo $action == 'edit' ? htmlspecialchars($course['code']) : ''; ?>" 
                                               <?php echo $action == 'edit' ? 'readonly' : 'required'; ?>
                                               pattern="[A-Z]{3,4}\d{4}" 
                                               title="Format: ABC1234 (3-4 letters followed by 4 digits)">
                                        <small class="form-text text-muted">Format: COMP3700</small>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Course Title *</label>
                                        <input type="text" class="form-control" name="title" 
                                               value="<?php echo $action == 'edit' ? htmlspecialchars($course['title']) : ''; ?>" 
                                               required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Instructor *</label>
                                        <input type="text" class="form-control" name="instructor" 
                                               value="<?php echo $action == 'edit' ? htmlspecialchars($course['instructor']) : ''; ?>" 
                                               required list="instructorsList">
                                        <datalist id="instructorsList">
                                            <?php foreach($instructors as $inst): ?>
                                                <option value="<?php echo htmlspecialchars($inst); ?>">
                                            <?php endforeach; ?>
                                        </datalist>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Credits *</label>
                                        <input type="number" class="form-control" name="credits" 
                                               value="<?php echo $action == 'edit' ? $course['credits'] : '3'; ?>" 
                                               min="1" max="5" required>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Available Seats *</label>
                                        <input type="number" class="form-control" name="seats" 
                                               value="<?php echo $action == 'edit' ? $course['seats'] : '30'; ?>" 
                                               min="1" max="200" required>
                                    </div>
                                </div>
                                
                                <div class="mt-4">
                                    <button type="submit" name="<?php echo $action == 'add' ? 'create_course' : 'update_course'; ?>" 
                                            class="btn btn-primary">
                                        <i class="bi bi-save"></i> 
                                        <?php echo $action == 'add' ? 'Create Course' : 'Update Course'; ?>
                                    </button>
                                    <a href="courses.php" class="btn btn-secondary">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            
        <?php elseif ($action == 'delete'): ?>
            <!-- DELETE CONFIRMATION -->
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card shadow border-danger">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Confirm Deletion</h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-warning">
                                <h5>Warning!</h5>
                                <p>Delete course: <strong><?php echo htmlspecialchars($course['title']); ?></strong> (<?php echo $course['code']; ?>)</p>
                                <p>This will also delete all enrollments and grades for this course.</p>
                            </div>
                            
                            <form method="POST" action="">
                                <div class="mb-3">
                                    <label class="form-label">Type course code to confirm:</label>
                                    <input type="text" class="form-control" name="confirm_text" 
                                           placeholder="<?php echo $course['code']; ?>" required 
                                           pattern="<?php echo $course['code']; ?>">
                                </div>
                                
                                <div class="mt-4">
                                    <button type="submit" name="delete_course" class="btn btn-danger">
                                        <i class="bi bi-trash"></i> Delete Course
                                    </button>
                                    <a href="courses.php" class="btn btn-secondary">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            
        <?php else: ?>
            <!-- COURSES LIST -->
            <div class="card shadow">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">All Courses</h5>
                    <div>
                        <a href="?action=add" class="btn btn-success">
                            <i class="bi bi-plus-circle"></i> Add New Course
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Statistics -->
                    <?php
                    $totalCourses = $pdo->query("SELECT COUNT(*) as count FROM courses")->fetch()['count'];
                    $totalSeats = $pdo->query("SELECT SUM(seats) as total FROM courses")->fetch()['total'];
                    $totalEnrollments = $pdo->query("SELECT COUNT(*) as count FROM student_records")->fetch()['count'];
                    $occupancyRate = $totalSeats > 0 ? round(($totalEnrollments / $totalSeats) * 100, 1) : 0;
                    ?>
                    
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center py-3">
                                    <h4><?php echo $totalCourses; ?></h4>
                                    <p class="mb-0">Total Courses</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center py-3">
                                    <h4><?php echo $totalSeats; ?></h4>
                                    <p class="mb-0">Total Seats</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-dark">
                                <div class="card-body text-center py-3">
                                    <h4><?php echo $totalEnrollments; ?></h4>
                                    <p class="mb-0">Enrollments</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center py-3">
                                    <h4><?php echo $occupancyRate; ?>%</h4>
                                    <p class="mb-0">Occupancy Rate</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Courses Table -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Code</th>
                                    <th>Title</th>
                                    <th>Instructor</th>
                                    <th>Credits</th>
                                    <th>Seats</th>
                                    <th>Enrolled</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "SELECT c.*, 
                                        COUNT(sr.id) as enrolled,
                                        (c.seats - COUNT(sr.id)) as available
                                        FROM courses c
                                        LEFT JOIN student_records sr ON c.code = sr.course_code
                                        GROUP BY c.code
                                        ORDER BY c.code";
                                $stmt = $pdo->query($sql);
                                $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                
                                foreach($courses as $c):
                                    $available = $c['available'];
                                    $status = $available > 0 ? 'Available' : 'Full';
                                    $statusClass = $available > 10 ? 'success' : ($available > 0 ? 'warning' : 'danger');
                                ?>
                                <tr>
                                    <td><strong><?php echo $c['code']; ?></strong></td>
                                    <td><?php echo htmlspecialchars($c['title']); ?></td>
                                    <td><?php echo htmlspecialchars($c['instructor']); ?></td>
                                    <td><?php echo $c['credits']; ?></td>
                                    <td><?php echo $c['seats']; ?></td>
                                    <td><?php echo $c['enrolled']; ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $statusClass; ?>">
                                            <?php echo $status; ?> (<?php echo $available; ?>)
                                        </span>
                                    </td>
                                    <td>
                                        <a href="?action=edit&code=<?php echo $c['code']; ?>" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="?action=delete&code=<?php echo $c['code']; ?>" 
                                           class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                        <a href="enrollments.php?course=<?php echo $c['code']; ?>" 
                                           class="btn btn-sm btn-outline-info">
                                            <i class="bi bi-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>