<?php
/**
 * API: Reset Employee Password
 * Admin only - Reset password for any employee
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../controllers/AuthController.php';

// Require admin role only
AuthController::requireRole(['admin']);

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

$employee_id = $input['employee_id'] ?? '';
$new_password = $input['new_password'] ?? '';

// Validation
if (empty($employee_id)) {
    echo json_encode(['success' => false, 'message' => 'Employee ID is required']);
    exit();
}

if (empty($new_password)) {
    echo json_encode(['success' => false, 'message' => 'New password is required']);
    exit();
}

if (strlen($new_password) < 6) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters']);
    exit();
}

$conn = getDbConnection();

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

// Check if employee exists
$check_sql = "SELECT employee_id, full_name_th, full_name_en FROM employees WHERE employee_id = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("s", $employee_id);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows === 0) {
    $check_stmt->close();
    $conn->close();
    echo json_encode(['success' => false, 'message' => 'Employee not found']);
    exit();
}

$employee = $result->fetch_assoc();
$check_stmt->close();

// Hash new password
$password_hash = password_hash($new_password, PASSWORD_DEFAULT);

// Update password
$sql = "UPDATE employees 
        SET password = ?, 
            updated_at = CURRENT_TIMESTAMP 
        WHERE employee_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $password_hash, $employee_id);

if ($stmt->execute()) {
    $stmt->close();
    $conn->close();
    
    echo json_encode([
        'success' => true,
        'message' => 'Password reset successfully',
        'employee' => [
            'id' => $employee['employee_id'],
            'name_th' => $employee['full_name_th'],
            'name_en' => $employee['full_name_en']
        ]
    ]);
} else {
    $error = $stmt->error;
    $stmt->close();
    $conn->close();
    
    echo json_encode([
        'success' => false,
        'message' => 'Failed to reset password: ' . $error
    ]);
}
?>