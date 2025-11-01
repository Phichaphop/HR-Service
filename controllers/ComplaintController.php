<?php
/**
 * Complaint Controller
 * Handles complaint submission, review, and resolution logic
 * 
 * Endpoints:
 * - /api/file_complaint.php → ComplaintController::fileComplaint()
 * - /api/get_complaint_status.php → ComplaintController::getStatus()
 * - /api/resolve_complaint.php → ComplaintController::resolve()
 * - /views/admin/complaints.php → ComplaintController::listComplaints()
 * - /views/employee/file_complaint.php → form submission
 */

require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../models/Complaint.php';
require_once __DIR__ . '/../db/Localization.php';

class ComplaintController {
    
    /**
     * File a new complaint
     * POST /api/file_complaint.php
     * 
     * Parameters:
     * - complaint_type (required): Harassment, Discrimination, Policy Violation, etc.
     * - complaint_title (required): Short title
     * - complaint_detail (required): Detailed description
     * - department_involved (optional): Which department is involved
     * - person_involved (optional): Person's name (if known)
     * - incident_date (optional): When did it happen
     * - is_anonymous (optional): 1 or 0
     * - notify_employee (optional): 1 or 0
     * - employee_email (optional): Email for anonymous notification
     */
    public static function fileComplaint() {
        // Check authentication
        if (!AuthController::isAuthenticated()) {
            return [
                'success' => false,
                'message' => 'Not authenticated'
            ];
        }
        
        // Validate input
        $complaint_type = $_POST['complaint_type'] ?? '';
        $complaint_title = trim($_POST['complaint_title'] ?? '');
        $complaint_detail = trim($_POST['complaint_detail'] ?? '');
        
        if (empty($complaint_type) || empty($complaint_title) || empty($complaint_detail)) {
            return [
                'success' => false,
                'message' => 'Missing required fields'
            ];
        }
        
        // Validate complaint type
        $valid_types = [
            'Harassment',
            'Discrimination',
            'Policy Violation',
            'Unsafe Working Condition',
            'Unfair Treatment',
            'Other'
        ];
        
        if (!in_array($complaint_type, $valid_types)) {
            return [
                'success' => false,
                'message' => 'Invalid complaint type'
            ];
        }
        
        // Check minimum length
        if (strlen($complaint_detail) < 50) {
            return [
                'success' => false,
                'message' => 'Complaint detail must be at least 50 characters'
            ];
        }
        
        // Prepare data
        $data = [
            'complaint_type' => $complaint_type,
            'complaint_title' => $complaint_title,
            'complaint_detail' => $complaint_detail,
            'department_involved' => $_POST['department_involved'] ?? null,
            'person_involved' => $_POST['person_involved'] ?? null,
            'incident_date' => !empty($_POST['incident_date']) ? $_POST['incident_date'] : null,
            'is_anonymous' => isset($_POST['is_anonymous']) ? 1 : 0,
            'notify_employee' => isset($_POST['notify_employee']) ? 1 : 0,
            'employee_email' => $_POST['employee_email'] ?? null,
            'filed_by' => $_SESSION['user_id'] ?? null
        ];
        
        // If not anonymous, use current user
        if (!$data['is_anonymous']) {
            $data['employee_id'] = $_SESSION['user_id'];
        }
        
        // Create complaint
        $result = Complaint::create($data);
        
        if ($result['success']) {
            // Send notification email to HR/Admin (optional)
            self::notifyAdmins($result['complaint_id'], $data);
            
            // If employee wants notification, prepare for it
            if ($data['notify_employee'] && $data['employee_email']) {
                // Email will be sent when complaint is resolved
            }
        }
        
        return $result;
    }
    
    /**
     * Get complaint status and details
     * GET /api/get_complaint_status.php
     * 
     * Parameters:
     * - complaint_id (required)
     */
    public static function getStatus() {
        if (!AuthController::isAuthenticated()) {
            return ['success' => false, 'message' => 'Not authenticated'];
        }
        
        $complaint_id = $_GET['complaint_id'] ?? null;
        if (empty($complaint_id)) {
            return ['success' => false, 'message' => 'Complaint ID required'];
        }
        
        $complaint = Complaint::getById($complaint_id);
        
        if (!$complaint) {
            return ['success' => false, 'message' => 'Complaint not found'];
        }
        
        // Check authorization
        // Employee can only see their own complaint
        if ($_SESSION['role'] === 'employee' && $complaint['employee_id'] !== $_SESSION['user_id']) {
            return ['success' => false, 'message' => 'Unauthorized'];
        }
        
        return [
            'success' => true,
            'data' => $complaint
        ];
    }
    
    /**
     * Resolve complaint (Admin/Officer only)
     * PUT /api/resolve_complaint.php
     * 
     * Parameters:
     * - complaint_id (required)
     * - resolution (required): How it was resolved
     * - new_status (optional): New status (Resolved, Closed, etc.)
     */
    public static function resolve() {
        // Require admin or officer
        AuthController::requireRole(['admin', 'officer']);
        
        $complaint_id = $_POST['complaint_id'] ?? null;
        $resolution = trim($_POST['resolution'] ?? '');
        $new_status = $_POST['new_status'] ?? 'Resolved';
        
        if (empty($complaint_id) || empty($resolution)) {
            return [
                'success' => false,
                'message' => 'Missing required fields'
            ];
        }
        
        // Get current complaint
        $complaint = Complaint::getById($complaint_id);
        
        if (!$complaint) {
            return ['success' => false, 'message' => 'Complaint not found'];
        }
        
        // Update status
        if ($new_status === 'Resolved') {
            $result = Complaint::resolve($complaint_id, $resolution, $_SESSION['user_id']);
        } else if ($new_status === 'Closed') {
            $result = Complaint::close($complaint_id, $resolution, $_SESSION['user_id']);
        } else {
            $result = Complaint::updateStatus(
                $complaint_id,
                $new_status,
                $_SESSION['user_id'],
                $resolution,
                $_SESSION['user_id']
            );
        }
        
        if ($result['success'] && $complaint['notify_employee'] && $complaint['employee_email']) {
            // Send email notification
            self::sendResolutionEmail($complaint, $resolution);
        }
        
        return $result;
    }
    
    /**
     * Assign complaint to officer
     * PUT /api/assign_complaint.php
     * 
     * Parameters:
     * - complaint_id (required)
     * - assigned_to (required): Officer's employee ID
     * - notes (optional): Assignment notes
     */
    public static function assign() {
        AuthController::requireRole(['admin']);
        
        $complaint_id = $_POST['complaint_id'] ?? null;
        $assigned_to = $_POST['assigned_to'] ?? null;
        $notes = $_POST['notes'] ?? 'Assigned for investigation';
        
        if (empty($complaint_id) || empty($assigned_to)) {
            return [
                'success' => false,
                'message' => 'Missing required fields'
            ];
        }
        
        // Check if officer exists
        $conn = getDbConnection();
        $stmt = $conn->prepare("SELECT employee_id FROM employees WHERE employee_id = ? AND role_id IN (SELECT role_id FROM roles WHERE role_name = 'officer')");
        $stmt->bind_param('s', $assigned_to);
        $stmt->execute();
        $result_check = $stmt->get_result();
        
        if ($result_check->num_rows === 0) {
            $conn->close();
            return ['success' => false, 'message' => 'Invalid officer selected'];
        }
        
        $stmt->close();
        $conn->close();
        
        // Update assignment
        return Complaint::updateStatus($complaint_id, 'Assigned', $assigned_to, $notes, $_SESSION['user_id']);
    }
    
    /**
     * List complaints (with filtering)
     * GET /views/admin/complaints.php
     * 
     * Parameters (Query String):
     * - status: New, Assigned, Under Review, In Progress, Resolved, Closed
     * - type: Harassment, Discrimination, etc.
     * - assigned_to: Employee ID
     * - search: Search in title/detail
     * - sort: created_at, status, type
     * - order: ASC, DESC
     */
    public static function listComplaints() {
        AuthController::requireRole(['admin', 'officer']);
        
        // Get filters from URL
        $filters = [
            'status' => $_GET['status'] ?? null,
            'type' => $_GET['type'] ?? null,
            'assigned_to' => $_GET['assigned_to'] ?? null,
            'search' => $_GET['search'] ?? null
        ];
        
        // Get pagination
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = intval($_GET['limit'] ?? 20);
        $offset = ($page - 1) * $limit;
        
        // Only admins can see all complaints
        // Officers see only assigned to them
        if ($_SESSION['role'] === 'officer' && empty($filters['assigned_to'])) {
            $filters['assigned_to'] = $_SESSION['user_id'];
        }
        
        // Get complaints
        $complaints = Complaint::getAll($filters, $limit, $offset);
        
        // Get statistics
        $stats = Complaint::getStatistics();
        
        return [
            'success' => true,
            'complaints' => $complaints,
            'stats' => $stats,
            'page' => $page,
            'limit' => $limit,
            'total' => $stats['total_complaints']
        ];
    }
    
    /**
     * Add note/update investigation
     * POST /api/add_complaint_note.php
     * 
     * Parameters:
     * - complaint_id (required)
     * - note (required): Investigation note
     */
    public static function addNote() {
        AuthController::requireRole(['admin', 'officer']);
        
        $complaint_id = $_POST['complaint_id'] ?? null;
        $note = trim($_POST['note'] ?? '');
        
        if (empty($complaint_id) || empty($note)) {
            return [
                'success' => false,
                'message' => 'Missing required fields'
            ];
        }
        
        $complaint = Complaint::getById($complaint_id);
        
        if (!$complaint) {
            return ['success' => false, 'message' => 'Complaint not found'];
        }
        
        // Authorization check
        if ($_SESSION['role'] === 'officer' && $complaint['assigned_to'] !== $_SESSION['user_id']) {
            return ['success' => false, 'message' => 'Unauthorized'];
        }
        
        // Add note
        $new_notes = $complaint['investigation_notes'] . "\n\n[" . date('Y-m-d H:i') . "] " . $_SESSION['full_name_th'] . ": " . $note;
        
        $conn = getDbConnection();
        $stmt = $conn->prepare("UPDATE anonymous_complaints SET investigation_notes = ? WHERE complaint_id = ?");
        $stmt->bind_param('si', $new_notes, $complaint_id);
        
        $result = $stmt->execute();
        $stmt->close();
        $conn->close();
        
        return $result ? 
            ['success' => true, 'message' => 'Note added'] :
            ['success' => false, 'message' => 'Failed to add note'];
    }
    
    // ===== PRIVATE HELPER METHODS =====
    
    /**
     * Notify admins about new complaint
     */
    private static function notifyAdmins($complaint_id, $data) {
        // TODO: Send email to HR/Admin group
        // This is a placeholder for email notification system
    }
    
    /**
     * Send resolution email to employee
     */
    private static function sendResolutionEmail($complaint, $resolution) {
        // TODO: Send email with resolution details
        // This is a placeholder for email notification system
    }
}

// API Endpoint Usage Example
/*
// File complaint
$result = ComplaintController::fileComplaint();
echo json_encode($result);

// Get status
$result = ComplaintController::getStatus();
echo json_encode($result);

// Resolve complaint
$result = ComplaintController::resolve();
echo json_encode($result);
*/
?>