<?php
// includes/admin_auth.php

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in AND is admin
function isAdmin() {
    return isset($_SESSION['loggedin']) && 
           $_SESSION['loggedin'] === true && 
           isset($_SESSION['user_role']) && 
           $_SESSION['user_role'] === 'admin';
}

// Redirect non-admin users
function requireAdmin() {
    if (!isAdmin()) {
        $_SESSION['error'] = "Access denied. Admin privileges required.";
        header("Location: ../login.php");
        exit();
    }
}

// Get admin-only stats - COMPLETELY FIXED VERSION
function getAdminStats($pdo) {
    $stats = [
        'users_by_role' => [],
        'total_courses' => 0,
        'total_enrollments' => 0,
        'recent_users' => 0
    ];
    
    try {
        // User counts by role - SAFER QUERY
        $sql = "SELECT role, COUNT(*) as role_count FROM users GROUP BY role";
        $stmt = $pdo->query($sql);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($result) {
            $stats['users_by_role'] = $result;
        } else {
            // Fallback if no results
            $stats['users_by_role'] = [
                ['role' => 'admin', 'role_count' => 0],
                ['role' => 'teacher', 'role_count' => 0],
                ['role' => 'student', 'role_count' => 0]
            ];
        }
        
        // Total courses - SAFE QUERY
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM courses");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['total_courses'] = $result ? (int)$result['total'] : 0;
        
        // Total enrollments - SAFE QUERY
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM student_records");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['total_enrollments'] = $result ? (int)$result['total'] : 0;
        
        // Recent activity (last 7 days) - SAFE QUERY
        $sql = "SELECT COUNT(*) as recent FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
        $stmt = $pdo->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['recent_users'] = $result ? (int)$result['recent'] : 0;
        
    } catch(PDOException $e) {
        // Log error but don't crash
        error_log("Admin stats error: " . $e->getMessage());
        
        // Return default values
        $stats = [
            'users_by_role' => [
                ['role' => 'admin', 'role_count' => 0],
                ['role' => 'teacher', 'role_count' => 0],
                ['role' => 'student', 'role_count' => 0]
            ],
            'total_courses' => 0,
            'total_enrollments' => 0,
            'recent_users' => 0
        ];
    }
    
    return $stats;
}
?>