<?php
/**
 * Employees Management Page
 * Supports: Thai (ไทย), English (EN), Myanmar (မြန်မာ)
 * Features: Multi-language UI, Dark Mode, Mobile Responsive
 * Admin/Officer only
 */

require_once __DIR__ . '/../../config/db_config.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../models/Employee.php';
require_once __DIR__ . '/../../db/Localization.php';

// Require admin or officer role
AuthController::requireRole(['admin', 'officer']);

// Get current settings from session
$current_lang = $_SESSION['language'] ?? 'th';
$theme_mode = $_SESSION['theme_mode'] ?? 'light';
$is_dark = ($theme_mode === 'dark');
$user_id = $_SESSION['user_id'] ?? '';

// Theme colors based on dark mode
$card_bg = $is_dark ? 'bg-gray-800' : 'bg-white';
$text_class = $is_dark ? 'text-white' : 'text-gray-900';
$bg_class = $is_dark ? 'bg-gray-900' : 'bg-gray-50';
$border_class = $is_dark ? 'border-gray-700' : 'border-gray-200';
$input_class = $is_dark ? 'bg-gray-700 border-gray-600 text-white placeholder-gray-400' : 'bg-white border-gray-300 text-gray-900 placeholder-gray-500';

// Multi-language translations
$translations = [
    'th' => [
        'page_title' => 'พนักงาน',
        'page_subtitle' => 'จัดการและดูข้อมูลพนักงานทั้งหมด',
        'total_employees' => 'พนักงานทั้งหมด',
        'import_csv' => 'นำเข้า CSV',
        'export' => 'ส่งออก',
        'add_employee' => 'เพิ่มพนักงาน',
        'total' => 'รวม',
        'active' => 'ใช้งาน',
        'inactive' => 'ไม่ใช้งาน',
        'new_this_month' => 'ใหม่ (เดือนนี้)',
        'filters' => 'ตัวกรอง',
        'clear_filters' => 'ล้างตัวกรอง',
        'search' => 'ค้นหา',
        'search_placeholder' => 'ID, ชื่อ, โทรศัพท์...',
        'status' => 'สถานะ',
        'all_status' => 'สถานะทั้งหมด',
        'function' => 'หน้าที่',
        'all_functions' => 'หน้าที่ทั้งหมด',
        'id' => 'รหัสพนักงาน',
        'name' => 'ชื่อ',
        'position' => 'ตำแหน่ง',
        'years' => 'ปี',
        'actions' => 'การกระทำ',
        'no_employees' => 'ไม่พบพนักงาน',
        'view' => 'ดู',
        'edit' => 'แก้ไข',
        'delete' => 'ลบ',
        'previous' => 'ก่อนหน้า',
        'next' => 'ถัดไป',
        'confirm_delete' => 'คุณแน่ใจหรือว่าต้องการลบพนักงานนี้?',
        'confirm_delete_note' => 'การกระทำนี้ไม่สามารถยกเลิกได้',
        'confirm_export' => 'ส่งออกข้อมูลพนักงานทั้งหมดไปยัง CSV?',
    ],
    'en' => [
        'page_title' => 'Employees',
        'page_subtitle' => 'Manage and view all employee information',
        'total_employees' => 'Total Employees',
        'import_csv' => 'Import CSV',
        'export' => 'Export',
        'add_employee' => 'Add Employee',
        'total' => 'Total',
        'active' => 'Active',
        'inactive' => 'Inactive',
        'new_this_month' => 'New (Month)',
        'filters' => 'Filters',
        'clear_filters' => 'Clear Filters',
        'search' => 'Search',
        'search_placeholder' => 'ID, Name, Phone...',
        'status' => 'Status',
        'all_status' => 'All Status',
        'function' => 'Function',
        'all_functions' => 'All Functions',
        'id' => 'Employee ID',
        'name' => 'Name',
        'position' => 'Position',
        'years' => 'Years',
        'actions' => 'Actions',
        'no_employees' => 'No employees found',
        'view' => 'View',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'previous' => 'Previous',
        'next' => 'Next',
        'confirm_delete' => 'Are you sure you want to delete this employee?',
        'confirm_delete_note' => 'This action cannot be undone.',
        'confirm_export' => 'Export all employee data to CSV?',
    ],
    'my' => [
        'page_title' => 'အလုပ်သမားများ',
        'page_subtitle' => 'အလုပ်သမားအားလုံးအကြောင်းအရာကိုစီမံခန့်ခွဲခြင်းနှင့်ကြည့်ရှုမည်',
        'total_employees' => 'စုစုပေါင်းအလုပ်သမားများ',
        'import_csv' => 'CSV သို့မဟုတ်တင်သွင်းမည်',
        'export' => 'ထုတ်ယူမည်',
        'add_employee' => 'အလုပ်သမားထည့်သွင်းမည်',
        'total' => 'စုစုပေါင်း',
        'active' => 'တက်ကြွတဲ့',
        'inactive' => 'တက်ကြွမှုမရှိသော',
        'new_this_month' => 'အသစ် (လ)',
        'filters' => 'စစ်ထုတ်မှုများ',
        'clear_filters' => 'စစ်ထုတ်မှုများကိုရှင်းလင်းမည်',
        'search' => 'ရှာဖွေမည်',
        'search_placeholder' => 'ID, အမည်, ဖုန်း...',
        'status' => 'အနေအထား',
        'all_status' => 'အနေအထားအားလုံး',
        'function' => 'လုပ်ဆောင်ချက်',
        'all_functions' => 'လုပ်ဆောင်ချက်အားလုံး',
        'id' => 'အလုပ်သမားအိုင်ဒီ',
        'name' => 'အမည်',
        'position' => 'အနေအထား',
        'years' => 'နှစ်',
        'actions' => 'အရቀွမ်များ',
        'no_employees' => 'အလုပ်သမားများမတွေ့ရှိ',
        'view' => 'ကြည့်ရှုမည်',
        'edit' => 'ပြင်ဆင်မည်',
        'delete' => 'ဖျက်မည်',
        'previous' => 'အရင်',
        'next' => 'နောက်တစ်ခု',
        'confirm_delete' => 'ဤအလုပ်သမားကိုဖျက်ရန်သေချာပါသလား?',
        'confirm_delete_note' => 'ဤအရቀွမ်သည်ပြန်ကန်မည်မဟုတ်ခြင်း',
        'confirm_export' => 'အလုပ်သမားအားလုံးအကြောင်းအရာကို CSV သို့ထုတ်ယူမည်?',
    ]
];

// Get current language strings
$t = $translations[$current_lang] ?? $translations['th'];

// Get theme vars for backward compatibility
extract(get_theme_vars());

$page_title = $t['page_title'];
ensure_session_started();

// Get filter parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';
$function_filter = $_GET['function'] ?? '';
$per_page = 20;

$filters = [
    'search' => $search,
    'status_id' => $status_filter,
    'function_id' => $function_filter
];

// Get employees
$result = Employee::getAll($page, $per_page, $filters);
$employees = $result['data'];
$total_pages = $result['total_pages'];
$total_records = $result['total'];

// Get master data for filters
$conn = getDbConnection();
$statuses = $conn->query("SELECT * FROM status_master ORDER BY status_id")->fetch_all(MYSQLI_ASSOC);
$functions = $conn->query("SELECT * FROM function_master ORDER BY function_id")->fetch_all(MYSQLI_ASSOC);

// Get statistics
$stats = [
    'total' => $total_records,
    'active' => $conn->query("SELECT COUNT(*) as cnt FROM employees WHERE status_id = 1")->fetch_assoc()['cnt'],
    'inactive' => $conn->query("SELECT COUNT(*) as cnt FROM employees WHERE status_id != 1")->fetch_assoc()['cnt'],
    'new_this_month' => $conn->query("SELECT COUNT(*) as cnt FROM employees WHERE MONTH(date_of_hire) = MONTH(CURDATE()) AND YEAR(date_of_hire) = YEAR(CURDATE())")->fetch_assoc()['cnt']
];

$conn->close();

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>

<!DOCTYPE html>
<html lang="<?php echo $current_lang; ?>" class="<?php echo $is_dark ? 'dark' : ''; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $t['page_title']; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .theme-transition {
            transition: all 0.3s ease;
        }
    </style>
</head>
<body class="<?php echo $bg_class; ?> <?php echo $text_class; ?> theme-transition">
    <div class="lg:ml-64 min-h-screen">
        <div class="container mx-auto px-4 py-6">
            
            <!-- Page Header -->
            <div class="mb-6 bg-gradient-to-r from-green-600 to-teal-600 rounded-lg shadow-lg p-6">
                <div class="flex items-center justify-between flex-col md:flex-row gap-4">
                    <div class="flex items-center">
                        <svg class="w-10 h-10 text-white mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <div>
                            <h1 class="text-3xl font-bold text-white"><?php echo $t['page_title']; ?></h1>
                            <p class="text-green-100 mt-1"><?php echo $total_records; ?> <?php echo $t['total_employees']; ?></p>
                        </div>
                    </div>
                    <?php if ($user_role === 'admin'): ?>
                        <div class="flex gap-2 flex-wrap justify-center md:justify-end">
                            <button onclick="window.location.href='<?php echo BASE_PATH; ?>/views/admin/import_employees.php'"
                                class="hidden md:flex items-center px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-medium transition shadow-lg">
                                <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                </svg>
                                <?php echo $t['import_csv']; ?>
                            </button>
                            <button onclick="exportData()"
                                class="hidden md:flex items-center px-6 py-3 bg-white text-green-600 rounded-lg font-medium hover:bg-green-50 transition shadow-lg">
                                <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <?php echo $t['export']; ?>
                            </button>
                            <button onclick="openAddModal()"
                                class="hidden md:flex items-center px-6 py-3 bg-white text-green-600 rounded-lg font-medium hover:bg-green-50 transition shadow-lg">
                                <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                <?php echo $t['add_employee']; ?>
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <?php
                $stat_cards = [
                    ['label_key' => 'total', 'value' => $stats['total'], 'color' => 'blue', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'],
                    ['label_key' => 'active', 'value' => $stats['active'], 'color' => 'green', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                    ['label_key' => 'inactive', 'value' => $stats['inactive'], 'color' => 'red', 'icon' => 'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636'],
                    ['label_key' => 'new_this_month', 'value' => $stats['new_this_month'], 'color' => 'purple', 'icon' => 'M12 4v16m8-8H4']
                ];

                foreach ($stat_cards as $stat):
                ?>
                    <div class="<?php echo $card_bg; ?> rounded-lg shadow-lg p-4 theme-transition hover:shadow-xl border <?php echo $border_class; ?>">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm <?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?> mb-1"><?php echo $t[$stat['label_key']]; ?></p>
                                <p class="text-2xl font-bold text-<?php echo $stat['color']; ?>-600"><?php echo $stat['value']; ?></p>
                            </div>
                            <div class="w-12 h-12 bg-<?php echo $stat['color']; ?>-500 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?php echo $stat['icon']; ?>"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Filters -->
            <div class="<?php echo $card_bg; ?> rounded-lg shadow-lg p-6 mb-6 theme-transition border <?php echo $border_class; ?>">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold <?php echo $text_class; ?>"><?php echo $t['filters']; ?></h3>
                    <?php if ($search || $status_filter || $function_filter): ?>
                        <a href="?" class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 font-medium"><?php echo $t['clear_filters']; ?></a>
                    <?php endif; ?>
                </div>
                <form method="GET" action="" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2"><?php echo $t['search']; ?></label>
                        <input type="text" name="search" placeholder="<?php echo $t['search_placeholder']; ?>" value="<?php echo htmlspecialchars($search); ?>"
                            class="w-full px-4 py-2 border <?php echo $border_class; ?> rounded-lg focus:ring-2 focus:ring-blue-500 <?php echo $input_class; ?> theme-transition">
                    </div>
                    <div>
                        <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2"><?php echo $t['status']; ?></label>
                        <select name="status" class="w-full px-4 py-2 border <?php echo $border_class; ?> rounded-lg focus:ring-2 focus:ring-blue-500 <?php echo $input_class; ?> theme-transition">
                            <option value=""><?php echo $t['all_status']; ?></option>
                            <?php foreach ($statuses as $status): ?>
                                <option value="<?php echo $status['status_id']; ?>" <?php echo $status_filter == $status['status_id'] ? 'selected' : ''; ?>>
                                    <?php 
                                    // Get status in current language
                                    if ($current_lang === 'th') {
                                        echo htmlspecialchars($status['status_name_th'] ?? 'N/A');
                                    } elseif ($current_lang === 'en') {
                                        echo htmlspecialchars($status['status_name_en'] ?? 'N/A');
                                    } else {
                                        echo htmlspecialchars($status['status_name_my'] ?? 'N/A');
                                    }
                                    ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2"><?php echo $t['function']; ?></label>
                        <select name="function" class="w-full px-4 py-2 border <?php echo $border_class; ?> rounded-lg focus:ring-2 focus:ring-blue-500 <?php echo $input_class; ?> theme-transition">
                            <option value=""><?php echo $t['all_functions']; ?></option>
                            <?php foreach ($functions as $func): ?>
                                <option value="<?php echo $func['function_id']; ?>" <?php echo $function_filter == $func['function_id'] ? 'selected' : ''; ?>>
                                    <?php 
                                    // Get function in current language
                                    if ($current_lang === 'th') {
                                        echo htmlspecialchars($func['function_name_th'] ?? 'N/A');
                                    } elseif ($current_lang === 'en') {
                                        echo htmlspecialchars($func['function_name_en'] ?? 'N/A');
                                    } else {
                                        echo htmlspecialchars($func['function_name_my'] ?? 'N/A');
                                    }
                                    ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition">
                            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            <?php echo $t['search']; ?>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Employee Table -->
            <div class="<?php echo $card_bg; ?> rounded-lg shadow-lg overflow-hidden theme-transition border <?php echo $border_class; ?>">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white">
                            <tr>
                                <th class="px-6 py-4 text-left text-sm font-semibold"><?php echo $t['id']; ?></th>
                                <th class="px-6 py-4 text-left text-sm font-semibold"><?php echo $t['name']; ?></th>
                                <th class="px-6 py-4 text-left text-sm font-semibold"><?php echo $t['position']; ?></th>
                                <th class="px-6 py-4 text-left text-sm font-semibold"><?php echo $t['function']; ?></th>
                                <th class="px-6 py-4 text-left text-sm font-semibold"><?php echo $t['status']; ?></th>
                                <th class="px-6 py-4 text-left text-sm font-semibold"><?php echo $t['years']; ?></th>
                                <th class="px-6 py-4 text-center text-sm font-semibold"><?php echo $t['actions']; ?></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y <?php echo $is_dark ? 'divide-gray-700' : 'divide-gray-200'; ?>">
                            <?php if (empty($employees)): ?>
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center">
                                        <svg class="w-16 h-16 <?php echo $is_dark ? 'text-gray-600' : 'text-gray-400'; ?> mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                        </svg>
                                        <p class="<?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?> font-medium"><?php echo $t['no_employees']; ?></p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($employees as $emp): ?>
                                    <tr class="<?php echo $is_dark ? 'hover:bg-gray-700' : 'hover:bg-gray-50'; ?> transition">
                                        <td class="px-6 py-4 text-sm font-medium <?php echo $text_class; ?>">
                                            <?php echo htmlspecialchars($emp['employee_id']); ?>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center">
                                                <div class="w-10 h-10 rounded-full <?php echo $is_dark ? 'bg-gray-700' : 'bg-gray-200'; ?> flex items-center justify-center mr-3">
                                                    <?php if ($emp['profile_pic_path']): ?>
                                                        <img src="<?php echo htmlspecialchars($emp['profile_pic_path']); ?>" class="w-full h-full rounded-full object-cover" alt="">
                                                    <?php else: ?>
                                                        <span class="<?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?> font-medium text-lg">
                                                            <?php 
                                                            $name = $current_lang === 'en' ? ($emp['full_name_en'] ?? '') : ($emp['full_name_th'] ?? '');
                                                            echo strtoupper(substr($name, 0, 1)); 
                                                            ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                                <div>
                                                    <p class="text-sm font-medium <?php echo $text_class; ?>">
                                                        <?php echo $current_lang === 'en' ? htmlspecialchars($emp['full_name_en'] ?? '') : htmlspecialchars($emp['full_name_th'] ?? ''); ?>
                                                    </p>
                                                    <p class="text-xs <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?>">
                                                        <?php
                                                        echo htmlspecialchars(
                                                            !empty($emp['phone_no']) && $emp['phone_no'] != '0'
                                                                ? $emp['phone_no']
                                                                : 'xxx-xxx-xxxx'
                                                        );
                                                        ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-sm <?php echo $is_dark ? 'text-gray-300' : 'text-gray-700'; ?>">
                                            <?php 
                                            if ($current_lang === 'th') {
                                                echo htmlspecialchars($emp['position_name_th'] ?? 'N/A');
                                            } elseif ($current_lang === 'en') {
                                                echo htmlspecialchars($emp['position_name_en'] ?? 'N/A');
                                            } else {
                                                echo htmlspecialchars($emp['position_name_my'] ?? 'N/A');
                                            }
                                            ?>
                                        </td>
                                        <td class="px-6 py-4 text-sm <?php echo $is_dark ? 'text-gray-300' : 'text-gray-700'; ?>">
                                            <?php 
                                            if ($current_lang === 'th') {
                                                echo htmlspecialchars($emp['function_name_th'] ?? 'N/A');
                                            } elseif ($current_lang === 'en') {
                                                echo htmlspecialchars($emp['function_name_en'] ?? 'N/A');
                                            } else {
                                                echo htmlspecialchars($emp['function_name_my'] ?? 'N/A');
                                            }
                                            ?>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="px-3 py-1 rounded-full text-xs font-medium <?php echo $emp['status_id'] == 1 ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' : 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200'; ?>">
                                                <?php 
                                                if ($current_lang === 'th') {
                                                    echo htmlspecialchars($emp['status_name_th'] ?? 'N/A');
                                                } elseif ($current_lang === 'en') {
                                                    echo htmlspecialchars($emp['status_name_en'] ?? 'N/A');
                                                } else {
                                                    echo htmlspecialchars($emp['status_name_my'] ?? 'N/A');
                                                }
                                                ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm <?php echo $is_dark ? 'text-gray-300' : 'text-gray-700'; ?>">
                                            <?php echo $emp['year_of_service']; ?> <?php echo $t['years']; ?>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <div class="flex items-center justify-center space-x-2">
                                                <button onclick="viewEmployee('<?php echo $emp['employee_id']; ?>')" 
                                                    class="p-2 text-blue-600 hover:bg-blue-100 dark:hover:bg-blue-900 rounded-lg transition" 
                                                    title="<?php echo $t['view']; ?>">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                    </svg>
                                                </button>
                                                <?php if ($user_role === 'admin'): ?>
                                                    <button onclick="editEmployee('<?php echo $emp['employee_id']; ?>')" 
                                                        class="p-2 text-green-600 hover:bg-green-100 dark:hover:bg-green-900 rounded-lg transition" 
                                                        title="<?php echo $t['edit']; ?>">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                        </svg>
                                                    </button>
                                                    <button onclick="deleteEmployee('<?php echo $emp['employee_id']; ?>')" 
                                                        class="p-2 text-red-600 hover:bg-red-100 dark:hover:bg-red-900 rounded-lg transition" 
                                                        title="<?php echo $t['delete']; ?>">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                        </svg>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="flex justify-center space-x-2 mt-6 flex-wrap">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status_filter; ?>&function=<?php echo $function_filter; ?>"
                            class="px-4 py-2 border <?php echo $border_class; ?> rounded-lg transition <?php echo $is_dark ? 'hover:bg-gray-700' : 'hover:bg-gray-50'; ?>"><?php echo $t['previous']; ?></a>
                    <?php endif; ?>

                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                        <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status_filter; ?>&function=<?php echo $function_filter; ?>"
                            class="px-4 py-2 border rounded-lg transition <?php echo $i === $page ? 'bg-blue-600 text-white border-blue-600' : $border_class . ($is_dark ? ' hover:bg-gray-700' : ' hover:bg-gray-50'); ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status_filter; ?>&function=<?php echo $function_filter; ?>"
                            class="px-4 py-2 border <?php echo $border_class; ?> rounded-lg transition <?php echo $is_dark ? 'hover:bg-gray-700' : 'hover:bg-gray-50'; ?>"><?php echo $t['next']; ?></a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        const currentLang = '<?php echo $current_lang; ?>';
        const t = <?php echo json_encode($t); ?>;

        function viewEmployee(id) {
            window.location.href = '<?php echo BASE_PATH; ?>/views/admin/employee_detail.php?id=' + id;
        }

        function editEmployee(id) {
            window.location.href = '<?php echo BASE_PATH; ?>/views/admin/employee_edit.php?id=' + id;
        }

        function deleteEmployee(id) {
            if (confirm(t['confirm_delete'] + '\n\n' + t['confirm_delete_note'])) {
                window.location.href = '<?php echo BASE_PATH; ?>/api/employee_delete.php?id=' + id;
            }
        }

        function openAddModal() {
            window.location.href = '<?php echo BASE_PATH; ?>/views/admin/employee_add.php';
        }

        function exportData() {
            if (confirm(t['confirm_export'])) {
                window.location.href = '<?php echo BASE_PATH; ?>/api/employee_export.php?<?php echo http_build_query(["search" => $search, "status" => $status_filter, "function" => $function_filter]); ?>';
            }
        }
    </script>

    <?php include __DIR__ . '/../../includes/footer.php'; ?>
</body>
</html>