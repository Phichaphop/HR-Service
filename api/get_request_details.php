<?php
/**
 * API: Get Request Details
 * Employee can view their own request details
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../controllers/AuthController.php';

AuthController::requireAuth();

$id = $_GET['id'] ?? 0;
$table = $_GET['table'] ?? '';

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
    'skill_test_requests'
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

// Build query based on table
if ($table === 'certificate_requests') {
    $sql = "SELECT cr.*, ct.type_name_th as cert_type_name
            FROM $table cr
            LEFT JOIN certificate_types ct ON cr.cert_type_id = ct.cert_type_id
            WHERE cr.request_id = ? AND cr.employee_id = ?";
} else {
    $sql = "SELECT * FROM $table WHERE request_id = ? AND employee_id = ?";
}

$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode(['success' => true, 'request' => $row]);
} else {
    echo json_encode(['success' => false, 'message' => 'Request not found or access denied']);
}

$stmt->close();
$conn->close();
?>