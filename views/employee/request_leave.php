<?php
/**
 * Request Leave Page - UPDATED UI VERSION
 * ✅ Standardized Layout Structure (Matches all request forms)
 * ✅ Improved Spacing and Typography
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
$border_class = $is_dark ? 'border-gray-700' : 'border-gray-200';
$input_class = $is_dark ? 'bg-gray-700 border-gray-600 text-white placeholder-gray-400' : 'bg-white border-gray-300 text-gray-900 placeholder-gray-500';

// Multi-language translations
$translations = [
    'th' => [
        'page_title' => 'ขอใบลา',
        'page_subtitle' => 'ส่งใบลาของคุณ',
        'employee_information' => 'ข้อมูลพนักงาน',
        'employee_id' => 'รหัสพนักงาน',
        'name' => 'ชื่อ',
        'position' => 'ตำแหน่ง',
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
        'reason_placeholder' => 'โปรดระบุเหตุผลโดยละเอียด (อย่างน้อย 10 ตัวอักษร)...',
        'important_notice' => 'ประกาศสำคัญ',
        'notice_1' => '✓ หลังจากส่งแล้ว คุณไม่สามารถแก้ไขคำขอได้',
        'notice_2' => '✓ คุณสามารถยกเลิกได้หากสถานะยังเป็น "ใหม่"',
        'notice_3' => '✓ ส่งคำขออย่างน้อย 3 วันก่อนที่จะลาตามแผน',
        'submit_request' => 'ส่งคำขอ',
        'cancel' => 'ยกเลิก',
        'required' => 'จำเป็น',
        'please_select_leave_type' => 'โปรดเลือกประเภทใบลา',
        'please_select_dates' => 'โปรดเลือกวันที่เริ่มต้นและสิ้นสุด',
        'reason_too_short' => 'เหตุผลต้องมีอย่างน้อย 10 ตัวอักษร',
        'end_date_after_start_date' => 'วันที่สิ้นสุดต้องเป็นวันหลังจากวันที่เริ่มต้น',
        'confirm_submit' => 'คุณแน่ใจว่าต้องการส่งคำขอลานี้หรือไม่?',
        'error_occurred' => 'เกิดข้อผิดพลาด:',
        'view_my_requests' => 'ดูคำขอของฉัน →',
        'failed_to_submit' => 'ล้มเหลวในการส่งคำขอลา',
    ],
    'en' => [
        'page_title' => 'Request Leave',
        'page_subtitle' => 'Submit your leave application',
        'employee_information' => 'Employee Information',
        'employee_id' => 'Employee ID',
        'name' => 'Name',
        'position' => 'Position',
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
        'reason_placeholder' => 'Please provide a detailed reason for your leave (minimum 10 characters)...',
        'important_notice' => 'Important Notice',
        'notice_1' => '✓ Once submitted, you cannot edit the request',
        'notice_2' => '✓ You can cancel if status is still "New"',
        'notice_3' => '✓ Submit at least 3 days in advance for planned leave',
        'submit_request' => 'Submit Request',
        'cancel' => 'Cancel',
        'required' => 'Required',
        'please_select_leave_type' => 'Please select a leave type',
        'please_select_dates' => 'Please select start and end dates',
        'reason_too_short' => 'Reason must be at least 10 characters',
        'end_date_after_start_date' => 'End date must be after start date',
        'confirm_submit' => 'Are you sure you want to submit this leave request?',
        'error_occurred' => 'An error occurred:',
        'view_my_requests' => 'View my requests →',
        'failed_to_submit' => 'Failed to submit leave request',
    ],
    'my' => [
        'page_title' => 'အငြိုးပြုစုတောင်းခံမှု',
        'page_subtitle' => 'သင်၏အငြိုးပြုစုအပ်ခြင်းအကြောင်းအရာတင်သွင်းမည်',
        'employee_information' => 'အလုပ်သမားအချက်အလက်',
        'employee_id' => 'အလုပ်သမားအိုင်ဒီ',
        'name' => 'အမည်',
        'position' => 'အနေအထား',
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
        'reason_placeholder' => 'သင်၏အငြိုးပြုစုအတွက်အ상세ကြောင်းခြင်းပေးပါ (အနည်းဆုံး 10 လက္ခဏာ)...',
        'important_notice' => 'အရေးကြီးသောအသိပေးချက်',
        'notice_1' => '✓ တင်သွင်းပြီးနောက်တွင် သင်သည်တောင်းခံမှုကိုပြင်ဆင်မရနိုင်သည်',
        'notice_2' => '✓ အခြေအနေသည်နယ်ခြင်း၌ သင်သည်ပယ်ဖျက်နိုင်သည်',
        'notice_3' => '✓ အစီအစဉ်ထားလုပ်ဆောင်ရန်အငြိုးပြုစုအတွက်အနည်းဆုံး 3 ရက်အကြိုတင်တင်သွင်းပါ',
        'submit_request' => 'တောင်းခံမှုတင်သွင်းမည်',
        'cancel' => 'ပယ်ဖျက်မည်',
        'required' => 'လိုအပ်ခြင်း',
        'please_select_leave_type' => 'အငြိုးပြုစုအမျိုးအစားရွေးချယ်ပါ',
        'please_select_dates' => 'စတင်သည့်နေ့စွဲနှင့်အဆုံးသတ်နေ့စွဲရွေးချယ်ပါ',
        'reason_too_short' => 'ကြောင်းခြင်းသည်အနည်းဆုံး 10 လက္ခဏာရှိရမည်',
        'end_date_after_start_date' => 'အဆုံးသတ်နေ့စွဲသည်စတင်သည့်နေ့စွဲနောက်တွင်ရှိရမည်',
        'confirm_submit' => 'ဤအငြိုးပြုစုတောင်းခံမှုတင်သွင်းရန်သေချာပါသလား?',
        'error_occurred' => 'အမှားအယွင်းပေါ်ပေါက်ခြင်း:',
        'view_my_requests' => 'ကျွန်ုပ်၏တောင်းခံများကိုကြည့်ရှုမည် →',
        'failed_to_submit' => 'အငြိုးပြုစုတောင်းခံမှုတင်သွင်းခြင်းမအောင်မြင်',
    ]
];

// Get current language strings
$t = $translations[$current_lang] ?? $translations['th'];

// Leave type mapping
$leave_types = [
    'Sick Leave' => ['th' => 'ลาป่วย', 'en' => 'Sick Leave', 'my' => 'ကျန်းမာရေးအငြိုးပြုစု'],
    'Annual Leave' => ['th' => 'ลาพักร้อน', 'en' => 'Annual Leave', 'my' => 'နှစ်စဉ်အငြိုးပြုစု'],
    'Personal Leave' => ['th' => 'ลากิจ', 'en' => 'Personal Leave', 'my' => 'ကိုယ်ရေးအငြိုးပြုစု'],
    'Maternity Leave' => ['th' => 'ลาคลอด', 'en' => 'Maternity Leave', 'my' => 'သူမင်းဖွားကင်းအငြိုးပြုစု'],
    'Paternity Leave' => ['th' => 'ลาบวช', 'en' => 'Paternity Leave', 'my' => 'သူကြီးဖွားကင်းအငြိုးပြုစု'],
    'Unpaid Leave' => ['th' => 'ลาไม่รับค่าจ้าง', 'en' => 'Unpaid Leave', 'my' => 'ခမဲ့အငြိုးပြုစု'],
    'Other' => ['th' => 'อื่นๆ', 'en' => 'Other', 'my' => 'အခြား']
];

ensure_session_started();
$user_id = $_SESSION['user_id'];

// Fetch employee data with JOIN to master tables (เหมือน ID Card)
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
if (!$stmt) {
    die("Database error: " . $conn->error);
}
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$employee = $result->fetch_assoc();
$stmt->close();
$conn->close();

if (!$employee) {
    echo "Error: Employee data not found";
    exit();
}

// ตอนนี้ $employee มี 'position_name' แล้ว!

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
        $stmt->close();
        $conn->close();
        header("Location: " . BASE_PATH . "/views/employee/my_requests.php?request_type=leave&success=1");
        exit();
    } else {
        $message = $t['failed_to_submit'];
        $message_type = 'error';
    }
    
    $stmt->close();
    $conn->close();
}

// Get display name
$display_name = $current_lang === 'en' ? ($employee['full_name_en'] ?? 'Unknown') : ($employee['full_name_th'] ?? 'Unknown');

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>

<div class="lg:ml-64">
    <div class="container mx-auto px-4 py-6 max-w-4xl">

        <!-- Error Alert Container -->
        <div id="alertContainer">
            <?php if ($message): ?>
                <div class="mb-6 p-4 bg-red-50 dark:bg-red-900 border border-red-200 dark:border-red-700 text-red-800 dark:text-red-200 rounded-lg flex items-start gap-3">
                    <svg class="w-6 h-6 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div class="flex-1"><?php echo htmlspecialchars($message); ?></div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Page Header -->
        <div class="mb-8 bg-gradient-to-r from-green-600 to-teal-600 rounded-lg shadow-md p-6">
            <div class="flex items-center gap-4">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <div>
                    <h1 class="text-3xl font-bold text-white"><?php echo $t['page_title']; ?></h1>
                    <p class="text-teal-100 text-sm mt-1"><?php echo $t['page_subtitle']; ?></p>
                </div>
            </div>
        </div>

        <!-- Main Form Card -->
        <div class="<?php echo $card_bg; ?> rounded-lg shadow-md border <?php echo $border_class; ?> p-6">
            <form method="POST" action="" id="leaveForm">

                <!-- Employee Information Section -->
                <div class="mb-8 pb-8 border-b <?php echo $border_class; ?>">
                    <h2 class="text-lg font-bold <?php echo $text_class; ?> mb-6 flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        <?php echo $t['employee_information']; ?>
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <!-- Employee ID -->
                        <div>
                            <label class="block text-sm font-medium <?php echo $is_dark ? 'text-gray-300' : 'text-gray-700'; ?> mb-2"><?php echo $t['employee_id']; ?></label>
                            <input type="text" value="<?php echo htmlspecialchars($employee['employee_id']); ?>" readonly
                                class="w-full px-4 py-2 border rounded-lg <?php echo $input_class; ?> opacity-75 cursor-not-allowed">
                        </div>

                        <!-- Employee Name -->
                        <div>
                            <label class="block text-sm font-medium <?php echo $is_dark ? 'text-gray-300' : 'text-gray-700'; ?> mb-2"><?php echo $t['name']; ?></label>
                            <input type="text" value="<?php echo htmlspecialchars($display_name); ?>" readonly
                                class="w-full px-4 py-2 border rounded-lg <?php echo $input_class; ?> opacity-75 cursor-not-allowed">
                        </div>

                        <!-- Position -->
                        <div>
                            <label class="block text-sm font-medium <?php echo $is_dark ? 'text-gray-300' : 'text-gray-700'; ?> mb-2"><?php echo $t['position']; ?></label>
                            <input type="text" value="<?php echo htmlspecialchars($employee['position_name'] ?? ''); ?>" readonly
                                class="w-full px-4 py-2 border rounded-lg <?php echo $input_class; ?> opacity-75 cursor-not-allowed">
                        </div>
                    </div>
                </div>

                <!-- Leave Type Selection -->
                <div class="mb-8">
                    <label for="leave_type" class="block text-sm font-bold <?php echo $text_class; ?> mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <?php echo $t['leave_type']; ?> <span class="text-red-500">*</span>
                    </label>
                    <select id="leave_type" name="leave_type" required
                        class="w-full px-4 py-3 border rounded-lg <?php echo $input_class; ?> focus:outline-none focus:ring-2 focus:ring-green-500">
                        <option value=""><?php echo $t['select_leave_type']; ?></option>
                        <?php foreach ($leave_types as $key => $langs):
                            $label = $langs[$current_lang] ?? $langs['en'];
                        ?>
                            <option value="<?php echo $key; ?>"><?php echo htmlspecialchars($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Date Range -->
                <div class="mb-8">
                    <label class="block text-sm font-bold <?php echo $text_class; ?> mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <?php echo $t['start_date']; ?> / <?php echo $t['end_date']; ?> <span class="text-red-500">*</span>
                    </label>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm <?php echo $is_dark ? 'text-gray-300' : 'text-gray-600'; ?> block mb-2"><?php echo $t['start_date']; ?></label>
                            <input type="date" id="start_date" name="start_date" required
                                min="<?php echo date('Y-m-d'); ?>"
                                onchange="calculateDays()"
                                class="w-full px-4 py-3 border rounded-lg <?php echo $input_class; ?> focus:outline-none focus:ring-2 focus:ring-green-500">
                        </div>
                        <div>
                            <label class="text-sm <?php echo $is_dark ? 'text-gray-300' : 'text-gray-600'; ?> block mb-2"><?php echo $t['end_date']; ?></label>
                            <input type="date" id="end_date" name="end_date" required
                                min="<?php echo date('Y-m-d'); ?>"
                                onchange="calculateDays()"
                                class="w-full px-4 py-3 border rounded-lg <?php echo $input_class; ?> focus:outline-none focus:ring-2 focus:ring-green-500">
                        </div>
                    </div>
                </div>

                <!-- Total Days Display -->
                <div id="totalDaysDisplay" class="mb-8 p-4 bg-green-50 dark:bg-green-900 rounded-lg border border-green-200 dark:border-green-700 hidden">
                    <p class="text-sm <?php echo $is_dark ? 'text-green-300' : 'text-green-800'; ?>">
                        <strong><?php echo $t['total_days']; ?>:</strong> <span id="totalDaysText" class="text-lg font-bold text-green-600 dark:text-green-400">0</span> <?php echo $t['days']; ?>
                    </p>
                </div>

                <!-- Reason Field -->
                <div class="mb-8">
                    <label class="block text-sm font-bold <?php echo $text_class; ?> mb-2 flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                        <?php echo $t['reason']; ?> <span class="text-red-500">*</span>
                    </label>
                    <textarea id="reason" name="reason" required rows="5"
                        placeholder="<?php echo $t['reason_placeholder']; ?>"
                        minlength="10"
                        class="w-full px-4 py-3 border rounded-lg <?php echo $input_class; ?> focus:outline-none focus:ring-2 focus:ring-green-500 resize-none"></textarea>
                </div>

                <!-- Important Notice -->
                <div class="mb-8 p-4 bg-yellow-50 dark:bg-yellow-900 border-l-4 border-yellow-400 rounded">
                    <div class="flex gap-3">
                        <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-bold text-yellow-800 dark:text-yellow-300"><?php echo $t['important_notice']; ?></p>
                            <ul class="text-sm text-yellow-700 dark:text-yellow-400 mt-2 space-y-1">
                                <li><?php echo $t['notice_1']; ?></li>
                                <li><?php echo $t['notice_2']; ?></li>
                                <li><?php echo $t['notice_3']; ?></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex flex-col md:flex-row gap-4 pt-6 border-t <?php echo $border_class; ?>">
                    <a href="<?php echo BASE_PATH; ?>/index.php" class="flex-1 px-6 py-3 border rounded-lg <?php echo $border_class; ?> <?php echo $text_class; ?> hover:<?php echo $is_dark ? 'bg-gray-700' : 'bg-gray-50'; ?> transition font-medium text-center">
                        <?php echo $t['cancel']; ?>
                    </a>
                    <button type="submit" class="flex-1 px-8 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg transition font-medium shadow-md hover:shadow-lg">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8m0 8l-6-2m6 2l6-2"></path>
                        </svg>
                        <?php echo $t['submit_request']; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>

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
    document.getElementById('leaveForm')?.addEventListener('submit', function(e) {
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
    document.getElementById('start_date')?.addEventListener('change', function() {
        document.getElementById('end_date').min = this.value;
    });
</script>