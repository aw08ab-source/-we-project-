<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniTrack - Courses</title>
    <link rel="stylesheet" href="style.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<!-- NAVBAR -->
<?php include 'includes/navbar.php'; ?>

<!-- MAIN CONTENT -->
<div class="container my-5" id="courses-page">
    <h2 class="mb-4">Course Catalog</h2>
    <p>Browse all available courses for the current semester.</p>

    <?php
    // Include database connection
    require_once 'includes/database.php';
    
    // Check if search parameter exists
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    
    // Prepare SQL query
    if (!empty($search)) {
        $sql = "SELECT * FROM courses 
                WHERE code LIKE :search 
                OR title LIKE :search 
                OR instructor LIKE :search 
                ORDER BY code";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':search' => "%$search%"]);
    } else {
        $sql = "SELECT * FROM courses ORDER BY code";
        $stmt = $pdo->query($sql);
    }
    
    // Fetch all courses
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($courses)) {
        echo '<div class="alert alert-warning">No courses found.</div>';
    }
    ?>

    <div class="table-responsive">
        <table class="table table-striped table-bordered" id="course-table">
            <thead class="table-primary">
                <tr>
                    <th>Course Code</th>
                    <th>Course Name</th>
                    <th>Instructor</th>
                    <th>Credits</th>
                    <th>Available Seats</th>
                </tr>
            </thead>
            <tbody id="course-table-body">
                <?php foreach ($courses as $course): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($course['code']); ?></strong></td>
                    <td><?php echo htmlspecialchars($course['title']); ?></td>
                    <td><?php echo htmlspecialchars($course['instructor']); ?></td>
                    <td><?php echo htmlspecialchars($course['credits']); ?></td>
                    <td>
                        <?php 
                        // Calculate available seats
                        $stmt = $pdo->prepare("SELECT COUNT(*) as enrolled FROM student_records WHERE course_code = ?");
                        $stmt->execute([$course['code']]);
                        $result = $stmt->fetch();
                        $available = $course['seats'] - $result['enrolled'];
                        echo max(0, $available) . ' / ' . $course['seats'];
                        ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <h3 class="mt-5">Course Registration</h3>
    <p>To register for courses, please <a href="login.php">login to your student account</a>.</p>

    <form method="get" action="" class="row g-3 mt-3" id="search-course-form">
        <div class="col-md-8">
            <label for="search-course" class="form-label">Search Courses:</label>
            <input type="text" id="search-course" name="search" 
                   placeholder="Search by code, title, or instructor..." 
                   class="form-control" 
                   value="<?php echo htmlspecialchars($search); ?>" />
        </div>

        <div class="col-md-4">
            <label class="form-label d-block">&nbsp;</label>
            <div class="row g-2">
                <div class="col-6">
                    <button type="submit" class="btn btn-primary w-100">Search</button>
                </div>
                <div class="col-6">
                    <a href="courses.php" class="btn btn-danger w-100">Reset</a>
                </div>
            </div>
        </div>
    </form>

</div>

<!-- FOOTER -->
<footer class="text-center py-3 mt-5 bg-light border-top">
    &copy; 2025 UniTrack - Student Registrar System. COMP3700 Project.
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Clear search functionality
    document.getElementById("clear-search")?.addEventListener("click", () => {
        window.location.href = "courses.php";
    });
</script>
</body>
</html>