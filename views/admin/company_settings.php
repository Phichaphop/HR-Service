<?php

/**
 * Company Settings Page - STANDARDIZED UI VERSION ✅
 * ✅ Matches my_requests.php design and layout
 * ✅ Gradient header with icon
 * ✅ max-w-4xl container (consistent)
 * ✅ Full dark mode support
 * ✅ Responsive design - Mobile First
 * Supports: Thai (ไทย), English (EN), Myanmar (မြန်မာ)
 */
require_once __DIR__ . '/../../config/db_config.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../db/Localization.php';

AuthController::requireRole(['admin']);

// Get current settings from session
$current_lang = $_SESSION['language'] ?? 'th';
$theme_mode = $_SESSION['theme_mode'] ?? 'light';
$is_dark = ($theme_mode === 'dark');
$user_id = $_SESSION['user_id'] ?? '';

// Theme colors
$card_bg = $is_dark ? 'bg-gray-800' : 'bg-white';
$text_class = $is_dark ? 'text-white' : 'text-gray-900';
$bg_class = $is_dark ? 'bg-gray-900' : 'bg-gray-50';
$border_class = $is_dark ? 'border-gray-700' : 'border-gray-200';
$input_class = $is_dark ? 'bg-gray-700 border-gray-600 text-white' : 'bg-white border-gray-300 text-gray-900';
$label_class = $is_dark ? 'text-gray-300' : 'text-gray-700';

// Translations for 3 languages
$translations = [
    'th' => [
        'page_title' => 'ตั้งค่าบริษัท',
        'page_subtitle' => 'จัดการข้อมูลบริษัทและแบรนด์',
        'admin_only' => 'เฉพาะผู้ดูแลระบบ',
        'current_logo' => 'โลโก้ปัจจุบัน',
        'logo_display_info' => 'โลโก้จะแสดงบนใบรับรองและเอกสาร',
        'company_logo' => 'โลโก้บริษัท',
        'click_upload' => 'คลิกเพื่ออัปโหลด',
        'drag_drop' => 'หรือลากและวางที่นี่',
        'file_format' => 'PNG, JPG หรือ GIF (สูงสุด 5MB)',
        'company_name_th' => 'ชื่อบริษัท (ไทย)',
        'company_name_en' => 'ชื่อบริษัท (อังกฤษ)',
        'company_name_my' => 'ชื่อบริษัท (พม่า)',
        'phone' => 'เบอร์โทรศัพท์',
        'fax' => 'แฟกซ์',
        'address' => 'ที่อยู่',
        'address_placeholder' => '123 ถนนหลัก อำเภอ จังหวัด รหัสไปรษณีย์',
        'representative_name' => 'ชื่อผู้แทน',
        'representative_placeholder' => 'นาย สมชาย ใจดี',
        'representative_info' => 'ชื่อบุคคลนี้จะปรากฏบนเอกสารและใบรับรองอย่างเป็นทางการ',
        'important_notice' => 'ประกาศสำคัญ',
        'notice_message' => 'ข้อมูลนี้จะแสดงบนเอกสารอย่างเป็นทางการ ใบรับรอง และบันทึกพนักงานทั้งหมด โปรดตรวจสอบความถูกต้อง',
        'cancel' => 'ยกเลิก',
        'save_changes' => 'บันทึกการเปลี่ยนแปลง',
        'success_message' => 'อัปเดตข้อมูลบริษัทเรียบร้อยแล้ว',
        'error_message' => 'ไม่สามารถอัปเดตข้อมูลบริษัทได้',
        'error_upload' => 'ไม่สามารถอัปโหลดไฟล์ได้',
        'error_file_type' => 'ประเภทไฟล์ไม่ถูกต้อง (รองรับเฉพาะ JPG, PNG, GIF)',
        'error_file_size' => 'ไฟล์มีขนาดใหญ่เกินไป (สูงสุด 5MB)',
        'required' => 'จำเป็น',
    ],
    'en' => [
        'page_title' => 'Company Settings',
        'page_subtitle' => 'Manage company information and branding',
        'admin_only' => 'Admin Only',
        'current_logo' => 'Current Logo',
        'logo_display_info' => 'Logo is displayed on certificates and documents',
        'company_logo' => 'Company Logo',
        'click_upload' => 'Click to upload',
        'drag_drop' => 'or drag and drop',
        'file_format' => 'PNG, JPG or GIF (MAX. 5MB)',
        'company_name_th' => 'Company Name (Thai)',
        'company_name_en' => 'Company Name (English)',
        'company_name_my' => 'Company Name (Myanmar)',
        'phone' => 'Phone',
        'fax' => 'Fax',
        'address' => 'Address',
        'address_placeholder' => '123 Main Street, District, Province, Postal Code',
        'representative_name' => 'Representative Name',
        'representative_placeholder' => 'Mr. John Doe',
        'representative_info' => "This person's name will appear on official documents and certificates",
        'important_notice' => 'Important Notice',
        'notice_message' => 'This information will be displayed on all official documents, certificates, and employee records. Please ensure accuracy.',
        'cancel' => 'Cancel',
        'save_changes' => 'Save Changes',
        'success_message' => 'Company information updated successfully',
        'error_message' => 'Failed to update company information',
        'error_upload' => 'Failed to upload file',
        'error_file_type' => 'Invalid file type (Only JPG, PNG, GIF allowed)',
        'error_file_size' => 'File size too large (Max 5MB)',
        'required' => 'Required',
    ],
    'my' => [
        'page_title' => 'ကုမ္ပဏီဆက်တင်များ',
        'page_subtitle' => 'ကုမ္ပဏီအချက်အလက်နှင့် အမှတ်တံဆိပ်ကို စီမံခန့်ခွဲပါ',
        'admin_only' => 'စီမံခန့်ခွဲသူသာ',
        'current_logo' => 'လက်ရှိလိုဂို',
        'logo_display_info' => 'လိုဂိုကို လက်မှတ်များနှင့် စာရွက်စာတမ်းများတွင် ပြသထားသည်',
        'company_logo' => 'ကုမ္ပဏီလိုဂို',
        'click_upload' => 'အပ်လုဒ်လုပ်ရန် နှိပ်ပါ',
        'drag_drop' => 'သို့မဟုတ် ဆွဲယူ၍ချပါ',
        'file_format' => 'PNG, JPG သို့မဟုတ် GIF (အများဆုံး 5MB)',
        'company_name_th' => 'ကုမ္ပဏီအမည် (ထိုင်း)',
        'company_name_en' => 'ကုမ္ပဏီအမည် (အင်္ဂလိပ်)',
        'company_name_my' => 'ကုမ္ပဏီအမည် (မြန်မာ)',
        'phone' => 'ဖုန်းနံပါတ်',
        'fax' => 'ဖက်စ်',
        'address' => 'လိပ်စာ',
        'address_placeholder' => '၁၂၃ ပင်မလမ်း၊ ခရိုင်၊ ပြည်နယ်၊ စာတိုက်နံပါတ်',
        'representative_name' => 'ကိုယ်စားလှယ်အမည်',
        'representative_placeholder' => 'Mr. John Doe',
        'representative_info' => 'ဤပုဂ္ဂိုလ်၏အမည်သည် တရားဝင်စာရွက်စာတမ်းများနှင့် လက်မှတ်များတွင် ပေါ်လာပါမည်',
        'important_notice' => 'အရေးကြီးသောသတိပေးချက်',
        'notice_message' => 'ဤအချက်အလက်ကို တရားဝင်စာရွက်စာတမ်းများ၊ လက်မှတ်များနှင့် ဝန်ထမ်းမှတ်တမ်းများအားလုံးတွင် ပြသမည်ဖြစ်သည်။ ကျေးဇူးပြု၍ တိကျမှန်ကန်မှုကို သေချာစေပါ။',
        'cancel' => 'ပယ်ဖျက်ရန်',
        'save_changes' => 'ပြောင်းလဲမှုများသိမ်းရန်',
        'success_message' => 'ကုမ္ပဏီအချက်အလက်များကို အောင်မြင်စွာ အပ်ဒိတ်လုပ်ပြီးပါပြီ',
        'error_message' => 'ကုမ္ပဏီအချက်အလက်များကို အပ်ဒိတ်လုပ်၍ မရပါ',
        'error_upload' => 'ဖိုင်အပ်လုဒ်လုပ်၍ မရပါ',
        'error_file_type' => 'ဖိုင်အမျိုးအစားမှားယွင်းနေသည် (JPG, PNG, GIF သာခွင့်ပြုသည်)',
        'error_file_size' => 'ဖိုင်အရွယ်အစားကြီးလွန်းသည် (အများဆုံး 5MB)',
        'required' => 'လိုအပ်သည်',
    ]
];
$t = $translations[$current_lang];
$page_title = $t['page_title'];
$message = '';
$message_type = '';

// Get company info
try {
    $conn = getDbConnection();
    $result = $conn->query("SELECT * FROM company_info LIMIT 1");
    $company = $result ? $result->fetch_assoc() : null;
    $conn->close();
} catch (Exception $e) {
    $company = null;
    error_log("Error fetching company info: " . $e->getMessage());
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        $conn = getDbConnection();

        if ($_POST['action'] === 'update_company') {
            $company_name_th = trim($_POST['company_name_th'] ?? '');
            $company_name_en = trim($_POST['company_name_en'] ?? '');
            $company_name_my = trim($_POST['company_name_my'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $fax = trim($_POST['fax'] ?? '');
            $address = trim($_POST['address'] ?? '');
            $representative_name = trim($_POST['representative_name'] ?? '');

            // Handle logo upload
            $logo_path = '';
            if (isset($_FILES['company_logo']) && $_FILES['company_logo']['error'] === UPLOAD_ERR_OK) {
                if (!defined('UPLOAD_PATH_COMPANY')) {
                    define('UPLOAD_PATH_COMPANY', __DIR__ . '/../../uploads/company/');
                }

                $upload_dir = UPLOAD_PATH_COMPANY;

                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }

                $file_ext = strtolower(pathinfo($_FILES['company_logo']['name'], PATHINFO_EXTENSION));
                $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];

                if (!defined('UPLOAD_MAX_SIZE')) {
                    define('UPLOAD_MAX_SIZE', 5242880); // 5MB
                }

                if (!in_array($file_ext, $allowed_exts)) {
                    throw new Exception($t['error_file_type']);
                }

                if ($_FILES['company_logo']['size'] > UPLOAD_MAX_SIZE) {
                    throw new Exception($t['error_file_size']);
                }

                $new_filename = 'company_logo_' . time() . '.' . $file_ext;
                $upload_path = $upload_dir . $new_filename;

                if (!move_uploaded_file($_FILES['company_logo']['tmp_name'], $upload_path)) {
                    throw new Exception($t['error_upload']);
                }

                $logo_path = 'uploads/company/' . $new_filename;

                // AUTO-DELETE OLD LOGO WHEN UPLOADING NEW ONE
                if (!empty($company['company_logo_path'])) {

                    $old_logo_path = __DIR__ . '/../../' . $company['company_logo_path'];

                    if (file_exists($old_logo_path)) {
                        unlink($old_logo_path);
                    }
                }
            }

            // Check if company record exists
            $check = $conn->query("SELECT company_id FROM company_info LIMIT 1");

            if ($check && $check->num_rows > 0) {
                // Update existing
                if ($logo_path !== '') {
                    // Update with new logo
                    $columns_check = $conn->query("SHOW COLUMNS FROM company_info LIKE 'company_name_my'");

                    if ($columns_check && $columns_check->num_rows > 0) {
                        $stmt = $conn->prepare("UPDATE company_info SET company_name_th = ?, company_name_en = ?, company_name_my = ?, phone = ?, fax = ?, address = ?, representative_name = ?, company_logo_path = ?, updated_at = CURRENT_TIMESTAMP");
                        $stmt->bind_param("ssssssss", $company_name_th, $company_name_en, $company_name_my, $phone, $fax, $address, $representative_name, $logo_path);
                    } else {
                        $stmt = $conn->prepare("UPDATE company_info SET company_name_th = ?, company_name_en = ?, phone = ?, fax = ?, address = ?, representative_name = ?, company_logo_path = ?, updated_at = CURRENT_TIMESTAMP");
                        $stmt->bind_param("sssssss", $company_name_th, $company_name_en, $phone, $fax, $address, $representative_name, $logo_path);
                    }
                } else {
                    // No logo changes - only update company info
                    $columns_check = $conn->query("SHOW COLUMNS FROM company_info LIKE 'company_name_my'");

                    if ($columns_check && $columns_check->num_rows > 0) {
                        $stmt = $conn->prepare("UPDATE company_info SET company_name_th = ?, company_name_en = ?, company_name_my = ?, phone = ?, fax = ?, address = ?, representative_name = ?, updated_at = CURRENT_TIMESTAMP");
                        $stmt->bind_param("sssssss", $company_name_th, $company_name_en, $company_name_my, $phone, $fax, $address, $representative_name);
                    } else {
                        $stmt = $conn->prepare("UPDATE company_info SET company_name_th = ?, company_name_en = ?, phone = ?, fax = ?, address = ?, representative_name = ?, updated_at = CURRENT_TIMESTAMP");
                        $stmt->bind_param("ssssss", $company_name_th, $company_name_en, $phone, $fax, $address, $representative_name);
                    }
                }
            } else {
                // Insert new
                $columns_check = $conn->query("SHOW COLUMNS FROM company_info LIKE 'company_name_my'");

                if ($columns_check && $columns_check->num_rows > 0) {
                    $stmt = $conn->prepare("INSERT INTO company_info (company_name_th, company_name_en, company_name_my, phone, fax, address, representative_name, company_logo_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("ssssssss", $company_name_th, $company_name_en, $company_name_my, $phone, $fax, $address, $representative_name, $logo_path);
                } else {
                    $stmt = $conn->prepare("INSERT INTO company_info (company_name_th, company_name_en, phone, fax, address, representative_name, company_logo_path) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("sssssss", $company_name_th, $company_name_en, $phone, $fax, $address, $representative_name, $logo_path);
                }
            }

            if ($stmt->execute()) {
                $message = $t['success_message'];
                $message_type = 'success';
            } else {
                throw new Exception($stmt->error);
            }

            $stmt->close();
        }

        $conn->close();
    } catch (Exception $e) {
        $message = $t['error_message'] . ': ' . $e->getMessage();
        $message_type = 'error';
        error_log("Company Settings Error: " . $e->getMessage());
    }
}

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>
    <div class="lg:ml-64 min-h-screen">
        <div class="container mx-auto px-4 py-6">

        <!-- Success/Error Message -->
        <?php if ($message): ?>
            <div class="mb-6 p-4 rounded-lg <?php echo $message_type === 'success' ? 'bg-green-50 dark:bg-green-900/20 border-l-4 border-green-500' : 'bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500'; ?>">
                <div class="flex items-center">
                    <?php if ($message_type === 'success'): ?>
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-green-700 dark:text-green-300 font-medium"><?php echo htmlspecialchars($message); ?></p>
                    <?php else: ?>
                        <svg class="w-6 h-6 text-red-600 dark:text-red-400 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-red-700 dark:text-red-300 font-medium"><?php echo htmlspecialchars($message); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Page Header - Gradient Style (Like my_requests.php) -->
        <div class="mb-6 bg-gradient-to-r from-blue-600 to-blue-700 rounded-lg shadow-lg p-6">
            <div class="flex items-center">
                <svg class="w-10 h-10 text-white mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
                <div>
                    <h1 class="text-3xl font-bold text-white"><?php echo $t['page_title']; ?></h1>
                    <p class="text-blue-100 mt-1"><?php echo $t['page_subtitle']; ?></p>
                </div>
            </div>
        </div>

        <!-- Company Settings Form -->
        <div class="<?php echo $card_bg; ?> rounded-lg shadow-lg overflow-hidden border <?php echo $border_class; ?>">

            <!-- Form -->
            <form method="POST" action="" enctype="multipart/form-data" class="p-6 md:p-8 space-y-8">
                <input type="hidden" name="action" value="update_company">

                <!-- Section 1: Logo Upload -->
                <div class="border-b <?php echo $border_class; ?> pb-8">
                    <h3 class="text-lg font-bold <?php echo $text_class; ?> mb-6 flex items-center">
                        <svg class="w-6 h-6 mr-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <?php echo $t['company_logo']; ?>
                    </h3>

                    <!-- Current Logo Preview -->
                    <div id="currentLogoSection" class="mb-6 p-4 rounded-lg <?php echo $is_dark ? 'bg-gray-700' : 'bg-gray-50'; ?> border-l-4 border-blue-500" <?php echo (!$company || !isset($company['company_logo_path']) || !$company['company_logo_path']) ? 'style="display:none;"' : ''; ?>>
                        <p class="text-sm font-semibold <?php echo $label_class; ?> mb-3 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <?php echo $t['current_logo']; ?>
                        </p>
                        <img id="currentLogoImage" src="<?php echo $company && isset($company['company_logo_path']) ? BASE_PATH . '/' . htmlspecialchars($company['company_logo_path']) : ''; ?>"
                            alt="Company Logo"
                            class="h-24 w-auto border-2 <?php echo $is_dark ? 'border-gray-600' : 'border-gray-200'; ?> rounded-lg shadow-sm mb-3"
                            onerror="this.style.display='none'">
                        <p class="text-xs <?php echo $is_dark ? 'text-blue-400' : 'text-blue-600'; ?> flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4v.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            🗑️ อัปโหลดรูปใหม่จะแทนที่รูปนี้โดยอัตโนมัติ
                        </p>
                    </div>

                    <!-- Logo Upload Area -->
                    <div class="flex items-center justify-center w-full">
                        <label class="flex flex-col items-center justify-center w-full h-40 border-2 border-dashed <?php echo $is_dark ? 'border-gray-600 hover:border-blue-500' : 'border-gray-300 hover:border-blue-500'; ?> rounded-lg cursor-pointer <?php echo $is_dark ? 'bg-gray-700 hover:bg-gray-600' : 'bg-gray-50 hover:bg-gray-100'; ?> transition" id="logoUploadZone">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                <svg class="w-10 h-10 mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                </svg>
                                <p class="mb-2 text-sm <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?>">
                                    <span class="font-semibold text-blue-600 dark:text-blue-400"><?php echo $t['click_upload']; ?></span> <span class="<?php echo $label_class; ?>"><?php echo $t['drag_drop']; ?></span>
                                </p>
                                <p class="text-xs <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?>">
                                    <?php echo $t['file_format']; ?>
                                </p>
                            </div>
                            <input type="file" name="company_logo" class="hidden" accept="image/*" id="logoInput">
                        </label>
                    </div>

                    <!-- Preview Section (Hidden by default) -->
                    <div id="previewContainer" class="hidden mt-6 p-4 rounded-lg <?php echo $is_dark ? 'bg-gray-700' : 'bg-gray-50'; ?> border <?php echo $border_class; ?>">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <p class="text-sm font-semibold <?php echo $label_class; ?> mb-3">📸 ตัวอย่างรูปที่เลือก</p>
                                <img id="previewImage" src="" alt="Preview" class="max-h-40 w-auto rounded-lg shadow-md mb-4 border <?php echo $border_class; ?>">
                                <div class="grid grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <p class="text-xs <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?> uppercase font-semibold">ชื่อไฟล์</p>
                                        <p class="<?php echo $text_class; ?> font-mono" id="fileName">-</p>
                                    </div>
                                    <div>
                                        <p class="text-xs <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?> uppercase font-semibold">ขนาดไฟล์</p>
                                        <p class="<?php echo $text_class; ?>" id="fileSize">-</p>
                                    </div>
                                    <div>
                                        <p class="text-xs <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?> uppercase font-semibold">ประเภท</p>
                                        <p class="<?php echo $text_class; ?>" id="fileType">-</p>
                                    </div>
                                    <div>
                                        <p class="text-xs <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?> uppercase font-semibold">ขนาดภาพ</p>
                                        <p class="<?php echo $text_class; ?>" id="imageDimensions">-</p>
                                    </div>
                                </div>
                            </div>
                            <!-- Clear Button -->
                            <button type="button" onclick="clearLogoPreview()"
                                class="ml-4 p-2 text-red-600 hover:text-red-800 hover:bg-red-50 dark:text-red-400 dark:hover:text-red-300 dark:hover:bg-gray-600 rounded-lg transition"
                                title="ลบตัวอย่าง">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Company Names (3 Languages) -->
                <div class="border-b <?php echo $border_class; ?> pb-8">
                    <h3 class="text-lg font-bold <?php echo $text_class; ?> mb-6 flex items-center">
                        <svg class="w-6 h-6 mr-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        ชื่อบริษัท
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-semibold <?php echo $label_class; ?> mb-2">
                                <?php echo $t['company_name_th']; ?> <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="company_name_th" required
                                value="<?php echo htmlspecialchars($company['company_name_th'] ?? ''); ?>"
                                class="w-full px-4 py-3 border <?php echo $input_class; ?> rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold <?php echo $label_class; ?> mb-2">
                                <?php echo $t['company_name_en']; ?> <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="company_name_en" required
                                value="<?php echo htmlspecialchars($company['company_name_en'] ?? ''); ?>"
                                class="w-full px-4 py-3 border <?php echo $input_class; ?> rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold <?php echo $label_class; ?> mb-2">
                                <?php echo $t['company_name_my']; ?>
                            </label>
                            <input type="text" name="company_name_my"
                                value="<?php echo htmlspecialchars($company['company_name_my'] ?? ''); ?>"
                                class="w-full px-4 py-3 border <?php echo $input_class; ?> rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                        </div>
                    </div>
                </div>

                <!-- Section 3: Contact Information -->
                <div class="border-b <?php echo $border_class; ?> pb-8">
                    <h3 class="text-lg font-bold <?php echo $text_class; ?> mb-6 flex items-center">
                        <svg class="w-6 h-6 mr-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        ข้อมูลติดต่อ
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold <?php echo $label_class; ?> mb-2">
                                <?php echo $t['phone']; ?> <span class="text-red-500">*</span>
                            </label>
                            <input type="tel" name="phone" required
                                value="<?php echo htmlspecialchars($company['phone'] ?? ''); ?>"
                                placeholder="042-123-456"
                                class="w-full px-4 py-3 border <?php echo $input_class; ?> rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold <?php echo $label_class; ?> mb-2">
                                <?php echo $t['fax']; ?>
                            </label>
                            <input type="tel" name="fax"
                                value="<?php echo htmlspecialchars($company['fax'] ?? ''); ?>"
                                placeholder="042-123-457"
                                class="w-full px-4 py-3 border <?php echo $input_class; ?> rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                        </div>
                    </div>
                </div>

                <!-- Section 4: Address -->
                <div class="border-b <?php echo $border_class; ?> pb-8">
                    <h3 class="text-lg font-bold <?php echo $text_class; ?> mb-6 flex items-center">
                        <svg class="w-6 h-6 mr-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        ที่อยู่
                    </h3>
                    <label class="block text-sm font-semibold <?php echo $label_class; ?> mb-2">
                        <?php echo $t['address']; ?> <span class="text-red-500">*</span>
                    </label>
                    <textarea name="address" rows="3" required
                        class="w-full px-4 py-3 border <?php echo $input_class; ?> rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition resize-none"
                        placeholder="<?php echo $t['address_placeholder']; ?>"><?php echo htmlspecialchars($company['address'] ?? ''); ?></textarea>
                </div>

                <!-- Section 5: Representative Name -->
                <div class="border-b <?php echo $border_class; ?> pb-8">
                    <h3 class="text-lg font-bold <?php echo $text_class; ?> mb-6 flex items-center">
                        <svg class="w-6 h-6 mr-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        บุคลากร
                    </h3>
                    <label class="block text-sm font-semibold <?php echo $label_class; ?> mb-2">
                        <?php echo $t['representative_name']; ?> <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="representative_name" required
                        value="<?php echo htmlspecialchars($company['representative_name'] ?? ''); ?>"
                        placeholder="<?php echo $t['representative_placeholder']; ?>"
                        class="w-full px-4 py-3 border <?php echo $input_class; ?> rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition mb-3">
                    <p class="text-sm <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?>">
                        ℹ️ <?php echo $t['representative_info']; ?>
                    </p>
                </div>

                <!-- Important Notice -->
                <div class="p-4 bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-400 rounded-r-lg">
                    <div class="flex">
                        <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-bold text-yellow-800 dark:text-yellow-300"><?php echo $t['important_notice']; ?></p>
                            <p class="text-sm text-yellow-700 dark:text-yellow-400 mt-1">
                                <?php echo $t['notice_message']; ?>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="flex justify-end gap-4 pt-6 border-t <?php echo $border_class; ?>">
                    <a href="<?php echo BASE_PATH; ?>/index.php"
                        class="px-8 py-3 bg-gray-300 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-lg font-bold hover:bg-gray-400 dark:hover:bg-gray-600 transition shadow-md hover:shadow-lg">
                        ✕ <?php echo $t['cancel']; ?>
                    </a>
                    <button type="submit"
                        class="px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-bold transition shadow-lg hover:shadow-xl flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <?php echo $t['save_changes']; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>

<script>
    // Logo Preview Functionality
    const logoInput = document.getElementById('logoInput');
    const previewContainer = document.getElementById('previewContainer');
    const previewImage = document.getElementById('previewImage');
    const fileName = document.getElementById('fileName');
    const fileSize = document.getElementById('fileSize');
    const fileType = document.getElementById('fileType');
    const imageDimensions = document.getElementById('imageDimensions');
    const logoUploadZone = document.getElementById('logoUploadZone');
    const currentLogoSection = document.getElementById('currentLogoSection');

    // Format file size to readable format
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    }

    // Handle file selection
    logoInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;

        // Validate file type
        const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!allowedTypes.includes(file.type)) {
            alert('❌ ประเภทไฟล์ไม่ถูกต้อง (รองรับเฉพาะ JPG, PNG, GIF)');
            logoInput.value = '';
            return;
        }

        // Validate file size (5MB)
        const maxSize = 5242880;
        if (file.size > maxSize) {
            alert('❌ ไฟล์มีขนาดใหญ่เกินไป (สูงสุด 5MB)');
            logoInput.value = '';
            return;
        }

        // Display file info
        fileName.textContent = file.name;
        fileSize.textContent = formatFileSize(file.size);
        fileType.textContent = file.type.split('/')[1].toUpperCase();

        // Read and display image
        const reader = new FileReader();
        reader.onload = function(event) {
            previewImage.src = event.target.result;

            // Get image dimensions
            const img = new Image();
            img.onload = function() {
                imageDimensions.textContent = this.width + ' × ' + this.height + ' px';
            };
            img.src = event.target.result;

            // Show preview container
            previewContainer.classList.remove('hidden');

            // ✅ Hide current logo section - รูปเก่าจะลบอัตโนมัติ
            if (currentLogoSection) {
                currentLogoSection.style.display = 'none';
            }

            // Show notification
            showNotification('✅ รูปใหม่จะแทนที่รูปเก่าเมื่อบันทึก');
        };
        reader.readAsDataURL(file);
    });

    // Drag and drop functionality
    logoUploadZone.addEventListener('dragover', function(e) {
        e.preventDefault();
        logoUploadZone.classList.add('border-blue-500', '<?php echo $is_dark ? 'bg-gray-600' : 'bg-blue-50'; ?>');
    });

    logoUploadZone.addEventListener('dragleave', function(e) {
        e.preventDefault();
        logoUploadZone.classList.remove('border-blue-500', '<?php echo $is_dark ? 'bg-gray-600' : 'bg-blue-50'; ?>');
    });

    logoUploadZone.addEventListener('drop', function(e) {
        e.preventDefault();
        logoUploadZone.classList.remove('border-blue-500', '<?php echo $is_dark ? 'bg-gray-600' : 'bg-blue-50'; ?>');

        const files = e.dataTransfer.files;
        if (files.length > 0) {
            logoInput.files = files;
            logoInput.dispatchEvent(new Event('change'));
        }
    });

    // Clear preview
    function clearLogoPreview() {
        logoInput.value = '';
        previewContainer.classList.add('hidden');
        previewImage.src = '';
        fileName.textContent = '-';
        fileSize.textContent = '-';
        fileType.textContent = '-';
        imageDimensions.textContent = '-';

        // Show current logo section again
        if (currentLogoSection && currentLogoSection.style.display === 'none') {
            currentLogoSection.style.display = 'block';
        }
    }

    // Show temporary notification
    function showNotification(message) {
        const notification = document.createElement('div');
        notification.className = 'fixed top-4 right-4 bg-blue-500 text-white px-6 py-3 rounded-lg shadow-lg animate-pulse z-50';
        notification.textContent = message;
        document.body.appendChild(notification);

        setTimeout(() => {
            notification.remove();
        }, 3000);
    }
</script>