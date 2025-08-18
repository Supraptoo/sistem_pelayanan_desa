<?php

// config/database.php

/**
 * Database Configuration for Desa Winduaji
 * 
 * Secure and optimized database connection settings
 */

// Error reporting for development (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Database connection parameters
define('DB_HOST', 'localhost');
define('DB_NAME', 'desa_winduaji');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATION', 'utf8mb4_unicode_ci');

/**
 * Establish database connection using PDO
 * 
 * @return PDO Returns PDO database connection object
 * @throws PDOException If connection fails
 */
function get_db_connection(): PDO
{
    static $pdo = null;
    
    if ($pdo === null) {
        // PDO connection options
        $pdo_options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_PERSISTENT         => false,
            PDO::ATTR_TIMEOUT            => 30
        ];
        
        try {
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=%s',
                DB_HOST,
                DB_NAME,
                DB_CHARSET
            );
            
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $pdo_options);
            
            // Set timezone if needed
            $pdo->exec("SET time_zone = '+07:00'");
            
        } catch (PDOException $e) {
            error_log('Database connection error: ' . $e->getMessage());
            
            // Custom error page or message for production
            if (file_exists(__DIR__ . '/../maintenance.html')) {
                readfile(__DIR__ . '/../maintenance.html');
            } else {
                die('Maaf, sistem sedang mengalami gangguan. Silakan coba lagi nanti.');
            }
            exit;
        }
    }
    
    return $pdo;
}

// Establish connection immediately (optional)
try {
    $pdo = get_db_connection();
} catch (PDOException $e) {
    // Handle connection failure
    error_log('Failed to connect to database: ' . $e->getMessage());
    die('Database connection failed. Please try again later.');
}