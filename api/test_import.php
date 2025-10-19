<?php
/**
 * Test Import - Debug Tool
 * ทดสอบการ import ด้วยข้อมูล hardcode
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../controllers/AuthController.php';

// Require admin role
AuthController::requireRole(['admin']);

$conn = getDbConnection();

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

// Test data
$test_employee = [
    'employee_id' => 'TEST999',
    'prefix_id' => 1,
    'full_name_th' => 'ทดสอบ ระบบ',
    'full_name_en' => 'Test System',
    'sex_id' => 1,
    'birthday' => '1990-01-01',
    'nationality_id' => 1,
    'education_level_id' => 1,
    'phone_no' => '081-999-9999',
    'address_village' => 'บ้านทดสอบ',
    'address_subdistrict' => 'ทดสอบ',
    'address_district' => 'ทดสอบ',
    'address_province' => 'ทดสอบ',
    'function_id' => 1,
    'division_id' => 1,
    'department_id' => 1,
    'section_id' => 1,
    'operation_id' => 1,
    'position_id' => 1,
    'position_level_id' => 1,
    'labour_cost_id' => 1,
    'hiring_type_id' => 1,
    'customer_zone_id' => 1,
    'contribution_level_id' => 1,
    'date_of_hire' => date('Y-m-d'),
    'status_id' => 1,
    'username' => 'test999',
    'password' => password_hash('test123456', PASSWORD_DEFAULT),
    'role_id' => 3
];

// Delete if exists
$conn->query("DELETE FROM employees WHERE employee_id = 'TEST999'");

// Try to insert
$sql = "INSERT INTO employees (
    employee_id, prefix_id, full_name_th, full_name_en, sex_id, birthday,
    nationality_id, education_level_id, phone_no, address_village, address_subdistrict,
    address_district, address_province, function_id, division_id, department_id,
    section_id, operation_id, position_id, position_level_id, labour_cost_id,
    hiring_type_id, customer_zone_id, contribution_level_id, date_of_hire,
    status_id, username, password, role_id
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode([
        'success' => false,
        'message' => 'Prepare failed',
        'error' => $conn->error
    ]);
    exit();
}

$bind_result = $stmt->bind_param(
    "sississssssssiiiiiiiiiisssi",
    $test_employee['employee_id'],
    $test_employee['prefix_id'],
    $test_employee['full_name_th'],
    $test_employee['full_name_en'],
    $test_employee['sex_id'],
    $test_employee['birthday'],
    $test_employee['nationality_id'],
    $test_employee['education_level_id'],
    $test_employee['phone_no'],
    $test_employee['address_village'],
    $test_employee['address_subdistrict'],
    $test_employee['address_district'],
    $test_employee['address_province'],
    $test_employee['function_id'],
    $test_employee['division_id'],
    $test_employee['department_id'],
    $test_employee['section_id'],
    $test_employee['operation_id'],
    $test_employee['position_id'],
    $test_employee['position_level_id'],
    $test_employee['labour_cost_id'],
    $test_employee['hiring_type_id'],
    $test_employee['customer_zone_id'],
    $test_employee['contribution_level_id'],
    $test_employee['date_of_hire'],
    $test_employee['status_id'],
    $test_employee['username'],
    $test_employee['password'],
    $test_employee['role_id']
);

if (!$bind_result) {
    echo json_encode([
        'success' => false,
        'message' => 'Bind failed',
        'error' => $stmt->error
    ]);
    $stmt->close();
    exit();
}

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Test employee inserted successfully',
        'employee_id' => 'TEST999',
        'note' => 'You can delete this test employee from the database'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Execute failed',
        'error' => $stmt->error,
        'errno' => $stmt->errno
    ]);
}

$stmt->close();
$conn->close();
?>