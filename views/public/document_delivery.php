<?php
/**
 * Document Delivery System - White Theme Edition
 * ระบบลงชื่อส่งเอกสาร (โทนขาว)
 * Features: White theme, Clear highlights, Settings in form
 */

// Handle API requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SERVER['CONTENT_TYPE'] === 'application/json') {
    handleApiRequest();
    exit;
}

function handleApiRequest() {
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

$current_theme = $_GET['theme'] ?? ($_COOKIE['doc_theme'] ?? 'light');
if (!in_array($current_theme, ['light', 'dark'])) {
    $current_theme = 'light';
}
setcookie('doc_theme', $current_theme, time() + (86400 * 30), '/');

$translations = [
    'th' => [
        'page_title' => 'ระบบลงชื่อส่งเอกสาร',
        'document_delivery_system' => 'ระบบลงชื่อส่งเอกสาร',
        'settings' => 'ตั้งค่า',
        'language' => 'ภาษา',
        'theme' => 'ธีม',
        'employee_id' => 'รหัสพนักงาน',
        'search_employee' => 'พิมพ์เพื่อค้นหา',
        'invalid_employee_id' => 'รหัสพนักงานไม่ถูกต้อง',
        'delivery_type' => 'ประเภทเอกสาร',
        'delivery_type_desc' => 'เลือกว่าคุณกำลัง ส่ง หรือ รับเอกสาร',
        'delivery_submit' => '📤 ส่งเอกสาร',
        'delivery_receive' => '📥 รับเอกสาร',
        'service_type' => 'ประเภทการส่ง',
        'service_type_desc' => 'เลือกว่าคุณ ส่งหรือรับเพียงคนเดียว หรือ เป็นตัวแทนกลุ่ม',
        'service_individual' => '👤 ส่วนตัว',
        'service_individual_desc' => 'ส่ง/รับเอกสารเพื่อตนเอง',
        'service_group' => '👥 กลุ่ม',
        'service_group_desc' => 'ส่ง/รับเอกสารแทนหลายคน',
        'select_documents' => 'ประเภทเอกสาร',
        'remarks' => 'หมายเหตุ',
        'remarks_placeholder' => 'ระบุรายละเอียดเพิ่มเติม (ถ้ามี)',
        'satisfaction_rating' => 'ให้คะแนนความพึงพอใจ',
        'confirm_submit' => 'ยืนยันการส่ง',
        'please_select_category' => 'กรุณาเลือกประเภทเอกสาร',
        'please_rate' => 'กรุณาให้คะแนนความพึงพอใจ',
        'valid_employee' => 'รหัสพนักงานถูกต้อง',
        'success_message' => '✅ บันทึกสำเร็จ!',
        'error_message' => 'เกิดข้อผิดพลาด',
        'connection_error' => 'เกิดข้อผิดพลาดในการเชื่อมต่อ',
        'processing' => 'กำลังบันทึก...',
        'light_mode' => 'สว่าง ☀️',
        'dark_mode' => 'มืด 🌙',
    ],
    'en' => [
        'page_title' => 'Document Delivery System',
        'document_delivery_system' => 'Document Delivery System',
        'settings' => 'Settings',
        'language' => 'Language',
        'theme' => 'Theme',
        'employee_id' => 'Employee ID',
        'search_employee' => 'Type to search',
        'invalid_employee_id' => 'Invalid Employee ID',
        'delivery_type' => 'Document Type',
        'delivery_type_desc' => 'Choose if you are submitting or receiving documents',
        'delivery_submit' => '📤 Submit Document',
        'delivery_receive' => '📥 Receive Document',
        'service_type' => 'Service Type',
        'service_type_desc' => 'Choose if you\'re submitting/receiving for yourself or on behalf of a group',
        'service_individual' => '👤 Individual',
        'service_individual_desc' => 'Submit/Receive for yourself only',
        'service_group' => '👥 Group',
        'service_group_desc' => 'Submit/Receive on behalf of multiple people',
        'select_documents' => 'Document Type',
        'remarks' => 'Remarks',
        'remarks_placeholder' => 'Add additional details (if any)',
        'satisfaction_rating' => 'Rate Your Satisfaction',
        'confirm_submit' => 'Confirm Submission',
        'please_select_category' => 'Please select a document type',
        'please_rate' => 'Please rate your satisfaction',
        'valid_employee' => 'Employee ID is valid',
        'success_message' => '✅ Successfully saved!',
        'error_message' => 'An error occurred',
        'connection_error' => 'Connection error',
        'processing' => 'Processing...',
        'light_mode' => 'Light ☀️',
        'dark_mode' => 'Dark 🌙',
    ],
    'my' => [
        'page_title' => 'စာ類တင်သွင်းမှုစနစ်',
        'document_delivery_system' => 'စာ類တင်သွင်းမှုစနစ်',
        'settings' => 'ဆည်တင်များ',
        'language' => 'ဘာသာစကား',
        'theme' => 'အပ်ပီယံ',
        'employee_id' => 'အလုပ်သမားအိုင်ဒီ',
        'search_employee' => 'ရှာဖွေရန်ကျူးကျော်',
        'invalid_employee_id' => 'အလုပ်သမားအိုင်ဒီမမှန်',
        'delivery_type' => 'စာ類အမျိုးအစား',
        'delivery_type_desc' => 'တင်သွင်းခြင်း သို့မဟုတ် လက်ခံခြင်းရွေးချယ်',
        'delivery_submit' => '📤 တင်သွင်းမည်',
        'delivery_receive' => '📥 လက်ခံမည်',
        'service_type' => 'ဆာလုံးမီယုံ',
        'service_type_desc' => 'ကိုယ့်တစ်ခုတည်း သို့မဟုတ် အုပ်စု',
        'service_individual' => '👤 တစ်ခုတည်း',
        'service_individual_desc' => 'ကိုယ့်အတွက်သာ တင်သွင်းခြင်း',
        'service_group' => '👥 အုပ်စု',
        'service_group_desc' => 'အုပ်စုအတွက် တင်သွင်းခြင်း',
        'select_documents' => 'စာ類အမျိုးအစား',
        'remarks' => 'မှတ်ချက်များ',
        'remarks_placeholder' => 'အပိုအသေးစိတ်ထည့်သွင်း',
        'satisfaction_rating' => 'ကျေးဇူးတင်မှုကို အဆင့်သတ်မှတ်',
        'confirm_submit' => 'အတည်ပြုတင်သွင်း',
        'please_select_category' => 'စာ類အမျိုးအစားရွေးချယ်',
        'please_rate' => 'ကျေးဇူးတင်မှုကို အဆင့်သတ်မှတ်',
        'valid_employee' => 'အလုပ်သမားအိုင်ဒီမှန်',
        'success_message' => '✅ အောင်မြင်!',
        'error_message' => 'အမှားအယွင်း',
        'connection_error' => 'ချိတ်ဆက်အမှားအယွင်း',
        'processing' => 'လုပ်ဆောင်နေ...',
        'light_mode' => 'အလင်း ☀️',
        'dark_mode' => 'အမှောင် 🌙',
    ]
];

$t = $translations[$current_lang] ?? $translations['th'];

$conn = getDbConnection();

$employees = [];
if ($conn) {
    $emp_query = "SELECT employee_id, full_name_th FROM employees WHERE status_id = 1 ORDER BY employee_id LIMIT 100";
    $emp_result = $conn->query($emp_query);
    if ($emp_result && $emp_result->num_rows > 0) {
        while ($row = $emp_result->fetch_assoc()) {
            $employees[] = $row;
        }
    }
}

$categories = [];
if ($conn) {
    $cat_query = "SELECT category_id, 
                         CASE WHEN '$current_lang' = 'en' THEN COALESCE(category_name_en, category_name_th)
                              WHEN '$current_lang' = 'my' THEN COALESCE(category_name_my, category_name_th)
                              ELSE COALESCE(category_name_th, category_name_en)
                         END as category_name
                  FROM service_category_master 
                  WHERE category_id IS NOT NULL
                  ORDER BY category_id";
    $cat_result = $conn->query($cat_query);
    if ($cat_result && $cat_result->num_rows > 0) {
        while ($row = $cat_result->fetch_assoc()) {
            $categories[] = $row;
        }
    }
}

if (empty($categories)) {
    $categories = [
        ['category_id' => 1, 'category_name' => 'ใบลา / Leave'],
        ['category_id' => 2, 'category_name' => 'หนังสือรับรอง / Certificate'],
        ['category_id' => 3, 'category_name' => 'บัตรประจำตัว / ID Card'],
    ];
}

if ($conn) $conn->close();
?>
<!DOCTYPE html>
<html lang="<?php echo $current_lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $t['page_title']; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        * { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%); min-height: 100vh; margin: 0; padding: 0; }
        body.dark { background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); }
        
        .star-rating { display: flex; gap: 8px; flex-direction: row-reverse; justify-content: center; }
        .star-rating input { display: none; }
        .star-rating label { cursor: pointer; font-size: 2.5rem; color: #cbd5e1; transition: all 0.2s; line-height: 1; }
        .star-rating label:hover,
        .star-rating label:hover ~ label,
        .star-rating input:checked ~ label { color: #fbbf24; transform: scale(1.2); }
        
        @keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; } }
        .animate-fade-in { animation: fadeIn 0.3s ease-out; }
        
        .radio-card { transition: all 0.3s; }
        .radio-card:hover { transform: translateY(-2px); }
        
        /* Highlight selected options */
        .radio-card input:checked ~ * {
            color: #fff !important;
        }
        
        .radio-card.highlighted {
            background-color: #2563eb !important;
            border-color: #2563eb !important;
            color: white;
            box-shadow: 0 0 20px rgba(37, 99, 235, 0.4);
        }
        
        .radio-card.highlighted .desc {
            color: rgba(255, 255, 255, 0.9) !important;
        }
        
        .category-btn.highlighted {
            background-color: #2563eb !important;
            border-color: #2563eb !important;
            color: white !important;
            box-shadow: 0 0 15px rgba(37, 99, 235, 0.4);
        }
    </style>
</head>
<body class="<?php echo $current_theme === 'dark' ? 'dark' : ''; ?>">
    
    <!-- Main Content -->
    <div class="min-h-screen w-full flex flex-col items-center justify-center px-4 py-8">
        <div class="w-full max-w-3xl">
            
            <!-- Header -->
            <div class="text-center mb-8">
                <h1 class="text-4xl md:text-5xl font-bold bg-gradient-to-r from-blue-600 to-blue-800 bg-clip-text text-transparent mb-2">
                    <?php echo $t['document_delivery_system']; ?>
                </h1>
                <p class="text-gray-600 dark:text-gray-300 text-lg">📋 <?php echo $current_lang === 'th' ? 'ระบบบริหารเอกสารออนไลน์' : 'Online Document Management System'; ?></p>
            </div>

            <!-- Form -->
            <form id="deliveryForm" onsubmit="submitDelivery(event)" class="space-y-6 bg-white dark:bg-gray-800 rounded-3xl p-8 shadow-xl border border-gray-200 dark:border-gray-700">
                
                <!-- Settings Bar -->
                <div class="bg-gradient-to-r from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 rounded-2xl p-5 border border-blue-200 dark:border-blue-700/50">
                    <div class="grid grid-cols-2 md:grid-cols-2 gap-4">
                        <!-- Language -->
                        <div>
                            <label class="block text-sm font-bold text-blue-900 dark:text-blue-100 mb-2">🌐 <?php echo $t['language']; ?></label>
                            <div class="flex gap-2">
                                <a href="?lang=th&theme=<?php echo $current_theme; ?>" class="flex-1 px-2 py-2 rounded text-xs font-bold transition text-center <?php echo $current_lang === 'th' ? 'bg-blue-600 text-white shadow-md' : 'bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 border border-blue-200 dark:border-blue-600 hover:border-blue-400'; ?>">ไทย</a>
                                <a href="?lang=en&theme=<?php echo $current_theme; ?>" class="flex-1 px-2 py-2 rounded text-xs font-bold transition text-center <?php echo $current_lang === 'en' ? 'bg-blue-600 text-white shadow-md' : 'bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 border border-blue-200 dark:border-blue-600 hover:border-blue-400'; ?>">EN</a>
                                <a href="?lang=my&theme=<?php echo $current_theme; ?>" class="flex-1 px-2 py-2 rounded text-xs font-bold transition text-center <?php echo $current_lang === 'my' ? 'bg-blue-600 text-white shadow-md' : 'bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 border border-blue-200 dark:border-blue-600 hover:border-blue-400'; ?>">Myanmar</a>
                            </div>
                        </div>
                        <!-- Theme -->
                        <div>
                            <label class="block text-sm font-bold text-blue-900 dark:text-blue-100 mb-2">🎨 <?php echo $t['theme']; ?></label>
                            <div class="flex gap-2">
                                <a href="?lang=<?php echo $current_lang; ?>&theme=light" class="flex-1 px-2 py-2 rounded text-xs font-bold transition text-center <?php echo $current_theme === 'light' ? 'bg-blue-600 text-white shadow-md' : 'bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 border border-blue-200 dark:border-blue-600 hover:border-blue-400'; ?>"><?php echo $t['light_mode']; ?></a>
                                <a href="?lang=<?php echo $current_lang; ?>&theme=dark" class="flex-1 px-2 py-2 rounded text-xs font-bold transition text-center <?php echo $current_theme === 'dark' ? 'bg-blue-600 text-white shadow-md' : 'bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 border border-blue-200 dark:border-blue-600 hover:border-blue-400'; ?>"><?php echo $t['dark_mode']; ?></a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Employee ID -->
                <div class="animate-fade-in">
                    <label class="block text-base font-bold text-gray-800 dark:text-gray-100 mb-2">👤 <?php echo $t['employee_id']; ?> <span class="text-red-600">*</span></label>
                    <input list="employeeList" id="employee_id" name="employee_id" required
                           placeholder="<?php echo $t['search_employee']; ?>"
                           class="w-full px-4 py-3 border-2 border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg focus:border-blue-600 focus:ring-2 focus:ring-blue-200 dark:focus:ring-blue-900 transition text-base">
                    <datalist id="employeeList">
                        <?php foreach ($employees as $emp): ?>
                            <option value="<?php echo htmlspecialchars($emp['employee_id']); ?>">
                                <?php echo htmlspecialchars($emp['full_name_th']); ?>
                            </option>
                        <?php endforeach; ?>
                    </datalist>
                    <div id="employeePreview" class="mt-2 text-sm font-semibold"></div>
                </div>

                <!-- Delivery Type -->
                <div class="animate-fade-in" style="animation-delay: 0.1s">
                    <label class="block text-base font-bold text-gray-800 dark:text-gray-100 mb-2">📋 <?php echo $t['delivery_type']; ?> <span class="text-red-600">*</span></label>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">ℹ️ <?php echo $t['delivery_type_desc']; ?></p>
                    <div class="grid grid-cols-2 gap-4">
                        <label class="radio-card p-5 border-3 border-gray-300 dark:border-gray-600 rounded-xl cursor-pointer bg-white dark:bg-gray-700 hover:border-blue-400 hover:shadow-lg transition">
                            <input type="radio" name="delivery_type" value="submit" checked class="sr-only peer" onchange="updateDeliveryDisplay(this)">
                            <div class="text-2xl mb-2 text-gray-800 dark:text-white">📤</div>
                            <div class="font-bold text-gray-800 dark:text-white"><?php echo $t['delivery_submit']; ?></div>
                            <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">ส่งเอกสารให้หน่วยงาน</div>
                        </label>
                        <label class="radio-card p-5 border-3 border-gray-300 dark:border-gray-600 rounded-xl cursor-pointer bg-white dark:bg-gray-700 hover:border-blue-400 hover:shadow-lg transition">
                            <input type="radio" name="delivery_type" value="receive" class="sr-only peer" onchange="updateDeliveryDisplay(this)">
                            <div class="text-2xl mb-2 text-gray-800 dark:text-white">📥</div>
                            <div class="font-bold text-gray-800 dark:text-white"><?php echo $t['delivery_receive']; ?></div>
                            <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">รับเอกสารจากหน่วยงาน</div>
                        </label>
                    </div>
                </div>

                <!-- Service Type -->
                <div class="animate-fade-in" style="animation-delay: 0.2s">
                    <label class="block text-base font-bold text-gray-800 dark:text-gray-100 mb-2">👥 <?php echo $t['service_type']; ?> <span class="text-red-600">*</span></label>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">ℹ️ <?php echo $t['service_type_desc']; ?></p>
                    <div class="grid grid-cols-2 gap-4">
                        <label class="radio-card p-5 border-3 border-gray-300 dark:border-gray-600 rounded-xl cursor-pointer bg-white dark:bg-gray-700 hover:border-blue-400 hover:shadow-lg transition">
                            <input type="radio" name="service_type" value="individual" checked class="sr-only peer" onchange="updateServiceDisplay(this)">
                            <div class="text-2xl mb-2 text-gray-800 dark:text-white">👤</div>
                            <div class="font-bold text-gray-800 dark:text-white"><?php echo $t['service_individual']; ?></div>
                            <div class="text-xs text-gray-600 dark:text-gray-400 mt-1"><?php echo $t['service_individual_desc']; ?></div>
                        </label>
                        <label class="radio-card p-5 border-3 border-gray-300 dark:border-gray-600 rounded-xl cursor-pointer bg-white dark:bg-gray-700 hover:border-blue-400 hover:shadow-lg transition">
                            <input type="radio" name="service_type" value="group" class="sr-only peer" onchange="updateServiceDisplay(this)">
                            <div class="text-2xl mb-2 text-gray-800 dark:text-white">👥</div>
                            <div class="font-bold text-gray-800 dark:text-white"><?php echo $t['service_group']; ?></div>
                            <div class="text-xs text-gray-600 dark:text-gray-400 mt-1"><?php echo $t['service_group_desc']; ?></div>
                        </label>
                    </div>
                </div>

                <!-- Document Category -->
                <div class="animate-fade-in" style="animation-delay: 0.3s">
                    <label class="block text-base font-bold text-gray-800 dark:text-gray-100 mb-2">📄 <?php echo $t['select_documents']; ?> <span class="text-red-600">*</span></label>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        <?php foreach ($categories as $cat): ?>
                            <button type="button" 
                                    onclick="toggleCategory(this, <?php echo $cat['category_id']; ?>)"
                                    class="category-btn p-4 border-2 border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 font-semibold text-sm transition hover:border-blue-400 hover:shadow-md">
                                <?php echo htmlspecialchars($cat['category_name']); ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" id="selected_category" name="document_category_id" required>
                </div>

                <!-- Remarks -->
                <div class="animate-fade-in" style="animation-delay: 0.4s">
                    <label class="block text-base font-bold text-gray-800 dark:text-gray-100 mb-2">📝 <?php echo $t['remarks']; ?></label>
                    <textarea name="remarks" rows="3" placeholder="<?php echo $t['remarks_placeholder']; ?>"
                              class="w-full px-4 py-3 border-2 border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg focus:border-blue-600 focus:ring-2 focus:ring-blue-200 dark:focus:ring-blue-900 resize-none transition"></textarea>
                </div>

                <!-- Rating -->
                <div class="animate-fade-in" style="animation-delay: 0.5s">
                    <label class="block text-base font-bold text-gray-800 dark:text-gray-100 mb-4 text-center">⭐ <?php echo $t['satisfaction_rating']; ?> <span class="text-red-600">*</span></label>
                    <div class="star-rating">
                        <input type="radio" name="satisfaction_score" value="5" id="star5" required>
                        <label for="star5">★</label>
                        <input type="radio" name="satisfaction_score" value="4" id="star4">
                        <label for="star4">★</label>
                        <input type="radio" name="satisfaction_score" value="3" id="star3">
                        <label for="star3">★</label>
                        <input type="radio" name="satisfaction_score" value="2" id="star2">
                        <label for="star2">★</label>
                        <input type="radio" name="satisfaction_score" value="1" id="star1">
                        <label for="star1">★</label>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="pt-4 animate-fade-in" style="animation-delay: 0.6s">
                    <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white py-4 rounded-lg font-bold text-lg transition transform hover:scale-105 shadow-lg">
                        ✓ <?php echo $t['confirm_submit']; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Toast -->
    <div id="toast" class="fixed top-20 right-4 hidden px-6 py-4 rounded-lg shadow-lg z-50 text-white font-semibold"></div>

    <script>
        const t = <?php echo json_encode($t); ?>;
        const employeeData = <?php echo json_encode($employees); ?>;

        // Initial highlight
        document.addEventListener('DOMContentLoaded', function() {
            // Highlight delivery type
            const deliveryRadios = document.querySelectorAll('input[name="delivery_type"]');
            deliveryRadios.forEach(radio => {
                const card = radio.closest('.radio-card');
                if (radio.checked) {
                    card.classList.add('highlighted');
                }
                radio.addEventListener('change', function() {
                    deliveryRadios.forEach(r => r.closest('.radio-card').classList.remove('highlighted'));
                    this.closest('.radio-card').classList.add('highlighted');
                });
            });

            // Highlight service type
            const serviceRadios = document.querySelectorAll('input[name="service_type"]');
            serviceRadios.forEach(radio => {
                const card = radio.closest('.radio-card');
                if (radio.checked) {
                    card.classList.add('highlighted');
                }
                radio.addEventListener('change', function() {
                    serviceRadios.forEach(r => r.closest('.radio-card').classList.remove('highlighted'));
                    this.closest('.radio-card').classList.add('highlighted');
                });
            });
        });

        function updateDeliveryDisplay(elem) {
            elem.closest('.radio-card').classList.add('highlighted');
        }

        function updateServiceDisplay(elem) {
            elem.closest('.radio-card').classList.add('highlighted');
        }

        document.getElementById('employee_id').addEventListener('input', function() {
            const emp = employeeData.find(e => e.employee_id === this.value);
            const preview = document.getElementById('employeePreview');
            if (emp) {
                preview.innerHTML = '✓ <span class="text-green-600 dark:text-green-400">' + emp.full_name_th + '</span>';
            } else if (this.value) {
                preview.innerHTML = '⚠ <span class="text-red-600 dark:text-red-400">' + t['invalid_employee_id'] + '</span>';
            } else {
                preview.innerHTML = '';
            }
        });

        function toggleCategory(btn, categoryId) {
            document.querySelectorAll('.category-btn').forEach(b => {
                b.classList.remove('highlighted');
            });
            btn.classList.add('highlighted');
            document.getElementById('selected_category').value = categoryId;
        }

        function submitDelivery(event) {
            event.preventDefault();
            
            const form = event.target;
            const empId = form.employee_id.value;
            const categoryId = form.document_category_id.value;
            const rating = form.satisfaction_score.value;
            
            if (!employeeData.find(e => e.employee_id === empId)) {
                showToast(t['invalid_employee_id'], 'error');
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
            btn.innerHTML = '⏳ ' + t['processing'];

            fetch(window.location.href, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(r => r.json())
            .then(result => {
                if (result.success) {
                    showToast(t['success_message'], 'success');
                    setTimeout(() => {
                        form.reset();
                        document.querySelectorAll('.category-btn').forEach(b => {
                            b.classList.remove('highlighted');
                        });
                        btn.disabled = false;
                        btn.innerHTML = '✓ ' + t['confirm_submit'];
                    }, 2000);
                } else {
                    showToast(t['error_message'] + ': ' + result.message, 'error');
                    btn.disabled = false;
                    btn.innerHTML = '✓ ' + t['confirm_submit'];
                }
            })
            .catch(e => {
                showToast(t['connection_error'], 'error');
                btn.disabled = false;
                btn.innerHTML = '✓ ' + t['confirm_submit'];
            });
        }

        function showToast(msg, type = 'success') {
            const toast = document.getElementById('toast');
            toast.classList.remove('hidden');
            const bgColor = type === 'success' ? 'bg-green-600' : 'bg-red-600';
            toast.className = 'fixed top-20 right-4 px-6 py-4 rounded-lg shadow-lg z-50 text-white font-semibold ' + bgColor;
            toast.innerHTML = msg;
            setTimeout(() => toast.classList.add('hidden'), 4000);
        }
    </script>
</body>
</html>