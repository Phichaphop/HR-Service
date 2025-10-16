<?php
/**
 * API: Bulk Update Requests
 * Update multiple requests at once
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../controllers/AuthController.php';

// Require admin role only
AuthController::requireRole(['admin']);

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

$requests = $input['requests'] ?? [];
$new_status = $input['status'] ?? '';

if (empty($requests) || empty($new_status)) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit();
}

// Validate status
$allowed_statuses = ['New', 'In Progress', 'Complete', 'Cancelled'];
if (!in_array($new_status, $allowed_statuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit();
}

$conn = getDbConnection();

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

session_start();
$handler_id = $_SESSION['user_id'] ?? '';

$conn->begin_transaction();

try {
    $updated = 0;
    
    foreach ($requests as $request) {
        $table = $request['table'] ?? '';
        $request_id = $request['id'] ?? '';
        
        // Validate table
        $allowed_tables = [
            'leave_requests', 'certificate_requests', 'id_card_requests',
            'shuttle_bus_requests', 'locker_requests', 'supplies_requests',
            'skill_test_requests', 'document_submissions'
        ];
        
        if (!in_array($table, $allowed_tables)) {
            continue;
        }
        
        $sql = "UPDATE $table 
                SET status = ?, 
                    handler_id = ?,
                    updated_at = CURRENT_TIMESTAMP 
                WHERE request_id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $new_status, $handler_id, $request_id);
        
        if ($stmt->execute()) {
            $updated++;
        }
        
        $stmt->close();
    }
    
    $conn->commit();
    $conn->close();
    
    echo json_encode([
        'success' => true,
        'message' => "Successfully updated $updated request(s)",
        'updated_count' => $updated
    ]);
    
} catch (Exception $e) {
    $conn->rollback();
    $conn->close();
    
    echo json_encode([
        'success' => false,
        'message' => 'Failed to update requests: ' . $e->getMessage()
    ]);
}
?>