<?php
/**
 * API: Get Request Details (FIXED VERSION)
 * Fetch detailed information about a specific request
 * Includes employee data (Hiring Type, Date of Hire, Base Salary)
 * Includes certificate type information
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../controllers/AuthController.php';

// Require authentication
AuthController::requireAuth();

// Check if user is accessing their own data or is admin/officer
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$table = $_GET['table'] ?? '';
$request_id = $_GET['id'] ?? '';
$user_id = $_SESSION['user_id'] ?? '';
$user_role = $_SESSION['user_role'] ?? 'employee';

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

// Build query with comprehensive JOINs
switch ($table) {
    case 'certificate_requests':
        $sql = "SELECT 
                    cr.*,
                    e.full_name_th as employee_name_th,
                    e.full_name_en as employee_name_en,
                    e.position_id,
                    e.division_id,
                    e.department_id,
                    e.section_id,
                    e.hiring_type_id,
                    e.date_of_hire,
                    e.base_salary,
                    COALESCE(p.position_name_th, 'ไม่ระบุ') as position_name_th,
                    COALESCE(p.position_name_en, 'Not Specified') as position_name_en,
                    COALESCE(d.division_name_th, 'ไม่ระบุ') as division_name_th,
                    COALESCE(d.division_name_en, 'Not Specified') as division_name_en,
                    COALESCE(dep.department_name_th, 'ไม่ระบุ') as department_name_th,
                    COALESCE(dep.department_name_en, 'Not Specified') as department_name_en,
                    COALESCE(s.section_name_th, 'ไม่ระบุ') as section_name_th,
                    COALESCE(s.section_name_en, 'Not Specified') as section_name_en,
                    COALESCE(ht.type_name_th, 'ไม่ระบุ') as hiring_type_name_th,
                    COALESCE(ht.type_name_en, 'Not Specified') as hiring_type_name_en,
                    COALESCE(ct.type_name_th, 'ไม่ระบุ') as cert_type_name_th,
                    COALESCE(ct.type_name_en, 'Not Specified') as cert_type_name_en
                FROM certificate_requests cr
                LEFT JOIN employees e ON cr.employee_id = e.employee_id
                LEFT JOIN position_master p ON e.position_id = p.position_id
                LEFT JOIN division_master d ON e.division_id = d.division_id
                LEFT JOIN department_master dep ON e.department_id = dep.department_id
                LEFT JOIN section_master s ON e.section_id = s.section_id
                LEFT JOIN hiring_type_master ht ON e.hiring_type_id = ht.hiring_type_id
                LEFT JOIN certificate_types ct ON cr.cert_type_id = ct.cert_type_id
                WHERE cr.$id_column = ?
                AND (cr.employee_id = ? OR ? IN ('admin', 'officer'))";
        break;

    case 'leave_requests':
        $sql = "SELECT 
                    lr.*,
                    e.full_name_th as employee_name_th,
                    e.full_name_en as employee_name_en,
                    e.position_id,
                    e.division_id,
                    COALESCE(p.position_name_th, 'ไม่ระบุ') as position_name_th,
                    COALESCE(p.position_name_en, 'Not Specified') as position_name_en,
                    COALESCE(d.division_name_th, 'ไม่ระบุ') as division_name_th,
                    COALESCE(d.division_name_en, 'Not Specified') as division_name_en
                FROM leave_requests lr
                LEFT JOIN employees e ON lr.employee_id = e.employee_id
                LEFT JOIN position_master p ON e.position_id = p.position_id
                LEFT JOIN division_master d ON e.division_id = d.division_id
                WHERE lr.$id_column = ?
                AND (lr.employee_id = ? OR ? IN ('admin', 'officer'))";
        break;

    case 'id_card_requests':
        $sql = "SELECT 
                    ir.*,
                    e.full_name_th as employee_name_th,
                    e.full_name_en as employee_name_en
                FROM id_card_requests ir
                LEFT JOIN employees e ON ir.employee_id = e.employee_id
                WHERE ir.$id_column = ?
                AND (ir.employee_id = ? OR ? IN ('admin', 'officer'))";
        break;

    case 'shuttle_bus_requests':
        $sql = "SELECT 
                    sbr.*,
                    e.full_name_th as employee_name_th,
                    e.full_name_en as employee_name_en,
                    COALESCE(p.position_name_th, 'ไม่ระบุ') as position_name_th,
                    COALESCE(p.position_name_en, 'Not Specified') as position_name_en
                FROM shuttle_bus_requests sbr
                LEFT JOIN employees e ON sbr.employee_id = e.employee_id
                LEFT JOIN position_master p ON e.position_id = p.position_id
                WHERE sbr.$id_column = ?
                AND (sbr.employee_id = ? OR ? IN ('admin', 'officer'))";
        break;

    case 'locker_requests':
        $sql = "SELECT 
                    lr.*,
                    e.full_name_th as employee_name_th,
                    e.full_name_en as employee_name_en,
                    lm.locker_number
                FROM locker_requests lr
                LEFT JOIN employees e ON lr.employee_id = e.employee_id
                LEFT JOIN locker_master lm ON lr.assigned_locker_id = lm.locker_id
                WHERE lr.$id_column = ?
                AND (lr.employee_id = ? OR ? IN ('admin', 'officer'))";
        break;

    case 'supplies_requests':
        $sql = "SELECT 
                    sr.*,
                    e.full_name_th as employee_name_th,
                    e.full_name_en as employee_name_en
                FROM supplies_requests sr
                LEFT JOIN employees e ON sr.employee_id = e.employee_id
                WHERE sr.$id_column = ?
                AND (sr.employee_id = ? OR ? IN ('admin', 'officer'))";
        break;

    case 'skill_test_requests':
        $sql = "SELECT 
                    str.*,
                    e.full_name_th as employee_name_th,
                    e.full_name_en as employee_name_en
                FROM skill_test_requests str
                LEFT JOIN employees e ON str.employee_id = e.employee_id
                WHERE str.$id_column = ?
                AND (str.employee_id = ? OR ? IN ('admin', 'officer'))";
        break;

    case 'document_submissions':
        $sql = "SELECT 
                    ds.*,
                    e.full_name_th as employee_name_th,
                    e.full_name_en as employee_name_en,
                    COALESCE(scm.category_name_th, 'ไม่ระบุ') as service_category_th,
                    COALESCE(scm.category_name_en, 'Not Specified') as service_category_en,
                    COALESCE(stm.type_name_th, 'ไม่ระบุ') as service_type_th,
                    COALESCE(stm.type_name_en, 'Not Specified') as service_type_en
                FROM document_submissions ds
                LEFT JOIN employees e ON ds.employee_id = e.employee_id
                LEFT JOIN service_category_master scm ON ds.service_category_id = scm.category_id
                LEFT JOIN service_type_master stm ON ds.service_type_id = stm.type_id
                WHERE ds.$id_column = ?
                AND (ds.employee_id = ? OR ? IN ('admin', 'officer'))";
        break;

    default:
        $sql = "SELECT * FROM $table WHERE $id_column = ?";
}

$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Prepare statement failed: ' . $conn->error]);
    exit();
}

$stmt->bind_param("iss", $request_id, $user_id, $user_role);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    // Add the actual ID to response (for consistency)
    $row['request_id'] = $row[$id_column];
    
    echo json_encode(['success' => true, 'request' => $row]);
} else {
    echo json_encode(['success' => false, 'message' => 'Request not found or access denied']);
}

$stmt->close();
$conn->close();
?>