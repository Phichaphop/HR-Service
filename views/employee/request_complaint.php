<?php
/**
 * Request Complaint Page - With Activity Log Viewer & Rating System
 * ✅ Submit complaints anonymously
 * ✅ View my complaints list
 * ✅ View activity log and responses
 * ✅ Rate complaints when status = Resolved/Closed
 * ✅ Multi-language support
 */

require_once __DIR__ . '/../../config/db_config.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../models/Employee.php';
require_once __DIR__ . '/../../db/Localization.php';

AuthController::requireAuth();

$current_lang = $_SESSION['language'] ?? 'th';
$theme_mode = $_SESSION['theme_mode'] ?? 'light';
$is_dark = ($theme_mode === 'dark');
$user_id = $_SESSION['user_id'];

$card_bg = $is_dark ? 'bg-gray-800' : 'bg-white';
$text_class = $is_dark ? 'text-white' : 'text-gray-900';
$border_class = $is_dark ? 'border-gray-700' : 'border-gray-200';
$input_class = $is_dark ? 'bg-gray-700 border-gray-600 text-white placeholder-gray-400' : 'bg-white border-gray-300 text-gray-900 placeholder-gray-500';

// ============================================================
// API HANDLER
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['api_action'])) {
    header('Content-Type: application/json; charset=utf-8');
    
    try {
        $conn = getDbConnection();
        if (!$conn) {
            throw new Exception('Database connection failed');
        }
        
        $api_action = $_GET['api_action'] ?? '';
        $complaint_id = intval($_GET['complaint_id'] ?? 0);
        $user_id_session = $_SESSION['user_id'] ?? '';
        
        // ============================================================
        // API: Get Complaint Detail
        // ============================================================
        if ($api_action === 'get_complaint_detail') {
            if ($complaint_id <= 0) {
                throw new Exception('Invalid complaint ID');
            }
            
            $sql = "
                SELECT 
                    c.complaint_id,
                    c.category_id,
                    c.subject,
                    c.description,
                    c.status,
                    c.officer_response,
                    c.officer_remarks,
                    c.response_date,
                    c.attachment_path,
                    c.rating,
                    c.rating_comment,
                    c.rated_at,
                    c.created_at,
                    c.updated_at,
                    cat.category_name_th, 
                    cat.category_name_en, 
                    cat.category_name_my
                FROM complaints c
                LEFT JOIN complaint_category_master cat ON c.category_id = cat.category_id
                WHERE c.complaint_id = $complaint_id
                AND c.complainer_id_hash = '" . $conn->real_escape_string(hash('sha256', $user_id_session)) . "'
            ";
            
            $result = $conn->query($sql);
            if (!$result) {
                throw new Exception("Query failed: " . $conn->error);
            }
            
            $complaint = $result->fetch_assoc();
            if (!$complaint) {
                throw new Exception('Complaint not found or access denied');
            }
            
            // Get activity log
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
        // API: Submit Rating
        // ============================================================
        if ($api_action === 'submit_rating') {
            try {
                $input = json_decode(file_get_contents('php://input'), true);
                
                $complaint_id = intval($input['complaint_id'] ?? 0);
                $rating = intval($input['rating'] ?? 0);
                $comment = trim($input['comment'] ?? '');
                
                // Validate
                if ($complaint_id <= 0) {
                    throw new Exception('Invalid complaint ID');
                }
                
                if ($rating < 1 || $rating > 5) {
                    throw new Exception('Rating must be between 1 and 5');
                }
                
                // Verify complaint belongs to user
                $sql = "SELECT complaint_id FROM complaints 
                       WHERE complaint_id = $complaint_id 
                       AND complainer_id_hash = '" . $conn->real_escape_string(hash('sha256', $user_id_session)) . "'";
                $result = $conn->query($sql);
                
                if (!$result || $result->num_rows === 0) {
                    throw new Exception('Complaint not found or access denied');
                }
                
                // Update rating
                $sql = "
                    UPDATE complaints 
                    SET rating = $rating,
                        rating_comment = '" . $conn->real_escape_string($comment) . "',
                        rated_at = NOW(),
                        updated_at = NOW()
                    WHERE complaint_id = $complaint_id
                ";
                
                if (!$conn->query($sql)) {
                    throw new Exception("Update failed: " . $conn->error);
                }
                
                http_response_code(200);
                echo json_encode(['success' => true, 'message' => 'Rating submitted successfully']);
                $conn->close();
                exit();
                
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                $conn->close();
                exit();
            }
        }
        
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid API action']);
        $conn->close();
        exit();
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit();
    }
}

// ============================================================
// MAIN PAGE
// ============================================================

$translations = [
    'th' => [
        'page_title' => 'ส่งคำร้องเรียน',
        'page_subtitle' => 'ส่งคำร้องเรียนของคุณแบบไม่เปิดเผยตัวตน',
        'tab_submit_complaint' => 'ส่งคำร้องเรียนใหม่',
        'tab_my_complaints' => 'รายการคำร้องเรียนของฉัน',
        'employee_information' => 'ข้อมูลพนักงาน',
        'employee_id' => 'รหัสพนักงาน',
        'name' => 'ชื่อ',
        'position' => 'ตำแหน่ง',
        'department' => 'แผนก',
        'complaint_category' => 'หมวดหมู่การร้องเรียน',
        'select_category' => 'เลือกหมวดหมู่',
        'subject' => 'หัวข้อ',
        'subject_placeholder' => 'กรอกหัวข้อคำร้องเรียนโดยสั้นๆ (อย่างน้อย 5 ตัวอักษร)...',
        'description' => 'รายละเอียด',
        'description_placeholder' => 'โปรดอธิบายรายละเอียดคำร้องเรียนของคุณอย่างละเอียด (อย่างน้อย 20 ตัวอักษร)...',
        'attachment' => 'แนบไฟล์หลักฐาน',
        'attachment_optional' => '(ไม่บังคับ)',
        'attachment_note' => 'รองรับไฟล์: PDF, DOCX, JPG, PNG (สูงสุด 5MB)',
        'important_notice' => 'ข้อมูลสำคัญ',
        'notice_1' => '🔒 คำร้องเรียนของคุณจะถูกส่งแบบไม่เปิดเผยตัวตน',
        'notice_2' => '✓ เจ้าหน้าที่จะไม่สามารถเห็นว่าใครเป็นผู้ร้องเรียน',
        'notice_3' => '✓ เฉพาะผู้ดูแลระบบเท่านั้นที่สามารถเข้าถึงข้อมูลจริงได้',
        'notice_4' => '⚠️ หลังจากส่งแล้ว คุณไม่สามารถแก้ไขคำร้องเรียนได้',
        'submit_request' => 'ส่งคำร้องเรียน',
        'cancel' => 'ยกเลิก',
        'required' => 'จำเป็น',
        'please_select_category' => 'โปรดเลือกหมวดหมู่การร้องเรียน',
        'subject_too_short' => 'หัวข้อต้องมีอย่างน้อย 5 ตัวอักษร',
        'description_too_short' => 'รายละเอียดต้องมีอย่างน้อย 20 ตัวอักษร',
        'confirm_submit' => 'คุณแน่ใจว่าต้องการส่งคำร้องเรียนนี้หรือไม่?\n\nคำร้องเรียนจะถูกส่งแบบไม่เปิดเผยตัวตนและไม่สามารถแก้ไขได้',
        'error_occurred' => 'เกิดข้อผิดพลาด:',
        'failed_to_submit' => 'ล้มเหลวในการส่งคำร้องเรียน',
        'success_submitted' => 'ส่งคำร้องเรียนเรียบร้อยแล้ว',
        'anonymous_badge' => 'ไม่เปิดเผยตัวตน',
        'no_complaints' => 'ไม่มีคำร้องเรียนใด ๆ',
        'complaint_no' => 'ลำดับที่',
        'category' => 'หมวดหมู่',
        'submitted_date' => 'วันที่ส่ง',
        'status' => 'สถานะ',
        'status_new' => 'ใหม่',
        'status_in_progress' => 'กำลังดำเนิน',
        'status_completed' => 'เสร็จสิ้น',
        'status_cancelled' => 'ยกเลิก',
        'status_resolved' => 'แก้ไขแล้ว',
        'status_closed' => 'ปิดแล้ว',
        'status_under_review' => 'กำลังตรวจสอบ',
        'status_dismissed' => 'ยกเลิก',
        'view_details' => 'ดูรายละเอียด',
        'no_complaints_message' => 'คุณยังไม่ได้ส่งคำร้องเรียนใด ๆ สามารถส่งคำร้องเรียนใหม่ได้จากแท็บด้านบน',
        // Details modal
        'complaint_details' => 'รายละเอียดคำร้องเรียน',
        'activity_log' => 'ประวัติการดำเนินการ',
        'response' => 'คำตอบจากเจ้าหน้าที่',
        'close' => 'ปิด',
        // Rating
        'rating' => 'ให้คะแนน',
        'rate_complaint' => 'กรุณาให้คะแนนความพึงพอใจ',
        'rating_instruction' => 'คลิกดาวเพื่อให้คะแนน (1-5)',
        'rating_comment' => 'ข้อเสนอแนะ',
        'rating_comment_placeholder' => 'ข้อเสนอแนะเพิ่มเติม (ไม่บังคับ)',
        'submit_rating' => 'ส่งคะแนน',
        'already_rated' => 'คุณให้คะแนนแล้ว',
        'rating_1' => 'ไม่พอใจ',
        'rating_2' => 'พอใจน้อย',
        'rating_3' => 'พอใจปานกลาง',
        'rating_4' => 'พอใจมาก',
        'rating_5' => 'พอใจมากที่สุด',
    ],
    'en' => [
        'page_title' => 'Submit Complaint',
        'page_subtitle' => 'Submit your complaint anonymously',
        'tab_submit_complaint' => 'Submit New Complaint',
        'tab_my_complaints' => 'My Complaints',
        'employee_information' => 'Employee Information',
        'employee_id' => 'Employee ID',
        'name' => 'Name',
        'position' => 'Position',
        'department' => 'Department',
        'complaint_category' => 'Complaint Category',
        'select_category' => 'Select category',
        'subject' => 'Subject',
        'subject_placeholder' => 'Enter a brief subject for your complaint (minimum 5 characters)...',
        'description' => 'Description',
        'description_placeholder' => 'Please describe your complaint in detail (minimum 20 characters)...',
        'attachment' => 'Attach Evidence',
        'attachment_optional' => '(Optional)',
        'attachment_note' => 'Supported files: PDF, DOCX, JPG, PNG (Max 5MB)',
        'important_notice' => 'Important Notice',
        'notice_1' => '🔒 Your complaint will be submitted anonymously',
        'notice_2' => '✓ Officers will not be able to see who filed the complaint',
        'notice_3' => '✓ Only system administrators can access the real identity',
        'notice_4' => '⚠️ Once submitted, you cannot edit the complaint',
        'submit_request' => 'Submit Complaint',
        'cancel' => 'Cancel',
        'required' => 'Required',
        'please_select_category' => 'Please select a complaint category',
        'subject_too_short' => 'Subject must be at least 5 characters',
        'description_too_short' => 'Description must be at least 20 characters',
        'confirm_submit' => 'Are you sure you want to submit this complaint?\n\nThe complaint will be submitted anonymously and cannot be edited.',
        'error_occurred' => 'An error occurred:',
        'failed_to_submit' => 'Failed to submit complaint',
        'success_submitted' => 'Complaint submitted successfully',
        'anonymous_badge' => 'Anonymous',
        'no_complaints' => 'No Complaints',
        'complaint_no' => 'No.',
        'category' => 'Category',
        'submitted_date' => 'Submitted Date',
        'status' => 'Status',
        'status_new' => 'New',
        'status_in_progress' => 'In Progress',
        'status_completed' => 'Completed',
        'status_cancelled' => 'Cancelled',
        'status_resolved' => 'Resolved',
        'status_closed' => 'Closed',
        'status_under_review' => 'Under Review',
        'status_dismissed' => 'Dismissed',
        'view_details' => 'View Details',
        'no_complaints_message' => 'You have not submitted any complaints yet. You can submit a new complaint from the tab above.',
        // Details modal
        'complaint_details' => 'Complaint Details',
        'activity_log' => 'Activity Log',
        'response' => 'Officer Response',
        'close' => 'Close',
        // Rating
        'rating' => 'Rating',
        'rate_complaint' => 'Please rate your satisfaction',
        'rating_instruction' => 'Click stars to rate (1-5)',
        'rating_comment' => 'Feedback',
        'rating_comment_placeholder' => 'Additional feedback (optional)',
        'submit_rating' => 'Submit Rating',
        'already_rated' => 'You have already rated this',
        'rating_1' => 'Very Unsatisfied',
        'rating_2' => 'Unsatisfied',
        'rating_3' => 'Neutral',
        'rating_4' => 'Satisfied',
        'rating_5' => 'Very Satisfied',
    ],
];

$t = $translations[$current_lang] ?? $translations['th'];

ensure_session_started();
$user_id = $_SESSION['user_id'];

// Fetch employee data
$conn = getDbConnection();
$lang_suffix = ($current_lang === 'en') ? '_en' : (($current_lang === 'my') ? '_my' : '_th');

$sql = "SELECT 
    e.employee_id,
    CASE 
        WHEN '{$current_lang}' = 'en' THEN e.full_name_en
        WHEN '{$current_lang}' = 'my' THEN e.full_name_th
        ELSE e.full_name_th
    END as full_name,
    COALESCE(p.position_name{$lang_suffix}, p.position_name_th) as position_name,
    COALESCE(d.department_name{$lang_suffix}, d.department_name_th) as department_name
FROM employees e
LEFT JOIN position_master p ON e.position_id = p.position_id
LEFT JOIN department_master d ON e.department_id = d.department_id
WHERE e.employee_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$employee = $result->fetch_assoc();
$stmt->close();

if (!$employee) {
    echo "Error: Employee data not found";
    exit();
}

// Fetch complaint categories
$categories = [];
$sql = "SELECT category_id, 
        category_name_th, category_name_en, category_name_my
        FROM complaint_category_master 
        WHERE is_active = 1 
        ORDER BY category_name_th";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $categories[] = $row;
}

// Fetch user's complaints
$user_complaints = [];
$complainer_id_hash = hash('sha256', $user_id);
$sql = "SELECT 
    c.complaint_id,
    c.category_id,
    ccm.category_name_th, ccm.category_name_en, ccm.category_name_my,
    c.subject,
    c.status,
    c.rating,
    c.created_at
FROM complaints c
LEFT JOIN complaint_category_master ccm ON c.category_id = ccm.category_id
WHERE c.complainer_id_hash = ?
ORDER BY c.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $complainer_id_hash);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $user_complaints[] = $row;
}
$stmt->close();

$message = '';
$message_type = '';
$active_tab = $_GET['tab'] ?? 'submit';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = intval($_POST['category_id'] ?? 0);
    $subject = trim($_POST['subject'] ?? '');
    $description = trim($_POST['description'] ?? '');
    
    if ($category_id <= 0 || empty($subject) || empty($description)) {
        $message = $t['failed_to_submit'];
        $message_type = 'error';
    } else {
        $attachment_path = null;
        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = __DIR__ . '/../../uploads/complaints/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_extension = strtolower(pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['pdf', 'docx', 'jpg', 'jpeg', 'png'];
            
            if (in_array($file_extension, $allowed_extensions) && $_FILES['attachment']['size'] <= 5 * 1024 * 1024) {
                $unique_filename = uniqid('complaint_') . '.' . $file_extension;
                $upload_path = $upload_dir . $unique_filename;
                
                if (move_uploaded_file($_FILES['attachment']['tmp_name'], $upload_path)) {
                    $attachment_path = '/uploads/complaints/' . $unique_filename;
                }
            }
        }
        
        $complainer_id_hash = hash('sha256', $user_id);
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
        $browser_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        $conn->begin_transaction();
        
        try {
            $stmt = $conn->prepare("
                INSERT INTO complaints 
                (complainer_id_hash, category_id, subject, description, attachment_path, status, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, 'New', NOW(), NOW())
            ");
            $stmt->bind_param("sisss", $complainer_id_hash, $category_id, $subject, $description, $attachment_path);
            
            if (!$stmt->execute()) {
                throw new Exception('Failed to insert complaint');
            }
            
            $complaint_id = $conn->insert_id;
            $stmt->close();
            
            $stmt = $conn->prepare("
                INSERT INTO complaint_complainer_audit 
                (complaint_id, complainer_id_plain, complainer_id_hash, ip_address, browser_agent, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            $stmt->bind_param("issss", $complaint_id, $user_id, $complainer_id_hash, $ip_address, $browser_agent);
            $stmt->execute();
            $stmt->close();
            
            $conn->commit();
            
            header("Location: " . BASE_PATH . "/views/employee/request_complaint.php?tab=list&success=1");
            exit();
            
        } catch (Exception $e) {
            $conn->rollback();
            $message = $t['failed_to_submit'] . ': ' . $e->getMessage();
            $message_type = 'error';
        }
    }
}

$conn->close();

$display_name = $employee['full_name'] ?? 'Unknown';

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';

function getStatusBadge($status, $t, $is_dark) {
    $statuses = [
        'New' => ['color' => 'bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200', 'label' => $t['status_new']],
        'In Progress' => ['color' => 'bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200', 'label' => $t['status_in_progress']],
        'Complete' => ['color' => 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200', 'label' => $t['status_completed']],
        'Resolved' => ['color' => 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200', 'label' => $t['status_resolved']],
        'Closed' => ['color' => 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200', 'label' => $t['status_closed']],
        'Cancelled' => ['color' => 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200', 'label' => $t['status_cancelled']],
    ];
    
    return $statuses[$status] ?? $statuses['New'];
}

function getCategoryName($category_row, $current_lang) {
    if ($current_lang === 'en') {
        return $category_row['category_name_en'] ?? $category_row['category_name_th'];
    } elseif ($current_lang === 'my') {
        return $category_row['category_name_my'] ?? $category_row['category_name_th'];
    }
    return $category_row['category_name_th'];
}
?>

<div class="lg:ml-64">
    <div class="container mx-auto px-4 py-6 max-w-4xl">
        
        <!-- Success Alert -->
        <?php if (isset($_GET['success'])): ?>
            <div class="mb-6 p-4 bg-green-50 dark:bg-green-900 border border-green-200 dark:border-green-700 text-green-800 dark:text-green-200 rounded-lg flex items-start gap-3">
                <svg class="w-6 h-6 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div class="flex-1"><?php echo $t['success_submitted']; ?></div>
            </div>
        <?php endif; ?>

        <!-- Error Alert -->
        <?php if ($message): ?>
            <div class="mb-6 p-4 bg-red-50 dark:bg-red-900 border border-red-200 dark:border-red-700 text-red-800 dark:text-red-200 rounded-lg flex items-start gap-3">
                <svg class="w-6 h-6 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div class="flex-1"><?php echo htmlspecialchars($message); ?></div>
            </div>
        <?php endif; ?>

        <!-- Page Header -->
        <div class="mb-8 bg-gradient-to-r from-red-600 to-pink-600 rounded-lg shadow-md p-6">
            <div class="flex items-center gap-4">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <div class="flex-1">
                    <div class="flex items-center gap-3">
                        <h1 class="text-3xl font-bold text-white"><?php echo $t['page_title']; ?></h1>
                        <span class="px-3 py-1 bg-white/20 backdrop-blur-sm rounded-full text-white text-sm font-semibold">
                            🔒 <?php echo $t['anonymous_badge']; ?>
                        </span>
                    </div>
                    <p class="text-red-100 text-sm mt-1"><?php echo $t['page_subtitle']; ?></p>
                </div>
            </div>
        </div>

        <!-- Tab Navigation -->
        <div class="mb-8 flex gap-2 border-b <?php echo $border_class; ?> overflow-x-auto">
            <a href="?tab=submit" class="px-6 py-3 font-medium border-b-2 transition whitespace-nowrap 
                <?php echo ($active_tab === 'submit') 
                    ? 'border-red-600 text-red-600' 
                    : ($is_dark ? 'border-transparent text-gray-400 hover:text-gray-300' : 'border-transparent text-gray-600 hover:text-gray-900'); ?>">
                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                </svg>
                <?php echo $t['tab_submit_complaint']; ?>
            </a>
            <a href="?tab=list" class="px-6 py-3 font-medium border-b-2 transition whitespace-nowrap 
                <?php echo ($active_tab === 'list') 
                    ? 'border-red-600 text-red-600' 
                    : ($is_dark ? 'border-transparent text-gray-400 hover:text-gray-300' : 'border-transparent text-gray-600 hover:text-gray-900'); ?>">
                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                </svg>
                <?php echo $t['tab_my_complaints']; ?>
                <?php if (count($user_complaints) > 0): ?>
                    <span class="ml-2 px-2 py-1 bg-red-600 text-white text-xs font-bold rounded-full">
                        <?php echo count($user_complaints); ?>
                    </span>
                <?php endif; ?>
            </a>
        </div>

        <!-- Tab: Submit Complaint Form -->
        <?php if ($active_tab === 'submit'): ?>
        <div class="<?php echo $card_bg; ?> rounded-lg shadow-md border <?php echo $border_class; ?> p-6">
            <form method="POST" action="" id="complaintForm" enctype="multipart/form-data">
                
                <!-- Employee Information Section -->
                <div class="mb-8 pb-8 border-b <?php echo $border_class; ?>">
                    <h2 class="text-lg font-bold <?php echo $text_class; ?> mb-6 flex items-center gap-2">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        <?php echo $t['employee_information']; ?>
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium <?php echo $is_dark ? 'text-gray-300' : 'text-gray-700'; ?> mb-2"><?php echo $t['employee_id']; ?></label>
                            <input type="text" value="<?php echo htmlspecialchars($employee['employee_id']); ?>" readonly
                                class="w-full px-4 py-2 border rounded-lg <?php echo $input_class; ?> opacity-75 cursor-not-allowed">
                        </div>
                        <div>
                            <label class="block text-sm font-medium <?php echo $is_dark ? 'text-gray-300' : 'text-gray-700'; ?> mb-2"><?php echo $t['name']; ?></label>
                            <input type="text" value="<?php echo htmlspecialchars($display_name); ?>" readonly
                                class="w-full px-4 py-2 border rounded-lg <?php echo $input_class; ?> opacity-75 cursor-not-allowed">
                        </div>
                        <div>
                            <label class="block text-sm font-medium <?php echo $is_dark ? 'text-gray-300' : 'text-gray-700'; ?> mb-2"><?php echo $t['position']; ?></label>
                            <input type="text" value="<?php echo htmlspecialchars($employee['position_name'] ?? ''); ?>" readonly
                                class="w-full px-4 py-2 border rounded-lg <?php echo $input_class; ?> opacity-75 cursor-not-allowed">
                        </div>
                        <div>
                            <label class="block text-sm font-medium <?php echo $is_dark ? 'text-gray-300' : 'text-gray-700'; ?> mb-2"><?php echo $t['department']; ?></label>
                            <input type="text" value="<?php echo htmlspecialchars($employee['department_name'] ?? ''); ?>" readonly
                                class="w-full px-4 py-2 border rounded-lg <?php echo $input_class; ?> opacity-75 cursor-not-allowed">
                        </div>
                    </div>
                </div>

                <!-- Category Selection -->
                <div class="mb-8">
                    <label for="category_id" class="block text-sm font-bold <?php echo $text_class; ?> mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                        </svg>
                        <?php echo $t['complaint_category']; ?> <span class="text-red-500">*</span>
                    </label>
                    <select id="category_id" name="category_id" required
                        class="w-full px-4 py-3 border rounded-lg <?php echo $input_class; ?> focus:outline-none focus:ring-2 focus:ring-red-500">
                        <option value=""><?php echo $t['select_category']; ?></option>
                        <?php foreach ($categories as $cat): 
                            $cat_name = getCategoryName($cat, $current_lang);
                        ?>
                            <option value="<?php echo $cat['category_id']; ?>">
                                <?php echo htmlspecialchars($cat_name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Subject -->
                <div class="mb-8">
                    <label for="subject" class="block text-sm font-bold <?php echo $text_class; ?> mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                        </svg>
                        <?php echo $t['subject']; ?> <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="subject" name="subject" required
                        minlength="5" maxlength="300"
                        placeholder="<?php echo $t['subject_placeholder']; ?>"
                        class="w-full px-4 py-3 border rounded-lg <?php echo $input_class; ?> focus:outline-none focus:ring-2 focus:ring-red-500">
                </div>

                <!-- Description -->
                <div class="mb-8">
                    <label for="description" class="block text-sm font-bold <?php echo $text_class; ?> mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <?php echo $t['description']; ?> <span class="text-red-500">*</span>
                    </label>
                    <textarea id="description" name="description" required rows="8"
                        minlength="20"
                        placeholder="<?php echo $t['description_placeholder']; ?>"
                        class="w-full px-4 py-3 border rounded-lg <?php echo $input_class; ?> focus:outline-none focus:ring-2 focus:ring-red-500 resize-none"></textarea>
                </div>

                <!-- File Attachment -->
                <div class="mb-8">
                    <label for="attachment" class="block text-sm font-bold <?php echo $text_class; ?> mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                        </svg>
                        <?php echo $t['attachment']; ?> 
                        <span class="text-xs font-normal <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?>"><?php echo $t['attachment_optional']; ?></span>
                    </label>
                    <input type="file" id="attachment" name="attachment" accept=".pdf,.docx,.jpg,.jpeg,.png"
                        class="w-full px-4 py-3 border rounded-lg <?php echo $input_class; ?> focus:outline-none focus:ring-2 focus:ring-red-500">
                    <p class="text-xs <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?> mt-2">
                        <?php echo $t['attachment_note']; ?>
                    </p>
                </div>

                <!-- Important Notice -->
                <div class="mb-8 p-4 bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 rounded">
                    <div class="flex gap-3">
                        <svg class="w-5 h-5 text-red-600 dark:text-red-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-bold text-red-800 dark:text-red-300"><?php echo $t['important_notice']; ?></p>
                            <ul class="text-sm text-red-700 dark:text-red-400 mt-2 space-y-1">
                                <li><?php echo $t['notice_1']; ?></li>
                                <li><?php echo $t['notice_2']; ?></li>
                                <li><?php echo $t['notice_3']; ?></li>
                                <li><?php echo $t['notice_4']; ?></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex flex-col md:flex-row gap-4 pt-6 border-t <?php echo $border_class; ?>">
                    <a href="<?php echo BASE_PATH; ?>/index.php" class="flex-1 px-6 py-3 border rounded-lg <?php echo $border_class; ?> <?php echo $text_class; ?> hover:<?php echo $is_dark ? 'bg-gray-700' : 'bg-gray-50'; ?> transition font-medium text-center">
                        <?php echo $t['cancel']; ?>
                    </a>
                    <button type="submit" class="flex-1 px-8 py-3 bg-red-600 hover:bg-red-700 text-white rounded-lg transition font-medium shadow-md hover:shadow-lg">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                        </svg>
                        <?php echo $t['submit_request']; ?>
                    </button>
                </div>
            </form>
        </div>

        <!-- Tab: My Complaints List -->
        <?php else: ?>
        <div class="<?php echo $card_bg; ?> rounded-lg shadow-md border <?php echo $border_class; ?> p-6">
            <?php if (empty($user_complaints)): ?>
                <!-- No Complaints Message -->
                <div class="py-12 text-center">
                    <svg class="w-16 h-16 mx-auto <?php echo $is_dark ? 'text-gray-600' : 'text-gray-300'; ?> mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <h3 class="text-xl font-bold <?php echo $text_class; ?> mb-2"><?php echo $t['no_complaints']; ?></h3>
                    <p class="<?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?> mb-6"><?php echo $t['no_complaints_message']; ?></p>
                    <a href="?tab=submit" class="inline-block px-6 py-3 bg-red-600 hover:bg-red-700 text-white rounded-lg transition font-medium">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                        </svg>
                        <?php echo $t['tab_submit_complaint']; ?>
                    </a>
                </div>
            <?php else: ?>
                <!-- Complaints List -->
                <div class="space-y-4">
                    <?php foreach ($user_complaints as $index => $complaint): 
                        $category_name = getCategoryName($complaint, $current_lang);
                        $status_info = getStatusBadge($complaint['status'], $t, $is_dark);
                        $submitted_date = date('d/m/Y H:i', strtotime($complaint['created_at']));
                    ?>
                    <div class="<?php echo $is_dark ? 'bg-gray-700' : 'bg-gray-50'; ?> border rounded-lg p-4 hover:shadow-md transition cursor-pointer" 
                         onclick="viewComplaintDetail(<?php echo $complaint['complaint_id']; ?>)">
                        <div class="flex justify-between items-start mb-3 flex-wrap gap-2">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2 flex-wrap">
                                    <span class="text-sm font-semibold px-3 py-1 bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 rounded">
                                        #<?php echo ($index + 1); ?>
                                    </span>
                                    <span class="text-sm <?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?>">
                                        <?php echo htmlspecialchars($category_name); ?>
                                    </span>
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold <?php echo $status_info['color']; ?>">
                                        <?php echo $status_info['label']; ?>
                                    </span>
                                    <?php if ($complaint['rating']): ?>
                                        <span class="text-xs font-bold px-3 py-1 bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200 rounded">
                                            ⭐ <?php echo $complaint['rating']; ?>/5
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <h3 class="font-bold <?php echo $text_class; ?> text-lg mb-1">
                                    <?php echo htmlspecialchars($complaint['subject']); ?>
                                </h3>
                                <p class="text-sm <?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?>">
                                    📅 <?php echo $submitted_date; ?>
                                </p>
                            </div>
                            <button type="button" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-semibold transition">
                                <?php echo $t['view_details']; ?>
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

    </div>
</div>

<!-- Modal: Complaint Details with Activity Log & Rating -->
<div id="detailModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4 overflow-y-auto">
    <div class="<?php echo $card_bg; ?> rounded-lg shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto border <?php echo $border_class; ?> my-8">
        <div id="detailContent" class="p-6">
            <div class="flex items-center justify-center py-12">
                <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-red-600"></div>
            </div>
        </div>
    </div>
</div>

<script>
const BASE_URL = '<?php echo BASE_URL; ?>';
const API_ENDPOINT = BASE_URL + '/views/employee/request_complaint.php';
const LANG = '<?php echo $current_lang; ?>';
const IS_DARK = <?php echo $is_dark ? 'true' : 'false'; ?>;
const TEXTS = <?php echo json_encode($t); ?>;

const statusLabels = {
    'New': TEXTS.status_new,
    'In Progress': TEXTS.status_in_progress,
    'Under Review': TEXTS.status_under_review,
    'Resolved': TEXTS.status_resolved,
    'Closed': TEXTS.status_closed,
    'Dismissed': TEXTS.status_dismissed,
};

const ratingLabels = {
    1: TEXTS.rating_1,
    2: TEXTS.rating_2,
    3: TEXTS.rating_3,
    4: TEXTS.rating_4,
    5: TEXTS.rating_5,
};

let currentComplaintId = null;

function viewComplaintDetail(complaintId) {
    currentComplaintId = complaintId;
    try {
        document.getElementById('detailModal').classList.remove('hidden');
        
        const url = API_ENDPOINT + '?api_action=get_complaint_detail&complaint_id=' + complaintId;
        
        fetch(url)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderComplaintDetail(data);
                } else {
                    throw new Error(data.message || 'Unknown error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('detailContent').innerHTML = `
                    <div class="p-6 text-center">
                        <div class="text-red-600 font-bold mb-2">⚠️ Error</div>
                        <p class="${IS_DARK ? 'text-gray-400' : 'text-gray-600'} mb-4">${error.message}</p>
                        <button onclick="closeDetailModal()" class="px-6 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg">Close</button>
                    </div>
                `;
            });
    } catch (error) {
        console.error('Error:', error);
        alert('Error loading complaint details');
    }
}

function renderComplaintDetail(data) {
    const complaint = data.complaint;
    const logs = data.logs || [];
    const catNameKey = `category_name_${LANG}`;
    
    const isResolved = ['Resolved', 'Closed'].includes(complaint.status);
    const hasRated = complaint.rating !== null;
    
    let html = `
        <div>
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h3 class="text-2xl font-bold ${IS_DARK ? 'text-white' : 'text-gray-900'}">${complaint.subject || 'No Subject'}</h3>
                    <p class="${IS_DARK ? 'text-gray-400' : 'text-gray-600'} mt-2">
                        ${complaint[catNameKey] || 'Unknown'} · ${new Date(complaint.created_at).toLocaleDateString(LANG === 'th' ? 'th-TH' : 'en-US')}
                    </p>
                </div>
                <button onclick="closeDetailModal()" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <div class="space-y-6">
                <!-- Description -->
                <div class="${IS_DARK ? 'bg-gray-700' : 'bg-gray-50'} p-4 rounded-lg">
                    <p class="${IS_DARK ? 'text-gray-300' : 'text-gray-700'}">${complaint.description || 'No Description'}</p>
                </div>
                
                <!-- Officer Response -->
                ${complaint.officer_response ? `
                <div class="${IS_DARK ? 'bg-gray-700' : 'bg-green-50'} border-l-4 border-green-500 p-4 rounded">
                    <h4 class="font-semibold ${IS_DARK ? 'text-white' : 'text-green-900'} mb-2">${TEXTS.response}</h4>
                    <p class="${IS_DARK ? 'text-gray-300' : 'text-green-800'}">${complaint.officer_response}</p>
                </div>
                ` : ''}
                
                <!-- Activity Log -->
                <div>
                    <h4 class="font-semibold ${IS_DARK ? 'text-white' : 'text-gray-900'} mb-3">${TEXTS.activity_log}</h4>
                    <div class="space-y-2 max-h-64 overflow-y-auto">
                        ${logs.length === 0 ? `<p class="${IS_DARK ? 'text-gray-400' : 'text-gray-600'} text-sm">No activity yet</p>` : logs.map(l => `
                            <div class="${IS_DARK ? 'bg-gray-700' : 'bg-gray-100'} p-3 rounded">
                                <div class="flex justify-between items-start mb-1">
                                    <span class="font-medium text-sm ${IS_DARK ? 'text-gray-300' : 'text-gray-900'}">
                                        ${l.action} ${l.action_by_name ? '- ' + l.action_by_name : ''}
                                    </span>
                                    <span class="text-xs ${IS_DARK ? 'text-gray-400' : 'text-gray-500'}">${new Date(l.created_at).toLocaleDateString(LANG === 'th' ? 'th-TH' : 'en-US')}</span>
                                </div>
                                ${l.remarks ? `<p class="text-sm ${IS_DARK ? 'text-gray-400' : 'text-gray-700'}">${l.remarks}</p>` : ''}
                                ${l.old_status ? `<p class="text-xs ${IS_DARK ? 'text-gray-400' : 'text-gray-600'}">${statusLabels[l.old_status] || l.old_status} → ${statusLabels[l.new_status] || l.new_status}</p>` : ''}
                            </div>
                        `).join('')}
                    </div>
                </div>
                
                <!-- Rating Section -->
                ${isResolved ? `
                <div class="${IS_DARK ? 'bg-gray-700' : 'bg-blue-50'} border-l-4 border-blue-500 p-4 rounded">
                    <h4 class="font-semibold ${IS_DARK ? 'text-white' : 'text-blue-900'} mb-3">
                        <svg class="w-5 h-5 inline mr-2" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                        </svg>
                        ${TEXTS.rate_complaint}
                    </h4>
                    ${hasRated ? `
                        <div class="mb-4 p-3 bg-yellow-100 dark:bg-yellow-900/30 rounded text-yellow-800 dark:text-yellow-300 text-sm">
                            ✓ ${TEXTS.already_rated}: ${complaint.rating}/5 ⭐
                            ${complaint.rating_comment ? `<p class="mt-2">${complaint.rating_comment}</p>` : ''}
                        </div>
                    ` : `
                        <form onsubmit="submitRating(event)">
                            <div class="mb-4">
                                <p class="text-sm ${IS_DARK ? 'text-gray-300' : 'text-blue-900'} mb-3">${TEXTS.rating_instruction}</p>
                                <div class="flex gap-2 text-3xl" id="ratingStars">
                                    <span onclick="setRating(1)" class="cursor-pointer text-gray-400 hover:text-yellow-400 transition">★</span>
                                    <span onclick="setRating(2)" class="cursor-pointer text-gray-400 hover:text-yellow-400 transition">★</span>
                                    <span onclick="setRating(3)" class="cursor-pointer text-gray-400 hover:text-yellow-400 transition">★</span>
                                    <span onclick="setRating(4)" class="cursor-pointer text-gray-400 hover:text-yellow-400 transition">★</span>
                                    <span onclick="setRating(5)" class="cursor-pointer text-gray-400 hover:text-yellow-400 transition">★</span>
                                </div>
                                <input type="hidden" id="ratingValue" name="rating" value="0">
                            </div>
                            
                            <div class="mb-4">
                                <label class="block text-sm ${IS_DARK ? 'text-gray-300' : 'text-blue-900'} mb-2">${TEXTS.rating_comment}</label>
                                <textarea name="comment" rows="3" placeholder="${TEXTS.rating_comment_placeholder}"
                                    class="w-full px-3 py-2 border rounded-lg ${IS_DARK ? 'bg-gray-600 border-gray-500 text-white' : 'bg-white border-gray-300'} text-sm resize-none"></textarea>
                            </div>
                            
                            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg font-medium transition">
                                ${TEXTS.submit_rating}
                            </button>
                        </form>
                    `}
                </div>
                ` : ''}
            </div>
            
            <div class="mt-6 pt-6 border-t ${IS_DARK ? 'border-gray-600' : 'border-gray-200'}">
                <button onclick="closeDetailModal()" class="w-full px-6 py-3 bg-gray-500 hover:bg-gray-600 text-white rounded-lg transition font-medium">
                    ${TEXTS.close}
                </button>
            </div>
        </div>
    `;
    
    document.getElementById('detailContent').innerHTML = html;
}

function setRating(rating) {
    const stars = document.querySelectorAll('#ratingStars span');
    stars.forEach((star, index) => {
        if (index < rating) {
            star.classList.remove('text-gray-400');
            star.classList.add('text-yellow-400');
        } else {
            star.classList.add('text-gray-400');
            star.classList.remove('text-yellow-400');
        }
    });
    document.getElementById('ratingValue').value = rating;
}

function submitRating(event) {
    event.preventDefault();
    
    const rating = parseInt(document.getElementById('ratingValue').value);
    const comment = document.querySelector('textarea[name="comment"]').value;
    
    if (rating === 0) {
        alert(TEXTS.rating_instruction);
        return;
    }
    
    const data = {
        api_action: 'submit_rating',
        complaint_id: currentComplaintId,
        rating: rating,
        comment: comment
    };
    
    fetch(API_ENDPOINT, {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            alert('Rating submitted successfully');
            viewComplaintDetail(currentComplaintId);
        } else {
            alert('Error: ' + result.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to submit rating');
    });
}

function closeDetailModal() {
    document.getElementById('detailModal').classList.add('hidden');
}

// Form validation
document.getElementById('complaintForm')?.addEventListener('submit', function(e) {
    const category = document.getElementById('category_id').value;
    const subject = document.getElementById('subject').value.trim();
    const description = document.getElementById('description').value.trim();
    
    if (!category || category === '') {
        e.preventDefault();
        alert(TEXTS.please_select_category);
        return;
    }
    
    if (subject.length < 5) {
        e.preventDefault();
        alert(TEXTS.subject_too_short);
        return;
    }
    
    if (description.length < 20) {
        e.preventDefault();
        alert(TEXTS.description_too_short);
        return;
    }
    
    if (!confirm(TEXTS.confirm_submit)) {
        e.preventDefault();
    }
});

// File size validation
document.getElementById('attachment')?.addEventListener('change', function() {
    if (this.files && this.files[0]) {
        const fileSize = this.files[0].size / 1024 / 1024;
        if (fileSize > 5) {
            alert('File size must be less than 5MB');
            this.value = '';
        }
    }
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>