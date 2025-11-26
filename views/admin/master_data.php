<?php
/**
 * Master Data Management System - STANDARDIZED UI VERSION ✅
 * ✅ Matches my_requests.php design and layout
 * ✅ Gradient header with icon
 * ✅ max-w-5xl container (consistent)
 * ✅ Full dark mode support
 * ✅ Responsive design - Mobile First
 * Supports: Thai (ไทย), English (EN), Myanmar (မြန်မာ)
 * Admin Only Access
 */
require_once __DIR__ . '/../../config/db_config.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../db/Localization.php';

// Require admin role
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

// Multi-language translations for entire page
$translations = [
    'th' => [
        'page_title' => 'จัดการข้อมูลหลัก',
        'page_subtitle' => 'บริหารจัดการข้อมูลหลักของระบบ',
        'admin_only' => 'เฉพาะผู้ดูแลระบบ',
        'select_table' => 'เลือกตาราง',
        'function' => 'หน้าที่',
        'division' => 'สังกัด',
        'department' => 'แผนก',
        'section' => 'ส่วน',
        'operation' => 'ปฏิบัติการ',
        'position' => 'ตำแหน่ง',
        'position_level' => 'ระดับตำแหน่ง',
        'labour_cost' => 'ประเภทลักษณะการจ้าง',
        'hiring_type' => 'ประเภทการจ้าง',
        'customer_zone' => 'เขตบริการลูกค้า',
        'contribution_level' => 'ระดับการจ่ายประกันสังคม',
        'sex' => 'เพศ',
        'nationality' => 'สัญชาติ',
        'education_level' => 'ระดับการศึกษา',
        'status' => 'สถานะพนักงาน',
        'termination_reason' => 'เหตุผลการสิ้นสุดการจ้าง',
        'prefix' => 'คำหน้า',
        'service_category' => 'หมวดหมู่บริการ',
        'service_type' => 'ประเภทบริการ',
        'doc_type' => 'ประเภทเอกสาร',
        'add_new' => 'เพิ่มใหม่',
        'edit' => 'แก้ไข',
        'delete' => 'ลบ',
        'cancel' => 'ยกเลิก',
        'save' => 'บันทึก',
        'name_th' => 'ชื่อ (ไทย)',
        'name_en' => 'ชื่อ (อังกฤษ)',
        'name_my' => 'ชื่อ (พม่า)',
        'required' => 'จำเป็น',
        'records' => 'รายการ',
        'total' => 'รวม',
        'no_data' => 'ไม่มีข้อมูล',
        'confirm_delete' => 'คุณแน่ใจหรือว่าต้องการลบรายการนี้?',
        'delete_success' => 'ลบเรียบร้อยแล้ว',
        'delete_error' => 'ไม่สามารถลบได้',
        'add_success' => 'เพิ่มเรียบร้อยแล้ว',
        'add_error' => 'ไม่สามารถเพิ่มได้',
        'edit_success' => 'แก้ไขเรียบร้อยแล้ว',
        'edit_error' => 'ไม่สามารถแก้ไขได้',
        'close' => 'ปิด',
        'actions' => 'การจัดการ',
    ],
    'en' => [
        'page_title' => 'Master Data Management',
        'page_subtitle' => 'Manage system master data and configurations',
        'admin_only' => 'Admin Only',
        'select_table' => 'Select Table',
        'function' => 'Functions',
        'division' => 'Divisions',
        'department' => 'Departments',
        'section' => 'Sections',
        'operation' => 'Operations',
        'position' => 'Positions',
        'position_level' => 'Position Levels',
        'labour_cost' => 'Labour Cost Types',
        'hiring_type' => 'Hiring Types',
        'customer_zone' => 'Customer Zones',
        'contribution_level' => 'Contribution Levels',
        'sex' => 'Gender',
        'nationality' => 'Nationalities',
        'education_level' => 'Education Levels',
        'status' => 'Employment Status',
        'termination_reason' => 'Termination Reasons',
        'prefix' => 'Prefixes',
        'service_category' => 'Service Categories',
        'service_type' => 'Service Types',
        'doc_type' => 'Document Types',
        'add_new' => 'Add New',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'cancel' => 'Cancel',
        'save' => 'Save',
        'name_th' => 'Name (Thai)',
        'name_en' => 'Name (English)',
        'name_my' => 'Name (Myanmar)',
        'required' => 'Required',
        'records' => 'Records',
        'total' => 'Total',
        'no_data' => 'No Data',
        'confirm_delete' => 'Are you sure you want to delete this item?',
        'delete_success' => 'Deleted successfully',
        'delete_error' => 'Failed to delete',
        'add_success' => 'Added successfully',
        'add_error' => 'Failed to add',
        'edit_success' => 'Updated successfully',
        'edit_error' => 'Failed to update',
        'close' => 'Close',
        'actions' => 'Actions',
    ],
    'my' => [
        'page_title' => 'အဓိကဒေတာစီမံခန့်ခွဲမှု',
        'page_subtitle' => 'စနစ်အဓိကဒေတာ သို့မဟုတ် ကွန်ဖစ်ဂျူးရေးရှင်းကိုစီမံခန့်ခွဲမည်',
        'admin_only' => 'အုပ်ချုပ်ရန်သာ',
        'select_table' => 'ဇယားရွေးချယ်မည်',
        'function' => 'လုပ်ဆောင်ချက်များ',
        'division' => 'ဌာနများ',
        'department' => 'ဝန်ဆောင်မှုများ',
        'section' => 'အပိုင်းများ',
        'operation' => 'လုပ်ဆောင်ချက်များ',
        'position' => 'အနေအထားများ',
        'position_level' => 'အနေအထားအဆင့်များ',
        'labour_cost' => 'အလုပ်အခကိုသုံးစွဲမှုအမျိုးအစား',
        'hiring_type' => 'ငှားရမ်းခြင်းအမျိုးအစား',
        'customer_zone' => 'ဝယ်ယူသူဒေသများ',
        'contribution_level' => 'ပံ့ပိုးမှုအဆင့်များ',
        'sex' => 'လိင်',
        'nationality' => 'နိုင်ငံ籍',
        'education_level' => 'ပညာရေးအဆင့်',
        'status' => 'အလုပ်သမားအနေအထား',
        'termination_reason' => 'အလုပ်အကျ်ုးခြင်းအကြောင်းအရာ',
        'prefix' => 'ရှေ့ဆက်များ',
        'service_category' => 'ဝန်ဆောင်မှုအမျိုးအစားများ',
        'service_type' => 'ဝန်ဆောင်မှုအမျိုးအစား',
        'doc_type' => 'စာ類အမျိုးအစား',
        'add_new' => 'အသစ်ထည့်သွင်းမည်',
        'edit' => 'ပြင်ဆင်မည်',
        'delete' => 'ဖျက်မည်',
        'cancel' => 'ပယ်ဖျက်မည်',
        'save' => 'သိမ်းဆည်းမည်',
        'name_th' => 'အမည် (ထိုင်း)',
        'name_en' => 'အမည် (အင်္ဂလိပ်)',
        'name_my' => 'အမည် (မြန်မာ)',
        'required' => 'လိုအပ်သည်',
        'records' => 'မှတ်တမ်းများ',
        'total' => 'စုစုပေါင်း',
        'no_data' => 'ဒေတာမရှိ',
        'confirm_delete' => 'ဤအရာကိုဖျက်ရန်သေချာပါသလား?',
        'delete_success' => 'ဖျက်ခြင်းအောင်မြင်ခြင်း',
        'delete_error' => 'ဖျက်ရန်မကြိုးစားနိုင်ခြင်း',
        'add_success' => 'အောင်မြင်စွာထည့်သွင်းခြင်း',
        'add_error' => 'ထည့်သွင်းရန်မကြိုးစားနိုင်ခြင်း',
        'edit_success' => 'အောင်မြင်စွာပြင်ဆင်ခြင်း',
        'edit_error' => 'ပြင်ဆင်ရန်မကြိုးစားနိုင်ခြင်း',
        'close' => 'ပိတ်မည်',
        'actions' => 'အရቀွမ်များ',
    ]
];

// Get current language strings
$t = $translations[$current_lang] ?? $translations['th'];

// Table configuration
$tables_config = [
    'function_master' => ['name_key' => 'function', 'columns' => ['function_name_th', 'function_name_en', 'function_name_my']],
    'division_master' => ['name_key' => 'division', 'columns' => ['division_name_th', 'division_name_en', 'division_name_my']],
    'department_master' => ['name_key' => 'department', 'columns' => ['department_name_th', 'department_name_en', 'department_name_my']],
    'section_master' => ['name_key' => 'section', 'columns' => ['section_name_th', 'section_name_en', 'section_name_my']],
    'operation_master' => ['name_key' => 'operation', 'columns' => ['operation_name_th', 'operation_name_en', 'operation_name_my']],
    'position_master' => ['name_key' => 'position', 'columns' => ['position_name_th', 'position_name_en', 'position_name_my']],
    'position_level_master' => ['name_key' => 'position_level', 'columns' => ['level_name_th', 'level_name_en', 'level_name_my']],
    'labour_cost_master' => ['name_key' => 'labour_cost', 'columns' => ['cost_name_th', 'cost_name_en', 'cost_name_my']],
    'hiring_type_master' => ['name_key' => 'hiring_type', 'columns' => ['type_name_th', 'type_name_en', 'type_name_my']],
    'customer_zone_master' => ['name_key' => 'customer_zone', 'columns' => ['zone_name_th', 'zone_name_en', 'zone_name_my']],
    'contribution_level_master' => ['name_key' => 'contribution_level', 'columns' => ['level_name_th', 'level_name_en', 'level_name_my']],
    'sex_master' => ['name_key' => 'sex', 'columns' => ['sex_name_th', 'sex_name_en', 'sex_name_my']],
    'nationality_master' => ['name_key' => 'nationality', 'columns' => ['nationality_th', 'nationality_en', 'nationality_my']],
    'education_level_master' => ['name_key' => 'education_level', 'columns' => ['level_name_th', 'level_name_en', 'level_name_my']],
    'status_master' => ['name_key' => 'status', 'columns' => ['status_name_th', 'status_name_en', 'status_name_my']],
    'termination_reason_master' => ['name_key' => 'termination_reason', 'columns' => ['reason_th', 'reason_en', 'reason_my']],
    'prefix_master' => ['name_key' => 'prefix', 'columns' => ['prefix_th', 'prefix_en', 'prefix_my']],
    'service_category_master' => ['name_key' => 'service_category', 'columns' => ['category_name_th', 'category_name_en', 'category_name_my']],
    'service_type_master' => ['name_key' => 'service_type', 'columns' => ['type_name_th', 'type_name_en', 'type_name_my']],
    'doc_type_master' => ['name_key' => 'doc_type', 'columns' => ['type_name_th', 'type_name_en', 'type_name_my']],
];

// Get selected table
$selected_table = $_GET['table'] ?? 'function_master';
if (!isset($tables_config[$selected_table])) {
    $selected_table = 'function_master';
}
$table_info = $tables_config[$selected_table];
$table_display_name = $t[$table_info['name_key']] ?? $selected_table;

// Handle CRUD operations
$message = '';
$message_type = '';
$conn = getDbConnection();

// Handle POST requests (Add/Edit/Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $table = $_POST['table'] ?? '';
    
    if (!isset($tables_config[$table])) {
        $message = $t['add_error'];
        $message_type = 'error';
    } else {
        $config = $tables_config[$table];
        
        // ✅ Handle DELETE action (ไม่ต้องตรวจสอบ name_th)
        if ($action === 'delete') {
            $id = $_POST['id'] ?? '';
            if (empty($id)) {
                $message = 'Invalid ID';
                $message_type = 'error';
            } else {
                $id_col = match ($table) {
                    'prefix_master' => 'prefix_id',
                    'function_master' => 'function_id',
                    'division_master' => 'division_id',
                    'department_master' => 'department_id',
                    'section_master' => 'section_id',
                    'operation_master' => 'operation_id',
                    'position_master' => 'position_id',
                    'position_level_master' => 'level_id',
                    'labour_cost_master' => 'labour_cost_id',
                    'hiring_type_master' => 'hiring_type_id',
                    'customer_zone_master' => 'zone_id',
                    'contribution_level_master' => 'contribution_id',
                    'sex_master' => 'sex_id',
                    'nationality_master' => 'nationality_id',
                    'education_level_master' => 'education_id',
                    'status_master' => 'status_id',
                    'termination_reason_master' => 'reason_id',
                    'service_category_master' => 'category_id',
                    'service_type_master' => 'type_id',
                    'doc_type_master' => 'doc_type_id',
                    default => 'id'
                };
                $sql = "DELETE FROM $table WHERE $id_col = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('i', $id);
                if ($stmt->execute()) {
                    $message = $t['delete_success'];
                    $message_type = 'success';
                } else {
                    $message = $t['delete_error'] . ': ' . $stmt->error;
                    $message_type = 'error';
                }
                $stmt->close();
            }
        } else {
            // ✅ For ADD and EDIT - validate name_th
            $name_th = $_POST['name_th'] ?? '';
            $name_en = $_POST['name_en'] ?? '';
            $name_my = $_POST['name_my'] ?? '';
            
            if (empty($name_th)) {
                $message = 'กรุณากรอกชื่อ (ไทย)';
                $message_type = 'error';
            } else {
                if ($action === 'add') {
                    $cols = $config['columns'];
                    $placeholders = implode(',', array_fill(0, 3, '?'));
                    $sql = "INSERT INTO $table ({$cols[0]}, {$cols[1]}, {$cols[2]}) VALUES ($placeholders)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param('sss', $name_th, $name_en, $name_my);
                    if ($stmt->execute()) {
                        $message = $t['add_success'];
                        $message_type = 'success';
                    } else {
                        $message = $t['add_error'] . ': ' . $stmt->error;
                        $message_type = 'error';
                    }
                    $stmt->close();
                } elseif ($action === 'edit') {
                    $id = $_POST['id'] ?? '';
                    if (empty($id)) {
                        $message = 'Invalid ID';
                        $message_type = 'error';
                    } else {
                        $cols = $config['columns'];
                        $id_col = match ($table) {
                            'prefix_master' => 'prefix_id',
                            'function_master' => 'function_id',
                            'division_master' => 'division_id',
                            'department_master' => 'department_id',
                            'section_master' => 'section_id',
                            'operation_master' => 'operation_id',
                            'position_master' => 'position_id',
                            'position_level_master' => 'level_id',
                            'labour_cost_master' => 'labour_cost_id',
                            'hiring_type_master' => 'hiring_type_id',
                            'customer_zone_master' => 'zone_id',
                            'contribution_level_master' => 'contribution_id',
                            'sex_master' => 'sex_id',
                            'nationality_master' => 'nationality_id',
                            'education_level_master' => 'education_id',
                            'status_master' => 'status_id',
                            'termination_reason_master' => 'reason_id',
                            'service_category_master' => 'category_id',
                            'service_type_master' => 'type_id',
                            'doc_type_master' => 'doc_type_id',
                            default => 'id'
                        };
                        $sql = "UPDATE $table SET {$cols[0]} = ?, {$cols[1]} = ?, {$cols[2]} = ?, updated_at = CURRENT_TIMESTAMP WHERE $id_col = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param('sssi', $name_th, $name_en, $name_my, $id);
                        if ($stmt->execute()) {
                            $message = $t['edit_success'];
                            $message_type = 'success';
                        } else {
                            $message = $t['edit_error'] . ': ' . $stmt->error;
                            $message_type = 'error';
                        }
                        $stmt->close();
                    }
                }
            }
        }
    }
}

// Get data from selected table
$records = [];
$primary_key = '';
$id_column = match ($selected_table) {
    'prefix_master' => 'prefix_id',
    'function_master' => 'function_id',
    'division_master' => 'division_id',
    'department_master' => 'department_id',
    'section_master' => 'section_id',
    'operation_master' => 'operation_id',
    'position_master' => 'position_id',
    'position_level_master' => 'level_id',
    'labour_cost_master' => 'labour_cost_id',
    'hiring_type_master' => 'hiring_type_id',
    'customer_zone_master' => 'zone_id',
    'contribution_level_master' => 'contribution_id',
    'sex_master' => 'sex_id',
    'nationality_master' => 'nationality_id',
    'education_level_master' => 'education_id',
    'status_master' => 'status_id',
    'termination_reason_master' => 'reason_id',
    'service_category_master' => 'category_id',
    'service_type_master' => 'type_id',
    'doc_type_master' => 'doc_type_id',
    default => 'id'
};

$result = $conn->query("SELECT * FROM $selected_table ORDER BY $id_column DESC LIMIT 100");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $records[] = $row;
    }
}

// Include header and sidebar
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>
    <div class="lg:ml-64 min-h-screen">
        <div class="container mx-auto px-4 py-6">
        
        <!-- Page Header - Gradient Style -->
        <div class="mb-6 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-lg shadow-lg p-6">
            <div class="flex items-center">
                <svg class="w-10 h-10 text-white mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path>
                </svg>
                <div>
                    <h1 class="text-3xl font-bold text-white"><?php echo $t['page_title']; ?></h1>
                    <p class="text-blue-100 mt-1"><?php echo $t['page_subtitle']; ?></p>
                </div>
            </div>
        </div>

        <!-- Messages -->
        <?php if ($message): ?>
            <div class="mb-6 p-4 rounded-lg flex items-center gap-3 <?php echo $message_type === 'success' ? 'bg-green-50 dark:bg-green-900/20 border-l-4 border-green-500' : 'bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500'; ?>">
                <?php if ($message_type === 'success'): ?>
                    <svg class="w-6 h-6 text-green-600 dark:text-green-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="text-green-700 dark:text-green-300 font-medium"><?php echo htmlspecialchars($message); ?></p>
                <?php else: ?>
                    <svg class="w-6 h-6 text-red-600 dark:text-red-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="text-red-700 dark:text-red-300 font-medium"><?php echo htmlspecialchars($message); ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Table Selection Tabs -->
        <div class="mb-6 <?php echo $card_bg; ?> rounded-lg shadow-lg border <?php echo $border_class; ?> overflow-hidden">
            <div class="overflow-x-auto">
                <div class="flex gap-2 p-4 min-w-full">
                    <?php foreach ($tables_config as $table_name => $config): ?>
                        <a href="?table=<?php echo urlencode($table_name); ?>" 
                           class="px-4 py-2 whitespace-nowrap rounded-lg font-medium text-sm transition <?php echo $selected_table === $table_name ? 'bg-blue-600 text-white' : ($is_dark ? 'bg-gray-700 text-gray-300 hover:bg-gray-600' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'); ?>">
                            <?php echo $t[$config['name_key']] ?? $table_name; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Main Card -->
        <div class="<?php echo $card_bg; ?> rounded-lg shadow-lg overflow-hidden border <?php echo $border_class; ?>">
            
            <!-- Card Header -->
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-4 flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-white"><?php echo $table_display_name; ?></h2>
                    <p class="text-blue-100 text-sm mt-1"><?php echo $t['total']; ?>: <span class="font-bold"><?php echo count($records); ?></span> <?php echo $t['records']; ?></p>
                </div>
                <button onclick="openAddModal()" class="px-6 py-3 bg-white hover:bg-gray-100 text-blue-600 font-bold rounded-lg transition shadow-md hover:shadow-lg flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <?php echo $t['add_new']; ?>
                </button>
            </div>

            <!-- Table Content -->
            <div class="p-6">
                <?php if (empty($records)): ?>
                    <div class="text-center py-12">
                        <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <p class="<?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?> text-lg font-medium"><?php echo $t['no_data']; ?></p>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="<?php echo $is_dark ? 'bg-gray-700' : 'bg-gray-100'; ?> border-b <?php echo $border_class; ?>">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold <?php echo $text_class; ?> uppercase"><?php echo $t['name_th']; ?></th>
                                    <th class="px-6 py-3 text-left text-xs font-bold <?php echo $text_class; ?> uppercase"><?php echo $t['name_en']; ?></th>
                                    <th class="px-6 py-3 text-left text-xs font-bold <?php echo $text_class; ?> uppercase"><?php echo $t['name_my']; ?></th>
                                    <th class="px-6 py-3 text-right text-xs font-bold <?php echo $text_class; ?> uppercase"><?php echo $t['actions']; ?></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y <?php echo $border_class; ?>">
                                <?php foreach ($records as $record): ?>
                                    <tr class="<?php echo $is_dark ? 'hover:bg-gray-700' : 'hover:bg-gray-50'; ?> transition">
                                        <td class="px-6 py-4 text-sm <?php echo $text_class; ?>"><?php echo htmlspecialchars($record[$table_info['columns'][0]] ?? ''); ?></td>
                                        <td class="px-6 py-4 text-sm <?php echo $text_class; ?>"><?php echo htmlspecialchars($record[$table_info['columns'][1]] ?? ''); ?></td>
                                        <td class="px-6 py-4 text-sm <?php echo $text_class; ?>"><?php echo htmlspecialchars($record[$table_info['columns'][2]] ?? ''); ?></td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <button onclick="openEditModal(<?php echo htmlspecialchars(json_encode($record)); ?>)" 
                                                        class="p-2 text-blue-600 hover:text-blue-800 hover:bg-blue-50 dark:text-blue-400 dark:hover:text-blue-300 dark:hover:bg-gray-600 rounded-lg transition"
                                                        title="<?php echo $t['edit']; ?>">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                    </svg>
                                                </button>
                                                <button onclick="deleteRecord(<?php echo $record[$id_column]; ?>)" 
                                                        class="p-2 text-red-600 hover:text-red-800 hover:bg-red-50 dark:text-red-400 dark:hover:text-red-300 dark:hover:bg-gray-600 rounded-lg transition"
                                                        title="<?php echo $t['delete']; ?>">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Modal -->
<div id="formModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center p-4 z-50">
    <div class="<?php echo $card_bg; ?> rounded-lg shadow-xl max-w-md w-full border <?php echo $border_class; ?>">
        <div class="flex items-center justify-between p-6 border-b <?php echo $border_class; ?> bg-gradient-to-r from-blue-600 to-indigo-600">
            <h3 id="modalTitle" class="text-xl font-bold text-white"></h3>
            <button onclick="closeModal()" class="text-white hover:text-gray-200 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <form id="masterForm" method="POST" class="p-6 space-y-4">
            <input type="hidden" name="action" id="formAction" value="add">
            <input type="hidden" name="table" value="<?php echo htmlspecialchars($selected_table); ?>">
            <input type="hidden" name="id" id="recordId" value="">
            
            <div>
                <label class="block text-sm font-semibold <?php echo $label_class; ?> mb-2">
                    <?php echo $t['name_th']; ?> <span class="text-red-500">*</span>
                </label>
                <input type="text" name="name_th" id="name_th" required
                    class="w-full px-4 py-3 border <?php echo $input_class; ?> rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
            </div>
            
            <div>
                <label class="block text-sm font-semibold <?php echo $label_class; ?> mb-2">
                    <?php echo $t['name_en']; ?>
                </label>
                <input type="text" name="name_en" id="name_en"
                    class="w-full px-4 py-3 border <?php echo $input_class; ?> rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
            </div>
            
            <div>
                <label class="block text-sm font-semibold <?php echo $label_class; ?> mb-2">
                    <?php echo $t['name_my']; ?>
                </label>
                <input type="text" name="name_my" id="name_my"
                    class="w-full px-4 py-3 border <?php echo $input_class; ?> rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
            </div>
            
            <div class="flex gap-3 pt-4 border-t <?php echo $border_class; ?>">
                <button type="submit" class="flex-1 px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg transition shadow-md hover:shadow-lg">
                    ✓ <?php echo $t['save']; ?>
                </button>
                <button type="button" onclick="closeModal()" class="flex-1 px-4 py-3 bg-gray-400 hover:bg-gray-500 text-white font-bold rounded-lg transition">
                    ✕ <?php echo $t['cancel']; ?>
                </button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>

<script>
const t = <?php echo json_encode($t); ?>;
const selectedTable = '<?php echo $selected_table; ?>';
const currentLang = '<?php echo $current_lang; ?>';
const tableInfo = <?php echo json_encode($table_info); ?>;

function openAddModal() {
    document.getElementById('formAction').value = 'add';
    document.getElementById('modalTitle').textContent = t['add_new'] + ' - ' + '<?php echo $table_display_name; ?>';
    document.getElementById('masterForm').reset();
    document.getElementById('recordId').value = '';
    document.getElementById('formModal').classList.remove('hidden');
    document.getElementById('formModal').classList.add('flex');
}

function openEditModal(record) {
    document.getElementById('formAction').value = 'edit';
    document.getElementById('modalTitle').textContent = t['edit'] + ' - ' + '<?php echo $table_display_name; ?>';
    const idCol = Object.keys(record).find(key => key.includes('_id'));
    document.getElementById('recordId').value = record[idCol];
    document.getElementById('name_th').value = record[tableInfo.columns[0]] || '';
    document.getElementById('name_en').value = record[tableInfo.columns[1]] || '';
    document.getElementById('name_my').value = record[tableInfo.columns[2]] || '';
    document.getElementById('formModal').classList.remove('hidden');
    document.getElementById('formModal').classList.add('flex');
}

function closeModal() {
    document.getElementById('formModal').classList.add('hidden');
    document.getElementById('formModal').classList.remove('flex');
}

function deleteRecord(id) {
    if (confirm(t['confirm_delete'])) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="table" value="${selectedTable}">
            <input type="hidden" name="id" value="${id}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// Close modal when clicking outside
document.getElementById('formModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});
</script>