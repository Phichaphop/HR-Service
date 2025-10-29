<?php
/**
 * ADMIN UPDATE REQUEST API
 * File: api/admin_update_request.php
 * 
 * Purpose: Update request status and additional data (e.g., salary for certificates)
 * Methods: POST
 * Required: request_id, table, status, handler_remarks, base_salary (for certificates)
 */

header('Content-Type: application/json');
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../controllers/AuthController.php';

// Require authentication
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

// Require admin or officer role
if (!in_array($_SESSION['role'] ?? '', ['admin', 'officer'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Access denied']);
    exit();
}

$request_id = isset($_POST['request_id']) ? (int)$_POST['request_id'] : 0;
$table_name = isset($_POST['table']) ? trim($_POST['table']) : '';
$status = isset($_POST['status']) ? trim($_POST['status']) : '';
$handler_remarks = isset($_POST['handler_remarks']) ? trim($_POST['handler_remarks']) : '';
$base_salary = isset($_POST['base_salary']) ? (float)$_POST['base_salary'] : null;

if (!$request_id || !$table_name || !$status) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing required parameters']);
    exit();
}

// Validate status
$valid_statuses = ['New', 'In Progress', 'Complete', 'Cancelled'];
if (!in_array($status, $valid_statuses)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid status']);
    exit();
}

// Validate table name
$allowed_tables = [
    'leave_requests',
    'certificate_requests',
    'id_card_requests',
    'shuttle_bus_requests',
    'locker_requests',
    'supplies_requests',
    'skill_test_requests',
    'document_submissions'
];

if (!in_array($table_name, $allowed_tables)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid table']);
    exit();
}

$conn = getDbConnection();

try {
    // Determine ID column
    $id_column = ($table_name === 'document_submissions') ? 'submission_id' : 'request_id';
    
    // Get current user ID
    $handler_id = $_SESSION['user_id'];

    // Update query
    $update_sql = "UPDATE $table_name SET 
        status = ?, 
        handler_id = ?, 
        handler_remarks = ?,
        updated_at = NOW()";
    
    $params = [$status, $handler_id, $handler_remarks];
    $types = 'sss';

    // Add base_salary update for certificate_requests
    if ($table_name === 'certificate_requests' && $base_salary !== null && $base_salary > 0) {
        $update_sql .= ", base_salary = ?";
        $params[] = $base_salary;
        $types .= 'd';
    }

    $update_sql .= " WHERE $id_column = ?";
    $params[] = $request_id;
    $types .= 'i';

    $stmt = $conn->prepare($update_sql);
    if (!$stmt) {
        throw new Exception('SQL Prepare Error: ' . $conn->error);
    }

    $stmt->bind_param($types, ...$params);
    
    if (!$stmt->execute()) {
        throw new Exception('Execute Error: ' . $stmt->error);
    }

    $affected_rows = $stmt->affected_rows;
    $stmt->close();

    if ($affected_rows === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Request not found or no changes made']);
        exit();
    }

    // Log activity (optional)
    $log_sql = "INSERT INTO audit_logs (user_id, action, table_name, record_id, old_values, new_values, created_at) 
               VALUES (?, ?, ?, ?, ?, ?, NOW())";
    
    if ($conn->prepare($log_sql)) {
        // Optional: implement audit logging
    }

    $conn->close();

    echo json_encode([
        'success' => true,
        'message' => 'Request updated successfully',
        'affected_rows' => $affected_rows
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>