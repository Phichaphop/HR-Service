<?php
/**
 * API: Admin Get Request Details - FIXED VERSION
 * File: api/admin_get_request_details.php
 * Purpose: Fetch full request details including certificate type information
 * Updated: Include certificate_types JOIN
 */

// Start session FIRST before any output
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Include required files
require_once __DIR__ . '/../config/db_config.php';

// ========== AUTHENTICATION & AUTHORIZATION ==========
try {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Not authenticated'
        ]);
        exit();
    }
    
    // Get user role - check multiple possible session keys
    $user_role = null;
    if (isset($_SESSION['user_role'])) {
        $user_role = $_SESSION['user_role'];
    } elseif (isset($_SESSION['role'])) {
        $user_role = $_SESSION['role'];
    }
    
    // If no role in session, get from database
    if (!$user_role) {
        $conn = getDbConnection();
        if ($conn) {
            $user_id = $_SESSION['user_id'];
            $stmt = $conn->prepare("SELECT r.role_name FROM employees e 
                                   LEFT JOIN roles r ON e.role_id = r.role_id 
                                   WHERE e.employee_id = ?");
            if ($stmt) {
                $stmt->bind_param("s", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($row = $result->fetch_assoc()) {
                    $user_role = strtolower($row['role_name']);
                    $_SESSION['user_role'] = $user_role;
                }
                $stmt->close();
            }
            $conn->close();
        }
    }
    
    // Normalize role name
    $user_role = strtolower(trim($user_role ?? ''));
    
    // Check if user has permission (admin/officer)
    $allowed_roles = ['admin', 'officer', 'administrator'];
    
    if (!in_array($user_role, $allowed_roles)) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Access denied - Admin/Officer only'
        ]);
        exit();
    }
    
    // ========== GET PARAMETERS ==========
    $table = $_GET['table'] ?? '';
    $request_id = intval($_GET['id'] ?? 0);
    $current_lang = $_SESSION['language'] ?? 'th';
    
    // Validate parameters
    if (empty($table) || $request_id <= 0) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid parameters'
        ]);
        exit();
    }
    
    // Validate table name (security)
    $allowed_tables = [
        'leave_requests', 'certificate_requests', 'id_card_requests',
        'shuttle_bus_requests', 'locker_requests', 'supplies_requests',
        'skill_test_requests', 'document_submissions'
    ];
    
    if (!in_array($table, $allowed_tables)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid table name'
        ]);
        exit();
    }
    
    // ========== DATABASE QUERY ==========
    $conn = getDbConnection();
    if (!$conn) {
        throw new Exception('Database connection failed');
    }
    
    // Determine primary key
    $id_column = ($table === 'document_submissions') ? 'submission_id' : 'request_id';
    
    // Build query with COALESCE to handle NULL values
    // Add JOIN for certificate_types if it's a certificate request
    $sql_base = "SELECT 
                r.*,
                e.employee_id as emp_id,
                COALESCE(e.full_name_th, '') as full_name_th,
                COALESCE(e.full_name_en, '') as full_name_en,
                e.position_id,
                e.division_id,
                COALESCE(p.position_name_th, '') as position_name_th,
                COALESCE(p.position_name_en, '') as position_name_en,
                COALESCE(d.division_name_th, '') as division_name_th,
                COALESCE(d.division_name_en, '') as division_name_en";
    
    // Additional fields for certificate requests
    if ($table === 'certificate_requests') {
        $sql_base .= ",
                COALESCE(ct.cert_type_id, '') as cert_type_id,
                COALESCE(ct.type_name_th, '') as cert_type_name_th,
                COALESCE(ct.type_name_en, '') as cert_type_name_en,
                COALESCE(ct.type_name_my, '') as cert_type_name_my";
    }
    
    $sql_base .= " FROM $table r
                LEFT JOIN employees e ON r.employee_id = e.employee_id
                LEFT JOIN position_master p ON e.position_id = p.position_id
                LEFT JOIN division_master d ON e.division_id = d.division_id";
    
    // Add JOIN for certificate_types if needed
    if ($table === 'certificate_requests') {
        $sql_base .= " LEFT JOIN certificate_types ct ON r.cert_type_id = ct.cert_type_id";
    }
    
    $sql_base .= " WHERE r.$id_column = ?";
    
    $sql = $sql_base;
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Query preparation failed: ' . $conn->error);
    }
    
    $stmt->bind_param("i", $request_id);
    
    if (!$stmt->execute()) {
        throw new Exception('Query execution failed: ' . $stmt->error);
    }
    
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    if (!$row) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Request not found'
        ]);
        exit();
    }
    
    // Ensure request_id exists for consistency
    if (!isset($row['request_id']) && isset($row['submission_id'])) {
        $row['request_id'] = $row['submission_id'];
    }
    
    // Clean null values and add extra fields
    foreach ($row as $key => $value) {
        if ($value === null) {
            $row[$key] = '';
        }
    }
    
    // ========== ADDITIONAL DATA PROCESSING ==========
    // For certificate requests, add certificate type display name
    if ($table === 'certificate_requests') {
        if ($current_lang === 'en') {
            $row['cert_type_display'] = $row['cert_type_name_en'] ?: ($row['cert_type_name_th'] ?? 'N/A');
        } elseif ($current_lang === 'my') {
            $row['cert_type_display'] = $row['cert_type_name_my'] ?: ($row['cert_type_name_th'] ?? 'N/A');
        } else {
            $row['cert_type_display'] = $row['cert_type_name_th'] ?? 'N/A';
        }
    }
    
    // Success response
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'request' => $row,
        'table' => $table,
        'lang' => $current_lang
    ], JSON_UNESCAPED_UNICODE);
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    error_log('admin_get_request_details.php error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?>