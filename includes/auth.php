<?php
// Include database connection
require_once 'database.php';

function authenticateUser($identifier, $password, $role) {
    global $pdo; // Your connection variable from database.php

    // Map the form's role to the database's 'role' column
    // Ensure these values match your database ENUM ('admin','student','teacher')
    $dbRole = $role;

    // Query: Find user by ID AND role
    // We check the 'id' and 'role' columns. Using 'email' is also common.
    $sql = "SELECT id, name, password, role FROM users WHERE id = :identifier AND role = :role LIMIT 1";
    $stmt = $pdo->prepare($sql);

    // Bind parameters to prevent SQL injection[citation:1]
    $stmt->bindParam(':identifier', $identifier, PDO::PARAM_STR);
    $stmt->bindParam(':role', $dbRole, PDO::PARAM_STR);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // If a user was found AND the password is correct
    if ($user && $password === $user['password']) {
        // Return the user data (excluding the password)
        unset($user['password']);
        return $user;
    }

    // Authentication failed
    return false;
}
?>