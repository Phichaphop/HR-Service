<?php
/**
 * ========================================================
 * COMPLAINTS API
 * File: /api/complaints.php
 * ========================================================
 * UPDATED: Removed icon_class field completely
 * Features:
 * ✅ CRUD operations for complaints
 * ✅ Anonymous complaint submission (hash employee_id)
 * ✅ Status updates by officers
 * ✅ Assignment to officers
 * ✅ Multi-language support
 * ========================================================
 */

require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../controllers/AuthController.php';

// Set JSON header
header('Content-Type: application/json; charset=utf-8');

// Ensure authenticated
AuthController::requireAuth();

// Get request method
$method = $_SERVER['REQUEST_METHOD'];
$user_id = $_SESSION['user_id'] ?? '';
$user_role = $_SESSION['role'] ?? '';

// Database connection
$conn = getDbConnection();

if (!$conn) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed'
    ]);
    exit;
}

/**
 * GET: Fetch complaints
 */
if ($method === 'GET') {
    
    if (isset($_GET['id'])) {
        // Get single complaint
        $id = intval($_GET['id']);
        
        $stmt = $conn->prepare("
            SELECT 
                c.*,
                cm.category_name_th,
                cm.category_name_en,
                cm.category_name_my,
                e.full_name_th as officer_name
            FROM complaints c
            LEFT JOIN complaint_category_master cm ON c.category_id = cm.category_id
            LEFT JOIN employees e ON c.assigned_to_officer_id = e.employee_id
            WHERE c.complaint_id = ?
        ");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            // Hide hash from response
            unset($row['complainer_id_hash']);
            
            echo json_encode([
                'success' => true,
                'data' => $row
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Complaint not found'
            ]);
        }
        $stmt->close();
        
    } elseif (isset($_GET['my_complaints'])) {
        // Get complaints by current user (using hash)
        $hash = hash('sha256', $user_id);
        
        $stmt = $conn->prepare("
            SELECT 
                c.complaint_id,
                c.category_id,
                c.subject,
                c.description,
                c.status,
                c.officer_response,
                c.rating,
                c.rating_comment,
                c.created_at,
                c.updated_at,
                c.resolved_at,
                cm.category_name_th,
                cm.category_name_en,
                cm.category_name_my
            FROM complaints c
            LEFT JOIN complaint_category_master cm ON c.category_id = cm.category_id
            WHERE c.complainer_id_hash = ?
            ORDER BY c.created_at DESC
        ");
        $stmt->bind_param("s", $hash);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $complaints = [];
        while ($row = $result->fetch_assoc()) {
            $complaints[] = $row;
        }
        
        echo json_encode([
            'success' => true,
            'data' => $complaints,
            'count' => count($complaints)
        ]);
        $stmt->close();
        
    } else {
        // Get all complaints (Officer/Admin only)
        AuthController::requireRole(['officer', 'admin']);
        
        $query = "
            SELECT 
                c.complaint_id,
                c.category_id,
                c.subject,
                c.status,
                c.assigned_to_officer_id,
                c.created_at,
                c.updated_at,
                cm.category_name_th,
                cm.category_name_en,
                cm.category_name_my,
                e.full_name_th as officer_name
            FROM complaints c
            LEFT JOIN complaint_category_master cm ON c.category_id = cm.category_id
            LEFT JOIN employees e ON c.assigned_to_officer_id = e.employee_id
            ORDER BY c.created_at DESC
        ";
        
        $result = $conn->query($query);
        $complaints = [];
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $complaints[] = $row;
            }
        }
        
        echo json_encode([
            'success' => true,
            'data' => $complaints,
            'count' => count($complaints)
        ]);
    }
}

/**
 * POST: Create new complaint (Anonymous)
 */
elseif ($method === 'POST') {
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    if (empty($data['category_id']) || 
        empty($data['subject']) || 
        empty($data['description'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Required fields missing'
        ]);
        exit;
    }
    
    // Hash the employee_id for anonymity
    $hash = hash('sha256', $user_id);
    
    // Get IP and User Agent
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
    $browser_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Insert complaint
        $stmt = $conn->prepare("
            INSERT INTO complaints 
            (complainer_id_hash, category_id, subject, description, attachment_path, status) 
            VALUES (?, ?, ?, ?, ?, 'New')
        ");
        
        $attachment = $data['attachment_path'] ?? null;
        
        $stmt->bind_param(
            "sisss",
            $hash,
            $data['category_id'],
            $data['subject'],
            $data['description'],
            $attachment
        );
        
        if (!$stmt->execute()) {
            throw new Exception('Error creating complaint');
        }
        
        $complaint_id = $conn->insert_id;
        $stmt->close();
        
        // Insert audit record (store plain employee_id for admin access only)
        $audit_stmt = $conn->prepare("
            INSERT INTO complaint_complainer_audit 
            (complaint_id, complainer_id_plain, complainer_id_hash, ip_address, browser_agent) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $audit_stmt->bind_param(
            "issss",
            $complaint_id,
            $user_id,
            $hash,
            $ip_address,
            $browser_agent
        );
        
        if (!$audit_stmt->execute()) {
            throw new Exception('Error creating audit record');
        }
        
        $audit_stmt->close();
        
        // Commit transaction
        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Complaint submitted successfully',
            'complaint_id' => $complaint_id
        ]);
        
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

/**
 * PUT: Update complaint (Officer/Admin)
 */
elseif ($method === 'PUT') {
    
    AuthController::requireRole(['officer', 'admin']);
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (empty($data['complaint_id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Complaint ID required'
        ]);
        exit;
    }
    
    $complaint_id = intval($data['complaint_id']);
    $action = $data['action'] ?? 'update';
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        
        if ($action === 'assign') {
            // Assign to officer
            $stmt = $conn->prepare("
                UPDATE complaints 
                SET 
                    assigned_to_officer_id = ?,
                    assigned_date = NOW()
                WHERE complaint_id = ?
            ");
            $stmt->bind_param("si", $data['assigned_to_officer_id'], $complaint_id);
            
            $log_action = 'Assigned to officer';
            
        } elseif ($action === 'update') {
            // Update status and response
            $stmt = $conn->prepare("
                UPDATE complaints 
                SET 
                    status = ?,
                    officer_response = ?,
                    officer_remarks = ?,
                    response_date = NOW(),
                    resolved_at = CASE WHEN ? IN ('Resolved', 'Closed') THEN NOW() ELSE resolved_at END
                WHERE complaint_id = ?
            ");
            $stmt->bind_param(
                "ssssi",
                $data['status'],
                $data['officer_response'],
                $data['officer_remarks'],
                $data['status'],
                $complaint_id
            );
            
            $log_action = 'Updated complaint';
            
        } else {
            throw new Exception('Invalid action');
        }
        
        if (!$stmt->execute()) {
            throw new Exception('Error updating complaint');
        }
        
        $stmt->close();
        
        // Get old status for log
        $old_status_stmt = $conn->prepare("SELECT status FROM complaints WHERE complaint_id = ?");
        $old_status_stmt->bind_param("i", $complaint_id);
        $old_status_stmt->execute();
        $old_status_result = $old_status_stmt->get_result();
        $old_status_row = $old_status_result->fetch_assoc();
        $old_status = $old_status_row['status'] ?? null;
        $old_status_stmt->close();
        
        // Log activity
        $log_stmt = $conn->prepare("
            INSERT INTO complaint_activity_log 
            (complaint_id, action, action_by_officer_id, old_status, new_status, remarks) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $new_status = $data['status'] ?? null;
        $remarks = $data['officer_remarks'] ?? '';
        
        $log_stmt->bind_param(
            "isssss",
            $complaint_id,
            $log_action,
            $user_id,
            $old_status,
            $new_status,
            $remarks
        );
        
        $log_stmt->execute();
        $log_stmt->close();
        
        // Commit transaction
        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Complaint updated successfully'
        ]);
        
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

/**
 * Invalid method
 */
else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}

$conn->close();