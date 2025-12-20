<?php
// config.php - WORKING VERSION

$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'university_system';

// Function to setup database
function setupDatabase() {
    global $host, $username, $password, $dbname;
    
    try {
        // Connect without database
        $pdo = new PDO("mysql:host=$host", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Create database if not exists
        $pdo->exec("CREATE DATABASE IF NOT EXISTS $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE $dbname");
        
        // Read and execute SQL file
        $sqlFile = __DIR__ . '/db.sql';
        if (file_exists($sqlFile)) {
            $sql = file_get_contents($sqlFile);
            
            // Remove CREATE DATABASE and USE statements from SQL file
            $sql = str_ireplace("CREATE DATABASE IF NOT EXISTS university_system;", "", $sql);
            $sql = str_ireplace("USE university_system;", "", $sql);
            
            // Execute remaining SQL
            $queries = explode(';', $sql);
            foreach ($queries as $query) {
                $query = trim($query);
                if (!empty($query) && strlen($query) > 5) {
                    $pdo->exec($query);
                }
            }
        }
        
        return new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        
    } catch(PDOException $e) {
        die("Database setup failed: " . $e->getMessage());
    }
}

// Main connection logic
try {
    // Try to connect to existing database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Test if tables exist
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tables)) {
        // No tables, run setup
        $pdo = setupDatabase();
    }
    
} catch(PDOException $e) {
    // Database doesn't exist, run setup
    $pdo = setupDatabase();
}

// Set error mode
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Helper functions
function getUserById($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function verifyLogin($email, $password) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND password = ?");
    $stmt->execute([$email, $password]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Debug: Uncomment to see if it's working
// echo "<!-- Database connected successfully -->";
?>