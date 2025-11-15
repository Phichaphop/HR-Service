<?php
require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../models/Employee.php';
require_once __DIR__ . '/../db/Localization.php';

AuthController::requireAuth();

// Get session variables
ensure_session_started();
$user_id = $_SESSION['user_id'];
$current_lang = $_SESSION['language'] ?? 'th';
$theme_mode = $_SESSION['theme_mode'] ?? 'light';

// Define theme classes
$is_dark = ($theme_mode === 'dark');
$bg_class = $is_dark ? 'bg-gray-900' : 'bg-gray-50';
$text_class = $is_dark ? 'text-gray-100' : 'text-gray-800';
$card_bg = $is_dark ? 'bg-gray-800' : 'bg-white';
$border_class = $is_dark ? 'border-gray-700' : 'border-gray-200';
$input_class = $is_dark ? 'bg-gray-700 border-gray-600 text-white' : 'bg-white border-gray-300 text-gray-900';
$label_class = $is_dark ? 'text-gray-300' : 'text-gray-700';

// Translations
$translations = [
    'th' => [
        'page_title' => 'ตั้งค่า',
        'page_subtitle' => 'จัดการบัญชีและความตั้งค่าส่วนตัวของคุณ',
        'profile_info' => 'ข้อมูลโปรไฟล์',
        'employee_id' => 'รหัสพนักงาน',
        'username' => 'ชื่อผู้ใช้',
        'full_name' => 'ชื่อ-นามสกุล',
        'email' => 'อีเมล',
        'phone' => 'เบอร์โทรศัพท์',
        'preferences' => 'ค่ากำหนดและการเลือก',
        'theme_mode' => 'โหมดธีม',
        'light_mode' => 'โหมดสว่าง',
        'dark_mode' => 'โหมดมืด',
        'language' => 'ภาษา',
        'security' => 'ความปลอดภัย',
        'change_password' => 'เปลี่ยนรหัสผ่าน',
        'current_password' => 'รหัสผ่านปัจจุบัน',
        'new_password' => 'รหัสผ่านใหม่',
        'confirm_password' => 'ยืนยันรหัสผ่านใหม่',
        'min_characters' => 'อย่างน้อย 6 ตัวอักษร',
        'change_password_btn' => 'เปลี่ยนรหัสผ่าน',
        'confirm_change' => 'คุณแน่ใจหรือไม่ว่าต้องการเปลี่ยนรหัสผ่าน?',
        'password_mismatch' => 'รหัสผ่านไม่ตรงกัน',
        'password_too_short' => 'รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร',
        'password_changed' => 'เปลี่ยนรหัสผ่านเรียบร้อยแล้ว',
        'password_change_failed' => 'ไม่สามารถเปลี่ยนรหัสผ่านได้',
        'current_password_incorrect' => 'รหัสผ่านปัจจุบันไม่ถูกต้อง',
        'required' => 'จำเป็น',
    ],
    'en' => [
        'page_title' => 'Settings',
        'page_subtitle' => 'Manage your account and personal preferences',
        'profile_info' => 'Profile Information',
        'employee_id' => 'Employee ID',
        'username' => 'Username',
        'full_name' => 'Full Name',
        'email' => 'Email',
        'phone' => 'Phone Number',
        'preferences' => 'Preferences & Choices',
        'theme_mode' => 'Theme Mode',
        'light_mode' => 'Light Mode',
        'dark_mode' => 'Dark Mode',
        'language' => 'Language',
        'security' => 'Security',
        'change_password' => 'Change Password',
        'current_password' => 'Current Password',
        'new_password' => 'New Password',
        'confirm_password' => 'Confirm New Password',
        'min_characters' => 'Minimum 6 characters',
        'change_password_btn' => 'Change Password',
        'confirm_change' => 'Are you sure you want to change your password?',
        'password_mismatch' => 'New passwords do not match',
        'password_too_short' => 'Password must be at least 6 characters',
        'password_changed' => 'Password changed successfully',
        'password_change_failed' => 'Failed to change password',
        'current_password_incorrect' => 'Current password is incorrect',
        'required' => 'Required',
    ],
    'my' => [
        'page_title' => 'ဆက်တင်များ',
        'page_subtitle' => 'သင့်အကောင့်နှင့်ကိုယ်ပိုင်ဦးစားပေးချက်များကိုစီမံခန့်ခွဲပါ',
        'profile_info' => 'ပရိုဖိုင်အချက်အလက်',
        'employee_id' => 'ဝန်ထမ်းနံပါတ်',
        'username' => 'အသုံးပြုသူအမည်',
        'full_name' => 'အမည်အပြည့်အစုံ',
        'email' => 'အီးမေးလ်',
        'phone' => 'ဖုန်းနံပါတ်',
        'preferences' => 'ဦးစားပေးချက်များ',
        'theme_mode' => 'ပုံစံမုဒ်',
        'light_mode' => 'အလင်းမုဒ်',
        'dark_mode' => 'အမှောင်မုဒ်',
        'language' => 'ဘာသာစကား',
        'security' => 'လုံခြုံရေး',
        'change_password' => 'လျှို့ဝှက်နံပါတ်ပြောင်းရန်',
        'current_password' => 'လက်ရှိလျှို့ဝှက်နံပါတ်',
        'new_password' => 'လျှို့ဝှက်နံပါတ်အသစ်',
        'confirm_password' => 'လျှို့ဝှက်နံပါတ်အသစ်အတည်ပြုပါ',
        'min_characters' => 'အနည်းဆုံး ၆ လုံး',
        'change_password_btn' => 'လျှို့ဝှက်နံပါတ်ပြောင်းရန်',
        'confirm_change' => 'သင့်လျှို့ဝှက်နံပါတ်ကိုပြောင်းလဲလိုသည်မှာသေချာပါသလား?',
        'password_mismatch' => 'လျှို့ဝှက်နံပါတ်များကိုက်ညီမှုမရှိပါ',
        'password_too_short' => 'လျှို့ဝှက်နံပါတ်သည်အနည်းဆုံး ၆ လုံးဖြစ်ရမည်',
        'password_changed' => 'လျှို့ဝှက်နံပါတ်အောင်မြင်စွာပြောင်းလဲပြီးပါပြီ',
        'password_change_failed' => 'လျှို့ဝှက်နံပါတ်ပြောင်းလဲ၍မရပါ',
        'current_password_incorrect' => 'လက်ရှိလျှို့ဝှက်နံပါတ်မမှန်ကန်ပါ',
        'required' => 'လိုအပ်သည်',
    ]
];

$t = $translations[$current_lang];
$page_title = $t['page_title'];

// Get employee data
$employee = Employee::getById($user_id);
$message = '';
$message_type = '';

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'change_password') {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if ($new_password !== $confirm_password) {
            $message = $t['password_mismatch'];
            $message_type = 'error';
        } elseif (strlen($new_password) < 6) {
            $message = $t['password_too_short'];
            $message_type = 'error';
        } else {
            if (password_verify($current_password, $employee['password'])) {
                $conn = getDbConnection();
                $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE employees SET password = ?, updated_at = CURRENT_TIMESTAMP WHERE employee_id = ?");
                $stmt->bind_param("ss", $new_hash, $user_id);
                if ($stmt->execute()) {
                    $message = $t['password_changed'];
                    $message_type = 'success';
                } else {
                    $message = $t['password_change_failed'];
                    $message_type = 'error';
                }
                $stmt->close();
                $conn->close();
            } else {
                $message = $t['current_password_incorrect'];
                $message_type = 'error';
            }
        }
    }
}

// Include header and sidebar
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="lg:ml-64 min-h-screen <?php echo $bg_class; ?>">
    <div class="container mx-auto px-4 py-6 max-w-4xl">
        
        <!-- Page Header with Gradient -->
        <div class="mb-8 bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg shadow-lg p-6">
            <div class="flex items-center">
                <svg class="w-10 h-10 text-white mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                <div>
                    <h1 class="text-3xl font-bold text-white"><?php echo $t['page_title']; ?></h1>
                    <p class="text-blue-100 mt-1"><?php echo $t['page_subtitle']; ?></p>
                </div>
            </div>
        </div>

        <!-- Success/Error Message -->
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

        <!-- Content Grid -->
        <div class="space-y-6">
            
            <!-- Profile Information Card -->
            <div class="<?php echo $card_bg; ?> rounded-lg shadow-lg border <?php echo $border_class; ?> p-6">
                <h2 class="text-xl font-bold <?php echo $text_class; ?> mb-6 flex items-center">
                    <svg class="w-6 h-6 mr-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <?php echo $t['profile_info']; ?>
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium <?php echo $label_class; ?> mb-2">
                            <?php echo $t['employee_id']; ?>
                        </label>
                        <input type="text" value="<?php echo htmlspecialchars($employee['employee_id']); ?>" readonly
                            class="w-full px-4 py-3 <?php echo $input_class; ?> border rounded-lg cursor-not-allowed opacity-70">
                    </div>
                    <div>
                        <label class="block text-sm font-medium <?php echo $label_class; ?> mb-2">
                            <?php echo $t['username']; ?>
                        </label>
                        <input type="text" value="<?php echo htmlspecialchars($employee['username']); ?>" readonly
                            class="w-full px-4 py-3 <?php echo $input_class; ?> border rounded-lg cursor-not-allowed opacity-70">
                    </div>
                    <div>
                        <label class="block text-sm font-medium <?php echo $label_class; ?> mb-2">
                            <?php echo $t['full_name']; ?>
                        </label>
                        <input type="text"
                            value="<?php
                                    if (empty($employee['full_name_th']) || $employee['full_name_th'] == '0') {
                                        echo htmlspecialchars($employee['full_name_en']);
                                    } else {
                                        echo htmlspecialchars($employee['full_name_th']);
                                    }
                                    ?>"
                            readonly
                            class="w-full px-4 py-3 <?php echo $input_class; ?> border rounded-lg cursor-not-allowed opacity-70">
                    </div>
                    <div>
                        <label class="block text-sm font-medium <?php echo $label_class; ?> mb-2">
                            <?php echo $t['email']; ?>
                        </label>
                        <input type="email" value="<?php echo htmlspecialchars($employee['email'] ?? ''); ?>" readonly
                            class="w-full px-4 py-3 <?php echo $input_class; ?> border rounded-lg cursor-not-allowed opacity-70">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium <?php echo $label_class; ?> mb-2">
                            <?php echo $t['phone']; ?>
                        </label>
                        <input type="text" value="<?php echo htmlspecialchars($employee['phone_no'] ?? ''); ?>" readonly
                            class="w-full px-4 py-3 <?php echo $input_class; ?> border rounded-lg cursor-not-allowed opacity-70">
                    </div>
                </div>
            </div>

            <!-- Preferences Card -->
            <div class="<?php echo $card_bg; ?> rounded-lg shadow-lg border <?php echo $border_class; ?> p-6">
                <h2 class="text-xl font-bold <?php echo $text_class; ?> mb-6 flex items-center">
                    <svg class="w-6 h-6 mr-3 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                    </svg>
                    <?php echo $t['preferences']; ?>
                </h2>

                <div class="space-y-6">
                    <!-- Theme Mode -->
                    <div>
                        <label class="block text-sm font-bold <?php echo $text_class; ?> mb-4">
                            <?php echo $t['theme_mode']; ?>
                        </label>
                        <div class="grid grid-cols-2 gap-4">
                            <button onclick="changeTheme('light')"
                                class="p-4 border-2 rounded-lg transition font-medium <?php echo $theme_mode === 'light' 
                                    ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300' 
                                    : ($is_dark ? 'border-gray-600 text-gray-300 hover:border-blue-500' : 'border-gray-300 text-gray-700 hover:border-blue-500'); ?>">
                                <svg class="w-8 h-8 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                </svg>
                                <?php echo $t['light_mode']; ?>
                            </button>
                            <button onclick="changeTheme('dark')"
                                class="p-4 border-2 rounded-lg transition font-medium <?php echo $theme_mode === 'dark' 
                                    ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300' 
                                    : ($is_dark ? 'border-gray-600 text-gray-300 hover:border-blue-500' : 'border-gray-300 text-gray-700 hover:border-blue-500'); ?>">
                                <svg class="w-8 h-8 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                                </svg>
                                <?php echo $t['dark_mode']; ?>
                            </button>
                        </div>
                    </div>

                    <!-- Language Selection -->
                    <div class="pt-4 border-t <?php echo $border_class; ?>">
                        <label class="block text-sm font-bold <?php echo $text_class; ?> mb-4">
                            <?php echo $t['language']; ?>
                        </label>
                        <select onchange="changeLanguage(this.value)"
                            class="w-full px-4 py-3 border <?php echo $input_class; ?> rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent font-medium">
                            <option value="th" <?php echo $current_lang === 'th' ? 'selected' : ''; ?>>🇹🇭 ภาษาไทย (Thai)</option>
                            <option value="en" <?php echo $current_lang === 'en' ? 'selected' : ''; ?>>🇬🇧 English</option>
                            <option value="my" <?php echo $current_lang === 'my' ? 'selected' : ''; ?>>🇲🇲 မြန်မာဘာသာ (Myanmar)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Security Card - Change Password -->
            <div class="<?php echo $card_bg; ?> rounded-lg shadow-lg border <?php echo $border_class; ?> p-6">
                <h2 class="text-xl font-bold <?php echo $text_class; ?> mb-6 flex items-center">
                    <svg class="w-6 h-6 mr-3 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                    <?php echo $t['security']; ?>
                </h2>
                <form method="POST" action="" id="passwordForm" class="space-y-4">
                    <input type="hidden" name="action" value="change_password">
                    
                    <div>
                        <label for="current_password" class="block text-sm font-medium <?php echo $label_class; ?> mb-2">
                            <?php echo $t['current_password']; ?> <span class="text-red-500">*</span>
                        </label>
                        <input type="password" id="current_password" name="current_password" required
                            class="w-full px-4 py-3 border <?php echo $input_class; ?> rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="new_password" class="block text-sm font-medium <?php echo $label_class; ?> mb-2">
                                <?php echo $t['new_password']; ?> <span class="text-red-500">*</span>
                            </label>
                            <input type="password" id="new_password" name="new_password" required
                                class="w-full px-4 py-3 border <?php echo $input_class; ?> rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent">
                            <p class="text-xs <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?> mt-2">
                                ℹ️ <?php echo $t['min_characters']; ?>
                            </p>
                        </div>
                        <div>
                            <label for="confirm_password" class="block text-sm font-medium <?php echo $label_class; ?> mb-2">
                                <?php echo $t['confirm_password']; ?> <span class="text-red-500">*</span>
                            </label>
                            <input type="password" id="confirm_password" name="confirm_password" required
                                class="w-full px-4 py-3 border <?php echo $input_class; ?> rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent">
                        </div>
                    </div>

                    <div class="pt-4">
                        <button type="submit"
                            class="w-full bg-red-600 hover:bg-red-700 text-white py-3 px-6 rounded-lg font-bold transition shadow-md hover:shadow-lg flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <?php echo $t['change_password_btn']; ?>
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<script>
    // Translation strings for JavaScript
    const translations = <?php echo json_encode([
                                'confirm_change' => $t['confirm_change'],
                                'password_mismatch' => $t['password_mismatch'],
                                'password_too_short' => $t['password_too_short']
                            ]); ?>;

    function changeTheme(mode) {
        fetch('<?php echo BASE_PATH; ?>/api/change_theme.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    mode: mode
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    console.error('Theme change failed:', data);
                    alert('Failed to change theme: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Network error: ' + error.message);
            });
    }

    function changeLanguage(lang) {
        fetch('<?php echo BASE_PATH; ?>/api/change_language.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    language: lang
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    console.error('Language change failed:', data);
                    alert('Failed to change language: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Network error: ' + error.message);
            });
    }

    document.getElementById('passwordForm').addEventListener('submit', function(e) {
        const newPass = document.getElementById('new_password').value;
        const confirmPass = document.getElementById('confirm_password').value;

        if (newPass !== confirmPass) {
            e.preventDefault();
            alert(translations.password_mismatch);
            return;
        }

        if (newPass.length < 6) {
            e.preventDefault();
            alert(translations.password_too_short);
            return;
        }

        if (!confirm(translations.confirm_change)) {
            e.preventDefault();
        }
    });
</script>