<?php
/**
 * Database Configuration File
 * HR Service Application
 * 
 * Database: if0_39800794_db (Local: localhost | Remote: infinityfree)
 * Character Set: utf8mb4
 * Timezone: Asia/Bangkok
 */

// ============================================================================
// DATABASE CONNECTION PARAMETERS
// ============================================================================

// Local Development (XAMPP/WAMP/MAMP)
define('DB_SERVER', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');  // Empty password for local development
define('DB_NAME', 'if0_39800794_db');
define('DB_CHARSET', 'utf8mb4');

// Remote Production (InfinityFree) - Uncomment to use
// define('DB_SERVER', 'sql206.infinityfree.com');
// define('DB_USER', 'if0_39800794');
// define('DB_PASS', 'Hmaf0JJFMfHcK8h');
// define('DB_NAME', 'if0_39800794_db');
// define('DB_CHARSET', 'utf8mb4');

// ============================================================================
// EMAIL CONFIGURATION (SMTP)
// ============================================================================

define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'traxintertrade.hrss@gmail.com');
define('SMTP_PASSWORD', '');  // ⚠️ Set this in production (use app password, not Gmail password)
define('SMTP_FROM_EMAIL', 'traxintertrade.hrss@gmail.com');
define('SMTP_FROM_NAME', 'HR Service System');

// ============================================================================
// APPLICATION CONFIGURATION
// ============================================================================

define('APP_NAME', 'HR Service');
define('APP_VERSION', '1.0.0');
define('DEFAULT_LANGUAGE', 'th');  // th, en, my
define('DEFAULT_THEME_MODE', 'light');  // light or dark

// Base Path - CRITICAL: Adjust based on your project folder
// Example:
//   If project is at: http://localhost/HR-Service
//   Set BASE_PATH to: '/HR-Service'
//
//   If project is at: http://localhost
//   Set BASE_PATH to: ''
define('BASE_PATH', '/HR-Service');
define('BASE_URL', 'http://localhost' . BASE_PATH);

// ============================================================================
// SECURITY SETTINGS ⚠️ CRITICAL
// ============================================================================

/**
 * SUPER_ADMIN_CODE - Required to access db_manager.php
 * 
 * Default: HRSA2024
 * 
 * ⚠️ CHANGE THIS IN PRODUCTION!
 * 
 * This code provides:
 * - Access to database management page (db_manager.php)
 * - Ability to create/drop database
 * - Ability to create/drop tables
 * - Ability to seed data
 * 
 * Keep this code SECURE and CONFIDENTIAL!
 * Change it after first login to prevent unauthorized access.
 */
define('SUPER_ADMIN_CODE', 'HRSA2024');

/**
 * Alternative: Use environment variable (more secure for production)
 * 
 * In production, set environment variable:
 *   export SUPER_ADMIN_CODE="your_secure_code_here"
 * 
 * Then uncomment this:
 * define('SUPER_ADMIN_CODE', getenv('SUPER_ADMIN_CODE') ?: 'HRSA2024');
 */

// ============================================================================
// FILE UPLOAD SETTINGS
// ============================================================================

// Maximum file size: 5MB (5242880 bytes)
define('UPLOAD_MAX_SIZE', 5242880);

// Allowed image types for profile pictures
define('ALLOWED_IMAGE_TYPES', [
    'image/jpeg',
    'image/png',
    'image/gif'
]);

// Allowed document types
define('ALLOWED_DOC_TYPES', [
    'application/pdf'
]);

// Upload directory paths
define('UPLOAD_PATH', __DIR__ . '/../uploads');
define('UPLOAD_PATH_PROFILE', UPLOAD_PATH . '/profiles/');
define('UPLOAD_PATH_DOCUMENTS', UPLOAD_PATH . '/documents/');
define('UPLOAD_PATH_COMPANY', UPLOAD_PATH . '/company/');

// ============================================================================
// SESSION CONFIGURATION
// ============================================================================

// Session timeout: 1 hour (3600 seconds)
define('SESSION_TIMEOUT', 3600);

// Session name
define('SESSION_NAME', 'HR_SERVICE_SESSION');

// ============================================================================
// TIMEZONE
// ============================================================================

date_default_timezone_set('Asia/Bangkok');  // Thailand timezone

// ============================================================================
// ERROR REPORTING & LOGGING
// ============================================================================

// Report all PHP errors
error_reporting(E_ALL);

// Do NOT display errors on page (prevent information leakage in production)
ini_set('display_errors', 0);

// DO log errors to file
ini_set('log_errors', 1);

// Log file location
ini_set('error_log', __DIR__ . '/../logs/error.log');

// Create logs directory if it doesn't exist
if (!file_exists(__DIR__ . '/../logs')) {
    mkdir(__DIR__ . '/../logs', 0755, true);
}

// ============================================================================
// HELPER FUNCTIONS
// ============================================================================

/**
 * Get Database Connection
 * 
 * @return mysqli|false The database connection or false on failure
 */
function getDbConnection() {
    static $conn = null;
    
    // Return cached connection if already exists
    if ($conn !== null) {
        return $conn;
    }
    
    // Create new connection
    $conn = @new mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
    
    // Check for connection errors
    if ($conn->connect_errno) {
        error_log("Database Connection Error: " . $conn->connect_error);
        
        // If database not found, redirect to setup page
        if ($conn->connect_errno == 1049) {  // Unknown database error
            header("Location: " . BASE_URL . "/views/admin/db_manager.php");
            exit();
        }
        
        // Other connection errors
        die("❌ Database Connection Failed: " . $conn->connect_error);
    }
    
    // Set character set
    $conn->set_charset(DB_CHARSET);
    
    return $conn;
}

/**
 * Check if database exists
 * 
 * @return bool True if database exists, false otherwise
 */
function checkDatabaseExists() {
    $conn = @new mysqli(DB_SERVER, DB_USER, DB_PASS);
    
    if ($conn->connect_errno) {
        error_log("Database Check Error: " . $conn->connect_error);
        return false;
    }
    
    $result = $conn->query("SHOW DATABASES LIKE '" . DB_NAME . "'");
    $exists = $result && $result->num_rows > 0;
    
    $conn->close();
    return $exists;
}

/**
 * Check if critical tables exist
 * 
 * @return bool True if all critical tables exist, false otherwise
 */
function checkTablesExist() {
    // Try to get connection
    $conn = @new mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_errno) {
        return false;
    }
    
    // Check for critical tables
    $critical_tables = [
        'roles',
        'employees',
        'localization_master'
    ];
    
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
 * Create upload directories if they don't exist
 * 
 * @return void
 */
function createUploadDirectories() {
    $dirs = [
        UPLOAD_PATH,
        UPLOAD_PATH_PROFILE,
        UPLOAD_PATH_DOCUMENTS,
        UPLOAD_PATH_COMPANY
    ];
    
    foreach ($dirs as $dir) {
        if (!file_exists($dir)) {
            if (!mkdir($dir, 0755, true)) {
                error_log("Failed to create directory: $dir");
            }
        }
    }
}

/**
 * Get current logged-in user ID from session
 * 
 * @return string|null User ID if logged in, null otherwise
 */
function getCurrentUserId() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current logged-in user's role
 * 
 * @return string|null User role (admin, officer, employee) or null
 */
function getCurrentUserRole() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return $_SESSION['user_role'] ?? null;
}

/**
 * Check if user is logged in
 * 
 * @return bool True if user is logged in, false otherwise
 */
function isUserLoggedIn() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return isset($_SESSION['user_id']) && isset($_SESSION['user_role']);
}

/**
 * Sanitize output to prevent XSS
 * 
 * @param string $data The data to sanitize
 * @return string Sanitized HTML
 */
function sanitize($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

/**
 * Log activity
 * 
 * @param string $action The action performed
 * @param string $details Additional details
 * @return void
 */
function logActivity($action, $details = '') {
    $log_dir = __DIR__ . '/../logs';
    if (!file_exists($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    
    $log_file = $log_dir . '/activity.log';
    $user_id = getCurrentUserId() ?: 'UNKNOWN';
    $timestamp = date('Y-m-d H:i:s');
    
    $log_entry = "[$timestamp] User: $user_id | Action: $action | Details: $details\n";
    
    file_put_contents($log_file, $log_entry, FILE_APPEND);
}

// ============================================================================
// INITIALIZATION
// ============================================================================

// Create upload directories on config load
createUploadDirectories();

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_start();
}

// Include other config files if needed
if (file_exists(__DIR__ . '/session_helper.php')) {
    require_once __DIR__ . '/session_helper.php';
}

// ============================================================================
// CONFIGURATION VERIFICATION
// ============================================================================

// Optional: Add startup checks (comment out if not needed)
if (php_sapi_name() === 'cli' || defined('RUNNING_TESTS')) {
    // Running from command line or tests
    // Skip startup checks
} else {
    // Running from web server
    // Optionally verify configuration
    if (defined('DEBUG_CONFIG') && DEBUG_CONFIG) {
        error_log("Configuration loaded successfully");
        error_log("Database: " . DB_NAME);
        error_log("Base URL: " . BASE_URL);
    }
}

// ============================================================================
// END OF CONFIGURATION FILE
// ============================================================================
?>