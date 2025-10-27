<?php
/**
 * API: Cancel Request
 * Employee can cancel their own requests if status is 'New'
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../controllers/AuthController.php';

AuthController::requireAuth();

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

$id = $input['id'] ?? 0;
$table = $input['table'] ?? '';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$user_id = $_SESSION['user_id'];

// Validate table
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

if (empty($id)) {
    echo json_encode(['success' => false, 'message' => 'Request ID is required']);
    exit();
}

$conn = getDbConnection();

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

// Determine the primary key column name
$id_column = ($table === 'document_submissions') ? 'submission_id' : 'request_id';

// Check if request exists and belongs to user and is in 'New' status
$check_sql = "SELECT $id_column, employee_id, status 
              FROM $table 
              WHERE $id_column = ? AND employee_id = ?";

$check_stmt = $conn->prepare($check_sql);

if (!$check_stmt) {
    echo json_encode(['success' => false, 'message' => 'Prepare check failed: ' . htmlspecialchars($conn->error)]);
    exit();
}

$check_stmt->bind_param("is", $id, $user_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows === 0) {
    $check_stmt->close();
    $conn->close();
    echo json_encode(['success' => false, 'message' => 'Request not found or access denied']);
    exit();
}

$request = $check_result->fetch_assoc();
$check_stmt->close();

// Check if status is 'New'
if ($request['status'] !== 'New') {
    $conn->close();
    echo json_encode(['success' => false, 'message' => 'Can only cancel requests with status "New"']);
    exit();
}

// Cancel the request
$update_sql = "UPDATE $table 
               SET status = 'Cancelled',
                   updated_at = CURRENT_TIMESTAMP 
               WHERE $id_column = ?";

$update_stmt = $conn->prepare($update_sql);

if (!$update_stmt) {
    echo json_encode(['success' => false, 'message' => 'Prepare update failed: ' . htmlspecialchars($conn->error)]);
    exit();
}

$update_stmt->bind_param("i", $id);

if ($update_stmt->execute()) {
    $update_stmt->close();
    $conn->close();
    
    echo json_encode([
        'success' => true,
        'message' => 'Request cancelled successfully'
    ]);
} else {
    $error = $update_stmt->error;
    $update_stmt->close();
    $conn->close();
    
    echo json_encode([
        'success' => false,
        'message' => 'Failed to cancel request: ' . htmlspecialchars($error)
    ]);
}
?>