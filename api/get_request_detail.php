<?php
/**
 * API: Get Request Detail
 * Fetch detailed information about a specific request
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../controllers/AuthController.php';

// Require authentication
AuthController::requireRole(['admin', 'officer']);

$table = $_GET['table'] ?? '';
$request_id = $_GET['id'] ?? '';

if (empty($table) || empty($request_id)) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit();
}

// Validate table name (security)
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

if (!in_array($table, $allowed_tables)) {
    echo json_encode(['success' => false, 'message' => 'Invalid table']);
    exit();
}

$conn = getDbConnection();

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

// Determine the primary key column name
$id_column = ($table === 'document_submissions') ? 'submission_id' : 'request_id';

// Build query with JOIN for certificate_requests
if ($table === 'certificate_requests') {
    $sql = "SELECT cr.*, ct.type_name_th as cert_type_name
            FROM $table cr
            LEFT JOIN certificate_types ct ON cr.cert_type_id = ct.cert_type_id
            WHERE cr.$id_column = ?";
} else {
    $sql = "SELECT * FROM $table WHERE $id_column = ?";
}

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $request_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    // Add the actual ID to response (for consistency)
    $row['request_id'] = $row[$id_column];
    
    // Get employee details
    $emp_sql = "SELECT employee_id, full_name_th, full_name_en FROM employees WHERE employee_id = ?";
    $emp_stmt = $conn->prepare($emp_sql);
    $emp_stmt->bind_param("s", $row['employee_id']);
    $emp_stmt->execute();
    $emp_result = $emp_stmt->get_result();
    $employee = $emp_result->fetch_assoc();
    $emp_stmt->close();
    
    // Add employee name to request data
    if ($employee) {
        $row['employee_name_th'] = $employee['full_name_th'];
        $row['employee_name_en'] = $employee['full_name_en'];
    }
    
    // Get handler details if exists
    if (!empty($row['handler_id'])) {
        $handler_sql = "SELECT employee_id, full_name_th, full_name_en FROM employees WHERE employee_id = ?";
        $handler_stmt = $conn->prepare($handler_sql);
        $handler_stmt->bind_param("s", $row['handler_id']);
        $handler_stmt->execute();
        $handler_result = $handler_stmt->get_result();
        $handler = $handler_result->fetch_assoc();
        $handler_stmt->close();
        
        if ($handler) {
            $row['handler_name_th'] = $handler['full_name_th'];
            $row['handler_name_en'] = $handler['full_name_en'];
        }
    }
    
    echo json_encode([
        'success' => true,
        'request' => $row,
        'table' => $table
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Request not found'
    ]);
}

$stmt->close();
$conn->close();
?>