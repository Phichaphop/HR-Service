<?php
/**
 * API: Update Certificate Request Salary
 * File: api/update_certificate_salary.php
 * 
 * Update base_salary for a specific certificate request
 */

session_start();
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once __DIR__ . '/../config/db_config.php';

try {
    // Check authentication
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Not authenticated');
    }
    
    // Get user role
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
    
    // Normalize role
    $user_role = strtolower(trim($user_role ?? ''));
    
    // Check authorization
    if (!in_array($user_role, ['admin', 'officer', 'administrator'])) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Access denied - Admin/Officer only'
        ]);
        exit();
    }
    
    // Get POST data
    $data = json_decode(file_get_contents('php://input'), true);
    
    $request_id = intval($data['request_id'] ?? 0);
    $base_salary = floatval($data['base_salary'] ?? 0);
    
    // Validate input
    if ($request_id <= 0) {
        throw new Exception('Request ID is required');
    }
    
    if ($base_salary <= 0) {
        throw new Exception('Salary must be greater than 0');
    }
    
    // Connect to database
    $conn = getDbConnection();
    if (!$conn) {
        throw new Exception('Database connection failed');
    }
    
    // Update certificate request
    $sql = "UPDATE certificate_requests 
            SET base_salary = ?,
                updated_at = CURRENT_TIMESTAMP 
            WHERE request_id = ?";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    
    $stmt->bind_param("di", $base_salary, $request_id);
    
    if (!$stmt->execute()) {
        throw new Exception('Execute failed: ' . $stmt->error);
    }
    
    $affected_rows = $stmt->affected_rows;
    $stmt->close();
    
    if ($affected_rows === 0) {
        throw new Exception('Certificate request not found or no changes made');
    }
    
    // Get updated data
    $select_sql = "SELECT 
                    cr.request_id,
                    cr.employee_id,
                    cr.base_salary,
                    cr.status,
                    cr.certificate_no,
                    e.full_name_th,
                    e.full_name_en
                FROM certificate_requests cr
                LEFT JOIN employees e ON cr.employee_id = e.employee_id
                WHERE cr.request_id = ?";
    
    $select_stmt = $conn->prepare($select_sql);
    $select_stmt->bind_param("i", $request_id);
    $select_stmt->execute();
    $result = $select_stmt->get_result();
    $updated_data = $result->fetch_assoc();
    $select_stmt->close();
    
    $conn->close();
    
    // Success response
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Certificate salary updated successfully',
        'data' => $updated_data
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    error_log('update_certificate_salary.php error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>