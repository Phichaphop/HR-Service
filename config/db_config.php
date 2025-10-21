<?php
/**
 * Database Configuration File
 * HR Service Application
 */

// Database Connection Parameters
define('DB_SERVER', 'localhost'); //sql206.infinityfree.com
define('DB_NAME', 'if0_39800794_db');
define('DB_USER', 'root'); //if0_39800794
define('DB_PASS', ''); //Hmaf0JJFMfHcK8h
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
define('UPLOAD_MAX_SIZE', 5242880); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif']);
define('ALLOWED_DOC_TYPES', ['application/pdf']);
define('UPLOAD_PATH_PROFILE', __DIR__ . '/../uploads/profiles/');
define('UPLOAD_PATH_DOCUMENTS', __DIR__ . '/../uploads/documents/');
define('UPLOAD_PATH_COMPANY', __DIR__ . '/../uploads/company/');

// Session Settings
define('SESSION_TIMEOUT', 3600); // 1 hour

// Security Settings
define('SUPER_ADMIN_CODE', 'HRSA2024');

// Timezone
date_default_timezone_set('Asia/Bangkok');

// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

/**
 * Get Database Connection
 * @return mysqli|false
 */
function getDbConnection() {
    $conn = @new mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME);

    if ($conn->connect_errno) {
        // ถ้าไม่พบฐานข้อมูล ให้ redirect ไปหน้าสร้าง
        if ($conn->connect_errno == 1049) { // Unknown database
            header("Location: " . BASE_URL . "/setup_database.php");
            exit();
        }

        // ข้อผิดพลาดอื่น ๆ
        die("❌ Database connection failed: " . $conn->connect_error);
    }

    $conn->set_charset(DB_CHARSET);
    return $conn;
}

/**
 * Check if database exists
 */
function checkDatabaseExists() {
    $conn = @new mysqli(DB_SERVER, DB_USER, DB_PASS);

    if ($conn->connect_errno) {
        die("❌ Database connection failed: " . $conn->connect_error);
    }

    $result = $conn->query("SHOW DATABASES LIKE '" . DB_NAME . "'");
    $exists = $result && $result->num_rows > 0;

    $conn->close();
    return $exists;
}

/**
 * Check if main tables exist
 */
function checkTablesExist() {
    $conn = getDbConnection();

    if (!$conn) {
        return false;
    }

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
 * Create directories if missing
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

// เพิ่มบรรทัดนี้ที่ด้านบนของไฟล์ (ลบทิ้งเมื่อแก้เสร็จ)
error_reporting(E_ALL);
ini_set('display_errors', 0);  // ← เปลี่ยนเป็น 0
ini_set('log_errors', 1);
?>
