<?php
/**
 * API: Delete Employee
 * Admin only endpoint
 */

require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../models/Employee.php';

// Require admin role only
AuthController::requireRole(['admin']);

// Get employee ID
$employee_id = $_GET['id'] ?? '';

if (empty($employee_id)) {
    header('Location: ' . BASE_PATH . '/views/admin/employees.php?error=' . urlencode('Employee ID is required'));
    exit();
}

// Check if employee exists
$employee = Employee::getById($employee_id);

if (!$employee) {
    header('Location: ' . BASE_PATH . '/views/admin/employees.php?error=' . urlencode('Employee not found'));
    exit();
}

// Cannot delete yourself
if ($employee_id === $_SESSION['user_id']) {
    header('Location: ' . BASE_PATH . '/views/admin/employee_detail.php?id=' . $employee_id . '&error=' . urlencode('You cannot delete your own account'));
    exit();
}

// Perform delete
$result = Employee::delete($employee_id);

if ($result['success']) {
    // Success - redirect to employees list
    header('Location: ' . BASE_PATH . '/views/admin/employees.php?success=1&message=' . urlencode('Employee deleted successfully'));
    exit();
} else {
    // Error - redirect back to detail page
    header('Location: ' . BASE_PATH . '/views/admin/employee_detail.php?id=' . $employee_id . '&error=' . urlencode($result['message']));
    exit();
}
?>