<?php
/**
 * API: Get Employee Info
 * File: /api/get_employee_for_request.php
 * 
 * Used by admin_create_request.php for auto-fill employee data
 */

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../controllers/AuthController.php';

try {
    // Check authentication
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Not authenticated');
    }

    // Check role
    $user_role = $_SESSION['user_role'] ?? '';
    if (!in_array($user_role, ['admin', 'officer'])) {
        throw new Exception('Only Admin/Officer can access');
    }

    // Get employee ID
    $employee_id = trim($_GET['id'] ?? '');
    
    if (empty($employee_id)) {
        throw new Exception('Employee ID is required');
    }

    // Connect to database
    $conn = getDbConnection();
    if (!$conn) {
        throw new Exception('Database connection failed');
    }

    // Query employee data
    $sql = "SELECT 
        e.employee_id,
        e.full_name_th,
        e.full_name_en,
        pm.position_name_th,
        dm.department_name_th,
        div.division_name_th,
        sm.section_name_th
    FROM employees e
    LEFT JOIN position_master pm ON e.position_id = pm.position_id
    LEFT JOIN department_master dm ON e.department_id = dm.department_id
    LEFT JOIN division_master div ON e.division_id = div.division_id
    LEFT JOIN section_master sm ON e.section_id = sm.section_id
    WHERE e.employee_id = ? 
    LIMIT 1";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }

    $stmt->bind_param("s", $employee_id);
    
    if (!$stmt->execute()) {
        throw new Exception('Execute failed: ' . $stmt->error);
    }

    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Employee ID not found: ' . $employee_id);
    }

    $emp = $result->fetch_assoc();
    $stmt->close();
    $conn->close();

    // Return success response
    echo json_encode([
        'success' => true,
        'data' => [
            'employee_id' => $emp['employee_id'],
            'employee_name' => trim($emp['full_name_th'] . ' (' . $emp['full_name_en'] . ')'),
            'position' => $emp['position_name_th'] ?? '-',
            'department' => $emp['department_name_th'] ?? '-',
            'division' => $emp['division_name_th'] ?? '-',
            'section' => $emp['section_name_th'] ?? '-',
        ]
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}