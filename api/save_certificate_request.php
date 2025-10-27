<?php
/**
 * Certificate Request API - FINAL WORKING VERSION
 * Compatible with certificate_requests table structure
 * Date: 2025-10-27
 */

header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Start output buffering to catch any headers
ob_start();

try {
    // Load requirements
    require_once __DIR__ . '/../config/db_config.php';
    require_once __DIR__ . '/../controllers/AuthController.php';
    
    // Start session if needed
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Require authentication
    AuthController::requireAuth();
    
    // Get user ID
    $user_id = $_SESSION['user_id'] ?? '';
    if (empty($user_id)) {
        throw new Exception('User not authenticated');
    }
    
    // Get POST data
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        throw new Exception('Invalid JSON input');
    }
    
    // Get purpose
    $purpose = trim($input['purpose'] ?? '');
    if (empty($purpose) || strlen($purpose) < 5) {
        throw new Exception('Purpose is required (minimum 5 characters)');
    }
    
    // Get database connection
    $conn = getDbConnection();
    if (!$conn) {
        throw new Exception('Database connection failed');
    }
    
    // Step 1: Get employee information
    $sql_employee = "SELECT 
                        e.employee_id,
                        CONCAT(COALESCE(p.prefix_name_th, ''), ' ', COALESCE(e.full_name_th, '')) as full_name,
                        COALESCE(pos.position_name_th, '') as position_name,
                        COALESCE(d.division_name_th, '') as division_name,
                        e.date_of_hire,
                        COALESCE(h.type_name_th, '') as hiring_type_name,
                        e.base_salary
                    FROM employees e
                    LEFT JOIN prefix_master p ON e.prefix_id = p.prefix_id
                    LEFT JOIN position_master pos ON e.position_id = pos.position_id
                    LEFT JOIN division_master d ON e.division_id = d.division_id
                    LEFT JOIN hiring_type_master h ON e.hiring_type_id = h.hiring_type_id
                    WHERE e.employee_id = ?
                    LIMIT 1";
    
    $stmt = $conn->prepare($sql_employee);
    if (!$stmt) {
        throw new Exception('Database error: ' . $conn->error);
    }
    
    $stmt->bind_param('s', $user_id);
    if (!$stmt->execute()) {
        throw new Exception('Database query error: ' . $stmt->error);
    }
    
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        throw new Exception('Employee record not found');
    }
    
    $employee = $result->fetch_assoc();
    $stmt->close();
    
    // Step 2: Generate certificate number
    $cert_no = 'CERT-' . date('Ymd') . '-' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
    
    // Step 3: Insert certificate request
    $sql_insert = "INSERT INTO certificate_requests 
                    (certificate_no, employee_id, employee_name, position, division, 
                     date_of_hire, hiring_type, base_salary, purpose, status, created_at, updated_at)
                   VALUES 
                    (?, ?, ?, ?, ?, ?, ?, ?, ?, 'New', NOW(), NOW())";
    
    $stmt_insert = $conn->prepare($sql_insert);
    if (!$stmt_insert) {
        throw new Exception('Insert prepare error: ' . $conn->error);
    }
    
    // Prepare values
    $employee_name = $employee['full_name'] ?? '';
    $position = $employee['position_name'] ?? '';
    $division = $employee['division_name'] ?? '';
    $date_of_hire = $employee['date_of_hire'] ?? null;
    $hiring_type = $employee['hiring_type_name'] ?? '';
    $base_salary = (float)($employee['base_salary'] ?? 0);
    
    // Bind parameters: 9 total (cert_no, employee_id, employee_name, position, division, date_of_hire, hiring_type, base_salary, purpose)
    if (!$stmt_insert->bind_param('sssssssds', $cert_no, $user_id, $employee_name, $position, $division, $date_of_hire, $hiring_type, $base_salary, $purpose)) {
        throw new Exception('Bind parameter error: ' . $stmt_insert->error);
    }
    
    // Execute insert
    if (!$stmt_insert->execute()) {
        throw new Exception('Insert execution error: ' . $stmt_insert->error);
    }
    
    $request_id = $conn->insert_id;
    $stmt_insert->close();
    $conn->close();
    
    // Clear output buffer
    ob_end_clean();
    
    // Success response
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Certificate request submitted successfully',
        'cert_no' => $cert_no,
        'request_id' => $request_id
    ]);
    
} catch (Exception $e) {
    // Clear output buffer
    ob_end_clean();
    
    // Error response
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    
}
?>