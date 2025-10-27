<?php
/**
 * Request Management Page
 * Supports: Thai (ไทย), English (EN), Myanmar (မြန်မာ)
 * Features: Multi-language UI, Dark Mode, Mobile Responsive
 * Admin/Officer only - Manage all service requests
 */

require_once __DIR__ . '/../../config/db_config.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../db/Localization.php';

// Require admin or officer role
AuthController::requireRole(['admin', 'officer']);

// Get current settings from session
$current_lang = $_SESSION['language'] ?? 'th';
$theme_mode = $_SESSION['theme_mode'] ?? 'light';
$is_dark = ($theme_mode === 'dark');
$user_id = $_SESSION['user_id'] ?? '';

// Theme colors based on dark mode
$card_bg = $is_dark ? 'bg-gray-800' : 'bg-white';
$text_class = $is_dark ? 'text-white' : 'text-gray-900';
$bg_class = $is_dark ? 'bg-gray-900' : 'bg-gray-50';
$border_class = $is_dark ? 'border-gray-700' : 'border-gray-200';
$input_class = $is_dark ? 'bg-gray-700 border-gray-600 text-white placeholder-gray-400' : 'bg-white border-gray-300 text-gray-900 placeholder-gray-500';

// Multi-language translations
$translations = [
    'th' => [
        'page_title' => 'จัดการคำขอ',
        'page_subtitle' => 'ตรวจสอบและจัดการคำขอบริการของพนักงานทั้งหมด',
        'total' => 'รวม',
        'new' => 'ใหม่',
        'in_progress' => 'กำลังดำเนิน',
        'complete' => 'เสร็จสิ้น',
        'cancelled' => 'ยกเลิก',
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
        'created' => 'สร้างเมื่อ',
        'handler' => 'ผู้ดำเนิน',
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
        'leave_request' => 'คำขอใบลา',
        'certificate_request' => 'คำขอหนังสือรับรอง',
        'id_card_request' => 'คำขอทำบัตรพนักงาน',
        'shuttle_bus_request' => 'คำขอขึ้นรถรับส่ง',
        'locker_request' => 'คำขอใช้งานตู้ล็อกเกอร์',
        'supplies_request' => 'คำขอเบิกอุปกรณ์',
        'skill_test_request' => 'คำขอสอบทักษะ',
        'document_submission' => 'ลงชื่อส่งเอกสาร',
        'optional' => '(ไม่บังคับ)',
        'updating' => 'กำลังอัปเดต...',
    ],
    'en' => [
        'page_title' => 'Request Management',
        'page_subtitle' => 'Review and manage all employee service requests',
        'total' => 'Total',
        'new' => 'New',
        'in_progress' => 'In Progress',
        'complete' => 'Complete',
        'cancelled' => 'Cancelled',
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
        'created' => 'Created',
        'handler' => 'Handler',
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
        'leave_request' => 'Leave Request',
        'certificate_request' => 'Certificate Request',
        'id_card_request' => 'ID Card Request',
        'shuttle_bus_request' => 'Shuttle Bus Request',
        'locker_request' => 'Locker Request',
        'supplies_request' => 'Supplies Request',
        'skill_test_request' => 'Skill Test Request',
        'document_submission' => 'Document Submission',
        'optional' => '(Optional)',
        'updating' => 'Updating...',
    ],
    'my' => [
        'page_title' => 'တောင်းခံမှုစီမံခန့်ခွဲမှု',
        'page_subtitle' => 'အလုပ်သမားဝန်ဆောင်မှုတောင်းခံမှုအားလုံးကိုပြန်လည်သုံးသပ်ခြင်းနှင့်စီမံခန့်ခွဲမည်',
        'total' => 'စုစုပေါင်း',
        'new' => 'အသစ်',
        'in_progress' => 'လုပ်ဆောင်နေ',
        'complete' => 'ပြည့်စုံမည်',
        'cancelled' => 'ပယ်ဖျက်ခြင်း',
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
        'created' => 'ဖန်တီးသည်',
        'handler' => 'အကျင့်တည်ဝတ်ပြုသူ',
        'actions' => 'အရቀວမ်များ',
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
        'leave_request' => 'အငြိုးပြုစုတောင်းခံမှု',
        'certificate_request' => 'လက်မှတ်တောင်းခံမှု',
        'id_card_request' => 'အိုင်ဒီကဒ်တောင်းခံမှု',
        'shuttle_bus_request' => 'ကားရီးယားတောင်းခံမှု',
        'locker_request' => 'အိတ်ဆောင်တင်သွင်းမှုတောင်းခံမှု',
        'supplies_request' => 'ပရိယာယ်တောင်းခံမှု',
        'skill_test_request' => 'အရည်အချင်းစမ်းသပ်မှုတောင်းခံမှု',
        'document_submission' => 'စာ類တင်သွင်းမှု',
        'optional' => '(အကြိုက်ရှိသည့်)',
        'updating' => 'အချက်အလက်အသစ်ပြုလုပ်နေ...',
    ]
];

// Get current language strings
$t = $translations[$current_lang] ?? $translations['th'];

$page_title = $t['page_title'];
ensure_session_started();

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
    $where_conditions[] = "status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

if (!empty($search)) {
    $where_conditions[] = "(employee_id LIKE ? OR employee_name LIKE ?)";
    $search_term = '%' . $search . '%';
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= 'ss';
}

$where_sql = implode(' AND ', $where_conditions);

// Function to get requests from a table
function getRequests($conn, $table, $type_name, $type_key, $where_sql, $params, $types, $offset, $per_page) {
    // Determine the primary key column name
    $id_column = ($table === 'document_submissions') ? 'submission_id' : 'request_id';
    
    $sql = "SELECT 
        $id_column as request_id,
        employee_id,
        '$type_name' as request_type,
        '$type_key' as request_type_key,
        status,
        created_at,
        handler_id,
        handler_remarks,
        satisfaction_score
    FROM $table 
    WHERE $where_sql 
    ORDER BY created_at DESC 
    LIMIT ? OFFSET ?";
    
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $all_params = array_merge($params, [$per_page, $offset]);
        $all_types = $types . 'ii';
        $stmt->bind_param($all_types, ...$all_params);
    } else {
        $stmt->bind_param('ii', $per_page, $offset);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $requests = [];
    while ($row = $result->fetch_assoc()) {
        $row['table_name'] = $table;
        $requests[] = $row;
    }
    
    $stmt->close();
    return $requests;
}

// Request types configuration
$request_types = [
    'leave_requests' => ['label_en' => 'Leave Request', 'label_th' => 'คำขอใบลา', 'label_my' => 'အငြိုးပြုစုတောင်းခံမှု'],
    'certificate_requests' => ['label_en' => 'Certificate Request', 'label_th' => 'คำขอหนังสือรับรอง', 'label_my' => 'လက်မှတ်တောင်းခံမှု'],
    'id_card_requests' => ['label_en' => 'ID Card Request', 'label_th' => 'คำขอทำบัตรพนักงาน', 'label_my' => 'အိုင်ဒီကဒ်တောင်းခံမှု'],
    'shuttle_bus_requests' => ['label_en' => 'Shuttle Bus Request', 'label_th' => 'คำขอขึ้นรถรับส่ง', 'label_my' => 'ကားရီးယားတောင်းခံမှု'],
    'locker_requests' => ['label_en' => 'Locker Request', 'label_th' => 'คำขอใช้งานตู้ล็อกเกอร์', 'label_my' => 'အိတ်ဆောင်တင်သွင်းမှုတောင်းခံမှု'],
    'supplies_requests' => ['label_en' => 'Supplies Request', 'label_th' => 'คำขอเบิกอุปกรณ์', 'label_my' => 'ပရိယာယ်တောင်းခံမှု'],
    'skill_test_requests' => ['label_en' => 'Skill Test Request', 'label_th' => 'คำขอสอบทักษะ', 'label_my' => 'အရည်အချင်းစမ်းသပ်မှုတောင်းခံမှု'],
    'document_submissions' => ['label_en' => 'Document Submission', 'label_th' => 'ลงชื่อส่งเอกสาร', 'label_my' => 'စာ類တင်သွင်းမှု']
];

// Get all requests based on type filter
$all_requests = [];

if ($type_filter === 'all') {
    foreach ($request_types as $table => $labels) {
        $type_name = $labels['label_en'];
        $requests = getRequests($conn, $table, $type_name, $table, $where_sql, $params, $types, 0, $per_page);
        $all_requests = array_merge($all_requests, $requests);
    }
} else {
    if (isset($request_types[$type_filter])) {
        $labels = $request_types[$type_filter];
        $type_name = $labels['label_en'];
        $all_requests = getRequests($conn, $type_filter, $type_name, $type_filter, $where_sql, $params, $types, $offset, $per_page);
    }
}

// Sort by created_at DESC
usort($all_requests, function($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});

// Limit to page size
$all_requests = array_slice($all_requests, 0, $per_page);

// Get statistics
$stats = [
    'total' => 0,
    'new' => 0,
    'in_progress' => 0,
    'complete' => 0,
    'cancelled' => 0
];

foreach ($request_types as $table => $labels) {
    $result = $conn->query("SELECT status, COUNT(*) as count FROM $table GROUP BY status");
    while ($row = $result->fetch_assoc()) {
        $stats['total'] += $row['count'];
        $status_key = strtolower(str_replace(' ', '_', $row['status']));
        if (isset($stats[$status_key])) {
            $stats[$status_key] += $row['count'];
        }
    }
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
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .theme-transition {
            transition: all 0.3s ease;
        }
        .modal-backdrop {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 50;
            align-items: center;
            justify-content: center;
        }
        .modal-backdrop.active {
            display: flex;
        }
    </style>
</head>
<body class="<?php echo $bg_class; ?> <?php echo $text_class; ?> theme-transition">
    <div class="lg:ml-64 min-h-screen">
        <div class="container mx-auto px-4 py-6">
            
            <!-- Page Header -->
            <div class="mb-6 bg-gradient-to-r from-green-600 to-teal-600 rounded-lg shadow-lg p-6">
                <div class="flex items-center justify-between flex-col md:flex-row gap-4">
                    <div class="flex items-center">
                        <svg class="w-10 h-10 text-white mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                        </svg>
                        <div>
                            <h1 class="text-3xl font-bold text-white"><?php echo $t['page_title']; ?></h1>
                            <p class="text-green-100 mt-1"><?php echo $t['page_subtitle']; ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
                <div class="<?php echo $card_bg; ?> p-4 rounded-lg border <?php echo $border_class; ?> shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="<?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?> text-sm"><?php echo $t['total']; ?></p>
                            <p class="text-2xl font-bold <?php echo $text_class; ?>"><?php echo number_format($stats['total']); ?></p>
                        </div>
                        <div class="bg-blue-100 dark:bg-blue-900 p-3 rounded-full">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="<?php echo $card_bg; ?> p-4 rounded-lg border <?php echo $border_class; ?> shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="<?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?> text-sm"><?php echo $t['new']; ?></p>
                            <p class="text-2xl font-bold text-yellow-600"><?php echo number_format($stats['new']); ?></p>
                        </div>
                        <div class="bg-yellow-100 dark:bg-yellow-900 p-3 rounded-full">
                            <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="<?php echo $card_bg; ?> p-4 rounded-lg border <?php echo $border_class; ?> shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="<?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?> text-sm"><?php echo $t['in_progress']; ?></p>
                            <p class="text-2xl font-bold text-blue-600"><?php echo number_format($stats['in_progress']); ?></p>
                        </div>
                        <div class="bg-blue-100 dark:bg-blue-900 p-3 rounded-full">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="<?php echo $card_bg; ?> p-4 rounded-lg border <?php echo $border_class; ?> shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="<?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?> text-sm"><?php echo $t['complete']; ?></p>
                            <p class="text-2xl font-bold text-green-600"><?php echo number_format($stats['complete']); ?></p>
                        </div>
                        <div class="bg-green-100 dark:bg-green-900 p-3 rounded-full">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="<?php echo $card_bg; ?> p-4 rounded-lg border <?php echo $border_class; ?> shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="<?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?> text-sm"><?php echo $t['cancelled']; ?></p>
                            <p class="text-2xl font-bold text-red-600"><?php echo number_format($stats['cancelled']); ?></p>
                        </div>
                        <div class="bg-red-100 dark:bg-red-900 p-3 rounded-full">
                            <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="<?php echo $card_bg; ?> rounded-lg shadow-sm p-6 mb-6 border <?php echo $border_class; ?>">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Search -->
                    <div>
                        <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2"><?php echo $t['search']; ?></label>
                        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                            placeholder="<?php echo $t['search_placeholder']; ?>"
                            class="w-full px-4 py-2 border rounded-lg <?php echo $input_class; ?> focus:ring-2 focus:ring-blue-500">
                    </div>

                    <!-- Status Filter -->
                    <div>
                        <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2"><?php echo $t['status']; ?></label>
                        <select name="status" class="w-full px-4 py-2 border rounded-lg <?php echo $input_class; ?> focus:ring-2 focus:ring-blue-500">
                            <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>><?php echo $t['all_status']; ?></option>
                            <option value="New" <?php echo $status_filter === 'New' ? 'selected' : ''; ?>><?php echo $t['new']; ?></option>
                            <option value="In Progress" <?php echo $status_filter === 'In Progress' ? 'selected' : ''; ?>><?php echo $t['in_progress']; ?></option>
                            <option value="Complete" <?php echo $status_filter === 'Complete' ? 'selected' : ''; ?>><?php echo $t['complete']; ?></option>
                            <option value="Cancelled" <?php echo $status_filter === 'Cancelled' ? 'selected' : ''; ?>><?php echo $t['cancelled']; ?></option>
                        </select>
                    </div>

                    <!-- Type Filter -->
                    <div>
                        <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2"><?php echo $t['request_type']; ?></label>
                        <select name="type" class="w-full px-4 py-2 border rounded-lg <?php echo $input_class; ?> focus:ring-2 focus:ring-blue-500">
                            <option value="all" <?php echo $type_filter === 'all' ? 'selected' : ''; ?>><?php echo $t['all_types']; ?></option>
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
                        <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition">
                            <?php echo $t['filter']; ?>
                        </button>
                        <a href="<?php echo BASE_PATH; ?>/views/admin/request_management.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition">
                            <?php echo $t['reset']; ?>
                        </a>
                    </div>
                </form>
            </div>

            <!-- Requests Table -->
            <div class="<?php echo $card_bg; ?> rounded-lg shadow-sm border <?php echo $border_class; ?> overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="<?php echo $is_dark ? 'bg-gray-800' : 'bg-gray-50'; ?>">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold <?php echo $text_class; ?> uppercase tracking-wider"><?php echo $t['request_id']; ?></th>
                                <th class="px-6 py-4 text-left text-xs font-semibold <?php echo $text_class; ?> uppercase tracking-wider"><?php echo $t['type']; ?></th>
                                <th class="px-6 py-4 text-left text-xs font-semibold <?php echo $text_class; ?> uppercase tracking-wider"><?php echo $t['employee']; ?></th>
                                <th class="px-6 py-4 text-left text-xs font-semibold <?php echo $text_class; ?> uppercase tracking-wider"><?php echo $t['created']; ?></th>
                                <th class="px-6 py-4 text-left text-xs font-semibold <?php echo $text_class; ?> uppercase tracking-wider"><?php echo $t['status']; ?></th>
                                <th class="px-6 py-4 text-left text-xs font-semibold <?php echo $text_class; ?> uppercase tracking-wider"><?php echo $t['handler']; ?></th>
                                <th class="px-6 py-4 text-center text-xs font-semibold <?php echo $text_class; ?> uppercase tracking-wider"><?php echo $t['actions']; ?></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y <?php echo $is_dark ? 'divide-gray-700' : 'divide-gray-200'; ?>">
                            <?php if (empty($all_requests)): ?>
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?>">
                                        <svg class="w-16 h-16 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                        </svg>
                                        <p class="text-lg font-medium"><?php echo $t['no_requests']; ?></p>
                                        <p class="text-sm mt-1"><?php echo $t['try_adjusting']; ?></p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($all_requests as $request): 
                                    // Get language-specific type label
                                    $type_config = $request_types[$request['request_type_key']] ?? [];
                                    $type_label = '';
                                    if ($current_lang === 'th') {
                                        $type_label = $type_config['label_th'] ?? $request['request_type'];
                                    } elseif ($current_lang === 'en') {
                                        $type_label = $type_config['label_en'] ?? $request['request_type'];
                                    } elseif ($current_lang === 'my') {
                                        $type_label = $type_config['label_my'] ?? $request['request_type'];
                                    }
                                ?>
                                    <tr class="hover:<?php echo $is_dark ? 'bg-gray-800' : 'bg-gray-50'; ?> transition">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="font-mono text-sm <?php echo $text_class; ?>">
                                                #<?php echo str_pad($request['request_id'], 5, '0', STR_PAD_LEFT); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                                <?php echo htmlspecialchars($type_label); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="<?php echo $text_class; ?> font-medium">
                                                <?php echo htmlspecialchars($request['employee_id']); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="<?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?> text-sm">
                                                <?php echo date('d M Y, H:i', strtotime($request['created_at'])); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <?php
                                            $status_colors = [
                                                'New' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                                                'In Progress' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                                                'Complete' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                                'Cancelled' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
                                            ];
                                            ?>
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium <?php echo $status_colors[$request['status']] ?? ''; ?>">
                                                <?php 
                                                // Get status in current language
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
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php if ($request['handler_id']): ?>
                                                <span class="<?php echo $text_class; ?> text-sm">
                                                    <?php echo htmlspecialchars($request['handler_id']); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="<?php echo $is_dark ? 'text-gray-500' : 'text-gray-400'; ?> text-sm italic">
                                                    <?php echo $t['unassigned']; ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <button onclick="openRequestModal('<?php echo $request['table_name']; ?>', <?php echo $request['request_id']; ?>)" 
                                                class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 font-medium text-sm">
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
        <div class="<?php echo $card_bg; ?> rounded-xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto border <?php echo $border_class; ?> m-4">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-bold <?php echo $text_class; ?>"><?php echo $t['request_details']; ?></h3>
                    <button onclick="closeRequestModal()" class="<?php echo $is_dark ? 'text-gray-400 hover:text-white' : 'text-gray-500 hover:text-gray-700'; ?>">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div id="modalContent">
                    <!-- Content loaded via JavaScript -->
                </div>
            </div>
        </div>
    </div>

    <script>
        const t = <?php echo json_encode($t); ?>;
        const currentLang = '<?php echo $current_lang; ?>';
        const isDark = <?php echo $is_dark ? 'true' : 'false'; ?>;
        const statusMap = {
            'th': {'New': 'ใหม่', 'In Progress': 'กำลังดำเนิน', 'Complete': 'เสร็จสิ้น', 'Cancelled': 'ยกเลิก'},
            'en': {'New': 'New', 'In Progress': 'In Progress', 'Complete': 'Complete', 'Cancelled': 'Cancelled'},
            'my': {'New': 'အသစ်', 'In Progress': 'လုပ်ဆောင်နေ', 'Complete': 'ပြည့်စုံမည်', 'Cancelled': 'ပယ်ဖျက်ခြင်း'}
        };

        function openRequestModal(table, requestId) {
            const modal = document.getElementById('requestModal');
            const content = document.getElementById('modalContent');
            
            // Show loading
            content.innerHTML = '<div class="text-center py-8"><div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div><p class="mt-4 <?php echo $text_class; ?>">' + t['loading'] + '</p></div>';
            modal.classList.add('active');
            
            // Fetch request details
            const url = `<?php echo BASE_PATH; ?>/api/admin_get_request_details.php?table=${table}&id=${requestId}`;
            
            fetch(url)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        content.innerHTML = generateRequestHTML(data.request, table);
                    } else {
                        content.innerHTML = `<div class="text-center py-8"><p class="text-red-600 font-medium">${data.message || t['error_loading']}</p><button onclick="closeRequestModal()" class="mt-4 px-6 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">${t['close']}</button></div>`;
                    }
                })
                .catch(error => {
                    content.innerHTML = `<div class="text-center py-8"><p class="text-red-600 font-medium">${t['error_loading']}</p><p class="text-sm <?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?> mt-2">${error.message}</p><button onclick="closeRequestModal()" class="mt-4 px-6 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">${t['close']}</button></div>`;
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
            const inputClass = isDarkMode ? 'bg-gray-700 border-gray-600 text-white' : 'bg-white border-gray-300';
            
            const statusLabel = statusMap[currentLang][request.status] || request.status;
            
            let html = `
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium ${grayTextClass}">${t['request_id']}</label>
                            <p class="${textClass} font-mono">#${String(request.request_id).padStart(5, '0')}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium ${grayTextClass}">${t['status']}</label>
                            <p class="${textClass}">${statusLabel}</p>
                        </div>
                    </div>
                    
                    <div>
                        <label class="text-sm font-medium ${grayTextClass}">${t['employee']}</label>
                        <p class="${textClass}">${request.employee_id}</p>
                    </div>
                    
                    <div>
                        <label class="text-sm font-medium ${grayTextClass}">${t['created']}</label>
                        <p class="${textClass}">${new Date(request.created_at).toLocaleString()}</p>
                    </div>
                    
                    <div class="pt-4 border-t ${borderClass}">
                        <h4 class="font-semibold ${textClass} mb-3">${t['status_update']}</h4>
                        <form onsubmit="updateRequestStatus(event, '${table}', ${request.request_id})">
                            <select name="status" class="w-full px-4 py-2 border rounded-lg mb-3 ${inputClass}">
                                <option value="New" ${request.status === 'New' ? 'selected' : ''}>${statusMap[currentLang]['New']}</option>
                                <option value="In Progress" ${request.status === 'In Progress' ? 'selected' : ''}>${statusMap[currentLang]['In Progress']}</option>
                                <option value="Complete" ${request.status === 'Complete' ? 'selected' : ''}>${statusMap[currentLang]['Complete']}</option>
                                <option value="Cancelled" ${request.status === 'Cancelled' ? 'selected' : ''}>${statusMap[currentLang]['Cancelled']}</option>
                            </select>
                            
                            <textarea name="remarks" placeholder="${t['handler_remarks']} ${t['optional']}" rows="3"
                                class="w-full px-4 py-2 border rounded-lg mb-3 ${inputClass}">${request.handler_remarks || ''}</textarea>
                            
                            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg transition font-medium">
                                ${t['update_request']}
                            </button>
                        </form>
                    </div>
                </div>
            `;
            return html;
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
            
            // Disable submit button
            const submitBtn = event.target.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="inline-block animate-spin mr-2">⏳</span> ' + t['updating'];
            
            fetch('<?php echo BASE_PATH; ?>/api/update_request_status.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(response => {
                return response.json();
            })
            .then(result => {
                if (result.success) {
                    showToast(t['updated_successfully'], 'success');
                    closeRequestModal();
                    setTimeout(() => {
                        location.reload();
                    }, 500);
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

        // Close modal on ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeRequestModal();
            }
        });

        // Close modal on outside click
        document.getElementById('requestModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeRequestModal();
            }
        });

        // Toast notification function
        function showToast(message, type = 'info') {
            const bgColor = type === 'success' ? 'bg-green-500' : (type === 'error' ? 'bg-red-500' : 'bg-blue-500');
            const toast = document.createElement('div');
            toast.className = `fixed bottom-6 right-6 ${bgColor} text-white px-6 py-4 rounded-lg shadow-lg z-50 animate-fade-in-up`;
            toast.textContent = message;
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.remove();
            }, 3000);
        }
    </script>

    <style>
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
    </style>

    <?php include __DIR__ . '/../../includes/footer.php'; ?>
</body>
</html>