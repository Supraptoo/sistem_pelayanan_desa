<?php
// config/database.php

/**
 * Database Configuration for Desa Winduaji
 * 
 * Secure and optimized database connection settings with enhanced security
 */

// Environment detection (development/production)
define('ENVIRONMENT', 'development'); // Change to 'production' when deployed

// Error reporting configuration
if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', __DIR__ . '/../logs/php_errors.log');
}

// Database connection parameters
define('DB_HOST', 'localhost');
define('DB_NAME', 'desa_winduaji');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATION', 'utf8mb4_unicode_ci');
define('DB_TIMEOUT', 30);


/**
 * Establish secure database connection using PDO with singleton pattern
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
            PDO::ATTR_TIMEOUT            => DB_TIMEOUT,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ];
        
        try {
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=%s',
                DB_HOST,
                DB_NAME,
                DB_CHARSET
            );
            
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $pdo_options);
            
            // Set timezone & strict mode
            $pdo->exec("SET time_zone = '+07:00'");
            $pdo->exec("SET SQL_MODE = 'STRICT_ALL_TABLES'");
            
        } catch (PDOException $e) {
            error_log('[' . date('Y-m-d H:i:s') . '] Database connection error: ' . $e->getMessage());
            
            if (ENVIRONMENT === 'production') {
                if (file_exists(__DIR__ . '/../maintenance.html')) {
                    readfile(__DIR__ . '/../maintenance.html');
                } else {
                    header('HTTP/1.1 503 Service Unavailable');
                    die('Maaf, sistem sedang mengalami gangguan. Silakan coba lagi nanti.');
                }
            } else {
                die('Database Error: ' . htmlspecialchars($e->getMessage()));
            }
            exit;
        }
    }
    
    return $pdo;
}

// Helper function for prepared statements
function db_prepare(string $sql): PDOStatement
{
    try {
        return get_db_connection()->prepare($sql);
    } catch (PDOException $e) {
        error_log('[' . date('Y-m-d H:i:s') . '] Database prepare error: ' . $e->getMessage());
        throw $e;
    }
}

// Establish connection immediately (optional)
try {
    $pdo = get_db_connection();
    $pdo->query("SELECT 1")->fetch(); // Test connection
} catch (PDOException $e) {
    error_log('[' . date('Y-m-d H:i:s') . '] Failed to connect to database: ' . $e->getMessage());
    if (ENVIRONMENT === 'production') {
        header('HTTP/1.1 503 Service Unavailable');
        die('Database connection failed. Please try again later.');
    } else {
        die('Database Connection Error: ' . htmlspecialchars($e->getMessage()));
    }
}


/**
 * Tambahan: Class Database dengan method getConnection()
 * (bisa dipakai alternatif OOP)
 */
class Database {
    private $host = DB_HOST;
    private $db_name = DB_NAME;
    private $username = DB_USER;
    private $password = DB_PASS;
    private $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }

        return $this->conn;
    }
}
