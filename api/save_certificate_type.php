<?php
/**
 * API: Save Certificate Type
 * Admin only
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../controllers/AuthController.php';

AuthController::requireRole(['admin']);

$input = json_decode(file_get_contents('php://input'), true);

$cert_type_id = $input['cert_type_id'] ?? '';
$type_name_th = $input['type_name_th'] ?? '';
$type_name_en = $input['type_name_en'] ?? '';
$type_name_my = $input['type_name_my'] ?? '';
$template_content = $input['template_content'] ?? '';
$is_active = isset($input['is_active']) ? (int)$input['is_active'] : 1;

if (empty($type_name_th) || empty($template_content)) {
    echo json_encode(['success' => false, 'message' => 'กรุณากรอกข้อมูลที่จำเป็น']);
    exit();
}

$conn = getDbConnection();

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

if (empty($cert_type_id)) {
    // Insert new
    $sql = "INSERT INTO certificate_types (type_name_th, type_name_en, type_name_my, template_content, is_active) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $type_name_th, $type_name_en, $type_name_my, $template_content, $is_active);
} else {
    // Update existing
    $sql = "UPDATE certificate_types 
            SET type_name_th = ?, type_name_en = ?, type_name_my = ?, template_content = ?, is_active = ?, updated_at = CURRENT_TIMESTAMP 
            WHERE cert_type_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssii", $type_name_th, $type_name_en, $type_name_my, $template_content, $is_active, $cert_type_id);
}

if ($stmt->execute()) {
    $stmt->close();
    $conn->close();
    echo json_encode(['success' => true, 'message' => 'บันทึกสำเร็จ']);
} else {
    $error = $stmt->error;
    $stmt->close();
    $conn->close();
    echo json_encode(['success' => false, 'message' => 'Failed: ' . $error]);
}
?>