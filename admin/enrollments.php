<?php
require_once '../includes/database.php';
require_once '../includes/admin_auth.php';
requireAdmin();

// Handle enrollment actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_enrollment'])) {
        $student_id = $_POST['student_id'];
        $course_code = $_POST['course_code'];
        $grade = $_POST['grade'] ?? null;
        
        try {
            $sql = "INSERT INTO student_records (student_id, course_code, grade) VALUES (?, ?, ?)
                    ON DUPLICATE KEY UPDATE grade = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$student_id, $course_code, $grade, $grade]);
            $_SESSION['message'] = "Enrollment added/updated successfully!";
        } catch(PDOException $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
        }
    } elseif (isset($_POST['remove_enrollment'])) {
        $enrollment_id = $_POST['enrollment_id'];
        $stmt = $pdo->prepare("DELETE FROM student_records WHERE id = ?");
        $stmt->execute([$enrollment_id]);
        $_SESSION['message'] = "Enrollment removed successfully!";
    }
    
    header("Location: enrollments.php");
    exit();
}

// Get all enrollments with details
$sql = "SELECT sr.*, u.name as student_name, u.role as student_role, 
               c.title as course_title, c.instructor
        FROM student_records sr
        JOIN users u ON sr.student_id = u.id
        JOIN courses c ON sr.course_code = c.code
        ORDER BY sr.recorded_at DESC";
$enrollments = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

// Get students and courses for dropdowns
$students = $pdo->query("SELECT id, name FROM users WHERE role = 'student' ORDER BY name")->fetchAll();
$courses = $pdo->query("SELECT code, title FROM courses ORDER BY code")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enrollments - Admin</title>
    <link rel="stylesheet" href="../style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body>
    <?php include_once '../includes/navbar.php'; ?>
    
    <div class="container mt-4">
        <!-- Messages -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <h1 class="mb-4"><i class="bi bi-clipboard-check"></i> Enrollments Management</h1>
        
        <div class="row">
            <!-- Add Enrollment Form -->
            <div class="col-md-4 mb-4">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-plus-circle"></i> Add Enrollment</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label class="form-label">Student *</label>
                                <select class="form-select" name="student_id" required>
                                    <option value="">Select Student</option>
                                    <?php foreach($students as $student): ?>
                                        <option value="<?php echo $student['id']; ?>">
                                            <?php echo htmlspecialchars($student['name']); ?> (<?php echo $student['id']; ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Course *</label>
                                <select class="form-select" name="course_code" required>
                                    <option value="">Select Course</option>
                                    <?php foreach($courses as $course): ?>
                                        <option value="<?php echo $course['code']; ?>">
                                            <?php echo $course['code']; ?> - <?php echo htmlspecialchars($course['title']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Grade (Optional)</label>
                                <select class="form-select" name="grade">
                                    <option value="">No Grade</option>
                                    <option value="A">A (Excellent)</option>
                                    <option value="A-">A-</option>
                                    <option value="B+">B+</option>
                                    <option value="B">B</option>
                                    <option value="B-">B-</option>
                                    <option value="C+">C+</option>
                                    <option value="C">C</option>
                                    <option value="C-">C-</option>
                                    <option value="D">D</option>
                                    <option value="F">F (Fail)</option>
                                </select>
                            </div>
                            <button type="submit" name="add_enrollment" class="btn btn-primary w-100">
                                <i class="bi bi-check-circle"></i> Add Enrollment
                            </button>
                        </form>
                    </div>
                </div>
                
                <!-- Quick Stats -->
                <div class="card shadow mt-4">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0"><i class="bi bi-graph-up"></i> Enrollment Stats</h6>
                    </div>
                    <div class="card-body">
                        <?php
                        $totalEnrollments = count($enrollments);
                        $withGrades = count(array_filter($enrollments, fn($e) => !empty($e['grade'])));
                        $withoutGrades = $totalEnrollments - $withGrades;
                        ?>
                        <p>Total Enrollments: <strong><?php echo $totalEnrollments; ?></strong></p>
                        <p>With Grades: <strong><?php echo $withGrades; ?></strong></p>
                        <p>Without Grades: <strong><?php echo $withoutGrades; ?></strong></p>
                    </div>
                </div>
            </div>
            
            <!-- Enrollments List -->
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header">
                        <h5 class="mb-0">Current Enrollments</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Student</th>
                                        <th>Course</th>
                                        <th>Instructor</th>
                                        <th>Grade</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($enrollments)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">
                                                <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                                                <p class="mt-2">No enrollments found</p>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach($enrollments as $enrollment): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($enrollment['student_name']); ?></strong><br>
                                                <small class="text-muted"><?php echo $enrollment['student_id']; ?></small>
                                            </td>
                                            <td>
                                                <strong><?php echo $enrollment['course_code']; ?></strong><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($enrollment['course_title']); ?></small>
                                            </td>
                                            <td><?php echo htmlspecialchars($enrollment['instructor']); ?></td>
                                            <td>
                                                <?php if ($enrollment['grade']): ?>
                                                    <span class="badge bg-<?php 
                                                        echo in_array($enrollment['grade'], ['A', 'A-']) ? 'success' : 
                                                             (in_array($enrollment['grade'], ['B+', 'B', 'B-']) ? 'info' :
                                                             (in_array($enrollment['grade'], ['C+', 'C', 'C-']) ? 'warning' : 'danger'));
                                                    ?>">
                                                        <?php echo $enrollment['grade']; ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">No Grade</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo date('M d, Y', strtotime($enrollment['recorded_at'])); ?></td>
                                            <td>
                                                <form method="POST" action="" class="d-inline">
                                                    <input type="hidden" name="enrollment_id" value="<?php echo $enrollment['id']; ?>">
                                                    <button type="submit" name="remove_enrollment" 
                                                            class="btn btn-sm btn-outline-danger"
                                                            onclick="return confirm('Remove this enrollment?')">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>