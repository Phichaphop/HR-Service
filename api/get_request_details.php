<?php
/**
 * API: Get Request Details - COMPATIBLE VERSION
 * 
 * ✅ ใช้พารามิเตอร์เดียวกัน: 'table' และ 'id' (ไม่ใช่ request_id)
 * ✅ ตรวจสอบ employee_id = user_id เหมือนเดิม
 * ✅ JOIN กับ master tables เพื่อให้ได้ชื่อแทนที่จะเป็น ID
 * ✅ ใช้ AuthController::requireAuth() เหมือนเดิม
 * ✅ เหมาะสำหรับ ID Card generation
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../controllers/AuthController.php';

// Require authentication
AuthController::requireAuth();

// Check if user is accessing their own data
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$table = $_GET['table'] ?? '';
$request_id = $_GET['id'] ?? '';  // ✅ ใช้ 'id' เหมือนเดิม
$user_id = $_SESSION['user_id'] ?? '';

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

try {
    // Build query based on table type
    switch ($table) {
        case 'certificate_requests':
            // Certificate requests - JOIN with certificate types and master data
            $sql = "SELECT 
                        cr.*,
                        e.full_name_th,
                        e.full_name_en,
                        e.prefix_th,
                        e.prefix_en,
                        e.phone_no,
                        e.profile_pic_path,
                        pm.position_name_th,
                        pm.position_name_en,
                        dm.division_name_th,
                        dm.division_name_en,
                        dept.department_name_th,
                        dept.department_name_en,
                        sm.section_name_th,
                        sm.section_name_en,
                        ct.cert_type_id,
                        ct.type_name_th,
                        ct.type_name_en,
                        ct.type_name_my
                    FROM certificate_requests cr
                    LEFT JOIN employees e ON cr.employee_id = e.employee_id
                    LEFT JOIN position_master pm ON e.position_id = pm.position_id
                    LEFT JOIN division_master dm ON e.division_id = dm.division_id
                    LEFT JOIN department_master dept ON e.department_id = dept.department_id
                    LEFT JOIN section_master sm ON e.section_id = sm.section_id
                    LEFT JOIN certificate_types ct ON cr.cert_type_id = ct.cert_type_id
                    WHERE cr.$id_column = ? AND cr.employee_id = ?";
            break;
            
        case 'leave_requests':
            $sql = "SELECT 
                        lr.*,
                        e.full_name_th,
                        e.full_name_en,
                        e.prefix_th,
                        e.prefix_en,
                        e.phone_no,
                        e.profile_pic_path,
                        pm.position_name_th,
                        pm.position_name_en,
                        dm.division_name_th,
                        dm.division_name_en,
                        dept.department_name_th,
                        dept.department_name_en,
                        sm.section_name_th,
                        sm.section_name_en
                    FROM leave_requests lr
                    LEFT JOIN employees e ON lr.employee_id = e.employee_id
                    LEFT JOIN position_master pm ON e.position_id = pm.position_id
                    LEFT JOIN division_master dm ON e.division_id = dm.division_id
                    LEFT JOIN department_master dept ON e.department_id = dept.department_id
                    LEFT JOIN section_master sm ON e.section_id = sm.section_id
                    WHERE lr.$id_column = ? AND lr.employee_id = ?";
            break;
            
        case 'id_card_requests':
            // ✅ ID Card - เพิ่ม profile_pic_path สำหรับ ID Card generation
            $sql = "SELECT 
                        icr.*,
                        e.full_name_th,
                        e.full_name_en,
                        e.prefix_th,
                        e.prefix_en,
                        e.phone_no,
                        e.profile_pic_path,
                        pm.position_name_th,
                        pm.position_name_en,
                        dm.division_name_th,
                        dm.division_name_en,
                        dept.department_name_th,
                        dept.department_name_en,
                        sm.section_name_th,
                        sm.section_name_en,
                        c.company_name,
                        c.company_logo_path
                    FROM id_card_requests icr
                    LEFT JOIN employees e ON icr.employee_id = e.employee_id
                    LEFT JOIN position_master pm ON e.position_id = pm.position_id
                    LEFT JOIN division_master dm ON e.division_id = dm.division_id
                    LEFT JOIN department_master dept ON e.department_id = dept.department_id
                    LEFT JOIN section_master sm ON e.section_id = sm.section_id
                    LEFT JOIN company_info c ON c.company_id = 1
                    WHERE icr.$id_column = ? AND icr.employee_id = ?";
            break;
            
        case 'shuttle_bus_requests':
            $sql = "SELECT 
                        sbr.*,
                        e.full_name_th,
                        e.full_name_en,
                        e.prefix_th,
                        e.prefix_en,
                        e.phone_no,
                        e.profile_pic_path,
                        pm.position_name_th,
                        pm.position_name_en,
                        dm.division_name_th,
                        dm.division_name_en,
                        dept.department_name_th,
                        dept.department_name_en,
                        sm.section_name_th,
                        sm.section_name_en
                    FROM shuttle_bus_requests sbr
                    LEFT JOIN employees e ON sbr.employee_id = e.employee_id
                    LEFT JOIN position_master pm ON e.position_id = pm.position_id
                    LEFT JOIN division_master dm ON e.division_id = dm.division_id
                    LEFT JOIN department_master dept ON e.department_id = dept.department_id
                    LEFT JOIN section_master sm ON e.section_id = sm.section_id
                    WHERE sbr.$id_column = ? AND sbr.employee_id = ?";
            break;
            
        case 'locker_requests':
            $sql = "SELECT 
                        lr.*,
                        e.full_name_th,
                        e.full_name_en,
                        e.prefix_th,
                        e.prefix_en,
                        e.phone_no,
                        e.profile_pic_path,
                        pm.position_name_th,
                        pm.position_name_en,
                        dm.division_name_th,
                        dm.division_name_en,
                        dept.department_name_th,
                        dept.department_name_en,
                        sm.section_name_th,
                        sm.section_name_en
                    FROM locker_requests lr
                    LEFT JOIN employees e ON lr.employee_id = e.employee_id
                    LEFT JOIN position_master pm ON e.position_id = pm.position_id
                    LEFT JOIN division_master dm ON e.division_id = dm.division_id
                    LEFT JOIN department_master dept ON e.department_id = dept.department_id
                    LEFT JOIN section_master sm ON e.section_id = sm.section_id
                    WHERE lr.$id_column = ? AND lr.employee_id = ?";
            break;
            
        case 'supplies_requests':
            $sql = "SELECT 
                        sr.*,
                        e.full_name_th,
                        e.full_name_en,
                        e.prefix_th,
                        e.prefix_en,
                        e.phone_no,
                        e.profile_pic_path,
                        pm.position_name_th,
                        pm.position_name_en,
                        dm.division_name_th,
                        dm.division_name_en,
                        dept.department_name_th,
                        dept.department_name_en,
                        sm.section_name_th,
                        sm.section_name_en
                    FROM supplies_requests sr
                    LEFT JOIN employees e ON sr.employee_id = e.employee_id
                    LEFT JOIN position_master pm ON e.position_id = pm.position_id
                    LEFT JOIN division_master dm ON e.division_id = dm.division_id
                    LEFT JOIN department_master dept ON e.department_id = dept.department_id
                    LEFT JOIN section_master sm ON e.section_id = sm.section_id
                    WHERE sr.$id_column = ? AND sr.employee_id = ?";
            break;
            
        case 'skill_test_requests':
            $sql = "SELECT 
                        str.*,
                        e.full_name_th,
                        e.full_name_en,
                        e.prefix_th,
                        e.prefix_en,
                        e.phone_no,
                        e.profile_pic_path,
                        pm.position_name_th,
                        pm.position_name_en,
                        dm.division_name_th,
                        dm.division_name_en,
                        dept.department_name_th,
                        dept.department_name_en,
                        sm.section_name_th,
                        sm.section_name_en
                    FROM skill_test_requests str
                    LEFT JOIN employees e ON str.employee_id = e.employee_id
                    LEFT JOIN position_master pm ON e.position_id = pm.position_id
                    LEFT JOIN division_master dm ON e.division_id = dm.division_id
                    LEFT JOIN department_master dept ON e.department_id = dept.department_id
                    LEFT JOIN section_master sm ON e.section_id = sm.section_id
                    WHERE str.$id_column = ? AND str.employee_id = ?";
            break;
            
        case 'document_submissions':
            $sql = "SELECT 
                        ds.*,
                        e.full_name_th,
                        e.full_name_en,
                        e.prefix_th,
                        e.prefix_en,
                        e.phone_no,
                        e.profile_pic_path,
                        pm.position_name_th,
                        pm.position_name_en,
                        dm.division_name_th,
                        dm.division_name_en,
                        dept.department_name_th,
                        dept.department_name_en,
                        sm.section_name_th,
                        sm.section_name_en,
                        scm.category_name_th,
                        scm.category_name_en,
                        scm.category_name_my,
                        stm.type_name_th as service_type_name_th,
                        stm.type_name_en as service_type_name_en,
                        stm.type_name_my as service_type_name_my
                    FROM document_submissions ds
                    LEFT JOIN employees e ON ds.employee_id = e.employee_id
                    LEFT JOIN position_master pm ON e.position_id = pm.position_id
                    LEFT JOIN division_master dm ON e.division_id = dm.division_id
                    LEFT JOIN department_master dept ON e.department_id = dept.department_id
                    LEFT JOIN section_master sm ON e.section_id = sm.section_id
                    LEFT JOIN service_category_master scm ON ds.service_category_id = scm.category_id
                    LEFT JOIN service_type_master stm ON ds.service_type_id = stm.type_id
                    WHERE ds.$id_column = ? AND ds.employee_id = ?";
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid table']);
            exit();
    }

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . htmlspecialchars($conn->error)]);
        exit();
    }

    // Bind parameters - request_id is INT, employee_id is string
    $stmt->bind_param("is", $request_id, $user_id);
    
    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'message' => 'Execute failed: ' . htmlspecialchars($stmt->error)]);
        exit();
    }

    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        // Ensure we have the primary key in response
        if (!isset($row['request_id'])) {
            $row['request_id'] = $row[$id_column] ?? $request_id;
        }
        
        // Add convenience fields for frontend
        $row['employee_name'] = $row['full_name_th'] ?? ($row['full_name_en'] ?? '');
        $row['position'] = $row['position_name_th'] ?? ($row['position_name_en'] ?? '');
        $row['division'] = $row['division_name_th'] ?? ($row['division_name_en'] ?? '');
        $row['department'] = $row['department_name_th'] ?? ($row['department_name_en'] ?? '');
        $row['section'] = $row['section_name_th'] ?? ($row['section_name_en'] ?? '');
        
        // ✅ สำหรับ ID Card generation
        $row['display_name'] = $row['employee_name'];
        $row['table_name'] = $table;
        
        echo json_encode(['success' => true, 'request' => $row]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Request not found or access denied']);
    }

    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
} finally {
    $conn->close();
}
?>