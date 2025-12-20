<?php
// results.php - Role-based grade management system
session_start();
require_once 'includes/database.php';

// Check if user is logged in
$isLoggedIn = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
$userRole = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : '';
$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : '';
$userName = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '';

// Initialize variables
$message = '';
$messageType = '';
$studentResults = [];
$teacherCourses = [];
$courseStudents = [];
$selectedCourse = '';

// Handle teacher grade submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $isLoggedIn && $userRole == 'teacher') {
    if (isset($_POST['submit_grade'])) {
        $studentId = $_POST['student_id'];
        $courseCode = $_POST['course_code'];
        $grade = $_POST['grade'];
        
        try {
            // Check if the teacher teaches this course
            $stmt = $pdo->prepare("SELECT * FROM courses WHERE code = ? AND instructor = ?");
            $stmt->execute([$courseCode, $userName]);
            
            if ($stmt->rowCount() == 0) {
                $message = "You are not authorized to submit grades for this course.";
                $messageType = "danger";
            } else {
                // Update or insert grade
                $sql = "INSERT INTO student_records (student_id, course_code, grade) 
                        VALUES (?, ?, ?) 
                        ON DUPLICATE KEY UPDATE grade = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$studentId, $courseCode, $grade, $grade]);
                
                $message = "Grade submitted successfully!";
                $messageType = "success";
            }
        } catch(PDOException $e) {
            $message = "Error: " . $e->getMessage();
            $messageType = "danger";
        }
    }
    
    // Handle grade update
    if (isset($_POST['update_grade'])) {
        $recordId = $_POST['record_id'];
        $grade = $_POST['grade'];
        
        try {
            // Verify the teacher has permission (teaches the course)
            $sql = "UPDATE student_records sr
                    JOIN courses c ON sr.course_code = c.code
                    SET sr.grade = ?
                    WHERE sr.id = ? AND c.instructor = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$grade, $recordId, $userName]);
            
            $message = "Grade updated successfully!";
            $messageType = "success";
        } catch(PDOException $e) {
            $message = "Error: " . $e->getMessage();
            $messageType = "danger";
        }
    }
}

// Load data based on user role
if ($isLoggedIn) {
    if ($userRole == 'student') {
        // Get student's grades
        $sql = "SELECT sr.*, c.title, c.credits, c.instructor 
                FROM student_records sr
                JOIN courses c ON sr.course_code = c.code
                WHERE sr.student_id = ?
                ORDER BY sr.recorded_at DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userId]);
        $studentResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Calculate GPA
        if (!empty($studentResults)) {
            $totalCredits = 0;
            $totalGradePoints = 0;
            
            foreach ($studentResults as $record) {
                if (!empty($record['grade'])) {
                    $gradePoint = getGradePoint($record['grade']);
                    $totalGradePoints += $gradePoint * $record['credits'];
                    $totalCredits += $record['credits'];
                }
            }
            
            $gpa = $totalCredits > 0 ? $totalGradePoints / $totalCredits : 0;
        }
    } 
    elseif ($userRole == 'teacher') {
        // Get courses taught by this teacher
        $sql = "SELECT * FROM courses WHERE instructor = ? ORDER BY code";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userName]);
        $teacherCourses = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get students for selected course
        if (isset($_GET['course']) && !empty($_GET['course'])) {
            $selectedCourse = $_GET['course'];
            
            // Verify teacher teaches this course
            $stmt = $pdo->prepare("SELECT * FROM courses WHERE code = ? AND instructor = ?");
            $stmt->execute([$selectedCourse, $userName]);
            
            if ($stmt->rowCount() > 0) {
                // Get all students in this course
                $sql = "SELECT u.id, u.name, u.email, sr.grade, sr.id as record_id, sr.recorded_at
                        FROM users u
                        LEFT JOIN student_records sr ON u.id = sr.student_id AND sr.course_code = ?
                        WHERE u.role = 'student' 
                        AND EXISTS (
                            SELECT 1 FROM student_records 
                            WHERE student_id = u.id AND course_code = ?
                        )
                        ORDER BY u.name";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$selectedCourse, $selectedCourse]);
                $courseStudents = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        }
    }
}

// Helper function to convert grade to grade points
function getGradePoint($grade) {
    $gradePoints = [
        'A' => 4.0, 'A-' => 3.7,
        'B+' => 3.3, 'B' => 3.0, 'B-' => 2.7,
        'C+' => 2.3, 'C' => 2.0, 'C-' => 1.7,
        'D' => 1.0, 'F' => 0.0
    ];
    return $gradePoints[$grade] ?? 0;
}

// Helper function to get academic standing
function getAcademicStanding($gpa) {
    if ($gpa >= 3.5) return 'Excellent';
    if ($gpa >= 3.0) return 'Good';
    if ($gpa >= 2.0) return 'Satisfactory';
    return 'Probation';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniTrack - Academic Results</title>
    <link rel="stylesheet" href="style.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .grade-badge {
            min-width: 50px;
            text-align: center;
        }
        .grade-edit {
            max-width: 100px;
        }
        .status-excellent { color: #198754; }
        .status-good { color: #0dcaf0; }
        .status-satisfactory { color: #ffc107; }
        .status-probation { color: #dc3545; }
        .course-selector {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .gpa-display {
            font-size: 2.5rem;
            font-weight: bold;
        }
        .highlight-row {
            background-color: #f8f9fa;
            transition: background-color 0.3s;
        }
        .highlight-row:hover {
            background-color: #e9ecef;
        }
    </style>
</head>
<body>

<!-- NAVBAR -->
<?php include 'includes/navbar.php'; ?>

<!-- MAIN CONTENT -->
<div class="container my-5" id="results-page">
    
    <!-- Display messages -->
    <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <h2 class="mb-4">
        <i class="bi bi-clipboard-data"></i> Academic Results
        <?php if ($isLoggedIn): ?>
            <small class="text-muted">- <?php echo htmlspecialchars($userName); ?></small>
        <?php endif; ?>
    </h2>
    
    <?php if (!$isLoggedIn): ?>
        <!-- Not logged in view -->
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong>Login Required:</strong> You need to <a href="login.php" class="alert-link">login</a> to view academic results.
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Sample Student Record</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Course Code</th>
                                <th>Course Name</th>
                                <th>Credits</th>
                                <th>Grade</th>
                                <th>Grade Points</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>COMP3700</td>
                                <td>Web Development</td>
                                <td>3</td>
                                <td><span class="badge bg-success">A</span></td>
                                <td>12.0</td>
                            </tr>
                            <tr>
                                <td>MATH1010</td>
                                <td>Calculus I</td>
                                <td>4</td>
                                <td><span class="badge bg-info">B+</span></td>
                                <td>13.2</td>
                            </tr>
                            <tr>
                                <td>COMP3501</td>
                                <td>Computer Organization</td>
                                <td>3</td>
                                <td><span class="badge bg-warning">C</span></td>
                                <td>6.0</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="text-center mt-3">
                    <a href="login.php" class="btn btn-primary">
                        <i class="bi bi-box-arrow-in-right"></i> Login to View Your Results
                    </a>
                </div>
            </div>
        </div>
        
    <?php elseif ($userRole == 'student'): ?>
        <!-- STUDENT VIEW -->
        <div class="alert alert-info">
            <i class="bi bi-person-circle me-2"></i>
            <strong>Student Dashboard:</strong> Viewing your personal academic results.
        </div>
        
        <?php if (empty($studentResults)): ?>
            <div class="alert alert-warning">
                <i class="bi bi-info-circle me-2"></i>
                You don't have any recorded grades yet.
            </div>
        <?php else: ?>
            <!-- Student's Grades Table -->
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-journal-check"></i> Your Academic Record</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Course Code</th>
                                    <th>Course Name</th>
                                    <th>Instructor</th>
                                    <th>Credits</th>
                                    <th>Grade</th>
                                    <th>Grade Points</th>
                                    <th>Date Recorded</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $totalCredits = 0;
                                $totalGradePoints = 0;
                                foreach ($studentResults as $record): 
                                    if (!empty($record['grade'])) {
                                        $gradePoint = getGradePoint($record['grade']);
                                        $points = $gradePoint * $record['credits'];
                                        $totalGradePoints += $points;
                                        $totalCredits += $record['credits'];
                                    }
                                ?>
                                <tr class="highlight-row">
                                    <td><strong><?php echo $record['course_code']; ?></strong></td>
                                    <td><?php echo htmlspecialchars($record['title']); ?></td>
                                    <td><?php echo htmlspecialchars($record['instructor']); ?></td>
                                    <td><?php echo $record['credits']; ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo in_array($record['grade'], ['A', 'A-']) ? 'success' : 
                                                 (in_array($record['grade'], ['B+', 'B', 'B-']) ? 'info' :
                                                 (in_array($record['grade'], ['C+', 'C', 'C-']) ? 'warning' : 'danger'));
                                        ?> grade-badge">
                                            <?php echo $record['grade'] ?: 'N/A'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (!empty($record['grade'])): ?>
                                            <?php echo number_format($gradePoint * $record['credits'], 1); ?>
                                        <?php else: ?>
                                            N/A
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($record['recorded_at'])); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- GPA Summary -->
            <?php 
            $gpa = $totalCredits > 0 ? $totalGradePoints / $totalCredits : 0;
            $standing = getAcademicStanding($gpa);
            $standingClass = strtolower($standing);
            ?>
            <div class="row mt-4">
                <div class="col-md-8 offset-md-2">
                    <div class="card shadow">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="bi bi-graph-up"></i> Academic Summary</h5>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-md-4">
                                    <div class="display-6 text-primary"><?php echo $totalCredits; ?></div>
                                    <p class="text-muted">Total Credits</p>
                                </div>
                                <div class="col-md-4">
                                    <div class="gpa-display text-<?php 
                                        echo $gpa >= 3.5 ? 'success' : 
                                             ($gpa >= 3.0 ? 'info' : 
                                             ($gpa >= 2.0 ? 'warning' : 'danger'));
                                    ?>">
                                        <?php echo number_format($gpa, 2); ?>
                                    </div>
                                    <p class="text-muted">Cumulative GPA</p>
                                </div>
                                <div class="col-md-4">
                                    <div class="display-6 status-<?php echo $standingClass; ?>">
                                        <?php echo $standing; ?>
                                    </div>
                                    <p class="text-muted">Academic Standing</p>
                                </div>
                            </div>
                            <div class="progress mt-3" style="height: 20px;">
                                <div class="progress-bar bg-success" 
                                     style="width: <?php echo min(($gpa / 4.0) * 100, 100); ?>%"
                                     role="progressbar">
                                    <?php echo number_format($gpa, 2); ?> / 4.0
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
    <?php elseif ($userRole == 'teacher'): ?>
        <!-- TEACHER VIEW -->
        <div class="alert alert-info">
            <i class="bi bi-person-badge me-2"></i>
            <strong>Teacher Dashboard:</strong> Manage and submit student grades for your courses.
        </div>
        
        <!-- Course Selection -->
        <div class="course-selector">
            <h5><i class="bi bi-book"></i> Select Course to Manage Grades</h5>
            <div class="row mt-3">
                <?php if (empty($teacherCourses)): ?>
                    <div class="col-12">
                        <div class="alert alert-warning">
                            You are not assigned to teach any courses.
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($teacherCourses as $course): ?>
                        <div class="col-md-4 mb-3">
                            <a href="results.php?course=<?php echo $course['code']; ?>" 
                               class="card text-decoration-none <?php echo $selectedCourse == $course['code'] ? 'border-primary' : ''; ?>">
                                <div class="card-body">
                                    <h6 class="card-title"><?php echo $course['code']; ?></h6>
                                    <p class="card-text small"><?php echo htmlspecialchars($course['title']); ?></p>
                                    <span class="badge bg-secondary"><?php echo $course['credits']; ?> credits</span>
                                    <?php if ($selectedCourse == $course['code']): ?>
                                        <span class="badge bg-primary float-end">Selected</span>
                                    <?php endif; ?>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if (!empty($selectedCourse)): ?>
            <!-- Students in Selected Course -->
            <div class="card shadow mt-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="bi bi-people"></i> 
                        Students in <?php echo $selectedCourse; ?>
                        <?php 
                            // Get course title
                            $stmt = $pdo->prepare("SELECT title FROM courses WHERE code = ?");
                            $stmt->execute([$selectedCourse]);
                            $courseTitle = $stmt->fetch()['title'];
                        ?>
                        <small class="text-muted">- <?php echo htmlspecialchars($courseTitle); ?></small>
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (empty($courseStudents)): ?>
                        <div class="alert alert-info">
                            No students enrolled in this course.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Student ID</th>
                                        <th>Student Name</th>
                                        <th>Email</th>
                                        <th>Current Grade</th>
                                        <th>Update Grade</th>
                                        <th>Last Updated</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($courseStudents as $student): ?>
                                    <tr>
                                        <td><code><?php echo $student['id']; ?></code></td>
                                        <td><?php echo htmlspecialchars($student['name']); ?></td>
                                        <td><?php echo htmlspecialchars($student['email']); ?></td>
                                        <td>
                                            <?php if ($student['grade']): ?>
                                                <span class="badge bg-<?php 
                                                    echo in_array($student['grade'], ['A', 'A-']) ? 'success' : 
                                                         (in_array($student['grade'], ['B+', 'B', 'B-']) ? 'info' :
                                                         (in_array($student['grade'], ['C+', 'C', 'C-']) ? 'warning' : 'danger'));
                                                ?>">
                                                    <?php echo $student['grade']; ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">No Grade</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <form method="POST" action="" class="d-inline">
                                                <input type="hidden" name="record_id" value="<?php echo $student['record_id']; ?>">
                                                <div class="input-group input-group-sm grade-edit">
                                                    <select class="form-select" name="grade">
                                                        <option value="">Select Grade</option>
                                                        <option value="A" <?php echo $student['grade'] == 'A' ? 'selected' : ''; ?>>A</option>
                                                        <option value="A-" <?php echo $student['grade'] == 'A-' ? 'selected' : ''; ?>>A-</option>
                                                        <option value="B+" <?php echo $student['grade'] == 'B+' ? 'selected' : ''; ?>>B+</option>
                                                        <option value="B" <?php echo $student['grade'] == 'B' ? 'selected' : ''; ?>>B</option>
                                                        <option value="B-" <?php echo $student['grade'] == 'B-' ? 'selected' : ''; ?>>B-</option>
                                                        <option value="C+" <?php echo $student['grade'] == 'C+' ? 'selected' : ''; ?>>C+</option>
                                                        <option value="C" <?php echo $student['grade'] == 'C' ? 'selected' : ''; ?>>C</option>
                                                        <option value="C-" <?php echo $student['grade'] == 'C-' ? 'selected' : ''; ?>>C-</option>
                                                        <option value="D" <?php echo $student['grade'] == 'D' ? 'selected' : ''; ?>>D</option>
                                                        <option value="F" <?php echo $student['grade'] == 'F' ? 'selected' : ''; ?>>F</option>
                                                    </select>
                                                    <button type="submit" name="update_grade" class="btn btn-primary btn-sm">
                                                        <i class="bi bi-check"></i>
                                                    </button>
                                                </div>
                                            </form>
                                        </td>
                                        <td>
                                            <?php if ($student['recorded_at']): ?>
                                                <?php echo date('M d, Y', strtotime($student['recorded_at'])); ?>
                                            <?php else: ?>
                                                N/A
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Grade Submission Form -->
            <div class="card shadow mt-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-plus-circle"></i> Submit New Grade</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Course Code</label>
                                <input type="text" class="form-control" 
                                       value="<?php echo $selectedCourse; ?>" readonly>
                                <input type="hidden" name="course_code" value="<?php echo $selectedCourse; ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Student ID</label>
                                <input type="text" name="student_id" class="form-control" 
                                       placeholder="e.g., s151920" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Grade</label>
                                <select name="grade" class="form-select" required>
                                    <option value="">Select Grade</option>
                                    <option value="A">A</option>
                                    <option value="A-">A-</option>
                                    <option value="B+">B+</option>
                                    <option value="B">B</option>
                                    <option value="B-">B-</option>
                                    <option value="C+">C+</option>
                                    <option value="C">C</option>
                                    <option value="C-">C-</option>
                                    <option value="D">D</option>
                                    <option value="F">F</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <button type="submit" name="submit_grade" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Submit Grade
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
        
    <?php else: ?>
        <!-- Other roles (admin, etc.) -->
        <div class="alert alert-info">
            <i class="bi bi-person-gear me-2"></i>
            You are logged in as <?php echo $userRole; ?>. 
            <?php if ($userRole == 'admin'): ?>
                Visit the <a href="admin/index.php">admin panel</a> for grade management.
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
</div>

<!-- FOOTER -->
<footer class="text-center py-3 mt-5 bg-light border-top">
    &copy; <?php echo date('Y'); ?> UniTrack - Student Registrar System. COMP3700 Project.
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Auto-dismiss alerts after 5 seconds
    document.addEventListener('DOMContentLoaded', function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            setTimeout(function() {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }, 5000);
        });
        
        // Highlight current grade in dropdown when clicked
        document.querySelectorAll('.grade-edit select').forEach(function(select) {
            select.addEventListener('focus', function() {
                const currentGrade = this.querySelector('option[selected]');
                if (currentGrade) {
                    currentGrade.scrollIntoView({block: 'nearest'});
                }
            });
        });
    });
</script>
</body>
</html>