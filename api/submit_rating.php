<?php
/**
 * API: Submit Rating - Fixed Version
 * Employee can rate completed requests
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../controllers/AuthController.php';

AuthController::requireAuth();

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$table = $input['table'] ?? '';
$request_id = $input['request_id'] ?? '';
$score = $input['score'] ?? '';
$feedback = $input['feedback'] ?? '';
$user_id = $_SESSION['user_id'] ?? '';

// Validation
if (empty($table)) {
    echo json_encode(['success' => false, 'message' => 'Table name is required']);
    exit();
}

if (empty($request_id)) {
    echo json_encode(['success' => false, 'message' => 'Request ID is required']);
    exit();
}

if ($score === '' || $score < 1 || $score > 5) {
    echo json_encode(['success' => false, 'message' => 'Score must be between 1 and 5']);
    exit();
}

if (empty($user_id)) {
    echo json_encode(['success' => false, 'message' => 'User ID not found']);
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

// Check if request belongs to this user and is completed
$check_sql = "SELECT $id_column, employee_id, status, satisfaction_score 
              FROM $table 
              WHERE $id_column = ? AND employee_id = ?";

$check_stmt = $conn->prepare($check_sql);

if (!$check_stmt) {
    echo json_encode(['success' => false, 'message' => 'Prepare check failed: ' . htmlspecialchars($conn->error)]);
    exit();
}

$check_stmt->bind_param("is", $request_id, $user_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows === 0) {
    $check_stmt->close();
    $conn->close();
    echo json_encode(['success' => false, 'message' => 'Request not found or access denied']);
    exit();
}

$request_data = $check_result->fetch_assoc();
$check_stmt->close();

// Check if already rated
if (!empty($request_data['satisfaction_score']) && $request_data['satisfaction_score'] > 0) {
    $conn->close();
    echo json_encode(['success' => false, 'message' => 'You have already rated this request']);
    exit();
}

// Check if request is completed
if ($request_data['status'] !== 'Complete') {
    $conn->close();
    echo json_encode(['success' => false, 'message' => 'You can only rate completed requests']);
    exit();
}

// Update satisfaction rating
$score = (int) $score;
$update_sql = "UPDATE $table 
               SET satisfaction_score = ?, 
                   satisfaction_feedback = ?,
                   updated_at = CURRENT_TIMESTAMP 
               WHERE $id_column = ?";

$update_stmt = $conn->prepare($update_sql);

if (!$update_stmt) {
    echo json_encode(['success' => false, 'message' => 'Prepare update failed: ' . htmlspecialchars($conn->error)]);
    exit();
}

$update_stmt->bind_param("isi", $score, $feedback, $request_id);

if ($update_stmt->execute()) {
    $update_stmt->close();
    $conn->close();
    
    echo json_encode([
        'success' => true,
        'message' => 'Thank you for your feedback!'
    ]);
} else {
    $error = $update_stmt->error;
    $update_stmt->close();
    $conn->close();
    
    echo json_encode([
        'success' => false,
        'message' => 'Failed to submit rating: ' . htmlspecialchars($error)
    ]);
}
?>