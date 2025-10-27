<?php
/**
 * API: Get Request Details for Admin/Officer Management
 * File: api/admin_get_request_details.php
 * 
 * Purpose: Fetch detailed information for Admin/Officer request management
 * Features:
 * - Role-based access (Admin/Officer only)
 * - Fetch from ALL tables without employee_id filter
 * - Join employee and handler information
 * - Support all 8 request types
 */

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../controllers/AuthController.php';

// Require authentication
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

AuthController::requireAuth();

// Check if user is admin or officer
$user_role = $_SESSION['user_role'] ?? 'employee';

if (!in_array($user_role, ['admin', 'officer'])) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Access denied - Admin/Officer only'
    ]);
    exit();
}

$table = $_GET['table'] ?? '';
$request_id = $_GET['id'] ?? '';

if (empty($table) || empty($request_id)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Missing parameters: table and id required'
    ]);
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
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid table name'
    ]);
    exit();
}

$conn = getDbConnection();

if (!$conn) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed'
    ]);
    exit();
}

// Determine the primary key column name
$id_column = ($table === 'document_submissions') ? 'submission_id' : 'request_id';

try {
    $sql = '';
    
    // Build query based on table type with all joins
    switch ($table) {
        case 'certificate_requests':
            $sql = "SELECT 
                        cr.*,
                        ct.cert_type_id,
                        ct.type_name_th as cert_type_name_th,
                        ct.type_name_en as cert_type_name_en,
                        ct.type_name_my as cert_type_name_my,
                        e.full_name_th as employee_name_th,
                        e.full_name_en as employee_name_en,
                        e.position_id,
                        e.division_id,
                        e.department_id,
                        p.position_name_th,
                        p.position_name_en,
                        d.division_name_th,
                        d.division_name_en,
                        dep.department_name_th,
                        dep.department_name_en,
                        e2.full_name_th as handler_name_th,
                        e2.full_name_en as handler_name_en
                    FROM certificate_requests cr
                    LEFT JOIN certificate_types ct ON cr.cert_type_id = ct.cert_type_id
                    LEFT JOIN employees e ON cr.employee_id = e.employee_id
                    LEFT JOIN position_master p ON e.position_id = p.position_id
                    LEFT JOIN division_master d ON e.division_id = d.division_id
                    LEFT JOIN department_master dep ON e.department_id = dep.department_id
                    LEFT JOIN employees e2 ON cr.handler_id = e2.employee_id
                    WHERE cr.$id_column = ?";
            break;
            
        case 'leave_requests':
            $sql = "SELECT 
                        lr.*,
                        e.full_name_th as employee_name_th,
                        e.full_name_en as employee_name_en,
                        e.position_id,
                        e.division_id,
                        p.position_name_th,
                        p.position_name_en,
                        d.division_name_th,
                        d.division_name_en,
                        e2.full_name_th as handler_name_th,
                        e2.full_name_en as handler_name_en
                    FROM leave_requests lr
                    LEFT JOIN employees e ON lr.employee_id = e.employee_id
                    LEFT JOIN position_master p ON e.position_id = p.position_id
                    LEFT JOIN division_master d ON e.division_id = d.division_id
                    LEFT JOIN employees e2 ON lr.handler_id = e2.employee_id
                    WHERE lr.$id_column = ?";
            break;
            
        case 'id_card_requests':
            $sql = "SELECT 
                        ir.*,
                        e.full_name_th as employee_name_th,
                        e.full_name_en as employee_name_en,
                        e.position_id,
                        e.division_id,
                        p.position_name_th,
                        p.position_name_en,
                        d.division_name_th,
                        d.division_name_en,
                        e2.full_name_th as handler_name_th,
                        e2.full_name_en as handler_name_en
                    FROM id_card_requests ir
                    LEFT JOIN employees e ON ir.employee_id = e.employee_id
                    LEFT JOIN position_master p ON e.position_id = p.position_id
                    LEFT JOIN division_master d ON e.division_id = d.division_id
                    LEFT JOIN employees e2 ON ir.handler_id = e2.employee_id
                    WHERE ir.$id_column = ?";
            break;
            
        case 'shuttle_bus_requests':
            $sql = "SELECT 
                        sr.*,
                        e.full_name_th as employee_name_th,
                        e.full_name_en as employee_name_en,
                        e.position_id,
                        e.division_id,
                        p.position_name_th,
                        p.position_name_en,
                        d.division_name_th,
                        d.division_name_en,
                        e2.full_name_th as handler_name_th,
                        e2.full_name_en as handler_name_en
                    FROM shuttle_bus_requests sr
                    LEFT JOIN employees e ON sr.employee_id = e.employee_id
                    LEFT JOIN position_master p ON e.position_id = p.position_id
                    LEFT JOIN division_master d ON e.division_id = d.division_id
                    LEFT JOIN employees e2 ON sr.handler_id = e2.employee_id
                    WHERE sr.$id_column = ?";
            break;
            
        case 'locker_requests':
            $sql = "SELECT 
                        lr.*,
                        e.full_name_th as employee_name_th,
                        e.full_name_en as employee_name_en,
                        e.position_id,
                        e.division_id,
                        p.position_name_th,
                        p.position_name_en,
                        d.division_name_th,
                        d.division_name_en,
                        e2.full_name_th as handler_name_th,
                        e2.full_name_en as handler_name_en,
                        lm.locker_number,
                        lm.location as locker_location
                    FROM locker_requests lr
                    LEFT JOIN employees e ON lr.employee_id = e.employee_id
                    LEFT JOIN position_master p ON e.position_id = p.position_id
                    LEFT JOIN division_master d ON e.division_id = d.division_id
                    LEFT JOIN employees e2 ON lr.handler_id = e2.employee_id
                    LEFT JOIN locker_master lm ON lr.locker_id = lm.locker_id
                    WHERE lr.$id_column = ?";
            break;
            
        case 'supplies_requests':
            $sql = "SELECT 
                        supp.*,
                        e.full_name_th as employee_name_th,
                        e.full_name_en as employee_name_en,
                        e.position_id,
                        e.division_id,
                        p.position_name_th,
                        p.position_name_en,
                        d.division_name_th,
                        d.division_name_en,
                        e2.full_name_th as handler_name_th,
                        e2.full_name_en as handler_name_en
                    FROM supplies_requests supp
                    LEFT JOIN employees e ON supp.employee_id = e.employee_id
                    LEFT JOIN position_master p ON e.position_id = p.position_id
                    LEFT JOIN division_master d ON e.division_id = d.division_id
                    LEFT JOIN employees e2 ON supp.handler_id = e2.employee_id
                    WHERE supp.$id_column = ?";
            break;
            
        case 'skill_test_requests':
            $sql = "SELECT 
                        st.*,
                        e.full_name_th as employee_name_th,
                        e.full_name_en as employee_name_en,
                        e.position_id,
                        e.division_id,
                        p.position_name_th,
                        p.position_name_en,
                        d.division_name_th,
                        d.division_name_en,
                        e2.full_name_th as handler_name_th,
                        e2.full_name_en as handler_name_en
                    FROM skill_test_requests st
                    LEFT JOIN employees e ON st.employee_id = e.employee_id
                    LEFT JOIN position_master p ON e.position_id = p.position_id
                    LEFT JOIN division_master d ON e.division_id = d.division_id
                    LEFT JOIN employees e2 ON st.handler_id = e2.employee_id
                    WHERE st.$id_column = ?";
            break;
            
        case 'document_submissions':
            $sql = "SELECT 
                        ds.*,
                        scm.category_name_th,
                        scm.category_name_en,
                        scm.category_name_my,
                        stm.type_name_th as service_type_name_th,
                        stm.type_name_en as service_type_name_en,
                        stm.type_name_my as service_type_name_my,
                        e.full_name_th as employee_name_th,
                        e.full_name_en as employee_name_en,
                        e.position_id,
                        e.division_id,
                        p.position_name_th,
                        p.position_name_en,
                        d.division_name_th,
                        d.division_name_en,
                        e2.full_name_th as handler_name_th,
                        e2.full_name_en as handler_name_en
                    FROM document_submissions ds
                    LEFT JOIN service_category_master scm ON ds.service_category_id = scm.category_id
                    LEFT JOIN service_type_master stm ON ds.service_type_id = stm.type_id
                    LEFT JOIN employees e ON ds.employee_id = e.employee_id
                    LEFT JOIN position_master p ON e.position_id = p.position_id
                    LEFT JOIN division_master d ON e.division_id = d.division_id
                    LEFT JOIN employees e2 ON ds.handler_id = e2.employee_id
                    WHERE ds.$id_column = ?";
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid table']);
            exit();
    }

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . htmlspecialchars($conn->error));
    }

    // Bind parameter - request_id is INT
    $stmt->bind_param("i", $request_id);
    
    if (!$stmt->execute()) {
        throw new Exception('Execute failed: ' . htmlspecialchars($stmt->error));
    }

    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        // Ensure we have the primary key in response
        if (!isset($row['request_id'])) {
            $row['request_id'] = $row[$id_column] ?? $request_id;
        }
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'request' => $row
        ]);
    } else {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Request not found'
        ]);
    }

    $stmt->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>