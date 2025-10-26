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
                      (certificate_no, employee_id, cert_type_id, purpose, status, created_at, updated_at) 
                      VALUES (?, ?, ?, ?, 'New', NOW(), NOW())";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("ssis", $cert_no, $user_id, $cert_type_id, $purpose);

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

<body class="<?php echo $bg_class; ?> <?php echo $text_class; ?> theme-transition">
    <div class="lg:ml-64">
        <div class="lg:ml-64 p-4 md:p-8">

            <!-- Page Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold <?php echo $text_class; ?> flex items-center">
                    <svg class="w-8 h-8 mr-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <?php echo $t['request_certificate']; ?>
                </h1>
                <p class="<?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?> mt-1">
                    <?php echo $t['page_subtitle']; ?>
                </p>
            </div>

            <!-- Success Message -->
            <?php if ($success): ?>
                <div class="mb-6 bg-green-50 dark:bg-green-900 border-l-4 border-green-500 p-4 rounded-lg">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-green-700 dark:text-green-300 font-medium"><?php echo htmlspecialchars($success); ?></p>
                    </div>
                    <a href="<?php echo BASE_PATH; ?>/views/employee/my_requests.php" class="text-green-700 dark:text-green-300 underline text-sm mt-2 inline-block">
                        <?php echo $t['view_my_requests']; ?>
                    </a>
                </div>
            <?php endif; ?>

            <!-- Error Message -->
            <?php if ($error): ?>
                <div class="mb-6 bg-red-50 dark:bg-red-900 border-l-4 border-red-500 p-4 rounded-lg">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-red-600 dark:text-red-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-red-700 dark:text-red-300 font-medium"><?php echo htmlspecialchars($error); ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Main Form Card -->
            <div class="<?php echo $card_bg; ?> rounded-lg shadow-lg p-6 border <?php echo $border_class; ?>">
                <form method="POST" id="certificateForm">

                    <!-- Employee Info Section -->
                    <div class="<?php echo $is_dark ? 'bg-gray-700' : 'bg-blue-50'; ?> rounded-lg p-6 mb-6">
                        <h3 class="font-semibold <?php echo $text_class; ?> mb-4 flex items-center text-lg">
                            <svg class="w-6 h-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            <?php echo $t['employee_information']; ?>
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium <?php echo $is_dark ? 'text-gray-300' : 'text-gray-700'; ?> mb-1"><?php echo $t['employee_id']; ?></label>
                                <p class="<?php echo $text_class; ?> font-semibold text-lg"><?php echo htmlspecialchars($employee['employee_id'] ?? ''); ?></p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium <?php echo $is_dark ? 'text-gray-300' : 'text-gray-700'; ?> mb-1"><?php echo $t['full_name']; ?></label>
                                <p class="<?php echo $text_class; ?> font-semibold text-lg"><?php echo htmlspecialchars($display_name); ?></p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium <?php echo $is_dark ? 'text-gray-300' : 'text-gray-700'; ?> mb-1"><?php echo $t['position']; ?></label>
                                <p class="<?php echo $text_class; ?>"><?php echo htmlspecialchars($employee['position_name'] ?? ''); ?></p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium <?php echo $is_dark ? 'text-gray-300' : 'text-gray-700'; ?> mb-1"><?php echo $t['division']; ?></label>
                                <p class="<?php echo $text_class; ?>"><?php echo htmlspecialchars($employee['division_name'] ?? ''); ?></p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium <?php echo $is_dark ? 'text-gray-300' : 'text-gray-700'; ?> mb-1"><?php echo $t['date_of_hire']; ?></label>
                                <p class="<?php echo $text_class; ?>"><?php echo date('d/m/Y', strtotime($employee['date_of_hire'] ?? date('Y-m-d'))); ?></p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium <?php echo $is_dark ? 'text-gray-300' : 'text-gray-700'; ?> mb-1"><?php echo $t['hiring_type']; ?></label>
                                <p class="<?php echo $text_class; ?>"><?php echo htmlspecialchars($employee['hiring_type_name'] ?? ''); ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Certificate Type Selection -->
                    <div class="mb-6">
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
                                            <?php
                                            // Show alternative language if available
                                            $alt_lang = $current_lang === 'en' ? 'th' : ($current_lang === 'th' ? 'en' : 'th');
                                            $alt_name = $type['type_name_' . $alt_lang] ?? '';
                                            if (!empty($alt_name)):
                                            ?>
                                                <div class="text-sm <?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?>">
                                                    <?php echo htmlspecialchars($alt_name); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <svg class="w-6 h-6 text-blue-600 hidden peer-checked:block flex-shrink-0 ml-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Purpose Section -->
                    <div class="mb-6">
                        <label class="block text-sm font-semibold <?php echo $text_class; ?> mb-2 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                            </svg>
                            <?php echo $t['purpose']; ?> <span class="text-red-500 ml-1">*</span>
                        </label>
                        <textarea name="purpose" rows="4" required
                            placeholder="<?php echo $t['purpose_placeholder']; ?>"
                            class="w-full px-4 py-3 border-2 rounded-lg <?php echo $input_class; ?> focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition resize-none"></textarea>
                        <p class="text-xs <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?> mt-1">ระบุวัตถุประสงค์อย่างชัดเจน (มากกว่า 5 คำ)</p>
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
                        echo 'Once submitted, your certificate request will be processed by the HR department. You will receive a reference number that you can use to track your request.';
                    } elseif ($current_lang === 'my') {
                        echo 'မြန်မာ မြန်မာ မြန်မာ';
                    } else {
                        echo 'หลังจากส่งคำขอแล้ว แผนกทรัพยากรบุคคลจะดำเนินการอนุมัติ คุณจะได้รับเลขที่อ้างอิงเพื่อใช้ในการติดตามสถานะคำขอ';
                    }
                    ?>
                </p>
            </div>
        </div>
    </div>

    <script>
        // Form validation
        document.getElementById('certificateForm').addEventListener('submit', function(e) {
            const certType = document.querySelector('input[name="cert_type_id"]:checked');
            const purpose = document.querySelector('textarea[name="purpose"]').value.trim();

            if (!certType) {
                e.preventDefault();
                alert('<?php echo $t['please_select_type']; ?>');
                return;
            }

            if (purpose.length < 5) {
                e.preventDefault();
                alert('<?php echo $current_lang === 'en' ? 'Purpose must be at least 5 words' : 'วัตถุประสงค์ต้องมีอย่างน้อย 5 คำ'; ?>');
                return;
            }
        });
    </script>

    <?php include __DIR__ . '/../../includes/footer.php'; ?>
</body>

</html>