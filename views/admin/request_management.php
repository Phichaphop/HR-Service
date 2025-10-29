<?php
/**
 * Request Management Page - COMPLETE VERSION
 * File: views/admin/request_management.php
 * 
 * Supports: Thai (ไทย), English (EN), Myanmar (မြန်မာ)
 * Features: 
 * - Multi-language UI, Dark Mode, Mobile Responsive
 * - Admin/Officer only - Manage all service requests
 * - Display Certificate Type information
 * - Search and Filter functionality
 * 
 * UPDATES:
 * 1. Integrated admin_get_request_details.php API
 * 2. Added Certificate Type display in modal
 * 3. Enhanced generateDetailHTML() function
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
        'employee_name' => 'ชื่อพนักงาน',
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
        'optional' => '(ไม่บังคับ)',
        'updating' => 'กำลังอัปเดต...',
        'position' => 'ตำแหน่ง',
        'division' => 'แผนก',
        'request_info' => 'ข้อมูลคำขอ',
        'employee_info' => 'ข้อมูลพนักงาน',
        'certificate_type' => 'ประเภทหนังสือรับรอง',
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
        'employee_name' => 'Employee Name',
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
        'optional' => '(Optional)',
        'updating' => 'Updating...',
        'position' => 'Position',
        'division' => 'Division',
        'request_info' => 'Request Information',
        'employee_info' => 'Employee Information',
        'certificate_type' => 'Certificate Type',
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
        'employee_name' => 'အလုပ်သမားအမည်',
        'created' => 'ဖန်တီးသည်',
        'handler' => 'အကျင့်တည်ဝတ်ပြုသူ',
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
        'certificate_type' => 'လက်မှတ်အမျိုးအစား',
    ]
];

// Get current language strings
$t = $translations[$current_lang] ?? $translations['th'];
$page_title = $t['page_title'];

$conn = getDbConnection();
if (!$conn) {
    die("Database connection failed");
}

// Get filter parameters
$status_filter = $_GET['status'] ?? 'all';
$type_filter = $_GET['type'] ?? 'all';
$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

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
function getRequests($conn, $table, $type_name, $type_key, $where_sql, $params, $types, $offset, $per_page, $current_lang) {
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
        r.satisfaction_score
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
        $requests = getRequests($conn, $table, $type_name, $table, $where_sql, $params, $types, 0, $per_page, $current_lang);
        $all_requests = array_merge($all_requests, $requests);
    }
} else {
    if (isset($request_types[$type_filter])) {
        $labels = $request_types[$type_filter];
        $type_name = $labels['label_en'];
        $all_requests = getRequests($conn, $type_filter, $type_name, $type_filter, $where_sql, $params, $types, $offset, $per_page, $current_lang);
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
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $stats['total'] += $row['count'];
            $status_key = strtolower(str_replace(' ', '_', $row['status']));
            if (isset($stats[$status_key])) {
                $stats[$status_key] += $row['count'];
            }
        }
    }
}

$conn->close();

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>

<div class="flex-1 lg:ml-64 p-4 lg:p-6">

    <!-- Page Header -->
    <div class="mb-6 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-lg shadow-lg p-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <svg class="w-10 h-10 text-white mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                <div>
                    <h1 class="text-3xl font-bold text-white"><?php echo $page_title; ?></h1>
                    <p class="text-blue-100 mt-1"><?php echo $t['page_subtitle']; ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
        <!-- Total -->
        <div class="<?php echo $card_bg; ?> rounded-lg shadow-sm p-4 border <?php echo $border_class; ?>">
            <p class="<?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?> text-sm"><?php echo $t['total']; ?></p>
            <p class="text-2xl font-bold text-blue-600"><?php echo number_format($stats['total']); ?></p>
        </div>
        
        <!-- New -->
        <div class="<?php echo $card_bg; ?> rounded-lg shadow-sm p-4 border <?php echo $border_class; ?>">
            <p class="<?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?> text-sm"><?php echo $t['new']; ?></p>
            <p class="text-2xl font-bold text-yellow-600"><?php echo number_format($stats['new']); ?></p>
        </div>
        
        <!-- In Progress -->
        <div class="<?php echo $card_bg; ?> rounded-lg shadow-sm p-4 border <?php echo $border_class; ?>">
            <p class="<?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?> text-sm"><?php echo $t['in_progress']; ?></p>
            <p class="text-2xl font-bold text-blue-600"><?php echo number_format($stats['in_progress']); ?></p>
        </div>
        
        <!-- Complete -->
        <div class="<?php echo $card_bg; ?> rounded-lg shadow-sm p-4 border <?php echo $border_class; ?>">
            <p class="<?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?> text-sm"><?php echo $t['complete']; ?></p>
            <p class="text-2xl font-bold text-green-600"><?php echo number_format($stats['complete']); ?></p>
        </div>
        
        <!-- Cancelled -->
        <div class="<?php echo $card_bg; ?> rounded-lg shadow-sm p-4 border <?php echo $border_class; ?>">
            <p class="<?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?> text-sm"><?php echo $t['cancelled']; ?></p>
            <p class="text-2xl font-bold text-red-600"><?php echo number_format($stats['cancelled']); ?></p>
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
                        <option value="<?php echo $table; ?>" <?php echo $type_filter === $table ? 'selected' : ''; ?>><?php echo htmlspecialchars($type_label); ?></option>
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
                        <th class="px-6 py-4 text-left text-xs font-semibold <?php echo $text_class; ?> uppercase tracking-wider"><?php echo $t['employee_name']; ?></th>
                        <th class="px-6 py-4 text-left text-xs font-semibold <?php echo $text_class; ?> uppercase tracking-wider"><?php echo $t['created']; ?></th>
                        <th class="px-6 py-4 text-left text-xs font-semibold <?php echo $text_class; ?> uppercase tracking-wider"><?php echo $t['status']; ?></th>
                        <th class="px-6 py-4 text-left text-xs font-semibold <?php echo $text_class; ?> uppercase tracking-wider"><?php echo $t['handler']; ?></th>
                        <th class="px-6 py-4 text-center text-xs font-semibold <?php echo $text_class; ?> uppercase tracking-wider"><?php echo $t['actions']; ?></th>
                    </tr>
                </thead>
                <tbody class="divide-y <?php echo $is_dark ? 'divide-gray-700' : 'divide-gray-200'; ?>">
                    <?php if (!empty($all_requests)): ?>
                        <?php foreach ($all_requests as $request): 
                            $status_label = isset($t[$request['status']]) ? $t[$request['status']] : $request['status'];
                        ?>
                            <tr class="<?php echo $is_dark ? 'hover:bg-gray-700' : 'hover:bg-gray-50'; ?> transition">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="font-mono <?php echo $text_class; ?> text-sm">#<?php echo str_pad($request['request_id'], 5, '0', STR_PAD_LEFT); ?></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="<?php echo $text_class; ?> text-sm"><?php echo htmlspecialchars($request['request_type']); ?></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="font-mono <?php echo $text_class; ?> text-sm"><?php echo htmlspecialchars($request['employee_id']); ?></span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="<?php echo $text_class; ?> text-sm"><?php echo htmlspecialchars($request['employee_name'] ?? 'N/A'); ?></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="<?php echo $text_class; ?> text-sm"><?php echo date('d/m/Y', strtotime($request['created_at'])); ?></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-3 py-1 rounded-full text-xs font-medium <?php 
                                        if ($request['status'] === 'New') echo 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200';
                                        elseif ($request['status'] === 'In Progress') echo 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200';
                                        elseif ($request['status'] === 'Complete') echo 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200';
                                        elseif ($request['status'] === 'Cancelled') echo 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200';
                                    ?>">
                                        <?php echo htmlspecialchars($status_label); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($request['handler_id']): ?>
                                        <span class="<?php echo $text_class; ?> text-sm"><?php echo htmlspecialchars($request['handler_id']); ?></span>
                                    <?php else: ?>
                                        <span class="<?php echo $is_dark ? 'text-gray-500' : 'text-gray-400'; ?> text-sm italic"><?php echo $t['unassigned']; ?></span>
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
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center">
                                <p class="<?php echo $text_class; ?>"><?php echo $t['no_requests']; ?></p>
                                <p class="<?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?> text-sm mt-2"><?php echo $t['try_adjusting']; ?></p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Request Detail Modal -->
<div id="requestModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="<?php echo $card_bg; ?> rounded-xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto border <?php echo $border_class; ?>">
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

    // ========== GENERATE DETAIL HTML WITH CERTIFICATE TYPE ==========
    function generateDetailHTML(request, isDark, table) {
        const grayTextClass = isDark ? 'text-gray-400' : 'text-gray-600';
        const textClass = isDark ? 'text-white' : 'text-gray-900';
        const bgSecondary = isDark ? 'bg-gray-700' : 'bg-gray-50';
        const borderClass = isDark ? 'border-gray-600' : 'border-gray-200';
        
        const typeLabels = {
            'th': {
                'leave_requests': '✈️ ใบลา',
                'certificate_requests': '📄 หนังสือรับรอง',
                'id_card_requests': '🆔 บัตรพนักงาน',
                'shuttle_bus_requests': '🚌 รถรับส่ง',
                'locker_requests': '🔒 ตู้ล็อกเกอร์',
                'supplies_requests': '📦 วัสดุสำนักงาน',
                'skill_test_requests': '📝 ทดสอบทักษะ',
                'document_submissions': '📮 ลงชื่อส่งเอกสาร'
            },
            'en': {
                'leave_requests': '✈️ Leave Request',
                'certificate_requests': '📄 Certificate Request',
                'id_card_requests': '🆔 ID Card Request',
                'shuttle_bus_requests': '🚌 Shuttle Bus Request',
                'locker_requests': '🔒 Locker Request',
                'supplies_requests': '📦 Supplies Request',
                'skill_test_requests': '📝 Skill Test Request',
                'document_submissions': '📮 Document Submission'
            },
            'my': {
                'leave_requests': '✈️ ခွင့်ယူမှု',
                'certificate_requests': '📄 လက်မှတ်တောင်းခံမှု',
                'id_card_requests': '🆔 အသိအမှတ်ပြုကဒ်တောင်းခံမှု',
                'shuttle_bus_requests': '🚌 ကားအရံတောင်းခံမှု',
                'locker_requests': '🔒 လော့ခ်ကုနှုတောင်းခံမှု',
                'supplies_requests': '📦 ပစ္စည်းတောင်းခံမှု',
                'skill_test_requests': '📝 ကျွမ်းကျင်မှုစမ်းသပ်မှု',
                'document_submissions': '📮 စာရွက်စာတမ်းတင်သွင်းမှု'
            }
        };
        
        let html = '';
        
        // REQUEST INFO
        html += `
            <div class="mb-6">
                <h4 class="flex items-center text-lg font-bold ${textClass} mb-4">
                    <svg class="w-6 h-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    ${t['request_info'] || 'Request Information'}
                </h4>
                
                <div class="grid grid-cols-2 gap-4">
                    <div class="${bgSecondary} rounded-lg p-4 border ${borderClass}">
                        <label class="text-sm font-medium ${grayTextClass} block mb-1">${t['request_id'] || 'Request ID'}</label>
                        <p class="font-mono ${textClass} text-lg">#${String(request.request_id).padStart(5, '0')}</p>
                    </div>
                    
                    <div class="${bgSecondary} rounded-lg p-4 border ${borderClass}">
                        <label class="text-sm font-medium ${grayTextClass} block mb-1">${t['type'] || 'Type'}</label>
                        <p class="${textClass}">${typeLabels[currentLang]?.[table] || table}</p>
                    </div>
                    
                    <div class="${bgSecondary} rounded-lg p-4 border ${borderClass}">
                        <label class="text-sm font-medium ${grayTextClass} block mb-1">${t['status'] || 'Status'}</label>
                        <p class="${textClass} font-medium">${statusMap[currentLang]?.[request.status] || request.status}</p>
                    </div>
                    
                    <div class="${bgSecondary} rounded-lg p-4 border ${borderClass}">
                        <label class="text-sm font-medium ${grayTextClass} block mb-1">${t['created'] || 'Created'}</label>
                        <p class="${textClass}">${new Date(request.created_at).toLocaleDateString(currentLang === 'th' ? 'th-TH' : 'en-US')}</p>
                    </div>
                </div>
            </div>
        `;
        
        // EMPLOYEE INFO
        html += `
            <div class="mb-6">
                <h4 class="flex items-center text-lg font-bold ${textClass} mb-4">
                    <svg class="w-6 h-6 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    ${t['employee_info'] || 'Employee Information'}
                </h4>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="${bgSecondary} rounded-lg p-4 border ${borderClass}">
                        <label class="text-sm font-medium ${grayTextClass} block mb-1">${t['employee'] || 'Employee ID'}</label>
                        <p class="font-mono ${textClass}">${request.employee_id || 'N/A'}</p>
                    </div>
                    
                    <div class="${bgSecondary} rounded-lg p-4 border ${borderClass}">
                        <label class="text-sm font-medium ${grayTextClass} block mb-1">${t['employee_name'] || 'Employee Name'}</label>
                        <p class="${textClass}">${request.full_name_th || request.full_name_en || 'N/A'}</p>
                    </div>
                </div>
            </div>
        `;
        
        // ✨ NEW: CERTIFICATE TYPE INFO
        if (table === 'certificate_requests' && request.cert_type_display) {
            html += `
                <div class="mb-6">
                    <h4 class="flex items-center text-lg font-bold ${textClass} mb-4">
                        <svg class="w-6 h-6 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        📋 ${t['certificate_type'] || 'Certificate Type'}
                    </h4>
                    
                    <div class="bg-gradient-to-r from-purple-50 to-blue-50 dark:from-purple-900 dark:to-blue-900 rounded-lg p-6 border-2 border-purple-200 dark:border-purple-700">
                        <div class="flex items-center gap-3">
                            <div class="text-3xl">📄</div>
                            <div>
                                <p class="text-sm font-medium ${grayTextClass}">${t['certificate_type'] || 'Type'}</p>
                                <p class="text-xl font-bold ${textClass}">${request.cert_type_display}</p>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }
        
        return html;
    }

    // ========== OPEN MODAL ==========
    function openRequestModal(table, requestId) {
        const modal = document.getElementById('requestModal');
        const content = document.getElementById('modalContent');
        
        content.innerHTML = '<div class="text-center py-8"><div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div><p class="mt-4 ' + (isDark ? 'text-gray-300' : 'text-gray-700') + '">' + t['loading'] + '</p></div>';
        modal.classList.remove('hidden');
        
        const basePath = '<?php echo defined("BASE_PATH") ? BASE_PATH : ""; ?>';
        const url = basePath ? `${basePath}/api/admin_get_request_details.php?table=${table}&id=${requestId}` 
                             : `/api/admin_get_request_details.php?table=${table}&id=${requestId}`;
        
        fetch(url)
            .then(response => {
                if (!response.ok) {
                    throw new Error('HTTP error! status: ' + response.status);
                }
                return response.json();
            })
            .then(result => {
                if (result.success) {
                    content.innerHTML = generateDetailHTML(result.request, isDark, table);
                } else {
                    content.innerHTML = '<div class="bg-red-50 dark:bg-red-900 border border-red-200 dark:border-red-700 rounded-lg p-4"><p class="text-red-800 dark:text-red-200">' + (result.message || t['error_loading']) + '</p></div>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                content.innerHTML = '<div class="bg-red-50 dark:bg-red-900 border border-red-200 dark:border-red-700 rounded-lg p-4"><p class="text-red-800 dark:text-red-200">' + t['error_loading'] + '</p></div>';
            });
    }

    // ========== CLOSE MODAL ==========
    function closeRequestModal() {
        document.getElementById('requestModal').classList.add('hidden');
    }

    // ========== CLOSE ON ESC ==========
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeRequestModal();
        }
    });

    // ========== CLOSE ON OUTSIDE CLICK ==========
    document.getElementById('requestModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeRequestModal();
        }
    });
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>