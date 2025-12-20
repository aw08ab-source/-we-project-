<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniTrack - Results</title>
    <link rel="stylesheet" href="style.css" />
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<!-- NAVBAR -->
<?php include 'includes/navbar.php'; ?>

<!-- MAIN CONTENT -->
<div class="container my-5" id="results-page">
    <h2 class="mb-4">Academic Results</h2>
    <p>View your grades and academic performance.</p>

    <div class="alert alert-warning">
        <strong>Note:</strong> You need to <a href="login.html">login</a> to view your personal results.
    </div>

    <h3 class="mt-4">Sample Student Record</h3>
    <div class="table-responsive">
        <table class="table table-striped table-bordered">
            <thead class="table-primary">
                <tr>
                    <th>Course Code</th>
                    <th>Course Name</th>
                    <th>Credits</th>
                    <th>Grade</th>
                    <th>Grade Points</th>
                </tr>
            </thead>
            <tbody id="results-table-body">
                <!-- Sample data will be populated here by script.js -->
            </tbody>
        </table>
    </div>

    <h3 class="mt-5">Summary</h3>
    <div class="table-responsive">
        <table class="table table-bordered w-75 mx-auto">
            <thead class="table-dark">
                <tr>
                    <th>Total Credits</th>
                    <th>GPA</th>
                    <th>Academic Standing</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td id="total-credits"></td>
                    <td id="gpa"></td>
                    <td id="academic-standing"></td>
                </tr>
            </tbody>
        </table>
    </div>

    <h3 class="mt-5">Grade Submission (Teachers Only)</h3>
    <form action="https://httpbin.org/get" method="get" class="row g-3">

        <div class="col-md-6">
            <label for="course-select" class="form-label">Select Course:</label>
            <select id="course-select" name="course_select" class="form-select">
                <option value="comp3700">COMP3700 - Web Computing</option>
                <option value="math1010">MATH1010 - Calculus I</option>
                <option value="phys1500">PHYS1500 - General Physics</option>
            </select>
        </div>

        <div class="col-md-6">
            <label for="student-id" class="form-label">Student ID:</label>
            <input type="text" id="student-id" name="student_id" class="form-control" />
        </div>

        <div class="col-md-6">
            <label for="grade" class="form-label">Grade:</label>
            <select id="grade" name="grade" class="form-select">
                <option value="A">A</option>
                <option value="A-">A-</option>
                <option value="B+">B+</option>
                <option value="B">B</option>
                <option value="B-">B-</option>
                <option value="C+">C+</option>
                <option value="C">C</option>
                <option value="D">D</option>
                <option value="F">F</option>
            </select>
        </div>

        <div class="col-12">
            <input type="submit" value="Submit Grade" class="btn btn-primary" />
        </div>
    </form>
</div>

<!-- FOOTER -->
<footer class="text-center py-3 mt-5 bg-light border-top">
    &copy; 2025 UniTrack - Student Registrar System. COMP3700 Project.
</footer>
<script src="script.js"></script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
