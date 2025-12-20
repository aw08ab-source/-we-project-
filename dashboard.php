<?php
// Protect this page - require login
require_once 'includes/session.php';
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - UniTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    <div class="container mt-5">
        <h1>Dashboard</h1>
        <div class="alert alert-success">
            <h4>Login Successful!</h4>
            <p>You are now logged in as a <strong><?php echo $_SESSION['user_role']; ?></strong>.</p>
        </div>
        <!-- Add role-specific content here -->
        <?php if ($_SESSION['user_role'] == 'student'): ?>
            <p>Access your <a href="results.php">results</a> and <a href="courses.php">courses</a>.</p>
        <?php elseif ($_SESSION['user_role'] == 'admin'): ?>
            <p>Go to the <a href="admin.php">admin panel</a> to manage users and courses.</p>
        <?php endif; ?>
    </div>
</body>
</html>