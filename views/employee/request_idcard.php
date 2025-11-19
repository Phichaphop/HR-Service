<?php

/**
 * Request ID Card Form - UPDATED UI VERSION
 * ✅ Standardized Layout Structure (Matches all request forms)
 * ✅ Improved Spacing and Typography
 */
require_once __DIR__ . '/../../config/db_config.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../models/Employee.php';
require_once __DIR__ . '/../../db/Localization.php';

AuthController::requireAuth();

// Ensure session started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get language and theme settings
$current_lang = $_SESSION['language'] ?? 'th';
$theme_mode = $_SESSION['theme_mode'] ?? 'light';
$is_dark = ($theme_mode === 'dark');
$text_class = $is_dark ? 'text-white' : 'text-gray-900';
$card_bg = $is_dark ? 'bg-gray-800' : 'bg-white';
$border_class = $is_dark ? 'border-gray-700' : 'border-gray-200';
$input_class = $is_dark ? 'bg-gray-700 border-gray-600 text-white placeholder-gray-400' : 'bg-white border-gray-300 text-gray-900 placeholder-gray-500';

// Multilingual Content Array
$translations = [
    'th' => [
        'page_title' => 'ขอบัตรพนักงาน',
        'page_subtitle' => 'ขอบัตรพนักงานใหม่หรือการเปลี่ยน',
        'employee_information' => 'ข้อมูลพนักงาน',
        'employee_id' => 'รหัสพนักงาน',
        'employee_name' => 'ชื่อพนักงาน',
        'position' => 'ตำแหน่ง',
        'department' => 'แผนก',
        'request_reason' => 'เหตุผลในการขอ',
        'select_reason' => 'โปรดเลือกเหตุผล',
        'information_update' => 'อัปเดตข้อมูล',
        'information_update_desc' => 'ข้อมูลของฉันเปลี่ยนแปลงไป (ตำแหน่ง, รูปถ่าย ฯลฯ)',
        'lost_id_card' => 'บัตรพนักงานหาย',
        'lost_id_card_desc' => 'ฉันสูญหายบัตรพนักงานของฉันและต้องการการเปลี่ยน',
        'damaged_id_card' => 'บัตรพนักงานเสียหาย',
        'damaged_id_card_desc' => 'บัตรพนักงานของฉันเสียหายและต้องการการเปลี่ยน',
        'first_time_issue' => 'ครั้งแรก',
        'first_time_issue_desc' => 'ฉันเป็นพนักงานใหม่ขอบัตรพนักงานฉบับแรก',
        'important_notice' => 'ประกาศสำคัญ',
        'processing_time' => '⏱ เวลาในการประมวลผล: 5-7 วันทำการ',
        'photo_required' => '📷 คุณอาจจำเป็นต้องให้รูปถ่ายสำหรับบัตรใหม่',
        'replacement_fee' => '💰 บัตรที่หายอาจมีค่าใช้จ่ายในการเปลี่ยน',
        'return_old_card' => '🔄 ส่งบัตรเก่าที่เสียหายเมื่อเก็บบัตรใหม่',
        'submit_request' => 'ส่งคำขอ',
        'cancel' => 'ยกเลิก',
        'success_message' => 'ส่งคำขอบัตรพนักงานเรียบร้อยแล้ว!',
        'error_message' => 'ไม่สามารถส่งคำขอได้',
        'select_reason_alert' => 'โปรดเลือกเหตุผลในการขอ',
        'confirmation' => 'คุณแน่ใจหรือไม่ที่จะส่งคำขอบัตรพนักงาน?',
        'view_my_requests' => 'ดูคำขอของฉัน →',
        'data_not_found' => 'ไม่พบข้อมูลพนักงาน',
        'required' => 'จำเป็น',
    ],
    'en' => [
        'page_title' => 'Request ID Card',
        'page_subtitle' => 'Request new or replacement ID card',
        'employee_information' => 'Employee Information',
        'employee_id' => 'Employee ID',
        'employee_name' => 'Employee Name',
        'position' => 'Position',
        'department' => 'Department',
        'request_reason' => 'Request Reason',
        'select_reason' => 'Please select a reason',
        'information_update' => 'Information Update',
        'information_update_desc' => 'My information has changed (position, photo, etc.)',
        'lost_id_card' => 'Lost ID Card',
        'lost_id_card_desc' => 'I have lost my ID card and need a replacement',
        'damaged_id_card' => 'Damaged ID Card',
        'damaged_id_card_desc' => 'My ID card is damaged and needs replacement',
        'first_time_issue' => 'First Time Issue',
        'first_time_issue_desc' => 'I am a new employee requesting my first ID card',
        'important_notice' => 'Important Notice',
        'processing_time' => '⏱ Processing time: 5-7 business days',
        'photo_required' => '📷 You may need to provide a photo for new cards',
        'replacement_fee' => '💰 Lost card may incur a replacement fee',
        'return_old_card' => '🔄 Return old damaged card when collecting new one',
        'submit_request' => 'Submit Request',
        'cancel' => 'Cancel',
        'success_message' => 'ID Card request submitted successfully!',
        'error_message' => 'Failed to submit request',
        'select_reason_alert' => 'Please select a reason for your request',
        'confirmation' => 'Are you sure you want to submit this ID card request?',
        'view_my_requests' => 'View my requests →',
        'data_not_found' => 'Employee data not found',
        'required' => 'Required',
    ],
    'my' => [
        'page_title' => 'အလုပ်သမားအိုင်ဒီကဒ်တောင်းဆိုခြင်း',
        'page_subtitle' => 'အလုပ်သမားအိုင်ဒီကဒ်သစ် သို့မဟုတ် အစားထိုးတောင်းဆိုခြင်း',
        'employee_information' => 'အလုပ်သမားအချက်အလက်',
        'employee_id' => 'အလုပ်သမားအိုင်ဒီ',
        'employee_name' => 'အလုပ်သမားအမည်',
        'position' => 'အနေအထားရာထူးခန်း',
        'department' => 'ဌာန',
        'request_reason' => 'တောင်းဆိုသည့် အကြောင်းအရာ',
        'select_reason' => 'အကြောင်းအရာကို ရွေးချယ်နိုင်သည်',
        'information_update' => 'အချက်အလက်အဆင့်မြှင့်တင်ခြင်း',
        'information_update_desc' => 'ကျွန်ုပ်၏အချက်အလက်ပြောင်းလဲသွားသည်',
        'lost_id_card' => 'သံုးခဲ့သောအိုင်ဒီကဒ်',
        'lost_id_card_desc' => 'ကျွန်ုပ်၏အိုင်ဒီကဒ်သံုးခဲ့ကြ၏အစားထိုးရန်လိုအပ်',
        'damaged_id_card' => 'ပျက်စီးသွားသောအိုင်ဒီကဒ်',
        'damaged_id_card_desc' => 'ကျွန်ုပ်၏အိုင်ဒီကဒ်ပျက်စီးပြီးအစားထိုးရန်လိုအပ်',
        'first_time_issue' => 'ပထမဆုံးအကြိမ်',
        'first_time_issue_desc' => 'ကျွန်ုပ်သည်အလုပ်သမားအသစ်ဖြစ်ပြီးပထမဆုံးအိုင်ဒီကဒ်တောင်းဆိုနေ',
        'important_notice' => 'အရေးကြီးသောအသိပေးချက်',
        'processing_time' => '⏱ အချိန်သုံးစွဲမှု - အလုပ်တစ်ရက် 5-7 ရက်',
        'photo_required' => '📷 လူတစ်ခုလုံးအတွက်ဓာတ်ပုံထည့်သွင်းခြင်းလိုအပ်',
        'replacement_fee' => '💰 သံုးခဲ့သောကဒ်များသည်အစားထိုးခ္ခငွေပေးဆောင်ရန်လိုအပ်',
        'return_old_card' => '🔄 ကဒ်အသစ်ယူသောအခါစကားနှင့်ပျက်စီးသောကဒ်အဟောင်းများပြန်ပေး',
        'submit_request' => 'တောင်းဆိုချက်ပိုပြီးပို့ဆောင်ခြင်း',
        'cancel' => 'ပယ်ဖျက်ခြင်း',
        'success_message' => 'အိုင်ဒီကဒ်တောင်းဆိုချက်အောင်မြင်စွာ ပို့ဆောင်ပြီး',
        'error_message' => 'တောင်းဆိုချက်ပိုပြီးပို့ဆောင်မရ',
        'select_reason_alert' => 'သင့်တောင်းဆိုချက်အတွက်အကြောင်းအရာကိုရွေးချယ်ပါ',
        'confirmation' => 'ဤအိုင်ဒီကဒ်တောင်းဆိုချက်ကိုပိုပြီးပို့ဆောင်ရန်သေချာပါသလား',
        'view_my_requests' => 'ကျွန်ုပ်၏တောင်းဆိုချက်များကိုကြည့်ရှုခြင်း →',
        'data_not_found' => 'အလုပ်သမားအချက်အလက်မတွေ့ရှိ',
        'required' => 'လိုအပ်ခြင်း',
    ]
];

// Get current translations
$t = $translations[$current_lang] ?? $translations['en'];

ensure_session_started();
$user_id = $_SESSION['user_id'];

// Fetch employee data with JOIN to master tables
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

// Check if employee data exists
if (!$employee) {
    echo "Error: " . $t['data_not_found'];
    exit();
}

$message = '';
$message_type = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = getDbConnection();
    $reason = $_POST['reason'] ?? '';

    $stmt = $conn->prepare("INSERT INTO id_card_requests (employee_id, reason, status, created_at, updated_at) VALUES (?, ?, 'New', NOW(), NOW())");
    $stmt->bind_param("ss", $user_id, $reason);

    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        header("Location: " . BASE_PATH . "/views/employee/my_requests.php?request_type=id_card&success=1");
        exit();
    } else {
        $message = $t['error_message'];
        $message_type = 'error';
    }

    $stmt->close();
    $conn->close();
}

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>

<div class="lg:ml-64">
    <div class="container mx-auto px-4 py-6 max-w-4xl">

        <!-- Error Alert Container -->
        <div id="alertContainer">
            <?php if ($message): ?>
                <div class="mb-6 p-4 <?php echo $message_type === 'error' ? 'bg-red-50 dark:bg-red-900 border border-red-200 dark:border-red-700 text-red-800 dark:text-red-200' : 'bg-green-50 dark:bg-green-900 border border-green-200 dark:border-green-700 text-green-800 dark:text-green-200'; ?> rounded-lg flex items-start gap-3">
                    <svg class="w-6 h-6 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div class="flex-1"><?php echo htmlspecialchars($message); ?></div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Page Header -->
        <div class="mb-8 bg-gradient-to-r from-purple-600 to-purple-700 rounded-lg shadow-md p-6">
            <div class="flex items-center gap-4">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path>
                </svg>
                <div>
                    <h1 class="text-3xl font-bold text-white"><?php echo $t['page_title']; ?></h1>
                    <p class="text-purple-100 text-sm mt-1"><?php echo $t['page_subtitle']; ?></p>
                </div>
            </div>
        </div>

        <!-- Main Form Card -->
        <div class="<?php echo $card_bg; ?> rounded-lg shadow-md border <?php echo $border_class; ?> p-6">
            <form method="POST" action="" id="idcardForm">

                <!-- Employee Info Section -->
                <div class="mb-8 pb-8 border-b <?php echo $border_class; ?>">
                    <h2 class="text-lg font-bold <?php echo $text_class; ?> mb-6 flex items-center gap-2">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        <?php echo $t['employee_information']; ?>
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Employee ID -->
                        <div>
                            <label class="block text-sm font-medium <?php echo $is_dark ? 'text-gray-300' : 'text-gray-700'; ?> mb-2">
                                <?php echo $t['employee_id']; ?>
                            </label>
                            <input type="text" readonly value="<?php echo htmlspecialchars($employee['employee_id'] ?? 'N/A'); ?>"
                                class="w-full px-4 py-2 border rounded-lg <?php echo $input_class; ?> opacity-75 cursor-not-allowed">
                        </div>

                        <!-- Employee Name -->
                        <div>
                            <label class="block text-sm font-medium <?php echo $is_dark ? 'text-gray-300' : 'text-gray-700'; ?> mb-2">
                                <?php echo $t['employee_name']; ?>
                            </label>
                            <input type="text" readonly value="<?php echo htmlspecialchars($employee['full_name'] ?? 'N/A'); ?>"
                                class="w-full px-4 py-2 border rounded-lg <?php echo $input_class; ?> opacity-75 cursor-not-allowed">
                        </div>

                        <!-- Position -->
                        <div>
                            <label class="block text-sm font-medium <?php echo $is_dark ? 'text-gray-300' : 'text-gray-700'; ?> mb-2">
                                <?php echo $t['position']; ?>
                            </label>
                            <input type="text" readonly value="<?php echo htmlspecialchars($employee['position_name'] ?? 'N/A'); ?>"
                                class="w-full px-4 py-2 border rounded-lg <?php echo $input_class; ?> opacity-75 cursor-not-allowed">
                        </div>

                        <!-- Department -->
                        <div>
                            <label class="block text-sm font-medium <?php echo $is_dark ? 'text-gray-300' : 'text-gray-700'; ?> mb-2">
                                <?php echo $t['department']; ?>
                            </label>
                            <input type="text" readonly value="<?php echo htmlspecialchars($employee['department_name'] ?? 'N/A'); ?>"
                                class="w-full px-4 py-2 border rounded-lg <?php echo $input_class; ?> opacity-75 cursor-not-allowed">
                        </div>
                    </div>
                </div>

                <!-- Request Reason Section -->
                <div class="mb-8">
                    <label class="block text-sm font-bold <?php echo $text_class; ?> mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <?php echo $t['request_reason']; ?> <span class="text-red-500">*</span>
                    </label>

                    <div class="space-y-3">
                        <!-- First Time Issue -->
                        <label class="flex items-start p-4 border-2 <?php echo $is_dark ? 'border-gray-600 hover:border-purple-500 hover:bg-gray-700' : 'border-gray-200 hover:border-purple-500 hover:bg-purple-50'; ?> rounded-lg cursor-pointer transition">
                            <input type="radio" name="reason" value="First Time Issue" required class="mt-1 w-4 h-4 text-purple-600">
                            <div class="ml-4 flex-1">
                                <span class="font-medium <?php echo $text_class; ?>">
                                    <?php echo $t['first_time_issue']; ?>
                                </span>
                                <p class="text-sm <?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?> mt-1">
                                    <?php echo $t['first_time_issue_desc']; ?>
                                </p>
                            </div>
                        </label>

                        <!-- Information Update -->
                        <label class="flex items-start p-4 border-2 <?php echo $is_dark ? 'border-gray-600 hover:border-purple-500 hover:bg-gray-700' : 'border-gray-200 hover:border-purple-500 hover:bg-purple-50'; ?> rounded-lg cursor-pointer transition">
                            <input type="radio" name="reason" value="Information Update" required class="mt-1 w-4 h-4 text-purple-600">
                            <div class="ml-4 flex-1">
                                <span class="font-medium <?php echo $text_class; ?>">
                                    <?php echo $t['information_update']; ?>
                                </span>
                                <p class="text-sm <?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?> mt-1">
                                    <?php echo $t['information_update_desc']; ?>
                                </p>
                            </div>
                        </label>

                        <!-- Lost ID Card -->
                        <label class="flex items-start p-4 border-2 <?php echo $is_dark ? 'border-gray-600 hover:border-purple-500 hover:bg-gray-700' : 'border-gray-200 hover:border-purple-500 hover:bg-purple-50'; ?> rounded-lg cursor-pointer transition">
                            <input type="radio" name="reason" value="Lost ID Card" required class="mt-1 w-4 h-4 text-purple-600">
                            <div class="ml-4 flex-1">
                                <span class="font-medium <?php echo $text_class; ?>">
                                    <?php echo $t['lost_id_card']; ?>
                                </span>
                                <p class="text-sm <?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?> mt-1">
                                    <?php echo $t['lost_id_card_desc']; ?>
                                </p>
                            </div>
                        </label>

                        <!-- Damaged ID Card -->
                        <label class="flex items-start p-4 border-2 <?php echo $is_dark ? 'border-gray-600 hover:border-purple-500 hover:bg-gray-700' : 'border-gray-200 hover:border-purple-500 hover:bg-purple-50'; ?> rounded-lg cursor-pointer transition">
                            <input type="radio" name="reason" value="Damaged ID Card" required class="mt-1 w-4 h-4 text-purple-600">
                            <div class="ml-4 flex-1">
                                <span class="font-medium <?php echo $text_class; ?>">
                                    <?php echo $t['damaged_id_card']; ?>
                                </span>
                                <p class="text-sm <?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?> mt-1">
                                    <?php echo $t['damaged_id_card_desc']; ?>
                                </p>
                            </div>
                        </label>
                    </div>
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
                                <li><?php echo $t['processing_time']; ?></li>
                                <li><?php echo $t['photo_required']; ?></li>
                                <li><?php echo $t['replacement_fee']; ?></li>
                                <li><?php echo $t['return_old_card']; ?></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex flex-col md:flex-row gap-4 pt-6 border-t <?php echo $border_class; ?>">
                    <a href="<?php echo BASE_PATH; ?>/index.php" class="flex-1 px-6 py-3 border rounded-lg <?php echo $border_class; ?> <?php echo $text_class; ?> hover:<?php echo $is_dark ? 'bg-gray-700' : 'bg-gray-50'; ?> transition font-medium text-center">
                        <?php echo $t['cancel']; ?>
                    </a>
                    <button type="submit" class="flex-1 px-8 py-3 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition font-medium shadow-md hover:shadow-lg">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path>
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
    document.getElementById('idcardForm').addEventListener('submit', function(e) {
        const reason = document.querySelector('input[name="reason"]:checked');

        if (!reason) {
            e.preventDefault();
            alert('<?php echo addslashes($t['select_reason_alert']); ?>');
            return;
        }

        if (!confirm('<?php echo addslashes($t['confirmation']); ?>')) {
            e.preventDefault();
        }
    });
</script>