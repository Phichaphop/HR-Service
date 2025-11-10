<?php
/**
 * API: Save Certificate Type
 * File: /api/save_certificate_type.php
 * Method: POST
 * Purpose: Add or Update certificate type
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../controllers/AuthController.php';

// Require admin role
AuthController::requireRole(['admin']);

// Response helper function
function sendResponse($success, $message, $data = null) {
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    sendResponse(false, 'Invalid JSON input');
}

// Extract and sanitize data
$cert_type_id = isset($input['cert_type_id']) && !empty($input['cert_type_id']) 
    ? (int)$input['cert_type_id'] 
    : 0;
$type_name_th = isset($input['type_name_th']) ? trim($input['type_name_th']) : '';
$type_name_en = isset($input['type_name_en']) ? trim($input['type_name_en']) : '';
$type_name_my = isset($input['type_name_my']) ? trim($input['type_name_my']) : '';
$is_active = isset($input['is_active']) ? (int)$input['is_active'] : 1;

// Validation
if (empty($type_name_th)) {
    sendResponse(false, 'กรุณากรอกชื่อประเภท (ไทย)');
}

if (strlen($type_name_th) > 255) {
    sendResponse(false, 'ชื่อประเภท (ไทย) ต้องไม่เกิน 255 ตัวอักษร');
}

// Database connection
$conn = getDbConnection();
if (!$conn) {
    sendResponse(false, 'Database connection failed');
}

try {
    if ($cert_type_id === 0) {
        // ====== INSERT NEW CERTIFICATE TYPE ======
        $sql = "INSERT INTO certificate_types 
                (type_name_th, type_name_en, type_name_my, is_active, created_at, updated_at) 
                VALUES (?, ?, ?, ?, NOW(), NOW())";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            sendResponse(false, 'Database prepare error: ' . $conn->error);
        }
        
        // Bind parameters: s=string, i=integer
        $stmt->bind_param('sssi', $type_name_th, $type_name_en, $type_name_my, $is_active);
        
        if ($stmt->execute()) {
            $new_id = $conn->insert_id;
            $stmt->close();
            
            sendResponse(true, 'ประเภทหนังสือรับรองถูกเพิ่มสำเร็จ', [
                'cert_type_id' => $new_id,
                'type_name_th' => $type_name_th
            ]);
        } else {
            $error = $stmt->error;
            $stmt->close();
            sendResponse(false, 'Insert failed: ' . $error);
        }
        
    } else {
        // ====== UPDATE EXISTING CERTIFICATE TYPE ======
        // First check if type exists
        $check_sql = "SELECT cert_type_id FROM certificate_types WHERE cert_type_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        if (!$check_stmt) {
            sendResponse(false, 'Database prepare error: ' . $conn->error);
        }
        
        $check_stmt->bind_param('i', $cert_type_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        $check_stmt->close();
        
        if ($check_result->num_rows === 0) {
            sendResponse(false, 'ไม่พบประเภทหนังสือรับรองที่ต้องการแก้ไข');
        }
        
        $sql = "UPDATE certificate_types 
                SET type_name_th = ?, 
                    type_name_en = ?, 
                    type_name_my = ?, 
                    is_active = ?, 
                    updated_at = NOW()
                WHERE cert_type_id = ?";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            sendResponse(false, 'Database prepare error: ' . $conn->error);
        }
        
        // Bind parameters: s=string, i=integer
        $stmt->bind_param('sssii', $type_name_th, $type_name_en, $type_name_my, $is_active, $cert_type_id);
        
        if ($stmt->execute()) {
            $affected_rows = $stmt->affected_rows;
            $stmt->close();
            
            if ($affected_rows > 0) {
                sendResponse(true, 'ประเภทหนังสือรับรองถูกอัปเดตสำเร็จ', [
                    'cert_type_id' => $cert_type_id,
                    'type_name_th' => $type_name_th
                ]);
            } else {
                sendResponse(false, 'ไม่มีการเปลี่ยนแปลงข้อมูล');
            }
        } else {
            $error = $stmt->error;
            $stmt->close();
            sendResponse(false, 'Update failed: ' . $error);
        }
    }
    
} catch (Exception $e) {
    sendResponse(false, 'Exception error: ' . $e->getMessage());
} finally {
    $conn->close();
}
?>