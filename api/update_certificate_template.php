<?php
/**
 * API: Update Certificate Template
 * File: /api/update_certificate_template.php
 * Purpose: บันทึก/อัพเดท template สำหรับประเภทหนังสือรับรอง
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../controllers/AuthController.php';

// Require admin role
AuthController::requireRole(['admin']);

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
    exit();
}

$cert_type_id = (int)($input['cert_type_id'] ?? 0);
$template_content = $input['template_content'] ?? '';

if ($cert_type_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid certificate type ID']);
    exit();
}

if (empty($template_content)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Template content is required']);
    exit();
}

$conn = getDbConnection();
if (!$conn) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

// Update template
$stmt = $conn->prepare("
    UPDATE certificate_types 
    SET template_content = ?, updated_at = CURRENT_TIMESTAMP 
    WHERE cert_type_id = ?
");

if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
    $conn->close();
    exit();
}

$stmt->bind_param("si", $template_content, $cert_type_id);

if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Execute failed: ' . $stmt->error]);
    $stmt->close();
    $conn->close();
    exit();
}

$affected = $stmt->affected_rows;
$stmt->close();
$conn->close();

if ($affected === 0) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Certificate type not found']);
    exit();
}

http_response_code(200);
echo json_encode([
    'success' => true,
    'message' => 'Template updated successfully',
    'affected_rows' => $affected
]);
?>