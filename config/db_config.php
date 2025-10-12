<?php
/**
 * Database Configuration File
 * HR Service Application
 */

// Database Connection Parameters
define('DB_SERVER', 'localhost');
define('DB_NAME', 'db_hr_service');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// SMTP Configuration for Email
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'traxintertrade.hrss@gmail.com');
define('SMTP_PASSWORD', ''); // Set this securely
define('SMTP_FROM_EMAIL', 'traxintertrade.hrss@gmail.com');
define('SMTP_FROM_NAME', 'HR Service System');

// Application Settings
define('APP_NAME', 'HR Service');
define('APP_VERSION', '1.0.0');
define('DEFAULT_LANGUAGE', 'th');
define('DEFAULT_THEME_MODE', 'light'); // light or dark

// Base Path Configuration (Change this if your folder name is different)
define('BASE_PATH', '/HR-Service'); // Change to your folder name or '' for root
define('BASE_URL', 'http://localhost' . BASE_PATH);

// File Upload Settings
define('UPLOAD_MAX_SIZE', 5242880); // 5MB in bytes
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif']);
define('ALLOWED_DOC_TYPES', ['application/pdf']);
define('UPLOAD_PATH_PROFILE', __DIR__ . '/../uploads/profiles/');
define('UPLOAD_PATH_DOCUMENTS', __DIR__ . '/../uploads/documents/');
define('UPLOAD_PATH_COMPANY', __DIR__ . '/../uploads/company/');

// Session Settings
define('SESSION_TIMEOUT', 3600); // 1 hour in seconds

// Security Settings
define('SUPER_ADMIN_CODE', 'HRSA2024'); // Change this to a secure code

// Timezone
date_default_timezone_set('Asia/Bangkok');

// Error Reporting (Change to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

/**
 * Get Database Connection
 * @return mysqli|false
 */
function getDbConnection() {
    $conn = new mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        return false;
    }
    
    $conn->set_charset(DB_CHARSET);
    return $conn;
}

/**
 * Check if database exists
 * @return bool
 */
function checkDatabaseExists() {
    $conn = new mysqli(DB_SERVER, DB_USER, DB_PASS);
    
    if ($conn->connect_error) {
        return false;
    }
    
    $result = $conn->query("SHOW DATABASES LIKE '" . DB_NAME . "'");
    $exists = $result && $result->num_rows > 0;
    
    $conn->close();
    return $exists;
}

/**
 * Check if main tables exist
 * @return bool
 */
function checkTablesExist() {
    $conn = getDbConnection();
    
    if (!$conn) {
        return false;
    }
    
    // Check for critical tables
    $critical_tables = ['roles', 'employees', 'localization_master'];
    
    foreach ($critical_tables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if (!$result || $result->num_rows === 0) {
            $conn->close();
            return false;
        }
    }
    
    $conn->close();
    return true;
}

/**
 * Create directory if it doesn't exist
 */
function createUploadDirectories() {
    $dirs = [
        UPLOAD_PATH_PROFILE,
        UPLOAD_PATH_DOCUMENTS,
        UPLOAD_PATH_COMPANY
    ];
    
    foreach ($dirs as $dir) {
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
    }
}

// Create upload directories on config load
createUploadDirectories();

// Include session helper
require_once __DIR__ . '/session_helper.php';
?>