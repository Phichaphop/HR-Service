<?php
require_once __DIR__ . '/../../config/db_config.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../db/Localization.php';

// Enable error reporting for debugging (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

AuthController::requireRole(['admin']);

// Get current language from session
$current_lang = $_SESSION['language'] ?? 'th';

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
                // Check if upload directory constant exists
                if (!defined('UPLOAD_PATH_COMPANY')) {
                    define('UPLOAD_PATH_COMPANY', __DIR__ . '/../../uploads/company/');
                }
                
                $upload_dir = UPLOAD_PATH_COMPANY;
                
                // Create directory if it doesn't exist
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                $file_ext = strtolower(pathinfo($_FILES['company_logo']['name'], PATHINFO_EXTENSION));
                $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];
                
                // Check if max size constant exists
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
            }
            
            // Check if company record exists
            $check = $conn->query("SELECT company_id FROM company_info LIMIT 1");
            
            if ($check && $check->num_rows > 0) {
                // Update existing
                if ($logo_path) {
                    // Check if company_name_my column exists
                    $columns_check = $conn->query("SHOW COLUMNS FROM company_info LIKE 'company_name_my'");
                    
                    if ($columns_check && $columns_check->num_rows > 0) {
                        // Column exists - use 3 language fields
                        $stmt = $conn->prepare("UPDATE company_info SET company_name_th = ?, company_name_en = ?, company_name_my = ?, phone = ?, fax = ?, address = ?, representative_name = ?, company_logo_path = ?, updated_at = CURRENT_TIMESTAMP");
                        $stmt->bind_param("ssssssss", $company_name_th, $company_name_en, $company_name_my, $phone, $fax, $address, $representative_name, $logo_path);
                    } else {
                        // Column doesn't exist - use only 2 language fields
                        $stmt = $conn->prepare("UPDATE company_info SET company_name_th = ?, company_name_en = ?, phone = ?, fax = ?, address = ?, representative_name = ?, company_logo_path = ?, updated_at = CURRENT_TIMESTAMP");
                        $stmt->bind_param("sssssss", $company_name_th, $company_name_en, $phone, $fax, $address, $representative_name, $logo_path);
                    }
                } else {
                    // Without logo update
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
        
        // Log error for debugging
        error_log("Company Settings Error: " . $e->getMessage());
    }
}

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

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>

<div class="lg:ml-64">
    <div class="container mx-auto px-4 py-6 max-w-5xl">
        
        <?php if ($message): ?>
            <div class="mb-6 p-4 rounded-lg <?php echo $message_type === 'success' ? 'bg-green-50 dark:bg-green-900/20 border-l-4 border-green-500' : 'bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500'; ?>">
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
            </div>
        <?php endif; ?>

        <!-- Company Settings Form -->
        <div class="<?php echo $card_bg; ?> rounded-lg shadow-lg overflow-hidden">
            
            <!-- Header -->
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <svg class="w-8 h-8 text-white mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        <div>
                            <h2 class="text-2xl font-bold text-white"><?php echo $t['page_title']; ?></h2>
                            <p class="text-blue-100 text-sm"><?php echo $t['page_subtitle']; ?></p>
                        </div>
                    </div>
                    <div class="hidden md:block">
                        <span class="px-4 py-2 bg-white bg-opacity-20 backdrop-blur-sm rounded-full text-white text-sm font-medium">
                            <?php echo $t['admin_only']; ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Form -->
            <form method="POST" action="" enctype="multipart/form-data" class="p-6 space-y-6">
                <input type="hidden" name="action" value="update_company">
                
                <!-- Current Logo Preview -->
                <?php if ($company && isset($company['company_logo_path']) && $company['company_logo_path']): ?>
                <div class="mb-6">
                    <label class="block text-sm font-medium <?php echo $is_dark ? 'text-gray-300' : 'text-gray-700'; ?> mb-2">
                        <?php echo $t['current_logo']; ?>
                    </label>
                    <div class="flex items-center space-x-4">
                        <img src="<?php echo BASE_PATH . '/' . htmlspecialchars($company['company_logo_path']); ?>" 
                             alt="Company Logo" 
                             class="h-20 w-auto border-2 <?php echo $is_dark ? 'border-gray-600' : 'border-gray-200'; ?> rounded-lg shadow-sm"
                             onerror="this.style.display='none'">
                        <p class="text-xs <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?>">
                            <?php echo $t['logo_display_info']; ?>
                        </p>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Company Logo Upload -->
                <div>
                    <label class="block text-sm font-medium <?php echo $is_dark ? 'text-gray-300' : 'text-gray-700'; ?> mb-2">
                        <?php echo $t['company_logo']; ?>
                    </label>
                    <div class="flex items-center justify-center w-full">
                        <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed <?php echo $is_dark ? 'border-gray-600 hover:border-blue-500' : 'border-gray-300 hover:border-blue-500'; ?> rounded-lg cursor-pointer <?php echo $is_dark ? 'bg-gray-700 hover:bg-gray-600' : 'bg-gray-50 hover:bg-gray-100'; ?> transition">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                <svg class="w-10 h-10 mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                </svg>
                                <p class="mb-2 text-sm <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?>">
                                    <span class="font-semibold"><?php echo $t['click_upload']; ?></span> <?php echo $t['drag_drop']; ?>
                                </p>
                                <p class="text-xs <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?>">
                                    <?php echo $t['file_format']; ?>
                                </p>
                            </div>
                            <input type="file" name="company_logo" class="hidden" accept="image/*">
                        </label>
                    </div>
                </div>

                <!-- Company Names (3 Languages) -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium <?php echo $is_dark ? 'text-gray-300' : 'text-gray-700'; ?> mb-2">
                            <?php echo $t['company_name_th']; ?> <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="company_name_th" required
                               value="<?php echo htmlspecialchars($company['company_name_th'] ?? ''); ?>"
                               class="w-full px-4 py-3 border <?php echo $is_dark ? 'bg-gray-700 border-gray-600 text-white' : 'border-gray-300'; ?> rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium <?php echo $is_dark ? 'text-gray-300' : 'text-gray-700'; ?> mb-2">
                            <?php echo $t['company_name_en']; ?> <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="company_name_en" required
                               value="<?php echo htmlspecialchars($company['company_name_en'] ?? ''); ?>"
                               class="w-full px-4 py-3 border <?php echo $is_dark ? 'bg-gray-700 border-gray-600 text-white' : 'border-gray-300'; ?> rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium <?php echo $is_dark ? 'text-gray-300' : 'text-gray-700'; ?> mb-2">
                            <?php echo $t['company_name_my']; ?>
                        </label>
                        <input type="text" name="company_name_my"
                               value="<?php echo htmlspecialchars($company['company_name_my'] ?? ''); ?>"
                               class="w-full px-4 py-3 border <?php echo $is_dark ? 'bg-gray-700 border-gray-600 text-white' : 'border-gray-300'; ?> rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium <?php echo $is_dark ? 'text-gray-300' : 'text-gray-700'; ?> mb-2">
                            <?php echo $t['phone']; ?> <span class="text-red-500">*</span>
                        </label>
                        <input type="tel" name="phone" required
                               value="<?php echo htmlspecialchars($company['phone'] ?? ''); ?>"
                               placeholder="042-123-456"
                               class="w-full px-4 py-3 border <?php echo $is_dark ? 'bg-gray-700 border-gray-600 text-white' : 'border-gray-300'; ?> rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium <?php echo $is_dark ? 'text-gray-300' : 'text-gray-700'; ?> mb-2">
                            <?php echo $t['fax']; ?>
                        </label>
                        <input type="tel" name="fax"
                               value="<?php echo htmlspecialchars($company['fax'] ?? ''); ?>"
                               placeholder="042-123-457"
                               class="w-full px-4 py-3 border <?php echo $is_dark ? 'bg-gray-700 border-gray-600 text-white' : 'border-gray-300'; ?> rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <!-- Address -->
                <div>
                    <label class="block text-sm font-medium <?php echo $is_dark ? 'text-gray-300' : 'text-gray-700'; ?> mb-2">
                        <?php echo $t['address']; ?> <span class="text-red-500">*</span>
                    </label>
                    <textarea name="address" rows="3" required
                              class="w-full px-4 py-3 border <?php echo $is_dark ? 'bg-gray-700 border-gray-600 text-white' : 'border-gray-300'; ?> rounded-lg focus:ring-2 focus:ring-blue-500"
                              placeholder="<?php echo $t['address_placeholder']; ?>"><?php echo htmlspecialchars($company['address'] ?? ''); ?></textarea>
                </div>

                <!-- Representative Name -->
                <div>
                    <label class="block text-sm font-medium <?php echo $is_dark ? 'text-gray-300' : 'text-gray-700'; ?> mb-2">
                        <?php echo $t['representative_name']; ?> <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="representative_name" required
                           value="<?php echo htmlspecialchars($company['representative_name'] ?? ''); ?>"
                           placeholder="<?php echo $t['representative_placeholder']; ?>"
                           class="w-full px-4 py-3 border <?php echo $is_dark ? 'bg-gray-700 border-gray-600 text-white' : 'border-gray-300'; ?> rounded-lg focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?> mt-1">
                        <?php echo $t['representative_info']; ?>
                    </p>
                </div>

                <!-- Important Notice -->
                <div class="p-4 bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-400 rounded">
                    <div class="flex">
                        <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-yellow-800 dark:text-yellow-300"><?php echo $t['important_notice']; ?></p>
                            <p class="text-xs text-yellow-700 dark:text-yellow-400 mt-1">
                                <?php echo $t['notice_message']; ?>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end space-x-4 pt-4">
                    <a href="<?php echo BASE_PATH; ?>/index.php" 
                       class="px-6 py-3 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg font-medium hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                        <?php echo $t['cancel']; ?>
                    </a>
                    <button type="submit" 
                            class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition shadow-lg hover:shadow-xl">
                        <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <?php echo $t['save_changes']; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>