<?php
/**
 * API: Submit Document Delivery
 * Public access - No login required
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/db_config.php';

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

$employee_id = $input['employee_id'] ?? '';
$delivery_type = $input['delivery_type'] ?? '';
$service_type = $input['service_type'] ?? '';
$document_category_id = $input['document_category_id'] ?? '';
$remarks = $input['remarks'] ?? '';
$satisfaction_score = $input['satisfaction_score'] ?? '';

// Validation
if (empty($employee_id)) {
    echo json_encode(['success' => false, 'message' => 'กรุณากรอกรหัสพนักงาน']);
    exit();
}

if (empty($delivery_type)) {
    echo json_encode(['success' => false, 'message' => 'กรุณาเลือกประเภทการส่งเอกสาร']);
    exit();
}

if (empty($service_type)) {
    echo json_encode(['success' => false, 'message' => 'กรุณาเลือกประเภทการส่ง']);
    exit();
}

if (empty($document_category_id)) {
    echo json_encode(['success' => false, 'message' => 'กรุณาเลือกประเภทเอกสาร']);
    exit();
}

if (empty($satisfaction_score) || $satisfaction_score < 1 || $satisfaction_score > 5) {
    echo json_encode(['success' => false, 'message' => 'กรุณาให้คะแนนความพึงพอใจ 1-5']);
    exit();
}

$conn = getDbConnection();

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

// Verify employee exists
$check_sql = "SELECT employee_id FROM employees WHERE employee_id = ? AND status_id = 1";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("s", $employee_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows === 0) {
    $check_stmt->close();
    $conn->close();
    echo json_encode(['success' => false, 'message' => 'รหัสพนักงานไม่ถูกต้อง']);
    exit();
}
$check_stmt->close();

// Validate delivery_type
if (!in_array($delivery_type, ['ส่ง', 'รับ'])) {
    $conn->close();
    echo json_encode(['success' => false, 'message' => 'ประเภทการส่งเอกสารไม่ถูกต้อง']);
    exit();
}

// Validate service_type
if (!in_array($service_type, ['คนเดียว', 'กลุ่ม'])) {
    $conn->close();
    echo json_encode(['success' => false, 'message' => 'ประเภทการส่งไม่ถูกต้อง']);
    exit();
}

// Insert document delivery record
$sql = "INSERT INTO document_delivery 
        (employee_id, delivery_type, service_type, document_category_id, remarks, satisfaction_score, delivery_date) 
        VALUES (?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    $conn->close();
    echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
    exit();
}

$stmt->bind_param("sssisi", $employee_id, $delivery_type, $service_type, $document_category_id, $remarks, $satisfaction_score);

if ($stmt->execute()) {
    $delivery_id = $conn->insert_id;
    $stmt->close();
    $conn->close();
    
    echo json_encode([
        'success' => true,
        'message' => 'บันทึกสำเร็จ',
        'delivery_id' => $delivery_id
    ]);
} else {
    $error = $stmt->error;
    $stmt->close();
    $conn->close();
    
    echo json_encode([
        'success' => false,
        'message' => 'Failed to submit: ' . $error
    ]);
}
?>