<?php
/**
 * Unified Complaint Management - OPTIMIZED ✅
 * File: /views/admin/complaint_management.php
 * 
 * FEATURES:
 * ✅ Works WITHOUT complaint_replies table
 * ✅ Uses complaint_activity_log for all activity tracking
 * ✅ Simpler database structure
 * ✅ Full functionality maintained
 */

define('DEBUG_MODE', true);
ob_start();

require_once __DIR__ . '/../../config/db_config.php';
require_once __DIR__ . '/../../controllers/AuthController.php';

AuthController::requireRole(['admin', 'officer']);

$current_lang = $_SESSION['language'] ?? 'th';
$theme_mode = $_SESSION['theme_mode'] ?? 'light';
$is_dark = ($theme_mode === 'dark');
$user_role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];

$card_bg = $is_dark ? 'bg-gray-800' : 'bg-white';
$text_class = $is_dark ? 'text-gray-100' : 'text-gray-900';
$input_class = $is_dark ? 'bg-gray-700 border-gray-600 text-white' : 'bg-white border-gray-300 text-gray-900';
$border_class = $is_dark ? 'border-gray-700' : 'border-gray-200';

// ============================================================
// API HANDLERS
// ============================================================
$is_api_request = (
    ($_SERVER['REQUEST_METHOD'] === 'POST' && str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'application/json')) ||
    ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['api_action']))
);

if ($is_api_request) {
    ob_end_clean();
    header('Content-Type: application/json; charset=utf-8');
    
    try {
        $conn = getDbConnection();
        if (!$conn) {
            throw new Exception('Database connection failed');
        }
        
        $api_action = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            $api_action = $input['api_action'] ?? '';
        } else {
            $api_action = $_GET['api_action'] ?? '';
        }
        
        error_log("API Action: $api_action");
        
        // ============================================================
        // API: Get All Complaints
        // ============================================================
        if ($api_action === 'get_all_complaints') {
            $status_filter = $_GET['status'] ?? 'all';
            $category_filter = intval($_GET['category'] ?? 0);
            
            $sql = "
                SELECT 
                    c.complaint_id,
                    c.complainer_id_hash,
                    c.category_id,
                    c.subject,
                    c.description,
                    c.status,
                    c.assigned_to_officer_id,
                    c.created_at,
                    c.updated_at,
                    cat.category_name_th, 
                    cat.category_name_en, 
                    cat.category_name_my,
                    e.full_name_th as handler_name
                FROM complaints c
                LEFT JOIN complaint_category_master cat ON c.category_id = cat.category_id
                LEFT JOIN employees e ON c.assigned_to_officer_id = e.employee_id
                WHERE 1=1
            ";
            
            if ($status_filter !== 'all') {
                $sql .= " AND c.status = '" . $conn->real_escape_string($status_filter) . "'";
            }
            
            if ($category_filter > 0) {
                $sql .= " AND c.category_id = $category_filter";
            }
            
            $sql .= " ORDER BY c.created_at DESC";
            
            $result = $conn->query($sql);
            if (!$result) {
                throw new Exception("Query failed: " . $conn->error);
            }
            
            $complaints = [];
            while ($row = $result->fetch_assoc()) {
                $complaints[] = $row;
            }
            
            http_response_code(200);
            echo json_encode(['success' => true, 'complaints' => $complaints]);
            $conn->close();
            exit();
        }
        
        // ============================================================
        // API: Get Complaint Detail
        // ============================================================
        if ($api_action === 'get_complaint_detail') {
            $complaint_id = intval($_GET['complaint_id'] ?? 0);
            
            if ($complaint_id <= 0) {
                throw new Exception('Invalid complaint ID');
            }
            
            // Get complaint detail
            $sql = "
                SELECT 
                    c.complaint_id,
                    c.complainer_id_hash,
                    c.category_id,
                    c.subject,
                    c.description,
                    c.status,
                    c.assigned_to_officer_id,
                    c.assigned_date,
                    c.officer_response,
                    c.officer_remarks,
                    c.response_date,
                    c.attachment_path,
                    c.rating,
                    c.rating_comment,
                    c.rated_at,
                    c.created_at,
                    c.updated_at,
                    c.resolved_at,
                    cat.category_name_th, 
                    cat.category_name_en, 
                    cat.category_name_my,
                    e.full_name_th as handler_name
                FROM complaints c
                LEFT JOIN complaint_category_master cat ON c.category_id = cat.category_id
                LEFT JOIN employees e ON c.assigned_to_officer_id = e.employee_id
                WHERE c.complaint_id = $complaint_id
            ";
            
            $result = $conn->query($sql);
            if (!$result) {
                throw new Exception("Complaint query failed: " . $conn->error);
            }
            
            $complaint = $result->fetch_assoc();
            if (!$complaint) {
                throw new Exception("Complaint not found with ID: $complaint_id");
            }
            
            // Get activity log (NO MORE complaint_replies table!)
            $logs = [];
            $sql_logs = "
                SELECT 
                    l.log_id,
                    l.complaint_id,
                    l.action,
                    l.action_by_officer_id,
                    l.old_status,
                    l.new_status,
                    l.remarks,
                    l.created_at,
                    e.full_name_th as action_by_name
                FROM complaint_activity_log l
                LEFT JOIN employees e ON l.action_by_officer_id = e.employee_id
                WHERE l.complaint_id = $complaint_id
                ORDER BY l.created_at DESC
            ";
            
            $result = $conn->query($sql_logs);
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $logs[] = $row;
                }
            } else {
                error_log("Warning: Activity log query failed: " . $conn->error);
            }
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'complaint' => $complaint,
                'logs' => $logs
            ]);
            $conn->close();
            exit();
        }
        
        // ============================================================
        // API: Update Complaint Status
        // ============================================================
        if ($api_action === 'update_status') {
            $input = json_decode(file_get_contents('php://input'), true);
            
            $complaint_id = intval($input['complaint_id'] ?? 0);
            $new_status = trim($input['status'] ?? '');
            $remarks = trim($input['remarks'] ?? '');
            
            $valid_statuses = ['New', 'In Progress', 'Under Review', 'Resolved', 'Closed', 'Dismissed'];
            if (!in_array($new_status, $valid_statuses)) {
                throw new Exception('Invalid status');
            }
            
            // Get current status
            $sql = "SELECT status FROM complaints WHERE complaint_id = $complaint_id";
            $result = $conn->query($sql);
            $old_status = $result ? $result->fetch_assoc()['status'] : '';
            
            // Update complaint
            $sql = "
                UPDATE complaints 
                SET status = '" . $conn->real_escape_string($new_status) . "',
                    assigned_to_officer_id = '" . $conn->real_escape_string($user_id) . "',
                    assigned_date = IF(assigned_date IS NULL, NOW(), assigned_date),
                    resolved_at = IF('$new_status' IN ('Resolved', 'Closed'), NOW(), resolved_at),
                    updated_at = NOW()
                WHERE complaint_id = $complaint_id
            ";
            
            if (!$conn->query($sql)) {
                throw new Exception("Update failed: " . $conn->error);
            }
            
            // Log activity in activity_log
            $sql_log = "
                INSERT INTO complaint_activity_log 
                (complaint_id, action, action_by_officer_id, old_status, new_status, remarks, created_at) 
                VALUES (
                    $complaint_id, 
                    'Status Changed', 
                    '" . $conn->real_escape_string($user_id) . "',
                    '" . $conn->real_escape_string($old_status) . "',
                    '" . $conn->real_escape_string($new_status) . "',
                    '" . $conn->real_escape_string($remarks) . "',
                    NOW()
                )
            ";
            
            if (!$conn->query($sql_log)) {
                error_log("Warning: Activity log insert failed: " . $conn->error);
            }
            
            http_response_code(200);
            echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
            $conn->close();
            exit();
        }
        
        // ============================================================
        // API: Add Response
        // ============================================================
        if ($api_action === 'add_response') {
            $input = json_decode(file_get_contents('php://input'), true);
            
            $complaint_id = intval($input['complaint_id'] ?? 0);
            $response = trim($input['response'] ?? '');
            $remarks = trim($input['remarks'] ?? '');
            
            if (empty($response)) {
                throw new Exception('Response cannot be empty');
            }
            
            // Update complaint with officer response
            $sql = "
                UPDATE complaints 
                SET officer_response = '" . $conn->real_escape_string($response) . "',
                    officer_remarks = '" . $conn->real_escape_string($remarks) . "',
                    response_date = NOW(),
                    assigned_to_officer_id = '" . $conn->real_escape_string($user_id) . "',
                    updated_at = NOW()
                WHERE complaint_id = $complaint_id
            ";
            
            if (!$conn->query($sql)) {
                throw new Exception("Update failed: " . $conn->error);
            }
            
            // Log activity
            $sql_log = "
                INSERT INTO complaint_activity_log 
                (complaint_id, action, action_by_officer_id, remarks, created_at) 
                VALUES (
                    $complaint_id, 
                    'Response Added', 
                    '" . $conn->real_escape_string($user_id) . "',
                    '" . $conn->real_escape_string($response) . "',
                    NOW()
                )
            ";
            
            if (!$conn->query($sql_log)) {
                error_log("Warning: Activity log insert failed: " . $conn->error);
            }
            
            http_response_code(200);
            echo json_encode(['success' => true, 'message' => 'Response added successfully']);
            $conn->close();
            exit();
        }
        
        // ============================================================
        // API: Add Comment (using activity_log remarks)
        // ============================================================
        if ($api_action === 'add_comment') {
            $input = json_decode(file_get_contents('php://input'), true);
            
            $complaint_id = intval($input['complaint_id'] ?? 0);
            $message = trim($input['message'] ?? '');
            
            if (empty($message)) {
                throw new Exception('Message cannot be empty');
            }
            
            // Insert as activity log entry
            $sql = "
                INSERT INTO complaint_activity_log 
                (complaint_id, action, action_by_officer_id, remarks, created_at) 
                VALUES (
                    $complaint_id, 
                    'Comment Added', 
                    '" . $conn->real_escape_string($user_id) . "',
                    '" . $conn->real_escape_string($message) . "',
                    NOW()
                )
            ";
            
            if (!$conn->query($sql)) {
                throw new Exception("Insert failed: " . $conn->error);
            }
            
            http_response_code(200);
            echo json_encode(['success' => true, 'message' => 'Comment added successfully']);
            $conn->close();
            exit();
        }
        
        // ============================================================
        // API: Save Category (Admin Only)
        // ============================================================
        if ($api_action === 'save_category') {
            if ($user_role !== 'admin') {
                http_response_code(403);
                throw new Exception('Admin only');
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            
            $category_id = $input['category_id'] ?? '';
            $name_th = trim($input['category_name_th'] ?? '');
            $name_en = trim($input['category_name_en'] ?? '');
            $name_my = trim($input['category_name_my'] ?? '');
            $is_active = isset($input['is_active']) ? (int)$input['is_active'] : 1;
            
            if (empty($name_th)) {
                throw new Exception('Name (Thai) is required');
            }
            
            if (empty($category_id)) {
                // Insert new
                $sql = "
                    INSERT INTO complaint_category_master 
                    (category_name_th, category_name_en, category_name_my, is_active, created_at) 
                    VALUES (
                        '" . $conn->real_escape_string($name_th) . "',
                        '" . $conn->real_escape_string($name_en) . "',
                        '" . $conn->real_escape_string($name_my) . "',
                        $is_active,
                        NOW()
                    )
                ";
            } else {
                // Update existing
                $category_id = (int)$category_id;
                $sql = "
                    UPDATE complaint_category_master 
                    SET category_name_th = '" . $conn->real_escape_string($name_th) . "',
                        category_name_en = '" . $conn->real_escape_string($name_en) . "',
                        category_name_my = '" . $conn->real_escape_string($name_my) . "',
                        is_active = $is_active,
                        updated_at = NOW()
                    WHERE category_id = $category_id
                ";
            }
            
            if (!$conn->query($sql)) {
                throw new Exception("Execute failed: " . $conn->error);
            }
            
            http_response_code(200);
            echo json_encode(['success' => true, 'message' => 'Saved successfully']);
            $conn->close();
            exit();
        }
        
        // ============================================================
        // API: Delete Category (Admin Only)
        // ============================================================
        if ($api_action === 'delete_category') {
            if ($user_role !== 'admin') {
                http_response_code(403);
                throw new Exception('Admin only');
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            $category_id = (int)($input['category_id'] ?? 0);
            
            if ($category_id <= 0) {
                throw new Exception('Invalid category ID');
            }
            
            // Check if category is being used
            $sql = "SELECT COUNT(*) as count FROM complaints WHERE category_id = $category_id";
            $result = $conn->query($sql);
            $row = $result->fetch_assoc();
            
            if ($row['count'] > 0) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Cannot delete: Category is being used'
                ]);
                $conn->close();
                exit();
            }
            
            // Delete category
            $sql = "DELETE FROM complaint_category_master WHERE category_id = $category_id";
            if (!$conn->query($sql)) {
                throw new Exception("Delete failed: " . $conn->error);
            }
            
            http_response_code(200);
            echo json_encode(['success' => true, 'message' => 'Deleted successfully']);
            $conn->close();
            exit();
        }
        
        // Invalid API action
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid API action']);
        $conn->close();
        exit();
        
    } catch (Exception $e) {
        http_response_code(500);
        error_log("Exception in API: " . $e->getMessage());
        
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage(),
            'debug' => DEBUG_MODE ? ['error' => $e->getMessage()] : null
        ]);
        exit();
    }
}

// ============================================================
// UI PAGE
// ============================================================
ob_end_clean();

$texts = [
    'th' => [
        'page_title' => 'จัดการคำร้องเรียน',
        'page_subtitle' => 'ตรวจสอบและจัดการคำร้องเรียนจากพนักงาน',
        'tab_complaints' => 'คำร้องเรียน',
        'tab_categories' => 'หมวดหมู่',
        'filter_all' => 'ทั้งหมด',
        'filter_new' => 'ใหม่',
        'filter_in_progress' => 'กำลังดำเนินการ',
        'filter_resolved' => 'แก้ไขแล้ว',
        'total_complaints' => 'คำร้องเรียนทั้งหมด',
        'pending_complaints' => 'รอดำเนินการ',
        'resolved_complaints' => 'แก้ไขแล้ว',
        'no_complaints' => 'ไม่มีคำร้องเรียน',
        'view_detail' => 'ดูรายละเอียด',
        'subject' => 'หัวข้อ',
        'category' => 'หมวดหมู่',
        'status' => 'สถานะ',
        'created_at' => 'วันที่สร้าง',
        'update_status' => 'อัปเดตสถานะ',
        'add_response' => 'ตอบกลับ',
        'response' => 'คำตอบ',
        'remarks' => 'หมายเหตุ',
        'save' => 'บันทึก',
        'cancel' => 'ยกเลิก',
        'close' => 'ปิด',
        'status_new' => 'ใหม่',
        'status_in_progress' => 'กำลังดำเนินการ',
        'status_under_review' => 'กำลังตรวจสอบ',
        'status_resolved' => 'แก้ไขแล้ว',
        'status_closed' => 'ปิดแล้ว',
        'status_dismissed' => 'ยกเลิก',
        'add_category' => 'เพิ่มหมวดหมู่',
        'manage_categories' => 'จัดการหมวดหมู่',
        'name_th' => 'ชื่อ (ไทย)',
        'name_en' => 'ชื่อ (English)',
        'name_my' => 'ชื่อ (Myanmar)',
        'description' => 'รายละเอียด',
        'active' => 'ใช้งาน',
        'inactive' => 'ไม่ใช้งาน',
        'edit' => 'แก้ไข',
        'delete' => 'ลบ',
        'confirm_delete' => 'ยืนยันการลบ',
        'actions' => 'การจัดการ',
        'no_data' => 'ยังไม่มีข้อมูล',
        'anonymous' => 'ไม่ระบุตัวตน',
        'complaint_details' => 'รายละเอียดคำร้องเรียน',
        'activity_log' => 'ประวัติการดำเนินการ',
        'add_comment' => 'เพิ่มความเห็น',
        'write_comment' => 'เขียนความเห็น',
        'send' => 'ส่ง',
    ],
    'en' => [
        'page_title' => 'Complaint Management',
        'page_subtitle' => 'Review and manage employee complaints',
        'tab_complaints' => 'Complaints',
        'tab_categories' => 'Categories',
        'filter_all' => 'All',
        'filter_new' => 'New',
        'filter_in_progress' => 'In Progress',
        'filter_resolved' => 'Resolved',
        'total_complaints' => 'Total Complaints',
        'pending_complaints' => 'Pending',
        'resolved_complaints' => 'Resolved',
        'no_complaints' => 'No complaints',
        'view_detail' => 'View Detail',
        'subject' => 'Subject',
        'category' => 'Category',
        'status' => 'Status',
        'created_at' => 'Created At',
        'update_status' => 'Update Status',
        'add_response' => 'Add Response',
        'response' => 'Response',
        'remarks' => 'Remarks',
        'save' => 'Save',
        'cancel' => 'Cancel',
        'close' => 'Close',
        'status_new' => 'New',
        'status_in_progress' => 'In Progress',
        'status_under_review' => 'Under Review',
        'status_resolved' => 'Resolved',
        'status_closed' => 'Closed',
        'status_dismissed' => 'Dismissed',
        'add_category' => 'Add Category',
        'manage_categories' => 'Manage Categories',
        'name_th' => 'Name (Thai)',
        'name_en' => 'Name (English)',
        'name_my' => 'Name (Myanmar)',
        'description' => 'Description',
        'active' => 'Active',
        'inactive' => 'Inactive',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'confirm_delete' => 'Confirm delete',
        'actions' => 'Actions',
        'no_data' => 'No data',
        'anonymous' => 'Anonymous',
        'complaint_details' => 'Complaint Details',
        'activity_log' => 'Activity Log',
        'add_comment' => 'Add Comment',
        'write_comment' => 'Write comment',
        'send' => 'Send',
    ]
];

$t = $texts[$current_lang] ?? $texts['th'];

$conn = getDbConnection();

// Get categories
$categories = [];
$result = $conn->query("SELECT * FROM complaint_category_master WHERE is_active = 1 ORDER BY category_id");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
}

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>

<div class="lg:ml-64">
    <div class="container mx-auto px-4 py-6 max-w-7xl">
        
        <!-- Page Header -->
        <div class="mb-6 bg-gradient-to-r from-red-600 to-pink-600 rounded-lg shadow-lg p-6">
            <div class="flex items-center">
                <svg class="w-10 h-10 text-white mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <div>
                    <h1 class="text-3xl font-bold text-white"><?php echo $t['page_title']; ?></h1>
                    <p class="text-red-100 mt-1"><?php echo $t['page_subtitle']; ?></p>
                </div>
            </div>
        </div>
        
        <!-- Statistics Cards -->
        <div id="statsContainer" class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <!-- Will be populated by JavaScript -->
        </div>
        
        <!-- Tab Navigation -->
        <div class="flex border-b <?php echo $border_class; ?> mb-6 <?php echo $card_bg; ?> rounded-t-lg px-6 py-4">
            <button onclick="switchTab('complaints')" 
                    id="tab-complaints-btn"
                    class="px-6 py-2 font-medium border-b-2 border-red-600 text-red-600 transition tab-btn">
                📋 <?php echo $t['tab_complaints']; ?>
            </button>
            <?php if ($user_role === 'admin'): ?>
            <button onclick="switchTab('categories')" 
                    id="tab-categories-btn"
                    class="px-6 py-2 font-medium border-b-2 border-transparent <?php echo $text_class; ?> hover:text-red-600 transition tab-btn">
                🏷️ <?php echo $t['tab_categories']; ?>
            </button>
            <?php endif; ?>
        </div>
        
        <!-- ============ TAB 1: COMPLAINTS LIST ============ -->
        <div id="complaints-section" class="tab-content <?php echo $card_bg; ?> rounded-b-lg shadow-lg p-6 border <?php echo $border_class; ?>">
            
            <!-- Filter Tabs -->
            <div class="flex flex-wrap gap-2 mb-6 pb-6 border-b <?php echo $border_class; ?>">
                <button onclick="filterComplaints('all')" class="filter-tab active" data-filter="all">
                    <?php echo $t['filter_all']; ?>
                </button>
                <button onclick="filterComplaints('New')" class="filter-tab" data-filter="New">
                    <?php echo $t['filter_new']; ?>
                </button>
                <button onclick="filterComplaints('In Progress')" class="filter-tab" data-filter="In Progress">
                    <?php echo $t['filter_in_progress']; ?>
                </button>
                <button onclick="filterComplaints('Resolved')" class="filter-tab" data-filter="Resolved">
                    <?php echo $t['filter_resolved']; ?>
                </button>
            </div>
            
            <!-- Complaints List -->
            <div id="complaintsContainer" class="space-y-4">
                <!-- Will be populated by JavaScript -->
            </div>
        </div>
        
        <!-- ============ TAB 2: CATEGORIES ============ -->
        <?php if ($user_role === 'admin'): ?>
        <div id="categories-section" class="tab-content hidden <?php echo $card_bg; ?> rounded-b-lg shadow-lg p-6 border <?php echo $border_class; ?>">
            <div class="flex justify-between items-center mb-6 pb-6 border-b <?php echo $border_class; ?>">
                <h2 class="text-2xl font-bold <?php echo $text_class; ?>"><?php echo $t['manage_categories']; ?></h2>
                <button onclick="openCategoryModal()" 
                        class="flex items-center px-6 py-3 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition shadow-lg">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <?php echo $t['add_category']; ?>
                </button>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="<?php echo $is_dark ? 'bg-gray-700' : 'bg-gray-50'; ?> border-b <?php echo $border_class; ?>">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold <?php echo $text_class; ?> uppercase">ID</th>
                            <th class="px-6 py-4 text-left text-xs font-bold <?php echo $text_class; ?> uppercase"><?php echo $t['name_th']; ?></th>
                            <th class="px-6 py-4 text-left text-xs font-bold <?php echo $text_class; ?> uppercase"><?php echo $t['name_en']; ?></th>
                            <th class="px-6 py-4 text-left text-xs font-bold <?php echo $text_class; ?> uppercase"><?php echo $t['name_my']; ?></th>
                            <th class="px-6 py-4 text-center text-xs font-bold <?php echo $text_class; ?> uppercase"><?php echo $t['status']; ?></th>
                            <th class="px-6 py-4 text-center text-xs font-bold <?php echo $text_class; ?> uppercase"><?php echo $t['actions']; ?></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y <?php echo $border_class; ?>">
                        <?php if (empty($categories)): ?>
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?>">
                                    ℹ️ <?php echo $t['no_data']; ?>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($categories as $cat): ?>
                                <tr class="hover:<?php echo $is_dark ? 'bg-gray-700' : 'bg-gray-50'; ?> transition">
                                    <td class="px-6 py-4 text-sm font-mono <?php echo $text_class; ?>">#<?php echo $cat['category_id']; ?></td>
                                    <td class="px-6 py-4 font-medium <?php echo $text_class; ?>"><?php echo htmlspecialchars($cat['category_name_th']); ?></td>
                                    <td class="px-6 py-4 text-sm <?php echo $text_class; ?>"><?php echo htmlspecialchars($cat['category_name_en'] ?? '-'); ?></td>
                                    <td class="px-6 py-4 text-sm <?php echo $text_class; ?>"><?php echo htmlspecialchars($cat['category_name_my'] ?? '-'); ?></td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400">✓</span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <button onclick='editCategory(<?php echo json_encode($cat); ?>)' class="text-blue-600 dark:text-blue-400 p-2 hover:bg-blue-50 dark:hover:bg-gray-600 rounded transition">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                            </button>
                                            <button onclick="deleteCategory(<?php echo $cat['category_id']; ?>, '<?php echo htmlspecialchars($cat['category_name_th']); ?>')" class="text-red-600 dark:text-red-400 p-2 hover:bg-red-50 dark:hover:bg-gray-600 rounded transition">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal: View Complaint Detail -->
<div id="detailModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4 overflow-y-auto">
    <div class="<?php echo $card_bg; ?> rounded-lg shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto border <?php echo $border_class; ?> my-8">
        <div id="detailContent" class="p-6">
            <div class="flex items-center justify-center py-12">
                <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-red-600"></div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Add/Edit Category -->
<div id="categoryModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="<?php echo $card_bg; ?> rounded-lg shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto border <?php echo $border_class; ?>">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-bold <?php echo $text_class; ?>" id="categoryModalTitle"><?php echo $t['add_category']; ?></h3>
                <button onclick="closeCategoryModal()" class="text-gray-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <form id="categoryForm" onsubmit="saveCategory(event)">
                <input type="hidden" id="category_id" name="category_id">
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2"><?php echo $t['name_th']; ?> *</label>
                        <input type="text" id="category_name_th" name="category_name_th" required class="w-full px-4 py-2 border rounded-lg <?php echo $input_class; ?> <?php echo $border_class; ?> focus:ring-2 focus:ring-red-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2"><?php echo $t['name_en']; ?></label>
                        <input type="text" id="category_name_en" name="category_name_en" class="w-full px-4 py-2 border rounded-lg <?php echo $input_class; ?> <?php echo $border_class; ?> focus:ring-2 focus:ring-red-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2"><?php echo $t['name_my']; ?></label>
                        <input type="text" id="category_name_my" name="category_name_my" class="w-full px-4 py-2 border rounded-lg <?php echo $input_class; ?> <?php echo $border_class; ?> focus:ring-2 focus:ring-red-500">
                    </div>
                </div>
                
                <div class="flex gap-3 mt-6 pt-6 border-t <?php echo $border_class; ?>">
                    <button type="submit" class="flex-1 bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg font-medium">✓ <?php echo $t['save']; ?></button>
                    <button type="button" onclick="closeCategoryModal()" class="flex-1 bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg font-medium">✕ <?php echo $t['cancel']; ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.filter-tab {
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    font-weight: 600;
    transition: all 0.2s;
    <?php echo $is_dark ? 'color: #9CA3AF; background-color: #374151;' : 'color: #6B7280; background-color: #F3F4F6;'; ?>
}
.filter-tab.active {
    color: white;
    background: linear-gradient(to right, #DC2626, #EC4899);
}
</style>

<script>
const BASE_URL = '<?php echo BASE_URL; ?>';
const API_ENDPOINT = BASE_URL + '/views/admin/complaint_management.php';
const LANG = '<?php echo $current_lang; ?>';
const IS_DARK = <?php echo $is_dark ? 'true' : 'false'; ?>;
const TEXTS = <?php echo json_encode($t); ?>;
const USER_ROLE = '<?php echo $user_role; ?>';

let allComplaints = [];
let currentFilter = 'all';

document.addEventListener('DOMContentLoaded', function() {
    loadComplaints();
});

function switchTab(tabName) {
    document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
    document.querySelectorAll('.tab-btn').forEach(el => {
        el.classList.remove('border-red-600', 'text-red-600');
        el.classList.add('border-transparent');
    });
    
    document.getElementById(tabName + '-section').classList.remove('hidden');
    document.getElementById('tab-' + tabName + '-btn').classList.add('border-red-600', 'text-red-600');
}

async function loadComplaints() {
    try {
        const response = await fetch(API_ENDPOINT + '?api_action=get_all_complaints');
        const data = await response.json();
        
        if (data.success) {
            allComplaints = data.complaints;
            updateStats();
            filterComplaints(currentFilter);
        } else {
            alert('Failed: ' + data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to load complaints');
    }
}

function updateStats() {
    const total = allComplaints.length;
    const pending = allComplaints.filter(c => ['New', 'In Progress'].includes(c.status)).length;
    const resolved = allComplaints.filter(c => ['Resolved', 'Closed'].includes(c.status)).length;
    
    document.getElementById('statsContainer').innerHTML = `
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white p-6 rounded-lg shadow-lg">
            <div class="text-3xl font-bold">${total}</div>
            <div class="text-blue-100 mt-1">${TEXTS.total_complaints}</div>
        </div>
        <div class="bg-gradient-to-br from-yellow-500 to-orange-600 text-white p-6 rounded-lg shadow-lg">
            <div class="text-3xl font-bold">${pending}</div>
            <div class="text-yellow-100 mt-1">${TEXTS.pending_complaints}</div>
        </div>
        <div class="bg-gradient-to-br from-green-500 to-emerald-600 text-white p-6 rounded-lg shadow-lg">
            <div class="text-3xl font-bold">${resolved}</div>
            <div class="text-green-100 mt-1">${TEXTS.resolved_complaints}</div>
        </div>
    `;
}

function filterComplaints(filter) {
    currentFilter = filter;
    document.querySelectorAll('.filter-tab').forEach(btn => {
        btn.classList.remove('active');
        if (btn.getAttribute('data-filter') === filter) {
            btn.classList.add('active');
        }
    });
    
    const filtered = filter === 'all' ? allComplaints : allComplaints.filter(c => c.status === filter);
    renderComplaints(filtered);
}

function renderComplaints(complaints) {
    const container = document.getElementById('complaintsContainer');
    
    if (complaints.length === 0) {
        container.innerHTML = `<div class="text-center py-12 text-gray-600">${TEXTS.no_complaints}</div>`;
        return;
    }
    
    const catNameKey = `category_name_${LANG}`;
    const statusLabels = {
        'New': TEXTS.status_new,
        'In Progress': TEXTS.status_in_progress,
        'Under Review': TEXTS.status_under_review,
        'Resolved': TEXTS.status_resolved,
        'Closed': TEXTS.status_closed,
        'Dismissed': TEXTS.status_dismissed
    };
    
    let html = '';
    complaints.forEach(complaint => {
        const statusLabel = statusLabels[complaint.status] || complaint.status;
        html += `
            <div class="${IS_DARK ? 'bg-gray-700' : 'bg-gray-50'} border rounded-lg p-6 hover:shadow-lg transition">
                <div class="flex justify-between items-start mb-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <span class="text-sm ${IS_DARK ? 'text-gray-400' : 'text-gray-600'}">${complaint[catNameKey] || 'Unknown'}</span>
                            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">${statusLabel}</span>
                        </div>
                        <h3 class="${IS_DARK ? 'text-white' : 'text-gray-900'} text-lg font-bold">${complaint.subject || 'No Subject'}</h3>
                        <p class="${IS_DARK ? 'text-gray-400' : 'text-gray-600'} mt-2">${(complaint.description || 'No Description').substring(0, 100)}...</p>
                    </div>
                </div>
                
                <div class="flex items-center justify-between mt-4 pt-4 border-t">
                    <span class="text-sm ${IS_DARK ? 'text-gray-400' : 'text-gray-600'}">${new Date(complaint.created_at).toLocaleDateString()}</span>
                    <button onclick="viewComplaintDetail(${complaint.complaint_id})" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-semibold hover:bg-red-700">
                        ${TEXTS.view_detail}
                    </button>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

async function viewComplaintDetail(complaintId) {
    try {
        document.getElementById('detailModal').classList.remove('hidden');
        
        const url = API_ENDPOINT + '?api_action=get_complaint_detail&complaint_id=' + complaintId;
        const response = await fetch(url);
        const data = await response.json();
        
        if (data.success) {
            renderComplaintDetail(data);
        } else {
            throw new Error(data.message || 'Unknown error');
        }
    } catch (error) {
        console.error('Error:', error);
        document.getElementById('detailContent').innerHTML = `
            <div class="p-6 text-center">
                <div class="text-red-600 font-bold mb-2">⚠️ Error Loading</div>
                <p class="${IS_DARK ? 'text-gray-400' : 'text-gray-600'} mb-4">${error.message}</p>
                <button onclick="closeDetailModal()" class="px-6 py-2 bg-gray-500 text-white rounded-lg">Close</button>
            </div>
        `;
    }
}

function renderComplaintDetail(data) {
    const complaint = data.complaint;
    const logs = data.logs || [];
    const catNameKey = `category_name_${LANG}`;
    
    const statusLabels = {
        'New': TEXTS.status_new,
        'In Progress': TEXTS.status_in_progress,
        'Under Review': TEXTS.status_under_review,
        'Resolved': TEXTS.status_resolved,
        'Closed': TEXTS.status_closed,
        'Dismissed': TEXTS.status_dismissed
    };
    
    // Filter logs to show replies/comments
    const comments = logs.filter(l => ['Comment Added', 'Reply Added'].includes(l.action));
    
    let html = `
        <div>
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h3 class="text-2xl font-bold ${IS_DARK ? 'text-white' : 'text-gray-900'}">${complaint.subject || 'No Subject'}</h3>
                    <p class="${IS_DARK ? 'text-gray-400' : 'text-gray-600'} mt-2">
                        ${complaint[catNameKey] || 'Unknown'} · ${new Date(complaint.created_at).toLocaleDateString()}
                    </p>
                </div>
                <button onclick="closeDetailModal()" class="text-gray-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <div class="space-y-6">
                <div class="${IS_DARK ? 'bg-gray-700' : 'bg-gray-50'} p-4 rounded-lg">
                    <p class="${IS_DARK ? 'text-gray-300' : 'text-gray-700'}">${complaint.description || 'No Description'}</p>
                </div>
                
                <div class="${IS_DARK ? 'bg-gray-700' : 'bg-blue-50'} border-l-4 border-blue-500 p-4 rounded">
                    <h4 class="font-semibold ${IS_DARK ? 'text-white' : 'text-blue-900'} mb-3">${TEXTS.update_status}</h4>
                    <form onsubmit="updateStatus(event, ${complaint.complaint_id})" class="space-y-3">
                        <select name="status" class="w-full px-4 py-2 border rounded-lg ${IS_DARK ? 'bg-gray-600 border-gray-500 text-white' : 'bg-white border-gray-300'}" required>
                            ${Object.keys(statusLabels).map(s => `<option value="${s}" ${complaint.status === s ? 'selected' : ''}>${statusLabels[s]}</option>`).join('')}
                        </select>
                        <textarea name="remarks" rows="2" placeholder="${TEXTS.remarks}" class="w-full px-4 py-2 border rounded-lg ${IS_DARK ? 'bg-gray-600 border-gray-500 text-white' : 'bg-white border-gray-300'}"></textarea>
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg font-medium">
                            ${TEXTS.update_status}
                        </button>
                    </form>
                </div>
                
                ${!complaint.officer_response ? `
                <div class="${IS_DARK ? 'bg-gray-700' : 'bg-green-50'} border-l-4 border-green-500 p-4 rounded">
                    <h4 class="font-semibold ${IS_DARK ? 'text-white' : 'text-green-900'} mb-3">${TEXTS.add_response}</h4>
                    <form onsubmit="addResponse(event, ${complaint.complaint_id})" class="space-y-3">
                        <textarea name="response" rows="3" placeholder="${TEXTS.response}" class="w-full px-4 py-2 border rounded-lg ${IS_DARK ? 'bg-gray-600 border-gray-500 text-white' : 'bg-white border-gray-300'}" required></textarea>
                        <textarea name="remarks" rows="2" placeholder="${TEXTS.remarks}" class="w-full px-4 py-2 border rounded-lg ${IS_DARK ? 'bg-gray-600 border-gray-500 text-white' : 'bg-white border-gray-300'}"></textarea>
                        <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white py-2 rounded-lg font-medium">
                            ${TEXTS.add_response}
                        </button>
                    </form>
                </div>
                ` : `
                <div class="${IS_DARK ? 'bg-gray-700' : 'bg-green-50'} border-l-4 border-green-500 p-4 rounded">
                    <h4 class="font-semibold ${IS_DARK ? 'text-white' : 'text-green-900'} mb-2">${TEXTS.response}</h4>
                    <p class="${IS_DARK ? 'text-gray-300' : 'text-green-800'}">${complaint.officer_response}</p>
                </div>
                `}
                
                <div>
                    <h4 class="font-semibold ${IS_DARK ? 'text-white' : 'text-gray-900'} mb-3">${TEXTS.activity_log}</h4>
                    <div class="space-y-2 max-h-60 overflow-y-auto mb-3">
                        ${logs.length === 0 ? `<p class="${IS_DARK ? 'text-gray-400' : 'text-gray-600'} text-sm">No activity yet</p>` : logs.map(l => `
                            <div class="${IS_DARK ? 'bg-gray-700' : 'bg-gray-100'} p-3 rounded">
                                <div class="flex justify-between items-start mb-1">
                                    <span class="font-medium text-sm ${IS_DARK ? 'text-gray-300' : 'text-gray-900'}">${l.action} - ${l.action_by_name || 'System'}</span>
                                    <span class="text-xs ${IS_DARK ? 'text-gray-400' : 'text-gray-500'}">${new Date(l.created_at).toLocaleDateString()}</span>
                                </div>
                                ${l.remarks ? `<p class="text-sm ${IS_DARK ? 'text-gray-400' : 'text-gray-700'}">${l.remarks}</p>` : ''}
                                ${l.old_status ? `<p class="text-xs ${IS_DARK ? 'text-gray-400' : 'text-gray-600'}">${l.old_status} → ${l.new_status}</p>` : ''}
                            </div>
                        `).join('')}
                    </div>
                    
                    <form onsubmit="addComment(event, ${complaint.complaint_id})" class="flex gap-2">
                        <input type="text" name="message" placeholder="${TEXTS.write_comment}" required class="flex-1 px-4 py-2 border rounded-lg ${IS_DARK ? 'bg-gray-600 border-gray-500 text-white' : 'bg-white border-gray-300'}">
                        <button type="submit" class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium">
                            ${TEXTS.send}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('detailContent').innerHTML = html;
}

async function updateStatus(event, complaintId) {
    event.preventDefault();
    const formData = new FormData(event.target);
    
    const data = {
        api_action: 'update_status',
        complaint_id: complaintId,
        status: formData.get('status'),
        remarks: formData.get('remarks')
    };
    
    try {
        const response = await fetch(API_ENDPOINT, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(data)
        });
        const result = await response.json();
        
        if (result.success) {
            alert('Status updated');
            loadComplaints();
            viewComplaintDetail(complaintId);
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        alert('Failed: ' + error.message);
    }
}

async function addResponse(event, complaintId) {
    event.preventDefault();
    const formData = new FormData(event.target);
    
    const data = {
        api_action: 'add_response',
        complaint_id: complaintId,
        response: formData.get('response'),
        remarks: formData.get('remarks')
    };
    
    try {
        const response = await fetch(API_ENDPOINT, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(data)
        });
        const result = await response.json();
        
        if (result.success) {
            alert('Response added');
            loadComplaints();
            viewComplaintDetail(complaintId);
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        alert('Failed: ' + error.message);
    }
}

async function addComment(event, complaintId) {
    event.preventDefault();
    const formData = new FormData(event.target);
    
    const data = {
        api_action: 'add_comment',
        complaint_id: complaintId,
        message: formData.get('message')
    };
    
    try {
        const response = await fetch(API_ENDPOINT, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(data)
        });
        const result = await response.json();
        
        if (result.success) {
            event.target.reset();
            viewComplaintDetail(complaintId);
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        alert('Failed: ' + error.message);
    }
}

function openCategoryModal() {
    document.getElementById('categoryModalTitle').textContent = TEXTS.add_category;
    document.getElementById('categoryForm').reset();
    document.getElementById('category_id').value = '';
    document.getElementById('categoryModal').classList.remove('hidden');
}

function editCategory(cat) {
    document.getElementById('categoryModalTitle').textContent = TEXTS.edit + ' ' + cat.category_name_th;
    document.getElementById('category_id').value = cat.category_id;
    document.getElementById('category_name_th').value = cat.category_name_th;
    document.getElementById('category_name_en').value = cat.category_name_en || '';
    document.getElementById('category_name_my').value = cat.category_name_my || '';
    document.getElementById('categoryModal').classList.remove('hidden');
}

function closeCategoryModal() {
    document.getElementById('categoryModal').classList.add('hidden');
}

async function saveCategory(event) {
    event.preventDefault();
    const formData = new FormData(event.target);
    
    const data = {
        api_action: 'save_category',
        category_id: formData.get('category_id'),
        category_name_th: formData.get('category_name_th'),
        category_name_en: formData.get('category_name_en'),
        category_name_my: formData.get('category_name_my'),
        is_active: 1
    };
    
    try {
        const response = await fetch(API_ENDPOINT, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(data)
        });
        const result = await response.json();
        
        if (result.success) {
            closeCategoryModal();
            location.reload();
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        alert('Failed: ' + error.message);
    }
}

async function deleteCategory(id, name) {
    if (!confirm('Delete "' + name + '"?')) return;
    
    const data = {
        api_action: 'delete_category',
        category_id: id
    };
    
    try {
        const response = await fetch(API_ENDPOINT, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(data)
        });
        const result = await response.json();
        
        if (result.success) {
            location.reload();
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        alert('Failed: ' + error.message);
    }
}

function closeDetailModal() {
    document.getElementById('detailModal').classList.add('hidden');
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>