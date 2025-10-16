<?php
/**
 * API: Update Request Status
 * Update status and remarks for any request type
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../controllers/AuthController.php';

// Require authentication
if (!AuthController::isAuthenticated()) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

// Check role
if (!AuthController::hasRole(['admin', 'officer'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized - Admin/Officer only']);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Debug: log input
error_log("Update Request Input: " . print_r($input, true));

$table = $input['table'] ?? '';
$request_id = $input['request_id'] ?? '';
$status = $input['status'] ?? '';
$handler_remarks = $input['handler_remarks'] ?? '';

// Validation
if (empty($table)) {
    echo json_encode(['success' => false, 'message' => 'Table name is required']);
    exit();
}

if (empty($request_id)) {
    echo json_encode(['success' => false, 'message' => 'Request ID is required']);
    exit();
}

if (empty($status)) {
    echo json_encode(['success' => false, 'message' => 'Status is required']);
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
    echo json_encode(['success' => false, 'message' => 'Invalid table: ' . $table]);
    exit();
}

// Validate status
$allowed_statuses = ['New', 'In Progress', 'Complete', 'Cancelled'];
if (!in_array($status, $allowed_statuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid status: ' . $status]);
    exit();
}

$conn = getDbConnection();

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

// Get current user ID
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$handler_id = $_SESSION['user_id'] ?? '';

if (empty($handler_id)) {
    echo json_encode(['success' => false, 'message' => 'Handler ID not found in session']);
    exit();
}

// Determine the primary key column name
$id_column = ($table === 'document_submissions') ? 'submission_id' : 'request_id';

// Check if record exists first
$check_sql = "SELECT $id_column FROM $table WHERE $id_column = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("i", $request_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows === 0) {
    $check_stmt->close();
    $conn->close();
    echo json_encode(['success' => false, 'message' => 'Request not found in table: ' . $table]);
    exit();
}
$check_stmt->close();

// Update request
$sql = "UPDATE $table 
        SET status = ?, 
            handler_id = ?, 
            handler_remarks = ?,
            updated_at = CURRENT_TIMESTAMP 
        WHERE $id_column = ?";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    $conn->close();
    echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
    exit();
}

$stmt->bind_param("sssi", $status, $handler_id, $handler_remarks, $request_id);

if ($stmt->execute()) {
    $affected = $stmt->affected_rows;
    $stmt->close();
    $conn->close();
    
    echo json_encode([
        'success' => true,
        'message' => 'Request updated successfully',
        'affected_rows' => $affected
    ]);
} else {
    $error = $stmt->error;
    $stmt->close();
    $conn->close();
    
    echo json_encode([
        'success' => false,
        'message' => 'Failed to update request: ' . $error
    ]);
}
?>