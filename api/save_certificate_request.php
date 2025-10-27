<?php
/**
 * Certificate Request API - IMPROVED VERSION
 * Stores ALL employee data including: name, position, division, hire date, hiring type, salary
 * Date: 27 October 2025
 */

header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(E_ALL);

ob_start();

try {
    require_once __DIR__ . '/../config/db_config.php';
    require_once __DIR__ . '/../controllers/AuthController.php';
    
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    AuthController::requireAuth();
    
    $user_id = $_SESSION['user_id'] ?? '';
    if (empty($user_id)) {
        throw new Exception('User not authenticated');
    }
    
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        throw new Exception('Invalid JSON input');
    }
    
    $cert_type_id = isset($input['cert_type_id']) ? (int)$input['cert_type_id'] : 0;
    $purpose = trim($input['purpose'] ?? '');
    
    if (empty($cert_type_id)) {
        throw new Exception('Certificate type is required');
    }
    
    if (empty($purpose) || strlen($purpose) < 5) {
        throw new Exception('Purpose is required (minimum 5 characters)');
    }
    
    $conn = getDbConnection();
    if (!$conn) {
        throw new Exception('Database connection failed');
    }
    
    // ============================================================
    // STEP 1: Fetch Employee with Complete Information
    // ============================================================
    $sql_employee = "SELECT 
                        e.employee_id,
                        e.full_name_th,
                        e.full_name_en,
                        p.prefix_th,
                        p.prefix_en,
                        pos.position_name_th,
                        pos.position_name_en,
                        d.division_name_th,
                        d.division_name_en,
                        e.date_of_hire,
                        ht.type_name_th,
                        ht.type_name_en
                    FROM employees e
                    LEFT JOIN prefix_master p ON e.prefix_id = p.prefix_id
                    LEFT JOIN position_master pos ON e.position_id = pos.position_id
                    LEFT JOIN division_master d ON e.division_id = d.division_id
                    LEFT JOIN hiring_type_master ht ON e.hiring_type_id = ht.hiring_type_id
                    WHERE e.employee_id = ?
                    LIMIT 1";
    
    $stmt = $conn->prepare($sql_employee);
    if (!$stmt) {
        throw new Exception('Prepare error: ' . $conn->error);
    }
    
    $stmt->bind_param('s', $user_id);
    if (!$stmt->execute()) {
        throw new Exception('Execute error: ' . $stmt->error);
    }
    
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        throw new Exception('Employee record not found');
    }
    
    $employee = $result->fetch_assoc();
    $stmt->close();
    
    // ============================================================
    // STEP 2: Build Employee Name (Prefix + Full Name)
    // ============================================================
    $prefix_th = trim($employee['prefix_th'] ?? '');
    $fullname_th = trim($employee['full_name_th'] ?? '');
    $employee_name = trim($prefix_th . ' ' . $fullname_th);
    
    if (empty($employee_name)) {
        $prefix_en = trim($employee['prefix_en'] ?? '');
        $fullname_en = trim($employee['full_name_en'] ?? '');
        $employee_name = trim($prefix_en . ' ' . $fullname_en);
    }
    
    if (empty($employee_name)) {
        throw new Exception('Employee name not found');
    }
    
    // ============================================================
    // STEP 3: Extract All Employee Data
    // ============================================================
    $position = trim($employee['position_name_th'] ?? $employee['position_name_en'] ?? '');
    $division = trim($employee['division_name_th'] ?? $employee['division_name_en'] ?? '');
    $date_of_hire = $employee['date_of_hire'] ?? null;
    $hiring_type = trim($employee['type_name_th'] ?? $employee['type_name_en']);
    $base_salary = (float)($employee['base_salary'] ?? 0);
    
    // ============================================================
    // STEP 4: Generate Certificate Number
    // ============================================================
    $cert_no = 'CERT-' . date('Ymd') . '-' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
    
    // ============================================================
    // STEP 5: INSERT with COMPLETE Data
    // ============================================================
    $sql_insert = "INSERT INTO certificate_requests 
                    (
                        certificate_no,
                        employee_id,
                        cert_type_id,
                        employee_name,
                        position,
                        division,
                        date_of_hire,
                        hiring_type,
                        base_salary,
                        purpose,
                        status,
                        created_at,
                        updated_at
                    )
                   VALUES 
                    (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'New', NOW(), NOW())";
    
    $stmt_insert = $conn->prepare($sql_insert);
    if (!$stmt_insert) {
        throw new Exception('Insert prepare error: ' . $conn->error);
    }
    
    // Bind parameters: 10 total
    // Type sequence: s=string, i=int, d=double
    if (!$stmt_insert->bind_param(
        'ssissssdss',
        $cert_no,           // s - certificate_no
        $user_id,           // s - employee_id
        $cert_type_id,      // i - cert_type_id
        $employee_name,     // s - employee_name ✅ COMPLETE
        $position,          // s - position ✅ COMPLETE
        $division,          // s - division ✅ COMPLETE
        $date_of_hire,      // s - date_of_hire ✅ COMPLETE
        $hiring_type,       // s - hiring_type ✅ COMPLETE
        $base_salary,       // d - base_salary ✅ COMPLETE
        $purpose            // s - purpose
    )) {
        throw new Exception('Bind error: ' . $stmt_insert->error);
    }
    
    if (!$stmt_insert->execute()) {
        throw new Exception('Insert execute error: ' . $stmt_insert->error);
    }
    
    $request_id = $conn->insert_id;
    $stmt_insert->close();
    $conn->close();
    
    // ============================================================
    // SUCCESS Response
    // ============================================================
    ob_end_clean();
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Certificate request submitted successfully',
        'cert_no' => $cert_no,
        'request_id' => $request_id
    ]);
    
} catch (Exception $e) {
    ob_end_clean();
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>