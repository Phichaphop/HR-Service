<?php

/**
 * Request Certificate Form - Complete Version with Multi-Language Support
 * Supports: Thai (ไทย), English (EN), Myanmar (မြန်မာ)
 * Employee can request certificate with type selection
 */

require_once __DIR__ . '/../../config/db_config.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
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
        'page_title' => 'ขอหนังสือรับรอง',
        'page_subtitle' => 'ยื่นคำขอหนังสือรับรองจากบริษัท',
        'request_certificate' => 'ขอหนังสือรับรอง',
        'submitted_successfully' => 'ส่งคำขอเรียบร้อยแล้ว! เลขที่:',
        'error_occurred' => 'เกิดข้อผิดพลาด:',
        'please_select_type' => 'กรุณาเลือกประเภทหนังสือรับรอง',
        'view_my_requests' => 'ดูคำขอของฉัน →',
        'employee_information' => 'ข้อมูลพนักงาน',
        'employee_id' => 'รหัสพนักงาน',
        'full_name' => 'ชื่อ-นามสกุล',
        'position' => 'ตำแหน่ง',
        'division' => 'สังกัด',
        'department' => 'แผนก',
        'date_of_hire' => 'วันที่เข้าทำงาน',
        'hiring_type' => 'ประเภทการจ้าง',
        'certificate_type' => 'ประเภทหนังสือรับรอง',
        'no_types_available' => 'ยังไม่มีประเภทหนังสือรับรองในระบบ',
        'purpose' => 'วัตถุประสงค์',
        'purpose_placeholder' => 'ระบุวัตถุประสงค์ในการขอหนังสือรับรอง (เช่น เพื่อทำวีซ่า, เพื่อกู้เงิน, ฯลฯ)',
        'submit_request' => 'ส่งคำขอ',
        'cancel' => 'ยกเลิก',
        'required' => 'จำเป็น',
        'base_salary' => 'เงินเดือนพื้นฐาน',
        'section' => 'สายงาน',
    ],
    'en' => [
        'page_title' => 'Request Certificate',
        'page_subtitle' => 'Submit your certificate request from the company',
        'request_certificate' => 'Request Certificate',
        'submitted_successfully' => 'Certificate request submitted successfully! Reference Number:',
        'error_occurred' => 'An error occurred:',
        'please_select_type' => 'Please select a certificate type',
        'view_my_requests' => 'View my requests →',
        'employee_information' => 'Employee Information',
        'employee_id' => 'Employee ID',
        'full_name' => 'Full Name',
        'position' => 'Position',
        'division' => 'Division',
        'department' => 'Department',
        'date_of_hire' => 'Date of Hire',
        'hiring_type' => 'Hiring Type',
        'certificate_type' => 'Certificate Type',
        'no_types_available' => 'No certificate types available in the system',
        'purpose' => 'Purpose',
        'purpose_placeholder' => 'Specify the purpose for requesting this certificate (e.g., for visa, for loan, etc.)',
        'submit_request' => 'Submit Request',
        'cancel' => 'Cancel',
        'required' => 'Required',
        'base_salary' => 'Base Salary',
        'section' => 'Section',
    ],
    'my' => [
        'page_title' => 'လက်မှတ်တောင်းခံ',
        'page_subtitle' => 'ကုမ္ပဏီမှလက်မှတ်တောင်းခံမှုတင်သွင်းမည်',
        'request_certificate' => 'လက်မှတ်တောင်းခံ',
        'submitted_successfully' => 'လက်မှတ်တောင်းခံမှုအောင်မြင်စွာတင်သွင်းခြင်း! ကိုးကားကုဒ်:',
        'error_occurred' => 'အမှားအယွင်းပေါ်ပေါက်ခြင်း:',
        'please_select_type' => 'လက်မှတ်အမျိုးအစားရွေးချယ်ပါ',
        'view_my_requests' => 'ကျွန်ုပ်၏တောင်းခံများကိုကြည့်ရှုမည် →',
        'employee_information' => 'အလုပ်သမားအချက်အလက်',
        'employee_id' => 'အလုပ်သမားအိုင်ဒီ',
        'full_name' => 'အမည်အပြည့်အစုံ',
        'position' => 'အနေအထား',
        'division' => 'ဌာန',
        'department' => 'ခွဲခြင်း',
        'date_of_hire' => 'ခန်းခြင်းနေ့စွဲ',
        'hiring_type' => 'ခန်းခြင်းအမျိုးအစား',
        'certificate_type' => 'လက်မှတ်အမျိုးအစား',
        'no_types_available' => 'စနစ်တွင်လက်မှတ်အမျိုးအစားမရှိ',
        'purpose' => 'ရည်ရွယ်ချက်',
        'purpose_placeholder' => 'လက်မှတ်တောင်းခံရန်ရည်ရွယ်ချက်ကိုသတ်မှတ်ပါ (ဥပမာ - ဗီအိုအ, ချေးငွေအတွက်)',
        'submit_request' => 'တောင်းခံမှုတင်သွင်းမည်',
        'cancel' => 'ပယ်ဖျက်မည်',
        'required' => 'လိုအပ်ခြင်း',
        'base_salary' => 'အခြေခံအခtime',
        'section' => 'အပိုင်းခွဲ',
    ]
];

// Get current language strings
$t = $translations[$current_lang] ?? $translations['th'];

$page_title = $t['page_title'];
ensure_session_started();

$conn = getDbConnection();

// Get employee info with multi-language support
$emp_sql = "SELECT e.*, 
 COALESCE(p.position_name_" . ($current_lang === 'en' ? 'en' : 'th') . ", p.position_name_th) as position_name,
 COALESCE(d.division_name_" . ($current_lang === 'en' ? 'en' : 'th') . ", d.division_name_th) as division_name,
 COALESCE(dep.department_name_" . ($current_lang === 'en' ? 'en' : 'th') . ", dep.department_name_th) as department_name,
 COALESCE(sec.section_name_" . ($current_lang === 'en' ? 'en' : 'th') . ", sec.section_name_th) as section_name,
 COALESCE(ht.type_name_" . ($current_lang === 'en' ? 'en' : 'th') . ", ht.type_name_th) as hiring_type_name
 FROM employees e
 LEFT JOIN position_master p ON e.position_id = p.position_id
 LEFT JOIN division_master d ON e.division_id = d.division_id
 LEFT JOIN department_master dep ON e.department_id = dep.department_id
 LEFT JOIN section_master sec ON e.section_id = sec.section_id
 LEFT JOIN hiring_type_master ht ON e.hiring_type_id = ht.hiring_type_id
 WHERE e.employee_id = ?";

$emp_stmt = $conn->prepare($emp_sql);
$emp_stmt->bind_param("s", $user_id);
$emp_stmt->execute();
$employee = $emp_stmt->get_result()->fetch_assoc();
$emp_stmt->close();

// Get active certificate types with multi-language support
$cert_types = [];
$types_sql = "SELECT cert_type_id, 
 COALESCE(type_name_" . ($current_lang === 'en' ? 'en' : 'th') . ", type_name_th) as type_name,
 type_name_th, type_name_en, type_name_my
 FROM certificate_types 
 WHERE is_active = 1 
 ORDER BY cert_type_id";
$types_result = $conn->query($types_sql);
while ($row = $types_result->fetch_assoc()) {
    $cert_types[] = $row;
}

// Handle form submission
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cert_type_id = $_POST['cert_type_id'] ?? '';
    $purpose = $_POST['purpose'] ?? '';

    if (empty($cert_type_id)) {
        $error = $t['please_select_type'];
    } else {
        // Generate certificate number
        $cert_no = 'CERT-' . date('Ymd') . '-' . rand(1000, 9999);

        $insert_sql = "INSERT INTO certificate_requests 
(
 certificate_no,
 employee_id,
 cert_type_id,
 employee_name, 
 position, 
 division, 
 date_of_hire, 
 hiring_type, 
 base_salary,
 purpose,
 status,
 created_at,
 updated_at
)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'New', NOW(), NOW())";
        $insert_stmt = $conn->prepare($insert_sql);
        $stmt->bind_param("ssis", $cert_no, $user_id, $cert_type_id, $purpose);
        $stmt->bind_param(
            'ssissssdss',
            $cert_no,
            $user_id,
            $cert_type_id,
            $employee_name,
            $position,
            $division,
            $date_of_hire,
            $hiring_type,
            $base_salary,
            $purpose
        );

        if ($insert_stmt->execute()) {
            $success = $t['submitted_successfully'] . ' ' . $cert_no;
        } else {
            $error = $t['error_occurred'] . ' ' . $insert_stmt->error;
        }
        $insert_stmt->close();
    }
}

$conn->close();

// Get display name
$display_name = $current_lang === 'en' ? ($employee['full_name_en'] ?? $employee['full_name_th'] ?? 'Unknown') : ($employee['full_name_th'] ?? 'Unknown');

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>

<div class="flex-1 lg:ml-64 p-4 md:p-8 bg-gradient-to-br <?php echo $is_dark ? 'from-gray-900 via-gray-800 to-gray-900' : 'from-gray-50 via-gray-50 to-gray-100'; ?>">

    <!-- Page Header -->
    <div class="mb-8 bg-gradient-to-r from-green-600 to-teal-600 rounded-lg shadow-lg p-8">
        <div>
            <h1 class="text-4xl font-bold text-white mb-2"><?php echo $t['request_certificate']; ?></h1>
            <p class="text-green-100 text-lg"><?php echo $t['page_subtitle']; ?></p>
        </div>
    </div>

    <!-- Main Container -->
    <div class="max-w-4xl mx-auto">

        <!-- Success/Error Messages -->
        <div id="alertContainer"></div>

        <!-- Form Card -->
        <div class="<?php echo $card_bg; ?> rounded-lg shadow-lg p-8 border <?php echo $border_class; ?>">

            <form id="certificateForm" method="POST" action="#" onsubmit="submitCertificateRequest(event)">

                <!-- Employee Information Section (Read-Only) -->
                <div class="mb-8 pb-8 border-b <?php echo $border_class; ?>">
                    <h3 class="text-xl font-bold <?php echo $text_class; ?> mb-6 flex items-center">
                        <svg class="w-6 h-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        <?php echo $t['employee_information']; ?>
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <!-- Employee ID -->
                        <div>
                            <label class="block text-sm font-medium <?php echo $is_dark ? 'text-gray-300' : 'text-gray-700'; ?> mb-1"><?php echo $t['employee_id']; ?></label>
                            <input type="text" readonly value="<?php echo htmlspecialchars($employee['employee_id'] ?? ''); ?>" class="w-full px-4 py-2 border rounded-lg <?php echo $input_class; ?> opacity-75">
                        </div>

                        <!-- Full Name -->
                        <div>
                            <label class="block text-sm font-medium <?php echo $is_dark ? 'text-gray-300' : 'text-gray-700'; ?> mb-1"><?php echo $t['full_name']; ?></label>
                            <input type="text" readonly value="<?php echo htmlspecialchars($display_name ?? ''); ?>" class="w-full px-4 py-2 border rounded-lg <?php echo $input_class; ?> opacity-75">
                        </div>

                        <!-- Position -->
                        <div>
                            <label class="block text-sm font-medium <?php echo $is_dark ? 'text-gray-300' : 'text-gray-700'; ?> mb-1"><?php echo $t['position']; ?></label>
                            <input type="text" readonly value="<?php echo htmlspecialchars($employee['position_name'] ?? ''); ?>" class="w-full px-4 py-2 border rounded-lg <?php echo $input_class; ?> opacity-75">
                        </div>

                        <!-- Division -->
                        <div>
                            <label class="block text-sm font-medium <?php echo $is_dark ? 'text-gray-300' : 'text-gray-700'; ?> mb-1"><?php echo $t['division']; ?></label>
                            <input type="text" readonly value="<?php echo htmlspecialchars($employee['division_name'] ?? ''); ?>" class="w-full px-4 py-2 border rounded-lg <?php echo $input_class; ?> opacity-75">
                        </div>

                        <!-- Department -->
                        <div>
                            <label class="block text-sm font-medium <?php echo $is_dark ? 'text-gray-300' : 'text-gray-700'; ?> mb-1"><?php echo $t['department']; ?></label>
                            <input type="text" readonly value="<?php echo htmlspecialchars($employee['department_name'] ?? ''); ?>" class="w-full px-4 py-2 border rounded-lg <?php echo $input_class; ?> opacity-75">
                        </div>

                        <!-- Section -->
                        <div>
                            <label class="block text-sm font-medium <?php echo $is_dark ? 'text-gray-300' : 'text-gray-700'; ?> mb-1"><?php echo $t['section']; ?></label>
                            <input type="text" readonly value="<?php echo htmlspecialchars($employee['section_name'] ?? ''); ?>" class="w-full px-4 py-2 border rounded-lg <?php echo $input_class; ?> opacity-75">
                        </div>

                        <!-- Date of Hire -->
                        <div>
                            <label class="block text-sm font-medium <?php echo $is_dark ? 'text-gray-300' : 'text-gray-700'; ?> mb-1"><?php echo $t['date_of_hire']; ?></label>
                            <input type="text" readonly value="<?php echo isset($employee['date_of_hire']) ? date('d-m-Y', strtotime($employee['date_of_hire'])) : ''; ?>" class="w-full px-4 py-2 border rounded-lg <?php echo $input_class; ?> opacity-75">
                        </div>

                        <!-- Hiring Type -->
                        <div>
                            <label class="block text-sm font-medium <?php echo $is_dark ? 'text-gray-300' : 'text-gray-700'; ?> mb-1"><?php echo $t['hiring_type']; ?></label>
                            <input type="text" readonly value="<?php echo htmlspecialchars($employee['hiring_type_name'] ?? ''); ?>" class="w-full px-4 py-2 border rounded-lg <?php echo $input_class; ?> opacity-75">
                        </div>

                        <!-- Base Salary -->
                        <div>
                            <label class="block text-sm font-medium <?php echo $is_dark ? 'text-gray-300' : 'text-gray-700'; ?> mb-1"><?php echo $t['base_salary']; ?></label>
                            <input type="text" readonly value="<?php echo number_format($employee['base_salary'] ?? 0, 2); ?>" class="w-full px-4 py-2 border rounded-lg <?php echo $input_class; ?> opacity-75">
                        </div>
                    </div>
                </div>

                <!-- Certificate Type Selection -->
                <div class="mb-8">
                    <label class="block text-sm font-semibold <?php echo $text_class; ?> mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <?php echo $t['certificate_type']; ?> <span class="text-red-500 ml-1">*</span>
                    </label>

                    <?php if (empty($cert_types)): ?>
                        <div class="text-center py-8 <?php echo $is_dark ? 'bg-gray-700' : 'bg-gray-50'; ?> rounded-lg border-2 border-dashed <?php echo $border_class; ?>">
                            <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <p class="<?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?>"><?php echo $t['no_types_available']; ?></p>
                        </div>
                    <?php else: ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <?php foreach ($cert_types as $type): ?>
                                <label class="relative flex items-start p-4 border-2 rounded-lg cursor-pointer transition hover:border-blue-500 hover:<?php echo $is_dark ? 'bg-gray-700' : 'bg-blue-50'; ?> <?php echo $is_dark ? 'border-gray-600' : 'border-gray-300'; ?>">
                                    <input type="radio" name="cert_type_id" value="<?php echo $type['cert_type_id']; ?>" required class="mt-1 sr-only peer">
                                    <div class="flex-1 peer-checked:font-semibold">
                                        <div class="<?php echo $text_class; ?> font-medium mb-1">
                                            <?php echo htmlspecialchars($type['type_name']); ?>
                                        </div>
                                    </div>
                                    <svg class="w-5 h-5 text-blue-600 hidden peer-checked:block absolute right-3 top-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Purpose Field -->
                <div class="mb-8">
                    <label class="block text-sm font-semibold <?php echo $text_class; ?> mb-2 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                        <?php echo $t['purpose']; ?> <span class="text-red-500 ml-1">*</span>
                    </label>
                    <textarea name="purpose"
                        placeholder="<?php echo $t['purpose_placeholder']; ?>"
                        rows="4"
                        required
                        minlength="5"
                        class="w-full px-4 py-3 border rounded-lg <?php echo $input_class; ?> focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    <p class="text-xs <?php echo $is_dark ? 'text-gray-500' : 'text-gray-400'; ?> mt-1">ระบุวัตถุประสงค์อย่างชัดเจน (มากกว่า 5 คำ)</p>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col md:flex-row gap-4">
                    <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg transition font-semibold text-lg shadow-lg hover:shadow-xl flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <?php echo $t['submit_request']; ?>
                    </button>
                    <a href="<?php echo BASE_PATH; ?>/views/employee/my_requests.php" class="flex-1 bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg transition font-semibold text-lg text-center shadow-lg hover:shadow-xl">
                        <?php echo $t['cancel']; ?>
                    </a>
                </div>
            </form>
        </div>

        <!-- Info Section -->
        <div class="mt-6 p-4 <?php echo $is_dark ? 'bg-gray-800' : 'bg-blue-50'; ?> rounded-lg border-l-4 border-blue-500">
            <h4 class="font-semibold <?php echo $text_class; ?> mb-2">💡 <?php echo $current_lang === 'en' ? 'Information' : ($current_lang === 'my' ? 'အချက်အလက်' : 'ข้อมูล'); ?></h4>
            <p class="text-sm <?php echo $is_dark ? 'text-gray-300' : 'text-blue-900'; ?>">
                <?php
                if ($current_lang === 'en') {
                    echo 'Once submitted, your certificate request will be processed by the HR department. You will receive a reference number that you can use to track your request status.';
                } elseif ($current_lang === 'my') {
                    echo 'ထုတ်ယူထားပြီးနောက် သင့်လက်မှတ်တောင်းခံမှုကို HR ဌာနက ဆောင်ရွက်မည်ဖြစ်သည်။';
                } else {
                    echo 'หลังจากส่งคำขอแล้ว แผนกทรัพยากรบุคคลจะดำเนินการอนุมัติ คุณจะได้รับเลขที่อ้างอิงเพื่อใช้ในการติดตามสถานะคำขอ';
                }
                ?>
            </p>
        </div>
    </div>
</div>

<!-- Scripts for Form Submission -->
<script>
    const currentLang = '<?php echo $current_lang; ?>';
    const translations = {
        th: {
            error: 'เกิดข้อผิดพลาด:',
            success: 'ส่งคำขอเรียบร้อยแล้ว! เลขที่:',
            selectType: 'กรุณาเลือกประเภทหนังสือรับรอง',
            purposeRequired: 'กรุณากรอกวัตถุประสงค์อย่างชัดเจน',
        },
        en: {
            error: 'An error occurred:',
            success: 'Certificate request submitted successfully! Reference:',
            selectType: 'Please select a certificate type',
            purposeRequired: 'Please specify the purpose clearly',
        },
        my: {
            error: 'အမှားအယွင်းပေါ်ပေါက်ခြင်း:',
            success: 'လက်မှတ်တောင်းခံမှုအောင်မြင်စွာတင်သွင်းခြင်း! ကိုးကားကုဒ်:',
            selectType: 'လက်မှတ်အမျိုးအစားရွေးချယ်ပါ',
            purposeRequired: 'ရည်ရွယ်ချက်ကိုသတ်မှတ်ပါ',
        }
    };

    function showAlert(message, type = 'error') {
        const alertContainer = document.getElementById('alertContainer');
        const alertClass = type === 'success' ?
            'bg-green-50 border border-green-200 text-green-800' :
            'bg-red-50 border border-red-200 text-red-800';

        const alertHTML = `
 <div class="mb-6 p-4 ${alertClass} rounded-lg flex items-start">
 <svg class="w-6 h-6 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${type === 'success' ? 'M5 13l4 4L19 7' : 'M6 18L18 6M6 6l12 12'}"></path>
 </svg>
 <div class="flex-1">${message}</div>
 <button onclick="this.parentElement.remove()" class="text-xl opacity-50 hover:opacity-100">&times;</button>
 </div>
 `;

        alertContainer.innerHTML = alertHTML;
    }

    async function submitCertificateRequest(e) {
        e.preventDefault();

        const t = translations[currentLang];
        const certTypeId = document.querySelector('input[name="cert_type_id"]:checked');
        const purpose = document.querySelector('textarea[name="purpose"]').value.trim();

        // Validate
        if (!certTypeId) {
            showAlert(t.selectType, 'error');
            return;
        }

        if (purpose.length < 5) {
            showAlert(t.purposeRequired, 'error');
            return;
        }

        try {
            const response = await fetch('<?php echo BASE_PATH; ?>/api/save_certificate_request.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    cert_type_id: certTypeId.value,
                    purpose: purpose
                })
            });

            const data = await response.json();

            if (data.success) {
                showAlert(`${t.success} ${data.cert_no}`, 'success');
                document.getElementById('certificateForm').reset();

                // Redirect after 2 seconds
                setTimeout(() => {
                    window.location.href = '<?php echo BASE_PATH; ?>/views/employee/my_requests.php';
                }, 2000);
            } else {
                showAlert(`${t.error} ${data.message}`, 'error');
            }
        } catch (error) {
            showAlert(`${t.error} ${error.message}`, 'error');
        }
    }
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>