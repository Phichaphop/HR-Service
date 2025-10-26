<?php

/**
 * Request Leave Page - Complete Version with Multi-Language Support
 * Supports: Thai (ไทย), English (EN), Myanmar (မြန်မာ)
 */

require_once __DIR__ . '/../../config/db_config.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../models/Employee.php';
require_once __DIR__ . '/../../db/Localization.php';

AuthController::requireAuth();

// Get current settings from session
$current_lang = $_SESSION['language'] ?? 'th';
$theme_mode = $_SESSION['theme_mode'] ?? 'light';
$is_dark = ($theme_mode === 'dark');
$user_id = $_SESSION['user_id'];

// Theme colors based on dark mode
$card_bg = $is_dark ? 'bg-gray-800' : 'bg-white';
$text_class = $is_dark ? 'text-white' : 'text-gray-900';
$bg_class = $is_dark ? 'bg-gray-900' : 'bg-gray-50';
$border_class = $is_dark ? 'border-gray-700' : 'border-gray-200';
$input_class = $is_dark ? 'bg-gray-700 border-gray-600 text-white placeholder-gray-400' : 'bg-white border-gray-300 text-gray-900 placeholder-gray-500';

// Multi-language translations
$translations = [
    'th' => [
        'page_title' => 'ขอใบลา',
        'page_subtitle' => 'ส่งใบลาของคุณ',
        'back_to_dashboard' => 'กลับไปหน้าหลัก',
        'request_leave' => 'ขอใบลา',
        'submit_leave_application' => 'ส่งใบคำขอลา',
        'submitted_successfully' => 'ส่งคำขอลาเรียบร้อยแล้ว',
        'failed_to_submit' => 'ล้มเหลวในการส่งคำขอลา',
        'view_my_requests' => 'ดูคำขอของฉัน →',
        'employee_information' => 'ข้อมูลพนักงาน',
        'employee_id' => 'รหัสพนักงาน',
        'name' => 'ชื่อ',
        'position' => 'ตำแหน่ง',
        'department' => 'แผนก',
        'leave_type' => 'ประเภทใบลา',
        'select_leave_type' => 'เลือกประเภทใบลา',
        'sick_leave' => 'ลาป่วย',
        'annual_leave' => 'ลาพักร้อน',
        'personal_leave' => 'ลากิจ',
        'maternity_leave' => 'ลาคลอด',
        'paternity_leave' => 'ลาบวช',
        'unpaid_leave' => 'ลาไม่รับค่าจ้าง',
        'other' => 'อื่นๆ',
        'start_date' => 'วันที่เริ่มต้น',
        'end_date' => 'วันที่สิ้นสุด',
        'total_days' => 'จำนวนวันที่ลา',
        'days' => 'วัน',
        'reason' => 'เหตุผล',
        'reason_placeholder' => 'โปรดระบุเหตุผลโดยละเอียด...',
        'minimum_characters' => 'อย่างน้อย 10 ตัวอักษร',
        'important_notice' => 'ประกาศสำคัญ',
        'notice_1' => '• หลังจากส่งแล้ว คุณไม่สามารถแก้ไขคำขอได้',
        'notice_2' => '• คุณสามารถยกเลิกได้หากสถานะยังเป็น "ใหม่"',
        'notice_3' => '• ส่งคำขออย่างน้อย 3 วันก่อนที่จะลาตามแผน',
        'submit_request' => 'ส่งคำขอ',
        'cancel' => 'ยกเลิก',
        'recent_leave_requests' => 'คำขอลาล่าสุด',
        'no_previous_requests' => 'ไม่มีคำขอในอดีต',
        'required' => 'จำเป็น',
        'please_select_leave_type' => 'โปรดเลือกประเภทใบลา',
        'please_select_dates' => 'โปรดเลือกวันที่เริ่มต้นและสิ้นสุด',
        'reason_too_short' => 'เหตุผลต้องมีอย่างน้อย 10 ตัวอักษร',
        'end_date_after_start_date' => 'วันที่สิ้นสุดต้องเป็นวันหลังจากวันที่เริ่มต้น',
        'confirm_submit' => 'คุณแน่ใจว่าต้องการส่งคำขอลานี้หรือไม่?',
    ],
    'en' => [
        'page_title' => 'Request Leave',
        'page_subtitle' => 'Submit your leave application',
        'back_to_dashboard' => 'Back to Dashboard',
        'request_leave' => 'Request Leave',
        'submit_leave_application' => 'Submit Your Leave Application',
        'submitted_successfully' => 'Leave request submitted successfully',
        'failed_to_submit' => 'Failed to submit leave request',
        'view_my_requests' => 'View my requests →',
        'employee_information' => 'Employee Information',
        'employee_id' => 'Employee ID',
        'name' => 'Name',
        'position' => 'Position',
        'department' => 'Department',
        'leave_type' => 'Leave Type',
        'select_leave_type' => 'Select leave type',
        'sick_leave' => 'Sick Leave',
        'annual_leave' => 'Annual Leave',
        'personal_leave' => 'Personal Leave',
        'maternity_leave' => 'Maternity Leave',
        'paternity_leave' => 'Paternity Leave',
        'unpaid_leave' => 'Unpaid Leave',
        'other' => 'Other',
        'start_date' => 'Start Date',
        'end_date' => 'End Date',
        'total_days' => 'Total Days',
        'days' => 'days',
        'reason' => 'Reason',
        'reason_placeholder' => 'Please provide a detailed reason for your leave...',
        'minimum_characters' => 'Minimum 10 characters',
        'important_notice' => 'Important Notice',
        'notice_1' => '• Once submitted, you cannot edit the request',
        'notice_2' => '• You can cancel if status is still "New"',
        'notice_3' => '• Submit at least 3 days in advance for planned leave',
        'submit_request' => 'Submit Request',
        'cancel' => 'Cancel',
        'recent_leave_requests' => 'Recent Leave Requests',
        'no_previous_requests' => 'No previous requests',
        'required' => 'Required',
        'please_select_leave_type' => 'Please select a leave type',
        'please_select_dates' => 'Please select start and end dates',
        'reason_too_short' => 'Reason must be at least 10 characters',
        'end_date_after_start_date' => 'End date must be after start date',
        'confirm_submit' => 'Are you sure you want to submit this leave request?',
    ],
    'my' => [
        'page_title' => 'အငြိုးပြုစုတောင်းခံမှု',
        'page_subtitle' => 'သင်၏အငြိုးပြုစုအပ်ခြင်းအကြောင်းအရာတင်သွင်းမည်',
        'back_to_dashboard' => 'Dashboard သို့ပြန်သွားမည်',
        'request_leave' => 'အငြိုးပြုစုတောင်းခံ',
        'submit_leave_application' => 'သင်၏အငြိုးပြုစုအပ်ခြင်းတင်သွင်းမည်',
        'submitted_successfully' => 'အငြိုးပြုစုတောင်းခံမှုအောင်မြင်စွာတင်သွင်းခြင်း',
        'failed_to_submit' => 'အငြိုးပြုစုတောင်းခံမှုတင်သွင်းခြင်းမအောင်မြင်',
        'view_my_requests' => 'ကျွန်ုပ်၏တောင်းခံများကိုကြည့်ရှုမည် →',
        'employee_information' => 'အလုပ်သမားအချက်အလက်',
        'employee_id' => 'အလုပ်သမားအိုင်ဒီ',
        'name' => 'အမည်',
        'position' => 'အနေအထား',
        'department' => 'ဌာန',
        'leave_type' => 'အငြိုးပြုစုအမျိုးအစား',
        'select_leave_type' => 'အငြိုးပြုစုအမျိုးအစားရွေးချယ်မည်',
        'sick_leave' => 'ကျန်းမာရေးအငြိုးပြုစု',
        'annual_leave' => 'နှစ်စဉ်အငြိုးပြုစု',
        'personal_leave' => 'ကိုယ်ရေးအငြိုးပြုစု',
        'maternity_leave' => 'သူမင်းဖွားကင်းအငြိုးပြုစု',
        'paternity_leave' => 'သူကြီးဖွားကင်းအငြိုးပြုစု',
        'unpaid_leave' => 'ခမဲ့အငြိုးပြုစု',
        'other' => 'အခြား',
        'start_date' => 'စတင်သည့်နေ့စွဲ',
        'end_date' => 'အဆုံးသတ်နေ့စွဲ',
        'total_days' => 'စုစုပေါင်းရက်သတ္တပတ်',
        'days' => 'ရက်',
        'reason' => 'ကြောင်းခြင်း',
        'reason_placeholder' => 'သင်၏အငြိုးပြုစုအတွက်အ상세ကြောင်းခြင်းပေးပါ...',
        'minimum_characters' => 'အနည်းဆုံး 10 လက္ခဏာ',
        'important_notice' => 'အရေးကြီးသောအသိပေးချက်',
        'notice_1' => '• တင်သွင်းပြီးနောက်တွင် သင်သည်တောင်းခံမှုကိုပြင်ဆင်မရနိုင်သည်',
        'notice_2' => '• အခြေအနေသည်နယ်ခြင်း၌ သင်သည်ပယ်ဖျက်နိုင်သည်',
        'notice_3' => '• အစီအစဉ်ထားလုပ်ဆောင်ရန်အငြိုးပြုစုအတွက်အနည်းဆုံး 3 ရက်အကြိုတင်တင်သွင်းပါ',
        'submit_request' => 'တောင်းခံမှုတင်သွင်းမည်',
        'cancel' => 'ပယ်ဖျက်မည်',
        'recent_leave_requests' => 'လတ်တလောအငြိုးပြုစုတောင်းခံများ',
        'no_previous_requests' => 'ယခင်တောင်းခံများမရှိ',
        'required' => 'လိုအပ်ခြင်း',
        'please_select_leave_type' => 'အငြိုးပြုစုအမျိုးအစားရွေးချယ်ပါ',
        'please_select_dates' => 'စတင်သည့်နေ့စွဲနှင့်အဆုံးသတ်နေ့စွဲရွေးချယ်ပါ',
        'reason_too_short' => 'ကြောင်းခြင်းသည်အနည်းဆုံး 10 လက္ခဏာရှိရမည်',
        'end_date_after_start_date' => 'အဆုံးသတ်နေ့စွဲသည်စတင်သည့်နေ့စွဲနောက်တွင်ရှိရမည်',
        'confirm_submit' => 'ဤအငြိုးပြုစုတောင်းခံမှုတင်သွင်းရန်သေချာပါသလား?',
    ]
];

// Get current language strings
$t = $translations[$current_lang] ?? $translations['th'];

// Leave type mapping with multi-language support
$leave_types = [
    'Sick Leave' => ['th' => 'ลาป่วย', 'en' => 'Sick Leave', 'my' => 'ကျန်းမာရေးအငြိုးပြုစု'],
    'Annual Leave' => ['th' => 'ลาพักร้อน', 'en' => 'Annual Leave', 'my' => 'နှစ်စဉ်အငြိုးပြုစု'],
    'Personal Leave' => ['th' => 'ลากิจ', 'en' => 'Personal Leave', 'my' => 'ကိုယ်ရေးအငြိုးပြုစု'],
    'Maternity Leave' => ['th' => 'ลาคลอด', 'en' => 'Maternity Leave', 'my' => 'သူမင်းဖွားကင်းအငြိုးပြုစု'],
    'Paternity Leave' => ['th' => 'ลาบวช', 'en' => 'Paternity Leave', 'my' => 'သူကြီးဖွားကင်းအငြိုးပြုစု'],
    'Unpaid Leave' => ['th' => 'ลาไม่รับค่าจ้าง', 'en' => 'Unpaid Leave', 'my' => 'ခမဲ့အငြိုးပြုစု'],
    'Other' => ['th' => 'อื่นๆ', 'en' => 'Other', 'my' => 'အခြား']
];

$page_title = $t['page_title'];
ensure_session_started();

$employee = Employee::getById($user_id);
$message = '';
$message_type = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = getDbConnection();

    $leave_type = $_POST['leave_type'] ?? '';
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';
    $reason = $_POST['reason'] ?? '';

    // Calculate total days
    $start = new DateTime($start_date);
    $end = new DateTime($end_date);
    $interval = $start->diff($end);
    $total_days = $interval->days + 1;

    $stmt = $conn->prepare("INSERT INTO leave_requests (employee_id, leave_type, start_date, end_date, total_days, reason, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, 'New', NOW(), NOW())");
    $stmt->bind_param("ssssis", $user_id, $leave_type, $start_date, $end_date, $total_days, $reason);

    if ($stmt->execute()) {
        $message = $t['submitted_successfully'];
        $message_type = 'success';
    } else {
        $message = $t['failed_to_submit'];
        $message_type = 'error';
    }

    $stmt->close();
    $conn->close();
}

// Get recent requests
$conn = getDbConnection();
$stmt = $conn->prepare("SELECT * FROM leave_requests WHERE employee_id = ? ORDER BY created_at DESC LIMIT 5");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$recent_requests = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();

// Get display name
$display_name = $current_lang === 'en' ? ($employee['full_name_en'] ?? 'Unknown') : ($employee['full_name_th'] ?? 'Unknown');

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>

<body class="<?php echo $bg_class; ?> <?php echo $text_class; ?> theme-transition">
    <div class="lg:ml-64">
        <div class="container mx-auto px-4 py-6 max-w-4xl">

            <!-- Message Alert -->
            <?php if ($message): ?>
                <div class="mb-6 p-4 rounded-lg <?php echo $message_type === 'success' ? 'bg-green-50 dark:bg-green-900 border-l-4 border-green-500' : 'bg-red-50 dark:bg-red-900 border-l-4 border-red-500'; ?>">
                    <div class="flex items-center">
                        <?php if ($message_type === 'success'): ?>
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        <?php else: ?>
                            <svg class="w-6 h-6 text-red-600 dark:text-red-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        <?php endif; ?>
                        <p class="<?php echo $message_type === 'success' ? 'text-green-700 dark:text-green-300' : 'text-red-700 dark:text-red-300'; ?> font-medium">
                            <?php echo htmlspecialchars($message); ?>
                        </p>
                    </div>
                    <?php if ($message_type === 'success'): ?>
                        <a href="<?php echo BASE_PATH; ?>/views/employee/my_requests.php"
                            class="text-green-700 dark:text-green-300 underline text-sm mt-2 inline-block">
                            <?php echo $t['view_my_requests']; ?>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Page Header -->
            <div class="mb-6 bg-gradient-to-r from-green-600 to-teal-600 rounded-lg shadow-lg p-6">
                <div class="flex items-center justify-between flex-col md:flex-row gap-4">
                    <div class="flex items-center">
                        <svg class="w-10 h-10 text-white mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <div>
                            <h1 class="text-3xl font-bold text-white"><?php echo $t['page_title']; ?></h1>
                            <p class="text-green-100 mt-1"><?php echo $t['page_subtitle']; ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Card -->
            <div class="<?php echo $card_bg; ?> rounded-lg shadow-lg p-6 border <?php echo $border_class; ?>">
                <form method="POST" action="" id="leaveForm">

                    <!-- Employee Info (Read-only) -->
                    <div class="<?php echo $is_dark ? 'bg-gray-700' : 'bg-blue-50'; ?> rounded-lg p-4 mb-6">
                        <h3 class="font-semibold <?php echo $is_dark ? 'text-blue-300' : 'text-blue-900'; ?> mb-3 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            <?php echo $t['employee_information']; ?>
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium <?php echo $is_dark ? 'text-gray-300' : 'text-blue-800'; ?> mb-1">
                                    <?php echo $t['employee_id']; ?>
                                </label>
                                <input type="text" value="<?php echo htmlspecialchars($employee['employee_id']); ?>" readonly
                                    class="w-full px-3 py-2 <?php echo $is_dark ? 'bg-gray-600 border-gray-500 text-gray-200' : 'bg-white border-blue-200 text-gray-700'; ?> border rounded cursor-not-allowed">
                            </div>
                            <div>
                                <label class="block text-sm font-medium <?php echo $is_dark ? 'text-gray-300' : 'text-blue-800'; ?> mb-1">
                                    <?php echo $t['name']; ?>
                                </label>
                                <input type="text" value="<?php echo htmlspecialchars($display_name); ?>" readonly
                                    class="w-full px-3 py-2 <?php echo $is_dark ? 'bg-gray-600 border-gray-500 text-gray-200' : 'bg-white border-blue-200 text-gray-700'; ?> border rounded cursor-not-allowed">
                            </div>
                            <div>
                                <label class="block text-sm font-medium <?php echo $is_dark ? 'text-gray-300' : 'text-blue-800'; ?> mb-1">
                                    <?php echo $t['position']; ?>
                                </label>
                                <input type="text" value="<?php echo get_master('position_master', $employee['position_id']); ?>" readonly
                                    class="w-full px-3 py-2 <?php echo $is_dark ? 'bg-gray-600 border-gray-500 text-gray-200' : 'bg-white border-blue-200 text-gray-700'; ?> border rounded cursor-not-allowed">
                            </div>
                            <div>
                                <label class="block text-sm font-medium <?php echo $is_dark ? 'text-gray-300' : 'text-blue-800'; ?> mb-1">
                                    <?php echo $t['department']; ?>
                                </label>
                                <input type="text" value="<?php echo get_master('department_master', $employee['department_id']); ?>" readonly
                                    class="w-full px-3 py-2 <?php echo $is_dark ? 'bg-gray-600 border-gray-500 text-gray-200' : 'bg-white border-blue-200 text-gray-700'; ?> border rounded cursor-not-allowed">
                            </div>
                        </div>
                    </div>

                    <!-- Leave Type -->
                    <div class="mb-6">
                        <label for="leave_type" class="block text-sm font-medium <?php echo $text_class; ?> mb-2">
                            <?php echo $t['leave_type']; ?> <span class="text-red-500">*</span>
                        </label>
                        <select id="leave_type" name="leave_type" required
                            class="w-full px-4 py-3 border <?php echo $input_class; ?> rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value=""><?php echo $t['select_leave_type']; ?></option>
                            <?php foreach ($leave_types as $key => $langs):
                                $label = $langs[$current_lang] ?? $langs['en'];
                            ?>
                                <option value="<?php echo $key; ?>"><?php echo htmlspecialchars($label); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Date Range -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="start_date" class="block text-sm font-medium <?php echo $text_class; ?> mb-2">
                                <?php echo $t['start_date']; ?> <span class="text-red-500">*</span>
                            </label>
                            <input type="date" id="start_date" name="start_date" required
                                min="<?php echo date('Y-m-d'); ?>"
                                onchange="calculateDays()"
                                class="w-full px-4 py-3 border <?php echo $input_class; ?> rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label for="end_date" class="block text-sm font-medium <?php echo $text_class; ?> mb-2">
                                <?php echo $t['end_date']; ?> <span class="text-red-500">*</span>
                            </label>
                            <input type="date" id="end_date" name="end_date" required
                                min="<?php echo date('Y-m-d'); ?>"
                                onchange="calculateDays()"
                                class="w-full px-4 py-3 border <?php echo $input_class; ?> rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <!-- Total Days Display -->
                    <div id="totalDaysDisplay" class="mb-6 p-4 <?php echo $is_dark ? 'bg-gray-700' : 'bg-gray-50'; ?> rounded-lg hidden">
                        <p class="text-sm <?php echo $text_class; ?>">
                            <strong><?php echo $t['total_days']; ?>:</strong> <span id="totalDaysText" class="text-lg font-bold text-blue-600">0</span> <?php echo $t['days']; ?>
                        </p>
                    </div>

                    <!-- Reason -->
                    <div class="mb-6">
                        <label for="reason" class="block text-sm font-medium <?php echo $text_class; ?> mb-2">
                            <?php echo $t['reason']; ?> <span class="text-red-500">*</span>
                        </label>
                        <textarea id="reason" name="reason" required rows="4"
                            placeholder="<?php echo $t['reason_placeholder']; ?>"
                            class="w-full px-4 py-3 border <?php echo $input_class; ?> rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
                        <p class="text-xs <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?> mt-1"><?php echo $t['minimum_characters']; ?></p>
                    </div>

                    <!-- Important Notice -->
                    <div class="mb-6 p-4 bg-yellow-50 dark:bg-yellow-900 border-l-4 border-yellow-400 rounded">
                        <div class="flex">
                            <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-yellow-800 dark:text-yellow-300"><?php echo $t['important_notice']; ?></p>
                                <p class="text-xs text-yellow-700 dark:text-yellow-400 mt-1">
                                    <?php echo $t['notice_1']; ?><br>
                                    <?php echo $t['notice_2']; ?><br>
                                    <?php echo $t['notice_3']; ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-col md:flex-row gap-4">
                        <button type="submit"
                            class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-3 px-6 rounded-lg font-medium transition shadow-lg hover:shadow-xl">
                            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                            </svg>
                            <?php echo $t['submit_request']; ?>
                        </button>
                        <a href="<?php echo BASE_PATH; ?>/index.php"
                            class="flex-1 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 py-3 px-6 rounded-lg font-medium hover:bg-gray-300 dark:hover:bg-gray-600 transition text-center">
                            <?php echo $t['cancel']; ?>
                        </a>
                    </div>
                </form>
            </div>

            <!-- Recent Requests -->
            <div class="mt-6 <?php echo $card_bg; ?> rounded-lg shadow-lg p-6 border <?php echo $border_class; ?>">
                <h3 class="font-bold <?php echo $text_class; ?> mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <?php echo $t['recent_leave_requests']; ?>
                </h3>
                <?php if (empty($recent_requests)): ?>
                    <p class="<?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?> text-sm"><?php echo $t['no_previous_requests']; ?></p>
                <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach ($recent_requests as $req):
                            $req_type_label = $leave_types[$req['leave_type']][$current_lang] ?? $req['leave_type'];
                            $status_colors = [
                                'New' => 'bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-300',
                                'In Progress' => 'bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-300',
                                'Complete' => 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-300',
                                'Cancelled' => 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-300'
                            ];
                            $status_color = $status_colors[$req['status']] ?? 'bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-300';
                        ?>
                            <div class="p-3 border <?php echo $is_dark ? 'border-gray-600 hover:bg-gray-700' : 'border-gray-200 hover:bg-gray-50'; ?> rounded-lg transition">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="font-medium <?php echo $text_class; ?>"><?php echo htmlspecialchars($req_type_label); ?></p>
                                        <p class="text-xs <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?>">
                                            <?php echo date('M d, Y', strtotime($req['start_date'])); ?> -
                                            <?php echo date('M d, Y', strtotime($req['end_date'])); ?>
                                            (<?php echo $req['total_days']; ?> <?php echo $t['days']; ?>)
                                        </p>
                                    </div>
                                    <span class="px-2 py-1 rounded-full text-xs font-medium <?php echo $status_color; ?>">
                                        <?php echo $req['status']; ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="mt-4">
                        <a href="<?php echo BASE_PATH; ?>/views/employee/my_requests.php"
                            class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 text-sm font-medium">
                            <?php echo $t['view_my_requests']; ?>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        const t = <?php echo json_encode($t); ?>;

        function calculateDays() {
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;

            if (startDate && endDate) {
                const start = new Date(startDate);
                const end = new Date(endDate);

                if (end < start) {
                    alert(t['end_date_after_start_date']);
                    document.getElementById('end_date').value = '';
                    return;
                }

                const diffTime = Math.abs(end - start);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;

                document.getElementById('totalDaysText').textContent = diffDays;
                document.getElementById('totalDaysDisplay').classList.remove('hidden');
            }
        }

        // Form validation
        document.getElementById('leaveForm').addEventListener('submit', function(e) {
            const reason = document.getElementById('reason').value.trim();
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            const leaveType = document.getElementById('leave_type').value;

            if (!leaveType) {
                e.preventDefault();
                alert(t['please_select_leave_type']);
                return;
            }

            if (!startDate || !endDate) {
                e.preventDefault();
                alert(t['please_select_dates']);
                return;
            }

            if (reason.length < 10) {
                e.preventDefault();
                alert(t['reason_too_short']);
                return;
            }

            if (!confirm(t['confirm_submit'])) {
                e.preventDefault();
            }
        });

        // Set minimum end date when start date changes
        document.getElementById('start_date').addEventListener('change', function() {
            document.getElementById('end_date').min = this.value;
        });
    </script>

    <?php include __DIR__ . '/../../includes/footer.php'; ?>
</body>

</html>