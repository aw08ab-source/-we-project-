<?php
// includes/database.php
$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'university_system';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", 
                   $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>