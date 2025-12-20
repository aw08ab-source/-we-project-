<?php
// Start the session and include necessary files
require_once 'includes/session.php';
require_once 'includes/database.php';
require_once 'includes/auth.php';

// Initialize error message
$login_error = '';

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get and sanitize user input
    $identifier = trim($_POST['user_id'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'student'; // Your form has a role selector

    // Call the authentication function
    $user = authenticateUser($identifier, $password, $role);

    if ($user) {
        // Login successful: Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['loggedin'] = true;

        // Redirect to the dashboard (or another page)
        header("Location: dashboard.php");
        exit();
    } else {
        // Login failed: Set error message
        $login_error = "Invalid ID, password, or role selected. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniTrack - Login</title>
    <link rel="stylesheet" href="style.css" />
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<!-- NAVBAR -->
<?php include 'includes/navbar.php'; ?>

<!-- MAIN CONTENT -->
<div class="container my-5">
    <h2 class="mb-4">Login to UniTrack</h2>
    <?php if (!empty($login_error)): ?>
        <div class="alert alert-danger"><?php echo $login_error; ?></div>
    <?php endif; ?>

    <form action="login.php" method="post" class="row g-3">

        <div class="col-md-6">
            <label for="user-id" class="form-label">User ID:</label>
            <input type="text" id="user-id" name="user_id" class="form-control">
            <span id="idError" class="text-danger"></span>
        </div>

        <div class="col-md-6">
            <label for="password" class="form-label">Password:</label>
            <input type="password" id="password" name="password" class="form-control">
            <span id="passwordError" class="text-danger"></span>
        </div>

        <div class="col-md-6">
            <label for="role" class="form-label">I am a:</label>
            <select id="role" name="role" class="form-select">
                <option value="student">Student</option>
                <option value="teacher">Teacher</option>
                <option value="admin">Administrator</option>
            </select>
        </div>

        <div class="col-md-6 form-check mt-4">
            <input type="checkbox" id="remember" name="remember" value="yes" class="form-check-input">
            <label for="remember" class="form-check-label">Remember me</label>
        </div>

        <div class="col-12">
            <input type="submit" value="Login" class="btn btn-primary">
        </div>
    </form>

    <div class="mt-5">
        <h3>Login Information</h3>
        <p><strong>Students:</strong> Use your student ID and password provided by the university.</p>
        <p><strong>Teachers:</strong> Use your faculty ID and password.</p>
        <p><strong>Administrators:</strong> Use your admin credentials.</p>
    </div>
</div>

<!-- FOOTER -->
<footer class="text-center py-3 mt-5 bg-light border-top">
    &copy; 2025 UniTrack - Student Registrar System. COMP3700 Project.
</footer>

<script>
function validateLogin(event) {
    let valid = true;

    document.getElementById("idError").innerHTML = "";
    document.getElementById("passwordError").innerHTML = "";

    let id = document.getElementById("user-id").value.trim();
    let password = document.getElementById("password").value.trim();

    // User ID validation – numbers only
    let idPattern = /^[0-9]+$/;
    if (id === "") {
        document.getElementById("idError").innerHTML = "User ID is required";
        valid = false;
    } 
    else if (!idPattern.test(id)) {
        document.getElementById("idError").innerHTML = "User ID must contain only numbers";
        valid = false;
    }

    // password validation
    if (password === "") {
        document.getElementById("passwordError").innerHTML = "Password is required";
        valid = false;
    }
    else if (password.length < 6) {
        document.getElementById("passwordError").innerHTML = "Password must be at least 6 characters long";
        valid = false;
    }

    if (!valid) {
        event.preventDefault();
    }
}
</script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
