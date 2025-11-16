<?php
/**
 * Request Complaint Page - Anonymous Complaint System
 * ✅ Standardized Layout Structure (Matches all request forms)
 * ✅ Anonymous submission (SHA256 hash employee_id)
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
        'page_title' => 'ส่งคำร้องเรียน',
        'page_subtitle' => 'ส่งคำร้องเรียนของคุณแบบไม่เปิดเผยตัวตน',
        'employee_information' => 'ข้อมูลพนักงาน',
        'employee_id' => 'รหัสพนักงาน',
        'name' => 'ชื่อ',
        'position' => 'ตำแหน่ง',
        'complaint_category' => 'หมวดหมู่การร้องเรียน',
        'select_category' => 'เลือกหมวดหมู่',
        'subject' => 'หัวข้อ',
        'subject_placeholder' => 'กรอกหัวข้อคำร้องเรียนโดยสั้นๆ (อย่างน้อย 5 ตัวอักษร)...',
        'description' => 'รายละเอียด',
        'description_placeholder' => 'โปรดอธิบายรายละเอียดคำร้องเรียนของคุณอย่างละเอียด (อย่างน้อย 20 ตัวอักษร)...',
        'attachment' => 'แนบไฟล์หลักฐาน',
        'attachment_optional' => '(ไม่บังคับ)',
        'attachment_note' => 'รองรับไฟล์: PDF, DOCX, JPG, PNG (สูงสุด 5MB)',
        'important_notice' => 'ข้อมูลสำคัญ',
        'notice_1' => '🔒 คำร้องเรียนของคุณจะถูกส่งแบบไม่เปิดเผยตัวตน',
        'notice_2' => '✓ เจ้าหน้าที่จะไม่สามารถเห็นว่าใครเป็นผู้ร้องเรียน',
        'notice_3' => '✓ เฉพาะผู้ดูแลระบบเท่านั้นที่สามารถเข้าถึงข้อมูลจริงได้',
        'notice_4' => '⚠️ หลังจากส่งแล้ว คุณไม่สามารถแก้ไขคำร้องเรียนได้',
        'submit_request' => 'ส่งคำร้องเรียน',
        'cancel' => 'ยกเลิก',
        'required' => 'จำเป็น',
        'please_select_category' => 'โปรดเลือกหมวดหมู่การร้องเรียน',
        'subject_too_short' => 'หัวข้อต้องมีอย่างน้อย 5 ตัวอักษร',
        'description_too_short' => 'รายละเอียดต้องมีอย่างน้อย 20 ตัวอักษร',
        'confirm_submit' => 'คุณแน่ใจว่าต้องการส่งคำร้องเรียนนี้หรือไม่?\n\nคำร้องเรียนจะถูกส่งแบบไม่เปิดเผยตัวตนและไม่สามารถแก้ไขได้',
        'error_occurred' => 'เกิดข้อผิดพลาด:',
        'view_my_complaints' => 'ดูคำร้องเรียนของฉัน →',
        'failed_to_submit' => 'ล้มเหลวในการส่งคำร้องเรียน',
        'success_submitted' => 'ส่งคำร้องเรียนเรียบร้อยแล้ว',
        'anonymous_badge' => 'ไม่เปิดเผยตัวตน',
    ],
    'en' => [
        'page_title' => 'Submit Complaint',
        'page_subtitle' => 'Submit your complaint anonymously',
        'employee_information' => 'Employee Information',
        'employee_id' => 'Employee ID',
        'name' => 'Name',
        'position' => 'Position',
        'complaint_category' => 'Complaint Category',
        'select_category' => 'Select category',
        'subject' => 'Subject',
        'subject_placeholder' => 'Enter a brief subject for your complaint (minimum 5 characters)...',
        'description' => 'Description',
        'description_placeholder' => 'Please describe your complaint in detail (minimum 20 characters)...',
        'attachment' => 'Attach Evidence',
        'attachment_optional' => '(Optional)',
        'attachment_note' => 'Supported files: PDF, DOCX, JPG, PNG (Max 5MB)',
        'important_notice' => 'Important Notice',
        'notice_1' => '🔒 Your complaint will be submitted anonymously',
        'notice_2' => '✓ Officers will not be able to see who filed the complaint',
        'notice_3' => '✓ Only system administrators can access the real identity',
        'notice_4' => '⚠️ Once submitted, you cannot edit the complaint',
        'submit_request' => 'Submit Complaint',
        'cancel' => 'Cancel',
        'required' => 'Required',
        'please_select_category' => 'Please select a complaint category',
        'subject_too_short' => 'Subject must be at least 5 characters',
        'description_too_short' => 'Description must be at least 20 characters',
        'confirm_submit' => 'Are you sure you want to submit this complaint?\n\nThe complaint will be submitted anonymously and cannot be edited.',
        'error_occurred' => 'An error occurred:',
        'view_my_complaints' => 'View my complaints →',
        'failed_to_submit' => 'Failed to submit complaint',
        'success_submitted' => 'Complaint submitted successfully',
        'anonymous_badge' => 'Anonymous',
    ],
    'my' => [
        'page_title' => 'တိုင်ကြားချက်တင်သွင်းမည်',
        'page_subtitle' => 'သင်၏တိုင်ကြားချက်ကို အမည်မဖော်ဘဲ တင်သွင်းမည်',
        'employee_information' => 'အလုပ်သမားအချက်အလက်',
        'employee_id' => 'အလုပ်သမားအိုင်ဒီ',
        'name' => 'အမည်',
        'position' => 'အနေအထား',
        'complaint_category' => 'တိုင်ကြားချက်အမျိုးအစား',
        'select_category' => 'အမျိုးအစားရွေးချယ်မည်',
        'subject' => 'ခေါင်းစဉ်',
        'subject_placeholder' => 'သင်၏တိုင်ကြားချက်အတွက် အကျဉ်းချုံးခေါင်းစဉ်ထည့်ပါ (အနည်းဆုံး 5 လက္ခဏာ)...',
        'description' => 'ဖော်ပြချက်',
        'description_placeholder' => 'သင်၏တိုင်ကြားချက်ကို အသေးစိတ်ဖော်ပြပါ (အနည်းဆုံး 20 လက္ခဏာ)...',
        'attachment' => 'သက်သေအထောက်အထားတွဲမည်',
        'attachment_optional' => '(မဖြစ်မနေမလို)',
        'attachment_note' => 'ပံ့ပိုးသောဖိုင်များ: PDF, DOCX, JPG, PNG (အများဆုံး 5MB)',
        'important_notice' => 'အရေးကြီးသောအသိပေးချက်',
        'notice_1' => '🔒 သင်၏တိုင်ကြားချက်ကို အမည်မဖော်ဘဲ တင်သွင်းမည်',
        'notice_2' => '✓ အရာရှိများသည် မည်သူတိုင်ကြားသည်ကို မသိနိုင်ပါ',
        'notice_3' => '✓ စနစ်စီမံခန့်ခွဲသူများသာလျှင် စစ်မှန်သောအချက်အလက်များကို ဝင်ရောက်နိုင်သည်',
        'notice_4' => '⚠️ တင်သွင်းပြီးနောက် သင်သည် တိုင်ကြားချက်ကို ပြင်ဆင်၍မရနိုင်ပါ',
        'submit_request' => 'တိုင်ကြားချက်တင်သွင်းမည်',
        'cancel' => 'ပယ်ဖျက်မည်',
        'required' => 'လိုအပ်သည်',
        'please_select_category' => 'တိုင်ကြားချက်အမျိုးအစားရွေးချယ်ပါ',
        'subject_too_short' => 'ခေါင်းစဉ်သည် အနည်းဆုံး 5 လက္ခဏာရှိရမည်',
        'description_too_short' => 'ဖော်ပြချက်သည် အနည်းဆုံး 20 လက္ခဏာရှိရမည်',
        'confirm_submit' => 'ဤတိုင်ကြားချက်တင်သွင်းရန် သေချာပါသလား?\n\nတိုင်ကြားချက်ကို အမည်မဖော်ဘဲ တင်သွင်းမည်ဖြစ်ပြီး ပြင်ဆင်၍မရနိုင်ပါ',
        'error_occurred' => 'အမှားအယွင်းတစ်ခုဖြစ်ပေါ်ခဲ့သည်:',
        'view_my_complaints' => 'ကျွန်ုပ်၏တိုင်ကြားချက်များကြည့်မည် →',
        'failed_to_submit' => 'တိုင်ကြားချက်တင်သွင်းခြင်း မအောင်မြင်ပါ',
        'success_submitted' => 'တိုင်ကြားချက်တင်သွင်းပြီးပါပြီ',
        'anonymous_badge' => 'အမည်မဖော်',
    ]
];

// Get current language strings
$t = $translations[$current_lang] ?? $translations['th'];

ensure_session_started();
$user_id = $_SESSION['user_id'];

// Fetch employee data
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

if (!$employee) {
    echo "Error: Employee data not found";
    exit();
}

// Fetch active complaint categories
$categories = [];
$cat_name_field = "category_name_{$current_lang}";
$desc_field = "description_{$current_lang}";

$sql = "SELECT category_id, 
        category_name_th, category_name_en, category_name_my,
        description_th, description_en, description_my
        FROM complaint_category_master 
        WHERE is_active = 1 
        ORDER BY category_name_th";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $categories[] = $row;
}

$message = '';
$message_type = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = intval($_POST['category_id'] ?? 0);
    $subject = trim($_POST['subject'] ?? '');
    $description = trim($_POST['description'] ?? '');
    
    // Validate
    if ($category_id <= 0 || empty($subject) || empty($description)) {
        $message = $t['failed_to_submit'];
        $message_type = 'error';
    } else {
        // Handle file upload if provided
        $attachment_path = null;
        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = __DIR__ . '/../../uploads/complaints/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_extension = strtolower(pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['pdf', 'docx', 'jpg', 'jpeg', 'png'];
            
            if (in_array($file_extension, $allowed_extensions) && $_FILES['attachment']['size'] <= 5 * 1024 * 1024) {
                $unique_filename = uniqid('complaint_') . '.' . $file_extension;
                $upload_path = $upload_dir . $unique_filename;
                
                if (move_uploaded_file($_FILES['attachment']['tmp_name'], $upload_path)) {
                    $attachment_path = '/uploads/complaints/' . $unique_filename;
                }
            }
        }
        
        // Hash employee_id for anonymity
        $complainer_id_hash = hash('sha256', $user_id);
        
        // Get IP and User Agent
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
        $browser_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Insert complaint
            $stmt = $conn->prepare("
                INSERT INTO complaints 
                (complainer_id_hash, category_id, subject, description, attachment_path, status, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, 'New', NOW(), NOW())
            ");
            $stmt->bind_param("sisss", $complainer_id_hash, $category_id, $subject, $description, $attachment_path);
            
            if (!$stmt->execute()) {
                throw new Exception('Failed to insert complaint');
            }
            
            $complaint_id = $conn->insert_id;
            $stmt->close();
            
            // Insert audit record
            $stmt = $conn->prepare("
                INSERT INTO complaint_complainer_audit 
                (complaint_id, complainer_id_plain, complainer_id_hash, ip_address, browser_agent, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            $stmt->bind_param("issss", $complaint_id, $user_id, $complainer_id_hash, $ip_address, $browser_agent);
            
            if (!$stmt->execute()) {
                throw new Exception('Failed to insert audit record');
            }
            
            $stmt->close();
            
            // Commit transaction
            $conn->commit();
            
            // Redirect to my complaints page
            header("Location: " . BASE_PATH . "/views/employee/my_complaints.php?success=1");
            exit();
            
        } catch (Exception $e) {
            $conn->rollback();
            $message = $t['failed_to_submit'] . ': ' . $e->getMessage();
            $message_type = 'error';
        }
    }
}

$conn->close();

// Get display name
$display_name = $employee['full_name'] ?? 'Unknown';

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
        <div class="mb-8 bg-gradient-to-r from-red-600 to-pink-600 rounded-lg shadow-md p-6">
            <div class="flex items-center gap-4">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <div class="flex-1">
                    <div class="flex items-center gap-3">
                        <h1 class="text-3xl font-bold text-white"><?php echo $t['page_title']; ?></h1>
                        <span class="px-3 py-1 bg-white/20 backdrop-blur-sm rounded-full text-white text-sm font-semibold">
                            🔒 <?php echo $t['anonymous_badge']; ?>
                        </span>
                    </div>
                    <p class="text-red-100 text-sm mt-1"><?php echo $t['page_subtitle']; ?></p>
                </div>
            </div>
        </div>

        <!-- Main Form Card -->
        <div class="<?php echo $card_bg; ?> rounded-lg shadow-md border <?php echo $border_class; ?> p-6">
            <form method="POST" action="" id="complaintForm" enctype="multipart/form-data">
                
                <!-- Employee Information Section (Read-only) -->
                <div class="mb-8 pb-8 border-b <?php echo $border_class; ?>">
                    <h2 class="text-lg font-bold <?php echo $text_class; ?> mb-6 flex items-center gap-2">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        <?php echo $t['employee_information']; ?>
                        <span class="text-xs px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded text-gray-600 dark:text-gray-400">
                            (<?php echo $t['anonymous_badge']; ?>)
                        </span>
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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

                <!-- Category Selection -->
                <div class="mb-8">
                    <label for="category_id" class="block text-sm font-bold <?php echo $text_class; ?> mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                        </svg>
                        <?php echo $t['complaint_category']; ?> <span class="text-red-500">*</span>
                    </label>
                    <select id="category_id" name="category_id" required
                        class="w-full px-4 py-3 border rounded-lg <?php echo $input_class; ?> focus:outline-none focus:ring-2 focus:ring-red-500">
                        <option value=""><?php echo $t['select_category']; ?></option>
                        <?php foreach ($categories as $cat): 
                            $cat_name = $cat["category_name_{$current_lang}"] ?? $cat['category_name_th'];
                            $cat_desc = $cat["description_{$current_lang}"] ?? '';
                        ?>
                            <option value="<?php echo $cat['category_id']; ?>" title="<?php echo htmlspecialchars($cat_desc); ?>">
                                <?php echo htmlspecialchars($cat_name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Subject -->
                <div class="mb-8">
                    <label for="subject" class="block text-sm font-bold <?php echo $text_class; ?> mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                        </svg>
                        <?php echo $t['subject']; ?> <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="subject" name="subject" required
                        minlength="5" maxlength="300"
                        placeholder="<?php echo $t['subject_placeholder']; ?>"
                        class="w-full px-4 py-3 border rounded-lg <?php echo $input_class; ?> focus:outline-none focus:ring-2 focus:ring-red-500">
                </div>

                <!-- Description -->
                <div class="mb-8">
                    <label for="description" class="block text-sm font-bold <?php echo $text_class; ?> mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <?php echo $t['description']; ?> <span class="text-red-500">*</span>
                    </label>
                    <textarea id="description" name="description" required rows="8"
                        minlength="20"
                        placeholder="<?php echo $t['description_placeholder']; ?>"
                        class="w-full px-4 py-3 border rounded-lg <?php echo $input_class; ?> focus:outline-none focus:ring-2 focus:ring-red-500 resize-none"></textarea>
                    <p class="text-xs <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?> mt-2">
                        <span id="charCount">0</span> / 20 characters minimum
                    </p>
                </div>

                <!-- File Attachment (Optional) -->
                <div class="mb-8">
                    <label for="attachment" class="block text-sm font-bold <?php echo $text_class; ?> mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                        </svg>
                        <?php echo $t['attachment']; ?> 
                        <span class="text-xs font-normal <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?>"><?php echo $t['attachment_optional']; ?></span>
                    </label>
                    <input type="file" id="attachment" name="attachment" accept=".pdf,.docx,.jpg,.jpeg,.png"
                        class="w-full px-4 py-3 border rounded-lg <?php echo $input_class; ?> focus:outline-none focus:ring-2 focus:ring-red-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-red-50 file:text-red-700 hover:file:bg-red-100 dark:file:bg-red-900 dark:file:text-red-300">
                    <p class="text-xs <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?> mt-2">
                        <?php echo $t['attachment_note']; ?>
                    </p>
                </div>

                <!-- Important Notice -->
                <div class="mb-8 p-4 bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 rounded">
                    <div class="flex gap-3">
                        <svg class="w-5 h-5 text-red-600 dark:text-red-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-bold text-red-800 dark:text-red-300"><?php echo $t['important_notice']; ?></p>
                            <ul class="text-sm text-red-700 dark:text-red-400 mt-2 space-y-1">
                                <li><?php echo $t['notice_1']; ?></li>
                                <li><?php echo $t['notice_2']; ?></li>
                                <li><?php echo $t['notice_3']; ?></li>
                                <li><?php echo $t['notice_4']; ?></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex flex-col md:flex-row gap-4 pt-6 border-t <?php echo $border_class; ?>">
                    <a href="<?php echo BASE_PATH; ?>/index.php" class="flex-1 px-6 py-3 border rounded-lg <?php echo $border_class; ?> <?php echo $text_class; ?> hover:<?php echo $is_dark ? 'bg-gray-700' : 'bg-gray-50'; ?> transition font-medium text-center">
                        <?php echo $t['cancel']; ?>
                    </a>
                    <button type="submit" class="flex-1 px-8 py-3 bg-red-600 hover:bg-red-700 text-white rounded-lg transition font-medium shadow-md hover:shadow-lg">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
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
    
    // Character counter
    const descriptionField = document.getElementById('description');
    const charCountSpan = document.getElementById('charCount');
    
    if (descriptionField && charCountSpan) {
        descriptionField.addEventListener('input', function() {
            charCountSpan.textContent = this.value.length;
        });
    }
    
    // Form validation
    document.getElementById('complaintForm')?.addEventListener('submit', function(e) {
        const category = document.getElementById('category_id').value;
        const subject = document.getElementById('subject').value.trim();
        const description = document.getElementById('description').value.trim();
        
        if (!category || category === '') {
            e.preventDefault();
            alert(t['please_select_category']);
            return;
        }
        
        if (subject.length < 5) {
            e.preventDefault();
            alert(t['subject_too_short']);
            return;
        }
        
        if (description.length < 20) {
            e.preventDefault();
            alert(t['description_too_short']);
            return;
        }
        
        if (!confirm(t['confirm_submit'])) {
            e.preventDefault();
        }
    });
    
    // File size validation
    document.getElementById('attachment')?.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const fileSize = this.files[0].size / 1024 / 1024; // in MB
            if (fileSize > 5) {
                alert('File size must be less than 5MB');
                this.value = '';
            }
        }
    });
</script>