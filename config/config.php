<?php
// config/config.php - Enhanced Database Configuration

/**
 * Load environment variables from .env file
 * Like reading a secret recipe book
 */
function loadEnv($filePath) {
    if (!file_exists($filePath)) {
        throw new Exception("Plik .env nie istnieje: $filePath");
    }
    
    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($lines as $line) {
        // Skip comments (lines starting with #)
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        // Process key=value pairs
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Remove quotes from values
            $value = trim($value, '"\'');
            
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }
}

try {
    // Load environment variables (like opening the recipe book)
    loadEnv(__DIR__ . '/.env');
} catch (Exception $e) {
    // If .env file doesn't exist, use default values
    error_log("Warning: " . $e->getMessage());
}

// Database configuration from environment variables
$host = $_ENV['DB_HOST'] ?? 'localhost';
$dbname = $_ENV['DB_DATABASE'] ?? 'c2_panel';
$username = $_ENV['DB_USERNAME'] ?? 'root';
$password = $_ENV['DB_PASSWORD'] ?? '';
$port = $_ENV['DB_PORT'] ?? 3306;

/**
 * Get database connection with enhanced security
 * Like opening a secure phone line to the database
 */
function getDBConnection() {
    global $host, $dbname, $username, $password, $port;
    static $pdo = null; // Keep connection alive (singleton pattern)
    
    if ($pdo === null) {
        try {
            // Create PDO connection with security options
            $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => false, // Don't use persistent connections
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ];
            
            $pdo = new PDO($dsn, $username, $password, $options);
            
            // Optional: Enable query logging in development
            if (defined('DEBUG') && DEBUG) {
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
            }
            
        } catch(PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            
            // In production, don't reveal database details
            if (defined('DEBUG') && DEBUG) {
                throw new Exception("Database connection failed: " . $e->getMessage());
            } else {
                throw new Exception("Database connection failed. Please contact administrator.");
            }
        }
    }
    
    return $pdo;
}

/**
 * Get all computers from database
 * Like getting a list of all classroom computers
 */
function getComputers() {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("
            SELECT computer_id, computer_name, owner_name, ip_address 
            FROM computers 
            ORDER BY computer_name
        ");
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (Exception $e) {
        error_log("Error fetching computers: " . $e->getMessage());
        return [];
    }
}

/**
 * Get specific computer by ID
 * Like finding a specific classroom computer
 */
function getComputer($computerId) {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("
            SELECT computer_id, computer_name, owner_name, ip_address 
            FROM computers 
            WHERE computer_id = ?
        ");
        $stmt->execute([$computerId]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
        
    } catch (Exception $e) {
        error_log("Error fetching computer: " . $e->getMessage());
        return null;
    }
}

/**
 * Check database connection and tables
 * Like checking if the school database is working
 */
function checkDatabaseHealth() {
    try {
        $pdo = getDBConnection();
        
        // Check if required tables exist
        $requiredTables = ['users', 'computers', 'user_sessions', 'command_logs'];
        $existingTables = [];
        
        $stmt = $pdo->query("SHOW TABLES");
        while ($table = $stmt->fetchColumn()) {
            $existingTables[] = $table;
        }
        
        $missingTables = array_diff($requiredTables, $existingTables);
        
        if (!empty($missingTables)) {
            throw new Exception("Missing database tables: " . implode(', ', $missingTables));
        }
        
        return true;
        
    } catch (Exception $e) {
        error_log("Database health check failed: " . $e->getMessage());
        return false;
    }
}

// Define constants for development/production
define('DEBUG', $_ENV['DEBUG'] ?? false);
define('APP_NAME', 'C2 Panel');
define('APP_VERSION', '1.0.0');

// Initialize database health check (optional - uncomment if needed)
// if (!checkDatabaseHealth()) {
//     die('Database configuration error. Please check logs.');
// }
?>