<?php
// Start the session securely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Function to check if a user is logged in
function isLoggedIn() {
    return isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
}

// Function to check if the logged-in user has a specific role
function hasRole($requiredRole) {
    return isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === $requiredRole;
}
?>
