<?php
/**
 * API Initialization
 * ใช้สำหรับ API endpoints เพื่อป้องกัน HTML output
 */

// Disable error display (errors will only go to log)
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);

// But still report errors to log
error_reporting(E_ALL);

// Start output buffering to catch any unwanted output
ob_start();

// Set JSON header
header('Content-Type: application/json; charset=utf-8');

// Ensure no session output
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_cookies', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.use_trans_sid', 0);
}

/**
 * Clean JSON Response
 * Clears buffer and outputs clean JSON
 */
function sendJSON($data, $status_code = 200) {
    http_response_code($status_code);
    
    // Clear any output buffer
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    
    // Send JSON with UTF-8 support
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit();
}

/**
 * Error handler for API
 */
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    // Log the error
    error_log("API Error: [$errno] $errstr in $errfile on line $errline");
    
    // Don't output anything - let it be caught in error log only
    return true;
});

/**
 * Exception handler for API
 */
set_exception_handler(function($exception) {
    error_log("API Exception: " . $exception->getMessage());
    
    sendJSON([
        'success' => false,
        'message' => 'An unexpected error occurred',
        'error' => $exception->getMessage()
    ], 500);
});
?>