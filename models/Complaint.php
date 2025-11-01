<?php
/**
 * Complaint Model Class
 * Handles Anonymous Complaints CRUD Operations
 * 
 * Used by:
 * - /controllers/ComplaintController.php
 * - /views/employee/file_complaint.php (Employee filing)
 * - /views/admin/complaints.php (Admin viewing)
 * - /views/officer/complaints_review.php (Officer reviewing)
 */

class Complaint {
    
    /**
     * File a new anonymous complaint
     * @param array $data
     * @return array
     */
    public static function create($data) {
        $conn = getDbConnection();
        
        try {
            $stmt = $conn->prepare("
                INSERT INTO anonymous_complaints (
                    employee_id, complaint_type, complaint_title, complaint_detail,
                    department_involved, person_involved, incident_date,
                    is_anonymous, notify_employee, employee_email, complaint_filed_date
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            if (!$stmt) {
                return ['success' => false, 'message' => $conn->error];
            }
            
            $stmt->bind_param(
                'ssssssisss',
                $data['employee_id'] ?? null,
                $data['complaint_type'],
                $data['complaint_title'],
                $data['complaint_detail'],
                $data['department_involved'] ?? null,
                $data['person_involved'] ?? null,
                $data['incident_date'] ?? null,
                $data['is_anonymous'] ?? 1,
                $data['notify_employee'] ?? 1,
                $data['employee_email'] ?? null
            );
            
            if ($stmt->execute()) {
                $complaint_id = $conn->insert_id;
                
                // Add to history log
                self::addHistory($complaint_id, 'Created', null, 'Complaint filed', 
                    $data['filed_by'] ?? null);
                
                $stmt->close();
                $conn->close();
                
                return [
                    'success' => true, 
                    'message' => 'Complaint filed successfully',
                    'complaint_id' => $complaint_id
                ];
            } else {
                $stmt->close();
                $conn->close();
                return ['success' => false, 'message' => 'Failed to file complaint'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Get complaint details by ID
     * @param int $complaint_id
     * @return array|false
     */
    public static function getById($complaint_id) {
        $conn = getDbConnection();
        
        $stmt = $conn->prepare("
            SELECT 
                c.*,
                e1.full_name_th as employee_name,
                e1.full_name_en as employee_name_en,
                e2.full_name_th as assigned_to_name,
                e2.full_name_en as assigned_to_name_en,
                e3.full_name_th as last_updated_by_name
            FROM anonymous_complaints c
            LEFT JOIN employees e1 ON c.employee_id = e1.employee_id
            LEFT JOIN employees e2 ON c.assigned_to = e2.employee_id
            LEFT JOIN employees e3 ON c.last_updated_by = e3.employee_id
            WHERE c.complaint_id = ?
        ");
        
        $stmt->bind_param('i', $complaint_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $complaint = $result->fetch_assoc();
        
        $stmt->close();
        $conn->close();
        
        return $complaint ?: false;
    }
    
    /**
     * Get all complaints (with filtering)
     * @param array $filters (status, type, assigned_to, etc.)
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public static function getAll($filters = [], $limit = 50, $offset = 0) {
        $conn = getDbConnection();
        
        $query = "
            SELECT 
                c.*,
                e1.full_name_th as employee_name,
                e2.full_name_th as assigned_to_name,
                COUNT(ch.history_id) as update_count
            FROM anonymous_complaints c
            LEFT JOIN employees e1 ON c.employee_id = e1.employee_id
            LEFT JOIN employees e2 ON c.assigned_to = e2.employee_id
            LEFT JOIN complaint_history ch ON c.complaint_id = ch.complaint_id
            WHERE 1=1
        ";
        
        // Add filters
        if (!empty($filters['status'])) {
            $query .= " AND c.status = '" . $conn->escape_string($filters['status']) . "'";
        }
        if (!empty($filters['type'])) {
            $query .= " AND c.complaint_type = '" . $conn->escape_string($filters['type']) . "'";
        }
        if (!empty($filters['assigned_to'])) {
            $query .= " AND c.assigned_to = '" . $conn->escape_string($filters['assigned_to']) . "'";
        }
        if (!empty($filters['search'])) {
            $search = $conn->escape_string($filters['search']);
            $query .= " AND (c.complaint_title LIKE '%$search%' OR c.complaint_detail LIKE '%$search%')";
        }
        
        $query .= " GROUP BY c.complaint_id ORDER BY c.created_at DESC LIMIT $limit OFFSET $offset";
        
        $result = $conn->query($query);
        $complaints = [];
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $complaints[] = $row;
            }
        }
        
        $conn->close();
        return $complaints;
    }
    
    /**
     * Update complaint status and assignment
     * @param int $complaint_id
     * @param string $new_status
     * @param string $assigned_to
     * @param string $notes
     * @param string $updated_by
     * @return array
     */
    public static function updateStatus($complaint_id, $new_status, $assigned_to, $notes, $updated_by) {
        $conn = getDbConnection();
        
        try {
            $stmt = $conn->prepare("
                UPDATE anonymous_complaints
                SET 
                    status = ?,
                    assigned_to = ?,
                    investigation_notes = CONCAT(IFNULL(investigation_notes, ''), '\n---\n', ?, '\nUpdated by: ', ?, ' at ', NOW()),
                    last_updated_by = ?,
                    investigation_start_date = CASE WHEN status = 'New' AND ? != 'New' THEN NOW() ELSE investigation_start_date END,
                    updated_at = NOW()
                WHERE complaint_id = ?
            ");
            
            $stmt->bind_param(
                'ssssssi',
                $new_status,
                $assigned_to,
                $notes,
                $updated_by,
                $updated_by,
                $new_status,
                $complaint_id
            );
            
            if ($stmt->execute()) {
                // Add history
                self::addHistory($complaint_id, 'Status Changed', null, "Status: $new_status", $updated_by);
                
                $stmt->close();
                $conn->close();
                
                return ['success' => true, 'message' => 'Complaint status updated'];
            } else {
                $stmt->close();
                $conn->close();
                return ['success' => false, 'message' => 'Failed to update status'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Resolve complaint
     * @param int $complaint_id
     * @param string $resolution
     * @param string $resolved_by
     * @return array
     */
    public static function resolve($complaint_id, $resolution, $resolved_by) {
        $conn = getDbConnection();
        
        try {
            $stmt = $conn->prepare("
                UPDATE anonymous_complaints
                SET 
                    status = 'Resolved',
                    resolution = ?,
                    resolved_date = NOW(),
                    last_updated_by = ?,
                    updated_at = NOW()
                WHERE complaint_id = ?
            ");
            
            $stmt->bind_param('ssi', $resolution, $resolved_by, $complaint_id);
            
            if ($stmt->execute()) {
                self::addHistory($complaint_id, 'Resolved', null, 'Complaint resolved', $resolved_by);
                
                $stmt->close();
                $conn->close();
                
                return ['success' => true, 'message' => 'Complaint resolved'];
            } else {
                $stmt->close();
                $conn->close();
                return ['success' => false, 'message' => 'Failed to resolve complaint'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Close complaint
     * @param int $complaint_id
     * @param string $reason
     * @param string $closed_by
     * @return array
     */
    public static function close($complaint_id, $reason, $closed_by) {
        $conn = getDbConnection();
        
        try {
            $stmt = $conn->prepare("
                UPDATE anonymous_complaints
                SET 
                    status = 'Closed',
                    investigation_notes = CONCAT(IFNULL(investigation_notes, ''), '\n---\nClosed: ', ?),
                    last_updated_by = ?,
                    updated_at = NOW()
                WHERE complaint_id = ?
            ");
            
            $stmt->bind_param('ssi', $reason, $closed_by, $complaint_id);
            
            if ($stmt->execute()) {
                self::addHistory($complaint_id, 'Closed', null, 'Complaint closed', $closed_by);
                
                $stmt->close();
                $conn->close();
                
                return ['success' => true, 'message' => 'Complaint closed'];
            } else {
                $stmt->close();
                $conn->close();
                return ['success' => false, 'message' => 'Failed to close complaint'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Add attachment to complaint
     * @param int $complaint_id
     * @param string $file_name
     * @param string $file_path
     * @param int $file_size
     * @param string $uploaded_by
     * @return array
     */
    public static function addAttachment($complaint_id, $file_name, $file_path, $file_size, $uploaded_by) {
        $conn = getDbConnection();
        
        $file_type = pathinfo($file_name, PATHINFO_EXTENSION);
        
        $stmt = $conn->prepare("
            INSERT INTO complaint_attachments 
            (complaint_id, file_name, file_path, file_size, file_type, uploaded_by)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->bind_param('ississ', $complaint_id, $file_name, $file_path, $file_size, $file_type, $uploaded_by);
        
        if ($stmt->execute()) {
            $stmt->close();
            $conn->close();
            return ['success' => true, 'message' => 'Attachment added'];
        } else {
            $stmt->close();
            $conn->close();
            return ['success' => false, 'message' => 'Failed to add attachment'];
        }
    }
    
    /**
     * Get complaint history
     * @param int $complaint_id
     * @return array
     */
    public static function getHistory($complaint_id) {
        $conn = getDbConnection();
        
        $stmt = $conn->prepare("
            SELECT 
                ch.*,
                e.full_name_th as changed_by_name
            FROM complaint_history ch
            LEFT JOIN employees e ON ch.changed_by = e.employee_id
            WHERE ch.complaint_id = ?
            ORDER BY ch.changed_at DESC
        ");
        
        $stmt->bind_param('i', $complaint_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $history = [];
        while ($row = $result->fetch_assoc()) {
            $history[] = $row;
        }
        
        $stmt->close();
        $conn->close();
        
        return $history;
    }
    
    /**
     * Get statistics
     * @return array
     */
    public static function getStatistics() {
        $conn = getDbConnection();
        
        $stats = [
            'total_complaints' => 0,
            'new_complaints' => 0,
            'under_review' => 0,
            'resolved' => 0,
            'closed' => 0,
            'by_type' => [],
            'pending_more_than_7_days' => 0
        ];
        
        // Total
        $result = $conn->query("SELECT COUNT(*) as count FROM anonymous_complaints");
        if ($row = $result->fetch_assoc()) {
            $stats['total_complaints'] = $row['count'];
        }
        
        // By Status
        $result = $conn->query("
            SELECT status, COUNT(*) as count 
            FROM anonymous_complaints 
            GROUP BY status
        ");
        
        while ($row = $result->fetch_assoc()) {
            $stats[strtolower(str_replace(' ', '_', $row['status']))] = $row['count'];
        }
        
        // By Type
        $result = $conn->query("
            SELECT complaint_type, COUNT(*) as count 
            FROM anonymous_complaints 
            GROUP BY complaint_type
        ");
        
        while ($row = $result->fetch_assoc()) {
            $stats['by_type'][$row['complaint_type']] = $row['count'];
        }
        
        // Pending more than 7 days
        $result = $conn->query("
            SELECT COUNT(*) as count 
            FROM anonymous_complaints 
            WHERE status IN ('New', 'Assigned', 'Under Review', 'In Progress')
            AND created_at < DATE_SUB(NOW(), INTERVAL 7 DAY)
        ");
        
        if ($row = $result->fetch_assoc()) {
            $stats['pending_more_than_7_days'] = $row['count'];
        }
        
        $conn->close();
        return $stats;
    }
    
    /**
     * Add entry to complaint history
     * @param int $complaint_id
     * @param string $action_type
     * @param string $old_value
     * @param string $new_value
     * @param string $changed_by
     * @return bool
     */
    private static function addHistory($complaint_id, $action_type, $old_value, $new_value, $changed_by) {
        $conn = getDbConnection();
        
        $stmt = $conn->prepare("
            INSERT INTO complaint_history 
            (complaint_id, action_type, old_value, new_value, changed_by, changed_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->bind_param('issss', $complaint_id, $action_type, $old_value, $new_value, $changed_by);
        $result = $stmt->execute();
        
        $stmt->close();
        $conn->close();
        
        return $result;
    }
}
?>