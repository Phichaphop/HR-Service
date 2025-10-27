<?php
/**
 * API: Get Request Details - FULLY FIXED VERSION
 * Fetches detailed information about a specific request
 * FIXES:
 * 1. Properly retrieves certificate_requests data including hiring_type, date_of_hire, base_salary
 * 2. Includes certificate_type information (cert_type_id and certificate type name)
 * 3. Handles all request types correctly
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

try {
    // Build query based on table type
    switch ($table) {
        case 'certificate_requests':
            // Certificate requests - all data is stored directly in the table
            // Also JOIN with certificate_types to get certificate type name
            $sql = "SELECT 
                        cr.*,
                        ct.cert_type_id,
                        ct.type_name_th,
                        ct.type_name_en,
                        ct.type_name_my
                    FROM certificate_requests cr
                    LEFT JOIN certificate_types ct ON cr.cert_type_id = ct.cert_type_id
                    WHERE cr.$id_column = ? AND cr.employee_id = ?";
            break;
            
        case 'leave_requests':
            $sql = "SELECT * FROM $table WHERE $id_column = ? AND employee_id = ?";
            break;
            
        case 'id_card_requests':
            $sql = "SELECT * FROM $table WHERE $id_column = ? AND employee_id = ?";
            break;
            
        case 'shuttle_bus_requests':
            $sql = "SELECT * FROM $table WHERE $id_column = ? AND employee_id = ?";
            break;
            
        case 'locker_requests':
            $sql = "SELECT * FROM $table WHERE $id_column = ? AND employee_id = ?";
            break;
            
        case 'supplies_requests':
            $sql = "SELECT * FROM $table WHERE $id_column = ? AND employee_id = ?";
            break;
            
        case 'skill_test_requests':
            $sql = "SELECT * FROM $table WHERE $id_column = ? AND employee_id = ?";
            break;
            
        case 'document_submissions':
            $sql = "SELECT 
                        ds.*,
                        scm.category_name_th,
                        scm.category_name_en,
                        scm.category_name_my,
                        stm.type_name_th as service_type_name_th,
                        stm.type_name_en as service_type_name_en,
                        stm.type_name_my as service_type_name_my
                    FROM $table ds
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