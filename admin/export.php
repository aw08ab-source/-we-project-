<?php
require_once '../includes/database.php';
require_once '../includes/admin_auth.php';
requireAdmin();

$type = $_GET['type'] ?? 'all';

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="unitrack_' . $type . '_' . date('Y-m-d') . '.csv"');

$output = fopen('php://output', 'w');

switch($type) {
    case 'users':
        fputcsv($output, ['ID', 'Name', 'Email', 'Role', 'Created At']);
        $stmt = $pdo->query("SELECT * FROM users ORDER BY created_at");
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            fputcsv($output, $row);
        }
        break;
        
    case 'courses':
        fputcsv($output, ['Code', 'Title', 'Instructor', 'Credits', 'Seats', 'Created At']);
        $stmt = $pdo->query("SELECT * FROM courses ORDER BY code");
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            fputcsv($output, $row);
        }
        break;
        
    case 'enrollments':
        fputcsv($output, ['Student ID', 'Student Name', 'Course Code', 'Course Title', 'Grade', 'Recorded At']);
        $stmt = $pdo->query("
            SELECT sr.student_id, u.name as student_name, sr.course_code, 
                   c.title as course_title, sr.grade, sr.recorded_at
            FROM student_records sr
            JOIN users u ON sr.student_id = u.id
            JOIN courses c ON sr.course_code = c.code
            ORDER BY sr.recorded_at DESC
        ");
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            fputcsv($output, $row);
        }
        break;
        
    case 'all':
    default:
        // Export all tables
        $tables = ['users', 'courses', 'student_records'];
        foreach($tables as $table) {
            fputcsv($output, ["=== $table ==="]);
            $stmt = $pdo->query("SELECT * FROM $table");
            $columns = [];
            for ($i = 0; $i < $stmt->columnCount(); $i++) {
                $col = $stmt->getColumnMeta($i);
                $columns[] = $col['name'];
            }
            fputcsv($output, $columns);
            
            $stmt = $pdo->query("SELECT * FROM $table");
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                fputcsv($output, $row);
            }
            fputcsv($output, []); // Empty row between tables
        }
        break;
}

fclose($output);
exit();