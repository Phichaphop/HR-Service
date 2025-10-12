<?php
/**
 * API: Get Employee Data
 * Returns employee information with localized master data
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../db/Localization.php';

$employee_id = $_GET['id'] ?? '';

if (empty($employee_id)) {
    echo json_encode(['success' => false, 'message' => 'Employee ID required']);
    exit();
}

$conn = getDbConnection();

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

// Get employee with localized data
$sql = "SELECT 
    e.*,
    p.position_name_th, p.position_name_en,
    pl.level_name_th, pl.level_name_en,
    s.section_name_th, s.section_name_en,
    d.department_name_th, d.department_name_en,
    div.division_name_th, div.division_name_en,
    f.function_name_th, f.function_name_en
FROM employees e
LEFT JOIN position_master p ON e.position_id = p.position_id
LEFT JOIN position_level_master pl ON e.position_level_id = pl.level_id
LEFT JOIN section_master s ON e.section_id = s.section_id
LEFT JOIN department_master d ON e.department_id = d.department_id
LEFT JOIN division_master div ON e.division_id = div.division_id
LEFT JOIN function_master f ON e.function_id = f.function_id
WHERE e.employee_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $employee_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    // Get current language preference
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $lang = $_SESSION['language'] ?? 'th';
    
    // Prepare response with language-specific data
    $employee = [
        'employee_id' => $row['employee_id'],
        'full_name_th' => $row['full_name_th'],
        'full_name_en' => $row['full_name_en'],
        'position_name' => $row['position_name_' . $lang],
        'level_name' => $row['level_name_' . $lang],
        'section_name' => $row['section_name_' . $lang],
        'department_name' => $row['department_name_' . $lang],
        'division_name' => $row['division_name_' . $lang],
        'function_name' => $row['function_name_' . $lang],
        'phone_no' => $row['phone_no'],
        'date_of_hire' => $row['date_of_hire'],
        'year_of_service' => $row['year_of_service'],
        'profile_pic_path' => $row['profile_pic_path']
    ];
    
    echo json_encode([
        'success' => true,
        'employee' => $employee
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Employee not found'
    ]);
}

$stmt->close();
$conn->close();
?>