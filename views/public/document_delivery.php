<?php

/**
 * Document Delivery System - Enhanced Beautiful Design
 * ระบบลงชื่อส่งเอกสาร (ดีไซน์ปรับปรุง + Button Selection สำหรับ Document Type)
 * Features: Modern gradient design, Beautiful UI, Tailwind CSS only
 */

// Handle API requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SERVER['CONTENT_TYPE'] === 'application/json') {
    handleApiRequest();
    exit;
}

function handleApiRequest()
{
    header('Content-Type: application/json; charset=utf-8');

    try {
        require_once __DIR__ . '/../../config/db_config.php';

        $json_input = file_get_contents('php://input');
        if (empty($json_input)) {
            throw new Exception('Empty request body');
        }

        $input = json_decode($json_input, true);
        if ($input === null) {
            throw new Exception('Invalid JSON format: ' . json_last_error_msg());
        }

        $required = ['employee_id', 'delivery_type', 'service_type', 'document_category_id', 'satisfaction_score'];
        foreach ($required as $field) {
            if (!isset($input[$field]) || ($input[$field] === '' && $field !== 'remarks')) {
                throw new Exception("Missing required field: $field");
            }
        }

        $employee_id = trim($input['employee_id']);
        $delivery_type = trim($input['delivery_type']);
        $service_type = trim($input['service_type']);
        $category_id = intval($input['document_category_id']);
        $remarks = trim($input['remarks'] ?? '');
        $satisfaction_score = intval($input['satisfaction_score']);

        if (empty($employee_id) || strlen($employee_id) > 20) {
            throw new Exception('Invalid employee ID');
        }

        if ($satisfaction_score < 1 || $satisfaction_score > 5) {
            throw new Exception('Invalid satisfaction score (must be 1-5)');
        }

        if ($category_id <= 0) {
            throw new Exception('Invalid category ID');
        }

        if (!in_array($delivery_type, ['submit', 'receive'])) {
            throw new Exception('Invalid delivery type');
        }

        if (!in_array($service_type, ['individual', 'group'])) {
            throw new Exception('Invalid service type');
        }

        $conn = getDbConnection();
        if (!$conn) {
            throw new Exception('Database connection failed');
        }

        $emp_id_safe = $conn->real_escape_string($employee_id);

        $emp_query = "SELECT employee_id, full_name_th FROM employees WHERE employee_id = '$emp_id_safe' AND status_id = 1 LIMIT 1";
        $emp_result = $conn->query($emp_query);

        if (!$emp_result) {
            throw new Exception('Database query error: ' . $conn->error);
        }

        if ($emp_result->num_rows === 0) {
            throw new Exception('Employee not found or inactive');
        }

        $employee = $emp_result->fetch_assoc();

        $cat_query = "SELECT category_id, category_name_th FROM service_category_master WHERE category_id = $category_id LIMIT 1";
        $cat_result = $conn->query($cat_query);

        if (!$cat_result) {
            throw new Exception('Database query error: ' . $conn->error);
        }

        if ($cat_result->num_rows === 0) {
            throw new Exception('Invalid document category');
        }

        $category = $cat_result->fetch_assoc();

        $service_type_id = ($service_type === 'group') ? 2 : 1;

        $feedback = "Delivery: $delivery_type | Service: $service_type | Remarks: $remarks";

        $employee_name = $employee['full_name_th'] ?? $employee_id;
        $employee_name_safe = $conn->real_escape_string($employee_name);
        $feedback_safe = $conn->real_escape_string($feedback);

        $insert_query = "INSERT INTO document_submissions 
                        (employee_id, employee_name, service_category_id, service_type_id, status, 
                         satisfaction_score, satisfaction_feedback, created_at, updated_at) 
                        VALUES ('$emp_id_safe', '$employee_name_safe', $category_id, $service_type_id, 'Complete', 
                                $satisfaction_score, '$feedback_safe', NOW(), NOW())";

        if (!$conn->query($insert_query)) {
            throw new Exception('Insert failed: ' . $conn->error);
        }

        $submission_id = $conn->insert_id;
        $conn->close();

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Document submission recorded successfully',
            'data' => [
                'submission_id' => $submission_id,
                'employee_id' => $employee_id,
                'employee_name' => $employee_name,
                'category' => $category['category_name_th'],
                'rating' => $satisfaction_score,
                'timestamp' => date('Y-m-d H:i:s')
            ]
        ], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
    }
}

// ================== FORM PAGE CODE ==================

require_once __DIR__ . '/../../config/db_config.php';

$current_lang = $_GET['lang'] ?? ($_COOKIE['doc_lang'] ?? 'th');
if (!in_array($current_lang, ['th', 'en', 'my'])) {
    $current_lang = 'th';
}
setcookie('doc_lang', $current_lang, time() + (86400 * 30), '/');

$translations = [
    'th' => [
        'page_title' => 'ระบบลงชื่อส่งเอกสาร',
        'subtitle' => 'ส่งเอกสารด้วยความสะดวก รวดเร็ว และปลอดภัย',
        'employee_id' => 'รหัสพนักงาน',
        'delivery_type' => 'ประเภทเอกสาร',
        'delivery_submit' => '📤 ส่งเอกสาร',
        'delivery_receive' => '📥 รับเอกสาร',
        'service_type' => 'ประเภทการส่ง',
        'service_individual' => '👤 ส่วนตัว',
        'service_group' => '👥 กลุ่ม',
        'select_documents' => 'ประเภทเอกสาร',
        'remarks' => 'หมายเหตุเพิ่มเติม',
        'remarks_placeholder' => 'ระบุรายละเอียดเพิ่มเติม (ไม่บังคับ)',
        'satisfaction_rating' => 'ให้คะแนนความพึงพอใจ',
        'confirm_submit' => 'ส่งข้อมูล',
        'clear' => 'เคลียร์ข้อมูล',
        'please_select_category' => 'กรุณาเลือกประเภทเอกสาร',
        'please_rate' => 'กรุณาให้คะแนนความพึงพอใจ',
        'valid_employee' => '✓ รหัสพนักงานถูกต้อง',
        'success_message' => '✓ บันทึกสำเร็จ!',
        'error_message' => '✗ เกิดข้อผิดพลาด',
        'connection_error' => '✗ เกิดข้อผิดพลาดในการเชื่อมต่อ',
        'processing' => 'กำลังบันทึก...',
        'language' => 'ภาษา',
    ],
    'en' => [
        'page_title' => 'Document Delivery System',
        'subtitle' => 'Submit documents easily, quickly, and safely',
        'employee_id' => 'Employee ID',
        'delivery_type' => 'Document Type',
        'delivery_submit' => '📤 Submit Document',
        'delivery_receive' => '📥 Receive Document',
        'service_type' => 'Service Type',
        'service_individual' => '👤 Individual',
        'service_group' => '👥 Group',
        'select_documents' => 'Document Type',
        'remarks' => 'Additional Remarks',
        'remarks_placeholder' => 'Add additional details (optional)',
        'satisfaction_rating' => 'Rate Your Satisfaction',
        'confirm_submit' => 'Submit',
        'clear' => 'Clear',
        'please_select_category' => 'Please select a document type',
        'please_rate' => 'Please rate your satisfaction',
        'valid_employee' => '✓ Employee ID is valid',
        'success_message' => '✓ Successfully saved!',
        'error_message' => '✗ An error occurred',
        'connection_error' => '✗ Connection error',
        'processing' => 'Processing...',
        'language' => 'Language',
    ],
    'my' => [
        'page_title' => 'စာ類တင်သွင်းမှုစနစ်',
        'subtitle' => 'စာ類များကို လွယ်လင့်တကူ တင်သွင်းပါ',
        'employee_id' => 'အလုပ်သမားအိုင်ဒီ',
        'delivery_type' => 'စာ類အမျိုးအစား',
        'delivery_submit' => '📤 တင်သွင်းမည်',
        'delivery_receive' => '📥 လက်ခံမည်',
        'service_type' => 'ဆာလုံးမီယုံ',
        'service_individual' => '👤 တစ်ခုတည်း',
        'service_group' => '👥 အုပ်စု',
        'select_documents' => 'စာ類အမျိုးအစား',
        'remarks' => 'အပိုမှတ်ချက်များ',
        'remarks_placeholder' => 'အပိုအသေးစိတ်ထည့်သွင်း',
        'satisfaction_rating' => 'ကျေးဇူးတင်မှုအဆင့်သတ်မှတ်',
        'confirm_submit' => 'တင်သွင်း',
        'clear' => 'ရှင်းလင်း',
        'please_select_category' => 'စာ類အမျိုးအစားရွေးချယ်',
        'please_rate' => 'ကျေးဇူးတင်မှုအဆင့်သတ်မှတ်',
        'valid_employee' => '✓ အလုပ်သမားအိုင်ဒီမှန်',
        'success_message' => '✓ အောင်မြင်!',
        'error_message' => '✗ အမှားအယွင်း',
        'connection_error' => '✗ ချိတ်ဆက်အမှားအယွင်း',
        'processing' => 'လုပ်ဆောင်နေ...',
        'language' => 'ဘာသာစကား',
    ]
];

$t = $translations[$current_lang] ?? $translations['th'];

// Get employees and categories from database
$conn = getDbConnection();
$employees = [];
$categories = [];

$emp_result = $conn->query("SELECT employee_id, full_name_th FROM employees WHERE status_id = 1 ORDER BY full_name_th");
if ($emp_result) {
    while ($row = $emp_result->fetch_assoc()) {
        $employees[] = $row;
    }
}

$cat_result = $conn->query("SELECT category_id, category_name_th FROM service_category_master ORDER BY category_name_th");
if ($cat_result) {
    while ($row = $cat_result->fetch_assoc()) {
        $categories[] = $row;
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="<?php echo $current_lang; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $t['page_title']; ?></title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo BASE_PATH; ?>/assets/images/favicons/favicon.ico">
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo BASE_PATH; ?>/assets/images/favicons/favicon-16x16.png">
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo BASE_PATH; ?>/assets/images/favicons/favicon-32x32.png">

    <!-- Apple Touch Icon -->
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo BASE_PATH; ?>/assets/images/favicons/apple-touch-icon.png">

    <!-- Android Chrome Icons -->
    <link rel="icon" type="image/png" sizes="192x192" href="<?php echo BASE_PATH; ?>/assets/images/favicons/android-chrome-192x192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="<?php echo BASE_PATH; ?>/assets/images/favicons/android-chrome-512x512.png">


    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 min-h-screen font-system">
    <!-- Decorative Elements -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-0 right-0 w-96 h-96 bg-blue-200 rounded-full mix-blend-multiply filter blur-3xl opacity-20"></div>
        <div class="absolute -bottom-8 left-20 w-72 h-72 bg-indigo-200 rounded-full mix-blend-multiply filter blur-3xl opacity-20"></div>
    </div>

    <!-- Main Container -->
    <div class="relative z-10 flex min-h-screen items-center justify-center py-12 px-4 sm:px-6">
        <div class="w-full max-w-2xl">

            <!-- Card Container -->
            <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">

                <!-- Header Section with Gradient -->
                <div class="bg-gradient-to-r from-blue-600 via-blue-500 to-indigo-600 px-6 sm:px-8 py-10 sm:py-12">
                    <h1 class="text-3xl sm:text-4xl font-bold text-white mb-2">
                        <?php echo $t['page_title']; ?>
                    </h1>
                    <p class="text-blue-100 text-sm sm:text-base mb-6">
                        <?php echo $t['subtitle']; ?>
                    </p>

                    <!-- Language Switcher -->
                    <div class="flex gap-2 flex-wrap">
                        <a href="?lang=th"
                            class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-all duration-300 backdrop-blur-sm <?php echo $current_lang === 'th' ? 'bg-white text-blue-600 shadow-lg' : 'bg-white/20 text-white hover:bg-white/30'; ?>">
                            ไทย
                        </a>
                        <a href="?lang=en"
                            class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-all duration-300 backdrop-blur-sm <?php echo $current_lang === 'en' ? 'bg-white text-blue-600 shadow-lg' : 'bg-white/20 text-white hover:bg-white/30'; ?>">
                            English
                        </a>
                        <a href="?lang=my"
                            class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-all duration-300 backdrop-blur-sm <?php echo $current_lang === 'my' ? 'bg-white text-blue-600 shadow-lg' : 'bg-white/20 text-white hover:bg-white/30'; ?>">
                            မြန်မာ
                        </a>
                    </div>
                </div>

                <!-- Form Section -->
                <div class="px-6 sm:px-8 py-8 sm:py-10">
                    <form id="deliveryForm" class="space-y-7">

                        <!-- Employee ID Section -->
                        <div>
                            <label class="block text-sm font-bold text-gray-800 mb-3 flex items-center gap-2">
                                <span class="text-xl">👤</span>
                                <?php echo $t['employee_id']; ?>
                                <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input
                                    list="employeeList"
                                    id="employee_id"
                                    name="employee_id"
                                    type="text"
                                    required
                                    placeholder="ค้นหาหรือพิมพ์รหัสพนักงาน"
                                    class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl text-sm focus:outline-none focus:border-blue-500 focus:ring-3 focus:ring-blue-200 transition-all bg-gray-50 hover:bg-white">
                                <datalist id="employeeList">
                                    <?php foreach ($employees as $emp): ?>
                                        <option value="<?php echo htmlspecialchars($emp['employee_id']); ?>">
                                            <?php echo htmlspecialchars($emp['full_name_th']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </datalist>
                            </div>
                            <div id="employeeInfo" class="hidden mt-2 text-xs text-green-600 font-semibold flex items-center gap-1">
                                <span>✓</span><span id="empName"></span>
                            </div>
                        </div>

                        <!-- Divider -->
                        <div class="h-px bg-gradient-to-r from-transparent via-gray-300 to-transparent"></div>

                        <!-- Delivery Type -->
                        <div>
                            <label class="block text-sm font-bold text-gray-800 mb-3 flex items-center gap-2">
                                <span class="text-xl">📋</span>
                                <?php echo $t['delivery_type']; ?>
                                <span class="text-red-500">*</span>
                            </label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <input
                                        type="radio"
                                        id="delivery_submit"
                                        name="delivery_type"
                                        value="submit"
                                        checked
                                        class="hidden peer">
                                    <label
                                        for="delivery_submit"
                                        class="flex items-center justify-center px-4 py-3 border-2 border-gray-300 rounded-xl text-sm font-semibold text-gray-700 cursor-pointer transition-all hover:border-blue-400 hover:shadow-md peer-checked:border-blue-600 peer-checked:bg-gradient-to-r peer-checked:from-blue-50 peer-checked:to-indigo-50 peer-checked:text-blue-700 peer-checked:shadow-lg">
                                        <?php echo $t['delivery_submit']; ?>
                                    </label>
                                </div>
                                <div>
                                    <input
                                        type="radio"
                                        id="delivery_receive"
                                        name="delivery_type"
                                        value="receive"
                                        class="hidden peer">
                                    <label
                                        for="delivery_receive"
                                        class="flex items-center justify-center px-4 py-3 border-2 border-gray-300 rounded-xl text-sm font-semibold text-gray-700 cursor-pointer transition-all hover:border-blue-400 hover:shadow-md peer-checked:border-blue-600 peer-checked:bg-gradient-to-r peer-checked:from-blue-50 peer-checked:to-indigo-50 peer-checked:text-blue-700 peer-checked:shadow-lg">
                                        <?php echo $t['delivery_receive']; ?>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Service Type -->
                        <div>
                            <label class="block text-sm font-bold text-gray-800 mb-3 flex items-center gap-2">
                                <span class="text-xl">🎯</span>
                                <?php echo $t['service_type']; ?>
                                <span class="text-red-500">*</span>
                            </label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <input
                                        type="radio"
                                        id="service_individual"
                                        name="service_type"
                                        value="individual"
                                        checked
                                        class="hidden peer">
                                    <label
                                        for="service_individual"
                                        class="flex items-center justify-center px-4 py-3 border-2 border-gray-300 rounded-xl text-sm font-semibold text-gray-700 cursor-pointer transition-all hover:border-indigo-400 hover:shadow-md peer-checked:border-indigo-600 peer-checked:bg-gradient-to-r peer-checked:from-indigo-50 peer-checked:to-purple-50 peer-checked:text-indigo-700 peer-checked:shadow-lg">
                                        <?php echo $t['service_individual']; ?>
                                    </label>
                                </div>
                                <div>
                                    <input
                                        type="radio"
                                        id="service_group"
                                        name="service_type"
                                        value="group"
                                        class="hidden peer">
                                    <label
                                        for="service_group"
                                        class="flex items-center justify-center px-4 py-3 border-2 border-gray-300 rounded-xl text-sm font-semibold text-gray-700 cursor-pointer transition-all hover:border-indigo-400 hover:shadow-md peer-checked:border-indigo-600 peer-checked:bg-gradient-to-r peer-checked:from-indigo-50 peer-checked:to-purple-50 peer-checked:text-indigo-700 peer-checked:shadow-lg">
                                        <?php echo $t['service_group']; ?>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Divider -->
                        <div class="h-px bg-gradient-to-r from-transparent via-gray-300 to-transparent"></div>

                        <!-- Document Category (Button Selection) -->
                        <div>
                            <label class="block text-sm font-bold text-gray-800 mb-3 flex items-center gap-2">
                                <span class="text-xl">📄</span>
                                <?php echo $t['select_documents']; ?>
                                <span class="text-red-500">*</span>
                            </label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" id="categoryContainer">
                                <?php foreach ($categories as $cat): ?>
                                    <div>
                                        <input
                                            type="radio"
                                            id="cat_<?php echo $cat['category_id']; ?>"
                                            name="document_category_id"
                                            value="<?php echo $cat['category_id']; ?>"
                                            class="hidden peer">
                                        <label
                                            for="cat_<?php echo $cat['category_id']; ?>"
                                            class="flex items-center justify-center px-4 py-3 border-2 border-gray-300 rounded-xl text-sm font-semibold text-gray-700 cursor-pointer transition-all hover:border-emerald-400 hover:shadow-md peer-checked:border-emerald-600 peer-checked:bg-gradient-to-r peer-checked:from-emerald-50 peer-checked:to-teal-50 peer-checked:text-emerald-700 peer-checked:shadow-lg">
                                            📎 <?php echo htmlspecialchars($cat['category_name_th']); ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Remarks -->
                        <div>
                            <label class="block text-sm font-bold text-gray-800 mb-3 flex items-center gap-2">
                                <span class="text-xl">💬</span>
                                <?php echo $t['remarks']; ?>
                            </label>
                            <textarea
                                id="remarks"
                                name="remarks"
                                placeholder="<?php echo $t['remarks_placeholder']; ?>"
                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl text-sm focus:outline-none focus:border-blue-500 focus:ring-3 focus:ring-blue-200 transition-all resize-none bg-gray-50 hover:bg-white"
                                rows="4"></textarea>
                        </div>

                        <!-- Divider -->
                        <div class="h-px bg-gradient-to-r from-transparent via-gray-300 to-transparent"></div>

                        <!-- Satisfaction Rating -->
                        <div>
                            <label class="block text-sm font-bold text-gray-800 mb-4 text-center flex items-center justify-center gap-2">
                                <span class="text-2xl">⭐</span>
                                <?php echo $t['satisfaction_rating']; ?>
                                <span class="text-red-500">*</span>
                            </label>
                            <div class="flex items-center justify-center gap-3" id="starContainer">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <div class="relative group">
                                        <input
                                            type="radio"
                                            id="star<?php echo $i; ?>"
                                            name="satisfaction_score"
                                            value="<?php echo $i; ?>"
                                            <?php echo $i === 5 ? 'required' : ''; ?>
                                            class="hidden peer">
                                        <label
                                            for="star<?php echo $i; ?>"
                                            class="star-label text-5xl text-gray-300 cursor-pointer transition-all duration-200 hover:scale-125 block"
                                            data-star="<?php echo $i; ?>">
                                            ★
                                        </label>
                                        <!-- Show rating number on hover -->
                                        <span class="absolute -top-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs font-bold px-2 py-1 rounded-md opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap">
                                            <?php echo $i; ?> Star<?php echo $i > 1 ? 's' : ''; ?>
                                        </span>
                                    </div>
                                <?php endfor; ?>
                            </div>
                            <!-- Show selected rating text -->
                            <div class="text-center mt-4">
                                <span id="ratingText" class="text-sm font-semibold text-gray-600 transition-all duration-300">
                                    เลือกคะแนน / Select Rating
                                </span>
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="flex gap-3 pt-4">
                            <button
                                type="submit"
                                class="flex-1 px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-sm font-bold uppercase tracking-wider rounded-xl hover:from-blue-700 hover:to-indigo-700 active:from-blue-800 active:to-indigo-800 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                                <?php echo $t['confirm_submit']; ?>
                            </button>
                            <button
                                type="reset"
                                class="flex-1 px-6 py-3 bg-gray-200 text-gray-700 text-sm font-bold uppercase tracking-wider rounded-xl hover:bg-gray-300 active:bg-gray-400 transition-all duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                                <?php echo $t['clear']; ?>
                            </button>
                        </div>

                    </form>
                </div>

            </div>

            <!-- Footer -->
            <div class="text-center mt-6 text-sm text-gray-600">
                <p>🔒 ข้อมูลของคุณปลอดภัย | 📱 ใช้ได้ทุกอุปกรณ์ | ⚡ รวดเร็วและง่าย</p>
            </div>

        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="fixed top-4 right-4 px-6 py-4 rounded-xl text-sm font-semibold text-white shadow-2xl opacity-0 translate-y-2 transition-all duration-300 pointer-events-none"></div>

    <script>
        const t = <?php echo json_encode($t); ?>;
        const employeeData = <?php echo json_encode($employees); ?>;
        const form = document.getElementById('deliveryForm');
        const employeeInput = document.getElementById('employee_id');

        // Update rating text and star colors when selected
        const ratingInputs = document.querySelectorAll('input[name="satisfaction_score"]');
        ratingInputs.forEach(input => {
            input.addEventListener('change', function() {
                const ratingText = document.getElementById('ratingText');
                const value = parseInt(this.value);
                const ratings = {
                    '1': '⭐ ไม่พอใจเลย / Very Dissatisfied',
                    '2': '⭐⭐ ไม่พอใจ / Dissatisfied',
                    '3': '⭐⭐⭐ ปกติ / Neutral',
                    '4': '⭐⭐⭐⭐ พอใจ / Satisfied',
                    '5': '⭐⭐⭐⭐⭐ พอใจมาก / Very Satisfied'
                };
                ratingText.textContent = ratings[value] || 'เลือกคะแนน / Select Rating';
                ratingText.classList.add('text-yellow-600', 'font-bold');

                // Update all star colors based on rating
                updateStarColors(value);
            });
        });

        // Function to update star colors
        function updateStarColors(rating) {
            const starLabels = document.querySelectorAll('.star-label');
            starLabels.forEach(label => {
                const starNumber = parseInt(label.getAttribute('data-star'));
                // Color stars up to the selected rating
                if (starNumber <= rating) {
                    label.classList.remove('text-gray-300');
                    label.classList.add('text-yellow-400', 'scale-125');
                } else {
                    label.classList.add('text-gray-300');
                    label.classList.remove('text-yellow-400', 'scale-125');
                }
            });
        }

        // Add hover effect for all stars
        document.getElementById('starContainer').addEventListener('mouseover', function(e) {
            if (e.target.classList.contains('star-label')) {
                const hoverStar = parseInt(e.target.getAttribute('data-star'));
                const starLabels = document.querySelectorAll('.star-label');
                starLabels.forEach(label => {
                    const starNumber = parseInt(label.getAttribute('data-star'));
                    if (starNumber <= hoverStar) {
                        label.classList.add('text-yellow-400');
                    } else {
                        label.classList.remove('text-yellow-400');
                    }
                });
            }
        });

        // Restore colors when mouse leaves
        document.getElementById('starContainer').addEventListener('mouseout', function(e) {
            const selectedStar = document.querySelector('input[name="satisfaction_score"]:checked');
            if (selectedStar) {
                updateStarColors(parseInt(selectedStar.value));
            } else {
                const starLabels = document.querySelectorAll('.star-label');
                starLabels.forEach(label => {
                    label.classList.add('text-gray-300');
                    label.classList.remove('text-yellow-400', 'scale-125');
                });
            }
        });

        // Validate employee ID
        employeeInput.addEventListener('input', function() {
            const found = employeeData.find(e => e.employee_id === this.value);
            const infoDiv = document.getElementById('employeeInfo');
            const empName = document.getElementById('empName');
            if (found) {
                empName.textContent = found.full_name_th;
                infoDiv.classList.remove('hidden');
            } else {
                infoDiv.classList.add('hidden');
            }
        });

        // Form submission
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const empId = employeeInput.value.trim();
            const categoryId = document.querySelector('input[name="document_category_id"]:checked')?.value;
            const rating = form.satisfaction_score.value;

            if (!empId) {
                showToast('กรุณากรอกรหัสพนักงาน', 'error');
                return;
            }

            if (!employeeData.find(e => e.employee_id === empId)) {
                showToast('รหัสพนักงานไม่ถูกต้อง', 'error');
                return;
            }

            if (!categoryId) {
                showToast(t['please_select_category'], 'error');
                return;
            }

            if (!rating) {
                showToast(t['please_rate'], 'error');
                return;
            }

            const data = {
                employee_id: empId,
                delivery_type: form.delivery_type.value,
                service_type: form.service_type.value,
                document_category_id: parseInt(categoryId),
                remarks: form.remarks.value,
                satisfaction_score: parseInt(rating)
            };

            const btn = form.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.textContent = t['processing'];

            fetch(window.location.href, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                })
                .then(r => r.json())
                .then(result => {
                    if (result.success) {
                        showToast(t['success_message'], 'success');
                        setTimeout(() => {
                            form.reset();
                            document.getElementById('employeeInfo').classList.add('hidden');
                            document.getElementById('ratingText').textContent = 'เลือกคะแนน / Select Rating';
                            document.getElementById('ratingText').classList.remove('text-yellow-600', 'font-bold');

                            // Reset star colors
                            const starLabels = document.querySelectorAll('.star-label');
                            starLabels.forEach(label => {
                                label.classList.add('text-gray-300');
                                label.classList.remove('text-yellow-400', 'scale-125');
                            });

                            btn.disabled = false;
                            btn.textContent = t['confirm_submit'];
                        }, 2000);
                    } else {
                        showToast(t['error_message'] + ': ' + result.message, 'error');
                        btn.disabled = false;
                        btn.textContent = t['confirm_submit'];
                    }
                })
                .catch(e => {
                    showToast(t['connection_error'], 'error');
                    btn.disabled = false;
                    btn.textContent = t['confirm_submit'];
                });
        });

        function showToast(msg, type = 'success') {
            const toast = document.getElementById('toast');
            toast.textContent = msg;
            toast.className = `fixed top-4 right-4 px-6 py-4 rounded-xl text-sm font-semibold text-white shadow-2xl transition-all duration-300 ${
                type === 'success' ? 'bg-gradient-to-r from-green-500 to-emerald-600' : 'bg-gradient-to-r from-red-500 to-rose-600'
            }`;
            toast.classList.remove('opacity-0', 'translate-y-2');
            toast.classList.add('opacity-100', 'translate-y-0');

            setTimeout(() => {
                toast.classList.add('opacity-0', 'translate-y-2');
                toast.classList.remove('opacity-100', 'translate-y-0');
            }, 4000);
        }

        // Add icon to reset button
        form.querySelector('button[type="reset"]').addEventListener('click', function() {
            document.getElementById('employeeInfo').classList.add('hidden');
            document.getElementById('ratingText').textContent = 'เลือกคะแนน / Select Rating';
            document.getElementById('ratingText').classList.remove('text-yellow-600', 'font-bold');

            // Reset star colors
            const starLabels = document.querySelectorAll('.star-label');
            starLabels.forEach(label => {
                label.classList.add('text-gray-300');
                label.classList.remove('text-yellow-400', 'scale-125');
            });
        });
    </script>
</body>

</html>