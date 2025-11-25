<?php

/**
 * Request Management Page - IMPROVED VERSION
 * ✅ Enhanced theme color support (Dark/Light Mode)
 * ✅ Multi-language font optimization (Thai, English, Myanmar)
 * ✅ Better contrast and readability
 * ✅ Consistent color scheme across all components
 * ✅ Officer TA can only see ID Card Requests
 * ✅ Admin and Officer Payroll can see all request types
 * ✅ Full support for ID Card generation with photo upload
 * ✅ Full support for Certificate generation with salary input
 * ✅ Rating display and average statistics
 * Supports: Thai (ไทย), English (EN), Myanmar (မြန်မာ)
 * Features: Multi-language UI, Dark Mode, Mobile Responsive
 * Admin/Officer only - Manage all service requests
 */
require_once __DIR__ . '/../../config/db_config.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../db/Localization.php';

// Require admin or officer role
AuthController::requireRole(['admin', 'officer_payroll', 'officer_ta']);

// Get current settings from session
$current_lang = $_SESSION['language'] ?? 'th';
$theme_mode = $_SESSION['theme_mode'] ?? 'light';
$is_dark = ($theme_mode === 'dark');
$user_id = $_SESSION['user_id'] ?? '';

// Enhanced theme colors with better contrast
$card_bg = $is_dark ? 'bg-gray-800' : 'bg-white';
$text_class = $is_dark ? 'text-gray-100' : 'text-gray-900';
$text_secondary = $is_dark ? 'text-gray-300' : 'text-gray-600';
$text_muted = $is_dark ? 'text-gray-400' : 'text-gray-500';
$bg_class = $is_dark ? 'bg-gray-900' : 'bg-gray-50';
$border_class = $is_dark ? 'border-gray-700' : 'border-gray-200';
$input_class = $is_dark ? 'bg-gray-700 border-gray-600 text-gray-100 placeholder-gray-400' : 'bg-white border-gray-300 text-gray-900 placeholder-gray-500';
$hover_bg = $is_dark ? 'hover:bg-gray-700' : 'hover:bg-gray-100';

// Multi-language translations
$translations = [
    'th' => [
        'page_title' => 'จัดการคำขอ',
        'page_subtitle' => 'ตรวจสอบและจัดการคำขอบริการของพนักงานทั้งหมด',
        'page_subtitle_ta' => 'ตรวจสอบและจัดการคำขอทำบัตรพนักงาน',
        'total' => 'รวม',
        'new' => 'ใหม่',
        'in_progress' => 'กำลังดำเนิน',
        'complete' => 'เสร็จสิ้น',
        'cancelled' => 'ยกเลิก',
        'avg_rating' => 'คะแนนเฉลี่ย',
        'search' => 'ค้นหา',
        'search_placeholder' => 'รหัสพนักงานหรือชื่อ',
        'status' => 'สถานะ',
        'all_status' => 'สถานะทั้งหมด',
        'request_type' => 'ประเภทคำขอ',
        'all_types' => 'ประเภททั้งหมด',
        'filter' => 'ค้นหา',
        'reset' => 'รีเซ็ต',
        'request_id' => 'รหัสคำขอ',
        'type' => 'ประเภท',
        'employee' => 'พนักงาน',
        'employee_name' => 'ชื่อพนักงาน',
        'created' => 'สร้างเมื่อ',
        'handler' => 'ผู้ดำเนิน',
        'rating' => 'คะแนน',
        'actions' => 'การกระทำ',
        'view_details' => 'ดูรายละเอียด',
        'no_requests' => 'ไม่พบคำขอ',
        'try_adjusting' => 'ลองปรับตัวกรองของคุณ',
        'request_details' => 'รายละเอียดคำขอ',
        'status_update' => 'อัปเดตสถานะ',
        'handler_remarks' => 'หมายเหตุของผู้ดำเนิน',
        'update_request' => 'อัปเดตคำขอ',
        'close' => 'ปิด',
        'loading' => 'กำลังโหลดรายละเอียดคำขอ...',
        'error_loading' => 'ข้อผิดพลาดในการโหลดคำขอ',
        'updated_successfully' => 'อัปเดตคำขอเรียบร้อยแล้ว',
        'update_error' => 'ข้อผิดพลาด: ',
        'failed_update' => 'ล้มเหลวในการอัปเดตคำขอ: ',
        'unassigned' => 'ยังไม่ได้มอบหมาย',
        'optional' => '(ไม่บังคับ)',
        'updating' => 'กำลังอัปเดต...',
        'position' => 'ตำแหน่ง',
        'division' => 'แผนก',
        'request_info' => 'ข้อมูลคำขอ',
        'employee_info' => 'ข้อมูลพนักงาน',
        'certificate_type' => 'ประเภทหนังสือรับรอง',
        'generate_id_card' => 'สร้างบัตรพนักงาน',
        'id_card_info' => 'บัตรพนักงาน',
        'upload_photo' => 'อัพโหลดรูปถ่าย',
        'change_photo' => 'เปลี่ยนรูปถ่าย',
        'uploading' => 'กำลังอัพโหลด...',
        'photo_uploaded' => 'อัพโหลดรูปสำเร็จ',
        'upload_failed' => 'อัพโหลดล้มเหลว',
        'select_photo' => 'เลือกรูปถ่าย',
        'max_5mb' => 'ไฟล์สูงสุด 5MB',
        'customer_satisfaction' => 'ความพึงพอใจของลูกค้า',
        'satisfaction_score' => 'คะแนนความพึงพอใจ',
        'customer_feedback' => 'ความคิดเห็นจากลูกค้า',
        'no_rating' => 'ยังไม่มีการให้คะแนน',
        'no_feedback' => 'ไม่มีความคิดเห็นเพิ่มเติม',
        'rated_requests' => 'คำขอที่มีคะแนน',
        'of' => 'จาก',
    ],
    'en' => [
        'page_title' => 'Request Management',
        'page_subtitle' => 'Review and manage all employee service requests',
        'page_subtitle_ta' => 'Review and manage employee ID card requests',
        'total' => 'Total',
        'new' => 'New',
        'in_progress' => 'In Progress',
        'complete' => 'Complete',
        'cancelled' => 'Cancelled',
        'avg_rating' => 'Avg Rating',
        'search' => 'Search',
        'search_placeholder' => 'Employee ID or Name',
        'status' => 'Status',
        'all_status' => 'All Status',
        'request_type' => 'Request Type',
        'all_types' => 'All Types',
        'filter' => 'Filter',
        'reset' => 'Reset',
        'request_id' => 'Request ID',
        'type' => 'Type',
        'employee' => 'Employee',
        'employee_name' => 'Employee Name',
        'created' => 'Created',
        'handler' => 'Handler',
        'rating' => 'Rating',
        'actions' => 'Actions',
        'view_details' => 'View Details',
        'no_requests' => 'No requests found',
        'try_adjusting' => 'Try adjusting your filters',
        'request_details' => 'Request Details',
        'status_update' => 'Update Status',
        'handler_remarks' => 'Handler Remarks',
        'update_request' => 'Update Request',
        'close' => 'Close',
        'loading' => 'Loading request details...',
        'error_loading' => 'Error loading request',
        'updated_successfully' => 'Request updated successfully',
        'update_error' => 'Error: ',
        'failed_update' => 'Failed to update request: ',
        'unassigned' => 'Unassigned',
        'optional' => '(Optional)',
        'updating' => 'Updating...',
        'position' => 'Position',
        'division' => 'Division',
        'request_info' => 'Request Information',
        'employee_info' => 'Employee Information',
        'certificate_type' => 'Certificate Type',
        'generate_id_card' => 'Generate ID Card',
        'id_card_info' => 'Employee ID Card',
        'upload_photo' => 'Upload Photo',
        'change_photo' => 'Change Photo',
        'uploading' => 'Uploading...',
        'photo_uploaded' => 'Photo uploaded successfully',
        'upload_failed' => 'Upload failed',
        'select_photo' => 'Select Photo',
        'max_5mb' => 'Max file size 5MB',
        'customer_satisfaction' => 'Customer Satisfaction',
        'satisfaction_score' => 'Satisfaction Score',
        'customer_feedback' => 'Customer Feedback',
        'no_rating' => 'Not rated yet',
        'no_feedback' => 'No additional feedback',
        'rated_requests' => 'Rated Requests',
        'of' => 'of',
    ],
    'my' => [
        'page_title' => 'တောင်းခံမှုစီမံခန့်ခွဲမှု',
        'page_subtitle' => 'အလုပ်သမားဝန်ဆောင်မှုတောင်းခံမှုအားလုံးကိုပြန်လည်သုံးသပ်ခြင်းနှင့်စီမံခန့်ခွဲမည်',
        'page_subtitle_ta' => 'အလုပ်သမားအိုင်ဒီကဒ်တောင်းခံမှုများကိုပြန်လည်သုံးသပ်ခြင်းနှင့်စီမံခန့်ခွဲမည်',
        'total' => 'စုစုပေါင်း',
        'new' => 'အသစ်',
        'in_progress' => 'လုပ်ဆောင်နေ',
        'complete' => 'ပြည့်စုံမည်',
        'cancelled' => 'ပယ်ဖျက်ခြင်း',
        'avg_rating' => 'ပျမ်းမျှအဆင့်',
        'search' => 'ရှာဖွေမည်',
        'search_placeholder' => 'အလုပ်သမားအိုင်ဒီ သို့မဟုတ် အမည်',
        'status' => 'အနေအထား',
        'all_status' => 'အနေအထားအားလုံး',
        'request_type' => 'တောင်းခံမှုအမျိုးအစား',
        'all_types' => 'အမျိုးအစားအားလုံး',
        'filter' => 'စစ်ထုတ်မည်',
        'reset' => 'ပြန်သတ်မှတ်မည်',
        'request_id' => 'တောင်းခံမှုအိုင်ဒီ',
        'type' => 'အမျိုးအစား',
        'employee' => 'အလုပ်သမား',
        'employee_name' => 'အလုပ်သမားအမည်',
        'created' => 'ဖန်တီးသည်',
        'handler' => 'အကျင့်တည်ဝတ်ပြုသူ',
        'rating' => 'အဆင့်',
        'actions' => 'အရေးယူမှုများ',
        'view_details' => 'အသေးစိတ်ကြည့်ရှုမည်',
        'no_requests' => 'တောင်းခံမှုများမတွေ့ရှိ',
        'try_adjusting' => 'သင်၏စစ်ထုတ်မှုများကိုချိန်ညှိရန်ကြိုးစားပါ',
        'request_details' => 'တောင်းခံမှုအသေးစိတ်',
        'status_update' => 'အနေအထားအချက်အလက်အသစ်',
        'handler_remarks' => 'အကျင့်တည်ဝတ်ပြုသူမှတ်ချက်များ',
        'update_request' => 'တောင်းခံမှုအချက်အလက်အသစ်',
        'close' => 'ပိတ်မည်',
        'loading' => 'တောင်းခံမှုအသေးစိတ်ကိုတင်ဆက်နေသည်...',
        'error_loading' => 'တောင်းခံမှုများကိုတင်ဆက်ရာတွင်အမှားအယွင်း',
        'updated_successfully' => 'တောင်းခံမှုအချက်အလက်အသစ်ပြီးစီးသည်',
        'update_error' => 'အမှားအယွင်း: ',
        'failed_update' => 'တောင်းခံမှုအချက်အလက်အသစ်မပြုလုပ်နိုင်ခြင်း: ',
        'unassigned' => 'မမှတ်မထားသေးခြင်း',
        'optional' => '(အကြိုက်ရှိသည့်)',
        'updating' => 'အချက်အလက်အသစ်ပြုလုပ်နေ...',
        'position' => 'ရာထူး',
        'division' => 'ဌာန',
        'request_info' => 'တောင်းခံမှုအချက်အလက်',
        'employee_info' => 'အလုပ်သမားအချက်အလက်',
        'certificate_type' => 'Certificate Type',
        'generate_id_card' => 'အိုင်ဒီကဒ်ဖန်တီးမည်',
        'id_card_info' => 'အလုပ်သမားအိုင်ဒီကဒ်',
        'upload_photo' => 'ဓာတ်ပုံတင်မည်',
        'change_photo' => 'ဓာတ်ပုံပြောင်းမည်',
        'uploading' => 'တင်နေသည်...',
        'photo_uploaded' => 'ဓာတ်ပုံတင်ပြီးပါပြီ',
        'upload_failed' => 'တင်မအောင်မြင်ပါ',
        'select_photo' => 'ဓာတ်ပုံရွေးချယ်ပါ',
        'max_5mb' => 'ဖိုင်အများဆုံး 5MB',
        'customer_satisfaction' => 'ဖောက်သည်စိတ်ကျေနပ်မှု',
        'satisfaction_score' => 'စိတ်ကျေနပ်မှုအမှတ်',
        'customer_feedback' => 'ဖောက်သည်အကြံပြုချက်',
        'no_rating' => 'အဆင့်သတ်မှတ်ထားခြင်းမရှိပါ',
        'no_feedback' => 'အခြားအကြံပြုချက်မရှိပါ',
        'rated_requests' => 'အဆင့်သတ်မှတ်ထားသည့်တောင်းခံမှု',
        'of' => 'မှ',
    ]
];

// Get current language strings
$t = $translations[$current_lang] ?? $translations['th'];
$page_title = $t['page_title'];

ensure_session_started();

// ✅ CRITICAL: Check user role for filtering
$user_role = $_SESSION['role'] ?? '';
$is_officer_ta = ($user_role === 'officer_ta');

// Get filter parameters
$status_filter = $_GET['status'] ?? 'all';
$type_filter = $_GET['type'] ?? 'all';
$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

$conn = getDbConnection();
if (!$conn) {
    die("Database connection failed");
}

// Build WHERE clause
$where_conditions = ["1=1"];
$params = [];
$types = '';

if ($status_filter !== 'all') {
    $where_conditions[] = "r.status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

if (!empty($search)) {
    $where_conditions[] = "(r.employee_id LIKE ? OR e.full_name_th LIKE ? OR e.full_name_en LIKE ?)";
    $search_term = '%' . $search . '%';
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= 'sss';
}

$where_sql = implode(' AND ', $where_conditions);

// Function to get requests from a table
function getRequests($conn, $table, $type_name, $type_key, $where_sql, $params, $types, $offset, $per_page, $current_lang)
{
    $id_column = ($table === 'document_submissions') ? 'submission_id' : 'request_id';
    $name_column = ($current_lang === 'en') ? 'e.full_name_en' : 'e.full_name_th';

    $sql = "SELECT 
        r.$id_column as request_id,
        r.employee_id,
        $name_column as employee_name,
        e.full_name_th,
        e.full_name_en,
        '$type_name' as request_type,
        '$type_key' as request_type_key,
        r.status,
        r.created_at,
        r.handler_id,
        r.handler_remarks,
        r.satisfaction_score,
        r.satisfaction_feedback
    FROM $table r
    LEFT JOIN employees e ON r.employee_id = e.employee_id
    WHERE $where_sql 
    ORDER BY r.created_at DESC 
    LIMIT ? OFFSET ?";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Prepare failed for table $table: " . $conn->error);
        return [];
    }

    if (!empty($params)) {
        $all_params = array_merge($params, [$per_page, $offset]);
        $all_types = $types . 'ii';
        $stmt->bind_param($all_types, ...$all_params);
    } else {
        $stmt->bind_param('ii', $per_page, $offset);
    }

    if (!$stmt->execute()) {
        error_log("Execute failed for table $table: " . $stmt->error);
        $stmt->close();
        return [];
    }

    $result = $stmt->get_result();
    $requests = [];
    while ($row = $result->fetch_assoc()) {
        $row['table_name'] = $table;
        $requests[] = $row;
    }

    $stmt->close();
    return $requests;
}

// Request types configuration - ALL REQUEST TYPES
$all_request_types = [
    'leave_requests' => ['label_en' => 'Leave Request', 'label_th' => 'คำขอใบลา', 'label_my' => 'အငြိုးပြုစုတောင်းခံမှု'],
    'certificate_requests' => ['label_en' => 'Certificate Request', 'label_th' => 'คำขอหนังสือรับรอง', 'label_my' => 'လက်မှတ်တောင်းခံမှု'],
    'id_card_requests' => ['label_en' => 'ID Card Request', 'label_th' => 'คำขอทำบัตรพนักงาน', 'label_my' => 'အိုင်ဒီကဒ်တောင်းခံမှု'],
    'shuttle_bus_requests' => ['label_en' => 'Shuttle Bus Request', 'label_th' => 'คำขอขึ้นรถรับส่ง', 'label_my' => 'ကားရီးယားတောင်းခံမှု'],
    'locker_requests' => ['label_en' => 'Locker Request', 'label_th' => 'คำขอใช้งานตู้ล็อกเกอร์', 'label_my' => 'အိတ်ဆောင်တင်သွင်းမှုတောင်းခံမှု'],
    'supplies_requests' => ['label_en' => 'Supplies Request', 'label_th' => 'คำขอเบิกอุปกรณ์', 'label_my' => 'ပရိယာယ်တောင်းခံမှု'],
    'skill_test_requests' => ['label_en' => 'Skill Test Request', 'label_th' => 'คำขอสอบทักษะ', 'label_my' => 'အရည်အချင်းစမ်းသပ်မှုတောင်းခံမှု'],
    'document_submissions' => ['label_en' => 'Document Submission', 'label_th' => 'ลงชื่อส่งเอกสาร', 'label_my' => 'စာ類တင်သွင်းမှု']
];

// ✅ CRITICAL: Filter request types based on user role
if ($is_officer_ta) {
    // Officer TA can only see ID Card Requests
    $request_types = [
        'id_card_requests' => $all_request_types['id_card_requests']
    ];
} else {
    // Admin and Officer Payroll can see all request types
    $request_types = $all_request_types;
}

// Get all requests based on type filter
$all_requests = [];
if ($type_filter === 'all') {
    foreach ($request_types as $table => $labels) {
        $type_name = $labels['label_en'];
        $requests = getRequests($conn, $table, $type_name, $table, $where_sql, $params, $types, 0, $per_page, $current_lang);
        $all_requests = array_merge($all_requests, $requests);
    }
} else {
    // ✅ Check if the type_filter is allowed for this user
    if (isset($request_types[$type_filter])) {
        $labels = $request_types[$type_filter];
        $type_name = $labels['label_en'];
        $all_requests = getRequests($conn, $type_filter, $type_name, $type_filter, $where_sql, $params, $types, $offset, $per_page, $current_lang);
    }
}

// Sort by created_at DESC
usort($all_requests, function ($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});

// Limit to page size
$all_requests = array_slice($all_requests, 0, $per_page);

// ✅ Get statistics - only from allowed tables for this user
$stats = [
    'total' => 0,
    'new' => 0,
    'in_progress' => 0,
    'complete' => 0,
    'cancelled' => 0,
    'total_ratings' => 0,
    'sum_ratings' => 0,
    'avg_rating' => 0
];

foreach ($request_types as $table => $labels) {
    // Count by status
    $result = $conn->query("SELECT status, COUNT(*) as count FROM $table GROUP BY status");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $stats['total'] += $row['count'];
            $status_key = strtolower(str_replace(' ', '_', $row['status']));
            if (isset($stats[$status_key])) {
                $stats[$status_key] += $row['count'];
            }
        }
    }

    // Calculate average rating
    $rating_result = $conn->query("SELECT COUNT(*) as rated_count, SUM(satisfaction_score) as total_score 
                                   FROM $table 
                                   WHERE satisfaction_score IS NOT NULL AND satisfaction_score > 0");
    if ($rating_result && $rating_row = $rating_result->fetch_assoc()) {
        $stats['total_ratings'] += $rating_row['rated_count'];
        $stats['sum_ratings'] += $rating_row['total_score'];
    }
}

// Calculate average
if ($stats['total_ratings'] > 0) {
    $stats['avg_rating'] = round($stats['sum_ratings'] / $stats['total_ratings'], 2);
}

$conn->close();

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>
<!DOCTYPE html>
<html lang="<?php echo $current_lang; ?>" class="<?php echo $is_dark ? 'dark' : ''; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $t['page_title']; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Google Fonts for better multi-language support -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Noto+Sans+Thai:wght@300;400;500;600;700&family=Noto+Sans+Myanmar:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            /* Light mode colors */
            --color-primary: #2563eb;
            --color-primary-hover: #1d4ed8;
            --color-success: #059669;
            --color-success-hover: #047857;
            --color-warning: #d97706;
            --color-warning-hover: #b45309;
            --color-danger: #dc2626;
            --color-danger-hover: #b91c1c;
            --color-info: #0891b2;
            --color-info-hover: #0e7490;

            /* Star rating colors */
            --star-filled: #f59e0b;
            --star-empty: #d1d5db;
        }

        .dark {
            /* Dark mode colors */
            --color-primary: #3b82f6;
            --color-primary-hover: #2563eb;
            --color-success: #10b981;
            --color-success-hover: #059669;
            --color-warning: #f59e0b;
            --color-warning-hover: #d97706;
            --color-danger: #ef4444;
            --color-danger-hover: #dc2626;
            --color-info: #06b6d4;
            --color-info-hover: #0891b2;

            /* Star rating colors for dark mode */
            --star-filled: #fbbf24;
            --star-empty: #4b5563;
        }

        /* Enhanced font family with better language support */
        body {
            font-family: 'Inter', 'Noto Sans Thai', 'Noto Sans Myanmar', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            font-feature-settings: "liga" 1, "calt" 1;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* Language-specific font optimizations */
        [lang="th"],
        .thai-text {
            font-family: 'Noto Sans Thai', 'Inter', sans-serif;
            line-height: 1.75;
        }

        [lang="my"],
        .myanmar-text {
            font-family: 'Noto Sans Myanmar', 'Inter', sans-serif;
            line-height: 1.8;
        }

        [lang="en"],
        .english-text {
            font-family: 'Inter', sans-serif;
            line-height: 1.6;
        }

        /* Smooth theme transitions */
        .theme-transition {
            transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
        }

        /* Enhanced star rating display */
        .star-rating {
            color: var(--star-filled);
            font-size: 1.25rem;
            letter-spacing: 0.05em;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }

        .star-empty {
            color: var(--star-empty);
        }

        /* Modal styling */
        .modal-backdrop {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(4px);
            z-index: 50;
            align-items: center;
            justify-content: center;
        }

        .modal-backdrop.active {
            display: flex;
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in-up {
            animation: fadeInUp 0.3s ease-in-out;
        }

        @keyframes shimmer {
            0% {
                background-position: -1000px 0;
            }

            100% {
                background-position: 1000px 0;
            }
        }

        .loading-shimmer {
            background: linear-gradient(90deg,
                    <?php echo $is_dark ? '#1f2937' : '#f3f4f6'; ?> 0%,
                    <?php echo $is_dark ? '#374151' : '#e5e7eb'; ?> 50%,
                    <?php echo $is_dark ? '#1f2937' : '#f3f4f6'; ?> 100%);
            background-size: 1000px 100%;
            animation: shimmer 2s infinite;
        }

        /* Enhanced button styles with theme support */
        .btn-primary {
            background-color: var(--color-primary);
            transition: all 0.2s ease;
        }

        .btn-primary:hover {
            background-color: var(--color-primary-hover);
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .btn-success {
            background-color: var(--color-success);
        }

        .btn-success:hover {
            background-color: var(--color-success-hover);
        }

        .btn-warning {
            background-color: var(--color-warning);
        }

        .btn-warning:hover {
            background-color: var(--color-warning-hover);
        }

        .btn-danger {
            background-color: var(--color-danger);
        }

        .btn-danger:hover {
            background-color: var(--color-danger-hover);
        }

        /* Enhanced table row hover */
        .table-row-hover {
            transition: all 0.2s ease;
        }

        .table-row-hover:hover {
            <?php if ($is_dark): ?>background-color: rgba(55, 65, 81, 0.5);
            <?php else: ?>background-color: rgba(249, 250, 251, 0.8);
            <?php endif; ?>transform: scale(1.001);
        }

        /* Status badge colors with better contrast */
        .status-badge {
            font-weight: 600;
            font-size: 0.75rem;
            padding: 0.375rem 0.75rem;
            border-radius: 9999px;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }

        <?php if ($is_dark): ?>.status-new {
            background-color: rgba(245, 158, 11, 0.2);
            color: #fbbf24;
            border: 1px solid rgba(245, 158, 11, 0.3);
        }

        .status-progress {
            background-color: rgba(59, 130, 246, 0.2);
            color: #60a5fa;
            border: 1px solid rgba(59, 130, 246, 0.3);
        }

        .status-complete {
            background-color: rgba(16, 185, 129, 0.2);
            color: #34d399;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }

        .status-cancelled {
            background-color: rgba(239, 68, 68, 0.2);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        <?php else: ?>.status-new {
            background-color: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
        }

        .status-progress {
            background-color: #dbeafe;
            color: #1e40af;
            border: 1px solid #bfdbfe;
        }

        .status-complete {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .status-cancelled {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        <?php endif; ?>

        /* Custom scrollbar for better UX */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: <?php echo $is_dark ? '#1f2937' : '#f3f4f6'; ?>;
        }

        ::-webkit-scrollbar-thumb {
            background: <?php echo $is_dark ? '#4b5563' : '#d1d5db'; ?>;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: <?php echo $is_dark ? '#6b7280' : '#9ca3af'; ?>;
        }
    </style>
</head>

<body class="<?php echo $bg_class; ?> <?php echo $text_class; ?> theme-transition" lang="<?php echo $current_lang; ?>">
    <div class="lg:ml-64 min-h-screen">
        <div class="container mx-auto px-4 py-6">

            <!-- Page Header with Gradient -->
            <div class="mb-6 bg-gradient-to-r from-green-600 to-teal-600 rounded-xl shadow-lg p-6 theme-transition">
                <div class="flex items-center justify-between flex-col md:flex-row gap-4">
                    <div class="flex items-center">
                        <svg class="w-10 h-10 text-white mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                        </svg>
                        <div>
                            <h1 class="text-3xl font-bold text-white"><?php echo $t['page_title']; ?></h1>
                            <p class="text-green-100 mt-1">
                                <?php echo $is_officer_ta ? $t['page_subtitle_ta'] : $t['page_subtitle']; ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards with Enhanced Design -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4 mb-6">
                <!-- Total Card -->
                <div class="<?php echo $card_bg; ?> p-5 rounded-xl border <?php echo $border_class; ?> shadow-sm hover:shadow-md theme-transition">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="<?php echo $text_muted; ?> text-sm font-medium mb-1"><?php echo $t['total']; ?></p>
                            <p class="text-3xl font-bold <?php echo $text_class; ?>"><?php echo number_format($stats['total']); ?></p>
                        </div>
                        <div class="bg-blue-100 dark:bg-blue-900/30 p-3 rounded-full">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- New Card -->
                <div class="<?php echo $card_bg; ?> p-5 rounded-xl border <?php echo $border_class; ?> shadow-sm hover:shadow-md theme-transition">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="<?php echo $text_muted; ?> text-sm font-medium mb-1"><?php echo $t['new']; ?></p>
                            <p class="text-3xl font-bold text-yellow-600 dark:text-yellow-400"><?php echo number_format($stats['new']); ?></p>
                        </div>
                        <div class="bg-yellow-100 dark:bg-yellow-900/30 p-3 rounded-full">
                            <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- In Progress Card -->
                <div class="<?php echo $card_bg; ?> p-5 rounded-xl border <?php echo $border_class; ?> shadow-sm hover:shadow-md theme-transition">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="<?php echo $text_muted; ?> text-sm font-medium mb-1"><?php echo $t['in_progress']; ?></p>
                            <p class="text-3xl font-bold text-blue-600 dark:text-blue-400"><?php echo number_format($stats['in_progress']); ?></p>
                        </div>
                        <div class="bg-blue-100 dark:bg-blue-900/30 p-3 rounded-full">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Complete Card -->
                <div class="<?php echo $card_bg; ?> p-5 rounded-xl border <?php echo $border_class; ?> shadow-sm hover:shadow-md theme-transition">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="<?php echo $text_muted; ?> text-sm font-medium mb-1"><?php echo $t['complete']; ?></p>
                            <p class="text-3xl font-bold text-green-600 dark:text-green-400"><?php echo number_format($stats['complete']); ?></p>
                        </div>
                        <div class="bg-green-100 dark:bg-green-900/30 p-3 rounded-full">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Cancelled Card -->
                <div class="<?php echo $card_bg; ?> p-5 rounded-xl border <?php echo $border_class; ?> shadow-sm hover:shadow-md theme-transition">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="<?php echo $text_muted; ?> text-sm font-medium mb-1"><?php echo $t['cancelled']; ?></p>
                            <p class="text-3xl font-bold text-red-600 dark:text-red-400"><?php echo number_format($stats['cancelled']); ?></p>
                        </div>
                        <div class="bg-red-100 dark:bg-red-900/30 p-3 rounded-full">
                            <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Average Rating Card with Enhanced Star Display -->
                <div class="<?php echo $card_bg; ?> p-5 rounded-xl border border-amber-300 dark:border-amber-700 shadow-sm hover:shadow-md theme-transition bg-gradient-to-br from-amber-50 to-orange-50 dark:from-amber-900/20 dark:to-orange-900/20">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="<?php echo $text_secondary; ?> text-sm font-semibold mb-1"><?php echo $t['avg_rating']; ?></p>
                            <?php if ($stats['avg_rating'] > 0): ?>
                                <div class="flex items-center gap-2 mb-1">
                                    <p class="text-3xl font-bold text-amber-600 dark:text-amber-400"><?php echo number_format($stats['avg_rating'], 1); ?></p>
                                    <span class="star-rating text-xl">
                                        <?php
                                        $full_stars = floor($stats['avg_rating']);
                                        $has_half_star = ($stats['avg_rating'] - $full_stars) >= 0.5;
                                        $empty_stars = 5 - ceil($stats['avg_rating']);

                                        echo str_repeat('★', $full_stars);
                                        if ($has_half_star) echo '★';
                                        ?>
                                        <span class="star-empty"><?php echo str_repeat('☆', $empty_stars); ?></span>
                                    </span>
                                </div>
                                <p class="text-xs <?php echo $text_muted; ?>">
                                    <?php echo $stats['total_ratings']; ?> <?php echo $t['rated_requests']; ?>
                                </p>
                            <?php else: ?>
                                <p class="text-2xl font-bold <?php echo $text_muted; ?>">—</p>
                                <p class="text-xs <?php echo $text_muted; ?>"><?php echo $t['no_rating']; ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="bg-amber-100 dark:bg-amber-900/40 p-3 rounded-full">
                            <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters Section with Enhanced Styling -->
            <div class="<?php echo $card_bg; ?> rounded-xl shadow-sm p-6 mb-6 border <?php echo $border_class; ?> theme-transition">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Search -->
                    <div>
                        <label class="block text-sm font-semibold <?php echo $text_class; ?> mb-2"><?php echo $t['search']; ?></label>
                        <input type="text"
                            name="search"
                            value="<?php echo htmlspecialchars($search); ?>"
                            placeholder="<?php echo $t['search_placeholder']; ?>"
                            class="w-full px-4 py-2.5 border rounded-lg <?php echo $input_class; ?> focus:ring-2 focus:ring-blue-500 focus:border-transparent theme-transition">
                    </div>

                    <!-- Status Filter -->
                    <div>
                        <label class="block text-sm font-semibold <?php echo $text_class; ?> mb-2"><?php echo $t['status']; ?></label>
                        <select name="status"
                            class="w-full px-4 py-2.5 border rounded-lg <?php echo $input_class; ?> focus:ring-2 focus:ring-blue-500 focus:border-transparent theme-transition">
                            <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>><?php echo $t['all_status']; ?></option>
                            <option value="New" <?php echo $status_filter === 'New' ? 'selected' : ''; ?>><?php echo $t['new']; ?></option>
                            <option value="In Progress" <?php echo $status_filter === 'In Progress' ? 'selected' : ''; ?>><?php echo $t['in_progress']; ?></option>
                            <option value="Complete" <?php echo $status_filter === 'Complete' ? 'selected' : ''; ?>><?php echo $t['complete']; ?></option>
                            <option value="Cancelled" <?php echo $status_filter === 'Cancelled' ? 'selected' : ''; ?>><?php echo $t['cancelled']; ?></option>
                        </select>
                    </div>

                    <!-- Type Filter -->
                    <div>
                        <label class="block text-sm font-semibold <?php echo $text_class; ?> mb-2"><?php echo $t['request_type']; ?></label>
                        <select name="type"
                            class="w-full px-4 py-2.5 border rounded-lg <?php echo $input_class; ?> focus:ring-2 focus:ring-blue-500 focus:border-transparent theme-transition">
                            <?php if (!$is_officer_ta): ?>
                                <option value="all" <?php echo $type_filter === 'all' ? 'selected' : ''; ?>><?php echo $t['all_types']; ?></option>
                            <?php endif; ?>
                            <?php foreach ($request_types as $table => $labels):
                                $type_label = '';
                                if ($current_lang === 'th') {
                                    $type_label = $labels['label_th'];
                                } elseif ($current_lang === 'en') {
                                    $type_label = $labels['label_en'];
                                } elseif ($current_lang === 'my') {
                                    $type_label = $labels['label_my'];
                                }
                            ?>
                                <option value="<?php echo $table; ?>" <?php echo $type_filter === $table ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($type_label); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Buttons -->
                    <div class="flex items-end space-x-2">
                        <button type="submit"
                            class="flex-1 btn-primary text-white px-6 py-2.5 rounded-lg font-medium">
                            <?php echo $t['filter']; ?>
                        </button>
                        <a href="<?php echo BASE_PATH; ?>/views/admin/request_management.php"
                            class="bg-gray-500 hover:bg-gray-600 dark:bg-gray-600 dark:hover:bg-gray-700 text-white px-4 py-2.5 rounded-lg font-medium theme-transition">
                            <?php echo $t['reset']; ?>
                        </a>
                    </div>
                </form>
            </div>

            <!-- Requests Table with Enhanced Design -->
            <div class="<?php echo $card_bg; ?> rounded-xl shadow-sm border <?php echo $border_class; ?> overflow-hidden theme-transition">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="<?php echo $is_dark ? 'bg-gray-800/80' : 'bg-gray-100'; ?> border-b-2 <?php echo $border_class; ?>">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold <?php echo $text_class; ?> uppercase tracking-wider"><?php echo $t['request_id']; ?></th>
                                <?php if (!$is_officer_ta): ?>
                                    <th class="px-6 py-4 text-left text-xs font-bold <?php echo $text_class; ?> uppercase tracking-wider"><?php echo $t['type']; ?></th>
                                <?php endif; ?>
                                <th class="px-6 py-4 text-left text-xs font-bold <?php echo $text_class; ?> uppercase tracking-wider"><?php echo $t['employee']; ?></th>
                                <th class="px-6 py-4 text-left text-xs font-bold <?php echo $text_class; ?> uppercase tracking-wider"><?php echo $t['employee_name']; ?></th>
                                <th class="px-6 py-4 text-left text-xs font-bold <?php echo $text_class; ?> uppercase tracking-wider"><?php echo $t['created']; ?></th>
                                <th class="px-6 py-4 text-left text-xs font-bold <?php echo $text_class; ?> uppercase tracking-wider"><?php echo $t['status']; ?></th>
                                <th class="px-6 py-4 text-center text-xs font-bold <?php echo $text_class; ?> uppercase tracking-wider"><?php echo $t['rating']; ?></th>
                                <th class="px-6 py-4 text-left text-xs font-bold <?php echo $text_class; ?> uppercase tracking-wider"><?php echo $t['handler']; ?></th>
                                <th class="px-6 py-4 text-center text-xs font-bold <?php echo $text_class; ?> uppercase tracking-wider"><?php echo $t['actions']; ?></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y <?php echo $is_dark ? 'divide-gray-700' : 'divide-gray-200'; ?>">
                            <?php if (empty($all_requests)): ?>
                                <tr>
                                    <td colspan="<?php echo $is_officer_ta ? '8' : '9'; ?>" class="px-6 py-16 text-center">
                                        <svg class="w-20 h-20 mx-auto mb-4 <?php echo $text_muted; ?> opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                        </svg>
                                        <p class="text-lg font-semibold <?php echo $text_secondary; ?> mb-1"><?php echo $t['no_requests']; ?></p>
                                        <p class="text-sm <?php echo $text_muted; ?>"><?php echo $t['try_adjusting']; ?></p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($all_requests as $request):
                                    $type_config = $request_types[$request['request_type_key']] ?? [];
                                    $type_label = '';
                                    if ($current_lang === 'th') {
                                        $type_label = $type_config['label_th'] ?? $request['request_type'];
                                    } elseif ($current_lang === 'en') {
                                        $type_label = $type_config['label_en'] ?? $request['request_type'];
                                    } elseif ($current_lang === 'my') {
                                        $type_label = $type_config['label_my'] ?? $request['request_type'];
                                    }

                                    // Determine status badge class
                                    $status_badge_class = 'status-badge ';
                                    switch ($request['status']) {
                                        case 'New':
                                            $status_badge_class .= 'status-new';
                                            break;
                                        case 'In Progress':
                                            $status_badge_class .= 'status-progress';
                                            break;
                                        case 'Complete':
                                            $status_badge_class .= 'status-complete';
                                            break;
                                        case 'Cancelled':
                                            $status_badge_class .= 'status-cancelled';
                                            break;
                                    }
                                ?>
                                    <tr class="table-row-hover">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="font-mono text-sm font-semibold <?php echo $text_class; ?>">
                                                #<?php echo str_pad($request['request_id'], 5, '0', STR_PAD_LEFT); ?>
                                            </span>
                                        </td>
                                        <?php if (!$is_officer_ta): ?>
                                            <td class="px-6 py-4">
                                                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300 border border-blue-200 dark:border-blue-700">
                                                    <?php echo htmlspecialchars($type_label); ?>
                                                </span>
                                            </td>
                                        <?php endif; ?>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="<?php echo $text_class; ?> font-semibold">
                                                <?php echo htmlspecialchars($request['employee_id']); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="<?php echo $text_secondary; ?> font-medium">
                                                <?php echo htmlspecialchars($request['employee_name'] ?? '-'); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="<?php echo $text_muted; ?> text-sm">
                                                <?php echo date('d M Y, H:i', strtotime($request['created_at'])); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="<?php echo $status_badge_class; ?>">
                                                <?php
                                                $status_map = [
                                                    'th' => ['New' => 'ใหม่', 'In Progress' => 'กำลังดำเนิน', 'Complete' => 'เสร็จสิ้น', 'Cancelled' => 'ยกเลิก'],
                                                    'en' => ['New' => 'New', 'In Progress' => 'In Progress', 'Complete' => 'Complete', 'Cancelled' => 'Cancelled'],
                                                    'my' => ['New' => 'အသစ်', 'In Progress' => 'လုပ်ဆောင်နေ', 'Complete' => 'ပြည့်စုံမည်', 'Cancelled' => 'ပယ်ဖျက်ခြင်း']
                                                ];
                                                $status_label = $status_map[$current_lang][$request['status']] ?? $request['status'];
                                                echo htmlspecialchars($status_label);
                                                ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-center whitespace-nowrap">
                                            <?php if (!empty($request['satisfaction_score']) && $request['satisfaction_score'] > 0): ?>
                                                <div class="flex flex-col items-center">
                                                    <span class="star-rating">
                                                        <?php
                                                        echo str_repeat('★', $request['satisfaction_score']);
                                                        ?>
                                                        <span class="star-empty"><?php echo str_repeat('☆', 5 - $request['satisfaction_score']); ?></span>
                                                    </span>
                                                    <span class="text-xs <?php echo $text_muted; ?> mt-1 font-medium">
                                                        <?php echo $request['satisfaction_score']; ?>/5
                                                    </span>
                                                </div>
                                            <?php else: ?>
                                                <span class="<?php echo $text_muted; ?> text-sm">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php if ($request['handler_id']): ?>
                                                <span class="<?php echo $text_secondary; ?> text-sm font-medium">
                                                    <?php echo htmlspecialchars($request['handler_id']); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="<?php echo $text_muted; ?> text-sm italic">
                                                    <?php echo $t['unassigned']; ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <button onclick="openRequestModal('<?php echo $request['table_name']; ?>', <?php echo $request['request_id']; ?>)"
                                                class="text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 font-semibold text-sm transition-colors">
                                                <?php echo $t['view_details']; ?>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Request Detail Modal -->
    <div id="requestModal" class="modal-backdrop">
        <div class="<?php echo $card_bg; ?> rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto border-2 <?php echo $border_class; ?> m-4 theme-transition">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6 pb-4 border-b <?php echo $border_class; ?>">
                    <h3 class="text-2xl font-bold <?php echo $text_class; ?>"><?php echo $t['request_details']; ?></h3>
                    <button onclick="closeRequestModal()"
                        class="<?php echo $text_muted; ?> hover:<?php echo $text_class; ?> transition-colors">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div id="modalContent" class="animate-fade-in-up">
                    <!-- Content loaded via JavaScript -->
                </div>
            </div>
        </div>
    </div>

    <script>
        const t = <?php echo json_encode($t); ?>;
        const currentLang = '<?php echo $current_lang; ?>';
        const isDark = <?php echo $is_dark ? 'true' : 'false'; ?>;

        const DEFAULT_AVATAR_SVG = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='128' height='128' viewBox='0 0 24 24' fill='none' stroke='%23999' stroke-width='2'%3E%3Cpath d='M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2'/%3E%3Ccircle cx='12' cy='7' r='4'/%3E%3C/svg%3E";

        const statusMap = {
            'th': {
                'New': 'ใหม่',
                'In Progress': 'กำลังดำเนิน',
                'Complete': 'เสร็จสิ้น',
                'Cancelled': 'ยกเลิก'
            },
            'en': {
                'New': 'New',
                'In Progress': 'In Progress',
                'Complete': 'Complete',
                'Cancelled': 'Cancelled'
            },
            'my': {
                'New': 'အသစ်',
                'In Progress': 'လုပ်ဆောင်နေ',
                'Complete': 'ပြည့်စုံမည်',
                'Cancelled': 'ပယ်ဖျက်ခြင်း'
            }
        };

        function openRequestModal(table, requestId) {
            const modal = document.getElementById('requestModal');
            const content = document.getElementById('modalContent');

            content.innerHTML = '<div class="text-center py-8"><div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div><p class="mt-4 <?php echo $text_class; ?>">' + t['loading'] + '</p></div>';
            modal.classList.add('active');

            const basePath = '<?php echo defined("BASE_PATH") ? BASE_PATH : ""; ?>';
            const url = basePath ? `${basePath}/api/admin_get_request_details.php?table=${table}&id=${requestId}` :
                `/api/admin_get_request_details.php?table=${table}&id=${requestId}`;

            fetch(url)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.text();
                })
                .then(text => {
                    try {
                        const data = JSON.parse(text);
                        if (data.success) {
                            content.innerHTML = generateRequestHTML(data.request, table);
                        } else {
                            content.innerHTML = `<div class="text-center py-8"><p class="text-red-600 font-medium">${data.message || t['error_loading']}</p></div>`;
                        }
                    } catch (e) {
                        console.error('JSON parse error:', e);
                        content.innerHTML = `<div class="text-center py-8"><p class="text-red-600 font-medium">Invalid JSON response</p></div>`;
                    }
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                    content.innerHTML = `<div class="text-center py-8"><p class="text-red-600 font-medium">${t['error_loading']}</p></div>`;
                });
        }

        function closeRequestModal() {
            document.getElementById('requestModal').classList.remove('active');
        }

        function generateRequestHTML(request, table) {
            const borderClass = '<?php echo $border_class; ?>';
            const textClass = '<?php echo $text_class; ?>';
            const isDarkMode = isDark;
            const grayTextClass = isDarkMode ? 'text-gray-400' : 'text-gray-600';
            const inputClass = isDarkMode ? 'bg-gray-700 border-gray-600 text-white placeholder-gray-400' : 'bg-white border-gray-300 text-gray-900 placeholder-gray-500';
            const cardBg = isDarkMode ? 'bg-gray-700' : 'bg-gray-50';

            const statusLabel = statusMap[currentLang][request.status] || request.status;

            let employeeName = request.employee_id;
            if (currentLang === 'en' && request.full_name_en) {
                employeeName = request.full_name_en;
            } else if (request.full_name_th) {
                employeeName = request.full_name_th;
            }

            let positionName = '';
            let divisionName = '';

            if (currentLang === 'en') {
                positionName = request.position_name_en || '-';
                divisionName = request.division_name_en || '-';
            } else {
                positionName = request.position_name_th || '-';
                divisionName = request.division_name_th || '-';
            }

            const isCertificateRequest = (table === 'certificate_requests');
            const isIDCardRequest = (table === 'id_card_requests');

            let html = `
                <div class="space-y-6">
                    <!-- Employee Information Section -->
                    <div class="p-4 ${cardBg} rounded-lg border ${borderClass}">
                        <h4 class="font-semibold ${textClass} mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            ${t['employee_info']}
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm font-medium ${grayTextClass}">${t['employee']}</label>
                                <p class="${textClass}">${request.employee_id}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium ${grayTextClass}">${t['employee_name']}</label>
                                <p class="${textClass}">${employeeName}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium ${grayTextClass}">${t['position']}</label>
                                <p class="${textClass}">${positionName}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium ${grayTextClass}">${t['division']}</label>
                                <p class="${textClass}">${divisionName}</p>
                            </div>
                            ${isCertificateRequest ? `
                                <div>
                                    <label class="text-sm font-medium ${grayTextClass}">${t['certificate_type']}</label>
                                    <p class="${textClass}">
                                        ${currentLang === 'en' 
                                            ? (request.cert_type_name_en || '-')
                                            : (request.cert_type_name_th || '-')
                                        }
                                    </p>
                                </div>
                            ` : ''}
                        </div>
                    </div>
                    
                    <!-- Request Information Section -->
                    <div class="p-4 ${cardBg} rounded-lg border ${borderClass}">
                        <h4 class="font-semibold ${textClass} mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            ${t['request_info']}
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm font-medium ${grayTextClass}">${t['request_id']}</label>
                                <p class="${textClass} font-mono">#${String(request.request_id).padStart(5, '0')}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium ${grayTextClass}">${t['status']}</label>
                                <p class="${textClass}">${statusLabel}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium ${grayTextClass}">${t['created']}</label>
                                <p class="${textClass}">${new Date(request.created_at).toLocaleString()}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium ${grayTextClass}">${t['handler']}</label>
                                <p class="${textClass}">${request.handler_id || t['unassigned']}</p>
                            </div>
                        </div>
                    </div>
            `;

            // Customer Satisfaction Section
            if (request.satisfaction_score && request.satisfaction_score > 0) {
                const stars = '★'.repeat(request.satisfaction_score) + '☆'.repeat(5 - request.satisfaction_score);
                html += `
                    <div class="p-4 bg-yellow-50 dark:bg-yellow-900 rounded-lg border border-yellow-200 dark:border-yellow-700">
                        <h4 class="font-semibold ${textClass} mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-yellow-600" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                            </svg>
                            ${t['customer_satisfaction']}
                        </h4>
                        <div class="grid grid-cols-1 gap-3">
                            <div>
                                <label class="text-sm font-medium ${grayTextClass} mb-2 block">${t['satisfaction_score']}</label>
                                <div class="flex items-center gap-3">
                                    <span class="text-3xl text-yellow-500 tracking-wider">${stars}</span>
                                    <span class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">${request.satisfaction_score}/5</span>
                                </div>
                            </div>
                            ${request.satisfaction_feedback ? `
                                <div>
                                    <label class="text-sm font-medium ${grayTextClass} mb-2 block">${t['customer_feedback']}</label>
                                    <p class="${textClass} p-3 bg-white dark:bg-gray-800 rounded border ${borderClass} whitespace-pre-wrap">${request.satisfaction_feedback}</p>
                                </div>
                            ` : `
                                <p class="text-sm ${grayTextClass} italic">${t['no_feedback']}</p>
                            `}
                        </div>
                    </div>
                `;
            }

            // Certificate Request Section with Salary Input
            if (isCertificateRequest) {
                const salaryLabel = currentLang === 'th' ? '💰 ข้อมูลเงินเดือน' :
                    currentLang === 'my' ? '💰 လစာအချက်အလက်' :
                    '💰 Salary Information';

                const salaryPlaceholder = currentLang === 'th' ? 'ระบุเงินเดือน' :
                    currentLang === 'my' ? 'လစာဖြည့်ပါ' :
                    'Enter Salary';

                const saveSalaryBtn = currentLang === 'th' ? '💾 บันทึกข้อมูลเงินเดือน' :
                    currentLang === 'my' ? '💾 လစာအချက်အလက်သိမ်းဆည်းပါ' :
                    '💾 Save Salary Information';

                const certButtonText = currentLang === 'th' ? '📄 สร้างหนังสือรับรอง' :
                    currentLang === 'my' ? '📄 လက်မှတ်ဖန်တီးမည်' :
                    '📄 Generate Certificate';

                const currentSalary = request.base_salary || 0;
                const formId = `salaryForm_${request.request_id}`;

                html += `
                    <div class="p-4 bg-yellow-50 dark:bg-yellow-900 rounded-lg border border-yellow-200 dark:border-yellow-700">
                        <h4 class="font-semibold ${textClass} mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            ${salaryLabel}
                        </h4>
                        
                        <form id="${formId}" onsubmit="updateEmployeeSalary(event, ${request.request_id})">
                            <div class="mb-4">
                                <label class="block text-sm font-medium ${textClass} mb-2">
                                    ${currentLang === 'th' ? 'เงินเดือนสำหรับหนังสือรับรองฉบับนี้ (บาท)' : 
                                      currentLang === 'my' ? 'ဤလက်မှတ်အတွက်လစာ (ကျပ်)' : 
                                      'Salary for this Certificate (THB)'}
                                </label>
                                <input type="number" 
                                       name="base_salary" 
                                       step="0.01" 
                                       min="0"
                                       value="${currentSalary}"
                                       placeholder="${salaryPlaceholder}"
                                       class="w-full px-4 py-2 border rounded-lg ${inputClass} focus:ring-2 focus:ring-yellow-500"
                                       required>
                                <p class="text-xs ${grayTextClass} mt-1">
                                    ${currentLang === 'th' ? '💡 เงินเดือนนี้จะถูกบันทึกเฉพาะคำขอนี้เท่านั้น' : 
                                      currentLang === 'my' ? '💡 ဤလစာကို ဤတောင်းဆိုမှုအတွက်သာသိမ်းဆည်းမည်' : 
                                      '💡 This salary will be saved only for this specific request'}
                                </p>
                            </div>
                            
                            <button type="submit" 
                                    class="w-full bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-3 rounded-lg transition font-medium">
                                ${saveSalaryBtn}
                            </button>
                        </form>
                        
                        ${currentSalary > 0 ? `
                            <div class="mt-3 p-3 bg-green-100 dark:bg-green-800 rounded text-sm ${textClass}">
                                ✅ ${currentLang === 'th' ? 'มีข้อมูลเงินเดือนแล้ว: ' : 
                                     currentLang === 'my' ? 'လစာအချက်အလက်ရှိပြီး: ' : 
                                     'Salary data available: '}
                                <strong>${parseFloat(currentSalary).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})} 
                                ${currentLang === 'th' ? 'บาท' : currentLang === 'my' ? 'ကျပ်' : 'THB'}</strong>
                            </div>
                        ` : `
                            <div class="mt-3 p-3 bg-red-100 dark:bg-red-800 rounded text-sm ${textClass}">
                                ⚠️ ${currentLang === 'th' ? 'กรุณากรอกข้อมูลเงินเดือนก่อนสร้างหนังสือรับรอง' : 
                                      currentLang === 'my' ? 'လက်မှတ်မဖန်တီးမီလစာအချက်အလက်ဖြည့်ပါ' : 
                                      'Please enter salary information before generating certificate'}
                            </div>
                        `}
                    </div>
                    
                    <div class="p-4 bg-blue-50 dark:bg-blue-900 rounded-lg border border-blue-200 dark:border-blue-700">
                        <h4 class="font-semibold ${textClass} mb-3 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            ${certButtonText}
                        </h4>
                        
                        ${currentSalary > 0 ? `
                            <div class="flex gap-2">
                                <button onclick="generateCertificate(${request.request_id}, 'th')" 
                                    class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition font-medium">
                                    ${currentLang === 'th' ? 'สร้างหนังสือรับรอง' : currentLang === 'my' ? 'တည်ထောင်စာအုပ်' : 'Generate Certificate'}
                                </button>
                            </div>
                            ${request.certificate_no ? `
                                <p class="text-sm ${grayTextClass} mt-2 text-center">
                                    ${currentLang === 'th' ? 'เลขที่หนังสือรับรอง' : currentLang === 'my' ? 'လက်မှတ်နံပါတ်' : 'Certificate No.'}: 
                                    <span class="font-mono font-semibold">${request.certificate_no}</span>
                                </p>
                            ` : ''}
                        ` : `
                            <div class="p-4 bg-red-100 dark:bg-red-800 text-red-800 dark:text-red-100 rounded-lg text-center">
                                <svg class="w-12 h-12 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                                <p class="font-medium">
                                    ${currentLang === 'th' ? 'กรุณากรอกข้อมูลเงินเดือนก่อน' : 
                                      currentLang === 'my' ? 'လစာအချက်အလက်ဖြည့်ပါ' : 
                                      'Please enter salary information first'}
                                </p>
                            </div>
                        `}
                    </div>
                `;
            }

            // ID Card Request Section with Photo Upload
            if (isIDCardRequest) {
                const idCardButtonText = currentLang === 'th' ? '🆔 สร้างบัตรพนักงาน' :
                    currentLang === 'my' ? '🆔 အိုင်ဒီကဒ်ဖန်တီးမည်' :
                    '🆔 Generate ID Card';

                const uploadPhotoText = currentLang === 'th' ? '📸 อัพโหลดรูปถ่าย' :
                    currentLang === 'my' ? '📸 ဓာတ်ပုံတင်မည်' :
                    '📸 Upload Photo';

                const changePhotoText = currentLang === 'th' ? '🔄 เปลี่ยนรูปถ่าย' :
                    currentLang === 'my' ? '🔄 ဓာတ်ပုံပြောင်းမည်' :
                    '🔄 Change Photo';

                const photoUrl = request.profile_pic_url || DEFAULT_AVATAR_SVG;
                const hasPhoto = !!request.profile_pic_url;

                html += `
                    <div class="p-4 bg-purple-50 dark:bg-purple-900 rounded-lg border border-purple-200 dark:border-purple-700">
                        <h4 class="font-semibold ${textClass} mb-3 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path>
                            </svg>
                            ${t['id_card_info']}
                        </h4>
                        
                        <div class="mb-4 flex flex-col items-center">
                            <div class="relative mb-3">
                                <img src="../../${photoUrl}" 
                                     alt="${employeeName}" 
                                     id="photoPreview_${request.request_id}"
                                     class="w-32 h-32 object-cover rounded-lg border-4 border-white dark:border-gray-700 shadow-lg"
                                     onerror="this.src='${DEFAULT_AVATAR_SVG}'">
                                ${hasPhoto ? `
                                    <div class="absolute -top-2 -right-2 bg-green-500 text-white rounded-full p-2">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                ` : ''}
                            </div>
                            
                            <form id="photoUploadForm_${request.request_id}" class="w-full" onsubmit="uploadEmployeePhoto(event, '${request.employee_id}', ${request.request_id})">
                                <input type="file" 
                                       id="photoInput_${request.request_id}" 
                                       name="photo" 
                                       accept="image/jpeg,image/jpg,image/png,image/gif,image/webp"
                                       class="hidden"
                                       onchange="previewPhoto(event, ${request.request_id})">
                                <label for="photoInput_${request.request_id}" 
                                       class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition font-medium cursor-pointer flex items-center justify-center gap-2 mb-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    ${hasPhoto ? changePhotoText : uploadPhotoText}
                                </label>
                                <p class="text-xs ${grayTextClass} text-center mb-2">
                                    ${t['max_5mb']} • JPG, PNG, GIF, WebP
                                </p>
                                <button type="submit" 
                                        id="uploadBtn_${request.request_id}"
                                        class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition font-medium hidden">
                                    💾 ${t['upload_photo']}
                                </button>
                            </form>
                        </div>
                        
                        ${hasPhoto ? `
                            <button onclick="generateIDCard(${request.request_id}, '${request.employee_id}')" 
                                class="w-full bg-purple-600 hover:bg-purple-700 text-white px-4 py-3 rounded-lg transition font-medium flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                                </svg>
                                ${idCardButtonText}
                            </button>
                        ` : `
                            <div class="p-3 bg-yellow-100 dark:bg-yellow-800 text-yellow-800 dark:text-yellow-100 rounded-lg text-center text-sm">
                                ⚠️ ${currentLang === 'th' ? 'อัพโหลดรูปถ่ายพนักงานเพื่อสร้างบัตร' : 
                                      currentLang === 'my' ? 'ကဒ်ဖန်တီးရန်ဓာတ်ပုံတင်ပါ' : 
                                      'Upload photo to generate ID card'}
                            </div>
                        `}
                    </div>
                `;
            }

            // Status Update Section
            html += `
                    <div class="pt-4 border-t ${borderClass}">
                        <h4 class="font-semibold ${textClass} mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            ${t['status_update']}
                        </h4>
                        <form onsubmit="updateRequestStatus(event, '${table}', ${request.request_id})">
                            <select name="status" class="w-full px-4 py-2 border rounded-lg mb-3 ${inputClass} focus:ring-2 focus:ring-blue-500">
                                <option value="New" ${request.status === 'New' ? 'selected' : ''}>${statusMap[currentLang]['New']}</option>
                                <option value="In Progress" ${request.status === 'In Progress' ? 'selected' : ''}>${statusMap[currentLang]['In Progress']}</option>
                                <option value="Complete" ${request.status === 'Complete' ? 'selected' : ''}>${statusMap[currentLang]['Complete']}</option>
                                <option value="Cancelled" ${request.status === 'Cancelled' ? 'selected' : ''}>${statusMap[currentLang]['Cancelled']}</option>
                            </select>
                            
                            <textarea name="remarks" placeholder="${t['handler_remarks']} ${t['optional']}" rows="3"
                                class="w-full px-4 py-2 border rounded-lg mb-3 ${inputClass} focus:ring-2 focus:ring-blue-500">${request.handler_remarks || ''}</textarea>
                            
                            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg transition font-medium">
                                ${t['update_request']}
                            </button>
                        </form>
                    </div>
                </div>
            `;

            return html;
        }

        // ===== JAVASCRIPT FUNCTIONS FOR PHOTO UPLOAD, SALARY, AND GENERATION =====

        function previewPhoto(event, requestId) {
            const file = event.target.files[0];
            if (!file) return;

            if (file.size > 5 * 1024 * 1024) {
                const message = {
                    'th': 'ไฟล์ใหญ่เกิน 5MB',
                    'en': 'File size exceeds 5MB',
                    'my': 'ဖိုင်အရွယ်အစား 5MB ကျော်လွန်သည်'
                };
                showToast(message[currentLang] || message['th'], 'error');
                event.target.value = '';
                return;
            }

            if (!file.type.match('image/(jpeg|jpg|png|gif|webp)')) {
                const message = {
                    'th': 'กรุณาเลือกไฟล์รูปภาพ (JPG, PNG, GIF)',
                    'en': 'Please select an image file (JPG, PNG, GIF)',
                    'my': 'ဓာတ်ပုံဖိုင်တစ်ခုကိုရွေးချယ်ပါ (JPG, PNG, GIF)'
                };
                showToast(message[currentLang] || message['th'], 'error');
                event.target.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                const imgId = `photoPreview_${requestId}`;
                const img = document.getElementById(imgId);
                if (img) {
                    img.src = e.target.result;
                }
            };
            reader.readAsDataURL(file);

            const uploadBtnId = `uploadBtn_${requestId}`;
            const uploadBtn = document.getElementById(uploadBtnId);
            if (uploadBtn) {
                uploadBtn.classList.remove('hidden');
            }
        }

        function uploadEmployeePhoto(event, employeeId, requestId) {
            event.preventDefault();

            const fileInputId = `photoInput_${requestId}`;
            const fileInput = document.getElementById(fileInputId);
            const file = fileInput.files[0];

            if (!file) {
                const message = {
                    'th': 'กรุณาเลือกรูปภาพ',
                    'en': 'Please select an image',
                    'my': 'ဓာတ်ပုံတစ်ခုကိုရွေးချယ်ပါ'
                };
                showToast(message[currentLang] || message['th'], 'error');
                return;
            }

            const formData = new FormData();
            formData.append('photo', file);
            formData.append('employee_id', employeeId);

            const uploadBtnId = `uploadBtn_${requestId}`;
            const uploadBtn = document.getElementById(uploadBtnId);
            const originalText = uploadBtn.innerHTML;
            uploadBtn.disabled = true;
            uploadBtn.innerHTML = '<span class="inline-block animate-spin mr-2">⏳</span> ' + t['uploading'];

            const basePath = '<?php echo defined("BASE_PATH") ? BASE_PATH : ""; ?>';
            const url = basePath ? `${basePath}/api/upload_employee_photo.php` :
                `/api/upload_employee_photo.php`;

            fetch(url, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        showToast(t['photo_uploaded'], 'success');

                        const imgId = `photoPreview_${requestId}`;
                        const img = document.getElementById(imgId);
                        if (img && result.data && result.data.photo_url) {
                            img.src = result.data.photo_url;
                        }

                        uploadBtn.classList.add('hidden');

                        setTimeout(() => {
                            closeRequestModal();
                            openRequestModal('id_card_requests', requestId);
                        }, 1000);
                    } else {
                        showToast(t['upload_failed'] + ': ' + (result.message || 'Unknown error'), 'error');
                        uploadBtn.disabled = false;
                        uploadBtn.innerHTML = originalText;
                    }
                })
                .catch(error => {
                    showToast(t['upload_failed'] + ': ' + error.message, 'error');
                    uploadBtn.disabled = false;
                    uploadBtn.innerHTML = originalText;
                });
        }

        function updateEmployeeSalary(event, requestId) {
            event.preventDefault();

            const formData = new FormData(event.target);
            const data = {
                request_id: parseInt(requestId),
                base_salary: parseFloat(formData.get('base_salary'))
            };

            const submitBtn = event.target.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="inline-block animate-spin mr-2">⏳</span> ' +
                (currentLang === 'th' ? 'กำลังบันทึก...' :
                    currentLang === 'my' ? 'သိမ်းဆည်းနေသည်...' :
                    'Saving...');

            const basePath = '<?php echo defined("BASE_PATH") ? BASE_PATH : ""; ?>';
            const url = basePath ? `${basePath}/api/update_certificate_salary.php` :
                `/api/update_certificate_salary.php`;

            fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        const successMessage = {
                            'th': 'บันทึกข้อมูลเงินเดือนเรียบร้อยแล้ว',
                            'en': 'Salary information saved successfully',
                            'my': 'လစာအချက်အလက်သိမ်းဆည်းပြီးပါပြီ'
                        };
                        showToast(successMessage[currentLang] || successMessage['th'], 'success');
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showToast(result.message || 'Error saving salary information', 'error');
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    }
                })
                .catch(error => {
                    const errorMessage = {
                        'th': 'เกิดข้อผิดพลาด: ',
                        'en': 'Error: ',
                        'my': 'အမှားအယွင်း: '
                    };
                    showToast((errorMessage[currentLang] || errorMessage['th']) + error.message, 'error');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                });
        }

        function generateCertificate(requestId, language) {
            const basePath = '<?php echo defined("BASE_PATH") ? BASE_PATH : ""; ?>';
            const url = basePath ? `${basePath}/api/generate_certificate.php?request_id=${requestId}&lang=${language}` :
                `/api/generate_certificate.php?request_id=${requestId}&lang=${language}`;

            window.open(url, '_blank');

            const langMessage = {
                'th': 'กำลังเปิดหนังสือรับรองในหน้าต่างใหม่...',
                'en': 'Opening certificate in new window...',
                'my': 'လက်မှတ်ကို ဝင်းဒိုးအသစ်တွင်ဖွင့်နေသည်...'
            };
            showToast(langMessage[currentLang] || langMessage['th'], 'info');
        }

        function generateIDCard(requestId, employeeId) {
            const basePath = '<?php echo defined("BASE_PATH") ? BASE_PATH : ""; ?>';
            const url = basePath ? `${basePath}/api/generate_id_card.php?request_id=${requestId}&employee_id=${employeeId}` :
                `/api/generate_id_card.php?request_id=${requestId}&employee_id=${employeeId}`;

            window.open(url, '_blank');

            const langMessage = {
                'th': 'กำลังเปิดบัตรพนักงานในหน้าต่างใหม่...',
                'en': 'Opening ID card in new window...',
                'my': 'အိုင်ဒီကဒ်ကို ဝင်းဒိုးအသစ်တွင်ဖွင့်နေသည်...'
            };
            showToast(langMessage[currentLang] || langMessage['th'], 'info');
        }

        function updateRequestStatus(event, table, requestId) {
            event.preventDefault();

            const formData = new FormData(event.target);
            const data = {
                table: table,
                request_id: requestId,
                status: formData.get('status'),
                handler_remarks: formData.get('remarks')
            };

            const submitBtn = event.target.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="inline-block animate-spin mr-2">⏳</span> ' + t['updating'];

            fetch('<?php echo BASE_PATH; ?>/api/update_request_status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        showToast(t['updated_successfully'], 'success');
                        closeRequestModal();
                        setTimeout(() => location.reload(), 500);
                    } else {
                        showToast(t['update_error'] + (result.message || 'Unknown error'), 'error');
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    }
                })
                .catch(error => {
                    showToast(t['failed_update'] + error.message, 'error');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                });
        }

        function showToast(message, type = 'info') {
            const bgColor = type === 'success' ? 'bg-green-500' : (type === 'error' ? 'bg-red-500' : 'bg-blue-500');
            const toast = document.createElement('div');
            toast.className = `fixed bottom-6 right-6 ${bgColor} text-white px-6 py-4 rounded-lg shadow-lg z-50 animate-fade-in-up`;
            toast.textContent = message;
            document.body.appendChild(toast);

            setTimeout(() => toast.remove(), 3000);
        }

        // Close modal on ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeRequestModal();
        });

        // Close modal on outside click
        document.getElementById('requestModal').addEventListener('click', function(e) {
            if (e.target === this) closeRequestModal();
        });
    </script>

    <?php include __DIR__ . '/../../includes/footer.php'; ?>
</body>

</html>