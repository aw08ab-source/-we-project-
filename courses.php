<?php
// courses.php - Fixed function declaration error
session_start();
require_once 'includes/database.php';

// Check if user is logged in
$isLoggedIn = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
$userRole = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : '';
$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : '';

// Define highlightSearch function OUTSIDE the loop
function highlightSearch($text, $search) {
    if (empty($search)) return htmlspecialchars($text);
    $pattern = '/' . preg_quote($search, '/') . '/i';
    return preg_replace($pattern, '<span class="search-highlight">$0</span>', htmlspecialchars($text));
}

// Handle course enrollment
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $isLoggedIn && $userRole == 'student') {
    if (isset($_POST['enroll_course'])) {
        $courseCode = $_POST['course_code'];
        
        try {
            // Check if already enrolled
            $stmt = $pdo->prepare("SELECT * FROM student_records WHERE student_id = ? AND course_code = ?");
            $stmt->execute([$userId, $courseCode]);
            
            if ($stmt->rowCount() > 0) {
                $message = "You are already enrolled in this course!";
                $messageType = "warning";
            } else {
                // Check available seats
                $stmt = $pdo->prepare("SELECT seats FROM courses WHERE code = ?");
                $stmt->execute([$courseCode]);
                $course = $stmt->fetch();
                
                $stmt = $pdo->prepare("SELECT COUNT(*) as enrolled FROM student_records WHERE course_code = ?");
                $stmt->execute([$courseCode]);
                $enrollment = $stmt->fetch();
                
                $availableSeats = $course['seats'] - $enrollment['enrolled'];
                
                if ($availableSeats > 0) {
                    // Enroll student
                    $stmt = $pdo->prepare("INSERT INTO student_records (student_id, course_code) VALUES (?, ?)");
                    $stmt->execute([$userId, $courseCode]);
                    $message = "Successfully enrolled in course!";
                    $messageType = "success";
                } else {
                    $message = "No available seats in this course!";
                    $messageType = "danger";
                }
            }
        } catch(PDOException $e) {
            $message = "Error: " . $e->getMessage();
            $messageType = "danger";
        }
    }
    
    if (isset($_POST['drop_course'])) {
        $courseCode = $_POST['course_code'];
        
        try {
            $stmt = $pdo->prepare("DELETE FROM student_records WHERE student_id = ? AND course_code = ?");
            $stmt->execute([$userId, $courseCode]);
            $message = "Successfully dropped the course!";
            $messageType = "success";
        } catch(PDOException $e) {
            $message = "Error: " . $e->getMessage();
            $messageType = "danger";
        }
    }
}

// Check if search parameter exists
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Prepare SQL query with proper parameter handling
if (!empty($search)) {
    // Using named parameters with unique names
    $sql = "SELECT c.*, 
            COUNT(sr.id) as enrolled_count,
            (c.seats - COUNT(sr.id)) as available_seats
            FROM courses c
            LEFT JOIN student_records sr ON c.code = sr.course_code
            WHERE c.code LIKE :search1 
            OR c.title LIKE :search2 
            OR c.instructor LIKE :search3 
            GROUP BY c.code
            ORDER BY c.code";
    
    try {
        $stmt = $pdo->prepare($sql);
        $searchParam = "%$search%";
        $stmt->bindParam(':search1', $searchParam, PDO::PARAM_STR);
        $stmt->bindParam(':search2', $searchParam, PDO::PARAM_STR);
        $stmt->bindParam(':search3', $searchParam, PDO::PARAM_STR);
        $stmt->execute();
    } catch(PDOException $e) {
        die("Search error: " . $e->getMessage());
    }
} else {
    $sql = "SELECT c.*, 
            COUNT(sr.id) as enrolled_count,
            (c.seats - COUNT(sr.id)) as available_seats
            FROM courses c
            LEFT JOIN student_records sr ON c.code = sr.course_code
            GROUP BY c.code
            ORDER BY c.code";
    
    try {
        $stmt = $pdo->query($sql);
    } catch(PDOException $e) {
        die("Query error: " . $e->getMessage());
    }
}

// Fetch all courses
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get student's enrolled courses (if logged in)
$myCourses = [];
if ($isLoggedIn && $userRole == 'student') {
    try {
        $sql = "SELECT c.*, sr.grade, sr.recorded_at 
                FROM courses c
                JOIN student_records sr ON c.code = sr.course_code
                WHERE sr.student_id = ?
                ORDER BY sr.recorded_at DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userId]);
        $myCourses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("My courses error: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniTrack - Courses</title>
    <link rel="stylesheet" href="style.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .enrollment-btn {
            min-width: 100px;
        }
        .course-status {
            font-size: 0.85rem;
        }
        .alert {
            animation: fadeIn 0.5s;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .card {
            transition: transform 0.2s;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .badge-seats {
            font-size: 0.8rem;
        }
        .search-highlight {
            background-color: #fff3cd;
            padding: 2px 4px;
            border-radius: 3px;
        }
        .search-results {
            background-color: #f8f9fa;
            border-left: 4px solid #4e73df;
        }
    </style>
</head>
<body>

<!-- NAVBAR -->
<?php include 'includes/navbar.php'; ?>

<!-- MAIN CONTENT -->
<div class="container my-5" id="courses-page">
    
    <!-- Display messages -->
    <?php if (isset($message)): ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <h2 class="mb-4">Course Catalog</h2>
    
    <?php if ($isLoggedIn): ?>
        <div class="alert alert-info d-flex align-items-center">
            <i class="bi bi-person-circle me-2"></i>
            <div>
                <strong>Logged in as:</strong> <?php echo htmlspecialchars($_SESSION['user_name']); ?> 
                (<?php echo $userRole; ?>)
                <?php if ($userRole == 'student' && !empty($myCourses)): ?>
                    | <a href="#my-courses" class="alert-link">View My Courses (<?php echo count($myCourses); ?>)</a>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle me-2"></i>
            Please <a href="login.php" class="alert-link">login</a> to enroll in courses.
        </div>
    <?php endif; ?>
    
    <p>Browse all available courses for the current semester. <?php if (!empty($search)) echo 'Showing results for: <strong>"' . htmlspecialchars($search) . '"</strong>'; ?></p>
    
    <!-- Search Form -->
    <form method="get" action="" class="row g-3 mb-4">
        <div class="col-md-8">
            <label for="search-course" class="form-label">Search Courses:</label>
            <div class="input-group">
                <input type="text" id="search-course" name="search" 
                       placeholder="Search by code, title, or instructor..." 
                       class="form-control" 
                       value="<?php echo htmlspecialchars($search); ?>" />
                <?php if (!empty($search)): ?>
                    <a href="courses.php" class="btn btn-outline-secondary" title="Clear search">
                        <i class="bi bi-x-circle"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-md-4">
            <label class="form-label d-block">&nbsp;</label>
            <div class="row g-2">
                <div class="col-6">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> Search
                    </button>
                </div>
                <div class="col-6">
                    <a href="courses.php" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-arrow-clockwise"></i> Reset
                    </a>
                </div>
            </div>
        </div>
    </form>
    
    <!-- Results Count -->
    <?php if (!empty($search)): ?>
        <div class="alert search-results mb-4">
            <i class="bi bi-search me-2"></i>
            Found <strong><?php echo count($courses); ?></strong> course(s) matching your search.
            <a href="courses.php" class="float-end text-decoration-none">
                <small><i class="bi bi-x-circle"></i> Clear search</small>
            </a>
        </div>
    <?php endif; ?>
    
    <!-- Courses Display (Grid View) -->
    <?php if (empty($courses)): ?>
        <div class="alert alert-warning text-center py-5">
            <i class="bi bi-search display-4 text-muted mb-3"></i>
            <h4>No courses found</h4>
            <p>No courses match your search criteria.</p>
            <a href="courses.php" class="btn btn-primary">View All Courses</a>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($courses as $course):
                $availableSeats = max(0, $course['available_seats']);
                $isFull = $availableSeats == 0;
                $isEnrolled = false;
                
                // Check if student is already enrolled
                if ($isLoggedIn && $userRole == 'student') {
                    $isEnrolled = in_array($course['code'], array_column($myCourses, 'code'));
                }
            ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 shadow-sm border-<?php echo $isFull ? 'danger' : 'primary'; ?>">
                    <div class="card-header bg-<?php echo $isFull ? 'danger' : 'primary'; ?> text-white">
                        <h5 class="card-title mb-0">
                            <?php echo !empty($search) ? highlightSearch($course['code'], $search) : htmlspecialchars($course['code']); ?>
                            <?php if ($isEnrolled): ?>
                                <span class="badge bg-success float-end">
                                    <i class="bi bi-check-circle"></i> Enrolled
                                </span>
                            <?php endif; ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2 text-muted">
                            <?php echo !empty($search) ? highlightSearch($course['title'], $search) : htmlspecialchars($course['title']); ?>
                        </h6>
                        <p class="card-text">
                            <strong>Instructor:</strong> 
                            <?php echo !empty($search) ? highlightSearch($course['instructor'], $search) : htmlspecialchars($course['instructor']); ?><br>
                            <strong>Credits:</strong> <?php echo htmlspecialchars($course['credits']); ?>
                        </p>
                        
                        <!-- Seat Information -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted course-status">
                                    <i class="bi bi-people"></i> 
                                    <?php echo $course['enrolled_count']; ?> enrolled
                                </span>
                                <span class="badge bg-<?php echo $isFull ? 'danger' : 'success'; ?> badge-seats">
                                    <i class="bi bi-door-open"></i> 
                                    <?php echo $availableSeats; ?> seats left
                                </span>
                            </div>
                            <div class="progress mt-2" style="height: 8px;">
                                <div class="progress-bar bg-<?php 
                                    $percentage = ($course['enrolled_count'] / $course['seats']) * 100;
                                    echo $percentage > 90 ? 'danger' : ($percentage > 70 ? 'warning' : 'success');
                                ?>" 
                                     style="width: <?php echo min($percentage, 100); ?>%">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Enrollment Buttons -->
                        <?php if ($isLoggedIn && $userRole == 'student'): ?>
                            <form method="POST" action="" class="mt-3">
                                <input type="hidden" name="course_code" value="<?php echo $course['code']; ?>">
                                
                                <?php if ($isEnrolled): ?>
                                    <button type="submit" name="drop_course" 
                                            class="btn btn-outline-danger w-100 enrollment-btn"
                                            onclick="return confirm('Are you sure you want to drop <?php echo $course['code']; ?>?')">
                                        <i class="bi bi-x-circle"></i> Drop Course
                                    </button>
                                <?php elseif (!$isFull): ?>
                                    <button type="submit" name="enroll_course" 
                                            class="btn btn-primary w-100 enrollment-btn">
                                        <i class="bi bi-plus-circle"></i> Enroll Now
                                    </button>
                                <?php else: ?>
                                    <button type="button" class="btn btn-secondary w-100" disabled>
                                        <i class="bi bi-slash-circle"></i> Course Full
                                    </button>
                                <?php endif; ?>
                            </form>
                        <?php elseif (!$isLoggedIn): ?>
                            <a href="login.php?redirect=courses.php" class="btn btn-outline-primary w-100">
                                <i class="bi bi-box-arrow-in-right"></i> Login to Enroll
                            </a>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer bg-transparent">
                        <small class="text-muted">
                            <i class="bi bi-info-circle"></i> 
                            Total capacity: <?php echo $course['seats']; ?> students
                        </small>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <!-- My Courses Section (For logged-in students) -->
    <?php if ($isLoggedIn && $userRole == 'student'): ?>
    <div class="row mt-5" id="my-courses">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">
                        <i class="bi bi-bookmark-check"></i> My Enrolled Courses (<?php echo count($myCourses); ?>)
                    </h4>
                </div>
                <div class="card-body">
                    <?php if (empty($myCourses)): ?>
                        <p class="text-muted">You are not enrolled in any courses yet.</p>
                        <a href="#courses-page" class="btn btn-primary">
                            <i class="bi bi-search"></i> Browse Courses
                        </a>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Course Code</th>
                                        <th>Course Title</th>
                                        <th>Instructor</th>
                                        <th>Credits</th>
                                        <th>Enrollment Date</th>
                                        <th>Grade</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($myCourses as $course): ?>
                                    <tr>
                                        <td><strong><?php echo $course['code']; ?></strong></td>
                                        <td><?php echo htmlspecialchars($course['title']); ?></td>
                                        <td><?php echo htmlspecialchars($course['instructor']); ?></td>
                                        <td><?php echo $course['credits']; ?></td>
                                        <td><?php echo date('M d, Y', strtotime($course['recorded_at'])); ?></td>
                                        <td>
                                            <?php if ($course['grade']): ?>
                                                <span class="badge bg-<?php 
                                                    echo in_array($course['grade'], ['A', 'A-']) ? 'success' : 
                                                         (in_array($course['grade'], ['B+', 'B', 'B-']) ? 'info' :
                                                         (in_array($course['grade'], ['C+', 'C', 'C-']) ? 'warning' : 'danger'));
                                                ?>">
                                                    <?php echo $course['grade']; ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">No Grade</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <form method="POST" action="" class="d-inline">
                                                <input type="hidden" name="course_code" value="<?php echo $course['code']; ?>">
                                                <button type="submit" name="drop_course" 
                                                        class="btn btn-sm btn-outline-danger"
                                                        onclick="return confirm('Drop <?php echo $course['code']; ?>?')">
                                                    <i class="bi bi-trash"></i> Drop
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr class="table-light">
                                        <td colspan="3"><strong>Total Credits:</strong></td>
                                        <td><strong>
                                            <?php 
                                            $totalCredits = array_sum(array_column($myCourses, 'credits'));
                                            echo $totalCredits;
                                            ?>
                                        </strong></td>
                                        <td colspan="3" class="text-end">
                                            <small>Enrolled in <?php echo count($myCourses); ?> course(s)</small>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Course Registration Info -->
    <?php if ($isLoggedIn && $userRole == 'student'): ?>
        <?php 
        $totalCredits = !empty($myCourses) ? array_sum(array_column($myCourses, 'credits')) : 0;
        $creditsLeft = 18 - $totalCredits;
        ?>
        <div class="alert alert-<?php echo $creditsLeft > 0 ? 'success' : 'warning'; ?> mt-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <i class="bi bi-graph-up me-2"></i>
                    <strong>Your Credit Status:</strong> 
                    <?php echo $totalCredits; ?> credits enrolled | 
                    <?php echo $creditsLeft; ?> credits remaining (Max: 18)
                </div>
                <?php if ($creditsLeft <= 0): ?>
                    <span class="badge bg-danger">Credit Limit Reached</span>
                <?php endif; ?>
            </div>
            <div class="progress mt-2" style="height: 10px;">
                <div class="progress-bar bg-<?php 
                    echo $totalCredits >= 18 ? 'danger' : 
                         ($totalCredits >= 15 ? 'warning' : 'success'); ?>" 
                     style="width: <?php echo min(($totalCredits / 18) * 100, 100); ?>%">
                </div>
            </div>
        </div>
    <?php endif; ?>

</div>

<!-- FOOTER -->
<footer class="text-center py-3 mt-5 bg-light border-top">
    &copy; 2025 UniTrack - Student Registrar System. COMP3700 Project.
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Auto-dismiss alerts after 5 seconds
    document.addEventListener('DOMContentLoaded', function() {
        const alerts = document.querySelectorAll('.alert:not(.alert-light):not(.search-results)');
        alerts.forEach(function(alert) {
            setTimeout(function() {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }, 5000);
        });
        
        // Smooth scroll to my courses
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                const href = this.getAttribute('href');
                if (href !== '#') {
                    e.preventDefault();
                    const target = document.querySelector(href);
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                }
            });
        });
        
        // Focus search input if there's a search term
        <?php if (!empty($search)): ?>
            document.getElementById('search-course')?.focus();
        <?php endif; ?>
        
        // Add keyboard shortcut for search (Ctrl+F)
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
                e.preventDefault();
                document.getElementById('search-course')?.focus();
            }
        });
    });
</script>
</body>
</html>