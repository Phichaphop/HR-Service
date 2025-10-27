<?php
/**
 * API: Update Employee Salary
 * File: api/update_employee_salary.php
 * 
 * Update employee base salary and hiring type for certificate generation
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
    
    $employee_id = $data['employee_id'] ?? '';
    $base_salary = floatval($data['base_salary'] ?? 0);
    $hiring_type_id = intval($data['hiring_type_id'] ?? 0);
    
    // Validate input
    if (empty($employee_id)) {
        throw new Exception('Employee ID is required');
    }
    
    if ($base_salary <= 0) {
        throw new Exception('Salary must be greater than 0');
    }
    
    // Connect to database
    $conn = getDbConnection();
    if (!$conn) {
        throw new Exception('Database connection failed');
    }
    
    // Update employee data
    $sql = "UPDATE employees 
            SET base_salary = ?, 
                hiring_type_id = ?,
                updated_at = CURRENT_TIMESTAMP 
            WHERE employee_id = ?";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    
    $stmt->bind_param("dis", $base_salary, $hiring_type_id, $employee_id);
    
    if (!$stmt->execute()) {
        throw new Exception('Execute failed: ' . $stmt->error);
    }
    
    $affected_rows = $stmt->affected_rows;
    $stmt->close();
    
    if ($affected_rows === 0) {
        throw new Exception('Employee not found or no changes made');
    }
    
    // Get updated data
    $select_sql = "SELECT 
                    e.employee_id,
                    e.base_salary,
                    e.hiring_type_id,
                    ht.type_name_th,
                    ht.type_name_en,
                    ht.type_name_my
                FROM employees e
                LEFT JOIN hiring_type_master ht ON e.hiring_type_id = ht.type_id
                WHERE e.employee_id = ?";
    
    $select_stmt = $conn->prepare($select_sql);
    $select_stmt->bind_param("s", $employee_id);
    $select_stmt->execute();
    $result = $select_stmt->get_result();
    $updated_data = $result->fetch_assoc();
    $select_stmt->close();
    
    $conn->close();
    
    // Success response
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Employee salary updated successfully',
        'data' => $updated_data
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    error_log('update_employee_salary.php error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>