<?php
/**
 * API: Submit Rating
 * Employee can rate completed requests
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../controllers/AuthController.php';

// Require authentication
if (!AuthController::isAuthenticated()) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Debug log
error_log("Submit Rating Input: " . print_r($input, true));

// Support both parameter names for compatibility
$table = $input['table'] ?? '';
$request_id = $input['request_id'] ?? ($input['id'] ?? '');
$score = $input['score'] ?? '';
$feedback = $input['feedback'] ?? '';

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

$conn = getDbConnection();

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

// Get current user ID
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$user_id = $_SESSION['user_id'] ?? '';

if (empty($user_id)) {
    $conn->close();
    echo json_encode(['success' => false, 'message' => 'User ID not found in session']);
    exit();
}

// Determine the primary key column name
$id_column = ($table === 'document_submissions') ? 'submission_id' : 'request_id';

// Check if request belongs to this user and is completed
$check_sql = "SELECT $id_column, employee_id, status, satisfaction_score 
              FROM $table 
              WHERE $id_column = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("i", $request_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows === 0) {
    $check_stmt->close();
    $conn->close();
    echo json_encode(['success' => false, 'message' => 'Request not found']);
    exit();
}

$request_data = $check_result->fetch_assoc();
$check_stmt->close();

// Verify ownership
if ($request_data['employee_id'] !== $user_id) {
    $conn->close();
    echo json_encode(['success' => false, 'message' => 'You can only rate your own requests']);
    exit();
}

// Check if already rated
if (!empty($request_data['satisfaction_score'])) {
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
$sql = "UPDATE $table 
        SET satisfaction_score = ?, 
            satisfaction_feedback = ?,
            updated_at = CURRENT_TIMESTAMP 
        WHERE $id_column = ?";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    $conn->close();
    echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
    exit();
}

$stmt->bind_param("isi", $score, $feedback, $request_id);

if ($stmt->execute()) {
    $affected = $stmt->affected_rows;
    $stmt->close();
    $conn->close();
    
    echo json_encode([
        'success' => true,
        'message' => 'Thank you for your feedback!',
        'affected_rows' => $affected
    ]);
} else {
    $error = $stmt->error;
    $stmt->close();
    $conn->close();
    
    echo json_encode([
        'success' => false,
        'message' => 'Failed to submit rating: ' . $error
    ]);
}
?>